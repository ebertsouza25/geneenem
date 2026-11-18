// Interface do GeneEnem: menu do celular e progresso do quiz.
// Nenhuma validação de formulário acontece aqui: quem valida é o PHP, depois do
// envio. Este arquivo só cuida da apresentação.

(function () {

    // ---------------------------------------------------------------------
    // 1) Menu do celular
    // ---------------------------------------------------------------------
    var cabecalho = document.getElementById("site-header");
    var botaoMenu = document.getElementById("nav-toggle");

    if (cabecalho && botaoMenu) {

        function fecharMenu() {
            cabecalho.classList.remove("aberto");
            botaoMenu.setAttribute("aria-expanded", "false");
            botaoMenu.setAttribute("aria-label", "Abrir menu");
        }

        botaoMenu.addEventListener("click", function () {
            var aberto = cabecalho.classList.toggle("aberto");

            botaoMenu.setAttribute("aria-expanded", aberto ? "true" : "false");
            botaoMenu.setAttribute("aria-label", aberto ? "Fechar menu" : "Abrir menu");
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                fecharMenu();
            }
        });

        window.addEventListener("resize", function () {
            if (window.innerWidth > 1080) {
                fecharMenu();
            }
        });
    }

    // ---------------------------------------------------------------------
    // 2) Progresso do quiz (quantas questões já foram marcadas)
    // ---------------------------------------------------------------------
    var quiz = document.getElementById("quiz");
    var barra = document.getElementById("quiz-barra");
    var texto = document.getElementById("quiz-progresso");

    if (quiz && barra && texto) {
        var cartoes = quiz.querySelectorAll("[data-questao]");

        function atualizarProgresso() {
            var respondidas = 0;

            cartoes.forEach(function (cartao) {
                if (cartao.querySelector("input[type=radio]:checked")) {
                    respondidas++;
                }
            });

            barra.style.width = (cartoes.length ? (respondidas * 100 / cartoes.length) : 0) + "%";
            texto.textContent = respondidas + " de " + cartoes.length + " respondidas";
        }

        quiz.addEventListener("change", atualizarProgresso);
        atualizarProgresso();
    }

})();
