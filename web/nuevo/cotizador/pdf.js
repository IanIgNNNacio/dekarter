/* Cotizador Dekarter — armado del PDF.

   El PDF se genera en el navegador con pdfmake: texto real (se puede copiar y
   buscar), tablas que se cortan bien entre páginas y fotos incrustadas. No
   hace falta instalar nada en el hosting.

   El PDF no se guarda: se rearma cada vez desde los datos guardados. Así no
   puede quedar un PDF viejo distinto de la cotización actual. */

(function () {
  'use strict';

  var AZUL = '#0D3857';
  var TINTA = '#16202A';
  var GRIS = '#5B6872';
  var LINEA = '#D5DCE2';
  var PAPEL = '#F2F5F7';

  var EMPRESA = null;   // se llena con los datos que entrega la página

  function pesos(n) {
    return '$ ' + Math.round(n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function fechaLarga(iso) {
    var m = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
      'septiembre', 'octubre', 'noviembre', 'diciembre'];
    var p = iso.split('-').map(Number);
    return p[2] + ' de ' + m[p[1] - 1] + ' de ' + p[0];
  }

  function cantidadTexto(n) {
    return String(n).replace('.', ',');
  }

  function aDataUrl(url) {
    return fetch(url, { credentials: 'same-origin' }).then(function (r) {
      if (!r.ok) { throw new Error('No se pudo leer ' + url); }
      return r.blob();
    }).then(function (b) {
      return new Promise(function (ok) {
        var fr = new FileReader();
        fr.onload = function () { ok(fr.result); };
        fr.readAsDataURL(b);
      });
    });
  }

  /* El logo de la web usa un degradado metálico. El motor de PDF no lo
     reproduce igual en todos los visores, así que para el PDF se usa la
     versión de acero plano, que es la que corresponde a impresión. */
  function logoPlano(svg) {
    return svg
      .replace(/<defs>[\s\S]*?<\/defs>/, '')
      .replace(/url\(#[^)]+\)/g, '#A3A9AF');
  }

  // ---------------------------------------------------------- piezas --

  function fichaFila(rotulo, valor) {
    return [
      { text: rotulo, style: 'rotulo' },
      { text: valor || '—', style: 'valor' }
    ];
  }

  function ficha(filas) {
    return {
      table: { widths: [78, '*'], body: filas },
      layout: {
        hLineWidth: function () { return 0; },
        vLineWidth: function () { return 0; },
        paddingTop: function () { return 2; },
        paddingBottom: function () { return 2; },
        paddingLeft: function () { return 0; }
      }
    };
  }

  function tablaItems(items) {
    var cuerpo = [[
      { text: 'Cant.', style: 'th', alignment: 'center' },
      { text: 'Detalle', style: 'th' },
      { text: 'Valor unitario', style: 'th', alignment: 'right' },
      { text: 'Valor total', style: 'th', alignment: 'right' }
    ]];
    items.forEach(function (it) {
      var det = [{ text: it.detalle, bold: true, color: TINTA }];
      if (it.descripcion) {
        det.push({ text: it.descripcion, fontSize: 8.5, color: GRIS, margin: [0, 2, 0, 0] });
      }
      cuerpo.push([
        { text: cantidadTexto(it.cantidad), alignment: 'center' },
        { stack: det },
        { text: pesos(it.unitario), alignment: 'right' },
        { text: pesos(it.total), alignment: 'right', bold: true }
      ]);
    });
    return {
      table: { headerRows: 1, widths: [38, '*', 84, 84], body: cuerpo, dontBreakRows: true },
      layout: {
        fillColor: function (i) { return i === 0 ? AZUL : (i % 2 === 0 ? PAPEL : null); },
        hLineWidth: function (i, nodo) { return (i === 0 || i === nodo.table.body.length) ? 0 : 0.5; },
        hLineColor: function () { return LINEA; },
        vLineWidth: function () { return 0; },
        paddingTop: function (i) { return i === 0 ? 6 : 7; },
        paddingBottom: function (i) { return i === 0 ? 6 : 7; },
        paddingLeft: function () { return 7; },
        paddingRight: function () { return 7; }
      }
    };
  }

  function totales(c) {
    var iva = Math.round((c.iva / (c.neto || 1)) * 100);
    return {
      columns: [
        { width: '*', text: '' },
        {
          width: 210,
          table: {
            widths: ['*', 100],
            body: [
              [{ text: 'Neto', style: 'rotulo' }, { text: pesos(c.neto), alignment: 'right' }],
              [{ text: 'IVA ' + (c.neto ? iva : 19) + '%', style: 'rotulo' }, { text: pesos(c.iva), alignment: 'right' }],
              [{ text: 'TOTAL', bold: true, color: '#FFFFFF', fontSize: 11 },
               { text: pesos(c.total), alignment: 'right', bold: true, color: '#FFFFFF', fontSize: 11 }]
            ]
          },
          layout: {
            fillColor: function (i) { return i === 2 ? AZUL : null; },
            hLineWidth: function (i) { return i === 1 || i === 2 ? 0.5 : 0; },
            hLineColor: function () { return LINEA; },
            vLineWidth: function () { return 0; },
            paddingTop: function (i) { return i === 2 ? 7 : 4; },
            paddingBottom: function (i) { return i === 2 ? 7 : 4; },
            paddingLeft: function () { return 8; },
            paddingRight: function () { return 8; }
          }
        }
      ],
      margin: [0, 10, 0, 0]
    };
  }

  function anexo(c, fotos) {
    if (!fotos.length) { return []; }
    var filas = [];
    for (var i = 0; i < fotos.length; i += 2) {
      filas.push({
        columns: [0, 1].map(function (k) {
          var f = fotos[i + k];
          return f
            ? { image: f, fit: [255, 205], alignment: 'center', width: '*' }
            : { text: '', width: '*' };
        }),
        columnGap: 12,
        margin: [0, 0, 0, 12],
        unbreakable: true
      });
    }
    return [
      { text: 'Anexo fotográfico', style: 'titulo', pageBreak: 'before' },
      { text: 'Cotización N° ' + c.numero + (c.referencia ? ' · ' + c.referencia : ''),
        color: GRIS, fontSize: 9, margin: [0, 2, 0, 14] }
    ].concat(filas);
  }

  // ---------------------------------------------------------- documento --

  function documento(c, logo, fotos) {
    var E = EMPRESA.empresa, F = EMPRESA.firma;
    var cl = c.cliente;

    var contenido = [
      // Encabezado: logo y número
      {
        columns: [
          { svg: logo, width: 200 },
          {
            width: '*',
            stack: [
              { text: 'COTIZACIÓN', fontSize: 9, color: GRIS, characterSpacing: 1.5, alignment: 'right' },
              { text: 'N° ' + c.numero, fontSize: 20, bold: true, color: AZUL, alignment: 'right' },
              { text: E.ciudad + ', ' + fechaLarga(c.fecha), fontSize: 9, color: GRIS, alignment: 'right', margin: [0, 3, 0, 0] }
            ]
          }
        ]
      },
      // Datos de la empresa
      {
        text: [
          { text: E.razon_social, bold: true, color: TINTA },
          ' · ' + E.bajada + ' · RUT ' + E.rut + '\n',
          E.direccion + ' · Fono ' + E.fono + ' · Cel. ' + E.celulares + ' · ' + E.email
        ],
        fontSize: 8.5, color: GRIS, margin: [0, 12, 0, 0]
      },
      { canvas: [{ type: 'line', x1: 0, y1: 0, x2: 532, y2: 0, lineWidth: 1.2, lineColor: AZUL }], margin: [0, 10, 0, 14] },

      // Cliente y condiciones
      {
        columns: [
          { width: '*', stack: [
            { text: 'CLIENTE', style: 'seccion' },
            ficha([
              fichaFila('Señores', cl.razon),
              fichaFila('RUT', cl.rut),
              fichaFila('Dirección', cl.direccion),
              fichaFila('Giro', cl.giro),
              fichaFila('Atención', cl.atencion)
            ])
          ] },
          { width: '*', stack: [
            { text: 'CONDICIONES', style: 'seccion' },
            ficha([
              fichaFila('Referencia', c.referencia),
              fichaFila('Validez', c.validez),
              fichaFila('Forma de pago', c.pago),
              fichaFila('Guía despacho', c.guia),
              fichaFila('Entrega', c.entrega)
            ])
          ] }
        ],
        columnGap: 24,
        margin: [0, 0, 0, 16]
      },

      tablaItems(c.items),
      totales(c)
    ];

    if (c.notas) {
      contenido.push({ text: 'NOTAS', style: 'seccion', margin: [0, 16, 0, 4] });
      contenido.push({ text: c.notas, fontSize: 9.5, color: TINTA });
    }

    // Firma
    contenido.push({
      stack: [
        { canvas: [{ type: 'line', x1: 0, y1: 0, x2: 180, y2: 0, lineWidth: 0.6, lineColor: GRIS }] },
        { text: F.nombre, bold: true, color: TINTA, margin: [0, 5, 0, 1] },
        { text: F.email, fontSize: 9, color: GRIS },
        { text: F.fono, fontSize: 9, color: GRIS }
      ],
      margin: [0, 34, 0, 0],
      unbreakable: true
    });

    contenido = contenido.concat(anexo(c, fotos));

    return {
      pageSize: 'LETTER',
      pageMargins: [40, 36, 40, 46],
      info: {
        title: 'Cotización ' + c.numero + ' — ' + cl.razon,
        author: E.razon_social,
        subject: c.referencia || 'Cotización'
      },
      content: contenido,
      footer: function (actual, total) {
        return {
          columns: [
            { text: E.razon_social + ' · ' + E.web, color: GRIS },
            { text: 'Cotización ' + c.numero + ' · Página ' + actual + ' de ' + total, alignment: 'right', color: GRIS }
          ],
          fontSize: 7.5,
          margin: [40, 16, 40, 0]
        };
      },
      defaultStyle: { font: 'Roboto', fontSize: 10, color: TINTA, lineHeight: 1.15 },
      styles: {
        th: { bold: true, color: '#FFFFFF', fontSize: 9 },
        rotulo: { color: GRIS, fontSize: 9 },
        valor: { color: TINTA, fontSize: 9.5, bold: true },
        seccion: { fontSize: 8, color: AZUL, bold: true, characterSpacing: 1.2, margin: [0, 0, 0, 4] },
        titulo: { fontSize: 15, bold: true, color: AZUL }
      }
    };
  }

  function nombreArchivo(c) {
    var partes = ['DK-' + c.numero, c.cliente.razon, c.referencia].filter(Boolean).join(' ');
    return partes.replace(/[\\/:*?"<>|]+/g, '').replace(/\s+/g, ' ').trim().slice(0, 120) + '.pdf';
  }

  // ---------------------------------------------------------- público --

  window.DekarterPDF = {
    generar: function (numero, api, boton) {
      if (!window.pdfMake) {
        alert('La librería de PDF todavía está cargando. Prueba en un segundo.');
        return;
      }
      var textoBoton = boton ? boton.textContent : '';
      if (boton) { boton.disabled = true; boton.textContent = 'Armando PDF…'; }

      var base = api + '?accion=';
      var c;

      Promise.all([
        fetch(base + 'cotizacion&n=' + encodeURIComponent(numero), { credentials: 'same-origin' })
          .then(function (r) { return r.json(); }),
        fetch(base + 'empresa', { credentials: 'same-origin' }).then(function (r) { return r.json(); })
      ]).then(function (res) {
        c = res[0];
        if (c.error) { throw new Error(c.error); }
        EMPRESA = res[1];
        return fetch(EMPRESA.logo).then(function (r) { return r.text(); });
      }).then(function (svg) {
        var logo = logoPlano(svg);
        return Promise.all((c.fotos || []).map(function (f) {
          return aDataUrl(base + 'foto&n=' + encodeURIComponent(numero) + '&f=' + encodeURIComponent(f.archivo));
        })).then(function (fotos) { return documento(c, logo, fotos); });
      }).then(function (doc) {
        pdfMake.createPdf(doc).download(nombreArchivo(c));
      }).catch(function (e) {
        alert('No se pudo generar el PDF: ' + e.message);
      }).then(function () {
        if (boton) { boton.disabled = false; boton.textContent = textoBoton; }
      });
    }
  };

  // Botones «PDF» del listado
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-pdf]');
    if (!b) { return; }
    var tabla = b.closest('[data-api]');
    window.DekarterPDF.generar(b.getAttribute('data-pdf'), tabla.getAttribute('data-api'), b);
  });
})();
