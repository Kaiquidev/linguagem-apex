<?apex
    pagina("Documentação da APEX");

    titulo("Documentação da linguagem APEX");
    paragrafo("Guia básico para escrever programas, páginas e sistemas simples usando a linguagem APEX.");

    separador();

    subtitulo("Estrutura de um arquivo APEX");
    paragrafo("Todo programa começa com o delimitador <?apex e termina com fim?>.");

    caixa_inicio();
        paragrafo("<?apex");
        paragrafo("    exibir(\"Olá, mundo!\");");
        paragrafo("fim?>");
    caixa_fim();

    espaco();

    subtitulo("Variáveis");
    paragrafo("As variáveis começam com o símbolo $, seguindo uma ideia parecida com PHP.");

    caixa_inicio();
        paragrafo("$nome = \"Kaiqui\";");
        paragrafo("$idade = 22;");
        paragrafo("$preco = 35;");
        paragrafo("exibir(\"Nome: \" + $nome);");
    caixa_fim();

    espaco();

    subtitulo("Operadores");
    paragrafo("A APEX suporta operadores aritméticos e relacionais.");
    paragrafo("• + soma ou concatenação");
    paragrafo("• - subtração");
    paragrafo("• * multiplicação");
    paragrafo("• / divisão");
    paragrafo("• == igual");
    paragrafo("• != diferente");
    paragrafo("• > maior");
    paragrafo("• < menor");
    paragrafo("• >= maior ou igual");
    paragrafo("• <= menor ou igual");

    separador();

    subtitulo("Condicionais");
    paragrafo("O comando se permite tomar decisões dentro do programa.");

    caixa_inicio();
        paragrafo("$idade = 18;");
        paragrafo("se ($idade >= 18) {");
        paragrafo("    exibir(\"Maior de idade\");");
        paragrafo("} senao {");
        paragrafo("    exibir(\"Menor de idade\");");
        paragrafo("}");
    caixa_fim();

    espaco();

    subtitulo("Laço de repetição");
    paragrafo("O comando enquanto repete um bloco enquanto a condição for verdadeira.");

    caixa_inicio();
        paragrafo("$i = 1;");
        paragrafo("enquanto ($i <= 5) {");
        paragrafo("    exibir(\"Número: \" + $i);");
        paragrafo("    $i = $i + 1;");
        paragrafo("}");
    caixa_fim();

    separador();

    subtitulo("Entrada e saída");
    paragrafo("A linguagem possui comandos para exibir dados e coletar informações do usuário.");
    paragrafo("• exibir(texto): mostra uma mensagem na tela");
    paragrafo("• ler(pergunta): recebe texto digitado pelo usuário");
    paragrafo("• ler_numero(pergunta): recebe um valor numérico");

    caixa_inicio();
        paragrafo("$nome = ler(\"Qual é seu nome?\");");
        paragrafo("$idade = ler_numero(\"Qual é sua idade?\");");
        paragrafo("exibir(\"Olá, \" + $nome);");
    caixa_fim();

    espaco();

    subtitulo("Funções nativas");
    paragrafo("• maiusculas(texto): converte texto para letras maiúsculas");
    paragrafo("• minusculas(texto): converte texto para letras minúsculas");
    paragrafo("• tamanho(texto): retorna a quantidade de caracteres");
    paragrafo("• aleatorio(min, max): gera número aleatório");
    paragrafo("• arredondar(valor): arredonda um número");

    separador();

    subtitulo("Comandos de interface web");
    paragrafo("A APEX também consegue gerar páginas web com comandos simples.");
    paragrafo("• pagina(\"Título\"): define o título da página");
    paragrafo("• titulo(\"Texto\"): cria um título principal");
    paragrafo("• subtitulo(\"Texto\"): cria um subtítulo");
    paragrafo("• paragrafo(\"Texto\"): cria um parágrafo");
    paragrafo("• botao(\"Texto\", \"destino.apex\"): cria um botão de navegação");
    paragrafo("• link(\"Texto\", \"destino\"): cria um link");
    paragrafo("• imagem(\"url\", \"descrição\"): exibe uma imagem");
    paragrafo("• caixa_inicio() e caixa_fim(): criam uma área visual em formato de card");
    paragrafo("• separador(): cria uma linha de separação");
    paragrafo("• espaco(): cria espaçamento vertical");

    separador();

    botao("Ver sistema de exemplo", "restaurante.apex");
    botao("Voltar para Home", "index.apex");
fim?>
