<?php
// Conexão com o MySQL do GeneEnem.
//
// Este arquivo NÃO guarda credenciais. Elas vêm de duas fontes, nesta ordem:
//
//   1. Variáveis de ambiente do servidor.
//      Na Wasmer Edge o banco é criado no painel (capabilities.database do
//      app.yaml) e a plataforma injeta automaticamente:
//         DB_HOST, DB_PORT, DB_NAME, DB_USERNAME, DB_PASSWORD
//      Os nomes antigos GENEENEM_DB_* continuam aceitos, então nada precisa
//      ser editado ao publicar.
//
//   2. config.local.php (só existe na sua máquina, com os dados do XAMPP).
//      Ele é ignorado pelo .wasmerignore/.gitignore, ou seja, não vai para a
//      hospedagem. Localmente é o que aponta para localhost:3307.
//
// Resultado: o mesmo projeto roda no XAMPP e no servidor sem trocar código.

// Lê uma variável de ambiente considerando getenv(), $_ENV e $_SERVER,
// porque cada runtime popula lugares diferentes.
function geneenemVariavelAmbiente(array $nomes, bool $aceitaVazio = false)
{
    foreach ($nomes as $nome) {
        $candidatos = array(
            getenv($nome),
            isset($_ENV[$nome]) ? $_ENV[$nome] : null,
            isset($_SERVER[$nome]) ? $_SERVER[$nome] : null
        );

        foreach ($candidatos as $valor) {
            if (!is_string($valor)) {
                continue;
            }

            $valor = trim($valor);

            if ($valor !== '') {
                return $valor;
            }

            // Senha vazia é válida (MySQL do XAMPP sem senha).
            if ($aceitaVazio) {
                return '';
            }
        }
    }

    return null;
}

$configuracao = array(
    'host'    => geneenemVariavelAmbiente(array('DB_HOST', 'GENEENEM_DB_HOST')),
    'porta'   => geneenemVariavelAmbiente(array('DB_PORT', 'GENEENEM_DB_PORT')),
    'nome_db' => geneenemVariavelAmbiente(array('DB_NAME', 'GENEENEM_DB_NAME')),
    'usuario' => geneenemVariavelAmbiente(array('DB_USERNAME', 'DB_USER', 'GENEENEM_DB_USER')),
    'senha'   => geneenemVariavelAmbiente(array('DB_PASSWORD', 'DB_PASS', 'GENEENEM_DB_PASS'), true),
    'ssl'     => geneenemVariavelAmbiente(array('GENEENEM_DB_SSL')),
    'origem'  => 'ambiente'
);

// Sem variáveis de ambiente: usa o arquivo local (apenas no XAMPP).
if ($configuracao['host'] === null) {
    $arquivoLocal = __DIR__ . '/config.local.php';

    if (is_readable($arquivoLocal)) {
        $local = require $arquivoLocal;

        if (is_array($local)) {
            $configuracao['host']    = isset($local['host']) ? (string) $local['host'] : null;
            $configuracao['porta']   = isset($local['porta']) ? (string) $local['porta'] : null;
            $configuracao['nome_db'] = isset($local['nome_db']) ? (string) $local['nome_db'] : null;
            $configuracao['usuario'] = isset($local['usuario']) ? (string) $local['usuario'] : null;
            $configuracao['senha']   = isset($local['senha']) ? (string) $local['senha'] : '';
            $configuracao['ssl']     = isset($local['ssl']) ? ($local['ssl'] ? '1' : '0') : null;
            $configuracao['origem']  = 'config.local.php';
        }
    }
}

// Servidor sem banco configurado: falha explícita, nunca silenciosa,
// e sem expor nome de usuário, senha ou host na página.
if ($configuracao['host'] === null || $configuracao['nome_db'] === null || $configuracao['usuario'] === null) {
    error_log(
        'GeneEnem: banco de dados não configurado. No servidor, crie o banco no painel '
        . '(app.yaml com capabilities.database) para receber DB_HOST/DB_PORT/DB_NAME/'
        . 'DB_USERNAME/DB_PASSWORD; no XAMPP, crie config.local.php.'
    );

    http_response_code(500);
    exit('O banco de dados do GeneEnem ainda não foi configurado neste servidor.');
}

$configuracao['porta'] = $configuracao['porta'] !== null ? (int) $configuracao['porta'] : 3306;

// O banco gerenciado da Wasmer exige TLS (porta não padrão, certificado de CA
// privada). XAMPP normalmente não tem TLS configurado, por isso o padrão é:
// host remoto -> TLS ligado; host local -> TLS desligado.
if ($configuracao['ssl'] === null) {
    $hostLocal = in_array(strtolower((string) $configuracao['host']), array('localhost', '127.0.0.1', '::1'), true);
    $configuracao['ssl'] = !$hostLocal;
} else {
    $configuracao['ssl'] = filter_var($configuracao['ssl'], FILTER_VALIDATE_BOOLEAN);
}

// A partir do PHP 8.1 o mysqli lança exceções (mais fácil de tratar do que
// checar o retorno de cada função).
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Abre a conexão. O TLS é negociado na mão porque o certificado do banco da
// Wasmer é emitido por uma CA privada: conexão criptografada, mas sem validar
// a cadeia (o mesmo que sslmode=require nos outros clientes).
function geneenemConectar($configuracao, $usarSsl)
{
    $conexao = mysqli_init();
    $conexao->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    $sinalizadores = 0;

    if ($usarSsl) {
        $conexao->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        $conexao->ssl_set(null, null, null, null, null);
        $sinalizadores = MYSQLI_CLIENT_SSL;
    }

    $conexao->real_connect(
        $configuracao['host'],
        $configuracao['usuario'],
        $configuracao['senha'],
        $configuracao['nome_db'],
        $configuracao['porta'],
        null,
        $sinalizadores
    );

    $conexao->set_charset('utf8mb4');

    return $conexao;
}

