<?php
// Monta o formulário do quiz. A validação e a correção ficam em resultado.php;
// o cronômetro é só um apoio de estudo e não interfere nas respostas.

require_once 'dados_questoes.php';

$pagina = 'questoes';
$tituloPagina = 'Questões';
$cssPagina = array('questoes');
$jsPagina = array('cronometro');

require_once 'topo.php';

$marcadas = array();
$erros = array();

// Token deste envio. O resultado só é gravado se o token tiver sido emitido
// nesta sessão, e o banco ainda tem uma chave única como segunda proteção.
$tokenQuiz = bin2hex(random_bytes(24));
$_SESSION['quiz_tokens'][$tokenQuiz] = time();

// Guarda apenas os últimos tokens para a sessão não crescer sem limite.
$_SESSION['quiz_tokens'] = array_slice($_SESSION['quiz_tokens'], -5, null, true);
?>

<section class="page-head">
    <div class="wrap">
        <span class="eyebrow">Questões · <?php echo $total; ?></span>
        <h1>Questões de Genética do ENEM</h1>
        <p class="lead">
            Responda no seu tempo, marque as alternativas e envie para ver a
            correção comentada.
        </p>
    </div>
</section>

<section class="section quiz-secao">
    <div class="wrap">

        <!-- Cronômetro de prova, acima da área das questões -->
        <div class="quiz-toolbar">
            <div class="quiz-tempo">
                <span class="quiz-tempo-rotulo">Cronômetro</span>
                <strong id="cronometro">15:00</strong>
            </div>

            <div class="quiz-tempo-controles">
                <label class="sr-only" for="minutos">Tempo de estudo</label>
                <select class="select select-min" id="minutos">
                    <option value="5">5 min</option>
                    <option value="10">10 min</option>
                    <option value="15" selected>15 min</option>
                    <option value="30">30 min</option>
                    <option value="60">60 min</option>
                </select>

                <button type="button" class="btn btn-primary btn-sm" id="btn-iniciar">Iniciar</button>
                <button type="button" class="btn btn-ghost btn-sm" id="btn-pausar">Pausar</button>
                <button type="button" class="btn btn-ghost btn-sm" id="btn-reiniciar">Reiniciar</button>
            </div>
        </div>

        <form class="quiz" id="quiz" method="post" action="resultado.php">

            <input type="hidden" name="token_quiz" value="<?php echo escaparHtml($tokenQuiz); ?>">

            <!-- Dados do aluno -->
            <section class="panel dados-aluno">
                <h2>Seus dados</h2>

                <div class="grade-2">
                    <div class="field">
                        <label for="nome">Nome</label>
                        <input class="input" type="text" id="nome" name="nome"
                               placeholder="Digite seu nome" value="<?php echo escaparHtml($nomeAluno); ?>">
                    </div>

                    <div class="field">
                        <label for="turma">Turma</label>
                        <input class="input" type="text" id="turma" name="turma"
                               placeholder="Ex: 3º Informática" value="">
                    </div>
                </div>
            </section>

            <!-- Progresso -->
            <div class="quiz-progresso">
                <span id="quiz-progresso">0 de <?php echo $total; ?> respondidas</span>
                <div class="bar"><i id="quiz-barra"></i></div>
            </div>

            <!-- Um card por questão do banco: o foreach entrega o número da
                 questão e os dados para parte_questao.php. -->
            <?php foreach ($questoes as $numeroQuestao => $dadosQuestao): ?>
                <?php include 'parte_questao.php'; ?>
            <?php endforeach; ?>

            <div class="quiz-envio panel">
                <div>
                    <strong>Terminou?</strong>
                    <p class="muted">A correção mostra a resposta certa e a explicação de cada questão.</p>
                </div>
                <button class="btn btn-primary" type="submit" name="enviar" value="1">Corrigir respostas</button>
            </div>

        </form>
    </div>
</section>

<?php require_once 'rodape.php'; ?>
