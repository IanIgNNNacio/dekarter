<?php
/**
 * API del cotizador. Todo pasa por acá y todo exige sesión.
 *
 * Las acciones que cambian algo van por POST y exigen el token CSRF en la
 * cabecera X-CSRF: sin él, una página ajena podría hacer que el navegador de
 * alguien con la sesión abierta guardara o borrara cotizaciones.
 *
 *   GET  ?accion=cotizacion&n=3462-26     datos de una cotización
 *   GET  ?accion=foto&n=3462-26&f=...      una foto del anexo
 *   GET  ?accion=clientes                  libreta de clientes
 *   POST ?accion=guardar[&n=...]           crea o actualiza (cuerpo JSON)
 *   POST ?accion=duplicar&n=...            copia como cotización nueva
 *   POST ?accion=subir-foto&n=...          multipart, campo «foto»
 *   POST ?accion=quitar-foto&n=...&f=...
 */

declare(strict_types=1);

require __DIR__ . '/../inc/cotizador.php';

cot_cabeceras_privadas();
cot_exigir_login_api();

$accion = (string) ($_GET['accion'] ?? '');
$n      = (string) ($_GET['n'] ?? '');
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($metodo === 'POST' && !cot_csrf_valido((string) ($_SERVER['HTTP_X_CSRF'] ?? ''))) {
    cot_json(['error' => 'La página quedó desactualizada. Recárgala y vuelve a intentar.'], 403);
}

try {
    switch ($metodo . ' ' . $accion) {

        case 'GET cotizacion':
            $c = cot_cargar($n);
            $c === null ? cot_json(['error' => 'No existe la cotización ' . $n], 404) : cot_json($c);
            break;

        case 'GET empresa':
            cot_json([
                'empresa' => $COT['empresa'],
                'firma'   => $COT['firma'],
                'logo'    => img('logo-dekarter-horizontal.svg'),
            ]);
            break;

        case 'GET clientes':
            cot_json(['clientes' => cot_clientes()]);
            break;

        case 'GET foto':
            $f = (string) ($_GET['f'] ?? '');
            if (!cot_numero_valido($n) || !cot_nombre_foto_valido($f)) {
                cot_json(['error' => 'Foto inválida'], 400);
            }
            $ruta = cot_dir_fotos($n) . '/' . $f;
            if (!is_file($ruta)) {
                cot_json(['error' => 'No existe'], 404);
            }
            header('Content-Type: ' . (str_ends_with($f, '.png') ? 'image/png' : 'image/jpeg'));
            header('Content-Length: ' . filesize($ruta));
            header('Cache-Control: private, max-age=86400');
            readfile($ruta);
            exit;

        case 'POST guardar':
            $entrada = json_decode((string) file_get_contents('php://input'), true);
            if (!is_array($entrada)) {
                cot_json(['error' => 'Datos inválidos'], 400);
            }
            $c = cot_guardar($entrada, $n !== '' ? $n : null);
            cot_json(['ok' => true, 'numero' => $c['numero'], 'cotizacion' => $c]);
            break;

        case 'POST duplicar':
            $origen = cot_cargar($n);
            if ($origen === null) {
                cot_json(['error' => 'No existe la cotización ' . $n], 404);
            }
            $copia = $origen;
            $copia['fecha'] = date('Y-m-d');
            $nueva = cot_guardar($copia, null);
            cot_copiar_fotos($n, $nueva['numero']);
            cot_json(['ok' => true, 'numero' => $nueva['numero']]);
            break;

        case 'POST subir-foto':
            $c = cot_agregar_foto($n, $_FILES['foto'] ?? []);
            cot_json(['ok' => true, 'fotos' => $c['fotos']]);
            break;

        case 'POST quitar-foto':
            $c = cot_quitar_foto($n, (string) ($_GET['f'] ?? ''));
            cot_json(['ok' => true, 'fotos' => $c['fotos']]);
            break;

        default:
            cot_json(['error' => 'Acción desconocida'], 400);
    }
} catch (InvalidArgumentException $e) {
    cot_json(['error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    error_log('cotizador: ' . $e->getMessage());
    cot_json(['error' => 'Error del servidor. No se guardó nada.'], 500);
}
