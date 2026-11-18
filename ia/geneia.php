<?php
// Endpoint da GeneIA. Recebe a mensagem do chat, confere a sessão, o token
// CSRF, o tamanho e o assunto; consulta a OpenAI pelo servidor e devolve a
// resposta em JSON. A chave da API nunca sai deste servidor.
//
// A chamada real à API fica em ia/cliente.php e as regras de assunto em
// ia/escopo.php, mantendo este arquivo só com a orquestração.

require_once __DIR__ . '/../funcoes.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/escopo.php';
require_once __DIR__ . '/cliente.php';

iniciarSessao();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

function geneiaResponder(array $dados, int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

// Só aluno autenticado usa o chat. O botão nem aparece no login, mas a
// verificação no servidor é a que realmente protege o endpoint.
if (!isset($_SESSION['aluno_id'])) {
    geneiaResponder(array('erro' => 'Sua sessão expirou. Entre novamente para usar a GeneIA.'), 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    geneiaResponder(array('erro' => 'Método não permitido.'), 405);
}

$tokenRecebido = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? (string) $_SERVER['HTTP_X_CSRF_TOKEN'] : '';

if (!csrfValido($tokenRecebido)) {
    geneiaResponder(array('erro' => 'Sessão inválida. Recarregue a página e tente de novo.'), 403);
}

$agora = microtime(true);

// Um envio por vez, com um pequeno intervalo, e um teto por minuto para
// evitar abuso. Não existe limite diário de perguntas.
if (isset($_SESSION['geneia_ultimo_envio']) && ($agora - (float) $_SESSION['geneia_ultimo_envio']) < GENEIA_INTERVALO_MINIMO) {
    geneiaResponder(array('erro' => 'Calma! Aguarde um instante antes de enviar outra pergunta.'), 429);
}

$janela = isset($_SESSION['geneia_janela']) && is_array($_SESSION['geneia_janela'])
    ? $_SESSION['geneia_janela']
    : array();

$janela = array_values(array_filter($janela, function ($marca) use ($agora) {
    return is_numeric($marca) && ($agora - (float) $marca) < 60;
}));

if (count($janela) >= GENEIA_MAX_MENSAGENS_MINUTO) {
    geneiaResponder(array('erro' => 'Você enviou muitas perguntas em pouco tempo. Aguarde alguns segundos e continue.'), 429);
}

$corpo = file_get_contents('php://input');

if (!is_string($corpo) || strlen($corpo) > 8000) {
    geneiaResponder(array('erro' => 'Não foi possível ler a mensagem enviada.'), 400);
}

$dadosRecebidos = json_decode($corpo, true);

if (!is_array($dadosRecebidos)) {
    geneiaResponder(array('erro' => 'Não foi possível ler a mensagem enviada.'), 400);
}

$mensagem = isset($dadosRecebidos['mensagem']) && is_string($dadosRecebidos['mensagem'])
    ? trim($dadosRecebidos['mensagem'])
    : '';

// Remove caracteres de controle que não fazem sentido em uma pergunta.
$mensagem = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $mensagem);

$mensagem = is_string($mensagem) ? trim($mensagem) : '';

if ($mensagem === '') {
    geneiaResponder(array('erro' => 'Escreva uma pergunta antes de enviar.'), 422);
}

if (mb_strlen($mensagem, 'UTF-8') > GENEIA_MAX_CARACTERES) {
    geneiaResponder(array('erro' => 'A pergunta pode ter no máximo ' . GENEIA_MAX_CARACTERES . ' caracteres.'), 422);
}

// Histórico guardado na sessão do servidor (o navegador não manda histórico).
$historico = isset($_SESSION['geneia_historico']) && is_array($_SESSION['geneia_historico'])
    ? $_SESSION['geneia_historico']
    : array();

$temHistorico = count($historico) > 0;

// Triagem no servidor: comandos de manipulação e assuntos claramente fora da
// Genética não chegam à API.
if (geneiaForaDoEscopo($mensagem, $temHistorico)) {
    $textoNormalizado = geneiaNormalizar($mensagem);

    geneiaResponder(array(
        'resposta' => geneiaTentaManipular($textoNormalizado)
            ? GENEIA_RESPOSTA_MANIPULACAO
            : GENEIA_RESPOSTA_FORA_ESCOPO,
        'fora_do_escopo' => true
    ));
}

// Chave não configurada: avisa o aluno, sem simular que a IA está funcionando.
if (GENEIA_CHAVE === '') {
    error_log('GeneIA: OPENAI_API_KEY não configurada no servidor.');
    geneiaResponder(array(
        'erro' => 'A GeneIA está temporariamente indisponível. O responsável pelo GeneEnem precisa concluir a configuração do serviço de IA.'
    ), 503);
}

// Conta o envio depois de todas as validações.
$_SESSION['geneia_ultimo_envio'] = $agora;
$janela[] = $agora;
$_SESSION['geneia_janela'] = $janela;

// Monta o histórico enviado à API: mensagens válidas, dentro do limite do
// modelo, começando por uma pergunta do aluno.
function geneiaHistoricoParaApi(array $historico): array
{
    $limpo = array();

    foreach ($historico as $item) {
        if (!is_array($item) || !isset($item['role'], $item['content'])) {
            continue;
        }

        if (!in_array($item['role'], array('user', 'assistant'), true)) {
            continue;
        }

        if (!is_string($item['content']) || trim($item['content']) === '') {
            continue;
        }

        $limpo[] = array(
            'role' => $item['role'],
            'content' => mb_substr($item['content'], 0, 4000, 'UTF-8')
        );
    }

    $limpo = array_slice($limpo, -GENEIA_HISTORICO);

    // O primeiro item precisa ser do aluno: um histórico que começa com fala
    // da IA confunde o modelo sobre quem disse o quê.
    while (count($limpo) > 0 && $limpo[0]['role'] !== 'user') {
        array_shift($limpo);
    }

    // Corte por tamanho total, descartando os pares mais antigos.
    $total = 0;

    foreach ($limpo as $item) {
        $total += mb_strlen($item['content'], 'UTF-8');
    }

    while ($total > GENEIA_HISTORICO_CARACTERES && count($limpo) > 1) {
        $removido = array_shift($limpo);
        $total -= mb_strlen($removido['content'], 'UTF-8');
    }

    while (count($limpo) > 0 && $limpo[0]['role'] !== 'user') {
        array_shift($limpo);
    }

    return $limpo;
}

$mensagens = geneiaHistoricoParaApi($historico);
$mensagens[] = array('role' => 'user', 'content' => $mensagem);

$resultado = geneiaChamarApi(geneiaPromptSistema(), $mensagens);

if (!$resultado['ok']) {
    error_log('GeneIA: ' . $resultado['interno']);

    $codigo = $resultado['http'] === 429 ? 429 : 502;

    if ($resultado['http'] === 401 || $resultado['http'] === 403) {
        $codigo = 502;
    }

    geneiaResponder(array('erro' => $resultado['erro']), $codigo);
}

$conteudo = $resultado['texto'];

// Registro de consumo no log do servidor (ajuda a acompanhar os tokens).
if (!empty($resultado['uso'])) {
    $uso = $resultado['uso'];
    error_log('GeneIA: consumo modelo=' . $resultado['modelo']
        . ' entrada=' . (isset($uso['input_tokens']) ? (int) $uso['input_tokens'] : '?')
        . ' saida=' . (isset($uso['output_tokens']) ? (int) $uso['output_tokens'] : '?'));
}

// Guarda a conversa na sessão para as próximas perguntas terem contexto.
$historico[] = array('role' => 'user', 'content' => $mensagem);
$historico[] = array('role' => 'assistant', 'content' => $conteudo);
$_SESSION['geneia_historico'] = array_slice($historico, -GENEIA_HISTORICO);

geneiaResponder(array('resposta' => $conteudo));
