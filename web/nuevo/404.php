<?php
require_once __DIR__ . '/inc/config.php';

http_response_code(404);

$pagina = [
    'archivo' => '404',
    'title'   => 'Página no encontrada | Maestranza Dekarter',
    'meta'    => 'La página que buscabas no existe. Revisa los servicios de mecanizado y fabricación de repuestos de Maestranza Dekarter.',
];

define('DK_SIN_INDICE', true);

require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<section class="banda">
  <div class="contenedor">
    <div class="prosa" style="max-width:56ch">
      <p class="rotulo">Error 404</p>
      <h1>Esta página no existe</h1>
      <p>
        Puede que el enlace esté viejo o que la dirección tenga un error de
        tipeo. Estos son los caminos que sí llevan a alguna parte:
      </p>

      <ul class="lista-chaflan">
        <?php foreach ($DK_SERVICIOS as $s): ?>
          <li><a href="<?= e(u($s['archivo'])) ?>"><?= e($s['nombre']) ?></a></li>
        <?php endforeach; ?>
        <?php foreach ($DK_EQUIPOS as $q): ?>
          <li><a href="<?= e(u($q['archivo'])) ?>">Repuestos para <?= e($q['modelo']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= e(u('contacto')) ?>">Contacto y cotizaciones</a></li>
      </ul>

      <div class="cierre-acciones" style="margin-top:12px">
        <a class="btn btn--principal" href="<?= e(u()) ?>">Ir a la portada</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
