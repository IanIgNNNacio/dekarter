<?php
require_once __DIR__ . '/inc/config.php';

$q = $DK_EQUIPOS['sandvik-lh410'];

$pagina = [
    'archivo' => $q['archivo'],
    'title'   => $q['title'],
    'meta'    => $q['meta'],
    'og_img'  => $q['imagen'] . '-1000.jpg',
    'migas'   => [[$q['modelo'], $q['archivo']]],
    'schema'  => [
        '@type'       => 'Service',
        'name'        => 'Repuestos y mecanizados para ' . $q['modelo'],
        'description' => $q['meta'],
        'serviceType' => 'Fabricación de repuestos para cargadores LHD',
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
        El LH410 es un cargador LHD de bajo perfil para minería subterránea.
        Fabricamos y mecanizamos las piezas de desgaste que lo tienen detenido.
      </p>
    </div>

    <div class="grilla grilla--2" style="gap:44px;align-items:start">
      <div class="prosa">
        <h2>Qué fabricamos para este equipo</h2>
        <ul class="lista-chaflan">
          <li><strong>Pasadores de articulación.</strong> Fabricación nueva y recuperación de medida.</li>
          <li><strong>Bujes.</strong> En bronce y en acero, para los alojamientos donde trabajan esos pasadores.</li>
          <li><strong>Bujes y pasadores de cilindros.</strong> Los puntos de pivote de levante y volteo.</li>
          <li><strong>Piezas mecanizadas sueltas.</strong> El componente puntual que no llega a tiempo.</li>
        </ul>

        <h2>Por qué conviene fabricarlo</h2>
        <p>
          En un cargador que trabaja turno completo, los pasadores y bujes son
          consumo, no falla. Importar cada juego significa plazo y stock
          inmovilizado; mecanizarlo acá significa tenerlo cuando el equipo entra a
          mantención, no tres semanas después.
        </p>
        <p>
          Trabajamos sobre la pieza original, sobre plano o sobre el número de
          parte. Si tienes la pieza gastada, se mide y se reconstruye la medida de
          origen respetando el ajuste con su alojamiento: eso es lo que determina
          cuánto dura la reparación.
        </p>

        <div class="cierre-acciones" style="margin-top:8px">
          <a class="btn btn--principal" href="<?= e(u('contacto')) ?>">Cotizar para LH410</a>
          <a class="btn btn--wsp" href="<?= e($DK['whatsapp']) ?>" target="_blank" rel="noopener">WhatsApp</a>
        </div>
      </div>

      <div>
        <?= foto([
            'base'   => $q['imagen'],
            'anchos' => $q['anchos'],
            'ancho'  => 1000,
            'alto'   => 750,
            'sizes'  => '(min-width: 900px) 540px, 100vw',
            'alt'    => $q['alt'],
        ]) ?>

        <dl class="ficha" style="margin-top:26px">
          <div>
            <dt>Tipo de equipo</dt>
            <dd>Cargador LHD de bajo perfil</dd>
          </div>
          <div>
            <dt>Piezas más pedidas</dt>
            <dd>Pasadores y bujes de articulación</dd>
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
      <h2>También trabajamos jumbos de perforación</h2>
      <p class="entrada">
        Reparación y fabricación de componentes para brazos de perforación de
        <?= e($DK_EQUIPOS['sandvik-dd321']['modelo']) ?>.
      </p>
      <div class="cierre-acciones">
        <a class="btn btn--claro" href="<?= e(u('sandvik-dd321')) ?>">Ver Sandvik DD321</a>
      </div>
    </div>
  </div>
</section>

<?php
$excluir = '';
require __DIR__ . '/inc/otros-servicios.php';
require __DIR__ . '/inc/footer.php';
?>
