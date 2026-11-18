<?php
// Conteúdo de Genética em seis revisões. Página só de leitura: cada revisão é
// um <details>, então o aluno abre apenas o assunto que quer estudar.

$pagina = 'revisoes';
$tituloPagina = 'Revisões';
$cssPagina = array('revisoes');

require_once 'topo.php';
?>

<section class="page-head">
    <div class="wrap">
        <span class="eyebrow">Revisões · 6 etapas</span>
        <h1>Genética do começo ao que cai na prova</h1>
        <p class="lead">
            Cada etapa tem a explicação curta, os conceitos que precisam ficar na
            cabeça e um exemplo resolvido.
        </p>

        <nav class="revisoes-indice" aria-label="Ir para uma revisão">
            <a href="#revisao-1">1. Genética</a>
            <a href="#revisao-2">2. DNA</a>
            <a href="#revisao-3">3. Alelos</a>
            <a href="#revisao-4">4. Punnett</a>
            <a href="#revisao-5">5. Ligada ao X</a>
            <a href="#revisao-6">6. ENEM</a>
        </nav>
    </div>
</section>

<!-- Videoaulas integradas ao estudo: ficam no mesmo ambiente das revisões,
     antes da lista de conteúdos, para o aluno revisar assistindo. -->
<section class="section revisoes-videos" id="videoaulas">
    <div class="wrap">

        <header class="revisoes-videos-topo">
            <span class="eyebrow">Videoaulas</span>
            <h2>Assista antes de praticar</h2>
            <p>Dois vídeos do projeto antigo, que continuam no ar no YouTube.</p>
        </header>

        <div class="video-grade">

            <article class="video-card">
                <div class="video-moldura">
                    <iframe
                        src="https://www.youtube.com/embed/YjwYJHqxwFY"
                        title="Resumo de Genética: principais conceitos"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen></iframe>
                </div>

                <div class="video-info">
                    <span class="chip">Genética básica</span>
                    <h3>Resumo de Genética: principais conceitos</h3>
                    <p>Revisão dos conceitos que mais aparecem nas provas: DNA, genes, cromossomos e hereditariedade.</p>
                    <p class="video-canal">Proenem - Enem 2026 · Prof. Adilson Teixeira</p>
                    <a class="video-link" href="https://www.youtube.com/watch?v=YjwYJHqxwFY"
                       target="_blank" rel="noopener">Assistir no YouTube</a>
                </div>
            </article>

            <article class="video-card">
                <div class="video-moldura">
                    <iframe
                        src="https://www.youtube.com/embed/tUtj4HIg4Wo"
                        title="Introdução à Genética"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen></iframe>
                </div>

                <div class="video-info">
                    <span class="chip">Introdução</span>
                    <h3>Introdução à Genética</h3>
                    <p>Vídeo de introdução, com a base necessária para entender os cruzamentos e as leis de Mendel.</p>
                    <p class="video-canal">Biologia com Samuel Cunha · Samuel Cunha</p>
                    <a class="video-link" href="https://www.youtube.com/watch?v=tUtj4HIg4Wo"
                       target="_blank" rel="noopener">Assistir no YouTube</a>
                </div>
            </article>

        </div>
    </div>
</section>

