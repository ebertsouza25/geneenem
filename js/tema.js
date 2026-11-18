// Tema claro / escuro do GeneEnem.
// Mesmo comportamento do Serpenteando: um botão redondo, com sol e lua, que
// alterna o tema em qualquer página. O tema escolhido fica salvo no navegador
// e é aplicado antes da página pintar (script curto no <head>), sem piscar.

(function () {
    var raiz = document.documentElement;
    var botao = document.getElementById("tema-botao");
    var metaCor = document.querySelector('meta[name="theme-color"]');

    // Aplica o tema na página e deixa o botão coerente com ele.
    function aplicar(escuro) {
        raiz.classList.toggle("escuro", escuro);

        if (botao) {
            var rotulo = escuro ? "Ativar modo claro" : "Ativar modo escuro";

            botao.setAttribute("aria-pressed", escuro ? "true" : "false");
            botao.setAttribute("aria-label", rotulo);
            botao.setAttribute("title", rotulo);
        }

        if (metaCor) {
            metaCor.setAttribute("content", escuro ? "#060f0c" : "#082a1f");
        }
    }

    var salvo = null;

    try {
        salvo = localStorage.getItem("geneenem.tema");
    } catch (erro) {
        // navegador sem localStorage: fica no tema claro
    }

    aplicar(salvo === "escuro");

    if (botao) {
        botao.addEventListener("click", function () {
            // O próximo tema é o contrário do que está na tela agora.
            var escuro = !raiz.classList.contains("escuro");

            aplicar(escuro);

            try {
                localStorage.setItem("geneenem.tema", escuro ? "escuro" : "claro");
            } catch (erro) {
                // sem localStorage a escolha vale só nesta visita
            }
        });
    }
})();
