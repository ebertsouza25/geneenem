<?php
// Correção do quiz: valida os dados do aluno e as respostas. Com qualquer erro,
// o formulário volta preenchido; sem erro, mostra a nota e a correção.

require_once 'funcoes.php';
require_once 'dados_questoes.php';
require_once 'config.php';
require_once 'Aluno.php';

// A sessão já precisa estar ativa para saber de quem é o resultado antes de
// qualquer HTML ser enviado.
iniciarSessao();

$alunoObj = new Aluno($conexao);

$pagina = 'questoes';
$tituloPagina = 'Resultado';
$cssPagina = array('questoes');

// Padrões das duas validações, declarados antes das condições.
$padraoNome  = "/^[A-Za-zÀ-ÿ ]{3,60}$/u";   // letras (com acento) e espaços
$padraoTurma = "/^[0-9][0-9A-Za-zÀ-ÿºª ]{1,29}$/u"; // começa com número (ex: 3º Informática)

$nome = '';
$turma = '';
$marcadas = array();
$erros = array();
$corrigido = false;
$quantidadeAcertos = 0;
$tokenQuiz = '';
$registroFalhou = false;

// Tudo abaixo só roda quando o formulário foi enviado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Dados do aluno: recebe e guarda nas variáveis usadas pelo formulário e
    // pelo resumo final.
    if (isset($_POST['nome'])) {
        $nome = valida_dados($_POST['nome']);
    }

    if (isset($_POST['turma'])) {
        $turma = valida_dados($_POST['turma']);
    }

    // O nome é obrigatório e precisa ter apenas letras e espaços.
    if (empty($nome)) {
        $erros['nome'] = 'O campo Nome precisa ser preenchido.';
    } elseif (!preg_match($padraoNome, $nome)) {
        $erros['nome'] = 'O nome deve ter apenas letras e espaços (mínimo 3 letras).';
    }

    // A turma é obrigatória e começa com o número da série (ex: 3º Informática).
    if (empty($turma)) {
        $erros['turma'] = 'O campo Turma precisa ser preenchido.';
    } elseif (!preg_match($padraoTurma, $turma)) {
        $erros['turma'] = 'Comece a turma pelo número. Exemplo: 3º Informática.';
    }

    // Token emitido por questoes.php. Sem ele o resultado aparece, mas não é
    // gravado no perfil (evita POST montado à mão).
    if (isset($_POST['token_quiz'])) {
        $tokenQuiz = substr(valida_dados($_POST['token_quiz']), 0, 64);
    }

    // --- 2) Respostas -----------------------------------------------------
    foreach ($questoes as $numeroQuestao => $dadosQuestao) {

        $respostaMarcada = '';

        if (isset($_POST['questao' . $numeroQuestao])) {
            $respostaMarcada = valida_dados($_POST['questao' . $numeroQuestao]);
        }

        if (empty($respostaMarcada)) {
            $erros['questao' . $numeroQuestao] = 'Marque uma alternativa nesta questão.';
        } elseif (!isset($dadosQuestao['alternativas'][$respostaMarcada])) {
            // Alternativa inventada no POST: não existe no banco de questões.
            // Sem essa conferência, o gabarito poderia ser comparado com
            // qualquer valor enviado pelo navegador.
            $erros['questao' . $numeroQuestao] = 'Alternativa inválida. Marque uma das opções.';
        } else {
            $marcadas[$numeroQuestao] = $respostaMarcada;
        }
    }

    // Só corrige quando não existe nenhum erro nos dados e nas respostas.
    if (count($erros) === 0) {
        $corrigido = true;
        $respostasParaSalvar = array();

        foreach ($questoes as $numeroQuestao => $dadosQuestao) {
            $acertou = $marcadas[$numeroQuestao] === $dadosQuestao['gabarito'];

            if ($acertou) {
                $quantidadeAcertos++;
            }

            $respostasParaSalvar[$numeroQuestao] = array(
                'area' => $dadosQuestao['area'],
                'resposta' => $marcadas[$numeroQuestao],
                'gabarito' => $dadosQuestao['gabarito'],
                'acertou' => $acertou
            );
        }

        // Grava uma única vez: o token sai da sessão depois de salvo e a
        // tabela atividades tem chave única no mesmo token.
        $tokenDaSessao = isset($_SESSION['quiz_tokens'][$tokenQuiz]);

        if ($tokenDaSessao && isset($_SESSION['aluno_id'])) {
            $statusRegistro = $alunoObj->registrarAtividade(
                (int) $_SESSION['aluno_id'],
                $tokenQuiz,
                $respostasParaSalvar
            );

            if ($statusRegistro === 'gravado' || $statusRegistro === 'duplicado') {
                unset($_SESSION['quiz_tokens'][$tokenQuiz]);
            } else {
                $registroFalhou = true;
            }
        }
    }
}

