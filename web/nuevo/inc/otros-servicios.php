<?php
/**
 * Banda con los demás servicios. Enlaza las páginas entre sí, que es lo que
 * Google usa para entender qué páginas del sitio importan.
 *
 * La página que lo incluye define $excluir con su propia clave de servicio.
 */

$excluir = $excluir ?? '';
?>
<section class="banda banda--papel">
  <div class="contenedor">
    <div class="cabeza-seccion">
      <p class="rotulo">También te puede servir</p>
      <h2>Otros servicios</h2>
    </div>

    <div class="grilla grilla--3">
      <?php foreach ($DK_SERVICIOS as $clave => $otro): ?>
        <?php if ($clave === $excluir) { continue; } ?>
        <a class="tarjeta" href="<?= e(u($otro['archivo'])) ?>">
          <?= foto([
              'base'   => $otro['imagen'],
              'anchos' => $otro['anchos'],
              'ancho'  => 640,
              'alto'   => 400,
              'sizes'  => '(min-width: 900px) 360px, 100vw',
              'clase'  => 'tarjeta-foto',
              'alt'    => $otro['alt'],
          ]) ?>
          <div class="tarjeta-cuerpo">
            <h3><?= e($otro['nombre']) ?></h3>
            <p><?= e($otro['resumen']) ?></p>
            <span class="tarjeta-mas">
              Ver detalle
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                   stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6"/>
              </svg>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
