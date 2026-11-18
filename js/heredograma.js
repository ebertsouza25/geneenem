// Heredograma interativo do GeneEnem.
//
// Por que isso é JavaScript puro (sem passar pelo PHP a cada clique)?
// Porque a ideia é deixar o aluno ir adicionando pessoa por pessoa na árvore,
// e cada adição precisa aparecer na hora. Se isso fosse um formulário PHP
// comum, cada "Adicionar pessoa" recarregaria a página inteira — ruim pra
// montar uma árvore com várias gerações. Então a árvore inteira (pessoas,
// desenho e análise) vive só na memória do navegador enquanto a página
// está aberta; não é salva em lugar nenhum (dá pra adicionar o quanto quiser,
// mas ao atualizar a página ela reinicia).
//
// Importante: essa ferramenta guarda só o FENÓTIPO de cada pessoa (afetado ou
// não, igual um heredograma de verdade mostra). Ela nunca inventa o
// GENÓTIPO de ninguém — isso é proposital, porque descobrir o genótipo exato
// de uma pessoa só com o heredograma, sem mais informação, nem sempre é
// possível (é exatamente esse o tipo de pegadinha que cai no ENEM).

(function () {
    var contenedorArvore = document.getElementById("heredograma-arvore");

    // Se esta página não tem heredograma, não tem nada pra fazer aqui.
    if (!contenedorArvore) {
        return;
    }

    var SVG_NS = "http://www.w3.org/2000/svg";

    // Medidas do desenho. Ficam aqui porque o layout e o SVG usam os mesmos
    // valores: assim o quadro cresce junto com a árvore.
    var ESPACAMENTO_X = 120;
    var ESPACAMENTO_Y = 130;
    var MARGEM = 70;
    var RAIO = 13;              // metade do símbolo (26 px)
    var ALTURA_DESCIDA = 55;    // distância do casal até a linha dos irmãos

    var pessoas = [];
    var proximoId = 1;

    // ----------------------------------------------------------------------
    // Funções pequenas de apoio
    // ----------------------------------------------------------------------

    function byId(id) {
        for (var indicePessoa = 0; indicePessoa < pessoas.length; indicePessoa++) {
            if (pessoas[indicePessoa].id === id) {
                return pessoas[indicePessoa];
            }
        }
        return null;
    }

    // Nome pra mostrar: se o aluno não digitou nada, usa "Pessoa N".
    function nomeDe(pessoa) {
        return pessoa.nome ? pessoa.nome : "Pessoa " + pessoa.id;
    }

    // Mesma coisa, mas escapada pra poder entrar em innerHTML sem risco de
    // quebrar o HTML (ou de rodar algo digitado por quem usa o navegador).
    function nomeSeguro(pessoa) {
        var div = document.createElement("div");
        div.textContent = nomeDe(pessoa);
        return div.innerHTML;
    }

    function adicionarPessoaInterna(nome, sexo, afetado, paiId, maeId) {
        var pessoa = {
            id: proximoId++,
            nome: nome,
            sexo: sexo,
            afetado: afetado,
            pai: paiId || null,
            mae: maeId || null
        };
        pessoas.push(pessoa);
        return pessoa.id;
    }

    // ----------------------------------------------------------------------
    // Geração e posição de cada pessoa na árvore
    // ----------------------------------------------------------------------

    // A geração de uma pessoa é 1 a mais que a maior geração dos pais
    // conhecidos. Quem não tem pai nem mãe na árvore é "geração 1"
    // (fundador). Isso evita o aluno ter que marcar a geração na mão e
    // erra menos do que deixar ele escolher um número qualquer.
    function geracaoDe(pessoa) {
        if (pessoa._geracao !== undefined && pessoa._geracao !== null) {
            return pessoa._geracao;
        }

        var geracaoPai = pessoa.pai ? geracaoDe(byId(pessoa.pai)) : 0;
        var geracaoMae = pessoa.mae ? geracaoDe(byId(pessoa.mae)) : 0;

        pessoa._geracao = Math.max(geracaoPai, geracaoMae) + 1;
        return pessoa._geracao;
    }

    // Posição X média dos pais já calculados — usada pra tentar deixar os
    // filhos desenhados mais ou menos abaixo dos pais, em vez de uma ordem
    // aleatória.
    function mediaXDosPais(pessoa) {
        var xs = [];

        if (pessoa.pai) {
            xs.push(byId(pessoa.pai)._x);
        }
        if (pessoa.mae) {
            xs.push(byId(pessoa.mae)._x);
        }

        if (!xs.length) {
            return pessoa.id;
        }

        return (xs[0] + (xs.length > 1 ? xs[1] : xs[0])) / 2;
    }

    // Filhos de uma pessoa: quem tem essa pessoa como pai ou mãe.
    function filhosDe(pessoa) {
        return pessoas.filter(function (outra) {
            return outra.pai === pessoa.id || outra.mae === pessoa.id;
        });
    }

    // Parceiro(a): a outra pessoa com quem ela tem um filho. É o que permite
    // alinhar o casal na mesma geração e desenhar a linha horizontal da união.
    function parceiroDe(pessoa) {
        var filhos = filhosDe(pessoa);

        for (var indice = 0; indice < filhos.length; indice++) {
            var filho = filhos[indice];

            if (filho.pai && filho.mae) {
                return byId(filho.pai === pessoa.id ? filho.mae : filho.pai);
            }
        }

        return null;
    }

    // Gerações com o casal alinhado.
    //
    // A regra é a mesma de antes: a pessoa tem uma geração a mais que a maior
    // geração dos pais na árvore. O problema era o casal: quem não tem pais
    // cadastrados (o pai, por exemplo) ficava na geração 1 e aparecia na linha
    // dos avós. Por isso, depois do cálculo, quem é parceiro de alguém que tem
    // pais na árvore passa para a geração desse parceiro.
    function calcularGeracoes() {
        pessoas.forEach(function (pessoa) { pessoa._geracao = null; });
        pessoas.forEach(geracaoDe);

        var mudou = true;
        var rodada = 0;

        while (mudou && rodada < 10) {
            mudou = false;
            rodada = rodada + 1;

            pessoas.forEach(function (crianca) {
                var pai = crianca.pai ? byId(crianca.pai) : null;
                var mae = crianca.mae ? byId(crianca.mae) : null;

                if (!pai || !mae) {
                    return;
                }

                var geracaoDoCasal = Math.max(pai._geracao, mae._geracao);

                if (pai._geracao !== geracaoDoCasal || mae._geracao !== geracaoDoCasal) {
                    pai._geracao = geracaoDoCasal;
                    mae._geracao = geracaoDoCasal;
                    mudou = true;
                }
            });
        }
    }

    // Chave de ordenação da linha: o filho fica perto do meio dos pais e o
    // parceiro fica do lado de quem tem pais na árvore.
    function chaveDeOrdem(pessoa) {
        if (pessoa.pai || pessoa.mae) {
            return mediaXDosPais(pessoa);
        }

        var parceiro = parceiroDe(pessoa);

        if (parceiro && (parceiro.pai || parceiro.mae)) {
            return mediaXDosPais(parceiro) + 0.1;
        }

        return pessoa.id;
    }

    // Deixa a linha em ordem de x e garante a distância mínima entre símbolos.
    function organizarLinha(linha) {
        linha.sort(function (pessoaA, pessoaB) { return pessoaA._x - pessoaB._x; });

        linha.forEach(function (pessoa, indice) {
            var minimoX = MARGEM + indice * ESPACAMENTO_X;

            if (pessoa._x < minimoX) {
                pessoa._x = minimoX;
            }
        });
    }

    // Centraliza cada casal sobre os filhos, de baixo para cima. É o que deixa
    // os filhos abaixo do casal em vez de deslocados para um lado.
    function centralizarPais(linhas) {
        for (var indiceLinha = linhas.length - 2; indiceLinha >= 0; indiceLinha--) {
            var linha = linhas[indiceLinha];
            var casais = [];

            linha.forEach(function (pessoa) {
                var parceiro = parceiroDe(pessoa);

                if (!parceiro || parceiro._geracao !== pessoa._geracao) {
                    return;
                }

                var chave = Math.min(pessoa.id, parceiro.id) + "-" + Math.max(pessoa.id, parceiro.id);

                if (casais.indexOf(chave) === -1) {
                    casais.push(chave);
                }
            });

            casais.forEach(function (chave) {
                var ids = chave.split("-");
                var primeira = byId(Number(ids[0]));
                var segunda = byId(Number(ids[1]));
                var filhos = filhosDe(primeira).filter(function (filho) {
                    return filho.pai === segunda.id || filho.mae === segunda.id;
                });

                if (!filhos.length) {
                    return;
                }

                var somaX = 0;

                filhos.forEach(function (filho) {
                    somaX = somaX + filho._x;
                });

                var meioDosFilhos = somaX / filhos.length;
                var meioDoCasal = (primeira._x + segunda._x) / 2;
                var deslocamento = meioDosFilhos - meioDoCasal;

                primeira._x = primeira._x + deslocamento;
                segunda._x = segunda._x + deslocamento;
            });

            organizarLinha(linha);
        }
    }

    // Recalcula geração e posição (x, y) de todo mundo. É chamado antes de
    // desenhar, porque cada "Adicionar pessoa" pode mudar a árvore inteira.
    function recalcularLayout() {
        calcularGeracoes();

        var maiorGeracao = 0;
        pessoas.forEach(function (pessoa) {
            if (pessoa._geracao > maiorGeracao) {
                maiorGeracao = pessoa._geracao;
            }
        });

        var linhas = [];

        for (var numeroGeracao = 1; numeroGeracao <= maiorGeracao; numeroGeracao++) {
            linhas.push(pessoas.filter(function (pessoa) { return pessoa._geracao === numeroGeracao; }));
        }

        // Primeiro coloca cada linha em sequência, já na ordem certa.
        linhas.forEach(function (linha, indice) {
            linha.sort(function (pessoaA, pessoaB) {
                var diferenca = chaveDeOrdem(pessoaA) - chaveDeOrdem(pessoaB);

                if (diferenca !== 0) {
                    return diferenca;
                }

                return pessoaA.id - pessoaB.id;
            });

            linha.forEach(function (pessoa, indicePessoa) {
                pessoa._x = MARGEM + indicePessoa * ESPACAMENTO_X;
                pessoa._y = MARGEM + indice * ESPACAMENTO_Y;
            });
        });

        // Com a árvore vazia não há posição para centralizar nem desenhar.
        if (!pessoas.length) {
            return { linhas: [], maiorGeracao: 0 };
        }

        centralizarPais(linhas);

        // Se a centralização empurrou alguém para a esquerda da margem, desloca
        // a árvore inteira de volta.
        var menorX = pessoas[0]._x;

        pessoas.forEach(function (pessoa) {
            if (pessoa._x < menorX) {
                menorX = pessoa._x;
            }
        });

        if (menorX < MARGEM) {
            pessoas.forEach(function (pessoa) {
                pessoa._x = pessoa._x + (MARGEM - menorX);
            });
        }

        return { linhas: linhas, maiorGeracao: maiorGeracao };
    }

    // ----------------------------------------------------------------------
    // Desenho (SVG)
    // ----------------------------------------------------------------------

    function criarLinhaSvg(x1, y1, x2, y2, classe) {
        var linha = document.createElementNS(SVG_NS, "line");
        linha.setAttribute("x1", x1);
        linha.setAttribute("y1", y1);
        linha.setAttribute("x2", x2);
        linha.setAttribute("y2", y2);
        linha.setAttribute("class", classe);
        return linha;
    }

    function criarSimboloPessoa(pessoa) {
        var TAMANHO = 26;

        var grupo = document.createElementNS(SVG_NS, "g");
        grupo.setAttribute("class", "hg-pessoa" + (pessoa.sexo === "M" ? " hg-m" : " hg-f") + (pessoa.afetado ? " hg-afetado" : ""));

        var titulo = document.createElementNS(SVG_NS, "title");
        titulo.textContent = nomeDe(pessoa) + " — " + (pessoa.sexo === "M" ? "homem" : "mulher") +
            ", " + (pessoa.afetado ? "afetado(a)" : "não afetado(a)");
        grupo.appendChild(titulo);

        var forma;

        if (pessoa.sexo === "M") {
            // Homem = quadrado (convenção padrão de heredograma)
            forma = document.createElementNS(SVG_NS, "rect");
            forma.setAttribute("x", pessoa._x - TAMANHO / 2);
            forma.setAttribute("y", pessoa._y - TAMANHO / 2);
            forma.setAttribute("width", TAMANHO);
            forma.setAttribute("height", TAMANHO);
        } else {
            // Mulher = círculo (convenção padrão de heredograma)
            forma = document.createElementNS(SVG_NS, "circle");
            forma.setAttribute("cx", pessoa._x);
            forma.setAttribute("cy", pessoa._y);
            forma.setAttribute("r", TAMANHO / 2);
        }

        forma.setAttribute("class", "hg-forma");
        grupo.appendChild(forma);

        var texto = document.createElementNS(SVG_NS, "text");
        texto.setAttribute("x", pessoa._x);
        texto.setAttribute("y", pessoa._y + TAMANHO / 2 + 16);
        texto.setAttribute("text-anchor", "middle");
        texto.setAttribute("class", "hg-texto");
        texto.textContent = nomeDe(pessoa);
        grupo.appendChild(texto);

        return grupo;
    }

    function desenharArvore() {
        // Estado inicial: árvore vazia mostra só a mensagem de convite.
        if (!pessoas.length) {
            contenedorArvore.innerHTML = '<p class="muted pequeno">Adicione pessoas para começar a montar a árvore.</p>';
            atualizarSelects();
            atualizarLista();
            return;
        }

        var info = recalcularLayout();

        // A largura acompanha a posição real da última pessoa: a centralização
        // dos casais pode deixar a árvore mais larga ou mais estreita do que o
        // número de pessoas sugere.
        var maiorX = 0;

        pessoas.forEach(function (pessoa) {
            if (pessoa._x > maiorX) {
                maiorX = pessoa._x;
            }
        });

        var largura = Math.max(maiorX + MARGEM, 2 * MARGEM + ESPACAMENTO_X);
        var altura = MARGEM + (info.maiorGeracao - 1) * ESPACAMENTO_Y + 60;

        var svg = document.createElementNS(SVG_NS, "svg");
        svg.setAttribute("viewBox", "0 0 " + largura + " " + altura);
        svg.setAttribute("width", largura);
        svg.setAttribute("height", altura);
        svg.setAttribute("class", "hg-svg");

        // 1) As linhas de parentesco primeiro, pra ficarem por baixo dos símbolos.
        //
        // Convenção do heredograma: horizontal = união do casal, vertical =
        // descendência e uma linha horizontal única ligando todos os irmãos.
        // Cada casal é desenhado uma vez só, mesmo tendo vários filhos.
        var casaisJaDesenhados = [];
        var gruposDeFilhos = [];

        // Reúne os filhos do mesmo pai e mãe em um grupo.
        pessoas.forEach(function (crianca) {
            var pai = crianca.pai ? byId(crianca.pai) : null;
            var mae = crianca.mae ? byId(crianca.mae) : null;

            if (!pai && !mae) {
                return;
            }

            var chave = (pai ? pai.id : 0) + "-" + (mae ? mae.id : 0);
            var grupo = null;

            gruposDeFilhos.forEach(function (grupoExistente) {
                if (grupoExistente.chave === chave) {
                    grupo = grupoExistente;
                }
            });

            if (!grupo) {
                grupo = { chave: chave, pais: [], filhos: [] };

                if (pai) {
                    grupo.pais.push(pai);
                }
                if (mae) {
                    grupo.pais.push(mae);
                }

                gruposDeFilhos.push(grupo);
            }

            grupo.filhos.push(crianca);
        });

        gruposDeFilhos.forEach(function (grupo) {
            var mesmoCasal = grupo.pais.length === 2 &&
                grupo.pais[0]._geracao === grupo.pais[1]._geracao;

            var origemX;
            var origemY;

            if (mesmoCasal) {
                origemX = (grupo.pais[0]._x + grupo.pais[1]._x) / 2;
                origemY = grupo.pais[0]._y;

                var chaveCasal = Math.min(grupo.pais[0].id, grupo.pais[1].id) + "-" +
                    Math.max(grupo.pais[0].id, grupo.pais[1].id);

                // Linha da união: uma por casal, com respiro para não
                // encostar nos símbolos.
                if (casaisJaDesenhados.indexOf(chaveCasal) === -1) {
                    casaisJaDesenhados.push(chaveCasal);

                    var esquerda = Math.min(grupo.pais[0]._x, grupo.pais[1]._x);
                    var direita = Math.max(grupo.pais[0]._x, grupo.pais[1]._x);

                    svg.appendChild(criarLinhaSvg(esquerda + RAIO, origemY, direita - RAIO, origemY, "hg-linha-casal"));
                }
            } else {
                // Só existe um dos pais na árvore (ou estão em gerações
                // diferentes): desce do próprio símbolo, sem inventar casal.
                origemX = grupo.pais[0]._x;
                origemY = grupo.pais[0]._y;
            }

            var yIrmaos = origemY + ALTURA_DESCIDA;
            var menorX = origemX;
            var maiorXGrupo = origemX;

            grupo.filhos.forEach(function (filho) {
                if (filho._x < menorX) {
                    menorX = filho._x;
                }
                if (filho._x > maiorXGrupo) {
                    maiorXGrupo = filho._x;
                }
            });

            // Descida do casal até a linha dos irmãos.
            svg.appendChild(criarLinhaSvg(origemX, origemY + RAIO, origemX, yIrmaos, "hg-linha-filho"));

            // Linha horizontal dos irmãos.
            svg.appendChild(criarLinhaSvg(menorX, yIrmaos, maiorXGrupo, yIrmaos, "hg-linha-filho"));

            // Uma descida para cada filho.
            grupo.filhos.forEach(function (filho) {
                svg.appendChild(criarLinhaSvg(filho._x, yIrmaos, filho._x, filho._y - RAIO, "hg-linha-filho"));
            });
        });

        // 2) Os símbolos das pessoas por cima das linhas.
        pessoas.forEach(function (pessoa) {
            svg.appendChild(criarSimboloPessoa(pessoa));
        });

        contenedorArvore.innerHTML = "";
        contenedorArvore.appendChild(svg);

        atualizarSelects();
        atualizarLista();
    }

    // ----------------------------------------------------------------------
    // Selects de pai/mãe e lista de pessoas (texto)
    // ----------------------------------------------------------------------

    function preencherSelect(select, sexo, rotuloVazio) {
        var valorAtual = select.value;
        select.innerHTML = "";

        var opcaoVazia = document.createElement("option");
        opcaoVazia.value = "";
        opcaoVazia.textContent = rotuloVazio;
        select.appendChild(opcaoVazia);

        pessoas.filter(function (pessoa) { return pessoa.sexo === sexo; }).forEach(function (pessoa) {
            var opcao = document.createElement("option");
            opcao.value = String(pessoa.id);
            opcao.textContent = nomeDe(pessoa);
            select.appendChild(opcao);
        });

        // Mantém a escolha anterior se a pessoa ainda existir na árvore.
        var aindaExiste = pessoas.some(function (pessoa) { return String(pessoa.id) === valorAtual; });
        select.value = aindaExiste ? valorAtual : "";
    }

    function atualizarSelects() {
        preencherSelect(document.getElementById("hg-pai"), "M", "Sem pai na árvore");
        preencherSelect(document.getElementById("hg-mae"), "F", "Sem mãe na árvore");
    }

    function atualizarLista() {
        var lista = document.getElementById("heredograma-lista");
        lista.innerHTML = "";

        pessoas.forEach(function (pessoa) {
            var pai = pessoa.pai ? byId(pessoa.pai) : null;
            var mae = pessoa.mae ? byId(pessoa.mae) : null;

            var pais = [];
            if (pai) { pais.push("pai: " + nomeDe(pai)); }
            if (mae) { pais.push("mãe: " + nomeDe(mae)); }

            var li = document.createElement("li");

            var span = document.createElement("span");
            span.textContent = nomeDe(pessoa) + " — " + (pessoa.sexo === "M" ? "homem" : "mulher") +
                ", " + (pessoa.afetado ? "afetado(a)" : "não afetado(a)") +
                ", geração " + pessoa._geracao +
                (pais.length ? " (" + pais.join(", ") + ")" : " (fundador da árvore)");

            var botao = document.createElement("button");
            botao.type = "button";
            botao.className = "hg-remover";
            botao.textContent = "Remover";
            botao.addEventListener("click", function () { removerPessoa(pessoa.id); });

            li.appendChild(span);
            li.appendChild(botao);
            lista.appendChild(li);
        });
    }

    function removerPessoa(id) {
        pessoas = pessoas.filter(function (pessoa) { return pessoa.id !== id; });

        // Quem apontava pra essa pessoa como pai/mãe fica sem esse vínculo,
        // em vez de quebrar (a pessoa continua na árvore, só perde aquele elo).
        pessoas.forEach(function (pessoa) {
            if (pessoa.pai === id) { pessoa.pai = null; }
            if (pessoa.mae === id) { pessoa.mae = null; }
        });

        desenharArvore();
    }

    function limparTudo() {
        pessoas = [];
        proximoId = 1;

        var resultado = document.getElementById("hg-resultado-analise");
        if (resultado) {
            resultado.innerHTML = "";
        }

        desenharArvore();
    }

// ----------------------------------------------------------------------
// Análise de compatibilidade com os 4 padrões de herança
// ----------------------------------------------------------------------
//
// Verifica se alguém na árvore contradiz regras de cada padrão.
// Se ninguém contradiz, o padrão é compatível, mas isso não confirma
// que seja o real, pois mais de um padrão pode ser compatível.
    function verificarCompatibilidade(padrao) {
        var problemas = [];

        pessoas.forEach(function (crianca) {
            var pai = crianca.pai ? byId(crianca.pai) : null;
            var mae = crianca.mae ? byId(crianca.mae) : null;

            // Sem nenhum dos pais na árvore não tem o que testar nessa pessoa.
            if (!pai && !mae) {
                return;
            }

            var nomeC = nomeSeguro(crianca);

            if (padrao === "ad" || padrao === "xld") {
                // Regra geral de toda herança dominante: pra ser afetado (sem
                // mutação nova), é preciso ter herdado o alelo de algum dos pais.
                if (pai && mae && !pai.afetado && !mae.afetado && crianca.afetado) {
                    problemas.push(
                        nomeC + " é afetado(a), mas nenhum dos pais é — numa herança " +
                        "dominante isso só aconteceria com uma mutação nova, e a árvore não indica isso."
                    );
                }
            }

            if (padrao === "ar") {
                if (pai && mae && pai.afetado && mae.afetado && !crianca.afetado) {
                    problemas.push(
                        nomeC + " não é afetado(a), mas os dois pais são — dois pais afetados " +
                        "(aa × aa) só geram filhos afetados na herança autossômica recessiva."
                    );
                }
            }

            if (padrao === "xld") {
                if (pai && pai.afetado && crianca.sexo === "F" && !crianca.afetado) {
                    problemas.push(
                        nomeC + " é filha de um pai afetado, mas não é afetada — na ligada ao X " +
                        "dominante, o pai afetado passa esse X para todas as filhas."
                    );
                }

                if (pai && pai.afetado && mae && !mae.afetado && crianca.sexo === "M" && crianca.afetado) {
                    problemas.push(
                        nomeC + " é homem afetado, filho de pai afetado e mãe não afetada — isso não " +
                        "é possível, porque filhos recebem o Y do pai, nunca o X."
                    );
                }
            }

            if (padrao === "xlr") {
                if (mae && mae.afetado && crianca.sexo === "M" && !crianca.afetado) {
                    problemas.push(
                        nomeC + " é filho de mãe afetada, mas não é afetado — mãe afetada só tem X " +
                        "com o alelo recessivo pra passar, então todo filho homem dela deveria ser afetado."
                    );
                }

                if (pai && crianca.sexo === "F" && crianca.afetado && !pai.afetado) {
                    problemas.push(
                        nomeC + " é uma filha afetada, mas o pai não é — na ligada ao X recessiva, a " +
                        "filha só é afetada recebendo o alelo recessivo dos dois pais, o que exige um pai também afetado."
                    );
                }
            }
        });

        return problemas;
    }

    // ----------------------------------------------------------------------
    // Ligação com os botões da página
    // ----------------------------------------------------------------------

    var campoNome = document.getElementById("hg-nome");
    var campoSexo = document.getElementById("hg-sexo");
    var campoAfetado = document.getElementById("hg-afetado");
    var campoPai = document.getElementById("hg-pai");
    var campoMae = document.getElementById("hg-mae");
    var campoErro = document.getElementById("hg-erro-form");

    document.getElementById("hg-adicionar").addEventListener("click", function () {
        var paiValor = campoPai.value;
        var maeValor = campoMae.value;

        campoErro.textContent = "";

        if (paiValor && maeValor && paiValor === maeValor) {
            campoErro.textContent = "Pai e mãe não podem ser a mesma pessoa.";
            return;
        }

        adicionarPessoaInterna(
            campoNome.value.trim(),
            campoSexo.value,
            campoAfetado.checked,
            paiValor ? Number(paiValor) : null,
            maeValor ? Number(maeValor) : null
        );

        // Limpa só o nome e o "afetado": sexo e pais continuam escolhidos,
        // porque é comum adicionar vários filhos do mesmo casal em seguida.
        campoNome.value = "";
        campoAfetado.checked = false;

        desenharArvore();
    });

    document.getElementById("hg-limpar").addEventListener("click", limparTudo);

    // Exemplo pronto: daltonismo (herança ligada ao X recessiva), parecido
    // com o heredograma da revisão 5 e da questão sobre daltonismo do banco de
    // questões. Os pais aparecem como "não afetados" porque é assim que uma
    // mãe portadora se apresenta num heredograma real — ela só é identificada
    // como portadora observando os filhos, nunca pelo próprio fenótipo.
    document.getElementById("hg-exemplo").addEventListener("click", function () {
        limparTudo();

        var avo = adicionarPessoaInterna("Avó materna", "F", false, null, null);
        var avoh = adicionarPessoaInterna("Avô materno", "M", false, null, null);
        var mae = adicionarPessoaInterna("Mãe", "F", false, avoh, avo);
        var pai = adicionarPessoaInterna("Pai", "M", false, null, null);

        adicionarPessoaInterna("Filho 1", "M", true, pai, mae);
        adicionarPessoaInterna("Filha 1", "F", false, pai, mae);
        adicionarPessoaInterna("Filho 2", "M", false, pai, mae);
        adicionarPessoaInterna("Ana", "F", false, pai, mae);

        desenharArvore();
    });

    document.getElementById("hg-analisar").addEventListener("click", function () {
        var padrao = document.getElementById("hg-padrao").value;
        var resultado = document.getElementById("hg-resultado-analise");

        var temAlguemComPai = pessoas.some(function (pessoa) { return pessoa.pai || pessoa.mae; });

        if (!temAlguemComPai) {
            resultado.innerHTML = '<p class="muted pequeno">Adicione pelo menos uma pessoa ligada a um pai ou mãe na árvore para testar um padrão de herança.</p>';
            return;
        }

        var problemas = verificarCompatibilidade(padrao);

        if (!problemas.length) {
            resultado.innerHTML =
                '<div class="hg-ok">Compatível com os dados da árvore até agora. Isso não ' +
                'prova que esse é o padrão real — às vezes mais de um padrão é compatível com a ' +
                'mesma árvore. Quanto mais pessoas e gerações você adicionar, mais confiável fica a análise.</div>';
            return;
        }

        var html = '<div class="hg-incompativel"><strong>Incompatível com esse padrão — motivo(s) encontrado(s):</strong><ul>';
        problemas.forEach(function (texto) {
            html += "<li>" + texto + "</li>";
        });
        html += "</ul></div>";
        resultado.innerHTML = html;
    });

    // Estado inicial: árvore vazia com a mensagem de "adicione pessoas".
    desenharArvore();
})();
