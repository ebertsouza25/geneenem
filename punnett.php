<?php
// Calculadora do quadro de Punnett: recebe os dados do cruzamento, valida
// campo por campo e só calcula quando tudo está correto.

require_once 'funcoes.php';

$pagina = 'punnett';
$tituloPagina = 'Quadro de Punnett';
$cssPagina = array('ferramentas');

// Padrões e filtros em variáveis com nome, para não deixar expressão regular
// solta no meio das condições.
$padraoTexto           = "/^[A-Za-zÀ-ÿ ]{3,60}$/u";         // letras e espaços
$padraoGenotipo        = "/^[A-Za-z]{2}$/";                 // exatamente 2 letras
$filtroNumeroInteiro   = FILTER_VALIDATE_INT;

// Começam vazias para a página abrir limpa; depois do POST guardam o que foi
// digitado, inclusive quando há erro.
$caracteristica = '';
$genotipo1 = '';
$genotipo2 = '';
$fenotipoDom = '';
$fenotipoRec = '';
$descendentes = '';

$erros = array();
$cruzamento = null;

// Todo o processamento começa pelo envio do formulário; sem POST, a página
// apenas mostra o formulário vazio.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe os campos. Se um deles não vier, fica uma string vazia para o
    // empty() logo abaixo tratar como campo não preenchido.
    $caracteristica = valida_dados(isset($_POST['caracteristica']) ? $_POST['caracteristica'] : '');
    $genotipo1 = valida_dados(isset($_POST['genotipo1']) ? $_POST['genotipo1'] : '');
    $genotipo2 = valida_dados(isset($_POST['genotipo2']) ? $_POST['genotipo2'] : '');
    $fenotipoDom = valida_dados(isset($_POST['fenotipoDom']) ? $_POST['fenotipoDom'] : '');
    $fenotipoRec = valida_dados(isset($_POST['fenotipoRec']) ? $_POST['fenotipoRec'] : '');
    $descendentes = valida_dados(isset($_POST['descendentes']) ? $_POST['descendentes'] : '');

    // Característica: obrigatória e só com letras.
    if (empty($caracteristica)) {
        $erros['caracteristica'] = 'Informe a característica estudada.';
    } elseif (!preg_match($padraoTexto, $caracteristica)) {
        $erros['caracteristica'] = 'Use apenas letras (mínimo 3).';
    }

    // Genótipo 1: obrigatório e com duas letras.
    if (empty($genotipo1)) {
        $erros['genotipo1'] = 'Informe o genótipo do primeiro indivíduo.';
    } elseif (!preg_match($padraoGenotipo, $genotipo1)) {
        $erros['genotipo1'] = 'O genótipo precisa ter 2 letras, como Aa.';
    }

    // Genótipo 2: obrigatório e com duas letras.
    if (empty($genotipo2)) {
        $erros['genotipo2'] = 'Informe o genótipo do segundo indivíduo.';
    } elseif (!preg_match($padraoGenotipo, $genotipo2)) {
        $erros['genotipo2'] = 'O genótipo precisa ter 2 letras, como Aa.';
    }

    // Confere se o genótipo é de um único gene (Aa ou AA) e se os dois são do
    // mesmo gene. Sem isso, "Ab × Aa" passaria na validação e o quadro juntaria
    // alelos de genes diferentes, mostrando genótipos sem sentido.
    // A comparação é feita com mesmoGene(), que ignora maiúscula/minúscula.
    if (!isset($erros['genotipo1']) && !isset($erros['genotipo2'])) {

        if (!mesmoGene($genotipo1[0], $genotipo1[1])) {
            $erros['genotipo1'] = 'Use sempre a mesma letra no genótipo. Exemplo: Aa ou AA.';
        } elseif (!mesmoGene($genotipo2[0], $genotipo2[1])) {
            $erros['genotipo2'] = 'Use sempre a mesma letra no genótipo. Exemplo: Aa ou AA.';
        } elseif (!mesmoGene($genotipo1[0], $genotipo2[0])) {
            $erros['genotipo1'] = 'Os dois genótipos devem usar a mesma letra. Exemplo: Aa × Aa.';
            $erros['genotipo2'] = 'Este genótipo está com uma letra diferente do genótipo 1.';
        } else {
            // Grava na grafia padrão: "aA" vira "Aa" já no topo do resultado.
            $genotipo1 = ordenarAlelos($genotipo1[0], $genotipo1[1]);
            $genotipo2 = ordenarAlelos($genotipo2[0], $genotipo2[1]);
        }
    }

    // Fenótipo dominante: obrigatório e só com letras.
    if (empty($fenotipoDom)) {
        $erros['fenotipoDom'] = 'Informe o fenótipo dominante.';
    } elseif (!preg_match($padraoTexto, $fenotipoDom)) {
        $erros['fenotipoDom'] = 'Use apenas letras (mínimo 3).';
    }

    // Fenótipo recessivo: obrigatório e só com letras.
    if (empty($fenotipoRec)) {
        $erros['fenotipoRec'] = 'Informe o fenótipo recessivo.';
    } elseif (!preg_match($padraoTexto, $fenotipoRec)) {
        $erros['fenotipoRec'] = 'Use apenas letras (mínimo 3).';
    }

    // Descendentes: obrigatório e precisa ser um número inteiro válido.
    if (empty($descendentes)) {
        $erros['descendentes'] = 'Informe quantos descendentes você espera.';
    } else {
        $totalDescendentes = filter_var($descendentes, $filtroNumeroInteiro);

        if ($totalDescendentes === false || $totalDescendentes < 1) {
            $erros['descendentes'] = 'Use um número inteiro maior que zero.';
        } elseif ($totalDescendentes > 100000) {
            $erros['descendentes'] = 'Use no máximo 100000 descendentes.';
        }
    }

    // Só calcula o cruzamento quando nenhum campo apresentou erro.
    if (count($erros) === 0) {

        $resultado = calcularCruzamento($genotipo1, $genotipo2);

        // Células do quadro: as linhas são os gametas do genótipo 1 e as
        // colunas são os gametas do genótipo 2. Cada célula recebe o par
        // padronizado (Aa), nunca a sequência de letras colada.
        $linhas = array($genotipo1[0], $genotipo1[1]);
        $colunas = array($genotipo2[0], $genotipo2[1]);
        $celulas = array();

        foreach ($linhas as $aleloLinha) {
            foreach ($colunas as $aleloColuna) {
                $celulas[] = ordenarAlelos($aleloLinha, $aleloColuna);
            }
        }

        // Classificação simples do cruzamento, para o resultado não ser só
        // números: dois heterozigotos dão a proporção clássica 3:1, um
        // heterozigoto com um homozigoto dá 1:1, e dois homozigotos geram um
        // único genótipo.
        $primeiroEhHeterozigoto = ehHeterozigoto($genotipo1);
        $segundoEhHeterozigoto = ehHeterozigoto($genotipo2);

        if ($primeiroEhHeterozigoto && $segundoEhHeterozigoto) {
            $classificacao = 'Cruzamento entre dois heterozigotos: a proporção clássica é 3 dominantes para 1 recessivo.';
        } elseif ($primeiroEhHeterozigoto || $segundoEhHeterozigoto) {
            $classificacao = 'Cruzamento entre um heterozigoto e um homozigoto: os genótipos aparecem na proporção 1 para 1.';
        } else {
            $classificacao = 'Cruzamento entre dois homozigotos: todos os descendentes apresentam o mesmo genótipo.';
        }

        $cruzamento = array(
            'resultado' => $resultado,
            'classificacao' => $classificacao,
            'celulas' => $celulas,
            'colunas' => $colunas,
            'linhas' => $linhas,
            'percentualDominante' => arredondar($resultado['dominantes'] * 100 / $resultado['combinacoes']),
            'percentualRecessivo' => arredondar($resultado['recessivos'] * 100 / $resultado['combinacoes']),
            'descendentesDominantes' => arredondar($totalDescendentes * $resultado['dominantes'] / $resultado['combinacoes']),
            'descendentesRecessivos' => arredondar($totalDescendentes * $resultado['recessivos'] / $resultado['combinacoes'])
        );
    }
}

