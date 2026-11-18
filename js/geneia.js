// Chat da GeneIA: abre e fecha a janela, envia a pergunta para o PHP e mostra
// a resposta sem recarregar a página. Toda validação de assunto é feita no
// servidor; aqui só cuidamos da interface.

(function () {
    "use strict";

    var raiz = document.getElementById("geneia");
    var botao = document.getElementById("geneia-fab");
    var janela = document.getElementById("geneia-janela");
    var botaoFechar = document.getElementById("geneia-fechar");
    var form = document.getElementById("geneia-form");
    var campo = document.getElementById("geneia-campo");
    var enviar = document.getElementById("geneia-enviar");
    var mensagens = document.getElementById("geneia-mensagens");

    if (!raiz || !botao || !janela || !form || !campo || !mensagens) {
        return;
    }

    var aberto = false;
    var enviando = false;

    function rolarParaOFim() {
        mensagens.scrollTop = mensagens.scrollHeight;
    }

    function abrir() {
        raiz.classList.add("aberta");
        janela.setAttribute("aria-hidden", "false");
        botao.setAttribute("aria-expanded", "true");
        aberto = true;

        window.setTimeout(function () {
            campo.focus();
        }, 220);

        rolarParaOFim();
    }

    function fechar() {
        raiz.classList.remove("aberta");
        janela.setAttribute("aria-hidden", "true");
        botao.setAttribute("aria-expanded", "false");
        aberto = false;
        botao.focus();
    }

    botao.addEventListener("click", function () {
        if (aberto) {
            fechar();
        } else {
            abrir();
        }
    });

    if (botaoFechar) {
        botaoFechar.addEventListener("click", fechar);
    }

    document.addEventListener("keydown", function (evento) {
        if (evento.key === "Escape" && aberto) {
            fechar();
        }
    });

    // Cria uma bolha de mensagem. O texto entra por textContent, então uma
    // resposta com tags aparece como texto e não como HTML.
    function criarMensagem(texto, tipo) {
        var bloco = document.createElement("div");
        bloco.className = "geneia-msg geneia-msg-" + tipo;

        var autor = document.createElement("span");
        autor.className = "geneia-msg-autor";
        autor.textContent = tipo === "aluno" ? "Você" : (tipo === "erro" ? "Aviso" : "GeneIA");

        var paragrafo = document.createElement("p");
        paragrafo.textContent = texto;

        bloco.appendChild(autor);
        bloco.appendChild(paragrafo);
        mensagens.appendChild(bloco);
        rolarParaOFim();

        return bloco;
    }

    // Três pontinhos enquanto a API responde. O autor não se repete aqui:
    // o cabeçalho já identifica a GeneIA e o indicador fica mais discreto.
    function criarDigitando() {
        var bloco = document.createElement("div");
        bloco.className = "geneia-msg geneia-msg-ia geneia-digitando";

        var paragrafo = document.createElement("p");
        paragrafo.setAttribute("aria-label", "GeneIA está preparando a resposta");

        for (var indice = 0; indice < 3; indice++) {
            var ponto = document.createElement("span");
            ponto.setAttribute("aria-hidden", "true");
            paragrafo.appendChild(ponto);
        }

        bloco.appendChild(paragrafo);
        mensagens.appendChild(bloco);
        rolarParaOFim();

        return bloco;
    }

    function ajustarAlturaDoCampo() {
        campo.style.height = "auto";
        // + 2px das bordas: o CSS usa box-sizing: border-box.
        campo.style.height = Math.min(campo.scrollHeight + 2, 104) + "px";
    }

    campo.addEventListener("input", ajustarAlturaDoCampo);

    // Enter envia; Shift+Enter quebra a linha.
    campo.addEventListener("keydown", function (evento) {
        if (evento.key === "Enter" && !evento.shiftKey) {
            evento.preventDefault();

            if (!enviando) {
                if (typeof form.requestSubmit === "function") {
                    form.requestSubmit();
                } else {
                    form.dispatchEvent(new Event("submit", { cancelable: true, bubbles: true }));
                }
            }
        }
    });

    form.addEventListener("submit", function (evento) {
        evento.preventDefault();

        // Trava contra clique duplo / Enter repetido enquanto a resposta não chega.
        if (enviando) {
            return;
        }

        var texto = campo.value.trim();

        if (texto === "") {
            campo.focus();
            return;
        }

        enviando = true;
        enviar.disabled = true;

        criarMensagem(texto, "aluno");

        campo.value = "";
        ajustarAlturaDoCampo();

        var digitando = criarDigitando();
        var controlador = window.AbortController ? new AbortController() : null;
        var limiteTempo = window.setTimeout(function () {
            if (controlador) {
                controlador.abort();
            }
        }, 55000);

        fetch(raiz.getAttribute("data-endpoint"), {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-Token": raiz.getAttribute("data-csrf")
            },
            body: JSON.stringify({ mensagem: texto }),
            signal: controlador ? controlador.signal : undefined
        })
            .then(function (resposta) {
                return resposta.json()
                    .catch(function () {
                        return { erro: "Resposta inválida do servidor." };
                    })
                    .then(function (dados) {
                        return { status: resposta.status, dados: dados };
                    });
            })
            .then(function (resultado) {
                digitando.remove();

                var dados = resultado.dados || {};

                if (resultado.status >= 200 && resultado.status < 300 && dados.resposta) {
                    criarMensagem(dados.resposta, "ia");
                } else {
                    criarMensagem(dados.erro || "Não consegui responder agora. Tente novamente.", "erro");
                }
            })
            .catch(function (erro) {
                if (digitando.parentNode) {
                    digitando.remove();
                }

                var aviso = "Não consegui falar com a GeneIA agora. Verifique a conexão e tente novamente.";

                if (erro && erro.name === "AbortError") {
                    aviso = "A GeneIA demorou para responder. Tente enviar a pergunta de novo.";
                }

                criarMensagem(aviso, "erro");
            })
            .then(function () {
                window.clearTimeout(limiteTempo);
                enviando = false;
                enviar.disabled = false;
                campo.focus();
            });
    });

})();
