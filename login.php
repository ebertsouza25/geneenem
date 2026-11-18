<?php
// Login do aluno: valida o formato dos campos, confere e-mail e senha no
// MySQL e só abre a sessão quando as credenciais estão certas.

require_once 'funcoes.php';
require_once 'dados_questoes.php';
require_once 'config.php';
require_once 'Aluno.php';

iniciarSessao();

// Quem já entrou não precisa ver o login de novo
if (isset($_SESSION['aluno_id'])) {
    header('Location: index.php');
    exit;
}

$alunoObj = new Aluno($conexao);
$tokenFormulario = tokenCsrf();

// Filtros e padrões ficam em variáveis com nome para as condições abaixo
// ficarem legíveis. A senha exige 8 caracteres, com maiúscula, minúscula,
// número e símbolo.
$filtroEmail = FILTER_VALIDATE_EMAIL;
$padraoSenha = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/";

$email = '';
$senha = '';
$erros = array();

// Só valida alguma coisa quando o formulário foi realmente enviado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe os dois campos do formulário e guarda nas variáveis que a página
    // usa depois (inclusive quando há erro, para não apagar o que foi digitado).
    if (isset($_POST['email'])) {
        $email = valida_dados($_POST['email']);
    }

    if (isset($_POST['senha'])) {
        $senha = valida_dados($_POST['senha']);
    }

    // E-mail: primeiro confere se foi preenchido, depois se o formato é válido.
    // A ordem importa: campo vazio tem uma mensagem própria, não "e-mail inválido".
    if (empty($email)) {
        $erros['email'] = 'Informe o seu e-mail.';
    } elseif (!filter_var($email, $filtroEmail)) {
        $erros['email'] = 'E-mail inválido. Exemplo: aluno@email.com';
    }

    // Senha: obrigatória e com as quatro exigências da expressão regular.
    if (empty($senha)) {
        $erros['senha'] = 'Informe a sua senha.';
    } elseif (!preg_match($padraoSenha, $senha)) {
        $erros['senha'] = 'Use 8 caracteres ou mais, com maiúscula, minúscula, número e símbolo.';
    }

    // Confere o token do formulário antes de tentar autenticar.
    if (!csrfValido(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $erros['geral'] = 'A página ficou aberta por muito tempo. Envie o formulário de novo.';
    }

    // Só continua quando não existe nenhum erro de validação.
    if (count($erros) === 0) {
        $aluno = $alunoObj->autenticar($email, $senha);

        if ($aluno === null) {
            // A mesma mensagem para e-mail inexistente e senha errada evita
            // entregar quais e-mails estão cadastrados.
            $erros['geral'] = 'E-mail ou senha incorretos.';
        } else {
            // Troca o id da sessão depois do login, contra session fixation.
            session_regenerate_id(true);

            $_SESSION['aluno_id'] = (int) $aluno['id'];
            $_SESSION['aluno'] = $aluno['email'];
            $_SESSION['aluno_nome'] = $aluno['nome'];
            $_SESSION['inicial'] = mb_substr($aluno['nome'], 0, 1, 'UTF-8');

            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar · GeneEnem</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <meta name="theme-color" content="#082a1f">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">

    <!-- Tema salvo aplicado antes da página pintar (script externo). -->
    <script src="js/tema-inicial.js"></script>

    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/responsivo.css">
</head>
<body class="login-page">

<main class="login-shell">

    <!-- ============ Apresentação ============ -->
    <section class="login-art">

        <a class="brand brand-claro" href="login.php">
            <span class="brand-tile">
                <img src="img/favicon.png" alt="Símbolo do GeneEnem">
            </span>
            <span class="brand-text">
                <strong>GeneEnem</strong>
                <small>genética para o enem</small>
            </span>
        </a>

        <div class="login-conteudo">

            <div class="login-texto">
                <span class="login-selo">Web II · ENEM · Genética</span>

                <h1>Estude genética <span class="grad-claro">com clareza</span>.</h1>

                <p>
                    Revisões curtas, questões de provas anteriores e ferramentas
                    para praticar no seu ritmo.
                </p>

                <ul class="login-recursos">
                    <li>
                        <span class="login-recurso-icone" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 5.5A2.5 2.5 0 016.5 3H19v15H6.5A2.5 2.5 0 004 20.5z"/>
                                <path d="M19 18v3H6.5A2.5 2.5 0 014 18.5"/>
                            </svg>
                        </span>
                        <span><strong>6 revisões</strong> do conceito de gene ao que cai na prova</span>
                    </li>
                    <li>
                        <span class="login-recurso-icone" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 11l2.5 2.5L16 8"/>
                                <rect x="3.5" y="3.5" width="17" height="17" rx="4"/>
                            </svg>
                        </span>
                        <span><strong><?php echo $total; ?> questões</strong> de provas anteriores com correção</span>
                    </li>
                    <li>
                        <span class="login-recurso-icone" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 3.5h6M10 3.5v5.2L5.6 17a2.4 2.4 0 002.1 3.5h8.6a2.4 2.4 0 002.1-3.5L14 8.7V3.5"/>
                                <path d="M7.6 14h8.8"/>
                            </svg>
                        </span>
                        <span><strong>4 ferramentas</strong> para treinar cruzamentos e heredogramas</span>
                    </li>
                </ul>
            </div>

            <div class="login-visual" aria-hidden="true">
                <img src="img/imagemApp.png" alt="" width="300" height="300" loading="lazy">
            </div>

        </div>

        <p class="login-art-rodape">
            Desenvolvimento de Aplicações Web II · IFPB Campus Santa Luzia
        </p>

    </section>

    <!-- ============ Formulário ============ -->
    <section class="login-panel">

        <button class="tema-botao login-tema" id="tema-botao" type="button"
                aria-pressed="false" title="Ativar modo escuro" aria-label="Ativar modo escuro">
            <svg class="tema-icone tema-icone-sol" viewBox="0 0 24 24" width="19" height="19"
                 fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                <circle cx="12" cy="12" r="4.2"/>
                <path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.2 5.2l1.6 1.6M17.2 17.2l1.6 1.6M18.8 5.2l-1.6 1.6M6.8 17.2l-1.6 1.6"/>
            </svg>
            <svg class="tema-icone tema-icone-lua" viewBox="0 0 24 24" width="19" height="19"
                 fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 14.5A8.5 8.5 0 019.5 4a8.5 8.5 0 1010.5 10.5z"/>
            </svg>
        </button>

        <!-- action FIXO no nome do arquivo: nada da URL entra no formulário -->
        <form class="login-card" method="post" action="login.php">

            <span class="eyebrow">Acesso do aluno</span>

            <h2>Entrar na conta</h2>
            <p class="login-card-texto">Use seu e-mail e sua senha para continuar.</p>

            <input type="hidden" name="csrf" value="<?php echo escaparHtml($tokenFormulario); ?>">

            <?php if (isset($erros['geral'])): ?>
                <p class="aviso aviso-erro"><?php echo escaparHtml($erros['geral']); ?></p>
            <?php endif; ?>

            <div class="field <?php echo classeErro($erros, 'email'); ?>">
                <label for="email">E-mail</label>
                <div class="input-icone">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2.5"/>
                        <path d="M4 7.5l7.3 5.2a1.2 1.2 0 001.4 0L20 7.5"/>
                    </svg>
                    <input class="input" type="text" id="email" name="email"
                           placeholder="aluno@email.com" autocomplete="email"
                           value="<?php echo escaparHtml($email); ?>">
                </div>
                <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'email')); ?></span>
            </div>

            <div class="field campo-senha <?php echo classeErro($erros, 'senha'); ?>">
                <label for="senha">Senha</label>
                <div class="input-icone">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="10" rx="2.5"/>
                        <path d="M8 10V7.5a4 4 0 018 0V10M12 14v2.5"/>
                    </svg>
                    <input class="input" type="password" id="senha" name="senha"
                           placeholder="Sua senha" autocomplete="current-password">
                </div>
                <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'senha')); ?></span>

                <!-- Só aparece com o cursor no campo ou quando a senha volta com erro -->
                <p class="dica senha-dica">
                    8 caracteres ou mais, com maiúscula, minúscula, número e símbolo.
                </p>
            </div>

            <button class="btn btn-primary login-botao" type="submit">
                Entrar no GeneEnem
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                     stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h13M13 6l6 6-6 6"/>
                </svg>
            </button>

            <p class="login-card-rodape">
                Ainda não tem conta? <a href="cadastro.php">Criar conta</a><br>
                GeneEnem · Web II · IFPB Campus Santa Luzia
            </p>

        </form>

    </section>

</main>

<script src="js/tema.js"></script>

</body>
</html>
