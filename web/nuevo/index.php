<?php
require_once __DIR__ . '/inc/proceso-contacto.php';

$pagina = [
    'archivo' => '',
    'title'   => 'Maestranza Dekarter | Mecanizados y repuestos, Conchalí',
    'meta'    => 'Maestranza en Conchalí, Santiago. Mecanizados y fabricación de repuestos a medida para maquinaria de minería subterránea. Más de 25 años. +56 9 3903 9509.',
    'og_img'  => 'bujes-bronce-acero-1000.jpg',
    'schema'  => [
        '@type' => 'WebSite',
        'name'  => 'Maestranza Dekarter',
        'url'   => DK_DOMINIO . '/',
    ],
];

require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
  <?= foto([
      'base'        => 'hero-taller',
      'anchos'      => [900, 1600],
      'ancho'       => 1600,
      'alto'        => 1200,
      'sizes'       => '100vw',
      'clase'       => 'hero-foto',
      'alt'         => '',
      'prioritaria' => true,
  ]) ?>

  <div class="contenedor hero-contenido">
    <p class="rotulo">Maestranza · Conchalí, Santiago</p>
    <h1>La pieza que tu equipo necesita, <em>fabricada acá</em>.</h1>
    <p class="hero-bajada">
      Mecanizamos y fabricamos a medida los repuestos de maquinaria de minería
      subterránea: pasadores, bujes y componentes de brazos de perforación. Más
      de <?= (int) $DK['anos'] ?> años haciendo que un equipo parado vuelva a
      producir sin esperar una importación.
    </p>
    <div class="hero-acciones">
      <a class="btn btn--wsp" href="<?= e($DK['whatsapp']) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M17.5 14.4c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.1-.7.1l-.7.9c-.1.2-.3.2-.6.1a8 8 0 0 1-2.3-1.4 8.7 8.7 0 0 1-1.6-2c-.1-.2-.1-.4.1-.5l.7-.8c.2-.2.2-.4.1-.6l-.9-2.1c-.2-.5-.4-.5-.6-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 1.9 3 4.7 4.1 2.3 1 2.8.8 3.3.7.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3 0-.2-.2-.3-.4-.4zM12 22a10 10 0 0 1-5.1-1.4L2 22l1.4-4.8A10 10 0 1 1 12 22z"/>
        </svg>
        Cotizar por WhatsApp
      </a>
      <a class="btn btn--claro" href="#servicios">Ver qué hacemos</a>
    </div>
  </div>

  <dl class="credenciales">
    <div class="credencial">
      <dt>Experiencia</dt>
      <dd>Más de <?= (int) $DK['anos'] ?> años</dd>
    </div>
    <div class="credencial">
      <dt>Taller</dt>
      <dd><?= e($DK['comuna']) ?>, Santiago</dd>
    </div>
    <div class="credencial">
      <dt>Atención</dt>
      <dd>Lun a vie · 8:30 – 18:30</dd>
    </div>
    <div class="credencial">
      <dt>Especialidad</dt>
      <dd>Sandvik LH410 y DD321</dd>
    </div>
  </dl>
</section>

<section class="banda" id="servicios">
  <div class="contenedor">
    <div class="cabeza-seccion">
      <p class="rotulo">Servicios</p>
      <h2>Tres formas de resolver un repuesto</h2>
      <p class="entrada">
        Trabajamos sobre plano, sobre la pieza original o sobre el número de
        parte. Si el repuesto existe pero no llega, lo fabricamos.
      </p>
    </div>

    <div class="grilla grilla--3">
      <?php foreach ($DK_SERVICIOS as $s): ?>
        <a class="tarjeta" href="<?= e(u($s['archivo'])) ?>">
          <?= foto([
              'base'   => $s['imagen'],
              'anchos' => $s['anchos'],
              'ancho'  => 640,
              'alto'   => 400,
              'sizes'  => '(min-width: 900px) 360px, 100vw',
              'clase'  => 'tarjeta-foto',
              'alt'    => $s['alt'],
          ]) ?>
          <div class="tarjeta-cuerpo">
            <h3><?= e($s['nombre']) ?></h3>
            <p><?= e($s['resumen']) ?></p>
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

<section class="banda banda--oscura">
  <div class="contenedor">
    <div class="cabeza-seccion">
      <p class="rotulo">Equipos</p>
      <h2>Especialistas en dos máquinas en particular</h2>
      <p class="entrada">
        Conocemos de memoria los componentes de desgaste de estos dos equipos.
        Eso significa menos preguntas, menos idas y vueltas y una cotización más
        rápida.
      </p>
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
            <p>Pasadores, bujes y componentes mecanizados. Ver qué fabricamos para este equipo.</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="banda banda--papel">
  <div class="contenedor">
    <div class="cabeza-seccion">
      <p class="rotulo">Cómo trabajamos</p>
      <h2>De la consulta a la pieza en tres pasos</h2>
    </div>

    <ol class="pasos">
      <li class="paso">
        <h3>Nos mandas lo que tengas</h3>
        <p>
          El plano, una foto de la pieza gastada, el número de parte o la pieza
          original para copiar. Con cualquiera de esas cuatro cosas partimos.
        </p>
      </li>
      <li class="paso">
        <h3>Cotizamos</h3>
        <p>
          Te confirmamos material, cantidad y plazo. Si algo del plano no cierra,
          te lo decimos antes de fabricar, no después.
        </p>
      </li>
      <li class="paso">
        <h3>Fabricamos y entregas</h3>
        <p>
          Mecanizamos en nuestro taller de <?= e($DK['comuna']) ?> y coordinamos
          la entrega. Facturamos como <?= e($DK['razon_social']) ?>.
        </p>
      </li>
    </ol>
  </div>
</section>

<section class="banda">
  <div class="contenedor">
    <div class="grilla grilla--2" style="gap:44px;align-items:center">
      <div class="prosa">
        <p class="rotulo">Nosotros</p>
        <h2>Una maestranza, no un intermediario</h2>
        <p>
          Somos <?= e($DK['razon_social']) ?>, una maestranza de
          <?= e($DK['comuna']) ?> con más de <?= (int) $DK['anos'] ?> años
          fabricando y reparando componentes para la industria. La pieza se
          mecaniza acá: no la encargamos a un tercero ni la revendemos.
        </p>
        <p>
          Eso cambia dos cosas prácticas. La primera, que cuando preguntas por un
          plazo te contesta quien va a hacer la pieza. La segunda, que si algo
          hay que ajustar, se ajusta el mismo día.
        </p>
        <a class="btn btn--linea" href="<?= e(u('nosotros')) ?>">Conocer la maestranza</a>
      </div>
      <?= foto([
          'base'   => 'pasadores-bujes-mecanizados',
          'anchos' => [640, 900],
          'ancho'  => 900,
          'alto'   => 713,
          'sizes'  => '(min-width: 900px) 540px, 100vw',
          'alt'    => 'Pallet con dos series de bujes y pasadores de acero terminados en el taller de Dekarter',
      ]) ?>
    </div>
  </div>
</section>

<section class="banda banda--papel" id="contacto">
  <div class="contenedor">
    <?php require __DIR__ . '/inc/bloque-contacto.php'; ?>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