$percentual = $corrigido ? arredondar($quantidadeAcertos * 100 / $total) : 0;

require_once 'topo.php';
?>

<?php if (!$corrigido): ?>

    <!-- ================= Formulário com erros ================= -->
    <section class="page-head">
        <div class="wrap">
            <span class="eyebrow">Revisar respostas</span>
            <h1>Faltou alguma coisa</h1>
            <p class="lead">
                Os campos marcados precisam de atenção. O que você já respondeu
                continua aqui.
            </p>
        </div>
    </section>

    <section class="section quiz-secao">
        <div class="wrap">

            <!-- Aviso geral: aponta que existem erros, mas cada mensagem
                 específica continua aparecendo junto do seu campo. -->
            <?php if (count($erros) > 0): ?>
                <p class="aviso aviso-erro">
                    Alguns campos precisam de atenção. Veja as mensagens em vermelho abaixo de cada um.
                </p>
            <?php endif; ?>

            <form class="quiz" id="quiz" method="post" action="resultado.php">

                <input type="hidden" name="token_quiz" value="<?php echo escaparHtml($tokenQuiz); ?>">

                <section class="panel dados-aluno">
                    <h2>Seus dados</h2>

                    <div class="grade-2">
                        <div class="field <?php echo classeErro($erros, 'nome'); ?>">
                            <label for="nome">Nome</label>
                            <input class="input" type="text" id="nome" name="nome"
                                   value="<?php echo escaparHtml($nome); ?>">
                            <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'nome')); ?></span>
                        </div>

                        <div class="field <?php echo classeErro($erros, 'turma'); ?>">
                            <label for="turma">Turma</label>
                            <input class="input" type="text" id="turma" name="turma"
                                   value="<?php echo escaparHtml($turma); ?>">
                            <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'turma')); ?></span>
                        </div>

                    </div>
                </section>

                <?php foreach ($questoes as $numeroQuestao => $dadosQuestao): ?>
                    <?php include 'parte_questao.php'; ?>
                <?php endforeach; ?>

                <div class="quiz-envio panel">
                    <div>
                        <strong>Corrigir de novo</strong>
                        <p class="muted">As respostas marcadas foram mantidas.</p>
                    </div>
                    <button class="btn btn-primary" type="submit" name="enviar" value="1">Corrigir respostas</button>
                </div>

            </form>
        </div>
    </section>