// A mensagem real (com o motivo técnico) fica no log do servidor; o visitante
// vê apenas um aviso simples, sem detalhes de infraestrutura.
function geneenemFalhaConexao(Throwable $erro)
{
    error_log('GeneEnem: falha na conexão com o MySQL: ' . $erro->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Tente novamente em instantes; se o problema continuar, avise o responsável pelo site.');
}

try {
    $conexao = geneenemConectar($configuracao, $configuracao['ssl']);
} catch (mysqli_sql_exception $erro) {
    $conexao = null;

    // Se o TLS foi a causa (host sem TLS, por exemplo), tenta uma vez sem
    // criptografia em vez de derrubar o site. O log registra o ocorrido.
    if ($configuracao['ssl']) {
        try {
            $conexao = geneenemConectar($configuracao, false);
            error_log('GeneEnem: conexão MySQL sem TLS (o servidor não aceitou TLS).');
        } catch (mysqli_sql_exception $erroSemSsl) {
            geneenemFalhaConexao($erroSemSsl);
        }
    } else {
        geneenemFalhaConexao($erro);
    }
}

// ---------------------------------------------------------------------------
// Erros inesperados depois da conexão (consulta, tabela ausente, etc.) não
// podem virar página em branco. O visitante vê um aviso; o motivo completo
// fica no log do servidor.
// ---------------------------------------------------------------------------

function geneenemPaginaIndisponivel(string $mensagem, string $detalhe = '', int $codigoHttp = 500): void
{
    if ($detalhe !== '') {
        error_log('GeneEnem: ' . $detalhe);
    }

    if (!headers_sent()) {
        http_response_code($codigoHttp);
        header('Content-Type: text/html; charset=UTF-8');
    }

    echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Indisponível · GeneEnem</title></head>'
        . '<body style="margin:0;min-height:100vh;display:grid;place-items:center;'
        . 'background:#f4f7f4;color:#0d2b21;font-family:system-ui,sans-serif">'
        . '<main style="max-width:520px;padding:32px;text-align:center">'
        . '<h1 style="font-size:1.35rem;margin:0 0 12px">GeneEnem indisponível</h1>'
        . '<p style="line-height:1.6;margin:0">' . htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</main></body></html>';

    exit;
}

// Cria as tabelas que faltarem a partir de banco_geneenem.sql.
// Usa CREATE TABLE IF NOT EXISTS, então nunca apaga nem altera dados.
// Devolve true quando o esquema terminou de ser aplicado.
function geneenemInstalarEsquema(mysqli $conexao): bool
{
    $arquivo = __DIR__ . '/banco_geneenem.sql';

    if (!is_readable($arquivo)) {
        error_log('GeneEnem: banco_geneenem.sql não encontrado para instalar o esquema.');
        return false;
    }

    $sql = file_get_contents($arquivo);

    try {
        $conexao->multi_query($sql);

        do {
            $resultado = $conexao->store_result();

            if ($resultado instanceof mysqli_result) {
                $resultado->free();
            }
        } while ($conexao->more_results() && $conexao->next_result());

        error_log('GeneEnem: esquema do banco aplicado automaticamente (tabelas ausentes).');
        return true;
    } catch (Throwable $erro) {
        error_log('GeneEnem: falha ao aplicar o esquema do banco: ' . $erro->getMessage());
        return false;
    }
}

set_exception_handler(function (Throwable $erro) use ($conexao) {
    $codigo = $erro instanceof mysqli_sql_exception ? (int) $erro->getCode() : 0;

    // 1146 = tabela não existe: o banco existe, mas o esquema não foi aplicado
    // (acontece na primeira publicação, com o banco recém-criado). Em vez de
    // página em branco, prepara as tabelas e pede para recarregar.
    if ($codigo === 1146) {
        static $tentouInstalar = false;

        if (!$tentouInstalar) {
            $tentouInstalar = true;

            if (geneenemInstalarEsquema($conexao)) {
                geneenemPaginaIndisponivel(
                    'O banco de dados do GeneEnem foi preparado agora. Recarregue a página para continuar.',
                    'esquema criado automaticamente após tabela ausente',
                    200
                );
            }
        }

        geneenemPaginaIndisponivel(
            'O banco de dados do GeneEnem ainda não está pronto neste servidor. '
            . 'Tente recarregar a página em alguns segundos; se continuar, avise o responsável pelo site.',
            'tabela ausente e instalação automática não concluída: ' . $erro->getMessage()
        );
    }

    geneenemPaginaIndisponivel(
        'Ocorreu um erro inesperado. Tente novamente; se continuar, avise o responsável pelo site.',
        'erro não tratado: ' . $erro->getMessage()
    );
});

// Última rede de segurança: erros fatais (que o handler acima não pega).
register_shutdown_function(function () {
    $erro = error_get_last();

    if (!is_array($erro) || !in_array($erro['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true)) {
        return;
    }

    error_log('GeneEnem: erro fatal: ' . $erro['message'] . ' em ' . $erro['file'] . ':' . $erro['line']);

    echo 'O GeneEnem encontrou um erro inesperado. Tente novamente; se continuar, avise o responsável pelo site.';
});
