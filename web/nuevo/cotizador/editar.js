/* Cotizador Dekarter — formulario de la cotización.

   Los totales que se ven acá son una ayuda mientras se escribe: el servidor
   los vuelve a calcular al guardar, y esos son los que valen. */

(function () {
  'use strict';

  var D = JSON.parse(document.getElementById('datos').textContent);
  var form = document.getElementById('cotizacion');
  var cuerpo = document.getElementById('items');
  var aviso = document.getElementById('aviso');
  var c = D.cotizacion;
  var sucio = false;

  // ---------------------------------------------------------------- utiles --

  function pesos(n) {
    return '$ ' + Math.round(n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  function numero(v) {                      // «200.000» o «200000» → 200000
    return parseInt(String(v || '').replace(/[^\d]/g, ''), 10) || 0;
  }
  function cantidad(v) {                    // «1,5» → 1.5
    return parseFloat(String(v || '').replace(',', '.')) || 0;
  }
  function mostrar(msg, tipo) {
    aviso.textContent = msg;
    aviso.className = 'aviso aviso--' + (tipo || 'ok');
    aviso.hidden = false;
    if (tipo === 'error') { aviso.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
  }
  function api(accion, opciones, extra) {
    opciones = opciones || {};
    opciones.headers = Object.assign({ 'X-CSRF': D.csrf }, opciones.headers || {});
    opciones.credentials = 'same-origin';
    var url = D.api + '?accion=' + accion + (c.numero ? '&n=' + encodeURIComponent(c.numero) : '') + (extra || '');
    return fetch(url, opciones).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (j) {
        if (!r.ok) { throw new Error(j.error || 'Error ' + r.status); }
        return j;
      });
    });
  }

  // ------------------------------------------------------- campos simples --

  var simples = ['referencia', 'fecha', 'validez', 'pago', 'guia', 'entrega', 'notas'];
  var deCliente = ['razon', 'rut', 'direccion', 'giro', 'atencion'];

  simples.forEach(function (k) { form.elements[k].value = c[k] || ''; });
  deCliente.forEach(function (k) { form.elements[k].value = (c.cliente || {})[k] || ''; });

  // Libreta: al elegir un cliente conocido se completan sus datos
  var lista = document.getElementById('lista-clientes');
  D.clientes.forEach(function (cl) {
    var o = document.createElement('option');
    o.value = cl.razon;
    o.label = cl.rut ? cl.rut : '';
    lista.appendChild(o);
  });
  form.elements.razon.addEventListener('change', function () {
    var elegido = D.clientes.find(function (cl) { return cl.razon === form.elements.razon.value; });
    if (!elegido) { return; }
    ['rut', 'direccion', 'giro', 'atencion'].forEach(function (k) {
      form.elements[k].value = elegido[k] || '';
    });
  });

  // ------------------------------------------------------------------ ítems --

  function filaItem(it) {
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td class="c-cant"><input class="i-cant" inputmode="decimal" aria-label="Cantidad"></td>' +
      '<td><input class="i-det" placeholder="Ej.: FABRICACIÓN DE PASADOR" aria-label="Detalle">' +
      '<textarea class="i-desc" rows="1" placeholder="Descripción (opcional)" aria-label="Descripción"></textarea></td>' +
      '<td class="c-num"><input class="i-uni" inputmode="numeric" aria-label="Valor unitario"></td>' +
      '<td class="c-num mono i-tot"></td>' +
      '<td class="c-acc"><button type="button" class="btn-icono" title="Subir" data-mover="-1">↑</button>' +
      '<button type="button" class="btn-icono" title="Bajar" data-mover="1">↓</button>' +
      '<button type="button" class="btn-icono btn-icono--peligro" title="Quitar ítem" data-quitar>×</button></td>';
    tr.querySelector('.i-cant').value = it.cantidad != null ? String(it.cantidad).replace('.', ',') : '1';
    tr.querySelector('.i-det').value = it.detalle || '';
    tr.querySelector('.i-desc').value = it.descripcion || '';
    tr.querySelector('.i-uni').value = it.unitario ? pesos(it.unitario).slice(2) : '';
    return tr;
  }

  function leerItems() {
    return Array.prototype.map.call(cuerpo.rows, function (tr) {
      return {
        cantidad: cantidad(tr.querySelector('.i-cant').value),
        detalle: tr.querySelector('.i-det').value.trim(),
        descripcion: tr.querySelector('.i-desc').value.trim(),
        unitario: numero(tr.querySelector('.i-uni').value)
      };
    });
  }

  function recalcular() {
    var neto = 0;
    Array.prototype.forEach.call(cuerpo.rows, function (tr) {
      var t = Math.round(cantidad(tr.querySelector('.i-cant').value) * numero(tr.querySelector('.i-uni').value));
      tr.querySelector('.i-tot').textContent = pesos(t);
      neto += t;
    });
    var iva = Math.round(neto * D.iva);
    document.getElementById('t-neto').textContent = pesos(neto);
    document.getElementById('t-iva').textContent = pesos(iva);
    document.getElementById('t-total').textContent = pesos(neto + iva);
  }

  (c.items && c.items.length ? c.items : [{ cantidad: 1 }]).forEach(function (it) {
    cuerpo.appendChild(filaItem(it));
  });
  recalcular();

  document.getElementById('agregar-item').addEventListener('click', function () {
    var tr = filaItem({ cantidad: 1 });
    cuerpo.appendChild(tr);
    tr.querySelector('.i-det').focus();
    recalcular();
  });

  cuerpo.addEventListener('input', function (ev) {
    if (ev.target.classList.contains('i-desc')) {         // la descripción crece con el texto
      ev.target.style.height = 'auto';
      ev.target.style.height = ev.target.scrollHeight + 'px';
    }
    recalcular();
  });
  // El valor unitario se muestra con puntos de miles al salir del campo
  cuerpo.addEventListener('blur', function (ev) {
    if (ev.target.classList.contains('i-uni') && ev.target.value !== '') {
      ev.target.value = pesos(numero(ev.target.value)).slice(2);
    }
  }, true);
  cuerpo.addEventListener('click', function (ev) {
    var tr = ev.target.closest('tr');
    if (ev.target.hasAttribute('data-quitar')) {
      if (cuerpo.rows.length > 1) { tr.remove(); } else { tr.replaceWith(filaItem({ cantidad: 1 })); }
      recalcular();
      sucio = true;
    } else if (ev.target.hasAttribute('data-mover')) {
      var dir = parseInt(ev.target.getAttribute('data-mover'), 10);
      var otro = dir < 0 ? tr.previousElementSibling : tr.nextElementSibling;
      if (otro) { dir < 0 ? otro.before(tr) : otro.after(tr); sucio = true; }
    }
  });

  // ------------------------------------------------------------- guardar --

  form.addEventListener('input', function () { sucio = true; });

  // Avisa antes de salir con cambios sin guardar
  window.addEventListener('beforeunload', function (ev) {
    if (sucio) { ev.preventDefault(); ev.returnValue = ''; }
  });

  function recoger() {
    var datos = { cliente: {}, items: leerItems() };
    simples.forEach(function (k) { datos[k] = form.elements[k].value; });
    deCliente.forEach(function (k) { datos.cliente[k] = form.elements[k].value; });
    return datos;
  }

  var guardando = false;
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (guardando) { return; }

    var datos = recoger();
    if (!datos.cliente.razon.trim()) {
      mostrar('Falta el cliente.', 'error');
      form.elements.razon.focus();
      return;
    }
    if (!datos.items.some(function (i) { return i.detalle; })) {
      mostrar('Agrega al menos un ítem con detalle.', 'error');
      return;
    }

    guardando = true;
    Array.prototype.forEach.call(form.querySelectorAll('button[type=submit]'), function (b) {
      b.disabled = true; b.textContent = 'Guardando…';
    });

    api('guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) })
      .then(function (r) {
        sucio = false;
        if (!c.numero) {
          // Recién nacida: se recarga con su número para habilitar las fotos.
          window.location.href = D.editar + '?n=' + encodeURIComponent(r.numero) + '&nueva=1';
          return;
        }
        c = r.cotizacion;
        mostrar('Guardada. Cotización N° ' + r.numero + '.', 'ok');
      })
      .catch(function (e) { mostrar(e.message, 'error'); })
      .then(function () {
        guardando = false;
        Array.prototype.forEach.call(form.querySelectorAll('button[type=submit]'), function (b) {
          b.disabled = false; b.textContent = 'Guardar';
        });
      });
  });

  if (/[?&]nueva=1/.test(location.search)) {
    mostrar('Cotización N° ' + c.numero + ' creada. Ahora puedes agregar fotos y bajar el PDF.', 'ok');
    history.replaceState(null, '', D.editar + '?n=' + encodeURIComponent(c.numero));
  }

  // ------------------------------------------------------ PDF y duplicar --

  var btnPdf = document.getElementById('ver-pdf');
  if (btnPdf) {
    btnPdf.addEventListener('click', function () {
      if (sucio) {
        mostrar('Hay cambios sin guardar. Guarda primero para que el PDF los incluya.', 'error');
        return;
      }
      window.DekarterPDF.generar(c.numero, D.api, btnPdf);
    });
  }

  var btnDup = document.getElementById('duplicar');
  if (btnDup) {
    btnDup.addEventListener('click', function () {
      if (!confirm('Se va a crear una cotización nueva, con número propio, copiando ésta y sus fotos. ¿Seguir?')) {
        return;
      }
      api('duplicar', { method: 'POST' }).then(function (r) {
        sucio = false;
        window.location.href = D.editar + '?n=' + encodeURIComponent(r.numero) + '&nueva=1';
      }).catch(function (e) { mostrar(e.message, 'error'); });
    });
  }

  // ---------------------------------------------------------------- fotos --

  var cajaFotos = document.getElementById('fotos');
  if (!cajaFotos) { return; }   // cotización sin guardar: todavía no hay fotos

  function urlFoto(f) {
    return D.api + '?accion=foto&n=' + encodeURIComponent(c.numero) + '&f=' + encodeURIComponent(f.archivo);
  }

  function pintarFotos() {
    cajaFotos.innerHTML = '';
    (c.fotos || []).forEach(function (f) {
      var fig = document.createElement('figure');
      fig.className = 'foto';
      fig.innerHTML = '<img loading="lazy" alt=""><button type="button" class="btn-icono btn-icono--peligro" title="Quitar foto">×</button>';
      fig.querySelector('img').src = urlFoto(f);
      fig.querySelector('button').addEventListener('click', function () {
        if (!confirm('¿Quitar esta foto de la cotización?')) { return; }
        api('quitar-foto', { method: 'POST' }, '&f=' + encodeURIComponent(f.archivo))
          .then(function (r) { c.fotos = r.fotos; pintarFotos(); })
          .catch(function (e) { mostrar(e.message, 'error'); });
      });
      cajaFotos.appendChild(fig);
    });
    document.getElementById('soltar').hidden = (c.fotos || []).length >= D.maxFotos;
  }
  pintarFotos();

  /* Las fotos del celular pesan 3 a 5 MB. Se achican acá, en el navegador, a
     1600 px de lado mayor: suben en un segundo, el PDF no pesa una tonelada y
     a ese tamaño el detalle del desgaste se sigue viendo bien. */
  function achicar(archivo) {
    return new Promise(function (ok, mal) {
      var url = URL.createObjectURL(archivo);
      var img = new Image();
      img.onload = function () {
        var max = 1600, w = img.naturalWidth, h = img.naturalHeight;
        var k = Math.min(1, max / Math.max(w, h));
        // Ya chica y liviana: se sube tal cual. Recomprimir una foto que ya
        // viene comprimida la hace pesar MÁS y la deja peor.
        if (k === 1 && archivo.type === 'image/jpeg' && archivo.size < 700 * 1024) {
          URL.revokeObjectURL(url);
          ok(archivo);
          return;
        }
        var cv = document.createElement('canvas');
        cv.width = Math.round(w * k); cv.height = Math.round(h * k);
        cv.getContext('2d').drawImage(img, 0, 0, cv.width, cv.height);
        URL.revokeObjectURL(url);
        cv.toBlob(function (b) { b ? ok(b) : mal(new Error('No se pudo procesar la foto')); }, 'image/jpeg', 0.8);
      };
      img.onerror = function () { URL.revokeObjectURL(url); mal(new Error(archivo.name + ' no es una imagen válida')); };
      img.src = url;
    });
  }

  var input = document.getElementById('archivo-fotos');
  var zona = document.getElementById('soltar');

  function subir(archivos) {
    archivos = Array.prototype.filter.call(archivos, function (a) { return /^image\/(jpeg|png)$/.test(a.type); });
    if (!archivos.length) { mostrar('Elige fotos JPG o PNG.', 'error'); return; }
    var libres = D.maxFotos - (c.fotos || []).length;
    if (archivos.length > libres) {
      mostrar('Sólo caben ' + libres + ' fotos más (máximo ' + D.maxFotos + ').', 'error');
      archivos = archivos.slice(0, libres);
    }

    var hechas = 0;
    mostrar('Subiendo fotos… 0 de ' + archivos.length, 'ok');
    // Una por una: en una conexión de celular, subirlas todas a la vez se corta.
    archivos.reduce(function (cadena, archivo) {
      return cadena.then(function () {
        return achicar(archivo).then(function (blob) {
          var fd = new FormData();
          fd.append('foto', blob, 'foto.jpg');
          return api('subir-foto', { method: 'POST', body: fd });
        }).then(function (r) {
          c.fotos = r.fotos;
          pintarFotos();
          hechas++;
          mostrar('Subiendo fotos… ' + hechas + ' de ' + archivos.length, 'ok');
        });
      });
    }, Promise.resolve()).then(function () {
      mostrar(hechas === 1 ? 'Foto agregada.' : hechas + ' fotos agregadas.', 'ok');
    }).catch(function (e) {
      mostrar(e.message + (hechas ? ' (alcanzaron a subir ' + hechas + ')' : ''), 'error');
    }).then(function () { input.value = ''; });
  }

  input.addEventListener('change', function () { subir(input.files); });
  ['dragenter', 'dragover'].forEach(function (t) {
    zona.addEventListener(t, function (ev) { ev.preventDefault(); zona.classList.add('soltar--activo'); });
  });
  ['dragleave', 'drop'].forEach(function (t) {
    zona.addEventListener(t, function (ev) { ev.preventDefault(); zona.classList.remove('soltar--activo'); });
  });
  zona.addEventListener('drop', function (ev) { subir(ev.dataTransfer.files); });
})();
