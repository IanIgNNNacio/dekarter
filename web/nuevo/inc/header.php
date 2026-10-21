<?php
/** Barra superior y migas de pan. Se incluye justo después de head.php. */

$actual = $pagina['archivo'] ?? '';

/** Marca el enlace de la página en curso, para lectores de pantalla y para el subrayado. */
function nav_actual(string $archivo, string $actual): string
{
    return $archivo === $actual ? ' aria-current="page"' : '';
}
?>
<header class="sitio-cabecera">
  <div class="contenedor cabecera-fila">

    <a class="marca" href="<?= e(u()) ?>">
      <img src="<?= e(img('logo-dekarter-horizontal.svg')) ?>" width="185" height="44"
           alt="<?= e($DK['razon_social']) ?> — inicio">
    </a>

    <button class="menu-boton" type="button"
            aria-expanded="false" aria-controls="nav-principal">
      <span class="solo-lectores">Abrir el menú</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
           stroke-linecap="round" aria-hidden="true">
        <path d="M4 7h16M4 12h16M4 17h16"/>
      </svg>
    </button>

    <nav class="nav-principal" id="nav-principal" data-abierto="no"
         aria-label="Navegación principal">
      <?php
      /*
       * Las variables de estos bucles llevan prefijo `nav_` a propósito.
       * Un include comparte el ámbito de variables con la página que lo llama:
       * si acá el bucle se llamara $s, le pisaría el $s de la página —que es el
       * servicio que se está mostrando— y el <h1> saldría con el texto de otro
       * servicio. Pasó exactamente eso.
       */
      ?>
      <?php foreach ($DK_SERVICIOS as $nav_s): ?>
        <a href="<?= e(u($nav_s['archivo'])) ?>"<?= nav_actual($nav_s['archivo'], $actual) ?>><?= e($nav_s['menu']) ?></a>
      <?php endforeach; ?>
      <?php foreach ($DK_EQUIPOS as $nav_q): ?>
        <a href="<?= e(u($nav_q['archivo'])) ?>"<?= nav_actual($nav_q['archivo'], $actual) ?>><?= e($nav_q['menu']) ?></a>
      <?php endforeach; ?>
      <a href="<?= e(u('nosotros')) ?>"<?= nav_actual('nosotros', $actual) ?>>Nosotros</a>
      <a href="<?= e(u('contacto')) ?>"<?= nav_actual('contacto', $actual) ?>>Contacto</a>
    </nav>

    <a class="cabecera-fono" href="tel:<?= e($DK['fono_e164']) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.6 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.8.6a2 2 0 0 1 1.7 2z"/>
      </svg>
      <?= e($DK['fono_visible']) ?>
    </a>

  </div>
</header>

<?php if (!empty($pagina['migas'])): ?>
<nav class="migas" aria-label="Ruta de navegación">
  <div class="contenedor">
    <ol>
      <li><a href="<?= e(u()) ?>">Inicio</a></li>
      <?php
      $ultimo = count($pagina['migas']) - 1;
      foreach ($pagina['migas'] as $i => $miga):
      ?>
        <li>
          <?php if ($i === $ultimo): ?>
            <span aria-current="page"><?= e($miga[0]) ?></span>
          <?php else: ?>
            <a href="<?= e(u($miga[1])) ?>"><?= e($miga[0]) ?></a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</nav>
<?php endif; ?>

<main id="principal">
