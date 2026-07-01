<?php
/**
 * APEX Site — componentes reutilizáveis
 * Incluído por todas as páginas
 */

// Detecta a página atual para marcar item ativo no menu
function pagina_atual() {
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return preg_replace('/\.php$/', '', $script) ?: 'index';
}

function eh_ativo($nome) {
    return pagina_atual() === $nome ? 'active' : '';
}

// Renderiza o <head> + <body abertura>
function abrir_pagina($titulo, $descricao = '') {
    $tituloCompleto = $titulo === 'Home' ? 'APEX Language' : $titulo . ' · APEX Language';
    $descricao = $descricao ?: 'APEX — Linguagem de programação 100% em português, criada como projeto de Compiladores.';
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloCompleto) ?></title>
    <meta name="description" content="<?= htmlspecialchars($descricao) ?>">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="logo">
            <span class="logo-mark">&lt;?</span>
            <span class="logo-text">APEX <span class="v">v2.0</span></span>
        </a>
        <button class="mobile-menu-btn" onclick="document.querySelector('nav.main-nav').classList.toggle('open')">☰</button>
        <nav class="main-nav">
<a href="index.html" class="<?= eh_ativo('index') ?>">Home</a>
<a href="docs.html" class="<?= eh_ativo('docs') ?>">Docs</a>
<a href="download.html" class="<?= eh_ativo('download') ?>">Download</a>
<a href="sobre.html" class="<?= eh_ativo('sobre') ?>">Sobre</a>
<a href="feito-em-apex.html" class="<?= eh_ativo('feito-em-apex') ?>">Feito em APEX</a>
<a href="download.html" class="nav-cta">Instalar</a>
        </nav>
    </div>
</header>
<?php
}

function fechar_pagina() {
?>
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="index.php" class="logo">
                <span class="logo-mark">&lt;?</span>
                <span class="logo-text">APEX</span>
            </a>
            <p>Linguagem de programação 100% em português, criada como projeto da disciplina de Compiladores.</p>
        </div>
        <div class="footer-col">
            <h5>Linguagem</h5>
            <ul>
                <li><a href="docs.php">Documentação</a></li>
                <li><a href="docs.php#exemplos">Exemplos</a></li>
                <li><a href="docs.php#sintaxe">Sintaxe</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>Projeto</h5>
            <ul>
                <li><a href="download.php">Download</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="sobre.php#equipe">Equipe</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        APEX Language · Projeto acadêmico de Compiladores · Engenharia de Software BES 2023
    </div>
</footer>

<script>
// Copy to clipboard nos blocos de código
document.querySelectorAll('.code-copy').forEach(btn => {
    btn.addEventListener('click', () => {
        const codigo = btn.closest('.code-block').querySelector('pre').innerText;
        navigator.clipboard.writeText(codigo).then(() => {
            const original = btn.textContent;
            btn.textContent = 'Copiado!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.textContent = original;
                btn.classList.remove('copied');
            }, 1500);
        });
    });
});

// Reveal on scroll
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) e.target.classList.add('visible');
    });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html><?php
}

/**
 * Renderiza um bloco de código APEX com syntax highlighting
 */
function bloco_codigo($codigo, $nome_arquivo = 'exemplo.apex') {
    $highlighted = destacar_apex($codigo);
?>
<div class="code-block">
    <div class="code-header">
        <span class="filename"><?= htmlspecialchars($nome_arquivo) ?></span>
        <button class="code-copy">Copiar</button>
    </div>
    <pre><?= $highlighted ?></pre>
</div>
<?php
}

/**
 * Aplica syntax highlighting de APEX em uma string (retorna HTML)
 * Mesma lógica do highlighter da IDE: substitui tokens por placeholders,
 * escapa o resto, e reinsere com spans.
 */
function destacar_apex($src) {
    $tokens = [];
    $placeholder = function($i) { return "\x00TOK{$i}\x00"; };

    // Comentários
    $src = preg_replace_callback('/\/\/[^\n]*/', function($m) use (&$tokens, $placeholder) {
        $tokens[] = ['com', $m[0]]; return $placeholder(count($tokens) - 1);
    }, $src);

    // Strings
    $src = preg_replace_callback('/"([^"\\\\]|\\\\.)*"/', function($m) use (&$tokens, $placeholder) {
        $tokens[] = ['str', $m[0]]; return $placeholder(count($tokens) - 1);
    }, $src);

    // Delimitadores
    $src = preg_replace_callback('/<\?apex|fim\?>/', function($m) use (&$tokens, $placeholder) {
        $tokens[] = ['delim', $m[0]]; return $placeholder(count($tokens) - 1);
    }, $src);

    // Funções
    $src = preg_replace_callback('/\b(exibir|ler_numero|ler|maiusculas|minusculas|aleatorio|arredondar|tamanho|pagina|titulo|subtitulo|paragrafo|botao|imagem|link|caixa_inicio|caixa_fim|separador|espaco)\b/', function($m) use (&$tokens, $placeholder) {
        $tokens[] = ['fn', $m[0]]; return $placeholder(count($tokens) - 1);
    }, $src);

    // Keywords
    $src = preg_replace_callback('/\b(se|senao|enquanto)\b/', function($m) use (&$tokens, $placeholder) {
        $tokens[] = ['kw', $m[0]]; return $placeholder(count($tokens) - 1);
    }, $src);

    // Variáveis
    $src = preg_replace_callback('/\$[a-zA-Z_][a-zA-Z0-9_]*/', function($m) use (&$tokens, $placeholder) {
        $tokens[] = ['var', $m[0]]; return $placeholder(count($tokens) - 1);
    }, $src);

    // Números
    $src = preg_replace_callback('/\b\d+(\.\d+)?\b/', function($m) use (&$tokens, $placeholder) {
        $tokens[] = ['num', $m[0]]; return $placeholder(count($tokens) - 1);
    }, $src);

    // Operadores
    $src = preg_replace_callback('/>=|<=|==|!=|&&|\|\||[+\-*\/=<>%]/', function($m) use (&$tokens, $placeholder) {
        $tokens[] = ['op', $m[0]]; return $placeholder(count($tokens) - 1);
    }, $src);

    // Escapa HTML do que sobrou
    $src = htmlspecialchars($src);

    // Reinjeta tokens
    $src = preg_replace_callback('/\x00TOK(\d+)\x00/', function($m) use (&$tokens) {
        list($tipo, $val) = $tokens[(int)$m[1]];
        return '<span class="tok-' . $tipo . '">' . htmlspecialchars($val) . '</span>';
    }, $src);

    return $src;
}