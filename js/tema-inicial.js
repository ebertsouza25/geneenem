// Tema aplicado antes da página aparecer.
//
// Este arquivo é carregado no <head> das páginas, antes do CSS pintar a tela.
// Assim o aluno que escolheu o modo escuro não vê a página "piscar" clara
// primeiro. A lógica é a mesma que existia escrita dentro do login.php e do
// topo.php; agora ela fica em um único arquivo externo.

(function () {
    // Marca que o navegador está com JavaScript ligado.
    document.documentElement.classList.add("js");

    try {
        if (localStorage.getItem("geneenem.tema") === "escuro") {
            document.documentElement.classList.add("escuro");
        }
    } catch (erro) {
        // navegador sem localStorage: segue no tema claro
    }
})();
