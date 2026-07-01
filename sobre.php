<?php
require __DIR__ . '/partials.php';
abrir_pagina('Sobre');
?>

<main class="narrow">

    <div class="eyebrow">Projeto Acadêmico</div>
    <h1>Sobre a APEX</h1>
    <p class="lead">Uma linguagem brasileira para aprender Compiladores.</p>

    <!-- HISTÓRIA -->
    <h2>O projeto</h2>
    <p>A <strong>APEX</strong> nasceu como trabalho da disciplina de <strong>Compiladores</strong> do curso de Engenharia de Software (BES 2023). O objetivo era criar uma linguagem de programação do zero, passando por todas as etapas clássicas: análise léxica, análise sintática, geração de código e execução.</p>

    <p>Em vez de criar mais um clone de C ou Python em inglês, decidimos fazer algo diferente: uma <strong>linguagem 100% em português brasileiro</strong>, com sintaxe acessível para quem está começando a programar mas robusta o suficiente pra criar sistemas web reais.</p>

    <!-- DECISÕES TÉCNICAS -->
    <h2>Decisões técnicas</h2>

    <h3>Por que transpilar para PHP?</h3>
    <p>Criar um interpretador do zero levaria meses. Optamos por uma estratégia consagrada: <strong>transpilação</strong>. Mesma abordagem que <a href="https://www.typescriptlang.org" target="_blank">TypeScript</a> usa para virar JavaScript ou <a href="https://kotlinlang.org" target="_blank">Kotlin</a> para virar Java bytecode.</p>

    <p>Escolhemos PHP como linguagem-alvo por três motivos:</p>
    <ul>
        <li>É <strong>nativo da web</strong> — gera HTML/CSS naturalmente</li>
        <li>É <strong>multiplataforma</strong> — roda em Windows, Linux, Mac</li>
        <li>É <strong>maduro e estável</strong> — runtime confiável há 30 anos</li>
    </ul>

    <h3>Por que extensão VS Code?</h3>
    <p>Em vez de criar uma IDE do zero, integramos a APEX ao editor mais usado do mundo. A extensão fornece <strong>syntax highlighting</strong>, <strong>execução com F5</strong> e <strong>diagnostics inline</strong> — exatamente como TypeScript ou Python fazem.</p>

    <h3>Por que gerar páginas web?</h3>
    <p>Linguagens didáticas tradicionalmente rodam em terminal. Achamos que o resultado visual aumenta a motivação do aluno e mostra que <strong>uma linguagem pode ser séria mesmo sendo simples</strong>.</p>

    <!-- ARQUITETURA -->
    <h2>Arquitetura</h2>

    <div class="code-block">
        <div class="code-header"><span class="filename">fluxo</span></div>
<pre>┌──────────────────┐
│  arquivo.apex    │  Código-fonte em português
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Análise léxica  │  Tokenização
│  e sintática     │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Transpilador    │  APEX → PHP
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Runtime PHP     │  Executa o PHP gerado
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Página HTML/CSS │  Resultado no navegador
└──────────────────┘
</pre>
    </div>

<!-- EQUIPE -->
<h2 id="equipe">Equipe</h2>

<div class="team-grid">

    <article class="team-card">
        <div class="team-photo-wrap">
            <img src="assets/img/equipe/gabriel.jpeg" alt="Foto de Gabriel Nascimento" class="team-photo">
        </div>
        <div class="team-info">
            <h3>Gabriel Nascimento</h3>
            <p>Engenharia de Software · BES 2023</p>
            <span>Desenvolvimento da linguagem APEX</span>
        </div>
    </article>

    <article class="team-card">
        <div class="team-photo-wrap">
            <img src="assets/img/equipe/kaiqui.jpeg" alt="Foto de Kaiqui Junior" class="team-photo">
        </div>
        <div class="team-info">
            <h3>Kaiqui Junior</h3>
            <p>Engenharia de Software · BES 2023</p>
            <span>Desenvolvimento da linguagem APEX</span>
        </div>
    </article>

    <article class="team-card">
        <div class="team-photo-wrap">
            <img src="assets/img/equipe/yolando.jpeg" alt="Foto de Yolando Junior" class="team-photo">
        </div>
        <div class="team-info">
            <h3>Yolando Junior</h3>
            <p>Engenharia de Software · BES 2023</p>
            <span>Desenvolvimento da linguagem APEX</span>
        </div>
    </article>

</div>

    <div class="callout">
        <p><strong>Orientação:</strong> Prof. Wilker José Caminha — disciplina de Compiladores.</p>
    </div>

    <!-- STACK -->
    <h2>Tecnologias utilizadas</h2>
    <div class="grid cols-4">
        <div class="card" style="text-align: center;">
            <div class="card-icon" style="margin: 0 auto 0.8rem;">PHP</div>
            <h3 style="font-size: 0.95rem;">Runtime</h3>
        </div>
        <div class="card" style="text-align: center;">
            <div class="card-icon" style="margin: 0 auto 0.8rem;">JS</div>
            <h3 style="font-size: 0.95rem;">Extensão VS Code</h3>
        </div>
        <div class="card" style="text-align: center;">
            <div class="card-icon" style="margin: 0 auto 0.8rem;">JSON</div>
            <h3 style="font-size: 0.95rem;">Gramática TextMate</h3>
        </div>
        <div class="card" style="text-align: center;">
            <div class="card-icon" style="margin: 0 auto 0.8rem;">CSS</div>
            <h3 style="font-size: 0.95rem;">Tema visual</h3>
        </div>
    </div>

    <!-- CONTEXTO ACADÊMICO -->
    <h2>Contexto acadêmico</h2>
    <p>Este projeto faz parte da avaliação da disciplina de <strong>Compiladores</strong>, que cobre:</p>
    <ul>
        <li>Análise léxica (tokenização)</li>
        <li>Análise sintática (gramática BNF)</li>
        <li>Análise semântica (validação de tipos e escopos)</li>
        <li>Geração de código</li>
        <li>Execução / interpretação</li>
    </ul>

    <p>A APEX implementa <strong>todas essas etapas</strong>, com foco didático na clareza do código e na demonstração visual dos resultados.</p>

    <!-- LICENÇA -->
    <h2>Licença</h2>
    <p>Projeto de uso <strong>acadêmico</strong>. Pode ser estudado, modificado e usado livremente para fins educacionais.</p>

    <!-- CTA -->
    <div style="text-align: center; padding: 3rem 0;">
        <h2>Comece a usar agora</h2>
        <div class="hero-cta">
            <a href="download.php" class="btn btn-primary">Baixar APEX</a>
            <a href="docs.php" class="btn btn-secondary">Ler docs</a>
        </div>
    </div>

</main>

<?php fechar_pagina(); ?>