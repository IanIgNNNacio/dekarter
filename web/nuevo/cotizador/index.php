<?php
/**
 * Entrada del cotizador: formulario de ingreso o, con sesión, el historial.
 */

declare(strict_types=1);

require __DIR__ . '/../inc/cotizador.php';
require __DIR__ . '/../inc/cotizador-plantilla.php';

// ---- Ingreso ---------------------------------------------------------------

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $error = cot_ingresar((string) ($_POST['clave'] ?? ''));
    if ($error === null) {
        header('Location: ' . u('cotizador/'), true, 303);
        exit;
    }
}

if (!cot_logueado()) {
    cot_cabecera('Ingresar', false);
    ?>
  <section class="ingreso">
    <h1>Cotizador</h1>
    <p class="suave">Herramienta interna de Maestranza Dekarter.</p>
    <?php if ($error !== null): ?>
      <div class="aviso aviso--error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(u('cotizador/')) ?>" class="pila">
      <label class="campo">
        <span>Contraseña</span>
        <input type="password" name="clave" required autofocus autocomplete="current-password">
      </label>
      <button class="btn btn--principal" type="submit">Ingresar</button>
    </form>
  </section>
    <?php
    cot_pie();
    exit;
}

// ---- Historial -------------------------------------------------------------

$buscar = cot_txt($_GET['q'] ?? '', 80);
$lista  = cot_listar($buscar);

cot_cabecera('Cotizaciones');
?>
  <div class="encabezado">
    <div>
      <h1>Cotizaciones</h1>
      <p class="suave"><?= count($lista) ?> <?= $buscar !== '' ? 'encontradas' : 'en total' ?></p>
    </div>
    <form class="buscador" method="get" action="<?= e(u('cotizador/')) ?>">
      <input type="search" name="q" value="<?= e($buscar) ?>" placeholder="Buscar por número, cliente, RUT o referencia"
             aria-label="Buscar cotizaciones">
      <button class="btn" type="submit">Buscar</button>
    </form>
  </div>

  <?php if ($lista === []): ?>
    <div class="vacio">
      <?php if ($buscar !== ''): ?>
        <p>Ninguna cotización coincide con «<?= e($buscar) ?>».</p>
        <a href="<?= e(u('cotizador/')) ?>">Ver todas</a>
      <?php else: ?>
        <p>Todavía no hay cotizaciones. La primera va a ser la N° <?= (int) $COT['numero_inicial'] ?>-<?= date('y') ?>.</p>
        <a class="btn btn--principal" href="<?= e(u('cotizador/editar')) ?>">Crear la primera</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="tabla-scroll">
      <table class="tabla" data-csrf="<?= e(cot_csrf()) ?>" data-api="<?= e(u('cotizador/api')) ?>">
        <thead>
          <tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Referencia</th><th class="num">Total</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($lista as $c): ?>
          <tr>
            <td class="mono"><a href="<?= e(u('cotizador/editar?n=' . $c['numero'])) ?>"><?= e($c['numero']) ?></a></td>
            <td class="mono"><?= e(date('d-m-Y', strtotime($c['fecha']))) ?></td>
            <td><?= e($c['cliente']['razon']) ?></td>
            <td class="suave"><?= e($c['referencia']) ?></td>
            <td class="num mono"><?= e(cot_pesos((int) $c['total'])) ?></td>
            <td class="acciones">
              <button class="btn btn--chico" type="button" data-pdf="<?= e($c['numero']) ?>">PDF</button>
              <a class="btn btn--chico btn--linea" href="<?= e(u('cotizador/editar?n=' . $c['numero'])) ?>">Abrir</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php
cot_pie(cot_scripts_pdf());
