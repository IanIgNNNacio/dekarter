<?php
/**
 * Cotizador de Dekarter — sesión, almacenamiento y reglas.
 *
 * Todo se guarda como archivos en var/cotizador/, que el .htaccess bloquea
 * para los navegadores:
 *
 *   numerador.json            el último correlativo usado
 *   clientes.json             libreta de clientes
 *   cotizaciones/3462-26.json una por cotización
 *   fotos/3462-26/*.jpg       el anexo fotográfico de cada una
 *   sesiones/                 sesiones de PHP propias (ver cot_sesion)
 *
 * Archivos y no una base de datos: Dekarter hace unas pocas cotizaciones al
 * día, cada una se puede abrir y respaldar a mano, y no depende de que el
 * hosting tenga un motor instalado. El correlativo es lo único que necesita
 * cuidado con la concurrencia, y lo tiene (cot_siguiente_numero).
 */

declare(strict_types=1);

define('DK_SUBAPP', 'cotizador');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cotizador-ajustes.php';

// Hash de la contraseña. Lo crea web/herramientas/clave_cotizador.py; el
// archivo no se versiona. Sin él, nadie puede entrar.
if (is_file(__DIR__ . '/cotizador.local.php')) {
    require __DIR__ . '/cotizador.local.php';
}

define('COT_DIR', DK_VAR . '/cotizador');
define('COT_DURACION_SESION', 10 * 3600);   // una jornada de trabajo
define('COT_MAX_INTENTOS', 8);              // intentos de clave fallidos…
define('COT_VENTANA_INTENTOS', 900);        // …cada 15 minutos

// ---------------------------------------------------------------------------
// Utilidades de archivos
// ---------------------------------------------------------------------------

function cot_dir(string $sub = ''): string
{
    $ruta = COT_DIR . ($sub !== '' ? '/' . $sub : '');
    if (!is_dir($ruta)) {
        @mkdir($ruta, 0700, true);
    }
    return $ruta;
}

function cot_leer_json(string $ruta, $defecto)
{
    if (!is_file($ruta)) {
        return $defecto;
    }
    $datos = json_decode((string) file_get_contents($ruta), true);
    return is_array($datos) ? $datos : $defecto;
}

/**
 * Escribe un JSON de forma atómica: primero a un archivo temporal y después se
 * renombra. Si el servidor se corta a la mitad, queda el archivo anterior
 * entero, nunca uno a medio escribir.
 */
function cot_escribir_json(string $ruta, array $datos): bool
{
    $tmp = $ruta . '.' . bin2hex(random_bytes(4)) . '.tmp';
    $json = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($tmp, $json, LOCK_EX) === false) {
        @unlink($tmp);
        return false;
    }
    return rename($tmp, $ruta);
}

// ---------------------------------------------------------------------------
// Sesión y acceso
// ---------------------------------------------------------------------------

