<?php
require __DIR__ . '/partials.php';
abrir_pagina('Documentação');
?>

<main>

<div class="doc-layout">

    <!-- SIDEBAR -->
    <aside class="doc-sidebar">
        <h4>Início</h4>
        <ul>
            <li><a href="#intro">Introdução</a></li>
            <li><a href="#primeiro">Primeiro programa</a></li>
        </ul>

        <h4>Sintaxe</h4>
        <ul>
            <li><a href="#delimitadores">Delimitadores</a></li>
            <li><a href="#variaveis">Variáveis</a></li>
            <li><a href="#operadores">Operadores</a></li>
            <li><a href="#controle">Controle de fluxo</a></li>
            <li><a href="#io">Entrada/Saída</a></li>
        </ul>

        <h4>Funções</h4>
        <ul>
            <li><a href="#funcoes">Funções nativas</a></li>
            <li><a href="#ui">Comandos de UI</a></li>
        </ul>

        <h4>Avançado</h4>
        <ul>
            <li><a href="#navegacao">Navegação</a></li>
            <li><a href="#exemplos">Exemplos completos</a></li>
        </ul>
    </aside>

    <!-- CONTEÚDO -->
    <article>

        <h1 id="intro">Documentação</h1>
        <p class="lead">Guia completo da linguagem APEX. Tudo que você precisa pra começar a programar em português.</p>

        <div class="callout">
            <p><strong>Pré-requisito:</strong> Instale a extensão da APEX no VS Code. Veja a página de <a href="download.php">Download</a>.</p>
        </div>

        <h2 id="primeiro">Primeiro programa</h2>
        <p>Todo programa APEX começa com <code>&lt;?apex</code> e termina com <code>fim?&gt;</code>. Veja o clássico "Olá, mundo":</p>

        <?php bloco_codigo(<<<'CODIGO'
<?apex
    exibir("Olá, mundo!");
fim?>
CODIGO
, 'ola.apex'); ?>

        <p>Salve esse código num arquivo <code>.apex</code>, aperte <strong>F5</strong> no VS Code e veja a mágica acontecer.</p>

        <h2 id="delimitadores">Delimitadores</h2>
        <p>Os delimitadores marcam onde o código APEX começa e termina:</p>

        <table>
            <thead>
                <tr><th>Token</th><th>Função</th></tr>
            </thead>
            <tbody>
                <tr><td><code>&lt;?apex</code></td><td>Início do programa (obrigatório)</td></tr>
                <tr><td><code>fim?&gt;</code></td><td>Fim do programa (obrigatório)</td></tr>
            </tbody>
        </table>

        <h2 id="variaveis">Variáveis</h2>
        <p>Variáveis em APEX começam com <code>$</code>, igual em PHP. Não precisa declarar tipo:</p>

        <?php bloco_codigo(<<<'CODIGO'
<?apex
    $nome = "Gabriel";
    $idade = 22;
    $preco = 12.50;
    $ativo = "sim";

    exibir("Nome: " + $nome);
    exibir("Idade: " + $idade);
fim?>
CODIGO
, 'variaveis.apex'); ?>

        <p>O operador <code>+</code> funciona como soma para números e como concatenação para strings.</p>

        <h2 id="operadores">Operadores</h2>

        <h3>Aritméticos</h3>
        <table>
            <thead>
                <tr><th>Operador</th><th>Função</th><th>Exemplo</th></tr>
            </thead>
            <tbody>
                <tr><td><code>+</code></td><td>Soma / concatenação</td><td><code>$a + $b</code></td></tr>
                <tr><td><code>-</code></td><td>Subtração</td><td><code>$a - $b</code></td></tr>
                <tr><td><code>*</code></td><td>Multiplicação</td><td><code>$a * $b</code></td></tr>
                <tr><td><code>/</code></td><td>Divisão</td><td><code>$a / $b</code></td></tr>
                <tr><td><code>%</code></td><td>Resto da divisão</td><td><code>$n % 2</code></td></tr>
            </tbody>
        </table>

        <h3>Relacionais</h3>
        <table>
            <thead>
                <tr><th>Operador</th><th>Significado</th></tr>
            </thead>
            <tbody>
                <tr><td><code>==</code></td><td>Igual</td></tr>
                <tr><td><code>!=</code></td><td>Diferente</td></tr>
                <tr><td><code>&gt;</code> / <code>&lt;</code></td><td>Maior / menor</td></tr>
                <tr><td><code>&gt;=</code> / <code>&lt;=</code></td><td>Maior ou igual / menor ou igual</td></tr>
            </tbody>
        </table>

        <h2 id="controle">Controle de fluxo</h2>

        <h3>Condicional</h3>
        <?php bloco_codigo(<<<'CODIGO'
<?apex
    $idade = 18;

    se ($idade >= 18) {
        exibir("Maior de idade");
    } senao {
        exibir("Menor de idade");
    }
fim?>
CODIGO
, 'condicional.apex'); ?>

        <h3>Laço de repetição</h3>
        <?php bloco_codigo(<<<'CODIGO'
<?apex
    $i = 1;
    enquanto ($i <= 5) {
        exibir("Número: " + $i);
        $i = $i + 1;
    }
fim?>
CODIGO
, 'laco.apex'); ?>

        <h2 id="io">Entrada e Saída</h2>
        <p>A APEX tem três funções principais de I/O. As funções <code>ler</code> têm prefixos especiais que mudam a aparência:</p>

        <table>
            <thead>
                <tr><th>Função</th><th>O que faz</th></tr>
            </thead>
            <tbody>
                <tr><td><code>exibir(texto)</code></td><td>Mostra texto na saída</td></tr>
                <tr><td><code>ler(pergunta)</code></td><td>Lê entrada de texto do usuário</td></tr>
                <tr><td><code>ler_numero(pergunta)</code></td><td>Lê entrada numérica</td></tr>
            </tbody>
        </table>

        <h3>Prefixos especiais do <code>ler()</code></h3>

        <p>O texto da pergunta pode começar com prefixos especiais que mudam o tipo de campo gerado:</p>

        <?php bloco_codigo(<<<'CODIGO'
<?apex
    // Texto simples
    $nome = ler("Qual seu nome?");

    // Cardápio: gera grid de cards selecionáveis
    $prato = ler("cardapio:Pizza=35|Lasanha=28|Salada=20");

    // Campo opcional (não obrigatório)
    $cupom = ler("opcional:Tem cupom de desconto?");

    // Número
    $qtd = ler_numero("Quantas pessoas?");

    exibir("Pedido: " + $prato);
    exibir("Total: R$ " + ($prato_preco * $qtd));
fim?>
CODIGO
, 'entrada.apex'); ?>

        <h2 id="funcoes">Funções nativas</h2>

        <table>
            <thead>
                <tr><th>Função</th><th>Exemplo</th><th>Resultado</th></tr>
            </thead>
            <tbody>
                <tr><td><code>maiusculas(texto)</code></td><td><code>maiusculas("pizza")</code></td><td><code>"PIZZA"</code></td></tr>
                <tr><td><code>minusculas(texto)</code></td><td><code>minusculas("PIZZA")</code></td><td><code>"pizza"</code></td></tr>
                <tr><td><code>tamanho(texto)</code></td><td><code>tamanho("apex")</code></td><td><code>4</code></td></tr>
                <tr><td><code>aleatorio(min, max)</code></td><td><code>aleatorio(1, 100)</code></td><td>número aleatório</td></tr>
                <tr><td><code>arredondar(valor)</code></td><td><code>arredondar(12.7)</code></td><td><code>13</code></td></tr>
            </tbody>
        </table>

        <h2 id="ui">Comandos de UI</h2>
        <p>A APEX é também uma <strong>linguagem geradora de páginas web</strong>. Use estes comandos pra criar HTML automaticamente:</p>

        <table>
            <thead>
                <tr><th>Comando</th><th>O que faz</th></tr>
            </thead>
            <tbody>
                <tr><td><code>pagina("titulo")</code></td><td>Define o título da aba</td></tr>
                <tr><td><code>titulo("texto")</code></td><td>Título principal (H1)</td></tr>
                <tr><td><code>subtitulo("texto")</code></td><td>Subtítulo (H2)</td></tr>
                <tr><td><code>paragrafo("texto")</code></td><td>Parágrafo</td></tr>
                <tr><td><code>botao("texto", "destino.apex")</code></td><td>Botão clicável</td></tr>
                <tr><td><code>link("texto", "destino")</code></td><td>Link simples</td></tr>
                <tr><td><code>imagem("url", "descricao")</code></td><td>Imagem</td></tr>
                <tr><td><code>caixa_inicio()</code> ... <code>caixa_fim()</code></td><td>Container visual (card)</td></tr>
                <tr><td><code>separador()</code></td><td>Linha horizontal</td></tr>
                <tr><td><code>espaco()</code></td><td>Espaçamento vertical</td></tr>
            </tbody>
        </table>

        <p>Exemplo de página completa:</p>

        <?php bloco_codigo(<<<'CODIGO'
<?apex
    pagina("Minha Loja");

    titulo("Bem-vindo!");
    paragrafo("Confira nossas categorias.");

    separador();

    subtitulo("Departamentos");

    caixa_inicio();
        paragrafo("Roupas, calçados e acessórios.");
        botao("Ver tudo", "moda.apex");
    caixa_fim();

    espaco();

    caixa_inicio();
        paragrafo("Eletrônicos e tecnologia.");
        botao("Ver tudo", "tech.apex");
    caixa_fim();
fim?>
CODIGO
, 'loja.apex'); ?>

        <h2 id="navegacao">Navegação entre páginas</h2>
        <p>Quando o <code>botao()</code> ou <code>link()</code> aponta pra um arquivo <code>.apex</code>, a APEX gera automaticamente uma URL amigável:</p>

        <?php bloco_codigo(<<<'CODIGO'
<?apex
    botao("Sobre nós", "sobre.apex");
    // Vira: <a href="/sobre.apex">Sobre nós</a>
fim?>
CODIGO
, 'navegacao.apex'); ?>

        <p>Cada arquivo APEX vira uma URL: <code>localhost:8765/sobre.apex</code>. Igual a um site real.</p>

        <h2 id="exemplos">Exemplos completos</h2>

        <h3>Sistema de pedidos (restaurante)</h3>
        <p>Demonstra cardápio interativo, cálculos, condicionais e funções nativas:</p>

        <?php bloco_codigo(<<<'CODIGO'
<?apex
    $prato = ler("cardapio:Pizza=35|Lasanha=28|Salada=20");
    $qtd = ler_numero("Quantas pessoas?");

    $preco = 0;
    se ($prato == "Pizza")   { $preco = 35; }
    se ($prato == "Lasanha") { $preco = 28; }
    se ($prato == "Salada")  { $preco = 20; }

    $subtotal = $preco * $qtd;
    $taxa = $subtotal * 10 / 100;
    $total = $subtotal + $taxa;
    $numero = aleatorio(100, 999);

    exibir("=== PEDIDO #" + $numero + " ===");
    exibir("Prato: " + maiusculas($prato));
    exibir("Pessoas: " + $qtd);
    exibir("Subtotal: R$ " + $subtotal);
    exibir("Taxa: R$ " + arredondar($taxa));
    exibir("TOTAL: R$ " + arredondar($total));
fim?>
CODIGO
, 'restaurante.apex'); ?>

    </article>
</div>

</main>

<?php fechar_pagina(); ?>