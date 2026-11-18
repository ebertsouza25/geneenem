<?php
// Página inicial. Não tem formulário, então não há validação aqui: ela só
// apresenta o site e mostra o total de questões vindo do banco.

require_once 'dados_questoes.php';

$pagina = 'inicio';
$tituloPagina = 'Início';
$cssPagina = array('home');

require_once 'topo.php';
?>

<!-- ================= Apresentação ================= -->
<section class="hero">
    <div class="wrap hero-grid">

        <div class="hero-texto">
            <span class="eyebrow rise">Genética · ENEM</span>

            <h1 class="rise d2">
                Genética para o ENEM,
                <span class="grad">sem decoreba</span>.
            </h1>

            <p class="lead rise d3">
                Seis revisões curtas, <?php echo $total; ?> questões de provas anteriores e
                ferramentas para treinar cruzamentos, DNA e heredogramas.
            </p>

            <div class="hero-actions rise d5">
                <a class="btn btn-primary btn-lg" href="revisoes.php">
                    Começar pelas revisões
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h13M13 6l6 6-6 6"/>
                    </svg>
                </a>
                <a class="btn btn-ghost btn-lg" href="questoes.php">Resolver questões</a>
            </div>

            <ul class="hero-stats rise d6">
                <li><strong>6</strong><span>revisões objetivas</span></li>
                <li><strong><?php echo $total; ?></strong><span>questões do ENEM</span></li>
                <li><strong>4</strong><span>ferramentas</span></li>
            </ul>
        </div>

        <aside class="dna-deck rise d4" aria-label="Banco de questões do GeneEnem">
            <div class="dna-deck-topo">
                <span class="deck-label">Banco de questões</span>
                <span class="deck-chip">com gabarito</span>
            </div>

            <img class="deck-imagem" src="img/imagemApp.png" alt="Hélice de DNA"
                 width="216" height="216" loading="lazy">

            <div class="dna-deck-base">
                <p class="deck-numero">
                    <strong><?php echo $total; ?></strong>
                    <small>questões do ENEM</small>
                </p>
                <a class="deck-link" href="questoes.php">
                    Ver questões
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor"
                         stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h13M13 6l6 6-6 6"/>
                    </svg>
                </a>
            </div>
        </aside>

    </div>
</section>

<!-- ================= Trilha de estudo ================= -->
<section class="section">
    <div class="wrap">

        <header class="section-head rise">
            <span class="eyebrow">Trilha</span>
            <h2>Três passos para estudar</h2>
            <p>Na ordem que funciona: entender, praticar e testar.</p>
        </header>

        <div class="trilha">

            <article class="passo rise d1">
                <span class="passo-num">01</span>
                <h3>Aprenda o conceito</h3>
                <p>Seis revisões diretas, com os conceitos destacados e exemplos resolvidos.</p>
                <a href="revisoes.php">Abrir revisões →</a>
            </article>

            <article class="passo rise d3">
                <span class="passo-num">02</span>
                <h3>Pratique</h3>
                <p>Monte o quadro de Punnett, complemente fitas de DNA e teste heredogramas.</p>
                <a href="laboratorio.php">Ir ao laboratório →</a>
            </article>

            <article class="passo rise d5">
                <span class="passo-num">03</span>
                <h3>Teste e acompanhe</h3>
                <p>Responda as questões do ENEM e veja a correção comentada da sua nota.</p>
                <a href="questoes.php">Responder questões →</a>
            </article>

        </div>
    </div>
</section>

<!-- ================= Ferramentas ================= -->
<section class="section">
    <div class="wrap">

        <header class="section-head rise">
            <span class="eyebrow">Ferramentas</span>
            <h2>Tudo em um lugar só</h2>
        </header>

        <div class="ferramentas">

            <a class="ferramenta rise d1" href="revisoes.php">
                <span class="ferramenta-icone" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 5.5A2.5 2.5 0 016.5 3H19v15H6.5A2.5 2.5 0 004 20.5z"/>
                        <path d="M19 18v3H6.5A2.5 2.5 0 014 18.5"/>
                    </svg>
                </span>
                <h3>Revisões</h3>
                <p>Genética do básico ao que realmente cai na prova.</p>
                <span class="ferramenta-seta" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 17L17 7M9 7h8v8"/>
                    </svg>
                </span>
            </a>

            <a class="ferramenta rise d2" href="questoes.php">
                <span class="ferramenta-icone" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l2.5 2.5L16 8"/>
                        <rect x="3.5" y="3.5" width="17" height="17" rx="4"/>
                    </svg>
                </span>
                <h3>Questões</h3>
                <p><?php echo $total; ?> questões de provas anteriores com cronômetro e correção.</p>
                <span class="ferramenta-seta" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 17L17 7M9 7h8v8"/>
                    </svg>
                </span>
            </a>

            <a class="ferramenta rise d4" href="punnett.php">
                <span class="ferramenta-icone" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3.5" y="3.5" width="17" height="17" rx="3"/>
                        <path d="M12 3.5v17M3.5 12h17"/>
                    </svg>
                </span>
                <h3>Quadro de Punnett</h3>
                <p>Cruzamentos com genótipos, fenótipos e probabilidades.</p>
                <span class="ferramenta-seta" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 17L17 7M9 7h8v8"/>
                    </svg>
                </span>
            </a>

            <a class="ferramenta rise d5" href="laboratorio.php">
                <span class="ferramenta-icone" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 3.5h6M10 3.5v5.2L5.6 17a2.4 2.4 0 002.1 3.5h8.6a2.4 2.4 0 002.1-3.5L14 8.7V3.5"/>
                        <path d="M7.6 14h8.8"/>
                    </svg>
                </span>
                <h3>Laboratório</h3>
                <p>DNA interativo, mutações, heredograma e flashcards.</p>
                <span class="ferramenta-seta" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 17L17 7M9 7h8v8"/>
                    </svg>
                </span>
            </a>

            <a class="ferramenta rise d6" href="sobre.php">
                <span class="ferramenta-icone" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="8.5"/>
                        <path d="M12 11v5.5M12 7.8h.01"/>
                    </svg>
                </span>
                <h3>Sobre</h3>
                <p>Equipe, disciplina e referências do projeto.</p>
                <span class="ferramenta-seta" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 17L17 7M9 7h8v8"/>
                    </svg>
                </span>
            </a>

        </div>
    </div>
</section>

<!-- ================= Chamada final ================= -->
<section class="section">
    <div class="wrap">
        <div class="cta rise">
            <div>
                <h2>Pronto para começar?</h2>
                <p>Abra a primeira revisão e leve 10 minutos. Depois teste o que aprendeu nas questões.</p>
            </div>

            <div class="cta-acoes">
                <a class="btn btn-primary" href="revisoes.php">Abrir revisões</a>
                <a class="btn btn-ghost" href="questoes.php">Questões</a>
            </div>

            <img class="cta-helice" src="img/imagemApp.png" alt="" aria-hidden="true"
                 width="240" height="240" loading="lazy">
        </div>
    </div>
</section>

<?php require_once 'rodape.php'; ?>
