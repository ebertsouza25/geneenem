<?php
// Cabeçalho comum das páginas internas. Antes de incluir, a página define
// $pagina, $tituloPagina e, se precisar, $cssPagina e $jsPagina.

require_once 'funcoes.php';

iniciarSessao();

// Quem não está autenticado volta para o login antes de ver qualquer HTML.
if (!isset($_SESSION['aluno_id'])) {
    header('Location: login.php');
    exit;
}

$pagina = isset($pagina) ? $pagina : '';
$tituloPagina = isset($tituloPagina) ? $tituloPagina : 'GeneEnem';
$cssPagina = isset($cssPagina) ? $cssPagina : array();
$jsPagina = isset($jsPagina) ? $jsPagina : array();

// Nome mostrado no cabeçalho. Sessões antigas (antes do banco) só tinham o
// e-mail, por isso o fallback.
$nomeAluno = isset($_SESSION['aluno_nome']) ? $_SESSION['aluno_nome'] : $_SESSION['aluno'];

// Itens do menu principal: chave => [rótulo, arquivo].
// A chave é comparada com $pagina para marcar o item atual.
$menu = array(
    'inicio'       => array('Início', 'index.php'),
    'revisoes'     => array('Revisões', 'revisoes.php'),
    'questoes'     => array('Questões', 'questoes.php'),
    'punnett'      => array('Punnett', 'punnett.php'),
    'laboratorio'  => array('Laboratório', 'laboratorio.php'),
    'perfil'       => array('Perfil', 'perfil.php'),
    'sobre'        => array('Sobre', 'sobre.php')
);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo escaparHtml($tituloPagina); ?> · GeneEnem</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <meta name="theme-color" content="#082a1f">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">

    <!-- Tema salvo aplicado antes de a página pintar (script externo) -->
    <script src="js/tema-inicial.js"></script>

    <link rel="stylesheet" href="css/base.css">
<?php foreach ($cssPagina as $arquivoCss): ?>
    <link rel="stylesheet" href="css/<?php echo escaparHtml($arquivoCss); ?>.css">
<?php endforeach; ?>
    <link rel="stylesheet" href="css/geneia.css">
    <link rel="stylesheet" href="css/responsivo.css">
</head>
<body class="pag-<?php echo escaparHtml($pagina); ?>">

<a class="pular" href="#conteudo">Ir para o conteúdo</a>

<header class="site-header" id="site-header">
    <div class="wrap header-inner">

        <a class="brand" href="index.php">
            <span class="brand-tile">
                <img src="img/favicon.png" alt="Símbolo do GeneEnem">
            </span>
            <span class="brand-text">
                <strong>GeneEnem</strong>
                <small>genética para o enem</small>
            </span>
        </a>

        <button class="nav-toggle" id="nav-toggle" type="button"
                aria-expanded="false" aria-controls="site-nav" aria-label="Abrir menu">
            <svg class="ico-menu" viewBox="0 0 24 24" width="19" height="19" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
            <svg class="ico-x" viewBox="0 0 24 24" width="19" height="19" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>

        <nav class="site-nav" id="site-nav" aria-label="Navegação principal">
<?php foreach ($menu as $chave => $item): ?>
            <a href="<?php echo escaparHtml($item[1]); ?>"
               <?php echo $pagina === $chave ? 'aria-current="page"' : ''; ?>><?php echo escaparHtml($item[0]); ?></a>
<?php endforeach; ?>
        </nav>

        <div class="header-tools">

            <!-- Botão de tema (padrão do Serpenteando): sol no escuro, lua no claro -->
            <button class="tema-botao" id="tema-botao" type="button"
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

            <a class="user" href="perfil.php" title="Ver meu perfil">
                <span class="user-avatar"><?php echo escaparHtml($_SESSION['inicial']); ?></span>
                <span class="user-info">
                    <strong>Meu perfil</strong>
                    <span><?php echo escaparHtml($nomeAluno); ?></span>
                </span>
            </a>

            <a class="icon-btn" href="logout.php" title="Sair da conta" aria-label="Sair da conta">
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3M10 8l-4 4 4 4M6 12h9"/>
                </svg>
            </a>

        </div>

    </div>
</header>

<?php require_once 'geneia_widget.php'; ?>

<main id="conteudo">
