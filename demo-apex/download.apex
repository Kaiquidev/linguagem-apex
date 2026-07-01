<?apex
    pagina("Download e instalação da APEX");

    titulo("Download da APEX");
    paragrafo("Instale a extensão no VS Code, configure o runtime e execute arquivos .apex com F5.");

    separador();

    subtitulo("Arquivos necessários");

    caixa_inicio();
        subtitulo("Extensão VS Code");
        paragrafo("Arquivo: apex-language-2.0.2.vsix");
        paragrafo("Função: adiciona suporte da linguagem APEX ao VS Code, incluindo destaque de sintaxe, execução com F5 e integração com o runtime.");
        botao("⬇ Baixar extensão VS Code", "../downloads/apex-language-2.0.2.vsix");
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Runtime APEX");
        paragrafo("Arquivo: apex-runtime.zip");
        paragrafo("Função: contém os arquivos responsáveis por executar e transpilar o código APEX para PHP.");
        botao("⬇ Baixar runtime APEX", "../downloads/runtime.zip");
    caixa_fim();

    separador();

    subtitulo("Tutorial de instalação no VS Code");

    caixa_inicio();
        subtitulo("Passo 1 - Baixar a extensão");
        paragrafo("Clique no botão Baixar extensão VS Code e salve o arquivo apex-language-2.0.2.vsix no computador.");
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Passo 2 - Abrir o VS Code");
        paragrafo("Abra o Visual Studio Code.");
        paragrafo("Depois vá até a aba de extensões ou use o atalho Ctrl + Shift + X.");
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Passo 3 - Instalar do VSIX");
        paragrafo("Na aba de extensões, clique nos três pontinhos.");
        paragrafo("Escolha a opção Instalar do VSIX.");
        paragrafo("Selecione o arquivo apex-language-2.0.2.vsix.");
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Passo 4 - Recarregar o VS Code");
        paragrafo("Se o VS Code pedir para recarregar, confirme.");
        paragrafo("Depois disso, arquivos .apex devem ser reconhecidos automaticamente.");
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Passo 5 - Preparar o runtime");
        paragrafo("Baixe o runtime APEX e extraia o arquivo apex-runtime.zip dentro da pasta do projeto.");
        paragrafo("A estrutura recomendada é ter uma pasta runtime com os arquivos apex.php e ide.php.");
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Passo 6 - Testar a linguagem");
        paragrafo("Crie um arquivo chamado teste.apex.");
        paragrafo("Escreva um código simples usando titulo e paragrafo.");
        paragrafo("Abra o arquivo no VS Code e pressione F5.");
    caixa_fim();

    separador();

    subtitulo("Código de teste");

    caixa_inicio();
        paragrafo("<?apex");
        paragrafo("    titulo(\"Funcionou!\");");
        paragrafo("    paragrafo(\"Minha primeira página feita com APEX.\");");
        paragrafo("fim?>");
    caixa_fim();

    separador();

    botao("Ver documentação", "docs.apex");
    botao("Ver demonstrações", "demos.apex");
    botao("Voltar para Home", "index.apex");
fim?>