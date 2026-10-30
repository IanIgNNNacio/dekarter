<?php
/** Cabecera y pie comunes de las páginas del cotizador. */

function cot_cabecera(string $titulo, bool $con_menu = true): void
{
    cot_cabeceras_privadas();
    $css = activo('cotizador/cotizador.css');
    ?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titulo) ?> · Cotizador Dekarter</title>
<link rel="icon" href="<?= e(img('favicon.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&display=swap">
<link rel="stylesheet" href="<?= e($css) ?>">
</head>
<body>
<header class="barra">
  <a class="barra-marca" href="<?= e(u('cotizador/')) ?>">
    <img src="<?= e(img('logo-dekarter-horizontal-negativo.svg')) ?>" alt="Maestranza Dekarter SPA" height="34">
    <span>Cotizador</span>
  </a>
  <?php if ($con_menu): ?>
    <nav class="barra-nav">
      <a href="<?= e(u('cotizador/')) ?>">Cotizaciones</a>
      <a class="btn btn--claro" href="<?= e(u('cotizador/editar')) ?>">+ Nueva</a>
      <a href="<?= e(u('cotizador/salir')) ?>">Salir</a>
    </nav>
  <?php endif; ?>
</header>
<main class="contenido">
    <?php
}

function cot_pie(array $scripts = []): void
{
    ?>
</main>
<?php foreach ($scripts as $s): ?>
<script src="<?= e($s) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
    <?php
}

/** Los scripts para armar el PDF: la librería, sus fuentes y el armado propio. */
function cot_scripts_pdf(): array
{
    return [
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.12/pdfmake.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.12/vfs_fonts.min.js',
        activo('cotizador/pdf.js'),
    ];
}
