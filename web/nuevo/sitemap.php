<?php
/**
 * Sitemap generado desde inc/config.php.
 *
 * Se genera en vez de escribirse a mano para que no pueda quedar desactualizado:
 * si mañana se agrega una página al arreglo de servicios o de equipos, aparece
 * acá sola. Hoy el sitio no tiene sitemap: /sitemap.xml devuelve 404.
 *
 * El .htaccess de la raíz debe apuntar /sitemap.xml a este archivo, o se
 * declara directamente esta dirección en Google Search Console.
 */

require_once __DIR__ . '/inc/config.php';

header('Content-Type: application/xml; charset=UTF-8');

// Un borrador en subcarpeta no se debe indexar, así que tampoco se anuncia.
if (DK_BORRADOR) {
    http_response_code(404);
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<!-- Sitio en borrador: sin sitemap hasta que pase a producción. -->';
    exit;
}

$fecha = date('Y-m-d');

$paginas = [
    ['', '1.0', 'weekly'],
];

foreach ($DK_SERVICIOS as $s) {
    $paginas[] = [$s['archivo'], $s['prioridad'], 'monthly'];
}
foreach ($DK_EQUIPOS as $q) {
    $paginas[] = [$q['archivo'], $q['prioridad'], 'monthly'];
}

$paginas[] = ['nosotros', '0.6', 'yearly'];
$paginas[] = ['contacto', '0.8', 'yearly'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($paginas as [$ruta, $prioridad, $frecuencia]): ?>
  <url>
    <loc><?= e(abs_url($ruta)) ?></loc>
    <lastmod><?= $fecha ?></lastmod>
    <changefreq><?= $frecuencia ?></changefreq>
    <priority><?= $prioridad ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
