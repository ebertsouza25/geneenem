<?php
// logout.php — encerra a sessão do aluno e volta para o login.
// session_unset() limpa as variáveis e session_destroy() descarta a sessão,
// então voltar pelo histórico do navegador não reabre a área interna.

require_once 'funcoes.php';

iniciarSessao();
session_unset();
session_destroy();

header('Location: login.php');
exit;
