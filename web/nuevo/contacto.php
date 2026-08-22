<?php
require_once __DIR__ . '/inc/proceso-contacto.php';

$pagina = [
    'archivo' => 'contacto',
    'title'   => 'Contacto y cotizaciones | Maestranza Dekarter, Conchalí',
    'meta'    => 'Cotiza mecanizados y repuestos a medida para maquinaria de minería. Taller en Dos Sur 5010, Conchalí. WhatsApp +56 9 3903 9509, lunes a viernes 8:30–18:30.',
    'og_img'  => 'bujes-bronce-acero-1000.jpg',
    'migas'   => [['Contacto', 'contacto']],
    'schema'  => [
        '@type'       => 'ContactPage',
        'name'        => 'Contacto — Maestranza Dekarter',
        'description' => 'Formulario de cotización, teléfono, WhatsApp y ubicación del taller.',
        'about'       => ['@id' => DK_DOMINIO . '/#negocio'],
    ],
];

require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<section class="banda">
  <div class="contenedor">
    <div class="cabeza-seccion">
      <p class="rotulo">Contacto</p>
      <h1>Cotiza tu pieza</h1>
      <p class="entrada">
        Respondemos en horario de taller: <?= e($DK['horario_texto']) ?>. Si es
        urgente, WhatsApp es el camino más rápido.
      </p>
    </div>

    <?php require __DIR__ . '/inc/bloque-contacto.php'; ?>
  </div>
</section>

<?php
$excluir = '';
require __DIR__ . '/inc/otros-servicios.php';
require __DIR__ . '/inc/footer.php';
?>
