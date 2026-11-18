<?php
// Funções utilizadas em várias páginas do GeneEnem.
//
// Os dados dos formulários passam por validação antes de serem processados.
// A proteção contra HTML Injection é aplicada antes de exibir os dados.
//
// Abre a sessão com cookies mais protegidos. Chamar esta função em vez de
// session_start() garante as mesmas opções em todas as páginas: o cookie não
// fica acessível ao JavaScript (HttpOnly), não vai junto em requisições de
// outros sites (SameSite=Lax) e só usa HTTPS quando ele está disponível.
function iniciarSessao()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $https
    ));

    session_start();
}

// Prepara os valores para serem exibidos no HTML.
// O htmlspecialchars() transforma caracteres especiais em entidades HTML,
// impedindo que códigos enviados pelo usuário sejam executados.
function escaparHtml($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// valida_dados() — usada no recebimento dos campos dos formulários.
// Remove espaços das pontas e barras invertidas dos dados recebidos.
//
// O htmlspecialchars() não é usado aqui, pois a proteção contra HTML
// Injection é feita na exibição, pela função escaparHtml().
function valida_dados($dados)
{
    $dados = trim($dados);
    $dados = stripslashes($dados);
    return $dados;
}

// Devolve a mensagem de erro do campo ou string vazia quando o campo está certo.
function mensagemErro($erros, $campo)
{
    if (isset($erros[$campo])) {
        return $erros[$campo];
    }

    return '';
}

// Devolve a classe CSS que deixa o campo com a borda vermelha quando há erro.
function classeErro($erros, $campo)
{
    if (isset($erros[$campo])) {
        return 'campo-erro';
    }

    return '';
}

// Diz se a letra recebida é maiúscula. Usado para deixar o alelo dominante
// sempre na frente do par (Aa em vez de aA).
function letraMaiuscula($letra)
{
    if (preg_match('/[A-Z]/', $letra)) {
        return true;
    }

    return false;
}

// Compara duas letras do genótipo ignorando maiúscula e minúscula, porque em
// genética A e a representam o mesmo gene (um dominante e outro recessivo).
// strtolower() deixa as duas em minúscula e a comparação fica direta.
function mesmoGene($primeiraLetra, $segundaLetra)
{
    $primeiraMinuscula = strtolower($primeiraLetra);
    $segundaMinuscula = strtolower($segundaLetra);

    if ($primeiraMinuscula === $segundaMinuscula) {
        return true;
    }

    return false;
}

// Devolve a base que pareia com a recebida: A com T e C com G.
// A comparação aceita maiúscula e minúscula para não precisar converter a
// sequência inteira antes da análise.
function baseComplementar($base)
{
    if (preg_match('/[Aa]/', $base)) {
        return 'T';
    }

    if (preg_match('/[Tt]/', $base)) {
        return 'A';
    }

    if (preg_match('/[Cc]/', $base)) {
        return 'G';
    }

    if (preg_match('/[Gg]/', $base)) {
        return 'C';
    }

    return '';
}

// Nome da classe CSS que colore cada base (base-a, base-t, base-c, base-g).
// A fita é digitada em maiúscula, mas as classes do CSS estão em minúscula.
function classeDaBase($base)
{
    if ($base === 'A') {
        return 'base-a';
    }

    if ($base === 'T') {
        return 'base-t';
    }

    if ($base === 'C') {
        return 'base-c';
    }

    if ($base === 'G') {
        return 'base-g';
    }

    if ($base === 'U') {
        return 'base-u';
    }

    return 'base';
}

// Monta a fita complementar de uma sequência inteira, base por base.
function fitaComplementar($sequenciaOriginal)
{
    $fita = '';
    $quantidadeBases = strlen($sequenciaOriginal);

    for ($indiceBase = 0; $indiceBase < $quantidadeBases; $indiceBase++) {
        $base = $sequenciaOriginal[$indiceBase];
        $fita = $fita . baseComplementar($base);
    }

    return $fita;
}

// Monta o RNA mensageiro a partir da fita molde.
// A fita complementar seria TACG; no RNA a timina vira uracila: UACG.
function transcreverRna($sequenciaOriginal)
{
    $fita = fitaComplementar($sequenciaOriginal);
    $rna = '';
    $quantidadeBases = strlen($fita);

    for ($indiceBase = 0; $indiceBase < $quantidadeBases; $indiceBase++) {
        $base = $fita[$indiceBase];

        if ($base === 'T') {
            $rna = $rna . 'U';
        } else {
            $rna = $rna . $base;
        }
    }

    return $rna;
}

// Escreve o par de alelos com a letra maiúscula primeiro, para o genótipo
// aparecer sempre na mesma grafia (Aa, e nunca aA).
function ordenarAlelos($primeiroAlelo, $segundoAlelo)
{
    if (letraMaiuscula($segundoAlelo) && !letraMaiuscula($primeiroAlelo)) {
        return $segundoAlelo . $primeiroAlelo;
    }

    return $primeiroAlelo . $segundoAlelo;
}

// Diz se o genótipo é heterozigoto (Aa). Como ordenarAlelos() já deixa a
// maiúscula na frente, basta conferir se a segunda letra é minúscula.
function ehHeterozigoto($genotipo)
{
    if (letraMaiuscula($genotipo[0]) && !letraMaiuscula($genotipo[1])) {
        return true;
    }

    return false;
}

// Calcula o cruzamento entre dois genótipos de duas letras (Aa x Aa).
// Passos: separa os gametas de cada um, cruza todos com todos, conta os pares
// e considera dominante o genótipo que tem pelo menos uma letra maiúscula.
function calcularCruzamento($genotipo1, $genotipo2)
{
    $gametas1 = array($genotipo1[0], $genotipo1[1]);
    $gametas2 = array($genotipo2[0], $genotipo2[1]);

    $contagem = array();
    $combinacoes = 0;

    foreach ($gametas1 as $aleloPrimeiro) {
        foreach ($gametas2 as $aleloSegundo) {
            $par = ordenarAlelos($aleloPrimeiro, $aleloSegundo);

            if (isset($contagem[$par])) {
                $contagem[$par] = $contagem[$par] + 1;
            } else {
                $contagem[$par] = 1;
            }

            $combinacoes = $combinacoes + 1;
        }
    }

    $dominantes = 0;

    foreach ($contagem as $genotipo => $quantidade) {
        if (preg_match('/[A-Z]/', $genotipo)) {
            $dominantes = $dominantes + $quantidade;
        }
    }

    return array(
        'genotipos' => $contagem,
        'combinacoes' => $combinacoes,
        'dominantes' => $dominantes,
        'recessivos' => $combinacoes - $dominantes
    );
}

// Arredonda para o inteiro mais próximo (usado em notas e porcentagens).
function arredondar($valor)
{
    return (int) round($valor);
}

// Token do formulário, guardado na sessão. Serve para conferir se o envio
// partiu de uma página do próprio site (proteção CSRF).
function tokenCsrf()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

// Compara o token recebido com o da sessão sem expor o valor na comparação.
function csrfValido($token)
{
    if (!isset($_SESSION['csrf']) || !is_string($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf'], $token);
}
