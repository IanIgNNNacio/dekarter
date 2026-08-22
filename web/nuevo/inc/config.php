<?php
/**
 * Configuración única del sitio de Maestranza Dekarter.
 *
 * Todo dato de la empresa, toda ruta y todo texto de SEO vive acá. Las páginas
 * no repiten nada: leen de estos arreglos. Así el sitemap, los datos
 * estructurados, el menú y los títulos no se pueden desincronizar.
 */

declare(strict_types=1);

// Se fija explícito: el pie de página y la hora que aparece en los correos de
// contacto dicen «hora de Chile», y eso tiene que ser verdad aunque el hosting
// mueva el servidor o cambie su configuración por omisión.
date_default_timezone_set('America/Santiago');

// ---------------------------------------------------------------------------
// Entorno
// ---------------------------------------------------------------------------

/*
 * Mientras el rediseño viva en una subcarpeta del hosting, estas dos constantes
 * lo mantienen aislado del sitio en producción.
 *
 * Al pasar a la raíz del dominio:
 *   DK_BASE   → '/'
 *   DK_BORRADOR → false
 */
define('DK_BASE', '/nuevo/');
define('DK_BORRADOR', true);   // true ⇒ noindex: Google no indexa el borrador

/*
 * Los errores se registran, nunca se muestran.
 *
 * Se fija acá y no sólo en el .htaccess porque HostGator corre PHP por LSAPI:
 * las directivas php_flag de Apache se ignoran y display_errors queda como lo
 * dejó el hosting. Un aviso de PHP en pantalla le regala al visitante la ruta
 * absoluta del servidor, y al robot que sondea el formulario le confirma qué
 * campo tocó.
 *
 * Para depurar en el hosting: poner DK_DEPURAR en true, mirar, y devolverlo a
 * false. Nunca dejarlo en true.
 */
define('DK_DEPURAR', false);