function cot_sesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Sesiones en una carpeta propia y con vida larga. Con la configuración por
    // omisión del hosting, PHP borra las sesiones a los 24 minutos sin
    // actividad: alguien armando una cotización larga perdería la sesión justo
    // al apretar Guardar.
    ini_set('session.save_path', cot_dir('sesiones'));
    ini_set('session.gc_maxlifetime', (string) COT_DURACION_SESION);
    ini_set('session.use_strict_mode', '1');

    session_name('DKCOT');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => DK_BASE . 'cotizador/',
        'secure'   => (($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function cot_logueado(): bool
{
    cot_sesion();
    if (empty($_SESSION['ok']) || ($_SESSION['vence'] ?? 0) < time()) {
        return false;
    }
    $_SESSION['vence'] = time() + COT_DURACION_SESION;   // se renueva con el uso
    return true;
}

function cot_csrf(): string
{
    cot_sesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function cot_csrf_valido(string $token): bool
{
    cot_sesion();
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/** Para páginas: sin sesión, al formulario de ingreso. */
function cot_exigir_login(): void
{
    if (!cot_logueado()) {
        header('Location: ' . u('cotizador/'), true, 303);
        exit;
    }
}

/** Para la API: sin sesión, 401 en JSON. */
function cot_exigir_login_api(): void
{
    if (!cot_logueado()) {
        cot_json(['error' => 'La sesión venció. Vuelve a ingresar.'], 401);
    }
}

/**
 * Comprueba la clave, con límite de intentos por IP.
 * Devuelve null si entró, o el mensaje de error.
 */
function cot_ingresar(string $clave): ?string
{
    if (!defined('DK_COT_CLAVE_HASH')) {
        return 'El cotizador todavía no tiene contraseña configurada.';
    }

    $archivo = cot_dir() . '/intentos.json';
    $ip      = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . DK_COT_CLAVE_HASH), 0, 16);
    $ahora   = time();
    $todos   = cot_leer_json($archivo, []);
    $mios    = array_values(array_filter($todos[$ip] ?? [], fn($t) => $t > $ahora - COT_VENTANA_INTENTOS));

    if (count($mios) >= COT_MAX_INTENTOS) {
        return 'Demasiados intentos. Espera 15 minutos y vuelve a probar.';
    }

    if (!password_verify($clave, DK_COT_CLAVE_HASH)) {
        $mios[] = $ahora;
        $todos[$ip] = $mios;
        cot_escribir_json($archivo, $todos);
        usleep(400000);   // frena un poco los intentos automáticos
        return 'La contraseña no es correcta.';
    }

    unset($todos[$ip]);
    cot_escribir_json($archivo, $todos);

    cot_sesion();
    session_regenerate_id(true);
    $_SESSION['ok']    = 1;
    $_SESSION['vence'] = $ahora + COT_DURACION_SESION;
    $_SESSION['csrf']  = bin2hex(random_bytes(32));
    return null;
}

function cot_salir(): void
{
    cot_sesion();
    $_SESSION = [];
    session_destroy();
}

// ---------------------------------------------------------------------------
// Correlativo
// ---------------------------------------------------------------------------

/**
 * Entrega el próximo número, garantizado único.
 *
 * El archivo se bloquea mientras se lee y se escribe: si dos personas guardan
 * una cotización nueva en el mismo segundo, una espera a la otra y cada una
 * recibe su propio número. Sin el bloqueo, las dos leerían 3462 y habría dos
 * cotizaciones con el mismo correlativo.
 */
function cot_siguiente_numero(): string
{
    global $COT;

    $mango = fopen(cot_dir() . '/numerador.json', 'c+');
    if ($mango === false || !flock($mango, LOCK_EX)) {
        throw new RuntimeException('No se pudo reservar el correlativo.');
    }

    $actual = json_decode((string) stream_get_contents($mango), true);
    $ultimo = (int) ($actual['ultimo'] ?? ($COT['numero_inicial'] - 1));
    $nuevo  = $ultimo + 1;

    ftruncate($mango, 0);
    rewind($mango);
    fwrite($mango, json_encode(['ultimo' => $nuevo, 'fecha' => date('c')]));
    fflush($mango);
    flock($mango, LOCK_UN);
    fclose($mango);

    return $nuevo . '-' . date('y');
}

/** Un número válido tiene la forma 3462-26. Todo lo demás se rechaza. */
function cot_numero_valido(string $n): bool
{
    return (bool) preg_match('~^\d{1,7}-\d{2}$~', $n);
}

// ---------------------------------------------------------------------------
// Cotizaciones
// ---------------------------------------------------------------------------

function cot_ruta(string $numero): string
{
    if (!cot_numero_valido($numero)) {
        throw new InvalidArgumentException('Número inválido');
    }
    return cot_dir('cotizaciones') . '/' . $numero . '.json';
}

function cot_cargar(string $numero): ?array
{
    if (!cot_numero_valido($numero)) {
        return null;
    }
    $c = cot_leer_json(cot_ruta($numero), null);
    return is_array($c) ? $c : null;
}

/** Todas, de la más nueva a la más vieja. */
function cot_listar(string $buscar = ''): array
{
    $lista = [];
    foreach (glob(cot_dir('cotizaciones') . '/*.json') ?: [] as $archivo) {
        $c = cot_leer_json($archivo, null);
        if (!is_array($c)) {
            continue;
        }
        if ($buscar !== '') {
            $pajar = mb_strtolower(implode(' ', [
                $c['numero'] ?? '', $c['cliente']['razon'] ?? '', $c['cliente']['rut'] ?? '',
                $c['referencia'] ?? '', $c['cliente']['atencion'] ?? '',
            ]));
            if (mb_strpos($pajar, mb_strtolower($buscar)) === false) {
                continue;
            }
        }
        $lista[] = $c;
    }
    usort($lista, function ($a, $b) {
        [$na, $aa] = array_map('intval', explode('-', $a['numero']));
        [$nb, $ab] = array_map('intval', explode('-', $b['numero']));
        return [$ab, $nb] <=> [$aa, $na];
    });
    return $lista;
}

/** Limpia un texto de una línea. */
function cot_txt($v, int $max = 200): string
{
    $v = preg_replace('~[\x00-\x1F\x7F]~u', ' ', (string) $v) ?? '';
    return mb_substr(trim(preg_replace('~\s+~u', ' ', $v) ?? ''), 0, $max);
}

/** Limpia un texto de varias líneas. */
function cot_txt_largo($v, int $max = 2000): string
{
    $v = str_replace("\r", '', (string) $v);
    $v = preg_replace('~[\x00-\x09\x0B-\x1F\x7F]~u', ' ', $v) ?? '';
    return mb_substr(trim($v), 0, $max);
}

/**
 * Normaliza lo que llega del formulario y recalcula los totales en el
 * servidor. Los totales del navegador son sólo para ver: los que valen son
 * éstos, para que un error de JavaScript no termine en una cotización mal
 * sumada.
 */
function cot_normalizar(array $e, ?array $anterior): array
{
    global $COT;

    $items = [];
    foreach (array_slice($e['items'] ?? [], 0, $COT['max_items']) as $i) {
        $detalle = cot_txt($i['detalle'] ?? '', 200);
        if ($detalle === '') {
            continue;
        }
        $cantidad = max(0, round((float) str_replace(',', '.', (string) ($i['cantidad'] ?? 0)), 2));
        $unitario = max(0, (int) round((float) preg_replace('~[^\d]~', '', (string) ($i['unitario'] ?? 0))));
        $items[] = [
            'cantidad'    => $cantidad,
            'detalle'     => $detalle,
            'descripcion' => cot_txt_largo($i['descripcion'] ?? '', 600),
            'unitario'    => $unitario,
            'total'       => (int) round($cantidad * $unitario),
        ];
    }

    $neto  = array_sum(array_column($items, 'total'));
    $iva   = (int) round($neto * $COT['iva']);

    $cli = $e['cliente'] ?? [];

    return [
        'numero'     => $anterior['numero'] ?? null,
        'fecha'      => preg_match('~^\d{4}-\d{2}-\d{2}$~', (string) ($e['fecha'] ?? '')) ? $e['fecha'] : date('Y-m-d'),
        'cliente'    => [
            'razon'     => cot_txt($cli['razon'] ?? '', 120),
            'rut'       => cot_txt($cli['rut'] ?? '', 20),
            'direccion' => cot_txt($cli['direccion'] ?? '', 160),
            'giro'      => cot_txt($cli['giro'] ?? '', 120),
            'atencion'  => cot_txt($cli['atencion'] ?? '', 100),
        ],
        'referencia' => cot_txt($e['referencia'] ?? '', 160),
        'validez'    => cot_txt($e['validez'] ?? '', 40),
        'pago'       => cot_txt($e['pago'] ?? '', 60),
        'guia'       => cot_txt($e['guia'] ?? '', 40),
        'entrega'    => cot_txt($e['entrega'] ?? '', 80),
        'notas'      => cot_txt_largo($e['notas'] ?? '', 1500),
        'items'      => $items,
        'neto'       => $neto,
        'iva'        => $iva,
        'total'      => $neto + $iva,
        'fotos'      => $anterior['fotos'] ?? [],
        'creada'     => $anterior['creada'] ?? date('c'),
        'modificada' => date('c'),
    ];
}

/**
 * Guarda. Si es nueva, recién aquí recibe su correlativo: abrir el formulario
 * no gasta números, sólo guardar.
 */
function cot_guardar(array $entrada, ?string $numero): array
{
    $anterior = $numero !== null ? cot_cargar($numero) : null;
    if ($numero !== null && $anterior === null) {
        throw new InvalidArgumentException('Esa cotización no existe.');
    }

    $c = cot_normalizar($entrada, $anterior);
    if ($c['cliente']['razon'] === '') {
        throw new InvalidArgumentException('Falta el cliente.');
    }
    if ($c['items'] === []) {
        throw new InvalidArgumentException('Agrega al menos un ítem.');
    }

    if ($c['numero'] === null) {
        $c['numero'] = cot_siguiente_numero();
    }

    if (!cot_escribir_json(cot_ruta($c['numero']), $c)) {
        throw new RuntimeException('No se pudo guardar en el servidor.');
    }

    cot_recordar_cliente($c['cliente']);
    return $c;
}

// ---------------------------------------------------------------------------
// Clientes
// ---------------------------------------------------------------------------

function cot_clientes(): array
{
    global $COT;
    $ruta = cot_dir() . '/clientes.json';
    if (!is_file($ruta)) {
        cot_escribir_json($ruta, $COT['clientes_iniciales']);
    }
    $lista = cot_leer_json($ruta, []);
    usort($lista, fn($a, $b) => strcasecmp($a['razon'], $b['razon']));
    return $lista;
}

/** Agrega o actualiza el cliente en la libreta (se identifica por RUT o nombre). */
function cot_recordar_cliente(array $c): void
{
    $lista = cot_clientes();
    $clave = static fn($x) => $x['rut'] !== '' ? 'rut:' . preg_replace('~[^\dkK]~', '', $x['rut'])
                                                  : 'razon:' . mb_strtolower($x['razon']);
    $encontrado = false;
    foreach ($lista as &$x) {
        if ($clave($x) === $clave($c)) {
            $x = $c;
            $encontrado = true;
        }
    }
    unset($x);
    if (!$encontrado) {
        $lista[] = $c;
    }
    cot_escribir_json(cot_dir() . '/clientes.json', $lista);
}

// ---------------------------------------------------------------------------
// Fotos del anexo
// ---------------------------------------------------------------------------

function cot_dir_fotos(string $numero): string
{
    if (!cot_numero_valido($numero)) {
        throw new InvalidArgumentException('Número inválido');
    }
    return cot_dir('fotos/' . $numero);
}

function cot_nombre_foto_valido(string $f): bool
{
    return (bool) preg_match('~^[a-f0-9]{16}\.(jpg|png)$~', $f);
}

/**
 * Guarda una foto subida. Se verifica que sea de verdad una imagen mirando su
 * contenido, no su nombre ni lo que dice el navegador.
 */
function cot_agregar_foto(string $numero, array $archivo): array
{
    global $COT;

    $c = cot_cargar($numero);
    if ($c === null) {
        throw new InvalidArgumentException('Esa cotización no existe.');
    }
    if (count($c['fotos']) >= $COT['max_fotos']) {
        throw new InvalidArgumentException('Se alcanzó el máximo de ' . $COT['max_fotos'] . ' fotos.');
    }
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'])) {
        throw new InvalidArgumentException('La foto no llegó completa. Prueba de nuevo.');
    }
    if ($archivo['size'] > 8 * 1024 * 1024) {
        throw new InvalidArgumentException('La foto pesa demasiado.');
    }

    $info = @getimagesize($archivo['tmp_name']);
    $ext  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png'][$info[2] ?? 0] ?? null;
    if ($ext === null) {
        throw new InvalidArgumentException('Sólo se aceptan fotos JPG o PNG.');
    }

    $nombre = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($archivo['tmp_name'], cot_dir_fotos($numero) . '/' . $nombre)) {
        throw new RuntimeException('No se pudo guardar la foto.');
    }

    $c['fotos'][] = ['archivo' => $nombre, 'ancho' => $info[0], 'alto' => $info[1]];
    $c['modificada'] = date('c');
    cot_escribir_json(cot_ruta($numero), $c);
    return $c;
}