<?php else: ?>

    <!-- ================= Resultado ================= -->
    <section class="page-head">
        <div class="wrap">
            <span class="eyebrow">Correção · <?php echo escaparHtml($nome); ?></span>
            <h1>Seu resultado</h1>
            <p class="lead">Cada questão abaixo mostra a sua resposta, o gabarito e a explicação.</p>
        </div>
    </section>

    <section class="section resultado-secao">
        <div class="wrap">

            <p class="aviso aviso-sucesso">
                Respostas enviadas e corrigidas. Confira o seu desempenho abaixo.
            </p>

            <?php if ($registroFalhou): ?>
                <p class="aviso aviso-erro">
                    A correção funcionou, mas o resultado não pôde ser salvo no seu perfil agora.
                    Tente enviar de novo mais tarde.
                </p>
            <?php endif; ?>

            <div class="resultado-topo panel panel-lg">
                <!-- Anel de progresso em SVG: a porcentagem vai no atributo
                     stroke-dasharray e o desenho (cor, espessura) fica no CSS,
                     então não é preciso style="" na página. -->
                <div class="ring">
                    <svg class="ring-desenho" viewBox="0 0 36 36" role="img"
                         aria-label="<?php echo $percentual; ?>% de acerto">
                        <circle class="ring-trilha" cx="18" cy="18" r="15.5"></circle>
                        <circle class="ring-valor" cx="18" cy="18" r="15.5" pathLength="100"
                                stroke-dasharray="<?php echo $percentual; ?> 100"></circle>
                    </svg>
                    <span>
                        <strong><?php echo $percentual; ?>%</strong>
                        <small>de acerto</small>
                    </span>
                </div>

                <div class="resultado-resumo">
                    <h2><?php echo $quantidadeAcertos; ?> de <?php echo $total; ?> corretas</h2>
                    <p class="muted"><?php echo escaparHtml($turma); ?></p>

                    <?php if ($percentual >= 70): ?>
                        <p><span class="chip status-ok">Bom desempenho</span> Continue treinando para manter o ritmo.</p>
                    <?php elseif ($percentual >= 50): ?>
                        <p><span class="chip">Na média</span> Revise as questões erradas e tente de novo.</p>
                    <?php else: ?>
                        <p><span class="chip status-erro">Reforçar</span> Volte às revisões e refaça o quiz com calma.</p>
                    <?php endif; ?>

                    <div class="resultado-acoes">
                        <a class="btn btn-primary" href="questoes.php">Refazer o quiz</a>
                        <a class="btn btn-ghost" href="perfil.php">Ver meu perfil</a>
                        <a class="btn btn-ghost" href="revisoes.php">Rever as revisões</a>
                    </div>
                </div>
            </div>

            <div class="revisao">
                <?php foreach ($questoes as $numeroQuestao => $dadosQuestao): ?>
                    <?php
                    $respostaMarcada = $marcadas[$numeroQuestao];
                    $acertou = $respostaMarcada === $dadosQuestao['gabarito'];

                    if ($numeroQuestao < 10) {
                        $numeroFormatado = '0' . $numeroQuestao;
                    } else {
                        $numeroFormatado = $numeroQuestao;
                    }
                    ?>

                    <article class="revisao-item <?php echo $acertou ? 'acertou' : 'errou'; ?>">
                        <header class="revisao-topo">
                            <span class="questao-num">#<?php echo $numeroFormatado; ?></span>
                            <span class="chip"><?php echo escaparHtml($dadosQuestao['ano']); ?> · Questão <?php echo (int) $dadosQuestao['numero_original']; ?></span>
                            <span class="chip"><?php echo escaparHtml($dadosQuestao['area']); ?></span>
                            <span class="chip <?php echo $acertou ? 'status-ok' : 'status-erro'; ?>">
                                <?php echo $acertou ? 'Acertou' : 'Errou'; ?>
                            </span>
                        </header>

                        <h3><?php echo escaparHtml($dadosQuestao['titulo']); ?></h3>

                        <p class="revisao-linha">
                            <span>Sua resposta</span>
                            <strong><?php echo escaparHtml($respostaMarcada); ?>) <?php echo escaparHtml($dadosQuestao['alternativas'][$respostaMarcada]); ?></strong>
                        </p>

                        <p class="revisao-linha">
                            <span>Gabarito</span>
                            <strong><?php echo escaparHtml($dadosQuestao['gabarito']); ?>) <?php echo escaparHtml($dadosQuestao['alternativas'][$dadosQuestao['gabarito']]); ?></strong>
                        </p>

                        <p class="revisao-explicacao">
                            <strong>Explicação:</strong> <?php echo escaparHtml($dadosQuestao['explicacao']); ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

<?php endif; ?>

<?php require_once 'rodape.php'; ?>
