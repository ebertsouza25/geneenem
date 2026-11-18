// Flashcards do laboratório.
//
// Antes esse script mostrava um cartão por vez, com botão de avançar/voltar.
// O Kaio pediu pra mudar: agora TODOS os cartões ficam visíveis ao mesmo
// tempo numa grade, e cada um abre/fecha a resposta sozinho, sem depender
// dos outros. Por isso a lógica ficou bem mais simples: é só alternar uma
// classe no cartão que foi clicado.

(function () {
    var cartoes = document.querySelectorAll(".flashcard");

    // Se a página não tem flashcards, não tem nada pra fazer aqui.
    if (!cartoes.length) {
        return;
    }

    cartoes.forEach(function (cartao) {
        cartao.addEventListener("click", function () {
            // toggle() devolve true/false conforme a classe ficou ou não,
            // então já aproveitamos pra atualizar o aria-expanded também
            // (ajuda quem usa leitor de tela a saber se a resposta está aberta).
            var aberto = cartao.classList.toggle("aberto");
            cartao.setAttribute("aria-expanded", aberto ? "true" : "false");
        });
    });
})();