function cot_quitar_foto(string $numero, string $nombre): array
{
    $c = cot_cargar($numero);
    if ($c === null || !cot_nombre_foto_valido($nombre)) {
        throw new InvalidArgumentException('Foto inválida.');
    }
    $c['fotos'] = array_values(array_filter($c['fotos'], fn($f) => $f['archivo'] !== $nombre));
    @unlink(cot_dir_fotos($numero) . '/' . $nombre);
    cot_escribir_json(cot_ruta($numero), $c);
    return $c;
}

/** Copia las fotos de una cotización a otra (al duplicar). */
function cot_copiar_fotos(string $desde, string $hacia): void
{
    $origen = cot_cargar($desde);
    $dest   = cot_cargar($hacia);
    if ($origen === null || $dest === null) {
        return;
    }
    foreach ($origen['fotos'] as $f) {
        $nuevo = bin2hex(random_bytes(8)) . '.' . pathinfo($f['archivo'], PATHINFO_EXTENSION);
        if (@copy(cot_dir_fotos($desde) . '/' . $f['archivo'], cot_dir_fotos($hacia) . '/' . $nuevo)) {
            $dest['fotos'][] = ['archivo' => $nuevo, 'ancho' => $f['ancho'], 'alto' => $f['alto']];
        }
    }
    cot_escribir_json(cot_ruta($hacia), $dest);
}

// ---------------------------------------------------------------------------
// Respuestas
// ---------------------------------------------------------------------------

function cot_json(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** $ 2.618.000 */
function cot_pesos(int $n): string
{
    return '$ ' . number_format($n, 0, ',', '.');
}

/** «30 de octubre de 2026» */
function cot_fecha_larga(string $iso): string
{
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    [$a, $m, $d] = array_map('intval', explode('-', $iso));
    return $d . ' de ' . $meses[$m - 1] . ' de ' . $a;
}

/** Cabeceras comunes de todas las páginas del cotizador. */
function cot_cabeceras_privadas(): void
{
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store, private');
    header('Referrer-Policy: same-origin');
}
