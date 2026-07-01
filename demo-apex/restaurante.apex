<?apex
    pagina("Restaurante APEX");

    titulo("Restaurante APEX");
    paragrafo("Sistema funcional de pedidos criado 100% com a linguagem APEX.");
    paragrafo("Use o cupom APEX10 para aplicar desconto de 10%.");

    separador();

    $cliente = ler("Nome do cliente:");
    $prato = ler("cardapio:Pizza=35|Lasanha=28|Hambúrguer=25|Salada=20|Pastel=12|Refrigerante=8");
    $quantidade = ler_numero("Quantidade:");
    $cupom = ler("opcional:Cupom de desconto:");

    $preco = 0;

    se ($prato == "Pizza") {
        $preco = 35;
    }

    se ($prato == "Lasanha") {
        $preco = 28;
    }

    se ($prato == "Hambúrguer") {
        $preco = 25;
    }

    se ($prato == "Salada") {
        $preco = 20;
    }

    se ($prato == "Pastel") {
        $preco = 12;
    }

    se ($prato == "Refrigerante") {
        $preco = 8;
    }

    $subtotal = $preco * $quantidade;
    $desconto = 0;
    $cupom = maiusculas($cupom);

    se ($cupom == "APEX10") {
        $desconto = $subtotal * 10 / 100;
    }

    $valorComDesconto = $subtotal - $desconto;
    $taxa = $valorComDesconto * 10 / 100;
    $total = $valorComDesconto + $taxa;
    $pedido = aleatorio(100, 999);

    separador();

    caixa_inicio();
        subtitulo("Resumo do Pedido");
        exibir("Pedido número: " + $pedido);
        exibir("Cliente: " + maiusculas($cliente));
        exibir("Prato escolhido: " + $prato);
        exibir("Quantidade: " + $quantidade);
        exibir("Valor unitário: R$ " + $preco);
        exibir("Subtotal: R$ " + $subtotal);
        exibir("Desconto: R$ " + arredondar($desconto));
        exibir("Taxa de serviço: R$ " + arredondar($taxa));
        exibir("Total final: R$ " + arredondar($total));
    caixa_fim();

    espaco();

    caixa_inicio();
        subtitulo("Recursos usados nesta demo");
        paragrafo("• Entrada de dados com ler() e ler_numero()");
        paragrafo("• Cardápio interativo usando prefixo cardapio:");
        paragrafo("• Variáveis para armazenar cliente, prato, quantidade e valores");
        paragrafo("• Condicionais se para escolher preço e aplicar cupom");
        paragrafo("• Funções maiusculas(), aleatorio() e arredondar()");
        paragrafo("• Cálculos de subtotal, desconto, taxa e total");
    caixa_fim();

    separador();

    botao("Fazer novo pedido", "restaurante.apex");
    botao("Voltar para Demos", "demos.apex");
    botao("Voltar para Home", "index.apex");
fim?>
