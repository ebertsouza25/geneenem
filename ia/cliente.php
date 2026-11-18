<?php
// Cliente da OpenAI (Responses API) usado pela GeneIA.
//
// Este arquivo fala com a API e devolve sempre a mesma estrutura, para o
// endpoint ia/geneia.php não precisar conhecer detalhes da OpenAI:
//   modelo  -> modelo que respondeu
//   texto   -> resposta em texto puro
//   erro    -> mensagem curta para o aluno (quando ok = false)
//   http    -> código HTTP recebido
//   interno -> detalhe técnico para o log do servidor

require_once __DIR__ . '/config.php';

// Erros da API que indicam "este modelo não existe / não está liberado".
function geneiaErroDeModelo(int $http, $respostaApi): bool
{
    if ($http === 404) {
        return true;
    }

    if (!is_array($respostaApi) || !isset($respostaApi['error']) || !is_array($respostaApi['error'])) {
        return false;
    }

    $codigo = isset($respostaApi['error']['code']) ? (string) $respostaApi['error']['code'] : '';
    $tipo = isset($respostaApi['error']['type']) ? (string) $respostaApi['error']['type'] : '';
    $mensagem = isset($respostaApi['error']['message']) ? (string) $respostaApi['error']['message'] : '';

    if ($codigo === 'model_not_found') {
        return true;
    }

    if ($tipo === 'invalid_request_error'
        && strpos($mensagem, 'model') !== false
        && (strpos($mensagem, 'does not exist') !== false
            || strpos($mensagem, 'not found') !== false
            || strpos($mensagem, 'no access') !== false)) {
        return true;
    }

    return false;
}

// Erros de parâmetro que podem ser resolvidos tentando de novo com o corpo
// mínimo (modelos diferentes aceitam argumentos diferentes).
function geneiaErroDeParametro(int $http, $respostaApi): bool
{
    if ($http !== 400) {
        return false;
    }

    if (!is_array($respostaApi) || !isset($respostaApi['error']['message'])) {
        return false;
    }

    $mensagem = mb_strtolower((string) $respostaApi['error']['message'], 'UTF-8');

    $termos = array(
        'unsupported parameter',
        'unknown parameter',
        'unrecognized request argument',
        'reasoning',
        'max_output_tokens',
        'max_tokens',
        'temperature',
        'store'
    );

    foreach ($termos as $termo) {
        if (strpos($mensagem, $termo) !== false) {
            return true;
        }
    }

    return false;
}

// O texto da resposta fica em output[]. Não é seguro supor output[0], porque
// modelos de raciocínio devolvem antes um item "reasoning". Aqui todos os itens
// do tipo "message" são percorridos.
function geneiaExtrairTexto($respostaApi): string
{
    if (!is_array($respostaApi)) {
        return '';
    }

    // Chat Completions: choices[0].message.content.
    if (isset($respostaApi['choices'][0]['message'])) {
        $mensagem = $respostaApi['choices'][0]['message'];
        $conteudo = isset($mensagem['content']) ? $mensagem['content'] : '';

        if (is_string($conteudo)) {
            return trim($conteudo);
        }

        // Alguns provedores devolvem a mensagem em partes.
        if (is_array($conteudo)) {
            $partes = array();

            foreach ($conteudo as $parte) {
                if (is_array($parte) && isset($parte['text']) && is_string($parte['text'])) {
                    $partes[] = $parte['text'];
                }
            }

            return trim(implode("\n", $partes));
        }

        return '';
    }

    if (!isset($respostaApi['output']) || !is_array($respostaApi['output'])) {
        return '';
    }

    $partes = array();

    foreach ($respostaApi['output'] as $item) {
        if (!is_array($item) || !isset($item['type']) || $item['type'] !== 'message') {
            continue;
        }

        if (!isset($item['content']) || !is_array($item['content'])) {
            continue;
        }

        foreach ($item['content'] as $conteudo) {
            if (!is_array($conteudo) || !isset($conteudo['type'])) {
                continue;
            }

            if ($conteudo['type'] === 'output_text' && isset($conteudo['text']) && is_string($conteudo['text'])) {
                $partes[] = $conteudo['text'];
            }

            // Recusa do próprio modelo: devolve o motivo, nunca uma resposta falsa.
            if ($conteudo['type'] === 'refusal' && isset($conteudo['refusal']) && is_string($conteudo['refusal'])) {
                $partes[] = $conteudo['refusal'];
            }
        }
    }

    return trim(implode("\n", $partes));
}

