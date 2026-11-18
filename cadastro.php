<?php
// Criação da conta do aluno. Depois do cadastro a sessão já é aberta, então o
// aluno cai direto na página inicial.

require_once 'funcoes.php';
require_once 'config.php';
require_once 'Aluno.php';

iniciarSessao();

if (isset($_SESSION['aluno_id'])) {
    header('Location: index.php');
    exit;
}

$alunoObj = new Aluno($conexao);
$tokenFormulario = tokenCsrf();

$nome = '';
$email = '';
$erros = array();

$padraoNome  = "/^[A-Za-zÀ-ÿ ]{3,80}$/u";
$padraoSenha = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome  = valida_dados(isset($_POST['nome']) ? $_POST['nome'] : '');
    $email = valida_dados(isset($_POST['email']) ? $_POST['email'] : '');
    $senha = valida_dados(isset($_POST['senha']) ? $_POST['senha'] : '');
    $confirmacao = valida_dados(isset($_POST['senha_confirma']) ? $_POST['senha_confirma'] : '');

    if (empty($nome)) {
        $erros['nome'] = 'Informe o seu nome.';
    } elseif (!preg_match($padraoNome, $nome)) {
        $erros['nome'] = 'Use apenas letras e espaços (mínimo 3 letras).';
    }

    if (empty($email)) {
        $erros['email'] = 'Informe o seu e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'E-mail inválido. Exemplo: aluno@email.com';
    }

    if (empty($senha)) {
        $erros['senha'] = 'Crie uma senha.';
    } elseif (!preg_match($padraoSenha, $senha)) {
        $erros['senha'] = 'Use 8 caracteres ou mais, com maiúscula, minúscula, número e símbolo.';
    }

    if (empty($confirmacao)) {
        $erros['senha_confirma'] = 'Repita a senha.';
    } elseif ($senha !== $confirmacao) {
        $erros['senha_confirma'] = 'As duas senhas precisam ser iguais.';
    }

    if (!csrfValido(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $erros['geral'] = 'A página ficou aberta por muito tempo. Envie o formulário de novo.';
    }

    if (count($erros) === 0) {
        $alunoId = $alunoObj->cadastrar($nome, $email, $senha);

        if ($alunoId === null) {
            $erros['email'] = 'Este e-mail já está cadastrado. Entre na sua conta.';
        } else {
            session_regenerate_id(true);

            $_SESSION['aluno_id'] = $alunoId;
            $_SESSION['aluno'] = $email;
            $_SESSION['aluno_nome'] = $nome;
            $_SESSION['inicial'] = mb_substr($nome, 0, 1, 'UTF-8');

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
    <title>Criar conta · GeneEnem</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <meta name="theme-color" content="#082a1f">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">

    <script src="js/tema-inicial.js"></script>

    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/responsivo.css">
</head>
<body class="login-page">

<main class="login-shell">

    <!-- Apresentação curta, no mesmo estilo da tela de login -->
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
                <span class="login-selo">Primeiro acesso</span>

                <h1>Crie sua conta e <span class="grad-claro">acompanhe sua evolução</span>.</h1>

                <p>
                    Com a conta, seus simulados ficam salvos: acertos, erros,
                    pontuação e histórico de estudo ficam no seu perfil.
                </p>

                <ul class="login-recursos">
                    <li>
                        <span class="login-recurso-icone" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/>
                            </svg>
                        </span>
                        <span><strong>Desempenho salvo</strong> a cada simulado concluído</span>
                    </li>
                    <li>
                        <span class="login-recurso-icone" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 11.5A8.5 8.5 0 019.5 4a8.5 8.5 0 1010.5 7.5z"/>
                            </svg>
                        </span>
                        <span><strong>GeneIA</strong> para tirar dúvidas de Genética</span>
                    </li>
                </ul>
            </div>
        </div>

        <p class="login-art-rodape">
            Desenvolvimento de Aplicações Web II · IFPB Campus Santa Luzia
        </p>
    </section>

    <!-- Formulário de cadastro -->
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

        <form class="login-card" method="post" action="cadastro.php">

            <span class="eyebrow">Novo aluno</span>

            <h2>Criar conta</h2>
            <p class="login-card-texto">Leva menos de um minuto. Depois é só entrar com o mesmo e-mail.</p>

            <input type="hidden" name="csrf" value="<?php echo escaparHtml($tokenFormulario); ?>">

            <?php if (isset($erros['geral'])): ?>
                <p class="aviso aviso-erro"><?php echo escaparHtml($erros['geral']); ?></p>
            <?php endif; ?>

            <div class="field <?php echo classeErro($erros, 'nome'); ?>">
                <label for="nome">Nome</label>
                <div class="input-icone">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.5"/>
                        <path d="M5 20a7 7 0 0114 0"/>
                    </svg>
                    <input class="input" type="text" id="nome" name="nome" maxlength="80"
                           placeholder="Seu nome" autocomplete="name"
                           value="<?php echo escaparHtml($nome); ?>">
                </div>
                <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'nome')); ?></span>
            </div>

            <div class="field <?php echo classeErro($erros, 'email'); ?>">
                <label for="email">E-mail</label>
                <div class="input-icone">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2.5"/>
                        <path d="M4 7.5l7.3 5.2a1.2 1.2 0 001.4 0L20 7.5"/>
                    </svg>
                    <input class="input" type="text" id="email" name="email" maxlength="180"
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
                           placeholder="Crie uma senha" autocomplete="new-password">
                </div>
                <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'senha')); ?></span>

                <p class="dica senha-dica">
                    8 caracteres ou mais, com maiúscula, minúscula, número e símbolo.
                </p>
            </div>

            <div class="field <?php echo classeErro($erros, 'senha_confirma'); ?>">
                <label for="senha_confirma">Repetir a senha</label>
                <div class="input-icone">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="10" rx="2.5"/>
                        <path d="M8 10V7.5a4 4 0 018 0V10M12 14v2.5"/>
                    </svg>
                    <input class="input" type="password" id="senha_confirma" name="senha_confirma"
                           placeholder="Digite a senha de novo" autocomplete="new-password">
                </div>
                <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'senha_confirma')); ?></span>
            </div>

            <button class="btn btn-primary login-botao" type="submit">Criar minha conta</button>

            <p class="login-card-rodape">
                Já tem conta? <a href="login.php">Entrar</a><br>
                GeneEnem · Web II · IFPB Campus Santa Luzia
            </p>

        </form>
    </section>

</main>

<script src="js/tema.js"></script>

</body>
</html>
