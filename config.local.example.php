<?php
// Modelo do config.local.php. Copie para config.local.php e preencha.
//
// Usado apenas quando o servidor NÃO define as variáveis de ambiente
// DB_HOST/DB_PORT/DB_NAME/DB_USERNAME/DB_PASSWORD — na prática, só no XAMPP.
// Na hospedagem nada disso é necessário: o banco é injetado pelo painel.
//
// Nunca publique o config.local.php preenchido.

return array(
    'host'    => 'localhost',
    'porta'   => 3307,
    'nome_db' => 'geneenem',
    'usuario' => 'root',
    'senha'   => '',
    'ssl'     => false
);
