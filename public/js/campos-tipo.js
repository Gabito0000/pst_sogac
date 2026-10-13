/*
 * Filtra lo que se puede escribir en un campo segun el tipo de dato que pide.
 *
 * Marca el campo con data-solo:
 *   data-solo="letras"  -> nombres y apellidos (acentos, enye, espacios, guion, apostrofo)
 *   data-solo="digitos" -> cedula y telefono
 *
 * Se apoya en el atributo pattern y en la validacion del servidor: aqui solo se
 * evita que el usuario llegue a escribir algo que ya se sabe que va a fallar.
 */
(function () {
  'use strict';

  var FUENTES = {
    letras: '[^\\p{L}\\s\'\\-]',
    digitos: '[^\\d]'
  };

  function regla(tipo, global) {
    return new RegExp(FUENTES[tipo], 'u' + (global ? 'g' : ''));
  }

  function activar(campo) {
    var tipo = campo.getAttribute('data-solo');

    if (!FUENTES[tipo]) {
      return;
    }

    var invalido = regla(tipo, false);
    var invalidoGlobal = regla(tipo, true);

    // beforeinput frena el caracter antes de que entre, en vez de reescribir el
    // valor despues: asi el cursor no se mueve de lugar.
    campo.addEventListener('beforeinput', function (evento) {
      if (evento.data && invalido.test(evento.data)) {
        evento.preventDefault();
      }
    });

    // Red de seguridad para lo que beforeinput no cubre: pegar, dictar, arrastrar
    // texto, metodos del navegador que injectan teclado. Si algo se coló, se quita
    // y se devuelve el cursor a donde estaba.
    campo.addEventListener('input', function () {
      if (!invalido.test(campo.value)) {
        return;
      }

      var antes = campo.value;
      var cursor = campo.selectionStart;
      var limpio = antes.replace(invalidoGlobal, '');

      // Solo cuenta lo borrado que estaba antes del cursor, para no displacearlo
      // de mas cuando el resto de la posicion queda mas cerca del final.
      var hastaCursor = antes.slice(0, cursor);
      var quitados = hastaCursor.length - hastaCursor.replace(invalidoGlobal, '').length;

      campo.value = limpio;

      try {
        campo.setSelectionRange(cursor - quitados, cursor - quitados);
      } catch (e) {
        // Algunos tipos de input no permiten mover el cursor (number, email).
        // Da igual: el valor ya quedo limpio.
      }
    });
  }

  function iniciar() {
    var campos = document.querySelectorAll('[data-solo]');

    Array.prototype.forEach.call(cambos, activar);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciar);
  } else {
    iniciar();
  }
})();