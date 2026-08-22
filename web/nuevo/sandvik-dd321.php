<?php
require_once __DIR__ . '/inc/config.php';

$q = $DK_EQUIPOS['sandvik-dd321'];

$pagina = [
    'archivo' => $q['archivo'],
    'title'   => $q['title'],
    'meta'    => $q['meta'],
    'og_img'  => $q['imagen'] . '-1200.jpg',
    'migas'   => [[$q['modelo'], $q['archivo']]],
    'schema'  => [
        '@type'       => 'Service',
        'name'        => 'Repuestos y mecanizados para ' . $q['modelo'],
        'description' => $q['meta'],
        'serviceType' => 'Reparación y fabricación de componentes para brazos de perforación',
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
      <p class="rotulo">Equipo · <?= e($q['tipo']) ?></p>
      <h1><?= e($q['h1']) ?></h1>
      <p class="entrada">
        El DD321 es un jumbo de perforación para desarrollo en minería
        subterránea. El brazo y su viga de avance son la zona que más
        mecanizado nos piden.
      </p>
    </div>

    <div class="grilla grilla--2" style="gap:44px;align-items:start">
      <div class="prosa">
        <h2>Qué fabricamos para este equipo</h2>
        <ul class="lista-chaflan">
          <li><strong>Componentes del brazo de perforación.</strong> Reparación y fabricación de las piezas de desgaste.</li>
          <li><strong>Pasadores y bujes de articulación.</strong> Los puntos de pivote del brazo.</li>
          <li><strong>Piezas de la viga de avance.</strong> Mecanizado de componentes de la zona de deslizamiento.</li>
          <li><strong>Bujes y pasadores de cilindros.</strong> Posicionamiento y giro.</li>
        </ul>

        <h2>El brazo es donde se juega la precisión</h2>
        <p>
          En un jumbo, el juego acumulado en las articulaciones del brazo se
          traduce directo en desviación del tiro. Un pasador con holgura no es
          sólo un repuesto pendiente: es perforación fuera de malla.
        </p>
        <p>
          Por eso el trabajo en estas piezas no es sólo reponer la medida
          nominal, es respetar el ajuste con el alojamiento donde va a trabajar.
          Si nos llega la pieza gastada, se mide y se reconstruye pensando en ese
          ajuste.
        </p>

        <div class="cierre-acciones" style="margin-top:8px">
          <a class="btn btn--principal" href="<?= e(u('contacto')) ?>">Cotizar para DD321</a>
          <a class="btn btn--wsp" href="<?= e($DK['whatsapp']) ?>" target="_blank" rel="noopener">WhatsApp</a>
        </div>
      </div>

      <div>
        <?= foto([
            'base'   => $q['imagen'],
            'anchos' => $q['anchos'],
            'ancho'  => 1200,
            'alto'   => 540,
            'sizes'  => '(min-width: 900px) 540px, 100vw',
            'alt'    => $q['alt'],
        ]) ?>

        <dl class="ficha" style="margin-top:26px">
          <div>
            <dt>Tipo de equipo</dt>
            <dd>Jumbo de perforación para desarrollo</dd>
          </div>
          <div>
            <dt>Zona más trabajada</dt>
            <dd>Brazo y viga de avance</dd>
          </div>
          <div>
            <dt>Materiales</dt>
            <dd>Acero y bronce</dd>
          </div>
          <div>
            <dt>Taller</dt>
            <dd><?= e($DK['calle']) ?>, <?= e($DK['comuna']) ?></dd>
          </div>
        </dl>

        <p class="legal" style="margin-top:22px">
          Sandvik es una marca registrada de Sandvik AB.
          <?= e($DK['razon_social']) ?> no está afiliada a Sandvik AB ni la
          representa: fabrica repuestos alternativos y presta servicios de
          mecanizado para equipos de esa marca. La referencia al modelo se usa
          únicamente para identificar la aplicación de la pieza.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="banda banda--oscura">
  <div class="contenedor">
    <div class="cierre">
      <p class="rotulo">Otro equipo</p>
      <h2>También trabajamos cargadores LHD</h2>
      <p class="entrada">
        Pasadores, bujes y componentes mecanizados para
        <?= e($DK_EQUIPOS['sandvik-lh410']['modelo']) ?>.
      </p>
      <div class="cierre-acciones">
        <a class="btn btn--claro" href="<?= e(u('sandvik-lh410')) ?>">Ver Sandvik LH410</a>
      </div>
    </div>
  </div>
</section>

<?php
$excluir = '';
require __DIR__ . '/inc/otros-servicios.php';
require __DIR__ . '/inc/footer.php';
?>
