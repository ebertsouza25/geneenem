<?php
// Card de uma questão, usado em questoes.php e em resultado.php.
// Quem inclui precisa ter definido $numeroQuestao, $dadosQuestao, $erros e,
// quando houver, $marcadas com as respostas já escolhidas.

// Sem respostas enviadas, a lista começa vazia.
if (!isset($marcadas)) {
    $marcadas = array();
}

$letraEscolhida = isset($marcadas[$numeroQuestao]) ? $marcadas[$numeroQuestao] : '';
$questaoComErro = isset($erros['questao' . $numeroQuestao]);

// Deixa o número com dois dígitos (#01, #02...) sem precisar de str_pad().
if ($numeroQuestao < 10) {
    $numeroFormatado = '0' . $numeroQuestao;
} else {
    $numeroFormatado = $numeroQuestao;
}
?>

<article class="questao<?php echo $questaoComErro ? ' questao-erro' : ''; ?>" data-questao id="q<?php echo $numeroQuestao; ?>">

    <header class="questao-topo">
        <span class="questao-num">#<?php echo $numeroFormatado; ?></span>
        <span class="chip"><?php echo escaparHtml($dadosQuestao['ano']); ?> · Questão <?php echo (int) $dadosQuestao['numero_original']; ?></span>
        <span class="chip"><?php echo escaparHtml($dadosQuestao['area']); ?></span>
    </header>

    <h3><?php echo escaparHtml($dadosQuestao['titulo']); ?></h3>

    <p class="questao-enunciado"><?php echo escaparHtml($dadosQuestao['enunciado']); ?></p>

    <!-- Cada alternativa é um rádio com o mesmo name: assim o aluno só consegue
         marcar uma por questão e o PHP recebe uma única letra. -->
    <div class="alternativas">
        <?php foreach ($dadosQuestao['alternativas'] as $letraAlternativa => $textoAlternativa): ?>
            <label class="alternativa">
                <input type="radio" name="questao<?php echo $numeroQuestao; ?>"
                       value="<?php echo escaparHtml($letraAlternativa); ?>"
                       <?php echo $letraEscolhida === $letraAlternativa ? 'checked' : ''; ?>>
                <span class="alternativa-letra"><?php echo escaparHtml($letraAlternativa); ?></span>
                <span class="alternativa-texto"><?php echo escaparHtml($textoAlternativa); ?></span>
            </label>
        <?php endforeach; ?>
    </div>

    <?php if ($questaoComErro): ?>
        <span class="erro"><?php echo escaparHtml($erros['questao' . $numeroQuestao]); ?></span>
    <?php endif; ?>

</article>
