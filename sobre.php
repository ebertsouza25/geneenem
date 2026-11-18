<?php
// sobre.php — informações do projeto, da equipe e das referências.
// Página estática: não recebe formulário, então não tem validação.

$pagina = 'sobre';
$tituloPagina = 'Sobre';
$cssPagina = array('sobre');

require_once 'topo.php';
?>

<section class="page-head">
    <div class="wrap">
        <span class="eyebrow">Sobre · Projeto escolar</span>
        <h1>O que é o GeneEnem</h1>
        <p class="lead">
            Uma plataforma de estudo de Genética para o ENEM, feita para a disciplina
            Desenvolvimento de Aplicações Web II do IFPB Campus Santa Luzia.
        </p>
    </div>
</section>

<section class="section sobre-secao">
    <div class="wrap sobre-grid">

        <article class="sobre-card">
            <h2>O objetivo</h2>
            <p>
                Reunir o conteúdo de Genética que cai no ENEM em um lugar só: revisões
                curtas, questões de provas anteriores com correção comentada e
                ferramentas para praticar.
            </p>
            <p class="muted">
                O foco é estudar sem distração: cada página tem uma função e o aluno
                chega ao conteúdo em poucos cliques.
            </p>
        </article>

        <article class="sobre-card">
            <h2>A disciplina</h2>
            <ul class="lista-info">
                <li><span>Curso</span><strong>Desenvolvimento de Aplicações Web II</strong></li>
                <li><span>Professor</span><strong>Felipe Ramos</strong></li>
                <li><span>Instituição</span><strong>IFPB · Campus Santa Luzia</strong></li>
                <li><span>Tecnologias</span><strong>PHP, MySQL, HTML, CSS e JavaScript</strong></li>
                <li><span>Assistente</span><strong>GeneIA · API da OpenAI</strong></li>
            </ul>
        </article>

        <article class="sobre-card sobre-largo">
            <h2>Equipe</h2>

            <div class="equipe">
                <article class="pessoa">
                    <div class="pessoa-foto">
                        <img src="img/equipe/ebert.jpeg" alt="Foto de Ebert Souza" loading="lazy">
                    </div>
                    <h3>Ebert Souza</h3>
                    <p class="pessoa-cargo">Desenvolvedor</p>
                </article>

                <article class="pessoa">
                    <div class="pessoa-foto">
                        <img src="img/equipe/kaio.jpeg" alt="Foto de Kaio Dantas" loading="lazy">
                    </div>
                    <h3>Kaio Dantas</h3>
                    <p class="pessoa-cargo">Desenvolvedor</p>
                </article>

                <article class="pessoa">
                    <div class="pessoa-foto">
                        <img src="img/equipe/alexandre-brito.jpg" alt="Foto de Alexandre Brito" loading="lazy">
                    </div>
                    <h3>Alexandre Brito</h3>
                    <p class="pessoa-cargo">Desenvolvedor</p>
                </article>

                <article class="pessoa">
                    <div class="pessoa-foto">
                        <img src="img/equipe/guilherme-emanuel.jpg" alt="Foto de Guilherme Emanuel" loading="lazy">
                    </div>
                    <h3>Guilherme Emanuel</h3>
                    <p class="pessoa-cargo">Desenvolvedor</p>
                </article>

                <article class="pessoa">
                    <div class="pessoa-foto">
                        <img src="img/equipe/moises-medeiros.jpg" alt="Foto de Moises Medeiros" loading="lazy">
                    </div>
                    <h3>Moises Medeiros</h3>
                    <p class="pessoa-cargo">Desenvolvedor</p>
                </article>

                <article class="pessoa pessoa-avaliador">
                    <div class="pessoa-foto">
                        <img src="img/equipe/felipe.jpeg" alt="Foto de Felipe Ramos" loading="lazy">
                    </div>
                    <h3>Felipe Ramos</h3>
                    <p class="pessoa-cargo">Avaliador</p>
                </article>
            </div>
        </article>

        <article class="sobre-card sobre-largo">
            <h2>O que você pode explorar no GeneEnem</h2>

            <div class="checks">
                <a class="check check-link" href="revisoes.php">
                    <span class="check-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 5.5A2.5 2.5 0 016.5 3H19v15H6.5A2.5 2.5 0 004 20.5z"/>
                            <path d="M19 18v3H6.5A2.5 2.5 0 014 18.5"/>
                        </svg>
                    </span>
                    <strong>Conteúdos de Genética</strong>
                    <span>Conceitos, processos biológicos e os assuntos que mais caem no ENEM.</span>
                </a>

                <a class="check check-link" href="questoes.php">
                    <span class="check-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 11l2.5 2.5L16 8"/>
                            <rect x="3.5" y="3.5" width="17" height="17" rx="4"/>
                        </svg>
                    </span>
                    <strong>Questões do ENEM</strong>
                    <span>Questões de provas anteriores, com ano, número original e correção comentada.</span>
                </a>

                <a class="check check-link" href="revisoes.php#videoaulas">
                    <span class="check-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 5.5v13l11-6.5z"/>
                        </svg>
                    </span>
                    <strong>Videoaulas</strong>
                    <span>Revisões em vídeo sobre os principais assuntos de Genética.</span>
                </a>

                <a class="check check-link" href="laboratorio.php#flashcards">
                    <span class="check-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="6" width="13" height="14" rx="2.5"/>
                            <path d="M8 3h10a3 3 0 013 3v10"/>
                        </svg>
                    </span>
                    <strong>Flashcards</strong>
                    <span>Revise conceitos e teste a memória com cartões interativos.</span>
                </a>

                <a class="check check-link" href="punnett.php">
                    <span class="check-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3.5" y="3.5" width="17" height="17" rx="3"/>
                            <path d="M12 3.5v17M3.5 12h17"/>
                        </svg>
                    </span>
                    <strong>Quadro de Punnett</strong>
                    <span>Simule cruzamentos e veja as probabilidades de genótipos e fenótipos.</span>
                </a>

                <a class="check check-link" href="laboratorio.php#heredograma">
                    <span class="check-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="3" width="6" height="6" rx="1.5"/>
                            <rect x="3" y="15" width="6" height="6" rx="1.5"/>
                            <rect x="15" y="15" width="6" height="6" rx="1.5"/>
                            <path d="M12 9v3M6 15v-3h12v3"/>
                        </svg>
                    </span>
                    <strong>Heredogramas</strong>
                    <span>Interprete padrões de herança genética em árvores genealógicas.</span>
                </a>

                <a class="check check-link" href="laboratorio.php#dna">
                    <span class="check-icone" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 3c0 6 10 6 10 12M17 3c0 6-10 6-10 12M7 21c0-2 2-3 5-3s5 1 5 3"/>
                        </svg>
                    </span>
                    <strong>DNA interativo</strong>
                    <span>Explore o pareamento de bases, a transcrição e as mutações.</span>
                </a>
            </div>
        </article>

        <article class="sobre-card sobre-largo">
            <h2>Referências</h2>
            <ul class="lista-refs">
                <li>INEP. <em>Provas e gabaritos do ENEM</em> (edições usadas no banco de questões).</li>
                <li>BORÉM, A.; SANTOS, F. R. <em>Entendendo a biotecnologia</em>. Viçosa: UFV.</li>
                <li>GRIFFITHS, A. J. F. et al. <em>Introdução à Genética</em>. Rio de Janeiro: Guanabara Koogan.</li>
            </ul>
        </article>

    </div>
</section>

<?php require_once 'rodape.php'; ?>
