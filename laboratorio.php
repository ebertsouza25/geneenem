<?php
// Laboratório: DNA interativo (processado no PHP), heredograma e flashcards
// (os dois últimos funcionam no navegador, com js/heredograma.js e
// js/laboratorio.js).

require_once 'funcoes.php';

$pagina = 'laboratorio';
$tituloPagina = 'Laboratório';
$cssPagina = array('ferramentas');
$jsPagina = array('laboratorio', 'heredograma');

$errosDna = array();
$sequencia = '';
$dna = null;

// Padrão da fita: só as quatro bases, em maiúscula, de 3 a 60 letras.
// Exigir a maiúscula evita ter que converter o texto depois: o que passou na
// validação é exatamente o que será analisado.
$padraoSequenciaDna = "/^[ATCG]{3,60}$/";

// A ferramenta de DNA envia o formulário para esta mesma página. A ação
// "dna" evita que um envio qualquer entre nesse processamento.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'dna') {

    $sequencia = valida_dados(isset($_POST['sequencia']) ? $_POST['sequencia'] : '');

    if (empty($sequencia)) {
        $errosDna['sequencia'] = 'Digite a sequência de DNA.';
    } elseif (!preg_match($padraoSequenciaDna, $sequencia)) {
        $errosDna['sequencia'] = 'Use apenas A, T, C e G em maiúsculas (de 3 a 60 bases).';
    } else {
        $pares = array();
        $quantidadeBases = strlen($sequencia);
        $quantidadeGuanina = 0;
        $quantidadeCitosina = 0;

        // Percorre a fita base por base guardando a posição e o pareamento.
        for ($indiceBase = 0; $indiceBase < $quantidadeBases; $indiceBase++) {
            $base = $sequencia[$indiceBase];

            $pares[] = array(
                'posicao' => $indiceBase + 1,
                'base' => $base,
                'complemento' => baseComplementar($base)
            );

            // Conta G e C para calcular o conteúdo GC no fim.
            if ($base === 'G') {
                $quantidadeGuanina++;
            }

            if ($base === 'C') {
                $quantidadeCitosina++;
            }
        }

        // Conteúdo GC = (G + C) / total de bases × 100.
        // Ex.: ATGC tem 1 G e 1 C em 4 bases -> (1+1)*100/4 = 50%.
        $dna = array(
            'sequencia' => $sequencia,
            'pares' => $pares,
            'complementar' => fitaComplementar($sequencia),
            'rna' => transcreverRna($sequencia),
            'gc' => arredondar(($quantidadeGuanina + $quantidadeCitosina) * 100 / $quantidadeBases),
            'tamanho' => $quantidadeBases
        );
    }
}

require_once 'topo.php';
?>

<section class="page-head">
    <div class="wrap">
        <span class="eyebrow">Laboratório · 2 ferramentas</span>
        <h1>Veja a genética acontecer</h1>
        <p class="lead">
            Complemente fitas de DNA, monte heredogramas e revise com
            flashcards.
        </p>

        <nav class="tool-nav" aria-label="Ferramentas do laboratório">
            <a href="#dna">DNA interativo</a>
            <a href="#heredograma">Heredograma</a>
            <a href="#flashcards">Flashcards</a>
        </nav>
    </div>
</section>

