<?php
/**
 * APEX IDE v1.2 — agora com URLs amigáveis (.apex direto)
 * Roda a IDE no navegador. Use:  php -S localhost:8000 ide.php
 */

// =====================================================================
// ROTEAMENTO AMIGÁVEL: URLs como /restaurante.apex são interpretadas
// como pedido para abrir aquele arquivo APEX.
// Exemplo: localhost:8765/restaurante.apex  →  abre restaurante.apex
// =====================================================================
$uri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($uri, PHP_URL_PATH) ?? '';
// Se a URL termina em .apex e não é a query string com acao=...
if (preg_match('/\.apex$/i', $path) && empty($_GET['acao'])) {
    // Extrai só o nome do arquivo (sem path traversal)
    $nomeArquivoAlvo = basename($path);
    // Define como se fosse uma chamada à rota abrir_apex
    $_GET['acao'] = 'abrir_apex';
    $_GET['arquivo'] = $nomeArquivoAlvo;
}

// =====================================================================
// API 1: análise + execução (para o botão "Executar" da IDE)
// =====================================================================
if (isset($_GET['acao']) && $_GET['acao'] === 'executar') {
    header('Content-Type: application/json; charset=utf-8');

    $codigo  = $_POST['codigo']  ?? '';
    $entradas = json_decode($_POST['entradas'] ?? '[]', true) ?: [];

    if (trim($codigo) === '') {
        echo json_encode(['ok' => false, 'erros' => [['linha' => 1, 'msg' => 'Código vazio.']], 'saida' => '', 'leituras' => []]);
        exit;
    }

    $resultado = analisarEexecutar($codigo, $entradas);
    echo json_encode($resultado);
    exit;
}

// =====================================================================
// Rota "validar": só faz análise estática (sem executar)
// Usada pela extensão VS Code antes de abrir no navegador.
// Aceita codigo via POST (form) ou via ?arquivo=NOME (lê do disco).
// Retorna JSON: {ok: bool, erros: [{linha, msg}, ...]}
// =====================================================================
if (isset($_GET['acao']) && $_GET['acao'] === 'validar') {
    header('Content-Type: application/json; charset=utf-8');

    $codigo = $_POST['codigo'] ?? '';
    if (empty($codigo) && isset($_GET['arquivo'])) {
        $arq = basename($_GET['arquivo']);
        $candidatos = [$arq, '../' . $arq, './' . $arq];
        foreach ($candidatos as $c) {
            if (file_exists($c)) { $codigo = file_get_contents($c); break; }
        }
    }

    if (trim($codigo) === '') {
        echo json_encode(['ok' => false, 'erros' => [['linha' => 1, 'msg' => 'Código vazio ou arquivo não encontrado.']]]);
        exit;
    }

    // Faz só análise: chama analisarEexecutar mas retorna antes de executar
    // (vamos extrair só a parte de validação)
    $erros = validarApenas($codigo);
    echo json_encode([
        'ok' => empty($erros),
        'erros' => $erros
    ]);
    exit;
}

// =====================================================================
// Rota "Abrir como site": gera formulário e/ou executa com respostas
// =====================================================================
if (isset($_GET['acao']) && $_GET['acao'] === 'site') {
    // Aceita código por POST (botão "Abrir como site" da IDE)
    // OU por GET com base64 (extensão do VS Code: ?acao=site&codigo=BASE64)
    $codigo = $_POST['codigo'] ?? '';
    if (empty($codigo) && isset($_GET['codigo'])) {
        $codigoB64 = $_GET['codigo'];
        $decodificado = base64_decode($codigoB64, true);
        if ($decodificado !== false) {
            $codigo = $decodificado;
        }
    }

    // Nome do arquivo (vindo da extensão VS Code) — usado no título da aba
    $nomeArquivo = $_GET['nome'] ?? $_POST['nome'] ?? 'Sistema';
    $nomeArquivo = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $nomeArquivo); // sanitiza

    $perguntasComTipo = extrairPerguntasComTipo($codigo);
    $temRespostas = isset($_POST['respostas']);

    if (!empty($perguntasComTipo) && !$temRespostas) {
        renderFormulario($codigo, $perguntasComTipo, $nomeArquivo);
        exit;
    }

    $entradas = [];
    if ($temRespostas) {
        $entradas = $_POST['respostas'];
        if (!is_array($entradas)) $entradas = [];
    }

    $resultado = analisarEexecutar($codigo, $entradas);
    renderResultado($resultado, $codigo, !empty($perguntasComTipo), $nomeArquivo);
    exit;
}

// =====================================================================
// Rota "abrir_apex": botões e links que apontam para outro .apex
// Lê o arquivo do disco (mesma pasta que o ide.php está rodando)
// e roda como se fosse F5 da extensão.
// =====================================================================
if (isset($_GET['acao']) && $_GET['acao'] === 'abrir_apex') {
    $arquivo = $_GET['arquivo'] ?? '';
    // Sanitização básica: só nome de arquivo, sem path traversal
    $arquivo = basename($arquivo);
    if ($arquivo === '' || !preg_match('/\.apex$/i', $arquivo)) {
        echo "Arquivo invalido.";
        exit;
    }
    // Busca em locais comuns: pasta corrente + pasta pai (caso runtime/)
    $candidatos = [
    __DIR__ . '/' . $arquivo,
    __DIR__ . '/../' . $arquivo,
    __DIR__ . '/../fontes-apex/' . $arquivo,
    './' . $arquivo,
    '../' . $arquivo,
    '../fontes-apex/' . $arquivo
];
    $caminho = null;
    foreach ($candidatos as $c) {
        if (file_exists($c)) { $caminho = $c; break; }
    }
    if ($caminho === null) {
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Erro</title></head>";
        echo "<body style='background:#0a0c12;color:#e6e8ee;font-family:sans-serif;padding:2rem'>";
        echo "<h1 style='color:#fc8181'>Arquivo nao encontrado</h1>";
        echo "<p>O arquivo <code>" . htmlspecialchars($arquivo) . "</code> nao foi encontrado.</p>";
        echo "<p>Salve-o na mesma pasta dos outros arquivos .apex.</p>";
        echo "<p><a style='color:#00D9C5' href='javascript:history.back()'>Voltar</a></p>";
        echo "</body></html>";
        exit;
    }
    $codigo = file_get_contents($caminho);
    $perguntasComTipo = extrairPerguntasComTipo($codigo);

    if (!empty($perguntasComTipo)) {
        renderFormulario($codigo, $perguntasComTipo, $arquivo);
    } else {
        $resultado = analisarEexecutar($codigo, []);
        renderResultado($resultado, $codigo, false, $arquivo);
    }
    exit;
}

// =====================================================================
// Rota "sistema": recebe codigo via GET (base64) e roda direto como sistema
// (usada pela extensao VS Code ao apertar F5)
// =====================================================================
if (isset($_GET['acao']) && $_GET['acao'] === 'sistema') {
    $codigoB64 = $_GET['codigo'] ?? '';
    $codigo = base64_decode($codigoB64);
    if ($codigo === false || trim($codigo) === '') {
        echo "Codigo vazio ou invalido.";
        exit;
    }

    $perguntasComTipo = extrairPerguntasComTipo($codigo);

    if (!empty($perguntasComTipo)) {
        // Tem ler()/ler_numero() -> mostra o formulario do sistema
        renderFormulario($codigo, $perguntasComTipo);
    } else {
        // Nao tem entrada -> executa direto e mostra o resultado
        $resultado = analisarEexecutar($codigo, []);
        renderResultado($resultado, $codigo, false);
    }
    exit;
}

// =====================================================================
// FUNÇÃO: extrai perguntas das chamadas ler("texto")
// =====================================================================
function extrairPerguntas($codigo) {
    // Retorna lista no formato ["pergunta1", "pergunta2"] — só textos
    preg_match_all('/(ler_numero|ler)\s*\(\s*"([^"]*)"\s*\)/', $codigo, $m);
    return $m[2];
}

