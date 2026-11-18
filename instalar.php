<?php
// Instalação do esquema do GeneEnem em um banco já criado (Wasmer, XAMPP, etc.).
//
// Uso: defina a variável de ambiente GENEENEM_SETUP_TOKEN e abra
//   https://SEU-SITE/instalar.php?token=O_TOKEN
//
// O arquivo apenas cria o que falta (CREATE TABLE IF NOT EXISTS). Ele nunca
// apaga, altera ou recria tabelas com dados. Depois de instalado, a página
// passa a responder "já instalado" e não executa mais nada.

require_once 'config.php';

$tokenEsperado = geneenemVariavelAmbiente(array('GENEENEM_SETUP_TOKEN'));
$tokenRecebido = isset($_GET['token']) ? (string) $_GET['token'] : '';

$status = 'erro';
$titulo = 'Instalação do banco';
$detalhes = array();

// Sem token configurado no servidor, a instalação fica desligada.
if ($tokenEsperado === null) {
    http_response_code(403);
    $titulo = 'Instalação desativada';
    $detalhes[] = 'Defina a variável de ambiente GENEENEM_SETUP_TOKEN no servidor '
        . '(na Wasmer: wasmer app secrets create GENEENEM_SETUP_TOKEN "uma-frase-secreta") '
        . 'e abra esta página novamente com ?token=essa-frase.';
} elseif (!hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    $titulo = 'Token inválido';
    $detalhes[] = 'O token informado não confere. Confira o valor de GENEENEM_SETUP_TOKEN.';
} else {
    $tabelas = array('alunos', 'atividades', 'respostas');
    $faltando = array();

    foreach ($tabelas as $tabela) {
        $resultado = $conexao->query("SHOW TABLES LIKE '" . $conexao->real_escape_string($tabela) . "'");

        if ($resultado->num_rows === 0) {
            $faltando[] = $tabela;
        }

        $resultado->free();
    }

    if (count($faltando) === 0) {
        $status = 'ok';
        $titulo = 'Banco já instalado';
        $contagem = $conexao->query('SELECT COUNT(*) AS alunos FROM alunos')->fetch_assoc();
        $detalhes[] = 'As tabelas alunos, atividades e respostas já existem. '
            . 'Alunos cadastrados: ' . (int) $contagem['alunos'] . '.';
        $detalhes[] = 'Nada foi alterado. O site já pode ser usado.';
    } else {
        if (geneenemInstalarEsquema($conexao)) {
            $status = 'ok';
            $titulo = 'Banco instalado';
            $detalhes[] = 'Tabelas criadas (as que faltavam): ' . implode(', ', $faltando) . '.';
            $detalhes[] = 'Os alunos já podem criar conta e entrar pelo site.';
        } else {
            $detalhes[] = 'A instalação falhou. O motivo foi registrado no log do servidor.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?> · GeneEnem</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center;
               background: #f4f7f4; color: #0d2b21;
               font-family: system-ui, -apple-system, Segoe UI, sans-serif; }
        main { max-width: 560px; padding: 32px; }
        h1 { font-size: 1.4rem; margin: 0 0 16px; }
        p { line-height: 1.6; margin: 0 0 10px; }
        .selo { display: inline-block; margin-bottom: 16px; padding: 4px 10px;
                border-radius: 999px; font-size: .78rem; font-weight: 600;
                letter-spacing: .04em; text-transform: uppercase; }
        .ok { background: #dff3e6; color: #14683c; }
        .erro { background: #fbe1e1; color: #8a1f1f; }
        code { background: #e8efe9; padding: 2px 6px; border-radius: 6px; }
    </style>
</head>
<body>
<main>
    <span class="selo <?php echo $status === 'ok' ? 'ok' : 'erro'; ?>">
        <?php echo $status === 'ok' ? 'Concluído' : 'Atenção'; ?>
    </span>

    <h1><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h1>

    <?php foreach ($detalhes as $linha): ?>
        <p><?php echo htmlspecialchars($linha, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endforeach; ?>

    <p><a href="login.php">Ir para o login</a></p>
</main>
</body>
</html>
