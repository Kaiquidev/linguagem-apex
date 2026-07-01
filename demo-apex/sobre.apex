<?apex
    pagina("Sobre a APEX");

    titulo("Sobre a APEX");
    paragrafo("A APEX nasceu como projeto acadêmico da disciplina de Compiladores no curso de Engenharia de Software.");
    paragrafo("A proposta foi criar uma linguagem didática, em português, capaz de demonstrar análise, transpilação e execução web.");

    separador();

    subtitulo("Objetivo do projeto");
    caixa_inicio();
        paragrafo("Criar uma linguagem de programação própria, com sintaxe clara e comandos em português brasileiro.");
        paragrafo("Integrar a linguagem ao VS Code por meio de uma extensão.");
        paragrafo("Permitir que arquivos .apex sejam executados e transformados em páginas web.");
        paragrafo("Demonstrar conceitos de compiladores de forma visual e prática.");
    caixa_fim();

    separador();

    subtitulo("Arquitetura da APEX");
    caixa_inicio();
        paragrafo("1. Código-fonte escrito em arquivo .apex.");
        paragrafo("2. Reconhecimento da linguagem no VS Code.");
        paragrafo("3. Análise léxica e sintática pelo runtime.");
        paragrafo("4. Transpilação de APEX para PHP.");
        paragrafo("5. Execução no navegador com resultado em HTML/CSS.");
    caixa_fim();

    separador();

    subtitulo("Equipe");

    caixa_inicio();
        imagem("assets/img/equipe/gabriel.jpeg", "Foto de Gabriel Nascimento");
        subtitulo("Gabriel Nascimento");
        paragrafo("Desenvolvimento da linguagem APEX");
    caixa_fim();

    espaco();

    caixa_inicio();
        imagem("assets/img/equipe/kaiqui.jpeg", "Foto de Kaiqui Junior");
        subtitulo("Kaiqui Junior");
        paragrafo("Desenvolvimento da linguagem APEX");
    caixa_fim();

    espaco();

    caixa_inicio();
        imagem("assets/img/equipe/yolando.jpeg", "Foto de Yolando Junior");
        subtitulo("Yolando Junior");
        paragrafo("Desenvolvimento da linguagem APEX");
    caixa_fim();

    separador();

    subtitulo("Tecnologias envolvidas");
    paragrafo("• APEX como linguagem criada pelo grupo");
    paragrafo("• PHP como linguagem-alvo da transpilação");
    paragrafo("• VS Code como ambiente de desenvolvimento");
    paragrafo("• Extensão VSIX para suporte da linguagem");
    paragrafo("• HTML/CSS como resultado final exibido no navegador");

    separador();

    botao("Ver documentação", "docs.apex");
    botao("Ver demos", "demos.apex");
    botao("Voltar para Home", "index.apex");
fim?>
