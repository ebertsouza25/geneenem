<?php
// Configuração da GeneIA.
//
// A chave da API fica SOMENTE no servidor. Ela nunca é enviada ao navegador:
// o JavaScript conversa apenas com ia/geneia.php, que fala com a OpenAI.
//
// Ordem de leitura das configurações (a primeira que existir vence):
//   1. variável de ambiente do servidor  (OPENAI_API_KEY, OPENAI_MODEL, ...)
//   2. arquivo ia/.env                   (OPENAI_API_KEY=sk-..., uma por linha)
//   3. constante abaixo, preenchida no servidor
//
// No XAMPP, o caminho mais simples é criar ia/.env com a chave.
// Na InfinityFree não é possível definir variáveis de ambiente, então use
// o ia/.env (o ia/.htaccess impediria o acesso pelo navegador, mas a pasta
// pode não aceitar .htaccess: por isso a chave nunca vai para arquivos
// públicos que o PHP não execute primeiro).

// Lê um arquivo no formato CHAVE=valor e coloca os valores no ambiente,
// sem sobrescrever o que já foi definido pelo servidor.
function geneiaCarregarEnv(string $caminho): void
{
    if (!is_readable($caminho)) {
        return;
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!is_array($linhas)) {
        return;
    }

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '' || $linha[0] === '#') {
            continue;
        }

        $partes = explode('=', $linha, 2);

        if (count($partes) !== 2) {
            continue;
        }

        $nome = trim($partes[0]);
        $valor = trim($partes[1]);

        // Aceita valores entre aspas: OPENAI_API_KEY="sk-..."
        $tamanho = strlen($valor);

        if ($tamanho >= 2) {
            $primeiro = $valor[0];
            $ultimo = $valor[$tamanho - 1];

            if (($primeiro === '"' && $ultimo === '"') || ($primeiro === "'" && $ultimo === "'")) {
                $valor = substr($valor, 1, $tamanho - 2);
            }
        }

        if ($nome === '' || getenv($nome) !== false) {
            continue;
        }

        putenv($nome . '=' . $valor);
        $_ENV[$nome] = $valor;
        $_SERVER[$nome] = $valor;
    }
}

// Devolve a configuração pedida: ambiente primeiro, valor padrão depois.
function geneiaEnv(string $nome, string $padrao = ''): string
{
    $valor = getenv($nome);

    if ($valor === false || trim((string) $valor) === '') {
        return $padrao;
    }

    return trim((string) $valor);
}

geneiaCarregarEnv(__DIR__ . '/.env');

// A chave da API. Vazia significa "ainda não configurada": o endpoint avisa
// o aluno em vez de fingir que a IA está funcionando.
// GENEENEM_OPENAI_KEY é aceita apenas para não quebrar instalações antigas.
define('GENEIA_CHAVE', geneiaEnv('OPENAI_API_KEY') !== '' ? geneiaEnv('OPENAI_API_KEY') : geneiaEnv('GENEENEM_OPENAI_KEY'));

// Modelo usado nas respostas. O padrão depende do formato da API (veja abaixo).
// Trocar de modelo é só mudar OPENAI_MODEL no ia/.env.

// Endereço base da API. Pode apontar para a OpenAI ou para qualquer provedor
// compatível (Google Gemini, Groq, OpenRouter, Mistral...).
define('GENEIA_BASE', rtrim(geneiaEnv('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/'));

// Formato do endpoint:
//   responses -> OpenAI Responses API (/responses)
//   chat      -> Chat Completions (/chat/completions), usado por quase todos
//                os provedores compatíveis gratuitos
// Automático: api.openai.com usa Responses; qualquer outro endereço usa Chat.
define('GENEIA_FORMATO', (function () {
    $formato = geneiaEnv('OPENAI_API_MODE');

    if ($formato === 'responses' || $formato === 'chat') {
        return $formato;
    }

    return strpos(GENEIA_BASE, 'api.openai.com') !== false ? 'responses' : 'chat';
})());

define('GENEIA_URL', GENEIA_BASE . (GENEIA_FORMATO === 'responses' ? '/responses' : '/chat/completions'));

define('GENEIA_MODELO', geneiaEnv('OPENAI_MODEL') !== ''
    ? geneiaEnv('OPENAI_MODEL')
    : geneiaEnv('GENEENEM_OPENAI_MODEL', GENEIA_FORMATO === 'responses' ? 'gpt-6-luna' : 'gemini-3.5-flash-lite'));

// Se a conta não tiver acesso ao modelo principal, a GeneIA tenta estes antes
// de desistir. Assim a troca de modelo é automática.
define(
    'GENEIA_MODELOS_RESERVA',
    array_values(array_filter(array_map('trim', explode(',', geneiaEnv(
        'OPENAI_MODEL_FALLBACKS',
        GENEIA_FORMATO === 'responses' ? 'gpt-6.1-terra,gpt-6.1-sol' : 'gemini-3.1-flash-lite,gemini-3.8-flash'
    )))))
);

// Esforço de raciocínio (none, low, medium, high). Vazio omite o parâmetro,
// o que é útil para modelos que não aceitam raciocínio.
define('GENEIA_ESFORCO', geneiaEnv('OPENAI_REASONING_EFFORT', 'low'));

// Não guardar a conversa do aluno na OpenAI.
define('GENEIA_ARMAZENAR', geneiaEnv('OPENAI_STORE', 'false') === 'true');

// Limites do chat.
define('GENEIA_MAX_CARACTERES', 1000);   // tamanho de uma pergunta
define('GENEIA_MAX_TOKENS', 1200);       // resposta + raciocínio do modelo
define('GENEIA_HISTORICO', 12);          // mensagens mantidas na sessão
define('GENEIA_HISTORICO_CARACTERES', 12000); // corte por tamanho total
define('GENEIA_MAX_MENSAGENS_MINUTO', 15);    // proteção contra abuso
define('GENEIA_INTERVALO_MINIMO', 0.8);       // segundos entre perguntas
define('GENEIA_TIMEOUT_CONEXAO', 10);
define('GENEIA_TIMEOUT_TOTAL', 55);
