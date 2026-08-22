<?php
/**
 * Procesa el envío del formulario de contacto.
 *
 * Se incluye ANTES de imprimir cualquier cosa, porque en el caso feliz redirige.
 * El patrón es POST → Redirect → GET: así el visitante no reenvía el correo al
 * apretar F5, y el navegador no le pregunta si quiere reenviar el formulario.
 *
 * Deja disponible el arreglo $fc:
 *   $fc['valores'] — lo que escribió, para volver a pintarlo si algo falló
 *   $fc['errores'] — ['campo' => 'mensaje para la persona']
 */

declare(strict_types=1);

require_once __DIR__ . '/antispam.php';

$fc = [
    'valores' => ['nombre' => '', 'empresa' => '', 'email' => '', 'telefono' => '', 'equipo' => '', 'mensaje' => ''],
    'errores' => [],
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['formulario'] ?? '') !== 'contacto') {
    return;
}

// --- Lo que llegó ----------------------------------------------------------

$v = [
    'nombre'   => dk_limpia_cabecera((string) ($_POST['nombre'] ?? ''), 90),
    'empresa'  => dk_limpia_cabecera((string) ($_POST['empresa'] ?? ''), 90),
    'email'    => dk_limpia_cabecera((string) ($_POST['email'] ?? ''), 120),
    'telefono' => dk_limpia_cabecera((string) ($_POST['telefono'] ?? ''), 30),
    'equipo'   => dk_limpia_cabecera((string) ($_POST['equipo'] ?? ''), 40),
    'mensaje'  => dk_limpia_cuerpo((string) ($_POST['mensaje'] ?? '')),
];
$fc['valores'] = $v;

// --- Capas silenciosas ----------------------------------------------------
// Cuando lo que hay al otro lado es un robot, se le muestra la página de
// gracias sin enviar nada. Que crea que funcionó: si le devolviéramos un error,
// el operador del bot ajustaría el script hasta pasar.

$es_robot = false;

// 1. Campo trampa relleno
if (trim((string) ($_POST['sitio_web'] ?? '')) !== '') {
    $es_robot = true;
}

// 2. Token ausente, falsificado o enviado en menos de 4 segundos
$motivo_token = dk_token_invalido((string) ($_POST['ficha'] ?? ''));
if ($motivo_token !== null && $motivo_token !== 'formulario_vencido') {
    $es_robot = true;
}

// 3. Contenido con olor a spam
if (dk_huele_a_spam($v['mensaje'], $v['nombre'] . ' ' . $v['empresa']) !== null) {
    $es_robot = true;
}

if ($es_robot) {
    header('Location: ' . u('gracias'), true, 303);
    exit;
}

// --- Errores que sí se muestran ------------------------------------------

// Una pestaña abierta desde ayer: la persona es real, sólo hay que reintentar.
if ($motivo_token === 'formulario_vencido') {
    $fc['errores']['general'] = 'El formulario estuvo abierto demasiado tiempo y venció por seguridad. Vuelve a apretar «Enviar» y esta vez sí sale.';
}

if (dk_limite_alcanzado()) {
    $fc['errores']['general'] = 'Ya recibimos varios mensajes desde tu conexión en la última hora. Si es urgente, escríbenos directo por WhatsApp al ' . $DK['fono_visible'] . '.';
}

if (mb_strlen($v['nombre']) < 3) {
    $fc['errores']['nombre'] = 'Escribe tu nombre.';
}

if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
    $fc['errores']['email'] = 'Revisa el correo: parece que le falta algo.';
}

$fono = dk_normaliza_fono($v['telefono']);
if ($fono === null) {
    $fc['errores']['telefono'] = 'Escribe el teléfono con los 9 dígitos, por ejemplo 9 1234 5678.';
}

if (mb_strlen($v['mensaje']) < 15) {
    $fc['errores']['mensaje'] = 'Cuéntanos un poco más: qué pieza necesitas, para qué equipo, cuántas unidades.';
}

if ($fc['errores'] !== []) {
    return;
}

// --- Armado del correo ----------------------------------------------------

$asunto = 'Cotización desde la web';
if ($v['equipo'] !== '' && $v['equipo'] !== 'No aplica') {
    $asunto .= ' — ' . $v['equipo'];
}

$cuerpo = "Nuevo mensaje desde el formulario de dekarter.cl\n"
    . str_repeat('-', 52) . "\n\n"
    . 'Nombre:   ' . $v['nombre'] . "\n"
    . 'Empresa:  ' . ($v['empresa'] !== '' ? $v['empresa'] : '(no indicó)') . "\n"
    . 'Correo:   ' . $v['email'] . "\n"
    . 'Teléfono: ' . $fono . "\n"
    . 'Equipo:   ' . ($v['equipo'] !== '' ? $v['equipo'] : '(no indicó)') . "\n\n"
    . "Mensaje:\n" . $v['mensaje'] . "\n\n"
    . str_repeat('-', 52) . "\n"
    . 'Recibido: ' . date('d-m-Y H:i') . " (hora de Chile)\n";

/*
 * El remitente TIENE que ser una dirección del propio dominio. Si se pone el
 * correo del visitante en From:, el servidor de destino ve un dominio ajeno
 * enviado desde una IP que su SPF no autoriza, y el mensaje se va a spam o
 * rebota. El correo del visitante va en Reply-To, que es donde sirve: apretar
 * «Responder» le contesta a él.
 */
$de = 'web@' . preg_replace('~^https?://(www\.)?~', '', DK_DOMINIO);

$cabeceras = [
    'From: Sitio Dekarter <' . $de . '>',
    'Reply-To: ' . $v['nombre'] . ' <' . $v['email'] . '>',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: dekarter.cl',
];

// El 5.º parámetro fija el remitente del sobre (Return-Path). Sin él, Exim usa
// el usuario del sistema y algunos filtros lo castigan.
$enviado = @mail(
    $DK['email'],
    '=?UTF-8?B?' . base64_encode($asunto) . '?=',
    $cuerpo,
    implode("\r\n", $cabeceras),
    '-f' . $de
);

if (!$enviado) {
    // Nunca se pierde un contacto: si el correo falla, queda en disco.
    if (!is_dir(DK_VAR) ) { @mkdir(DK_VAR, 0700, true); }
    @file_put_contents(
        DK_VAR . '/contactos-no-enviados.txt',
        "\n===== " . date('c') . " =====\n" . $cuerpo,
        FILE_APPEND | LOCK_EX
    );

    $fc['errores']['general'] = 'No pudimos enviar el mensaje por un problema del servidor de correo. '
        . 'Escríbenos por WhatsApp al ' . $DK['fono_visible'] . ' o a ' . $DK['email'] . '.';
    return;
}

dk_anota_envio();

header('Location: ' . u('gracias'), true, 303);
exit;