<section class="section tool-secao">
    <div class="wrap">

        <!-- ============================ DNA ============================ -->
        <section class="tool" id="dna">
            <h2>DNA interativo</h2>
            <p>Digite uma fita molde e veja o pareamento, a transcrição e o RNA mensageiro.</p>

            <form method="post" action="laboratorio.php" class="tool-grade">
                <input type="hidden" name="acao" value="dna">

                <div class="field <?php echo classeErro($errosDna, 'sequencia'); ?>">
                    <label for="sequencia">Fita molde (3' → 5')</label>
                    <input class="input" type="text" id="sequencia" name="sequencia"
                           placeholder="Ex: ATGC" maxlength="60" value="<?php echo escaparHtml($sequencia); ?>">
                    <span class="erro"><?php echo escaparHtml(mensagemErro($errosDna, 'sequencia')); ?></span>
                    <p class="dica">Use de 3 a 60 bases, apenas A, T, C e G (em maiúsculas).</p>
                </div>

                <button class="btn btn-primary" type="submit">Analisar fita</button>
            </form>

            <?php if ($dna !== null): ?>
                <div class="dna-resultado">

                    <div class="fita-linha">
                        <span class="fita-rotulo">Molde <span class="fita-direcao">3' → 5'</span></span>
                        <div class="fita">
                            <?php for ($indiceBase = 0; $indiceBase < strlen($dna['sequencia']); $indiceBase++): ?>
                                <?php $base = $dna['sequencia'][$indiceBase]; ?>
                                <span class="base <?php echo classeDaBase($base); ?>"><?php echo escaparHtml($base); ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="fita-linha">
                        <span class="fita-rotulo">Complementar <span class="fita-direcao">5' → 3'</span></span>
                        <div class="fita">
                            <?php for ($indiceBase = 0; $indiceBase < strlen($dna['complementar']); $indiceBase++): ?>
                                <?php $base = $dna['complementar'][$indiceBase]; ?>
                                <span class="base <?php echo classeDaBase($base); ?>"><?php echo escaparHtml($base); ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="fita-linha">
                        <span class="fita-rotulo">RNA mensageiro <span class="fita-direcao">5' → 3'</span></span>
                        <div class="fita">
                            <?php for ($indiceBase = 0; $indiceBase < strlen($dna['rna']); $indiceBase++): ?>
                                <?php $base = $dna['rna'][$indiceBase]; ?>
                                <span class="base <?php echo classeDaBase($base); ?>"><?php echo escaparHtml($base); ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Nota sobre antiparalelismo: isso é texto fixo, não depende da
                         sequência digitada, então não precisa de escaparHtml() nem de PHP. -->
                    <p class="dica">
                        As duas fitas de DNA são <strong>antiparalelas</strong>: a fita molde é
                        lida pela RNA polimerase de 3' para 5', enquanto a fita complementar e o
                        RNA mensageiro crescem no sentido contrário, de 5' para 3'.
                    </p>

                    <div class="mini-dados">
                        <span><strong><?php echo $dna['tamanho']; ?></strong> bases</span>
                        <span><strong><?php echo $dna['gc']; ?>%</strong> de conteúdo GC</span>
                        <span>Pareamento <strong>A–T</strong> e <strong>C–G</strong></span>
                        <span>No RNA, <strong>T</strong> vira <strong>U</strong></span>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- ======================= HEREDOGRAMA ======================= -->
        <!-- Esta ferramenta é 100% JavaScript (js/heredograma.js). O PHP só
             entrega os elementos vazios abaixo; quem desenha a árvore, monta
             a lista de pessoas e faz a análise é o script. -->
        <section class="tool tool-heredograma" id="heredograma">
            <h2>Heredograma</h2>
            <p>
                Monte a árvore genealógica: adicione pessoas, escolha o sexo biológico,
                marque quem é afetado pela característica e ligue cada um aos pais.
            </p>

            <form class="tool-grade" id="hg-form">
                <div class="field">
                    <label for="hg-nome">Nome (opcional)</label>
                    <input class="input" type="text" id="hg-nome" maxlength="30" placeholder="Ex: Ana">
                </div>

                <div class="field">
                    <label for="hg-sexo">Sexo biológico</label>
                    <select class="select" id="hg-sexo">
                        <option value="F">Feminino</option>
                        <option value="M">Masculino</option>
                    </select>
                </div>

                <div class="field">
                    <label for="hg-pai">Pai</label>
                    <select class="select" id="hg-pai">
                        <option value="">Sem pai na árvore</option>
                    </select>
                </div>

                <div class="field">
                    <label for="hg-mae">Mãe</label>
                    <select class="select" id="hg-mae">
                        <option value="">Sem mãe na árvore</option>
                    </select>
                </div>

                <div class="field campo-checkbox">
                    <label class="checkbox-linha">
                        <input type="checkbox" id="hg-afetado">
                        Afetado(a) pela característica estudada
                    </label>
                </div>
            </form>

            <div class="hg-acoes">
                <button class="btn btn-primary" type="button" id="hg-adicionar">Adicionar pessoa</button>
                <button class="btn btn-ghost" type="button" id="hg-exemplo">Carregar exemplo</button>
                <button class="btn btn-ghost" type="button" id="hg-limpar">Limpar tudo</button>
            </div>
            <p class="erro" id="hg-erro-form"></p>

            <!-- A árvore em si (SVG) é desenhada pelo JS aqui dentro -->
            <div class="heredograma-arvore" id="heredograma-arvore"
                 role="img" aria-label="Árvore genealógica montada até agora"></div>

            <div class="heredograma-legenda">
                <span><i class="hg-leg hg-leg-m"></i> Homem não afetado</span>
                <span><i class="hg-leg hg-leg-m hg-leg-afetado"></i> Homem afetado</span>
                <span><i class="hg-leg hg-leg-f"></i> Mulher não afetada</span>
                <span><i class="hg-leg hg-leg-f hg-leg-afetado"></i> Mulher afetada</span>
            </div>

            <!-- Lista por texto: além de ajudar quem usa leitor de tela, deixa
                 claro quem é filho de quem sem precisar interpretar o desenho. -->
            <ul class="heredograma-lista" id="heredograma-lista"></ul>

            <div class="heredograma-analise">
                <h3 class="sub-titulo">Analisar padrão de herança</h3>
                <p class="muted pequeno">
                    O teste confere se a árvore É COMPATÍVEL com o padrão escolhido —
                    ele não inventa genótipo de ninguém, só aponta quando alguém na árvore
                    contraria as regras daquele tipo de herança.
                </p>

                <div class="tool-grade">
                    <div class="field">
                        <label for="hg-padrao">Tipo de herança para testar</label>
                        <select class="select" id="hg-padrao">
                            <option value="ad">Autossômica dominante</option>
                            <option value="ar">Autossômica recessiva</option>
                            <option value="xld">Ligada ao X dominante</option>
                            <option value="xlr">Ligada ao X recessiva</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="button" id="hg-analisar">Verificar compatibilidade</button>
                </div>

                <div id="hg-resultado-analise" aria-live="polite"></div>
            </div>
        </section>

        <!-- ======================= FLASHCARDS ======================== -->
        <section class="tool" id="flashcards">
            <h2>Flashcards</h2>
            <p>Cada cartão mostra a pergunta e, ao clicar, a resposta aparece logo abaixo. Clique de novo para recolher.</p>

            <div class="flashcards" id="flashcards-deck">

                <button class="flashcard" type="button" aria-expanded="false">
                    <span class="fc-topo">DNA</span>
                    <span class="fc-pergunta">Como as bases se pareiam?</span>
                    <span class="fc-resposta">No DNA, a adenina (A) se liga à timina (T), e a citosina (C) se liga à guanina (G). No RNA, a uracila (U) substitui a timina.</span>
                </button>

                <button class="flashcard" type="button" aria-expanded="false">
                    <span class="fc-topo">DNA</span>
                    <span class="fc-pergunta">O que é o pareamento de bases?</span>
                    <span class="fc-resposta">É a união complementar entre as duas fitas do DNA: A com T e C com G, ligadas por pontes de hidrogênio. É isso que permite a molécula se copiar e transmitir a informação genética.</span>
                </button>

                <button class="flashcard" type="button" aria-expanded="false">
                    <span class="fc-topo">Genes</span>
                    <span class="fc-pergunta">O que é genótipo?</span>
                    <span class="fc-resposta">É a constituição genética de um indivíduo, representada pelos alelos que ele possui, como AA, Aa ou aa para determinada característica.</span>
                </button>

                <button class="flashcard" type="button" aria-expanded="false">
                    <span class="fc-topo">Genes</span>
                    <span class="fc-pergunta">O que é fenótipo?</span>
                    <span class="fc-resposta">É o conjunto de características observáveis de um indivíduo, resultante da interação entre seu genótipo e o ambiente.</span>
                </button>

                <button class="flashcard" type="button" aria-expanded="false">
                    <span class="fc-topo">Mendel</span>
                    <span class="fc-pergunta">Qual é a proporção genotípica e fenotípica do cruzamento Aa × Aa?</span>
                    <span class="fc-resposta">Genótipos: 25% AA, 50% Aa e 25% aa, na proporção 1:2:1. Considerando dominância completa, a proporção fenotípica é 3 dominantes para 1 recessivo (3:1).</span>
                </button>

                <button class="flashcard" type="button" aria-expanded="false">
                    <span class="fc-topo">Sexo</span>
                    <span class="fc-pergunta">Como os pais determinam o sexo cromossômico dos filhos?</span>
                    <span class="fc-resposta">A mãe fornece um cromossomo X. O pai fornece X ou Y. Quando o espermatozoide fornece X, geralmente resulta em XX; quando fornece Y, geralmente resulta em XY. Assim, o pai determina qual cromossomo sexual será fornecido, no sistema XX/XY.</span>
                </button>

                <button class="flashcard" type="button" aria-expanded="false">
                    <span class="fc-topo">Mutações</span>
                    <span class="fc-pergunta">O que é mutação silenciosa?</span>
                    <span class="fc-resposta">É uma alteração na sequência de DNA que não modifica o aminoácido produzido, devido à redundância do código genético.</span>
                </button>

            </div>
        </section>

    </div>
</section>

<?php require_once 'rodape.php'; ?>