<section class="section revisoes-secao">
    <div class="wrap revisoes-lista">

        <!-- 1 -->
        <details class="revisao" id="revisao-1" open>
            <summary>
                <span class="revisao-num">1</span>
                <span class="revisao-titulo">
                    <strong>O que é genética</strong>
                    <span>Hereditariedade e informação genética</span>
                </span>
                <svg class="revisao-seta" viewBox="0 0 24 24" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </summary>

            <div class="revisao-corpo">
                <p>
                    Genética é a parte da Biologia que estuda como as características
                    são formadas e passadas de uma geração para outra. Ela explica por
                    que filhos se parecem com os pais e como as informações ficam
                    guardadas nas células.
                </p>

                <ul class="conceitos">
                    <li><strong>DNA</strong> — molécula que guarda a informação.</li>
                    <li><strong>Gene</strong> — trecho do DNA ligado a uma característica.</li>
                    <li><strong>Cromossomo</strong> — onde o DNA fica organizado.</li>
                    <li><strong>Hereditariedade</strong> — transmissão aos descendentes.</li>
                </ul>

                <p class="exemplo">
                    <strong>Para visualizar:</strong> o DNA é o manual, os genes são os
                    trechos do manual e os cromossomos são os volumes onde ele fica.
                </p>
            </div>
        </details>

        <!-- 2 -->
        <details class="revisao" id="revisao-2">
            <summary>
                <span class="revisao-num">2</span>
                <span class="revisao-titulo">
                    <strong>DNA, genes e cromossomos</strong>
                    <span>Pareamento de bases e transcrição</span>
                </span>
                <svg class="revisao-seta" viewBox="0 0 24 24" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </summary>

            <div class="revisao-corpo">
                <p>
                    O DNA tem duas fitas enroladas em hélice, formadas por bases
                    nitrogenadas: adenina (A), timina (T), citosina (C) e guanina (G).
                    O pareamento é fixo: <strong>A com T</strong> e <strong>C com G</strong>.
                </p>

                <ul class="conceitos">
                    <li><strong>Fita molde:</strong> ATGC</li>
                    <li><strong>Complementar:</strong> TACG</li>
                    <li><strong>RNA mensageiro:</strong> UACG (a timina vira uracila)</li>
                    <li><strong>Tradução:</strong> o RNA é lido em códons e vira proteína.</li>
                </ul>

                <p class="exemplo">
                    <strong>Exemplo:</strong> a fita ATGC origina TACG na fita
                    complementar e UACG no RNA mensageiro. Teste outras sequências no
                    <a href="laboratorio.php#dna">laboratório de DNA</a>.
                </p>
            </div>
        </details>

        <!-- 3 -->
        <details class="revisao" id="revisao-3">
            <summary>
                <span class="revisao-num">3</span>
                <span class="revisao-titulo">
                    <strong>Alelos e hereditariedade</strong>
                    <span>Genótipo, fenótipo e dominância</span>
                </span>
                <svg class="revisao-seta" viewBox="0 0 24 24" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </summary>

            <div class="revisao-corpo">
                <p>
                    Cada característica é definida por um par de alelos: um vem do pai e
                    outro da mãe. A combinação é o <strong>genótipo</strong>; o que
                    aparece no corpo é o <strong>fenótipo</strong>.
                </p>

                <ul class="conceitos">
                    <li><strong>AA</strong> — homozigoto dominante (fenótipo dominante).</li>
                    <li><strong>Aa</strong> — heterozigoto (fenótipo dominante, portador).</li>
                    <li><strong>aa</strong> — homozigoto recessivo (fenótipo recessivo).</li>
                </ul>

                <p class="exemplo">
                    <strong>Regra prática:</strong> para o alelo dominante aparecer, basta
                    uma letra maiúscula. O recessivo só aparece em dose dupla (aa).
                </p>
            </div>
        </details>

        <!-- 4 -->
        <details class="revisao" id="revisao-4">
            <summary>
                <span class="revisao-num">4</span>
                <span class="revisao-titulo">
                    <strong>Cruzamentos e probabilidade</strong>
                    <span>Quadro de Punnett e proporção 3:1</span>
                </span>
                <svg class="revisao-seta" viewBox="0 0 24 24" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </summary>

            <div class="revisao-corpo">
                <p>
                    Para prever os filhos, separamos os gametas de cada pai e combinamos
                    todos os pares possíveis. É isso que o quadro de Punnett faz.
                </p>

                <ul class="conceitos">
                    <li><strong>Gametas de Aa:</strong> A e a.</li>
                    <li><strong>Cruzamento Aa × Aa:</strong> AA, Aa, Aa, aa.</li>
                    <li><strong>Genótipos:</strong> 1 AA : 2 Aa : 1 aa.</li>
                    <li><strong>Fenótipos:</strong> 3 dominantes : 1 recessivo (75% e 25%).</li>
                </ul>

                <p class="exemplo">
                    <strong>Treine:</strong> monte outros cruzamentos na
                    <a href="punnett.php">calculadora de Punnett</a> e compare as
                    proporções.
                </p>
            </div>
        </details>

        <!-- 5 -->
        <details class="revisao" id="revisao-5">
            <summary>
                <span class="revisao-num">5</span>
                <span class="revisao-titulo">
                    <strong>Herança ligada ao sexo</strong>
                    <span>Por que o daltonismo é mais comum em homens</span>
                </span>
                <svg class="revisao-seta" viewBox="0 0 24 24" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </summary>

            <div class="revisao-corpo">
                <p>
                    Na herança ligada ao X, o gene fica no cromossomo X. A mulher tem dois
                    X e o homem tem apenas um, por isso um único alelo recessivo já
                    manifesta a característica no homem.
                </p>

                <ul class="conceitos">
                    <li><strong>XAY</strong> — homem não afetado.</li>
                    <li><strong>XaY</strong> — homem afetado.</li>
                    <li><strong>XAXA</strong> — mulher não afetada.</li>
                    <li><strong>XAXa</strong> — mulher portadora (não manifesta).</li>
                    <li><strong>XaXa</strong> — mulher afetada.</li>
                </ul>

                <p class="exemplo">
                    <strong>Exemplo:</strong> pai XAY e mãe XAXa podem ter filhas XAXA ou
                    XAXa e filhos XAY ou XaY. Teste no
                    <a href="laboratorio.php#heredograma">heredograma</a>.
                </p>
            </div>
        </details>

        <!-- 6 -->
        <details class="revisao" id="revisao-6">
            <summary>
                <span class="revisao-num">6</span>
                <span class="revisao-titulo">
                    <strong>O que cai no ENEM</strong>
                    <span>Como a genética aparece na prova</span>
                </span>
                <svg class="revisao-seta" viewBox="0 0 24 24" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </summary>

            <div class="revisao-corpo">
                <p>
                    O ENEM raramente pede uma definição solta. Ele apresenta uma situação
                    real — vacina de RNA, transgênico, exame de DNA, heredograma — e pede
                    interpretação.
                </p>

                <ul class="conceitos">
                    <li><strong>Leia o comando</strong> antes das alternativas.</li>
                    <li><strong>Procure o mecanismo</strong> (transcrição, herança, mutação).</li>
                    <li><strong>Elimine o improvável:</strong> afirmações absolutas costumam errar.</li>
                </ul>

                <div class="topicos">
                    <span>DNA e RNA</span>
                    <span>Heredogramas</span>
                    <span>Biotecnologia</span>
                    <span>Mutações</span>
                    <span>Terapia gênica</span>
                    <span>Sistema ABO</span>
                </div>

                <p class="exemplo">
                    <strong>Próximo passo:</strong> responda as
                    <a href="questoes.php">questões do ENEM</a> e confira a correção.
                </p>
            </div>
        </details>

    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="cta-revisoes">
            <div>
                <h2>Revisou? Agora pratique.</h2>
                <p>Use o laboratório para ver o DNA funcionando e depois teste nas questões.</p>
            </div>
            <div class="cta-revisoes-acoes">
                <a class="btn btn-primary" href="laboratorio.php">Laboratório</a>
                <a class="btn btn-ghost" href="questoes.php">Questões</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'rodape.php'; ?>
