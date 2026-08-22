<?php
require_once __DIR__ . '/inc/config.php';

$s = $DK_SERVICIOS['repuestos-a-medida'];

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
        'serviceType' => 'Fabricación de repuestos industriales a medida',
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
          Un equipo detenido esperando un repuesto importado cuesta mucho más que
          el repuesto. Cuando la pieza no llega, está descontinuada o el plazo del
          proveedor no calza con la operación, se fabrica.
        </p>

        <h2>Los casos que llegan siempre</h2>
        <ul class="lista-chaflan">
          <li><strong>El repuesto no llega.</strong> Está en el catálogo pero el plazo de importación no sirve.</li>
          <li><strong>Está descontinuado.</strong> El equipo sigue trabajando, la pieza ya no se vende.</li>
          <li><strong>Sale más barato fabricarlo.</strong> Sobre todo en pasadores y bujes, donde el valor está en el mecanizado.</li>
          <li><strong>Se necesita adaptado.</strong> La pieza de catálogo no calza con la modificación que tiene el equipo.</li>
        </ul>

        <h2>Cómo se copia una pieza</h2>
        <p>
          Con la pieza original en la mano, se mide y se fabrica igual. Con la
          pieza gastada, se mide lo que queda y se reconstruye la medida de
          origen: para eso importa saber en qué alojamiento va a trabajar, porque
          el ajuste entre pasador y buje es lo que define si la reparación dura.
        </p>
        <p>
          Si no hay pieza ni plano, sirve el número de parte del fabricante o una
          foto con las medidas principales tomadas con pie de metro.
        </p>

        <a class="btn btn--principal" href="<?= e(u('contacto')) ?>">Cotizar un repuesto</a>
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
            <dt>Piezas habituales</dt>
            <dd>Pasadores, bujes y componentes de brazo</dd>
          </div>
          <div>
            <dt>Materiales</dt>
            <dd>Acero y bronce</dd>
          </div>
          <div>
            <dt>Basta con</dt>
            <dd>La pieza, el plano o el número de parte</dd>
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

<section class="banda banda--oscura">
  <div class="contenedor">
    <div class="cabeza-seccion">
      <p class="rotulo">Equipos</p>
      <h2>Los dos equipos que más trabajamos</h2>
    </div>
    <div class="grilla grilla--2">
      <?php foreach ($DK_EQUIPOS as $q): ?>
        <a class="equipo" href="<?= e(u($q['archivo'])) ?>">
          <?= foto([
              'base'   => $q['imagen'],
              'anchos' => $q['anchos'],
              'ancho'  => 640,
              'alto'   => 360,
              'sizes'  => '(min-width: 900px) 540px, 100vw',
              'clase'  => 'equipo-foto',
              'alt'    => $q['alt'],
          ]) ?>
          <div class="equipo-cuerpo">
            <span class="equipo-tipo"><?= e($q['tipo']) ?></span>
            <span class="equipo-modelo"><?= e($q['modelo']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
$excluir = 'repuestos-a-medida';
require __DIR__ . '/inc/otros-servicios.php';
require __DIR__ . '/inc/footer.php';
?>
