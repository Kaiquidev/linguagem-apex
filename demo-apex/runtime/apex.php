<?php
/**
 * APEX - Transpilador para PHP
 * --------------------------------
 * Lê um arquivo .apex, converte para PHP equivalente e executa.
 *
 * Uso:
 *   php apex.php meu_programa.apex          (executa)
 *   php apex.php meu_programa.apex --ver    (mostra o PHP gerado)
 *   php apex.php meu_programa.apex --salvar (salva como .php sem executar)
 */

// ----------- Verifica argumentos -----------
if ($argc < 2) {
    echo "APEX - Linguagem de Programacao\n";
    echo "Uso: php apex.php <arquivo.apex> [opcao]\n";
    echo "Opcoes:\n";
    echo "  (nenhuma)   Executa o programa\n";
    echo "  --ver       Mostra o codigo PHP gerado\n";
    echo "  --salvar    Salva como .php sem executar\n";
    exit(1);
}

$arquivo = $argv[1];
$opcao   = $argv[2] ?? '';

if (!file_exists($arquivo)) {
    echo "Erro: arquivo '$arquivo' nao encontrado.\n";
    exit(1);
}

$codigoApex = file_get_contents($arquivo);

// ----------- Transpilador APEX -> PHP -----------
function transpilarApex($codigo) {
    // 1) Delimitadores de bloco
    $codigo = str_replace('<?apex', '<?php', $codigo);
    $codigo = str_replace('fim?>', '?>',     $codigo);

    // 2) Palavras-chave (usa \b pra nao confundir 'se' com 'senao' ou nomes de variaveis)
    //    Substituimos primeiro 'senao' (mais especifico) e depois 'se'.
    $codigo = preg_replace('/\bsenao\b/', 'else', $codigo);
    $codigo = preg_replace('/\bse\s*\(/', 'if (', $codigo);

    // 3) Funcao de saida exibir() -> echo
    //    Convertemos chamadas exibir(...) em "echo ...;"
    //    Tratamos a expressao interna respeitando parenteses.
    $codigo = preg_replace_callback(
        '/exibir\s*\((.*)\)\s*;/',
        function ($m) {
            return 'echo ' . $m[1] . ' . PHP_EOL;';
        },
        $codigo
    );

    // 4) Concatenacao com strings: no PHP, "+" entre string e numero
    //    nao concatena. Convertemos "+" para "." quando houver string envolvida.
    //    Heuristica simples: se a linha tem aspas, troca + por . nessa linha.
    $linhas = explode("\n", $codigo);
    foreach ($linhas as $i => $linha) {
        if (strpos($linha, '"') !== false || strpos($linha, "'") !== false) {
            // Nao mexe em "+=" nem "++"
            $linha = preg_replace('/(?<![+\.])\+(?![+=])/', '.', $linha);
            $linhas[$i] = $linha;
        }
    }
    $codigo = implode("\n", $linhas);

    return $codigo;
}

$codigoPhp = transpilarApex($codigoApex);

// ----------- Executa de acordo com a opcao -----------
switch ($opcao) {
    case '--ver':
        echo "=== Codigo PHP gerado ===\n";
        echo $codigoPhp;
        echo "\n=========================\n";
        break;

    case '--salvar':
        $saida = preg_replace('/\.apex$/', '.php', $arquivo);
        file_put_contents($saida, $codigoPhp);
        echo "Arquivo salvo como: $saida\n";
        break;

    default:
        // Executa o PHP gerado
        $tmp = tempnam(sys_get_temp_dir(), 'apex_') . '.php';
        file_put_contents($tmp, $codigoPhp);
        include $tmp;
        echo "\n";
        unlink($tmp);
        break;
}
