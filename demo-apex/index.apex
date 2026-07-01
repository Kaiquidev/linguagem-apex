<?apex
    pagina("APEX Language - Home");

    link("← Voltar para o site PHP", "/apexsite/index.php");

    espaco();

    titulo("APEX Language");
    paragrafo("Uma linguagem de programação 100% em português, criada como projeto acadêmico da disciplina de Compiladores.");
    paragrafo("Esta versão do site foi escrita usando a própria linguagem APEX.");

    separador();

    subtitulo("Programar em português nunca foi tão direto");
    paragrafo("A APEX permite criar páginas web, sistemas simples, exemplos interativos e demonstrações usando comandos escritos em português brasileiro.");
    paragrafo("O código APEX é transpilado para PHP e executado no navegador, transformando comandos didáticos em páginas reais.");

    espaco();

    botao("Ver documentação", "docs.apex");
    botao("Baixar e instalar", "download.apex");
    botao("Ver demonstrações", "demos.apex");
    botao("Sobre o projeto", "sobre.apex");

    separador();

    subtitulo("Por que a APEX existe?");

    caixa_inicio();
        subtitulo("Linguagem em português");
        paragrafo("A sintaxe usa comandos como se, senao, enquanto, exibir, ler, titulo, paragrafo e botao.");
        paragrafo("Isso aproxima a programação de estudantes que estão aprendendo lógica e compiladores.");
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Foco em web");
        paragrafo("Além de lógica, a APEX também gera páginas web com comandos visuais simples.");
        paragrafo("Com poucas linhas é possível criar títulos, textos, botões, caixas, links e navegação.");
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Integração com VS Code");
        paragrafo("A linguagem possui extensão para VS Code com destaque de sintaxe, execução com F5 e estrutura própria para arquivos .apex.");
        paragrafo("A proposta é aproximar a experiência de uso de linguagens modernas dentro de um editor real.");
    caixa_fim();

    separador();

    subtitulo("Recursos principais");
    paragrafo("• Variáveis com o símbolo $");
    paragrafo("• Operadores aritméticos e relacionais");
    paragrafo("• Condicionais com se e senao");
    paragrafo("• Laços com enquanto");
    paragrafo("• Funções nativas como maiusculas, minusculas, aleatorio e arredondar");
    paragrafo("• Comandos de interface como pagina, titulo, subtitulo, paragrafo, botao, link, imagem e caixa");

    separador();

    subtitulo("Fluxo de funcionamento");

    caixa_inicio();
        paragrafo("1. O programador escreve um arquivo .apex em português.");
        paragrafo("2. A extensão no VS Code reconhece a linguagem e executa o projeto.");
        paragrafo("3. O runtime faz a análise e transpila APEX para PHP.");
        paragrafo("4. O PHP gera HTML/CSS e o navegador exibe a página final.");
    caixa_fim();

    espaco();

    botao("Começar pela documentação", "docs.apex");

    separador();

    link("← Voltar para o site PHP", "/apexsite/index.php");
fim?>