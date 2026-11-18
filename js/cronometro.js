// Cronômetro de estudo do quiz.
// Conta o tempo no navegador e serve só como apoio: nenhuma resposta depende
// dele. Ao chegar em zero, o relógio para.

(function () {
    var display = document.getElementById("cronometro");
    var seletor = document.getElementById("minutos");
    var iniciar = document.getElementById("btn-iniciar");
    var pausar = document.getElementById("btn-pausar");
    var reiniciar = document.getElementById("btn-reiniciar");

    if (!display || !seletor || !iniciar) {
        return;
    }

    var restantes = 0;
    var relogio = null;

    function mostrar() {
        var minutos = Math.floor(restantes / 60);
        var segundos = restantes % 60;

        display.textContent =
            (minutos < 10 ? "0" : "") + minutos + ":" +
            (segundos < 10 ? "0" : "") + segundos;
    }

    function carregarTempo() {
        restantes = parseInt(seletor.value, 10) * 60;
        mostrar();
    }

    function iniciarTempo() {
        if (relogio !== null) {
            return;
        }

        if (restantes <= 0) {
            carregarTempo();
        }

        relogio = setInterval(function () {
            if (restantes > 0) {
                restantes--;
                mostrar();
            } else {
                clearInterval(relogio);
                relogio = null;
            }
        }, 1000);
    }

    function pausarTempo() {
        if (relogio !== null) {
            clearInterval(relogio);
            relogio = null;
        }
    }

    seletor.addEventListener("change", function () {
        pausarTempo();
        carregarTempo();
    });

    iniciar.addEventListener("click", iniciarTempo);
    pausar.addEventListener("click", pausarTempo);

    reiniciar.addEventListener("click", function () {
        pausarTempo();
        carregarTempo();
    });

    carregarTempo();
})();