// Uma requisição HTTP à API. Devolve o corpo já decodificado e o status.
function geneiaHttp(string $url, string $corpo): array
{
    $curl = curl_init($url);

    curl_setopt_array($curl, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $corpo,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . GENEIA_CHAVE
        ),
        CURLOPT_CONNECTTIMEOUT => GENEIA_TIMEOUT_CONEXAO,
        CURLOPT_TIMEOUT => GENEIA_TIMEOUT_TOTAL,
        CURLOPT_SSL_VERIFYPEER => true
    ));

    $respostaBruta = curl_exec($curl);
    $http = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $erroCurl = curl_error($curl);
    curl_close($curl);

    if ($respostaBruta === false) {
        return array('http' => 0, 'corpo' => null, 'erro_curl' => $erroCurl);
    }

    $corpo = json_decode($respostaBruta, true);

    // O Google devolve o erro dentro de uma lista: [{"error": {...}}].
    // Normalizar aqui evita que a mensagem real se perca.
    if (is_array($corpo) && isset($corpo[0]) && is_array($corpo[0]) && isset($corpo[0]['error'])) {
        $corpo = $corpo[0];
    }

    return array(
        'http' => $http,
        'corpo' => $corpo,
        'erro_curl' => ''
    );
}

// Monta o corpo da requisição no formato configurado. Quando $completo é
// false, envia só o essencial — usado na segunda tentativa depois de um erro
// de parâmetro.
function geneiaCorpoRequisicao(string $modelo, string $instrucoes, array $mensagens, bool $completo): array
{
    // Chat Completions: formato aceito pela OpenAI e por praticamente todos os
    // provedores compatíveis. As instruções fixas viram a mensagem "system".
    if (GENEIA_FORMATO === 'chat') {
        $corpo = array(
            'model' => $modelo,
            'messages' => array_merge(
                array(array('role' => 'system', 'content' => $instrucoes)),
                $mensagens
            )
        );

        if ($completo) {
            $corpo['max_tokens'] = GENEIA_MAX_TOKENS;
            $corpo['temperature'] = 0.4;
        }

        return $corpo;
    }

    // Responses API: instruções com prioridade sobre o input.
    $corpo = array(
        'model' => $modelo,
        'instructions' => $instrucoes,
        'input' => $mensagens,
        'store' => GENEIA_ARMAZENAR
    );

    if ($completo) {
        $corpo['max_output_tokens'] = GENEIA_MAX_TOKENS;

        if (GENEIA_ESFORCO !== '') {
            $corpo['reasoning'] = array('effort' => GENEIA_ESFORCO);
        }
    }

    return $corpo;
}

