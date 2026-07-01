<?php
require __DIR__ . '/partials.php';
abrir_pagina('Download');
?>

<main class="narrow">

    <div class="eyebrow">Versão 2.0.2 · Estável</div>
    <h1>Baixe a APEX e comece a programar</h1>
    <p class="lead">Instalação em 3 passos. Tudo gratuito.</p>

    <!-- DOWNLOAD PRINCIPAL -->
    <div class="card reveal" style="text-align: center; padding: 2.5rem; margin: 2rem 0;">
        <div style="font-family: var(--font-mono); font-size: 0.78rem; color: var(--text-faded); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.8rem;">
            Extensão VS Code
        </div>
        <h2 style="margin: 0 0 0.6rem; font-size: 1.6rem;">apex-language-2.0.2.vsix</h2>
        <p style="margin-bottom: 1.5rem;">Syntax highlighting · Execução com F5 · Detecção de erros</p>
        <a href="downloads/apex-language-2.0.2.vsix" class="btn btn-primary" download>
            ⬇ Baixar extensão (16 KB)
        </a>
    </div>

    <!-- DOWNLOAD RUNTIME -->
    <div class="card reveal" style="text-align: center; padding: 2rem; margin: 2rem 0;">
        <div style="font-family: var(--font-mono); font-size: 0.78rem; color: var(--text-faded); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.8rem;">
            Runtime APEX
        </div>
        <h3 style="margin: 0 0 0.6rem;">apex-runtime.zip</h3>
        <p style="margin-bottom: 1.5rem; font-size: 0.95rem;">Contém o transpilador <code>apex.php</code> e a IDE web <code>ide.php</code></p>
        <a href="downloads/runtime.zip" class="btn btn-secondary" download>
            ⬇ Baixar runtime
        </a>
    </div>

    <!-- PRÉ-REQUISITOS -->
    <h2 id="requisitos">Pré-requisitos</h2>

    <div class="grid cols-2">
        <div class="card">
            <h3>VS Code 1.50+</h3>
            <p>Editor de código onde a extensão APEX roda.</p>
            <a href="https://code.visualstudio.com" target="_blank" class="btn btn-ghost">Baixar VS Code →</a>
        </div>
        <div class="card">
            <h3>PHP 7.4+</h3>
            <p>Runtime que executa o código transpilado. Deve estar no PATH do sistema.</p>
            <a href="https://windows.php.net/download/" target="_blank" class="btn btn-ghost">Baixar PHP →</a>
        </div>
    </div>

    <div class="callout warning">
        <p><strong>Importante:</strong> Pra verificar se o PHP está acessível, abra o terminal (cmd) e rode <code>php -v</code>. Se aparecer a versão, está tudo certo.</p>
    </div>

    <!-- TUTORIAL DE INSTALAÇÃO -->
    <h2 id="instalacao">Tutorial de instalação</h2>

    <h3>Passo 1 — Preparar a pasta da APEX</h3>
    <ol>
        <li>Crie uma pasta no seu computador, por exemplo: <code>C:\Users\SeuNome\Desktop\linguagem-apex\</code></li>
        <li>Baixe o <strong>Runtime APEX</strong> (apex-runtime.zip) e extraia dentro dela</li>
        <li>Você terá uma subpasta <code>runtime/</code> com os arquivos <code>apex.php</code> e <code>ide.php</code></li>
    </ol>

    <p>A estrutura final fica assim:</p>

    <div class="code-block">
        <div class="code-header">
            <span class="filename">estrutura</span>
        </div>
<pre>linguagem-apex/
├── runtime/
│   ├── apex.php
│   └── ide.php
└── (seus arquivos .apex aqui)
</pre>
    </div>

    <h3>Passo 2 — Instalar a extensão no VS Code</h3>
    <ol>
        <li>Baixe o arquivo <code>apex-language-2.0.2.vsix</code></li>
        <li>Clique com botão direito → <strong>Propriedades</strong> → marque <strong>"Desbloquear"</strong> se aparecer → Aplicar</li>
        <li>Abra o VS Code e aperte <code>Ctrl + Shift + X</code> (painel de Extensões)</li>
        <li>Clique nos três pontinhos <code>...</code> no topo do painel</li>
        <li>Escolha <strong>"Instalar do VSIX..."</strong></li>
        <li>Selecione o arquivo <code>apex-language-2.0.2.vsix</code></li>
        <li>Clique em <strong>"Recarregar"</strong> quando perguntar</li>
    </ol>

    <div class="callout">
        <p><strong>Dica:</strong> Se aparecer erro ou a extensão não carregar, verifique se o arquivo VSIX está desbloqueado nas propriedades.</p>
    </div>

    <h3>Passo 3 — Testar</h3>
    <ol>
        <li>No VS Code: <strong>Arquivo → Abrir Pasta</strong> → selecione a pasta <code>linguagem-apex</code></li>
        <li>Crie um arquivo novo chamado <code>teste.apex</code></li>
        <li>Cole o código abaixo:</li>
    </ol>

    <?php bloco_codigo(<<<'CODIGO'
<?apex
    titulo("Funcionou!");
    paragrafo("Sua APEX está pronta pra usar.");
fim?>
CODIGO
, 'teste.apex'); ?>

    <ol start="4">
        <li>Aperte <strong>F5</strong></li>
        <li>O navegador deve abrir com a página em verde mostrando "Funcionou!"</li>
    </ol>

    <!-- TROUBLESHOOTING -->
    <h2 id="problemas">Resolvendo problemas comuns</h2>

    <h3>"php não é reconhecido como comando"</h3>
    <p>O PHP não está no PATH do sistema. Adicione manualmente:</p>
    <ol>
        <li>Win + R → digite <code>sysdm.cpl</code> → Enter</li>
        <li>Aba <strong>Avançado</strong> → <strong>Variáveis de Ambiente</strong></li>
        <li>Em "Path" → Editar → Novo → adicione <code>C:\php</code> (ou onde está o PHP)</li>
        <li>OK em tudo e reinicie o VS Code</li>
    </ol>

    <h3>A extensão não aparece colorindo o código</h3>
    <ol>
        <li>Aperte <code>Ctrl + Shift + P</code> → "Developer: Reload Window"</li>
        <li>Se ainda não funcionar, desinstale e instale de novo o VSIX</li>
    </ol>

    <h3>F5 abre uma janela estranha de Debug</h3>
    <p>A linguagem do arquivo não foi detectada como APEX. Clique no canto inferior direito (onde aparece a linguagem) e selecione <strong>APEX</strong>.</p>

    <!-- CTA -->
    <div style="text-align: center; padding: 3rem 0;">
        <h2>Tudo certo?</h2>
        <p class="lead">Agora aprenda a sintaxe completa.</p>
        <a href="docs.php" class="btn btn-primary">Ver documentação →</a>
    </div>

</main>

<?php fechar_pagina(); ?>