require_once 'topo.php';
?>

<section class="page-head">
    <div class="wrap">
        <span class="eyebrow">Ferramenta · Punnett</span>
        <h1>Quadro de Punnett</h1>
        <p class="lead">
            Monte o cruzamento entre dois genótipos e veja genótipos, fenótipos e
            quantos descendentes de cada tipo são esperados.
        </p>
    </div>
</section>

<section class="section tool-secao">
    <div class="wrap tool-layout">

        <!-- Formulário -->
        <section class="tool tool-form">
            <h2>Dados do cruzamento</h2>
            <p>Use a mesma letra nos dois genótipos (Aa × Aa).</p>

            <!-- Aviso geral de erro; as mensagens específicas ficam em cada campo. -->
            <?php if (count($erros) > 0): ?>
                <p class="aviso aviso-erro">
                    Confira os campos marcados em vermelho antes de montar o quadro.
                </p>
            <?php endif; ?>

            <form method="post" action="punnett.php">

                <div class="field <?php echo classeErro($erros, 'caracteristica'); ?>">
                    <label for="caracteristica">Característica</label>
                    <input class="input" type="text" id="caracteristica" name="caracteristica"
                           placeholder="Ex: cor da semente" value="<?php echo escaparHtml($caracteristica); ?>">
                    <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'caracteristica')); ?></span>
                </div>

                <div class="grade-2">
                    <div class="field <?php echo classeErro($erros, 'genotipo1'); ?>">
                        <label for="genotipo1">Genótipo 1</label>
                        <input class="input" type="text" id="genotipo1" name="genotipo1"
                               placeholder="Aa" maxlength="2" value="<?php echo escaparHtml($genotipo1); ?>">
                        <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'genotipo1')); ?></span>
                    </div>

                    <div class="field <?php echo classeErro($erros, 'genotipo2'); ?>">
                        <label for="genotipo2">Genótipo 2</label>
                        <input class="input" type="text" id="genotipo2" name="genotipo2"
                               placeholder="Aa" maxlength="2" value="<?php echo escaparHtml($genotipo2); ?>">
                        <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'genotipo2')); ?></span>
                    </div>
                </div>

                <div class="grade-2">
                    <div class="field <?php echo classeErro($erros, 'fenotipoDom'); ?>">
                        <label for="fenotipoDom">Fenótipo dominante</label>
                        <input class="input" type="text" id="fenotipoDom" name="fenotipoDom"
                               placeholder="Ex: amarela" value="<?php echo escaparHtml($fenotipoDom); ?>">
                        <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'fenotipoDom')); ?></span>
                    </div>

                    <div class="field <?php echo classeErro($erros, 'fenotipoRec'); ?>">
                        <label for="fenotipoRec">Fenótipo recessivo</label>
                        <input class="input" type="text" id="fenotipoRec" name="fenotipoRec"
                               placeholder="Ex: verde" value="<?php echo escaparHtml($fenotipoRec); ?>">
                        <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'fenotipoRec')); ?></span>
                    </div>
                </div>

                <div class="field <?php echo classeErro($erros, 'descendentes'); ?>">
                    <label for="descendentes">Descendentes esperados</label>
                    <input class="input" type="text" id="descendentes" name="descendentes"
                           placeholder="Ex: 40" value="<?php echo escaparHtml($descendentes); ?>">
                    <span class="erro"><?php echo escaparHtml(mensagemErro($erros, 'descendentes')); ?></span>
                </div>

                <button class="btn btn-primary" type="submit">Montar o quadro</button>
            </form>
        </section>

        <!-- Resultado ou explicação -->
        <section class="tool tool-resultado">

            <?php if ($cruzamento === null): ?>

                <h2>Como o quadro funciona</h2>
                <p>
                    Cada indivíduo forma gametas com um alelo. O quadro cruza todos os
                    gametas possíveis, e cada célula é um genótipo possível para o filho.
                </p>

                <ul class="lista-explica">
                    <li><strong>Aa</strong> forma os gametas A e a.</li>
                    <li><strong>Aa × Aa</strong> gera 1 AA : 2 Aa : 1 aa.</li>
                    <li>Como A domina, <strong>3 de 4</strong> têm o fenótipo dominante.</li>
                </ul>

            <?php else: ?>

                <!-- Confirmação visual de que o formulário foi processado. -->
                <p class="aviso aviso-sucesso">
                    Cruzamento calculado com sucesso. Confira o quadro abaixo.
                </p>

                <h2>Resultado</h2>
                <p class="muted">
                    <?php echo escaparHtml($caracteristica); ?> ·
                    <?php echo escaparHtml($genotipo1); ?> × <?php echo escaparHtml($genotipo2); ?>
                </p>

                <div class="quadro" role="table" aria-label="Quadro de Punnett">
                    <span class="quadro-canto" role="columnheader"></span>
                    <?php foreach ($cruzamento['colunas'] as $aleloColuna): ?>
                        <span class="quadro-cab" role="columnheader"><?php echo escaparHtml($aleloColuna); ?></span>
                    <?php endforeach; ?>

                    <?php $posicaoCelula = 0; ?>
                    <?php foreach ($cruzamento['linhas'] as $aleloLinha): ?>
                        <span class="quadro-lado" role="rowheader"><?php echo escaparHtml($aleloLinha); ?></span>
                        <?php for ($indiceColuna = 0; $indiceColuna < count($cruzamento['colunas']); $indiceColuna++): ?>
                            <span class="quadro-celula" role="cell"><?php echo escaparHtml($cruzamento['celulas'][$posicaoCelula]); ?></span>
                            <?php $posicaoCelula++; ?>
                        <?php endfor; ?>
                    <?php endforeach; ?>
                </div>

                <h3 class="sub-titulo">Genótipos</h3>
                <ul class="genotipos">
                    <?php foreach ($cruzamento['resultado']['genotipos'] as $genotipo => $quantidade): ?>
                        <li>
                            <strong><?php echo escaparHtml($genotipo); ?></strong>
                            <span><?php echo $quantidade; ?> de <?php echo $cruzamento['resultado']['combinacoes']; ?> · <?php echo arredondar($quantidade * 100 / $cruzamento['resultado']['combinacoes']); ?>%</span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h3 class="sub-titulo">Fenótipos</h3>

                <!-- A barra é um <progress>: a porcentagem fica no atributo
                     value e a cor fica no CSS, sem style="" na página. -->
                <div class="medida">
                    <div class="medida-topo">
                        <span><?php echo escaparHtml($fenotipoDom); ?> (dominante)</span>
                        <strong><?php echo $cruzamento['percentualDominante']; ?>%</strong>
                    </div>
                    <progress class="barra-progresso" value="<?php echo $cruzamento['percentualDominante']; ?>" max="100">
                        <?php echo $cruzamento['percentualDominante']; ?>%
                    </progress>
                </div>

                <div class="medida">
                    <div class="medida-topo">
                        <span><?php echo escaparHtml($fenotipoRec); ?> (recessivo)</span>
                        <strong><?php echo $cruzamento['percentualRecessivo']; ?>%</strong>
                    </div>
                    <progress class="barra-progresso" value="<?php echo $cruzamento['percentualRecessivo']; ?>" max="100">
                        <?php echo $cruzamento['percentualRecessivo']; ?>%
                    </progress>
                </div>

                <div class="caixa-conta">
                    Em <strong><?php echo escaparHtml($descendentes); ?></strong> descendentes, a previsão é
                    de aproximadamente <strong><?php echo $cruzamento['descendentesDominantes']; ?></strong>
                    com <?php echo escaparHtml($fenotipoDom); ?> e
                    <strong><?php echo $cruzamento['descendentesRecessivos']; ?></strong>
                    com <?php echo escaparHtml($fenotipoRec); ?>.
                    <span class="muted">Isso é probabilidade, não um número garantido:
                    cada filho é um sorteio independente dos alelos.</span>
                </div>

                <p class="dica">
                    <strong>Classificação:</strong> <?php echo escaparHtml($cruzamento['classificacao']); ?>
                </p>

            <?php endif; ?>

        </section>

    </div>
</section>

<?php require_once 'rodape.php'; ?>
