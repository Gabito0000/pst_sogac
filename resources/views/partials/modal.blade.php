{{--
    Comportamiento compartido de los modales.

    Se incluye una sola vez al pie de cada plantilla. Cualquier elemento con
    la clase .modal-overlay se abre con onclick="abrirModal('id')" y se cierra
    con cerrarModal('id'), con la tecla Escape o clic en el fondo.

    El estado vive en el atributo data-abierto para que la visibilidad la
    resuelva el CSS y no una cadena de style.display en línea.
--}}
<script>
    (function () {
        function alternarModal(id, abrir) {
            var modal = document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.setAttribute('data-abierto', abrir ? '1' : '0');
        }

        window.abrirModal = function (id) { alternarModal(id, true); };
        window.cerrarModal = function (id) { alternarModal(id, false); };

        // Clic en el fondo oscuro.
        document.addEventListener('click', function (evento) {
            if (evento.target.matches('.modal-overlay[data-abierto="1"]')) {
                evento.target.setAttribute('data-abierto', '0');
            }
        });

        // Tecla Escape.
        document.addEventListener('keydown', function (evento) {
            if (evento.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('.modal-overlay[data-abierto="1"]').forEach(function (modal) {
                modal.setAttribute('data-abierto', '0');
            });
        });
    })();
</script>
