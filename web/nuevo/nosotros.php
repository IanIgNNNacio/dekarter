<?php
require_once __DIR__ . '/inc/config.php';

$pagina = [
    'archivo' => 'nosotros',
    'title'   => 'Nosotros | Maestranza Dekarter, Conchalí · Más de 25 años',
    'meta'    => 'Maestranza Dekarter SPA: más de 25 años mecanizando y fabricando repuestos para maquinaria de minería. Taller propio en Dos Sur 5010, Conchalí, Santiago.',
    'og_img'  => 'centro-mecanizado-interior-1000.jpg',
    'migas'   => [['Nosotros', 'nosotros']],
    'schema'  => [
        '@type'       => 'AboutPage',
        'name'        => 'Nosotros — Maestranza Dekarter',
        'description' => 'Historia y forma de trabajo de Maestranza Dekarter SPA.',
        'about'       => ['@id' => DK_DOMINIO . '/#negocio'],
    ],
];

require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<section class="banda">
  <div class="contenedor">
    <div class="cabeza-seccion">
      <p class="rotulo">Nosotros</p>
      <h1>Una maestranza con taller propio en Conchalí</h1>
    </div>

    <div class="grilla grilla--2" style="gap:44px;align-items:start">
      <div class="prosa">
        <p>
          <?= e($DK['razon_social']) ?> lleva más de <?= (int) $DK['anos'] ?> años
          fabricando y reparando componentes para la industria. Nacimos como
          maestranza y seguimos siendo eso: un taller donde la pieza se mecaniza,
          se mide y se entrega.
        </p>
        <p>
          Con los años el trabajo se fue concentrando en maquinaria de minería
          subterránea, y dentro de eso en las piezas que más se consumen:
          pasadores, bujes y componentes de brazos de perforación. Hoy los dos
          equipos que más vemos son el
          <a href="<?= e(u('sandvik-lh410')) ?>">Sandvik LH410</a> y el
          <a href="<?= e(u('sandvik-dd321')) ?>">Sandvik DD321</a>.
        </p>

        <h2>Cómo trabajamos</h2>
        <p>
          Somos maestranza, no distribuidor. La pieza no se encarga a un tercero
          ni se revende: se hace acá. En la práctica eso significa tres cosas.
        </p>
        <ul class="lista-chaflan">
          <li>Cuando preguntas por un plazo, te responde quien va a hacer la pieza.</li>
          <li>Si el plano tiene algo que no cierra, te lo decimos antes de mecanizar.</li>
          <li>Si al montar hay que ajustar una medida, se ajusta el mismo día.</li>
        </ul>

        <h2>Soluciones a medida, en el sentido literal</h2>
        <p>
          Buena parte de lo que fabricamos no existe en un catálogo: es la pieza
          descontinuada, la adaptación de un equipo modificado, o el repuesto que
          sale más barato mecanizar que importar. Ese es el trabajo que sabemos
          hacer.
        </p>

        <div class="cierre-acciones" style="margin-top:10px">
          <a class="btn btn--principal" href="<?= e(u('contacto')) ?>">Hablemos de tu pieza</a>
          <a class="btn btn--linea" href="<?= e($DK['mapa_url']) ?>" target="_blank" rel="noopener">Ver el taller en el mapa</a>
        </div>
      </div>

      <div>
        <?= foto([
            'base'   => 'centro-mecanizado-interior',
            'anchos' => [640, 1000],
            'ancho'  => 1000,
            'alto'   => 750,
            'sizes'  => '(min-width: 900px) 540px, 100vw',
            'alt'    => 'Interior de un centro de mecanizado de Dekarter, con husillo, prensa y viruta de acero sobre la mesa',
        ]) ?>

        <dl class="ficha" style="margin-top:26px">
          <div>
            <dt>Razón social</dt>
            <dd><?= e($DK['razon_social']) ?></dd>
          </div>
          <div>
            <dt>RUT</dt>
            <dd class="dato-mono"><?= e($DK['rut']) ?></dd>
          </div>
          <div>
            <dt>Dirección</dt>
            <dd><?= e($DK['calle']) ?>, <?= e($DK['comuna']) ?>, <?= e($DK['region']) ?></dd>
          </div>
          <div>
            <dt>Horario</dt>
            <dd><?= e($DK['horario_texto']) ?></dd>
          </div>
          <div>
            <dt>Teléfono</dt>
            <dd class="dato-mono"><?= e($DK['fono_visible']) ?></dd>
          </div>
          <div>
            <dt>Correo</dt>
            <dd><a href="mailto:<?= e($DK['email']) ?>"><?= e($DK['email']) ?></a></dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</section>

<?php
$excluir = '';
require __DIR__ . '/inc/otros-servicios.php';
require __DIR__ . '/inc/footer.php';
?>