// Conversa com a API. Tenta o modelo configurado, depois os de reserva, e
// repete uma vez sem os parâmetros opcionais quando o modelo os recusa.
function geneiaChamarApi(string $instrucoes, array $mensagens): array
{
    if (!function_exists('curl_init')) {
        return array(
            'ok' => false,
            'texto' => '',
            'modelo' => '',
            'http' => 0,
            'erro' => 'A GeneIA não está disponível no servidor agora (extensão cURL desativada).',
            'interno' => 'cURL indisponível'
        );
    }

    $modelos = array_merge(array(GENEIA_MODELO), GENEIA_MODELOS_RESERVA);
    $modelos = array_values(array_unique(array_filter($modelos)));

    $ultimoErroInterno = '';
    $ultimoHttp = 0;

    foreach ($modelos as $modelo) {
        $completo = true;
        $tentativas = 0;

        while ($tentativas < 2) {
            $tentativas++;

            $corpo = json_encode(
                geneiaCorpoRequisicao($modelo, $instrucoes, $mensagens, $completo),
                JSON_UNESCAPED_UNICODE
            );

            if ($corpo === false) {
                return array(
                    'ok' => false,
                    'texto' => '',
                    'modelo' => $modelo,
                    'http' => 0,
                    'erro' => 'Não consegui preparar a pergunta para a GeneIA. Tente novamente.',
                    'interno' => 'json_encode falhou'
                );
            }

            $resultado = geneiaHttp(GENEIA_URL, $corpo);
            $http = $resultado['http'];
            $respostaApi = $resultado['corpo'];
            $ultimoHttp = $http;

            if ($http === 0) {
                $ultimoErroInterno = 'cURL: ' . $resultado['erro_curl'];

                return array(
                    'ok' => false,
                    'texto' => '',
                    'modelo' => $modelo,
                    'http' => 0,
                    'erro' => 'Não consegui falar com a GeneIA agora. Verifique a conexão e tente de novo.',
                    'interno' => $ultimoErroInterno
                );
            }

            if ($http >= 200 && $http < 300) {
                $texto = geneiaExtrairTexto($respostaApi);

                if ($texto === '') {
                    $motivo = is_array($respostaApi) && isset($respostaApi['incomplete_details']['reason'])
                        ? (string) $respostaApi['incomplete_details']['reason']
                        : 'sem texto';

                    return array(
                        'ok' => false,
                        'texto' => '',
                        'modelo' => $modelo,
                        'http' => $http,
                        'erro' => 'A GeneIA não retornou uma resposta. Tente reformular a pergunta.',
                        'interno' => 'resposta vazia (' . $motivo . ')'
                    );
                }

                return array(
                    'ok' => true,
                    'texto' => $texto,
                    'modelo' => $modelo,
                    'http' => $http,
                    'erro' => '',
                    'interno' => '',
                    'uso' => is_array($respostaApi) && isset($respostaApi['usage']) ? $respostaApi['usage'] : array()
                );
            }

            $detalhe = is_array($respostaApi) && isset($respostaApi['error']['message'])
                ? (string) $respostaApi['error']['message']
                : 'sem detalhe';

            $ultimoErroInterno = 'HTTP ' . $http . ': ' . mb_substr($detalhe, 0, 300, 'UTF-8');

            // Parâmetro recusado: tenta de novo com o corpo mínimo.
            if (geneiaErroDeParametro($http, $respostaApi) && $completo) {
                $completo = false;
                continue;
            }

            // Instabilidade do provedor (503 "high demand", 500, 502, 504):
            // não é erro da pergunta nem da chave, então vale tentar o próximo
            // modelo antes de desistir.
            if ($http >= 500) {
                error_log('GeneIA: provedor instável com ' . $modelo . ': ' . $ultimoErroInterno);
                break;
            }

            // Modelo indisponível: passa para o próximo da lista.
            if (geneiaErroDeModelo($http, $respostaApi)) {
                error_log('GeneIA: modelo indisponível (' . $modelo . '): ' . $ultimoErroInterno);
                break;
            }

            // Chave inválida também chega como 400 em alguns provedores.
            $chaveInvalida = ($http === 400 || $http === 401 || $http === 403)
                && (stripos($detalhe, 'api key') !== false || stripos($detalhe, 'api_key') !== false);

            if ($http === 401 || $http === 403 || $chaveInvalida) {
                return array(
                    'ok' => false,
                    'texto' => '',
                    'modelo' => $modelo,
                    'http' => $http,
                    'erro' => 'A GeneIA está temporariamente indisponível: a chave da API foi recusada.',
                    'interno' => $ultimoErroInterno
                );
            }

            $codigo = is_array($respostaApi) && isset($respostaApi['error']['code'])
                ? (string) $respostaApi['error']['code']
                : '';

            if ($http === 429) {
                $semCreditos = $codigo === 'insufficient_quota'
                    || stripos($detalhe, 'quota') !== false
                    || stripos($detalhe, 'billing') !== false;

                return array(
                    'ok' => false,
                    'texto' => '',
                    'modelo' => $modelo,
                    'http' => $http,
                    'erro' => $semCreditos
                        ? 'A GeneIA está temporariamente indisponível: o limite de uso da API foi atingido (créditos, cota diária ou requisições por minuto). Tente de novo mais tarde.'
                        : 'Muitas perguntas em pouco tempo. Aguarde alguns segundos e tente de novo.',
                    'interno' => $ultimoErroInterno
                );
            }

            if ($http === 400 && stripos($detalhe, 'context') !== false) {
                return array(
                    'ok' => false,
                    'texto' => '',
                    'modelo' => $modelo,
                    'http' => $http,
                    'erro' => 'A conversa ficou longa para este modelo. Recarregue a página para começar um novo assunto.',
                    'interno' => $ultimoErroInterno
                );
            }

            return array(
                'ok' => false,
                'texto' => '',
                'modelo' => $modelo,
                'http' => $http,
                'erro' => 'A GeneIA não conseguiu responder agora. Tente novamente em instantes.',
                'interno' => $ultimoErroInterno
            );
        }
    }

    $mensagemFinal = ($ultimoHttp >= 500)
        ? 'A GeneIA está com a demanda alta no momento. Tente enviar a pergunta de novo em alguns segundos.'
        : 'A GeneIA está temporariamente indisponível: nenhum modelo configurado está disponível nesta conta.';

    return array(
        'ok' => false,
        'texto' => '',
        'modelo' => GENEIA_MODELO,
        'http' => $ultimoHttp,
        'erro' => $mensagemFinal,
        'interno' => $ultimoErroInterno
    );
}