function extrairPerguntasComTipo($codigo) {
    // Versão que devolve [['tipo' => 'ler_numero', 'texto' => '...'], ...]
    preg_match_all('/(ler_numero|ler)\s*\(\s*"([^"]*)"\s*\)/', $codigo, $m, PREG_SET_ORDER);
    $out = [];
    foreach ($m as $match) {
        $out[] = ['tipo' => $match[1], 'texto' => $match[2]];
    }
    return $out;
}
// =====================================================================
// FUNÇÃO: remove textos entre aspas antes de validar variáveis
// Isso evita que exemplos como "$nome" dentro de paragrafo("...") gerem erro
// =====================================================================
function apex_remover_strings($linha) {
    return preg_replace('/"([^"\\\\]|\\\\.)*"/', '""', $linha);
}
// =====================================================================
// FUNÇÃO: validarApenas — faz só análise léxica/sintática, sem executar
// Reutiliza a mesma lógica do analisarEexecutar mas para na validação.
// =====================================================================
function validarApenas($codigo) {
    $erros = [];
    $linhas = explode("\n", $codigo);

    $temAbertura = false;
    $temFechamento = false;
    foreach ($linhas as $i => $l) {
        if (strpos($l, '<?apex') !== false) $temAbertura = true;
        if (strpos($l, 'fim?>') !== false)  $temFechamento = true;
    }
    if (!$temAbertura)   $erros[] = ['linha' => 1, 'msg' => 'Programa não inicia com "<?apex".'];
    if (!$temFechamento) $erros[] = ['linha' => count($linhas), 'msg' => 'Programa não finaliza com "fim?>".'];

    $variaveis = [];

    foreach ($linhas as $i => $linha) {
        $num = $i + 1;
        $l = trim($linha);

        if ($l === '' || strpos($l, '<?apex') !== false || strpos($l, 'fim?>') !== false
            || strpos($l, '//') === 0 || $l === '{' || $l === '}' || $l === '} senao {') continue;

        if (substr_count($l, '(') !== substr_count($l, ')')) {
            $erros[] = ['linha' => $num, 'msg' => 'Parênteses não balanceados — verifique `(` e `)`.'];
            continue;
        }

        $ehAtribuicao = preg_match('/^\$[a-zA-Z][a-zA-Z0-9_]*\s*=/', $l);
        $ehExibir     = preg_match('/^exibir\s*\(/', $l);
        $ehSe         = preg_match('/^se\s*\(/', $l);
        $ehEnquanto   = preg_match('/^enquanto\s*\(/', $l);
        $ehComandoUI  = preg_match('/^(pagina|titulo|subtitulo|paragrafo|botao|imagem|link|caixa_inicio|caixa_fim|separador|espaco)\s*\(/', $l);

        if ($ehAtribuicao || $ehExibir || $ehComandoUI) {
            if (substr(rtrim($l), -1) !== ';') {
                $erros[] = ['linha' => $num, 'msg' => 'Falta `;` no final da instrução.'];
            }
        }

        if ($ehSe && !preg_match('/^se\s*\(.+\)\s*(\{.*\}?|\{?)\s*$/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => 'Comando `se` mal formado. Use: se (condição) {'];
        }

        if ($ehEnquanto && !preg_match('/^enquanto\s*\(.+\)\s*(\{.*\}?|\{?)\s*$/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => 'Comando `enquanto` mal formado. Use: enquanto (condição) {'];
        }

        if ($ehAtribuicao) {
            if (preg_match('/^\$([a-zA-Z][a-zA-Z0-9_]*)\s*=/', $l, $m)) {
                $variaveis[$m[1]] = true;
            }
        }

        if (preg_match('/\bsenão\b/u', $l)) {
            $erros[] = ['linha' => $num, 'msg' => 'Use `senao` (sem til) em vez de `senão`.'];
        }
        if (preg_match('/\becho\b/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`echo` não existe na APEX. Use `exibir()`.'];
        }
        if (preg_match('/\bif\s*\(/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`if` não existe na APEX. Use `se(...)`.'];
        }
        if (preg_match('/^else\b|\selse\b/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`else` não existe na APEX. Use `senao`.'];
        }
        if (preg_match('/\bwhile\s*\(/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`while` não existe na APEX. Use `enquanto(...)`.'];
        }
        if (preg_match('/\bfor\s*\(/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`for` não existe na APEX. Use `enquanto(...)`.'];
        }

        if ($ehExibir || $ehSe || $ehEnquanto || $ehComandoUI) {
    // Remove textos entre aspas antes de procurar variáveis.
    // Assim, paragrafo("Exemplo: $nome") não gera erro falso.
    $linhaParaValidar = apex_remover_strings($l);

    preg_match_all('/\$([a-zA-Z][a-zA-Z0-9_]*)/', $linhaParaValidar, $usados);

    foreach ($usados[1] as $u) {
        if (!isset($variaveis[$u])) {
            $erros[] = ['linha' => $num, 'msg' => "Variável \$$u usada sem ter sido declarada."];
        }
    }
}

    }

    return $erros;
}

// =====================================================================
// MOTOR APEX — análise léxica, sintática e execução
// =====================================================================
function analisarEexecutar($codigo, $entradas = []) {
    $erros = [];
    $linhas = explode("\n", $codigo);

    $temAbertura = false;
    $temFechamento = false;
    foreach ($linhas as $i => $l) {
        if (strpos($l, '<?apex') !== false) $temAbertura = true;
        if (strpos($l, 'fim?>') !== false)  $temFechamento = true;
    }
    if (!$temAbertura)   $erros[] = ['linha' => 1, 'msg' => 'Programa não inicia com "<?apex".'];
    if (!$temFechamento) $erros[] = ['linha' => count($linhas), 'msg' => 'Programa não finaliza com "fim?>".'];

    $variaveis = [];

    foreach ($linhas as $i => $linha) {
        $num = $i + 1;
        $l = trim($linha);

        if ($l === '' || strpos($l, '<?apex') !== false || strpos($l, 'fim?>') !== false
            || strpos($l, '//') === 0) {
            continue;
        }

        if (substr_count($l, '"') % 2 !== 0) {
            $erros[] = ['linha' => $num, 'msg' => 'Aspas não fechadas — esperava `"` para fechar o texto.'];
            continue;
        }

        if (substr_count($l, '(') !== substr_count($l, ')')) {
            $erros[] = ['linha' => $num, 'msg' => 'Parênteses não balanceados — verifique `(` e `)`.'];
            continue;
        }

        $ehAtribuicao = preg_match('/^\$[a-zA-Z][a-zA-Z0-9_]*\s*=/', $l);
        $ehExibir     = preg_match('/^exibir\s*\(/', $l);
        $ehSe         = preg_match('/^se\s*\(/', $l);
        $ehEnquanto   = preg_match('/^enquanto\s*\(/', $l);
        // Comandos de UI (geram HTML) — também são instruções que devem terminar em ;
        $ehComandoUI  = preg_match('/^(pagina|titulo|subtitulo|paragrafo|botao|imagem|link|caixa_inicio|caixa_fim|separador|espaco)\s*\(/', $l);

        if ($ehAtribuicao || $ehExibir || $ehComandoUI) {
            if (substr(rtrim($l), -1) !== ';') {
                $erros[] = ['linha' => $num, 'msg' => 'Falta `;` no final da instrução.'];
            }
        }

        if ($ehSe && !preg_match('/^se\s*\(.+\)\s*(\{.*\}?|\{?)\s*$/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => 'Comando `se` mal formado. Use: se (condição) {'];
        }

        if ($ehEnquanto && !preg_match('/^enquanto\s*\(.+\)\s*(\{.*\}?|\{?)\s*$/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => 'Comando `enquanto` mal formado. Use: enquanto (condição) {'];
        }

        if ($ehAtribuicao) {
            if (preg_match('/^\$([a-zA-Z][a-zA-Z0-9_]*)\s*=/', $l, $m)) {
                $variaveis[$m[1]] = true;
            }
        }

        if (preg_match('/\bsenão\b/u', $l)) {
            $erros[] = ['linha' => $num, 'msg' => 'Use `senao` (sem til) em vez de `senão`.'];
        }
        if (preg_match('/\becho\b/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`echo` não existe na APEX. Use `exibir()`.'];
        }
        if (preg_match('/\bif\s*\(/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`if` não existe na APEX. Use `se(...)`.'];
        }
        if (preg_match('/^else\b|\selse\b/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`else` não existe na APEX. Use `senao`.'];
        }
        if (preg_match('/\bwhile\s*\(/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`while` não existe na APEX. Use `enquanto(...)`.'];
        }
        if (preg_match('/\bfor\s*\(/', $l)) {
            $erros[] = ['linha' => $num, 'msg' => '`for` não existe na APEX. Use `enquanto(...)`.'];
        }

        if ($ehExibir || $ehSe || $ehEnquanto || $ehComandoUI) {
    // Remove textos entre aspas antes de procurar variáveis.
    // Assim, paragrafo("Exemplo: $nome") não gera erro falso.
    $linhaParaValidar = apex_remover_strings($l);

    preg_match_all('/\$([a-zA-Z][a-zA-Z0-9_]*)/', $linhaParaValidar, $usados);

    foreach ($usados[1] as $u) {
        if (!isset($variaveis[$u])) {
            $erros[] = ['linha' => $num, 'msg' => "Variável \$$u usada sem ter sido declarada."];
        }
    }
}

    }

    if (!empty($erros)) {
        return ['ok' => false, 'erros' => $erros, 'saida' => '', 'simbolos' => array_keys($variaveis), 'leituras' => []];
    }

    try {
        $perguntasComTipo = extrairPerguntasComTipo($codigo);
        $php = transpilarApex($codigo, $entradas, $perguntasComTipo);
        $tmp = tempnam(sys_get_temp_dir(), 'apex_') . '.php';
        file_put_contents($tmp, $php);

        ob_start();
        try {
            include $tmp;
            $saida = ob_get_clean();
        } catch (Throwable $e) {
            // Descarta qualquer saída parcial e relança para o catch externo
            ob_end_clean();
            throw $e;
        }
        @unlink($tmp);

        return ['ok' => true, 'erros' => [], 'saida' => $saida,
                'simbolos' => array_keys($variaveis), 'leituras' => extrairPerguntas($codigo)];
    } catch (Throwable $e) {
        // Garante que nenhum buffer fique pendurado
        while (ob_get_level() > 0) { @ob_end_clean(); }
        return ['ok' => false,
                'erros' => [['linha' => 1, 'msg' => 'Erro de execução: ' . $e->getMessage()]],
                'saida' => '', 'simbolos' => array_keys($variaveis), 'leituras' => []];
    }
}

// =====================================================================
// TRANSPILADOR APEX → PHP (v1.2 com mais funções)
// =====================================================================
function transpilarApex($codigo, $entradas = [], $perguntas = []) {

    // 1. Primeiro protege todos os textos entre aspas.
    // Isso impede que exemplos dentro de paragrafo() sejam transpilados como código real.
    $stringsApex = [];

    $codigo = preg_replace_callback('/"([^"\\\\]|\\\\.)*"/', function ($m) use (&$stringsApex) {
        $chave = '__APEX_STR_' . count($stringsApex) . '__';

        // Evita que PHP tente interpretar variáveis dentro de textos da documentação.
        $texto = preg_replace_callback('/(?<!\\\\)\$/', function () {
            return '\\$';
        }, $m[0]);

        $stringsApex[$chave] = $texto;
        return $chave;
    }, $codigo);

    // 2. Só agora converte os delimitadores reais do programa.
    // Como os textos já estão protegidos, exemplos da documentação continuam aparecendo como APEX.
    $codigo = str_replace('<?apex', '<?php', $codigo);
    $codigo = str_replace('fim?>', '?>', $codigo);

    $codigo = preg_replace('/\bsenao\b/', 'else', $codigo);
    $codigo = preg_replace('/\bse\s*\(/', 'if (', $codigo);
    $codigo = preg_replace('/\benquanto\s*\(/', 'while (', $codigo);

// ler("pergunta") → __apex_ler(indice)
$contador = 0;
$codigo = preg_replace_callback('/ler\s*\(\s*__APEX_STR_\d+__\s*\)/', function ($m) use (&$contador) {
    $idx = $contador++;
    return "__apex_ler($idx)";
}, $codigo);

// ler_numero("pergunta") → __apex_ler_numero(indice)
$contadorN = 0;
$codigo = preg_replace_callback('/ler_numero\s*\(\s*__APEX_STR_\d+__\s*\)/', function ($m) use (&$contadorN, &$contador) {
    $idx = $contador++;
    return "__apex_ler_numero($idx)";
}, $codigo);

    // Funções nativas: traduzem direto para PHP
    // Usamos versões nativas (não-mbstring) para garantir compatibilidade com qualquer instalação PHP
    $codigo = preg_replace('/\bmaiusculas\s*\(/', 'strtoupper(', $codigo);
    $codigo = preg_replace('/\bminusculas\s*\(/', 'strtolower(', $codigo);
    $codigo = preg_replace('/\baleatorio\s*\(/', 'mt_rand(', $codigo);
    $codigo = preg_replace('/\barredondar\s*\(/', 'round(', $codigo);
    $codigo = preg_replace('/\btamanho\s*\(/', 'strlen(', $codigo);

    // Comandos de UI da APEX -> chamam funções helper que geram HTML
    $codigo = preg_replace('/\bpagina\s*\(/', '__apex_pagina(', $codigo);
    $codigo = preg_replace('/\btitulo\s*\(/', '__apex_titulo(', $codigo);
    $codigo = preg_replace('/\bsubtitulo\s*\(/', '__apex_subtitulo(', $codigo);
    $codigo = preg_replace('/\bparagrafo\s*\(/', '__apex_paragrafo(', $codigo);
    $codigo = preg_replace('/\bbotao\s*\(/', '__apex_botao(', $codigo);
    $codigo = preg_replace('/\bimagem\s*\(/', '__apex_imagem(', $codigo);
    $codigo = preg_replace('/\blink\s*\(/', '__apex_link(', $codigo);
    $codigo = preg_replace('/\bcaixa_inicio\s*\(/', '__apex_caixa_inicio(', $codigo);
    $codigo = preg_replace('/\bcaixa_fim\s*\(/', '__apex_caixa_fim(', $codigo);
    $codigo = preg_replace('/\bseparador\s*\(/', '__apex_separador(', $codigo);
    $codigo = preg_replace('/\bespaco\s*\(/', '__apex_espaco(', $codigo);

    // exibir(...) → echo ...;
    $codigo = preg_replace_callback('/exibir\s*\((.*)\)\s*;/', function ($m) {
        return 'echo ' . $m[1] . ' . PHP_EOL;';
    }, $codigo);

// "+" em strings → "." (concatenação)
$linhas = explode("\n", $codigo);
foreach ($linhas as $i => $linha) {
    if (
        strpos($linha, '"') !== false ||
        strpos($linha, "'") !== false ||
        strpos($linha, '__APEX_STR_') !== false
    ) {
        $linha = preg_replace('/(?<![+\.])\+(?![+=])/', '.', $linha);
        $linhas[$i] = $linha;
    }
}
$codigo = implode("\n", $linhas);

// Restaura os textos protegidos depois das conversões
$codigo = strtr($codigo, $stringsApex);

    // Detecta quais índices são numéricos: ler_numero ou ler("numero:...")
    $indicesNumericos = [];
    foreach ($perguntas as $i => $p) {
        // $p pode ser string (formato antigo) ou ['tipo'=>, 'texto'=>] (formato novo)
        if (is_array($p)) {
            if ($p['tipo'] === 'ler_numero' || strpos($p['texto'], 'numero:') === 0) {
                $indicesNumericos[$i] = true;
            }
        } else {
            if (strpos($p, 'numero:') === 0) {
                $indicesNumericos[$i] = true;
            }
        }
    }

    // Injeta o prelúdio com as funções da APEX
    $entradasPhp = var_export(array_values($entradas), true);
    $numericosPhp = var_export($indicesNumericos, true);
    $tag = uniqid('apex_');

    // Bloco com funções de UI da APEX (cada uma gera HTML estilizado)
    // Usa nowdoc <<<'EOT' pra preservar literalmente sem expansão de variáveis PHP
    $funcoesUI = <<<'PHPCODE'

if (!function_exists('__apex_html_iniciar')) {
    function __apex_html_iniciar($titulo = 'Pagina APEX') {
        // Se já iniciou (chamadas múltiplas de pagina()), só atualiza título
        if (isset($GLOBALS['__apex_html_started'])) return;
        $GLOBALS['__apex_html_started'] = true;
        $GLOBALS['__apex_titulo_pagina'] = $titulo;
        $h = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
        echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>' . $h . ' · APEX</title>';
        echo '<style>';
        echo '*{margin:0;padding:0;box-sizing:border-box}';
        echo 'body{background:#0a0c12;color:#e6e8ee;font-family:"Segoe UI",system-ui,sans-serif;min-height:100vh;padding:2rem 1rem}';
        echo '.apex-container{max-width:900px;margin:0 auto}';
        echo '.apex-h1{font-size:2.4rem;font-weight:700;color:#00D9C5;margin:1.2rem 0 .6rem;letter-spacing:-.02em}';
        echo '.apex-h2{font-size:1.5rem;font-weight:600;color:#e6e8ee;margin:1rem 0 .5rem}';
        echo '.apex-p{font-size:1.05rem;line-height:1.7;color:#c3c7d1;margin:.6rem 0;white-space:pre-wrap}';
        echo '.apex-btn{display:inline-block;padding:.85rem 1.6rem;background:#00D9C5;color:#0a0c12;border:none;border-radius:8px;font-size:1rem;font-weight:600;text-decoration:none;cursor:pointer;margin:.4rem .4rem .4rem 0;transition:transform .15s ease, background .15s ease}';
        echo '.apex-btn:hover{background:#00f0d8;transform:translateY(-1px)}';
        echo '.apex-link{color:#00D9C5;text-decoration:underline;text-underline-offset:3px}';
        echo '.apex-link:hover{color:#00f0d8}';
        echo '.apex-img{max-width:100%;height:auto;border-radius:8px;margin:.8rem 0;border:1px solid #232733}';
        echo '.apex-card{background:#13161f;border:1px solid #232733;border-radius:12px;padding:1.4rem 1.6rem;margin:1rem 0}';
        echo '.apex-sep{border:none;border-top:1px solid #232733;margin:1.6rem 0}';
        echo '.apex-space{height:1.2rem}';
        echo '</style></head><body><div class="apex-container">';
    }
    function __apex_html_finalizar() {
        if (isset($GLOBALS['__apex_html_started'])) {
            echo '</div></body></html>';
        }
    }
    register_shutdown_function('__apex_html_finalizar');
}

if (!function_exists('__apex_pagina')) {
    function __apex_pagina($titulo) {
        __apex_html_iniciar($titulo);
    }
}
if (!function_exists('__apex_titulo')) {
    function __apex_titulo($texto) {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');
        echo '<h1 class="apex-h1">' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</h1>';
    }
}
if (!function_exists('__apex_subtitulo')) {
    function __apex_subtitulo($texto) {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');
        echo '<h2 class="apex-h2">' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</h2>';
    }
}
if (!function_exists('__apex_paragrafo')) {
    function __apex_paragrafo($texto) {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');
        echo '<p class="apex-p">' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</p>';
    }
}
if (!function_exists('__apex_botao')) {
    function __apex_botao($texto, $destino = '') {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');

        $tx = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');

        if ($destino !== '') {

            // Se for uma página .apex, abre pela rota amigável da APEX
            if (preg_match('/\.apex$/i', $destino)) {
                $url = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES, 'UTF-8') . '?acao=abrir_apex&arquivo=' . urlencode(basename($destino));
                $download = '';
            } else {
                // Se for arquivo comum, como .zip ou .vsix, baixa/abre direto
                $url = htmlspecialchars($destino, ENT_QUOTES, 'UTF-8');
                $download = preg_match('/\.(zip|vsix|pdf|rar|7z|exe|msi)$/i', $destino) ? ' download' : '';
            }

            echo '<a class="apex-btn" href="' . $url . '"' . $download . '>' . $tx . '</a>';

        } else {
            echo '<button class="apex-btn">' . $tx . '</button>';
        }
    }
}
if (!function_exists('__apex_imagem')) {
    function __apex_imagem($url, $descricao = '') {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');

        // Ajuste fixo para o projeto rodando em:
        // http://localhost/apexsite/demo-apex/
        if (!preg_match('/^(https?:\/\/|data:|\/)/i', $url)) {
            $url = '/apexsite/demo-apex/' . ltrim($url, '/');
        }

        $u = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $d = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');

        echo '<img class="apex-img" src="' . $u . '" alt="' . $d . '">';
    }
}
if (!function_exists('__apex_link')) {
    function __apex_link($texto, $destino) {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');

        $tx = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');

        if (preg_match('/\.apex$/i', $destino)) {
            $url = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES, 'UTF-8') . '?acao=abrir_apex&arquivo=' . urlencode(basename($destino));
            $download = '';
        } else {
            $url = htmlspecialchars($destino, ENT_QUOTES, 'UTF-8');
            $download = preg_match('/\.(zip|vsix|pdf|rar|7z|exe|msi)$/i', $destino) ? ' download' : '';
        }

        echo '<a class="apex-link" href="' . $url . '"' . $download . '>' . $tx . '</a>';
    }
}
if (!function_exists('__apex_caixa_inicio')) {
    function __apex_caixa_inicio() {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');
        echo '<div class="apex-card">';
    }
}
if (!function_exists('__apex_caixa_fim')) {
    function __apex_caixa_fim() {
        echo '</div>';
    }
}
if (!function_exists('__apex_separador')) {
    function __apex_separador() {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');
        echo '<hr class="apex-sep">';
    }
}
if (!function_exists('__apex_espaco')) {
    function __apex_espaco() {
        __apex_html_iniciar($GLOBALS['__apex_titulo_pagina'] ?? 'Pagina APEX');
        echo '<div class="apex-space"></div>';
    }
}
PHPCODE;

    $preludio = "\n"
              . "\$GLOBALS['{$tag}_e'] = $entradasPhp;\n"
              . "\$GLOBALS['{$tag}_n'] = $numericosPhp;\n"
              . "\$GLOBALS['__apex_tag'] = '$tag';\n"
              . "if (!function_exists('__apex_ler')) {\n"
              . "    function __apex_ler(\$idx) {\n"
              . "        \$tag = \$GLOBALS['__apex_tag'];\n"
              . "        \$entradas = \$GLOBALS[\$tag . '_e'] ?? [];\n"
              . "        \$numericos = \$GLOBALS[\$tag . '_n'] ?? [];\n"
              . "        \$v = \$entradas[\$idx] ?? '';\n"
              . "        if (isset(\$numericos[\$idx])) {\n"
              . "            return is_numeric(\$v) ? \$v + 0 : 0;\n"
              . "        }\n"
              . "        return is_numeric(\$v) ? \$v + 0 : \$v;\n"
              . "    }\n"
              . "}\n"
              . "if (!function_exists('__apex_ler_numero')) {\n"
              . "    function __apex_ler_numero(\$idx) {\n"
              . "        \$tag = \$GLOBALS['__apex_tag'];\n"
              . "        \$entradas = \$GLOBALS[\$tag . '_e'] ?? [];\n"
              . "        \$v = \$entradas[\$idx] ?? '0';\n"
              . "        return is_numeric(\$v) ? \$v + 0 : 0;\n"
              . "    }\n"
              . "}\n"
              . $funcoesUI;
    $codigo = preg_replace('/<\?php/', "<?php$preludio", $codigo, 1);

    return $codigo;
}

// =====================================================================
// RENDER: formulário do modo "Abrir como site"
// =====================================================================
function renderFormulario($codigo, $perguntas, $nomeArquivo = 'Sistema') {
    // Monta título amigável: "restaurante.apex" -> "Restaurante"
    $tituloLimpo = preg_replace('/\.apex$/', '', $nomeArquivo);
    $tituloLimpo = ucfirst($tituloLimpo) ?: 'Sistema';
    ?><!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($tituloLimpo) ?> · APEX</title>
        <?php emitirCssSite(); ?>
    </head>
    <body>
        <header class="page-header">
            <h1><?= htmlspecialchars(strtoupper($tituloLimpo)) ?> <small>sistema interativo em APEX</small></h1>
        </header>
        <main class="page-main">
            <div class="card">
                <div class="card-title">Preencha os dados</div>
                <p class="card-subtitle">O programa em APEX precisa destas informações para executar.</p>
                <form method="POST" action="?acao=site" class="apex-form">
                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($codigo) ?>">
                    <input type="hidden" name="nome" value="<?= htmlspecialchars($nomeArquivo) ?>">
                    <?php foreach ($perguntas as $i => $p): ?>
                        <?php
                        // Compatibilidade: aceita string ou array
                        $tipo  = is_array($p) ? $p['tipo']  : 'ler';
                        $texto = is_array($p) ? $p['texto'] : $p;
                        renderCampo($i, $texto, $tipo);
                        ?>
                    <?php endforeach; ?>
                    <button type="submit" class="btn-submit">Executar programa</button>
                </form>
            </div>
        </main>
    </body>
    </html>
    <?php
}

// =====================================================================
// RENDER: campo do formulário — detecta cardápio automaticamente
// =====================================================================
function renderCampo($i, $pergunta, $tipo = 'ler') {
    // Cardapio: grid de cards
    if (strpos($pergunta, 'cardapio:') === 0) {
        $opcoes = substr($pergunta, strlen('cardapio:'));
        $itens = array_filter(array_map('trim', explode('|', $opcoes)));
        ?>
        <div class="field">
            <label class="field-label">Escolha o produto</label>
            <div class="menu-grid">
                <?php foreach ($itens as $idx => $item): ?>
                    <?php list($nome, $preco) = array_pad(explode('=', $item), 2, '0'); ?>
                    <label class="menu-card">
                        <input type="radio" name="respostas[<?= $i ?>]" value="<?= htmlspecialchars($nome) ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                        <div class="menu-info">
                            <div class="menu-name"><?= htmlspecialchars($nome) ?></div>
                            <div class="menu-price">R$ <?= number_format((float)$preco, 2, ',', '.') ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return;
    }

    // Campo numérico: ler_numero(...) OU ler("numero:...")
    if ($tipo === 'ler_numero' || strpos($pergunta, 'numero:') === 0) {
        $rotulo = (strpos($pergunta, 'numero:') === 0)
            ? substr($pergunta, strlen('numero:'))
            : $pergunta;
        ?>
        <div class="field">
            <label class="field-label" for="r<?= $i ?>"><?= htmlspecialchars($rotulo) ?></label>
            <input type="number" id="r<?= $i ?>" name="respostas[<?= $i ?>]" value="1" min="1" required class="field-input">
        </div>
        <?php
        return;
    }

    // Campo de texto: detecta prefixo "opcional:" para campos não-obrigatorios
    $ehOpcional = (strpos($pergunta, 'opcional:') === 0);
    $rotulo = $ehOpcional ? substr($pergunta, strlen('opcional:')) : $pergunta;
    ?>
    <div class="field">
        <label class="field-label" for="r<?= $i ?>"><?= htmlspecialchars($rotulo) ?></label>
        <input type="text" id="r<?= $i ?>" name="respostas[<?= $i ?>]" <?= $ehOpcional ? '' : 'required' ?> class="field-input" placeholder="<?= $ehOpcional ? 'Opcional - deixe em branco se nao quiser' : '' ?>">
    </div>
    <?php
}

// =====================================================================
// RENDER: resultado final do modo "site"
// =====================================================================
function renderResultado($resultado, $codigo, $temFormulario, $nomeArquivo = 'Sistema') {
    // Se o programa gerou HTML completo (usou pagina/titulo/botao/etc),
    // emite a saída direto, sem encapsular em outro layout.
    if ($resultado['ok'] && !empty($resultado['saida']) && stripos(ltrim($resultado['saida']), '<!DOCTYPE') === 0) {
        echo $resultado['saida'];
        return;
    }

    $tituloLimpo = preg_replace('/\.apex$/', '', $nomeArquivo);
    $tituloLimpo = ucfirst($tituloLimpo) ?: 'Sistema';
    ?><!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($tituloLimpo) ?> · APEX</title>
        <?php emitirCssSite(); ?>
    </head>
    <body>
        <header class="page-header">
            <h1><?= htmlspecialchars(strtoupper($tituloLimpo)) ?> <small>resultado da execução</small></h1>
        </header>
        <main class="page-main">
            <div class="card">
                <?php if (!$resultado['ok']): ?>
                    <div class="card-title danger">Erros no programa</div>
                    <?php foreach ($resultado['erros'] as $e): ?>
                        <div class="error-box">
                            <strong>Linha <?= $e['linha'] ?>:</strong>
                            <?= htmlspecialchars($e['msg']) ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card-title">Saída do programa</div>
                    <pre class="output-box"><?= htmlspecialchars($resultado['saida']) ?></pre>
                <?php endif; ?>

                <?php if ($temFormulario): ?>
                    <form method="POST" action="?acao=site" style="margin-top: 1.5rem;">
                        <input type="hidden" name="codigo" value="<?= htmlspecialchars($codigo) ?>">
                        <input type="hidden" name="nome" value="<?= htmlspecialchars($nomeArquivo) ?>">
                        <button type="submit" class="btn-submit secondary">Fazer novo pedido</button>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </body>
    </html>
    <?php
}

// =====================================================================
// CSS do modo "site"
// =====================================================================
function emitirCssSite() {
    ?>
    <style>
        :root {
            --bg: #0F1117; --panel: #1A1D29; --border: #2D3142;
            --text: #F8F9FC; --mute: #8B92A8; --accent: #00D9C5;
            --accent-dim: #00A89A; --err: #FC8181;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg);
               color: var(--text); min-height: 100vh; }
        .page-header { border-bottom: 2px solid var(--accent); padding: 1.5rem 2rem;
                       background: #0A0B10; }
        .page-header h1 { font-size: 1.4rem; color: var(--accent); letter-spacing: 2px; }
        .page-header h1 small { color: var(--mute); font-weight: normal;
                                letter-spacing: 0; font-size: 0.85rem; margin-left: 1rem; }
        .page-main { padding: 2rem; max-width: 720px; margin: 0 auto; }
        .card { background: var(--panel); border: 1px solid var(--border);
                border-radius: 8px; padding: 2rem; }
        .card-title { font-size: 1.3rem; font-weight: 600; margin-bottom: 0.4rem;
                      color: var(--accent); }
        .card-title.danger { color: var(--err); }
        .card-subtitle { color: var(--mute); font-size: 0.9rem; margin-bottom: 1.5rem; }

        .field { margin-bottom: 1.4rem; }
        .field-label { display: block; font-weight: 600; margin-bottom: 0.6rem;
                       color: var(--text); font-size: 0.95rem; }
        .field-input { width: 100%; padding: 0.7rem 0.9rem; background: #0F1117;
                       border: 1px solid var(--border); border-radius: 6px;
                       color: var(--text); font-size: 1rem; font-family: inherit; }
        .field-input:focus { outline: none; border-color: var(--accent); }

        .menu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; }
        .menu-card { display: flex; align-items: center; gap: 0.7rem;
                     padding: 0.9rem 1rem; background: #0F1117;
                     border: 2px solid var(--border); border-radius: 6px;
                     cursor: pointer; transition: all 0.15s; }
        .menu-card:hover { border-color: var(--accent); }
        .menu-card input { accent-color: var(--accent); transform: scale(1.2); }
        .menu-card:has(input:checked) { border-color: var(--accent);
                                        background: rgba(0, 217, 197, 0.05); }
        .menu-info { flex: 1; }
        .menu-name { font-weight: 600; font-size: 0.95rem; }
        .menu-price { color: var(--mute); font-size: 0.85rem; margin-top: 2px; }

        .btn-submit { width: 100%; padding: 0.9rem; background: var(--accent);
                      color: var(--bg); border: none; border-radius: 6px;
                      font-size: 1rem; font-weight: 600; cursor: pointer;
                      font-family: inherit; transition: background 0.15s; }
        .btn-submit:hover { background: var(--accent-dim); }
        .btn-submit.secondary { background: transparent; color: var(--accent);
                                border: 1px solid var(--accent); }
        .btn-submit.secondary:hover { background: rgba(0, 217, 197, 0.1); }

        .output-box { background: #0A0B10; padding: 1.2rem; border-radius: 6px;
                      font-family: Consolas, monospace; font-size: 0.95rem;
                      line-height: 1.7; white-space: pre-wrap; word-break: break-word;
                      border-left: 3px solid var(--accent); }
        .error-box { background: rgba(252, 129, 129, 0.08); border-left: 3px solid var(--err);
                     padding: 0.9rem 1rem; margin-bottom: 0.7rem; border-radius: 4px; }
        .error-box strong { color: var(--err); margin-right: 0.5rem; }
    </style>
    <?php
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APEX IDE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #0A0B10; --bg-base: #0F1117; --bg-panel: #1A1D29;
            --bg-elev: #232734; --border: #2D3142;
            --text: #F8F9FC; --text-mute: #8B92A8; --text-dim: #5B6275;
            --accent: #00D9C5; --accent-dim: #00A89A;
            --kw: #F6AD55; --var: #B794F6; --str: #FC8181;
            --num: #68D391; --com: #5B6275; --op: #00D9C5;
            --err: #FC8181; --ok: #68D391;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }
        body { font-family: 'Outfit', system-ui, sans-serif; background: var(--bg-base);
               color: var(--text); display: flex; flex-direction: column;
               -webkit-font-smoothing: antialiased; }
        header { display: flex; align-items: center; justify-content: space-between;
                 padding: 0.7rem 1.2rem; background: var(--bg-deep);
                 border-bottom: 1px solid var(--border); flex-shrink: 0; }
        .brand { display: flex; align-items: center; gap: 0.7rem; }
        .brand-mark { font-family: 'JetBrains Mono', monospace;
                      color: var(--accent); font-weight: 700; font-size: 1rem; }
        .brand-sep { width: 1px; height: 18px; background: var(--border); }
        .brand-title { font-weight: 600; letter-spacing: 0.5px; font-size: 0.95rem; }
        .brand-tag { font-size: 0.7rem; color: var(--accent); letter-spacing: 2px;
                     border: 1px solid var(--accent); padding: 2px 8px; border-radius: 10px; }
        .toolbar { display: flex; gap: 0.5rem; }
        .btn { font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 500;
               background: var(--bg-elev); color: var(--text); border: 1px solid var(--border);
               padding: 0.45rem 0.9rem; border-radius: 4px; cursor: pointer;
               display: inline-flex; align-items: center; gap: 0.4rem;
               transition: all 0.15s ease; }
        .btn:hover { border-color: var(--accent); color: var(--accent); }
        .btn:active { transform: translateY(1px); }
        .btn.primary { background: var(--accent); color: var(--bg-base);
                       border-color: var(--accent); font-weight: 600; }
        .btn.primary:hover { background: var(--accent-dim); border-color: var(--accent-dim);
                             color: var(--bg-base); }
        .btn.danger { color: var(--err); }
        .btn.danger:hover { border-color: var(--err); }
        .btn svg { width: 14px; height: 14px; }

        main { flex: 1; display: grid; grid-template-columns: 220px 1fr 320px;
               min-height: 0; }

        aside.sidebar { background: var(--bg-deep); border-right: 1px solid var(--border);
                        display: flex; flex-direction: column; min-height: 0; }
        .sidebar-header { padding: 0.9rem 1rem; font-size: 0.7rem; letter-spacing: 3px;
                          color: var(--accent); font-weight: 600;
                          border-bottom: 1px solid var(--border); }
        .examples { padding: 0.5rem; flex: 1; overflow-y: auto; }
        .example-item { display: block; width: 100%; background: transparent;
                        color: var(--text); border: 1px solid transparent;
                        text-align: left; padding: 0.6rem 0.7rem;
                        font-family: 'Outfit', sans-serif; font-size: 0.85rem;
                        cursor: pointer; border-radius: 4px; margin-bottom: 0.2rem;
                        transition: all 0.15s ease; }
        .example-item:hover { background: var(--bg-panel); border-color: var(--border); }
        .example-item.featured { border-color: var(--accent);
                                 background: rgba(0, 217, 197, 0.06); }
        .example-item .ex-title { display: block; font-weight: 500; }
        .example-item .ex-desc { display: block; font-size: 0.72rem;
                                 color: var(--text-dim); margin-top: 2px; }
        .example-item.featured .ex-title { color: var(--accent); }

        .sidebar-info { padding: 1rem; border-top: 1px solid var(--border);
                        font-size: 0.75rem; color: var(--text-dim); line-height: 1.6; }
        .sidebar-info strong { color: var(--accent); font-weight: 600; }

        section.editor-area { display: flex; flex-direction: column;
                              min-width: 0; min-height: 0; background: var(--bg-base); }
        .tab-bar { display: flex; align-items: center; background: var(--bg-deep);
                   border-bottom: 1px solid var(--border); padding: 0 0.5rem;
                   flex-shrink: 0; }
        .tab { padding: 0.55rem 1rem; font-size: 0.8rem; color: var(--text-mute);
               border-bottom: 2px solid transparent;
               display: flex; align-items: center; gap: 0.4rem; }
        .tab.active { color: var(--accent); border-bottom-color: var(--accent); }
        .tab .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); }

        .editor-wrap { flex: 1; position: relative;
                       display: flex; min-height: 0; overflow: hidden; }
        .gutter { background: var(--bg-base); color: var(--text-dim);
                  font-family: 'JetBrains Mono', monospace; font-size: 13px;
                  line-height: 1.6; padding: 1rem 0 1rem 0;
                  user-select: none;
                  border-right: 1px solid var(--border);
                  min-width: 50px; overflow: hidden;
                  display: flex; flex-direction: column; }
        .gutter-line { padding: 0 0.6rem 0 1rem; text-align: right;
                       white-space: pre; position: relative;
                       transition: background 0.15s ease; }
        .gutter-line.has-error { color: var(--err); font-weight: 700;
                                 background: rgba(252, 129, 129, 0.12); cursor: help; }
        .gutter-line.has-error::before { content: "●";
                                         position: absolute; left: 4px;
                                         color: var(--err); font-size: 11px; }
        .code-area { flex: 1; position: relative; overflow: auto; display: flex; }
        .code-stack { position: relative; flex: 1; min-width: 100%; }
        .highlight, .input { position: absolute; top: 0; left: 0; right: 0;
                             padding: 1rem 1.2rem;
                             font-family: 'JetBrains Mono', monospace; font-size: 13px;
                             line-height: 1.6;
                             border: 0; margin: 0; min-height: 100%; }
        .highlight { pointer-events: none; color: var(--text); white-space: normal; }
        .highlight .line { white-space: pre; min-height: 1.6em; }
        .highlight .line.line-error {
            background: rgba(252, 129, 129, 0.10);
            border-left: 2px solid var(--err);
            margin-left: -1.2rem; padding-left: calc(1.2rem - 2px);
            text-decoration: underline wavy var(--err);
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }
        .input { background: transparent; color: transparent; caret-color: var(--accent);
                 outline: none; resize: none; width: 100%; height: 100%; tab-size: 4;
                 white-space: pre; }
        .input::selection { background: rgba(0, 217, 197, 0.3); }

        /* Tooltip de erro (aparece ao passar mouse no gutter) */
        .error-tooltip { position: fixed; z-index: 100;
                         background: #2D1518; color: var(--text);
                         border: 1px solid var(--err);
                         border-left: 3px solid var(--err);
                         padding: 0.6rem 0.85rem;
                         border-radius: 4px;
                         max-width: 380px;
                         font-size: 0.82rem; line-height: 1.5;
                         box-shadow: 0 6px 18px rgba(0,0,0,0.4);
                         pointer-events: none;
                         opacity: 0; transition: opacity 0.12s ease; }
        .error-tooltip.show { opacity: 1; }
        .error-tooltip-line { font-family: 'JetBrains Mono', monospace;
                              font-size: 0.7rem; letter-spacing: 1px;
                              color: var(--err); font-weight: 700;
                              margin-bottom: 4px; }

        .tok-kw   { color: var(--kw); font-weight: 500; }
        .tok-var  { color: var(--var); }
        .tok-str  { color: var(--str); }
        .tok-num  { color: var(--num); }
        .tok-com  { color: var(--com); font-style: italic; }
        .tok-op   { color: var(--op); font-weight: 500; }
        .tok-delim{ color: var(--kw); font-weight: 700; }
        .tok-fn   { color: var(--accent); font-weight: 500; }

        aside.right-panel { background: var(--bg-deep);
                            border-left: 1px solid var(--border);
                            display: flex; flex-direction: column; min-height: 0; }
        .panel-tabs { display: flex; border-bottom: 1px solid var(--border);
                      background: var(--bg-deep); flex-shrink: 0; }
        .panel-tab { flex: 1; padding: 0.7rem; text-align: center;
                     font-size: 0.75rem; letter-spacing: 2px;
                     color: var(--text-mute); cursor: pointer;
                     border-bottom: 2px solid transparent; background: transparent;
                     border-left: 0; border-right: 0; border-top: 0;
                     font-family: 'Outfit', sans-serif;
                     transition: all 0.15s ease; position: relative; }
        .panel-tab.active { color: var(--accent); border-bottom-color: var(--accent); }
        .panel-tab .badge { position: absolute; top: 4px; right: 8px;
                            background: var(--err); color: white;
                            font-size: 0.65rem; padding: 1px 6px; border-radius: 8px;
                            min-width: 16px; line-height: 1.3; }
        .panel-tab .badge.hidden { display: none; }

        .panel-content { flex: 1; overflow-y: auto; padding: 1rem; min-height: 0; }
        .panel-page { display: none; }
        .panel-page.active { display: block; }

        .output { font-family: 'JetBrains Mono', monospace; font-size: 13px;
                  line-height: 1.6; color: var(--text);
                  white-space: pre-wrap; word-break: break-word; }
        .output:empty::before { content: 'Aguardando execução…';
                                color: var(--text-dim); font-style: italic; }

        .error-item { background: rgba(252, 129, 129, 0.08);
                      border-left: 3px solid var(--err);
                      padding: 0.7rem 0.9rem; margin-bottom: 0.6rem;
                      border-radius: 0 4px 4px 0; }
        .error-line { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem;
                      color: var(--err); font-weight: 700; letter-spacing: 1px;
                      margin-bottom: 4px; }
        .error-msg { font-size: 0.85rem; line-height: 1.5; }
        .no-errors { color: var(--ok); font-size: 0.85rem;
                     display: flex; align-items: center; gap: 0.5rem; }
        .no-errors::before { content: '✓'; font-size: 1.2rem; }
        .empty-state { color: var(--text-dim); font-style: italic; font-size: 0.85rem; }

        footer.statusbar { background: var(--bg-deep); border-top: 1px solid var(--border);
                           padding: 0.35rem 1.2rem; font-size: 0.72rem;
                           color: var(--text-mute);
                           display: flex; justify-content: space-between; align-items: center;
                           flex-shrink: 0; }
        footer .left { display: flex; gap: 1.5rem; align-items: center; }
        footer .status-dot { width: 8px; height: 8px; border-radius: 50%;
                             background: var(--ok); display: inline-block; margin-right: 6px; }
        footer .status-dot.err { background: var(--err); }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: var(--bg-base); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--bg-elev); }

        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .running { animation: pulse 1s infinite; }
    </style>
</head>
<body>
    <header>
        <div class="brand">
            <span class="brand-mark">&lt;?apex</span>
            <div class="brand-sep"></div>
            <span class="brand-title">APEX IDE</span>
            <span class="brand-tag">v1.1</span>
        </div>
        <div class="toolbar">
            <button class="btn primary" id="btn-executar" title="Executar (Ctrl+Enter)">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                Executar
            </button>
            <button class="btn" id="btn-site" title="Abrir saída em nova aba como site">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                    <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
                Abrir como site
            </button>
            <button class="btn danger" id="btn-limpar" title="Limpar editor e saída">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                    <line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
                Limpar
            </button>
        </div>
    </header>

    <main>
        <aside class="sidebar">
            <div class="sidebar-header">EXEMPLOS</div>
            <div class="examples" id="examples"></div>
            <div class="sidebar-info">
                <strong>Atalhos</strong><br>
                Ctrl + Enter — Executar<br>
                Ctrl + L — Limpar
            </div>
        </aside>

        <section class="editor-area">
            <div class="tab-bar">
                <div class="tab active">
                    <span class="dot"></span>
                    programa.apex
                </div>
            </div>
            <div class="editor-wrap">
                <div class="gutter" id="gutter"></div>
                <div class="code-area">
                    <div class="code-stack">
                        <pre class="highlight" id="highlight"></pre>
                        <textarea class="input" id="editor" spellcheck="false" autofocus></textarea>
                    </div>
                </div>
            </div>
        </section>

        <aside class="right-panel">
            <div class="panel-tabs">
                <button class="panel-tab active" data-page="saida">SAÍDA</button>
                <button class="panel-tab" data-page="erros">
                    ERROS
                    <span class="badge hidden" id="error-badge">0</span>
                </button>
            </div>
            <div class="panel-content">
                <div class="panel-page active" id="page-saida">
                    <div class="output" id="output"></div>
                </div>
                <div class="panel-page" id="page-erros">
                    <div id="errors-list">
                        <div class="empty-state">Nenhum erro detectado ainda. Execute o código para analisar.</div>
                    </div>
                </div>
            </div>
        </aside>
    </main>

    <footer class="statusbar">
        <div class="left">
            <span><span class="status-dot" id="status-dot"></span><span id="status-text">Pronto</span></span>
            <span id="line-info">Linha 1, Coluna 1</span>
        </div>
        <div>APEX · Linguagem de Programação</div>
    </footer>

    <div class="error-tooltip" id="error-tooltip"></div>

    <script>
        // Helper: tags da APEX em pedacos para nao confundir o parser PHP
        const ABRE = '<' + '?apex';
        const FECHA = 'fim?' + '>';

        const EXEMPLOS = [
            {
                titulo: 'Pizzaria com desconto',
                desc: 'Sistema interativo completo',
                featured: true,
                codigo: ABRE + `
    // Pizzaria APEX: sistema completo de pedido
    $sabor = ler("cardapio:Mussarela=35|Calabresa=40|Portuguesa=45|Frango=42");
    $quantidade = ler_numero("Quantas pizzas?");

    // Calcula preco com base no sabor
    $preco = 35;
    se ($sabor == "Calabresa") { $preco = 40; }
    se ($sabor == "Portuguesa") { $preco = 45; }
    se ($sabor == "Frango") { $preco = 42; }

    $subtotal = $preco * $quantidade;

    // Desconto: 10% se comprar 3 ou mais
    $desconto = 0;
    se ($quantidade >= 3) {
        $desconto = $subtotal * 10 / 100;
    }

    $total = $subtotal - $desconto;
    $pedido = aleatorio(1000, 9999);

    exibir("============================");
    exibir("  PIZZARIA APEX - PEDIDO #" + $pedido);
    exibir("============================");
    exibir("Sabor: " + maiusculas($sabor));
    exibir("Quantidade: " + $quantidade);
    exibir("Subtotal: R$ " + $subtotal);

    se ($desconto > 0) {
        exibir("Desconto (10%): R$ " + $desconto);
    }

    exibir("Total: R$ " + arredondar($total));
    exibir("============================");
` + FECHA
            },
            {
                titulo: 'Olá, mundo',
                desc: 'Primeiro programa',
                codigo: ABRE + `
    exibir("Olá, mundo!");
    exibir("Bem-vindo à linguagem APEX!");
` + FECHA
            },
            {
                titulo: 'Variáveis',
                desc: 'Texto e números',
                codigo: ABRE + `
    $nome = "Gabriel";
    $idade = 22;
    exibir("Nome: " + $nome);
    exibir("Idade: " + $idade);
` + FECHA
            },
            {
                titulo: 'Operação matemática',
                desc: 'Soma e multiplicação',
                codigo: ABRE + `
    $a = 10;
    $b = 5;
    $soma = $a + $b;
    $produto = $a * $b;
    exibir("Soma: " + $soma);
    exibir("Produto: " + $produto);
` + FECHA
            },
            {
                titulo: 'Aprovação',
                desc: 'Condicional se/senao',
                codigo: ABRE + `
    $nota = 8;
    se ($nota >= 7) {
        exibir("Aluno aprovado");
    } senao {
        exibir("Aluno reprovado");
    }
` + FECHA
            },
            {
                titulo: 'Pedido de lanche',
                desc: 'Cálculo de total',
                codigo: ABRE + `
    $produto = "Hamburguer";
    $quantidade = 3;
    $preco = 12;
    $total = $quantidade * $preco;
    exibir("Produto: " + $produto);
    exibir("Quantidade: " + $quantidade);
    exibir("Total: R$ " + $total);
` + FECHA
            },
            {
                titulo: 'Maioridade',
                desc: 'Verificação de idade',
                codigo: ABRE + `
    $idade = 18;
    se ($idade >= 18) {
        exibir("Maior de idade");
    } senao {
        exibir("Menor de idade");
    }
` + FECHA
            },
            {
                titulo: 'Contagem regressiva',
                desc: 'Demonstra o enquanto',
                codigo: ABRE + `
    $i = 5;
    enquanto ($i > 0) {
        exibir("Faltam " + $i + " segundos");
        $i = $i - 1;
    }
    exibir("LANCAMENTO!");
` + FECHA
            },
            {
                titulo: 'Par ou impar',
                desc: 'Usa o operador %',
                codigo: ABRE + `
    $n = 7;
    $resto = $n % 2;
    se ($resto == 0) {
        exibir($n + " e par");
    } senao {
        exibir($n + " e impar");
    }
` + FECHA
            },
            {
                titulo: 'Sorteio',
                desc: 'Numero aleatorio',
                codigo: ABRE + `
    $sorteado = aleatorio(1, 100);
    exibir("Numero sorteado: " + $sorteado);
    se ($sorteado >= 50) {
        exibir("Voce ganhou! Numero alto.");
    } senao {
        exibir("Tente novamente. Numero baixo.");
    }
` + FECHA
            },
            {
                titulo: 'Erro proposital',
                desc: 'Veja a detecção',
                codigo: ABRE + `
    $nome = "teste"
    exibir($idade);
` + FECHA
            },
        ];

        const editor    = document.getElementById('editor');
        const highlight = document.getElementById('highlight');
        const gutter    = document.getElementById('gutter');
        const output    = document.getElementById('output');
        const errorsList= document.getElementById('errors-list');
        const errorBadge= document.getElementById('error-badge');
        const statusDot = document.getElementById('status-dot');
        const statusText= document.getElementById('status-text');
        const lineInfo  = document.getElementById('line-info');

        const examplesContainer = document.getElementById('examples');
        EXEMPLOS.forEach((ex) => {
            const btn = document.createElement('button');
            btn.className = 'example-item' + (ex.featured ? ' featured' : '');
            btn.innerHTML = `<span class="ex-title">${ex.titulo}</span><span class="ex-desc">${ex.desc}</span>`;
            btn.addEventListener('click', () => {
                editor.value = ex.codigo;
                limparErros();
                editor.focus();
            });
            examplesContainer.appendChild(btn);
        });

        function escapeHtml(s) {
            return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function highlightLine(src) {
            // Highlight de uma única linha (sem newline final)
            const tokens = [];
            const placeholder = (i) => `\u0000TOK${i}\u0000`;
            src = src.replace(/\/\/[^\n]*/g, (m) => { tokens.push(['com', m]); return placeholder(tokens.length-1); });
            src = src.replace(/"([^"\\]|\\.)*"/g, (m) => { tokens.push(['str', m]); return placeholder(tokens.length-1); });
            src = src.replace(/<\?apex|fim\?>/g, (m) => { tokens.push(['delim', m]); return placeholder(tokens.length-1); });
            src = src.replace(/\b(exibir|ler_numero|ler|maiusculas|minusculas|aleatorio|arredondar|tamanho|pagina|titulo|subtitulo|paragrafo|botao|imagem|link|caixa_inicio|caixa_fim|separador|espaco)\b/g, (m) => { tokens.push(['fn', m]); return placeholder(tokens.length-1); });
            src = src.replace(/\b(se|senao|enquanto)\b/g, (m) => { tokens.push(['kw', m]); return placeholder(tokens.length-1); });
            src = src.replace(/\$[a-zA-Z][a-zA-Z0-9_]*/g, (m) => { tokens.push(['var', m]); return placeholder(tokens.length-1); });
            src = src.replace(/\b\d+(\.\d+)?\b/g, (m) => { tokens.push(['num', m]); return placeholder(tokens.length-1); });
            src = src.replace(/>=|<=|==|!=|&&|\|\||[+\-*\/=<>%]/g, (m) => { tokens.push(['op', m]); return placeholder(tokens.length-1); });
            src = escapeHtml(src);
            src = src.replace(/\u0000TOK(\d+)\u0000/g, (_, i) => {
                const [type, val] = tokens[parseInt(i)];
                return `<span class="tok-${type}">${escapeHtml(val)}</span>`;
            });
            return src;
        }

        function highlightCode(src) {
            // Mantida só para compatibilidade — não é mais usada
            return src.split('\n').map(highlightLine).join('\n') + '\n';
        }

        // Estado global de erros (linha → mensagem)
        let errosAtuais = new Map();
        const errorTooltip = document.getElementById('error-tooltip');

        function atualizar() {
            const linhasCodigo = editor.value.split('\n');
            const totalLinhas = linhasCodigo.length;

            // Aplica syntax highlighting linha por linha, marcando as linhas com erro
            let highlightHtml = '';
            for (let i = 0; i < totalLinhas; i++) {
                const linhaNum = i + 1;
                const conteudo = linhasCodigo[i] || '';
                const colorida = highlightLine(conteudo);
                if (errosAtuais.has(linhaNum)) {
                    highlightHtml += `<div class="line line-error">${colorida || '&nbsp;'}</div>`;
                } else {
                    highlightHtml += `<div class="line">${colorida || '&nbsp;'}</div>`;
                }
            }
            highlight.innerHTML = highlightHtml;

            // Gutter: cada linha em div individual; marca as que têm erro
            gutter.innerHTML = '';
            for (let i = 1; i <= totalLinhas; i++) {
                const div = document.createElement('div');
                div.className = 'gutter-line';
                div.textContent = i;
                if (errosAtuais.has(i)) {
                    div.classList.add('has-error');
                    div.dataset.linha = i;
                    div.dataset.msg = errosAtuais.get(i).join(' · ');
                }
                gutter.appendChild(div);
            }

            atualizarPosicao();
        }

        function setErros(listaErros) {
            errosAtuais = new Map();
            for (const e of listaErros) {
                if (!errosAtuais.has(e.linha)) errosAtuais.set(e.linha, []);
                errosAtuais.get(e.linha).push(e.msg);
            }
            atualizar();
        }

        function limparErros() {
            errosAtuais = new Map();
            atualizar();
        }

        // Tooltip ao passar mouse em linhas com erro no gutter
        gutter.addEventListener('mousemove', (e) => {
            const linha = e.target.closest('.gutter-line.has-error');
            if (linha) {
                errorTooltip.innerHTML = `<div class="error-tooltip-line">LINHA ${linha.dataset.linha}</div>${escapeHtml(linha.dataset.msg)}`;
                errorTooltip.style.left = (e.clientX + 14) + 'px';
                errorTooltip.style.top  = (e.clientY + 14) + 'px';
                errorTooltip.classList.add('show');
            } else {
                errorTooltip.classList.remove('show');
            }
        });
        gutter.addEventListener('mouseleave', () => {
            errorTooltip.classList.remove('show');
        });

        function atualizarPosicao() {
            const pos = editor.selectionStart;
            const before = editor.value.substring(0, pos);
            const lines = before.split('\n');
            const linha = lines.length;
            const coluna = lines[lines.length - 1].length + 1;
            lineInfo.textContent = `Linha ${linha}, Coluna ${coluna}`;
        }

        editor.addEventListener('input', () => {
            // Quando o usuário edita, limpa os erros (eles serão revalidados ao executar)
            limparErros();
        });
        editor.addEventListener('scroll', () => {
            const t = `translate(${-editor.scrollLeft}px, ${-editor.scrollTop}px)`;
            highlight.style.transform = t;
            gutter.scrollTop = editor.scrollTop;
        });
        editor.addEventListener('keyup', atualizarPosicao);
        editor.addEventListener('click', atualizarPosicao);
        editor.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = editor.selectionStart;
                const end = editor.selectionEnd;
                editor.value = editor.value.substring(0, start) + '    ' + editor.value.substring(end);
                editor.selectionStart = editor.selectionEnd = start + 4;
                atualizar();
            }
            if (e.ctrlKey && e.key === 'Enter') { e.preventDefault(); executar(); }
            if (e.ctrlKey && e.key === 'l') { e.preventDefault(); limpar(); }
        });

        document.querySelectorAll('.panel-tab').forEach(t => {
            t.addEventListener('click', () => {
                document.querySelectorAll('.panel-tab').forEach(x => x.classList.remove('active'));
                document.querySelectorAll('.panel-page').forEach(x => x.classList.remove('active'));
                t.classList.add('active');
                document.getElementById('page-' + t.dataset.page).classList.add('active');
            });
        });

        function extrairPerguntas(codigo) {
            const re = /(ler_numero|ler)\s*\(\s*"([^"]*)"\s*\)/g;
            const perguntas = [];
            let m;
            while ((m = re.exec(codigo)) !== null) {
                perguntas.push({ tipo: m[1], texto: m[2] });
            }
            return perguntas;
        }

        function coletarRespostas(perguntas) {
            const respostas = [];
            for (const p of perguntas) {
                let pergunta = p.texto;
                let opcoes = null;
                let ehNumero = (p.tipo === 'ler_numero');

                if (p.texto.startsWith('cardapio:')) {
                    const items = p.texto.substring('cardapio:'.length).split('|');
                    opcoes = items.map(x => x.split('=')[0].trim());
                    pergunta = 'Escolha uma opção (digite o número):\n' + items.map((x, i) => {
                        const [nome, preco] = x.split('=');
                        return `${i+1}. ${nome} - R$ ${preco}`;
                    }).join('\n');
                } else if (p.texto.startsWith('numero:')) {
                    pergunta = p.texto.substring('numero:'.length);
                    ehNumero = true;
                }

                if (ehNumero) pergunta += '\n(Digite um número)';

                const r = prompt(pergunta);
                if (r === null) return null;

                if (opcoes) {
                    const num = parseInt(r);
                    if (!isNaN(num) && num >= 1 && num <= opcoes.length) {
                        respostas.push(opcoes[num - 1]);
                    } else {
                        respostas.push(r);
                    }
                } else {
                    respostas.push(r);
                }
            }
            return respostas;
        }

        async function executar() {
            const codigo = editor.value;
            if (!codigo.trim()) return;

            const perguntas = extrairPerguntas(codigo);
            let entradas = [];
            if (perguntas.length > 0) {
                const respostas = coletarRespostas(perguntas);
                if (respostas === null) return;
                entradas = respostas;
            }

            statusDot.classList.remove('err');
            statusText.textContent = 'Executando…';
            statusText.classList.add('running');
            output.innerHTML = '';
            errorsList.innerHTML = '';

            try {
                const fd = new FormData();
                fd.append('codigo', codigo);
                fd.append('entradas', JSON.stringify(entradas));
                const r = await fetch('?acao=executar', { method: 'POST', body: fd });
                const data = await r.json();

                statusText.classList.remove('running');

                if (data.ok) {
                    output.textContent = data.saida || '(sem saída)';
                    errorBadge.classList.add('hidden');
                    errorsList.innerHTML = '<div class="no-errors">Nenhum erro detectado</div>';
                    statusDot.classList.remove('err');
                    statusText.textContent = 'Execução concluída';
                    limparErros();
                } else {
                    output.innerHTML = '<span style="color:var(--text-dim);font-style:italic">Execução interrompida por erros — veja diretamente no editor (linhas vermelhas).</span>';
                    errorBadge.textContent = data.erros.length;
                    errorBadge.classList.remove('hidden');
                    errorsList.innerHTML = data.erros.map(e => `
                        <div class="error-item">
                            <div class="error-line">LINHA ${e.linha}</div>
                            <div class="error-msg">${escapeHtml(e.msg)}</div>
                        </div>
                    `).join('');
                    statusDot.classList.add('err');
                    statusText.textContent = `${data.erros.length} erro(s) encontrado(s)`;
                    setErros(data.erros);  // marca as linhas no editor
                }
            } catch (err) {
                statusText.classList.remove('running');
                statusDot.classList.add('err');
                statusText.textContent = 'Erro de conexão';
                output.textContent = 'Erro de comunicação com o servidor: ' + err.message;
            }
        }

        function abrirComoSite() {
            const codigo = editor.value;
            if (!codigo.trim()) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?acao=site';
            form.target = '_blank';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'codigo';
            input.value = codigo;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function limpar() {
            editor.value = '';
            output.innerHTML = '';
            errorsList.innerHTML = '<div class="empty-state">Nenhum erro detectado ainda. Execute o código para analisar.</div>';
            errorBadge.classList.add('hidden');
            statusDot.classList.remove('err');
            statusText.textContent = 'Pronto';
            limparErros();
            editor.focus();
        }

        document.getElementById('btn-executar').addEventListener('click', executar);
        document.getElementById('btn-site').addEventListener('click', abrirComoSite);
        document.getElementById('btn-limpar').addEventListener('click', limpar);

        // Carrega código vindo via URL (?carregar=BASE64) — usado pela extensão do VS Code
        function carregarCodigoDaUrl() {
            const params = new URLSearchParams(window.location.search);
            const codigoB64 = params.get('carregar');
            if (codigoB64) {
                try {
                    // Decodifica base64 mantendo UTF-8 correto
                    const codigo = decodeURIComponent(escape(atob(codigoB64)));
                    editor.value = codigo;
                    statusText.textContent = 'Código carregado do VS Code';
                    // Remove o parâmetro da URL pra não recarregar de novo se atualizar a página
                    window.history.replaceState({}, document.title, window.location.pathname);
                    return true;
                } catch (e) {
                    console.error('Erro ao decodificar código:', e);
                }
            }
            return false;
        }

        if (!carregarCodigoDaUrl()) {
            editor.value = EXEMPLOS[0].codigo;
        }
        atualizar();
        editor.focus();
    </script>
</body>
</html>
