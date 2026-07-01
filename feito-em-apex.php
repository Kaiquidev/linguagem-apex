<?php
require __DIR__ . '/partials.php';
abrir_pagina('Site feito em APEX');
?>

<main>

    <section class="hero reveal">
        <div class="eyebrow">Demonstração real</div>

        <h1>
            Este site também foi criado em <span class="accent">APEX</span>
        </h1>

        <p class="lead" style="max-width: 760px; margin-left: auto; margin-right: auto;">
            Abaixo está uma versão funcional do site de apresentação criada com arquivos .apex.
            Isso demonstra que a linguagem APEX consegue gerar páginas web reais com navegação,
            textos, cards, botões e sistemas simples.
        </p>

        <div class="hero-cta">
            <a href="demo-apex/runtime/ide.php?acao=abrir_apex&arquivo=index.apex" target="_blank" class="btn btn-primary">
                Abrir em tela cheia
            </a>

            <a href="docs.php" class="btn btn-secondary">
                Ver documentação
            </a>
        </div>
    </section>

    <section class="reveal">
        <h2>Site APEX rodando dentro do site PHP</h2>

        <p>
            Esta área carrega o runtime da APEX e executa o arquivo <code>index.apex</code>.
            Os botões internos navegam para outras páginas como <code>docs.apex</code>,
            <code>download.apex</code>, <code>demos.apex</code> e <code>sobre.apex</code>.
        </p>

        <div class="apex-demo-frame">
            <div class="apex-demo-top">
                <span></span>
                <span></span>
                <span></span>
                <strong>demo-apex / index.apex</strong>
            </div>

            <iframe 
                src="demo-apex/runtime/ide.php?acao=abrir_apex&arquivo=index.apex"
                class="apex-demo-iframe"
                title="Site criado com APEX">
            </iframe>
        </div>
    </section>

    <section class="reveal">
        <h2>O que essa demonstração prova?</h2>

        <div class="grid cols-3">
            <div class="card">
                <div class="card-icon">.apex</div>
                <h3>Arquivos reais</h3>
                <p>O conteúdo exibido vem de arquivos escritos diretamente na linguagem APEX.</p>
            </div>

            <div class="card">
                <div class="card-icon">PHP</div>
                <h3>Transpilação</h3>
                <p>A APEX transforma os comandos em PHP para executar no navegador.</p>
            </div>

            <div class="card">
                <div class="card-icon">web</div>
                <h3>Interface visual</h3>
                <p>Comandos como <code>titulo</code>, <code>paragrafo</code>, <code>caixa</code> e <code>botao</code> geram uma página web.</p>
            </div>
        </div>
    </section>

</main>

<?php fechar_pagina(); ?>