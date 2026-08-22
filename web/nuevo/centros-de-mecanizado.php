<?php
require_once __DIR__ . '/inc/config.php';

$s = $DK_SERVICIOS['centros-de-mecanizado'];

$pagina = [
    'archivo' => $s['archivo'],
    'title'   => $s['title'],
    'meta'    => $s['meta'],
    'og_img'  => $s['imagen'] . '-1000.jpg',
    'migas'   => [[$s['nombre'], $s['archivo']]],
    'schema'  => [
        '@type'       => 'Service',
        'name'        => $s['nombre'],
        'description' => $s['meta'],
        'serviceType' => 'Mecanizado en centro CNC',
        'provider'    => ['@id' => DK_DOMINIO . '/#negocio'],
        'areaServed'  => ['@type' => 'Country', 'name' => 'Chile'],
    ],
];

require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<section class="banda">
  <div class="contenedor">
    <div class="cabeza-seccion">
      <p class="rotulo">Servicio</p>
      <h1><?= e($s['h1']) ?></h1>
      <p class="entrada"><?= e($s['resumen']) ?></p>
    </div>

    <div class="grilla grilla--2" style="gap:44px;align-items:start">
      <div class="prosa">
        <p>
          Hay piezas que en torno y fresa manual se pueden hacer, pero salen caras
          y desparejas: geometrías con varias caras, agujeros que tienen que caer
          exactos entre sí, o una serie de veinte unidades donde la número veinte
          tiene que medir lo mismo que la número uno.
        </p>
        <p>
          Para esas trabajamos en centro de mecanizado. La pieza se monta una vez
          y sale terminada, y la repetibilidad la garantiza el programa, no el
          pulso del operador.
        </p>

        <h2>Cuándo conviene</h2>
        <ul class="lista-chaflan">
          <li><strong>Series repetidas.</strong> Cuando necesitas varias unidades idénticas y no una sola pieza.</li>
          <li><strong>Varias caras mecanizadas.</strong> Menos montajes significa menos error acumulado.</li>
          <li><strong>Agujeros que deben coincidir.</strong> Patrones de perforación que tienen que calzar con otra pieza.</li>
          <li><strong>Reposición futura.</strong> El programa queda guardado: la próxima vez que pidas la misma pieza, sale igual.</li>
        </ul>

        <h2>Lo que nos tienes que mandar</h2>
        <p>
          Para trabajo en centro sirve mucho el plano con cotas y tolerancias. Si
          sólo tienes la pieza física, también se puede: se mide acá y se levanta
          el plano antes de programar.
        </p>

        <a class="btn btn--principal" href="<?= e(u('contacto')) ?>">Consultar por una serie</a>
      </div>

      <div>
        <?= foto([
            'base'   => $s['imagen'],
            'anchos' => $s['anchos'],
            'ancho'  => 1000,
            'alto'   => 750,
            'sizes'  => '(min-width: 900px) 540px, 100vw',
            'alt'    => $s['alt'],
        ]) ?>

        <dl class="ficha" style="margin-top:26px">
          <div>
            <dt>Ventaja principal</dt>
            <dd>Repetibilidad entre unidades</dd>
          </div>
          <div>
            <dt>Ideal para</dt>
            <dd>Series y piezas de varias caras</dd>
          </div>
          <div>
            <dt>Se trabaja sobre</dt>
            <dd>Plano con cotas, o pieza para medir</dd>
          </div>
          <div>
            <dt>Consultas</dt>
            <dd class="dato-mono"><?= e($DK['fono_visible']) ?></dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</section>

<?php
$excluir = 'centros-de-mecanizado';
require __DIR__ . '/inc/otros-servicios.php';
require __DIR__ . '/inc/footer.php';
?>
