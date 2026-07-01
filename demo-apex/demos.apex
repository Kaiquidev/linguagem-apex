<?apex
    pagina("Demonstrações APEX");

    titulo("Demonstrações da linguagem APEX");
    paragrafo("Nesta área estão exemplos criados com a própria linguagem APEX para provar que ela consegue gerar páginas e sistemas simples.");

    separador();

    subtitulo("Demo principal: Sistema de Pedidos");

    caixa_inicio();
        subtitulo("Restaurante APEX");
        paragrafo("Sistema funcional de pedidos escrito em APEX.");
        paragrafo("Ele usa entrada de dados, variáveis, condicionais, cálculos, cupom de desconto, funções nativas e saída formatada.");
        botao("Abrir sistema de pedidos", "restaurante.apex");
    caixa_fim();

    espaco();

    subtitulo("O que a demo mostra?");
    paragrafo("• Coleta de nome do cliente com ler()");
    paragrafo("• Seleção de prato com cardapio em ler()");
    paragrafo("• Entrada de quantidade com ler_numero()");
    paragrafo("• Uso de cupom opcional");
    paragrafo("• Cálculo de subtotal, desconto, taxa e total");
    paragrafo("• Uso de se para regras de negócio");
    paragrafo("• Uso de maiusculas(), aleatorio() e arredondar()");
    paragrafo("• Geração de resultado visual com caixa_inicio() e caixa_fim()");

    separador();

    subtitulo("Demo secundária: Este próprio site");

    caixa_inicio();
        paragrafo("As páginas Home, Docs, Download, Demos e Sobre foram escritas em arquivos .apex.");
        paragrafo("Isso demonstra que a APEX também pode ser usada para criar um site de apresentação com navegação entre páginas.");
    caixa_fim();

    separador();

    botao("Abrir restaurante", "restaurante.apex");
    botao("Ver documentação", "docs.apex");
    botao("Voltar para Home", "index.apex");
fim?>
