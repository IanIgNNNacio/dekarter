<?php
/**
 * Crear o editar una cotización.
 *
 *   editar              nueva (el número se asigna recién al guardar)
 *   editar?n=3462-26    editar una existente
 */

declare(strict_types=1);

require __DIR__ . '/../inc/cotizador.php';
require __DIR__ . '/../inc/cotizador-plantilla.php';

cot_exigir_login();

$n = (string) ($_GET['n'] ?? '');
$c = $n !== '' ? cot_cargar($n) : null;

if ($n !== '' && $c === null) {
    http_response_code(404);
    cot_cabecera('No encontrada');
    echo '<div class="vacio"><p>No existe la cotización ' . e($n) . '.</p>'
       . '<a href="' . e(u('cotizador/')) . '">Volver al listado</a></div>';
    cot_pie();
    exit;
}

$d = $COT['por_defecto'];
$inicial = $c ?? [
    'numero' => null, 'fecha' => date('Y-m-d'),
    'cliente' => ['razon' => '', 'rut' => '', 'direccion' => '', 'giro' => '', 'atencion' => ''],
    'referencia' => '', 'validez' => $d['validez'], 'pago' => $d['pago'], 'guia' => $d['guia'],
    'entrega' => $d['entrega'], 'notas' => '',
    'items' => [['cantidad' => 1, 'detalle' => '', 'descripcion' => '', 'unitario' => 0]],
    'fotos' => [],
];

$datos_js = [
    'cotizacion' => $inicial,
    'clientes'   => cot_clientes(),
    'csrf'       => cot_csrf(),
    'api'        => u('cotizador/api'),
    'editar'     => u('cotizador/editar'),
    'iva'        => $COT['iva'],
    'maxFotos'   => $COT['max_fotos'],
];

cot_cabecera($c ? 'Cotización ' . $c['numero'] : 'Nueva cotización');
?>
  <form id="cotizacion" class="hoja" novalidate>

    <div class="encabezado">
      <div>
        <h1><?= $c ? 'Cotización N° <span class="mono">' . e($c['numero']) . '</span>' : 'Nueva cotización' ?></h1>
        <p class="suave">
          <?= $c ? 'Última modificación: ' . e(date('d-m-Y H:i', strtotime($c['modificada'])))
                 : 'El número se asigna al guardar. La siguiente libre se reserva recién entonces.' ?>
        </p>
      </div>
      <div class="acciones-cabecera">
        <?php if ($c): ?>
          <button class="btn btn--linea" type="button" id="duplicar">Duplicar</button>
          <button class="btn" type="button" id="ver-pdf">Ver PDF</button>
        <?php endif; ?>
        <button class="btn btn--principal" type="submit" id="guardar">Guardar</button>
      </div>
    </div>

    <div id="aviso" class="aviso" role="status" hidden></div>

    <!-- Cliente -->
    <section class="bloque">
      <h2>Cliente</h2>
      <div class="rejilla">
        <label class="campo campo--ancho">
          <span>Señores <b aria-hidden="true">*</b></span>
          <input name="razon" list="lista-clientes" required autocomplete="off"
                 placeholder="Escribe o elige de la libreta">
          <datalist id="lista-clientes"></datalist>
        </label>
        <label class="campo"><span>RUT</span><input name="rut" placeholder="79.538.350-6"></label>
        <label class="campo"><span>Atención Sr(a).</span><input name="atencion"></label>
        <label class="campo campo--ancho"><span>Dirección</span><input name="direccion"></label>
        <label class="campo"><span>Giro</span><input name="giro"></label>
      </div>
    </section>

    <!-- Condiciones -->
    <section class="bloque">
      <h2>Condiciones</h2>
      <div class="rejilla">
        <label class="campo campo--ancho"><span>Referencia</span>
          <input name="referencia" placeholder="Ej.: Manipulador JCB M-959"></label>
        <label class="campo"><span>Fecha</span><input type="date" name="fecha" required></label>
        <label class="campo"><span>Validez</span><input name="validez"></label>
        <label class="campo"><span>Forma de pago</span><input name="pago"></label>
        <label class="campo"><span>Guía de despacho</span><input name="guia"></label>
        <label class="campo"><span>Plazo de entrega</span><input name="entrega"></label>
      </div>
    </section>

    <!-- Ítems -->
    <section class="bloque">
      <h2>Detalle</h2>
      <div class="tabla-scroll">
        <table class="items">
          <thead>
            <tr>
              <th class="c-cant">Cant.</th>
              <th>Detalle</th>
              <th class="c-num">Valor unitario</th>
              <th class="c-num">Total</th>
              <th class="c-acc"></th>
            </tr>
          </thead>
          <tbody id="items"></tbody>
        </table>
      </div>
      <button class="btn btn--linea" type="button" id="agregar-item">+ Agregar ítem</button>

      <dl class="totales">
        <div><dt>Neto</dt><dd class="mono" id="t-neto">$ 0</dd></div>
        <div><dt>IVA <?= (int) round($COT['iva'] * 100) ?>%</dt><dd class="mono" id="t-iva">$ 0</dd></div>
        <div class="totales-final"><dt>Total</dt><dd class="mono" id="t-total">$ 0</dd></div>
      </dl>
    </section>

    <!-- Notas -->
    <section class="bloque">
      <h2>Notas <span class="suave">(opcional)</span></h2>
      <label class="campo campo--ancho">
        <span class="solo-lectores">Notas</span>
        <textarea name="notas" rows="3" placeholder="Aparece en el PDF bajo el plazo de entrega."></textarea>
      </label>
    </section>

    <!-- Fotos -->
    <section class="bloque" id="bloque-fotos">
      <h2>Anexo fotográfico</h2>
      <?php if ($c): ?>
        <p class="suave">Las fotos salen al final del PDF, dos por fila. Se achican en tu equipo antes de subir,
          así que se pueden mandar directo desde el celular.</p>
        <div id="fotos" class="fotos"></div>
        <label class="soltar" id="soltar">
          <input type="file" id="archivo-fotos" accept="image/jpeg,image/png" multiple>
          <strong>Agregar fotos</strong>
          <span>Arrástralas aquí o toca para elegir. JPG o PNG.</span>
        </label>
      <?php else: ?>
        <p class="suave">Guarda la cotización primero: las fotos se agregan cuando ya tiene número.</p>
      <?php endif; ?>
    </section>

    <div class="pie-hoja">
      <button class="btn btn--principal" type="submit">Guardar</button>
    </div>
  </form>

  <script type="application/json" id="datos"><?= json_encode($datos_js, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php
cot_pie(array_merge(cot_scripts_pdf(), [activo('cotizador/editar.js')]));
