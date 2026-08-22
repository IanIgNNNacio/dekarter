/* Maestranza Dekarter — el único JavaScript del sitio.
   Tres cosas: menú en móvil, mapa que se carga a pedido y envío del formulario
   sin doble clic. Nada más. La página funciona completa sin este archivo. */

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

  /* --- Mapa a pedido -----------------------------------------------------
     El embed de Google Maps arrastra más de medio megabyte y cookies de
     terceros en cada visita. Se carga sólo si el visitante lo pide. */

  var avisoMapa = document.querySelector('.mapa-aviso');

  if (avisoMapa) {
    avisoMapa.addEventListener('click', function () {
      var contenedor = avisoMapa.parentNode;
      var marco = document.createElement('iframe');

      marco.src = avisoMapa.getAttribute('data-src');
      marco.title = 'Ubicación de Maestranza Dekarter en Dos Sur 5010, Conchalí';
      marco.loading = 'lazy';
      marco.referrerPolicy = 'no-referrer-when-downgrade';
      marco.setAttribute('allowfullscreen', '');

      contenedor.appendChild(marco);
      avisoMapa.remove();
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
