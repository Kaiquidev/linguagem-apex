<?php
require __DIR__ . '/partials.php';
abrir_pagina('Home');
?>

<main>

    <!-- HERO -->
    <section class="hero reveal">
        <div class="eyebrow">v2.0 · Compiladores · BES 2023</div>
        <h1>
            Programar em <span class="accent">português</span><br>
            nunca foi tão divertido.
        </h1>
        <p class="lead" style="max-width: 680px; margin-left: auto; margin-right: auto;">
            APEX é uma linguagem de programação 100% em português que transpila para PHP e gera páginas web completas direto do VS Code.
        </p>

        <div class="hero-cta">
            <a href="download.php" class="btn btn-primary">⬇ Baixar extensão</a>
            <a href="docs.php" class="btn btn-secondary">Ver documentação</a>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="num">3</div>
                <div class="label">Estruturas de controle</div>
            </div>
            <div class="stat">
                <div class="num">8</div>
                <div class="label">Funções nativas</div>
            </div>
            <div class="stat">
                <div class="num">10+</div>
                <div class="label">Comandos de UI</div>
            </div>
            <div class="stat">
                <div class="num">100%</div>
                <div class="label">Em português</div>
            </div>
        </div>
    </section>

    <!-- EXEMPLO DE CÓDIGO PRINCIPAL -->
    <section class="reveal">
        <h2>Hello world em APEX</h2>
        <p>Veja como é simples criar uma página web completa em português:</p>

        <?php bloco_codigo(<<<'CODIGO'
<?apex
    pagina("Minha primeira página");

    titulo("Olá, mundo!");
    paragrafo("Esta página foi feita 100% em APEX.");

    separador();

    subtitulo("E agora?");
    botao("Ver mais exemplos", "exemplos.apex");
fim?>
CODIGO
, 'ola.apex'); ?>

        <p style="text-align: center; margin-top: 2rem;">
            <a href="docs.php#exemplos" class="btn btn-ghost">Ver mais exemplos →</a>
        </p>
    </section>

    <!-- FEATURES -->
    <section class="reveal">
        <h2>Tudo que você precisa, em português</h2>
        <p>A APEX traz o vocabulário familiar do português para o mundo da programação web:</p>

        <div class="grid cols-3">
            <div class="card">
                <div class="card-icon">se</div>
                <h3>Estruturas de controle</h3>
                <p><code>se</code>, <code>senao</code>, <code>enquanto</code> — toda a lógica em palavras que você já conhece.</p>
            </div>
            <div class="card">
                <div class="card-icon">ler</div>
                <h3>Entrada e saída</h3>
                <p><code>ler()</code>, <code>ler_numero()</code> e <code>exibir()</code> com formulários gerados automaticamente.</p>
            </div>
            <div class="card">
                <div class="card-icon">ui</div>
                <h3>Páginas web nativas</h3>
                <p><code>titulo</code>, <code>botao</code>, <code>imagem</code>, <code>caixa</code> — HTML é gerado pra você.</p>
            </div>
            <div class="card">
                <div class="card-icon">⚡</div>
                <h3>Execução com F5</h3>
                <p>Aperte F5 no VS Code e veja seu programa rodando no navegador, instantaneamente.</p>
            </div>
            <div class="card">
                <div class="card-icon">🛑</div>
                <h3>Detecção de erros</h3>
                <p>Sublinhado vermelho ondulado igual VS Code real, com mensagens claras em português.</p>
            </div>
            <div class="card">
                <div class="card-icon">🎨</div>
                <h3>Tema visual incluso</h3>
                <p>Páginas saem prontas com tema dark/ciano, sem precisar escrever uma linha de CSS.</p>
            </div>
        </div>
    </section>

    <!-- ARQUITETURA -->
    <section class="reveal">
        <h2>Como funciona por baixo</h2>
        <p>A APEX é uma <strong>linguagem transpilada</strong>: seu código é convertido para PHP antes de executar. Mesma estratégia que TypeScript usa pra virar JavaScript.</p>

        <div class="grid cols-3" style="margin-top: 2rem;">
            <div class="card" style="text-align: center;">
                <div class="card-icon" style="margin: 0 auto 1rem;">1</div>
                <h3>Você escreve</h3>
                <p>Código <code>.apex</code> em português no VS Code, com syntax highlighting próprio.</p>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-icon" style="margin: 0 auto 1rem;">2</div>
                <h3>Nosso transpilador</h3>
                <p>Análise léxica, sintática e conversão para PHP equivalente — feito do zero por nós.</p>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-icon" style="margin: 0 auto 1rem;">3</div>
                <h3>Roda no navegador</h3>
                <p>O PHP gerado executa e produz HTML/CSS — uma página web completa aparece pronta.</p>
            </div>
        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="reveal" style="text-align: center; padding: 4rem 0 0;">
        <h2>Pronto pra começar?</h2>
        <p class="lead">Instale a extensão do VS Code e escreva seu primeiro programa em APEX agora.</p>
        <div class="hero-cta">
            <a href="download.php" class="btn btn-primary">⬇ Baixar agora</a>
            <a href="docs.php" class="btn btn-secondary">Ler a documentação</a>
        </div>
    </section>

</main>

<?php fechar_pagina(); ?>