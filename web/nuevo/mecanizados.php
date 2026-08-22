<?php
require_once __DIR__ . '/inc/config.php';

$s = $DK_SERVICIOS['mecanizados'];

$pagina = [
    'archivo' => $s['archivo'],
    'title'   => $s['title'],
    'meta'    => $s['meta'],
    'og_img'  => $s['imagen'] . '-900.jpg',
    'migas'   => [[$s['nombre'], $s['archivo']]],
    'schema'  => [
        '@type'       => 'Service',
        'name'        => $s['nombre'],
        'description' => $s['meta'],
        'serviceType' => 'Mecanizado de piezas industriales',
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
          Mecanizamos piezas y componentes para maquinaria de minería
          subterránea. El trabajo típico llega de tres maneras: con el plano,
          con la pieza original para copiar, o con la pieza gastada y la
          instrucción de dejarla como nueva.
        </p>

        <h2>Lo que más nos piden</h2>
        <ul class="lista-chaflan">
          <li><strong>Pasadores.</strong> Fabricación nueva y recuperación de medida de pasadores de articulación y de cilindro.</li>
          <li><strong>Bujes.</strong> En bronce y en acero, para los alojamientos donde trabajan esos pasadores.</li>
          <li><strong>Componentes de brazos de perforación.</strong> Reparación y fabricación de las piezas de desgaste del brazo.</li>
          <li><strong>Piezas sueltas de maquinaria.</strong> El componente puntual que dejó una máquina detenida.</li>
        </ul>

        <h2>Qué necesitamos para cotizar</h2>
        <p>
          Cualquiera de estas cuatro cosas alcanza para partir: el plano, una foto
          con las medidas principales, el número de parte del fabricante, o la
          pieza física. Si tienes la pieza original, mejor: se copia sin margen de
          interpretación.
        </p>
        <p>
          Lo que más demora una cotización no es el mecanizado, es la ida y vuelta
          preguntando medidas. Mientras más completo llegue, más rápido sale.
        </p>

        <a class="btn btn--principal" href="<?= e(u('contacto')) ?>">Pedir una cotización</a>
      </div>

      <div>
        <?= foto([
            'base'   => $s['imagen'],
            'anchos' => $s['anchos'],
            'ancho'  => 900,
            'alto'   => 713,
            'sizes'  => '(min-width: 900px) 540px, 100vw',
            'alt'    => $s['alt'],
        ]) ?>

        <dl class="ficha" style="margin-top:26px">
          <div>
            <dt>Materiales habituales</dt>
            <dd>Acero y bronce</dd>
          </div>
          <div>
            <dt>Se trabaja sobre</dt>
            <dd>Plano, pieza de muestra o número de parte</dd>
          </div>
          <div>
            <dt>Taller</dt>
            <dd><?= e($DK['calle']) ?>, <?= e($DK['comuna']) ?></dd>
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
$excluir = 'mecanizados';
require __DIR__ . '/inc/otros-servicios.php';
require __DIR__ . '/inc/footer.php';
?>
