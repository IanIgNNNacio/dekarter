<?php
/**
 * Cabecera HTML. Espera que la página haya definido antes de incluirla:
 *
 *   $pagina = [
 *     'archivo'  => 'contacto',                 // sin extensión; '' para la portada
 *     'title'    => 'Título para Google',
 *     'meta'     => 'Meta description, 150–160 caracteres',
 *     'og_img'   => '4.jpeg',                   // opcional
 *     'schema'   => [ ... ],                    // opcional: JSON-LD extra
 *     'migas'    => [['Servicios', 'mecanizados'], ...],  // opcional
 *   ];
 */

require_once __DIR__ . '/config.php';

$archivo   = $pagina['archivo'] ?? '';
$canonical = abs_url($archivo === '' ? '' : $archivo);
$og_img    = abs_url('assets/img/' . ($pagina['og_img'] ?? '1.jpeg'));

// --- Datos estructurados -----------------------------------------------------
// Una sola ficha de negocio local, repetida en todas las páginas: es lo que
// Google usa para el panel lateral y el Perfil de Empresa.

$negocio = [
    '@type'       => 'LocalBusiness',
    '@id'         => DK_DOMINIO . '/#negocio',
    'name'        => $DK['nombre'],
    'legalName'   => $DK['razon_social'],
    'taxID'       => $DK['rut'],
    'description' => $DK['descripcion'],
    'url'         => DK_DOMINIO . '/',
    'telephone'   => $DK['fono_e164'],
    'email'       => $DK['email'],
    'image'       => abs_url('assets/img/avatar-dekarter.svg'),
    'logo'        => abs_url('assets/img/marca-dekarter.svg'),
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $DK['calle'],
        'addressLocality' => $DK['comuna'],
        'addressRegion'   => $DK['region'],
        'postalCode'      => $DK['cp'],
        'addressCountry'  => $DK['pais'],
    ],
    'geo' => [
        '@type'     => 'GeoCoordinates',
        'latitude'  => $DK['lat'],
        'longitude' => $DK['lng'],
    ],
    'openingHoursSpecification' => [[
        '@type'     => 'OpeningHoursSpecification',
        'dayOfWeek' => $DK['horario_schema'],
        'opens'     => $DK['hora_abre'],
        'closes'    => $DK['hora_cierra'],
    ]],
    'areaServed'  => ['@type' => 'Country', 'name' => 'Chile'],
    'priceRange'  => '$$',
];

$grafo = [$negocio];

// Migas de pan, si la página las declaró
if (!empty($pagina['migas'])) {
    $items = [[
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => 'Inicio',
        'item'     => DK_DOMINIO . '/',
    ]];
    foreach ($pagina['migas'] as $i => $miga) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 2,
            'name'     => $miga[0],
            'item'     => abs_url($miga[1]),
        ];
    }
    $grafo[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

// Ficha extra propia de la página (servicio, producto, etc.)
if (!empty($pagina['schema'])) {
    $grafo[] = $pagina['schema'];
}

$jsonld = json_encode(
    ['@context' => 'https://schema.org', '@graph' => $grafo],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= e($pagina['title']) ?></title>
<meta name="description" content="<?= e($pagina['meta']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if (DK_BORRADOR): ?>
<meta name="robots" content="noindex, nofollow"><!-- Borrador en subcarpeta. Se apaga solo al poner DK_BORRADOR en false. -->
<?php elseif (defined('DK_SIN_INDICE')): ?>
<meta name="robots" content="noindex, follow"><!-- Página de servicio: no aporta a la búsqueda. -->
<?php else: ?>
<meta name="robots" content="index, follow, max-image-preview:large">
<?php endif; ?>

<meta name="geo.region" content="CL-RM">
<meta name="geo.placename" content="<?= e($DK['comuna']) ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($DK['nombre']) ?>">
<meta property="og:locale" content="es_CL">
<meta property="og:title" content="<?= e($pagina['title']) ?>">
<meta property="og:description" content="<?= e($pagina['meta']) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($og_img) ?>">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="<?= e(img('favicon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(img('avatar-dekarter.svg')) ?>">
<meta name="theme-color" content="#163a7d">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&display=swap">
<link rel="stylesheet" href="<?= e(activo('assets/css/dekarter.css')) ?>">

<script type="application/ld+json"><?= $jsonld ?></script>
</head>
<body>
<a class="saltar" href="#principal">Saltar al contenido</a>