ini_set('display_errors', DK_DEPURAR ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(DK_DEPURAR ? E_ALL : E_ALL & ~E_DEPRECATED);

/*
 * Dirección canónica del sitio. Hoy el servidor responde igual en cuatro
 * direcciones (con y sin www, con y sin HTTPS), lo que para Google son cuatro
 * copias del mismo sitio compitiendo entre ellas. Se elige una y las otras tres
 * redirigen: eso lo hace el .htaccess.
 */
define('DK_DOMINIO', 'https://dekarter.cl');
define('DK_RAIZ', __DIR__ . '/..');

// Carpeta de escritura para el limitador de envíos del formulario.
// Queda fuera del alcance web gracias a la regla de .htaccess.
define('DK_VAR', DK_RAIZ . '/var');

// ---------------------------------------------------------------------------
// La empresa
// ---------------------------------------------------------------------------

$DK = [
    'nombre'        => 'Maestranza Dekarter',
    'razon_social'  => 'Maestranza Dekarter SPA',
    'rut'           => '76.102.503-1',
    'rut_plano'     => '76102503-1',
    'descripcion'   => 'Maestranza especializada en mecanizados y fabricación de repuestos a medida para maquinaria de minería subterránea.',

    'calle'         => 'Dos Sur 5010',
    'comuna'        => 'Conchalí',
    'region'        => 'Región Metropolitana',
    'pais'          => 'CL',
    'cp'            => '8561308',
    'lat'           => -33.3816826,
    'lng'           => -70.6816555,
    'mapa_url'      => 'https://maps.app.goo.gl/bPhbXXPehSQVCGzB8',

    /*
     * Embed del mapa. Esta URL sale de Google Maps → Compartir → Insertar un
     * mapa, y es la misma que ya usaba el sitio anterior.
     *
     * Parece basura ilegible, pero el trozo que importa es
     * `1s0x9662...:0xc691...!2sDos Sur 5010, 8561308 Conchalí…`: es el
     * identificador del lugar más su etiqueta. Eso es lo que hace que el mapa
     * muestre la dirección escrita junto al pin.
     *
     * Construirla a mano con `?q=lat,lng&output=embed` NO sirve: Google la
     * resuelve a un pin pelado, sin etiqueta ni dirección. Con `?q=` y la
     * dirección en texto tampoco: queda como una búsqueda, no como un lugar.
     *
     * Si algún día hay que regenerarla: abrir la ficha del taller en Google
     * Maps, Compartir → Insertar un mapa, y copiar el `src` del iframe.
     */
    'mapa_embed'    => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3331.5932901318624!2d-70.6816554879468!3d-33.38168259388429!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9662c6f247a23f8d%3A0xc691cd84b14584d0!2sDos%20Sur%205010%2C%208561308%20Conchal%C3%AD%2C%20Regi%C3%B3n%20Metropolitana!5e0!3m2!1ses!2scl!4v1753837767733!5m2!1ses!2scl',

    'fono_e164'     => '+56939039509',
    'fono_visible'  => '+56 9 3903 9509',
    'email'         => 'ventas@dekarter.cl',

    /*
     * Remitente de los correos del formulario.
     *
     * Tiene que ser del propio dominio: si se pusiera el correo del visitante,
     * el servidor de destino vería un dominio ajeno enviado desde una IP que su
     * SPF no autoriza, y el mensaje se iría a spam. El visitante va en Reply-To.
     *
     * Se usa una dirección distinta de la de destino a propósito. Con `From` y
     * `To` iguales, los filtros de spam suman puntos en contra: es un patrón
     * típico de correo falsificado.
     *
     * PENDIENTE en cPanel: `web@dekarter.cl` no existe como casilla. Entrega
     * bien —está probado—, pero los rebotes no llegan a ninguna parte. Conviene
     * crearla como redirección a ventas@ para no perder los avisos de rebote.
     */
    'email_de'      => 'web@dekarter.cl',

    // Nombre visible del remitente: es lo que se lee en la bandeja de entrada,
    // antes de la dirección. Sólo caracteres simples, sin comas ni tildes: van
    // a una cabecera de correo y hay clientes viejos que las rompen.
    'email_de_nombre' => 'Web Dekarter',

    /*
     * Copia directa del aviso, opcional.
     *
     * El correo del dominio llega al instante al servidor de HostGator, pero de
     * ahí a Gmail pasa por el recolector POP de Google, que consulta cuando
     * quiere: se han medido demoras de 19 minutos. Poniendo acá la dirección de
     * Gmail del cliente, el aviso del formulario le llega directo y al instante,
     * sin depender de esa sincronización.
     *
     * Vacío = desactivado.
     */
    'email_copia'   => '',

    'horario_texto' => 'Lunes a viernes, 8:30 a 18:30',
    'horario_schema'=> ['Mo', 'Tu', 'We', 'Th', 'Fr'],
    'hora_abre'     => '08:30',
    'hora_cierra'   => '18:30',

    // El sitio actual declara «más de 25 años de experiencia».
    'anos'          => 25,
];

$DK['whatsapp'] = 'https://wa.me/' . ltrim($DK['fono_e164'], '+')
    . '?text=' . rawurlencode('Hola, necesito una cotización de maestranza.');

// ---------------------------------------------------------------------------
// Servicios
// ---------------------------------------------------------------------------
// El orden de este arreglo manda: define el menú, la grilla de la portada y el
// sitemap. `prioridad` es la del sitemap.xml.

$DK_SERVICIOS = [
    'mecanizados' => [
        'archivo'    => 'mecanizados',
        'nombre'     => 'Mecanizados',
        'menu'       => 'Mecanizados',
        'h1'         => 'Mecanizados para maquinaria de minería',
        'title'      => 'Mecanizados para maquinaria minera | Maestranza en Conchalí',
        'meta'       => 'Mecanizado de piezas y componentes para maquinaria de minería subterránea. Maestranza con taller propio en Conchalí, Santiago. Cotiza al +56 9 3903 9509.',
        'resumen'    => 'Mecanizado de piezas y componentes de maquinaria minera, sobre plano o sobre la pieza de muestra.',
        'imagen'     => 'pasadores-bujes-mecanizados',
        'anchos'     => [640, 900],
        'alt'        => 'Pallet con bujes y pasadores de acero recién mecanizados en Maestranza Dekarter',
        'prioridad'  => '0.9',
    ],
    'centros-de-mecanizado' => [
        'archivo'    => 'centros-de-mecanizado',
        'nombre'     => 'Trabajos en centros de mecanizado',
        'menu'       => 'Centros de mecanizado',
        'h1'         => 'Trabajos en centros de mecanizado',
        'title'      => 'Trabajos en centros de mecanizado CNC | Maestranza Dekarter',
        'meta'       => 'Trabajos en centros de mecanizado para piezas de maquinaria minera: geometrías complejas y series con la misma medida. Maestranza en Conchalí.',
        'resumen'    => 'Piezas de geometría compleja y series que necesitan repetir la misma medida una y otra vez.',
        'imagen'     => 'centro-mecanizado-interior',
        'anchos'     => [640, 1000],
        'alt'        => 'Interior de un centro de mecanizado de Dekarter: husillo, prensa y viruta de acero sobre la mesa',
        'prioridad'  => '0.9',
    ],
    'repuestos-a-medida' => [
        'archivo'    => 'repuestos-a-medida',
        'nombre'     => 'Fabricación de repuestos a medida',
        'menu'       => 'Repuestos a medida',
        'h1'         => 'Fabricación de repuestos a medida',
        'title'      => 'Repuestos a medida para maquinaria minera | Dekarter',
        'meta'       => 'Fabricamos el repuesto que no llega o ya no se vende: pasadores, bujes y componentes para brazos de perforación. Maestranza en Conchalí con más de 25 años.',
        'resumen'    => 'El repuesto que no llega, que está descontinuado o que sale más barato fabricar que importar.',
        'imagen'     => 'bujes-bronce-acero',
        'anchos'     => [640, 1000],
        'alt'        => 'Bujes de bronce y de acero de distintos diámetros, terminados, sobre un pallet en Maestranza Dekarter',
        'prioridad'  => '1.0',
    ],
];

// ---------------------------------------------------------------------------
// Equipos
// ---------------------------------------------------------------------------
// Estas dos páginas son la apuesta de posicionamiento: «Sandvik LH410» y
// «Sandvik DD321» son términos exactos que busca el mantenedor de una mina, con
// muy poca competencia en Chile.

$DK_EQUIPOS = [
    'sandvik-lh410' => [
        'archivo'   => 'sandvik-lh410',
        'modelo'    => 'Sandvik LH410',
        'tipo'      => 'cargador LHD de bajo perfil',
        'menu'      => 'Sandvik LH410',
        'h1'        => 'Mecanizados y repuestos para Sandvik LH410',
        'title'     => 'Repuestos y mecanizados Sandvik LH410 | Maestranza Dekarter',
        'meta'      => 'Fabricación de pasadores, bujes y componentes mecanizados para cargadores Sandvik LH410. Maestranza en Conchalí, Santiago. Cotiza al +56 9 3903 9509.',
        // Foto propia de piezas fabricadas. No se usan fotos de prensa del
        // fabricante del equipo: son de su propiedad y llevan su marca.
        'imagen'    => 'bujes-bronce-acero',
        'anchos'    => [640, 1000],
        'alt'       => 'Bujes de bronce y acero fabricados en Dekarter para componentes de cargadores LHD',
        'prioridad' => '0.9',
    ],
    'sandvik-dd321' => [
        'archivo'   => 'sandvik-dd321',
        'modelo'    => 'Sandvik DD321',
        'tipo'      => 'jumbo de perforación',
        'menu'      => 'Sandvik DD321',
        'h1'        => 'Mecanizados y repuestos para Sandvik DD321',
        'title'     => 'Repuestos y mecanizados Sandvik DD321 | Maestranza Dekarter',
        'meta'      => 'Reparación y fabricación de componentes para brazos de perforación de jumbos Sandvik DD321. Maestranza en Conchalí, Santiago, con más de 25 años.',
        'imagen'    => 'brazo-perforacion-viga-avance',
        'anchos'    => [640, 1200],
        'alt'       => 'Brazo de perforación con su viga de avance, en el taller de Maestranza Dekarter',
        'prioridad' => '0.9',
    ],
];

// ---------------------------------------------------------------------------
// Ayudantes
// ---------------------------------------------------------------------------

/** Escapa para HTML. Se usa en TODA variable que se imprima. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ruta absoluta dentro del sitio, respetando la subcarpeta del borrador. */
function u(string $ruta = ''): string
{
    return DK_BASE . ltrim($ruta, '/');
}

/** URL completa con dominio. Para canonical, Open Graph y sitemap. */
function abs_url(string $ruta = ''): string
{
    return DK_DOMINIO . u($ruta);
}

/** Ruta de una imagen del catálogo. */
function img(string $archivo): string
{
    return u('assets/img/' . $archivo);
}

/**
 * Ruta de un CSS o un JS, con la versión puesta sola.
 *
 * El .htaccess manda estos archivos con `immutable` y un año de caché, que es
 * lo correcto para que un visitante que vuelve no los descargue de nuevo. El
 * problema es el otro lado: si cambia el archivo, el navegador que ya lo tiene
 * NO lo va a pedir nunca más, y el visitante se queda con el diseño viejo.
 *
 * Antes esto se resolvía con un `?v=1` escrito a mano, que había que acordarse
 * de subir en cada cambio. Acordarse no es un plan. Ahora la versión es la fecha
 * de modificación del archivo: cambia el archivo, cambia la dirección, y el
 * navegador lo vuelve a pedir. Sin intervención.
 */
function activo(string $ruta): string
{
    $absoluta = DK_RAIZ . '/' . ltrim($ruta, '/');
    $version  = is_file($absoluta) ? (string) filemtime($absoluta) : '1';

    return u($ruta) . '?v=' . $version;
}

/**
 * Imprime una foto responsiva: WebP primero, JPEG de respaldo para navegadores
 * viejos, y srcset para que un teléfono no descargue la versión de escritorio.
 *
 * Opciones: base, anchos, alt, ancho, alto, clase, sizes, prioritaria.
 */
function foto(array $o): string
{
    $base   = $o['base'];
    $anchos = $o['anchos'];
    $sizes  = $o['sizes'] ?? '100vw';
    $mayor  = max($anchos);

    $lista = static function (string $ext) use ($base, $anchos): string {
        $partes = [];
        foreach ($anchos as $a) {
            $partes[] = img("{$base}-{$a}.{$ext}") . ' ' . $a . 'w';
        }
        return implode(', ', $partes);
    };

    // La imagen del hero se carga con prioridad; el resto, en diferido.
    $carga = !empty($o['prioritaria'])
        ? ' fetchpriority="high" decoding="async"'
        : ' loading="lazy" decoding="async"';

    return '<picture>'
        . '<source type="image/webp" srcset="' . e($lista('webp')) . '" sizes="' . e($sizes) . '">'
        . '<img src="' . e(img("{$base}-{$mayor}.jpg")) . '"'
        . ' srcset="' . e($lista('jpg')) . '" sizes="' . e($sizes) . '"'
        . ' width="' . (int) $o['ancho'] . '" height="' . (int) $o['alto'] . '"'
        . ' alt="' . e($o['alt']) . '"'
        . (isset($o['clase']) ? ' class="' . e($o['clase']) . '"' : '')
        . $carga
        . '></picture>';
}
