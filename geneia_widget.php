<?php
// Botão flutuante e janela da GeneIA. Este arquivo é incluído pelo topo.php,
// então aparece somente nas páginas internas (nunca no login). O endpoint
// também exige sessão de aluno.

iniciarSessao();

if (!isset($_SESSION['aluno_id'])) {
    return;
}

$tokenGeneia = tokenCsrf();

// Conversa guardada na sessão pelo endpoint. Mostrar aqui mantém a janela
// coerente com o contexto que a IA recebe, inclusive depois de trocar de página.
$historicoGeneia = isset($_SESSION['geneia_historico']) && is_array($_SESSION['geneia_historico'])
    ? array_slice($_SESSION['geneia_historico'], -12)
    : array();
?>

<div class="geneia" id="geneia"
     data-endpoint="ia/geneia.php"
     data-csrf="<?php echo escaparHtml($tokenGeneia); ?>">

    <button class="geneia-fab" id="geneia-fab" type="button"
            aria-controls="geneia-janela" aria-expanded="false"
            aria-label="Abrir a GeneIA, assistente virtual de Genética"
            title="GeneIA · Assistente de Genética">
        <span class="geneia-fab-icone" aria-hidden="true">
            <img src="img/geneia-avatar.png" alt="" width="24" height="24">
        </span>
        <span class="geneia-fab-texto" aria-hidden="true">GeneIA</span>
    </button>

    <section class="geneia-janela" id="geneia-janela" role="dialog" aria-modal="false"
             aria-labelledby="geneia-titulo" aria-hidden="true">

        <header class="geneia-topo">

            <!-- Quadrado da GeneIA: usa img/geneia-avatar.png, uma versão
                 quadrada do ícone com o desenho centralizado (sem margem
                 sobrando). O botão flutuante usa a mesma imagem.
                 O tamanho e o recorte vêm de .geneia-avatar / .geneia-avatar img
                 no css/geneia.css (42x42, object-fit: cover, sem distorção). -->
            <span class="geneia-avatar">
                <img src="img/geneia-avatar.png" alt="GeneIA" width="42" height="42">
            </span>

            <div class="geneia-identidade">
                <strong id="geneia-titulo">GeneIA</strong>
                <span>Assistente de Genética</span>
            </div>

            <button class="geneia-fechar" id="geneia-fechar" type="button"
                    aria-label="Fechar a GeneIA" title="Fechar">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>

        </header>

        <div class="geneia-mensagens" id="geneia-mensagens" role="log" aria-live="polite">
            <?php if (count($historicoGeneia) === 0): ?>
                <div class="geneia-msg geneia-msg-ia">
                    <span class="geneia-msg-autor">GeneIA</span>
                    <p>Olá! Sou a GeneIA, sua professora de Genética do GeneEnem. Posso explicar DNA, RNA, hereditariedade, Mendel, heredogramas, grupos sanguíneos, mutações, biotecnologia e resolver questões passo a passo com você. O que você quer estudar?</p>
                </div>
            <?php else: ?>
                <?php foreach ($historicoGeneia as $itemGeneia): ?>
                    <?php
                    if (!is_array($itemGeneia) || !isset($itemGeneia['role'], $itemGeneia['content'])) {
                        continue;
                    }

                    if (!in_array($itemGeneia['role'], array('user', 'assistant'), true)) {
                        continue;
                    }

                    if (!is_string($itemGeneia['content']) || $itemGeneia['content'] === '') {
                        continue;
                    }

                    $ehAluno = $itemGeneia['role'] === 'user';
                    ?>
                    <div class="geneia-msg geneia-msg-<?php echo $ehAluno ? 'aluno' : 'ia'; ?>">
                        <span class="geneia-msg-autor"><?php echo $ehAluno ? 'Você' : 'GeneIA'; ?></span>
                        <p><?php echo escaparHtml($itemGeneia['content']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form class="geneia-form" id="geneia-form" autocomplete="off">
            <label class="sr-only" for="geneia-campo">Sua pergunta de Genética</label>
            <textarea class="geneia-campo" id="geneia-campo" name="mensagem" rows="1"
                      maxlength="1000" placeholder="Pergunte algo sobre Genética"></textarea>

            <button class="geneia-enviar" id="geneia-enviar" type="submit"
                    aria-label="Enviar mensagem" title="Enviar">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 12l16-8-6 16-3-6-7-2z"/>
                </svg>
            </button>
        </form>

        <p class="geneia-rodape">
            Conteúdo educativo gerado por IA. Confira sempre o material do GeneEnem.
        </p>

    </section>
</div>
