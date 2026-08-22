/* Maestranza Dekarter — el único JavaScript del sitio.
   Dos cosas: el menú en móvil y evitar el doble envío del formulario. Nada más.
   La página funciona completa sin este archivo. */

(function () {
  'use strict';

  /* --- Menú en pantallas chicas ------------------------------------------ */

  var boton = document.querySelector('.menu-boton');
  var menu = document.getElementById('nav-principal');

  if (boton && menu) {
    boton.addEventListener('click', function () {
      var abierto = menu.getAttribute('data-abierto') === 'si';
      menu.setAttribute('data-abierto', abierto ? 'no' : 'si');
      boton.setAttribute('aria-expanded', abierto ? 'false' : 'true');
    });

    // Escape cierra y devuelve el foco al botón
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && menu.getAttribute('data-abierto') === 'si') {
        menu.setAttribute('data-abierto', 'no');
        boton.setAttribute('aria-expanded', 'false');
        boton.focus();
      }
    });
  }

  /* --- Formulario: un solo envío ---------------------------------------- */

  var forma = document.querySelector('form[data-formulario="contacto"]');

  if (forma) {
    forma.addEventListener('submit', function () {
      var enviar = forma.querySelector('button[type="submit"]');
      if (!enviar) { return; }

      // Se deshabilita después de que el navegador ya recogió los datos.
      window.setTimeout(function () {
        enviar.disabled = true;
        enviar.textContent = 'Enviando…';
      }, 0);
    });
  }
})();
