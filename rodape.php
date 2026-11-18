<?php
// Fecha o <main> e carrega o JavaScript comum: tema, navegação e os scripts
// extras que cada página pediu em $jsPagina.
?>
</main>

<script src="js/tema.js"></script>
<script src="js/app.js"></script>
<script src="js/geneia.js"></script>
<?php foreach ($jsPagina as $arquivoJs): ?>
<script src="js/<?php echo escaparHtml($arquivoJs); ?>.js"></script>
<?php endforeach; ?>
</body>
</html>
