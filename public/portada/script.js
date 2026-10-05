var elementoHtml = document.documentElement;
var botonTema = document.getElementById("btnTema");
var iconoTema = document.getElementById("iconoTema");
var textoTema = document.getElementById("textoTema");

function aplicarTema(tema) {
    elementoHtml.setAttribute("data-bs-theme", tema);
    if (tema === "dark") {
        iconoTema.className = "bi bi-sun-fill";
        textoTema.textContent = "Claro";
    } else {
        iconoTema.className = "bi bi-moon-stars-fill";
        textoTema.textContent = "Oscuro";
    }
    localStorage.setItem("tema-uptp", tema);
}

var temaGuardado = localStorage.getItem("tema-uptp") || "light";
aplicarTema(temaGuardado);

botonTema.addEventListener("click", function () {
    var temaActual = elementoHtml.getAttribute("data-bs-theme");
    var nuevoTema = (temaActual === "dark") ? "light" : "dark";
    aplicarTema(nuevoTema);
});

var tarjetasInteractivas = document.querySelectorAll(".card-interactiva");
for (var i = 0; i < tarjetasInteractivas.length; i++) {
    tarjetasInteractivas[i].addEventListener("touchstart", function() {
        this.classList.toggle("touched");
        for (var j = 0; j < tarjetasInteractivas.length; j++) {
            if (tarjetasInteractivas[j] !== this) {
                tarjetasInteractivas[j].classList.remove("touched");
            }
        }
    }, { passive: true });
}
