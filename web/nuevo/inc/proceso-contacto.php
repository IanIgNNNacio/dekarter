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
// Cuando al otro lado hay un robot, se le muestra la página de gracias sin
// enviar nada. Que crea que funcionó: si le devolviéramos un error, el operador
// del bot ajustaría el script hasta pasar.
//
// Ojo con qué se descarta en silencio. Descartar callado a una persona real es
// mucho peor que dejar pasar un correo basura: el cliente ve «gracias», se va
// tranquilo, y su consulta no existió nunca. Por eso acá sólo van las señales
// que una persona no puede activar sin querer.

$es_robot = false;
$motivo   = '';

// 1. Campo trampa relleno. Es invisible: nadie lo llena a mano.
if (trim((string) ($_POST['sitio_web'] ?? '')) !== '') {
    $es_robot = true;
    $motivo   = 'trampa';
}

// 2. Token ausente o con firma falsa: el POST no viene de un formulario nuestro.
$motivo_token = dk_token_invalido((string) ($_POST['ficha'] ?? ''));
if ($motivo_token === 'token_malformado' || $motivo_token === 'firma_invalida') {
    $es_robot = true;
    $motivo   = $motivo_token;
}

// 3. Contenido con olor a spam.
$motivo_spam = dk_huele_a_spam($v['mensaje'], $v['nombre'] . ' ' . $v['empresa']);
if ($motivo_spam !== null) {
    $es_robot = true;
    $motivo   = $motivo_spam;
}

if ($es_robot) {
    dk_bitacora('descartado:' . $motivo, $v['email']);
    header('Location: ' . u('gracias'), true, 303);
    exit;
}

// --- Errores que sí se muestran ------------------------------------------

/*
 * Enviado demasiado rápido: ANTES esto se descartaba en silencio, y estaba mal.
 * Con el autocompletado del navegador una persona real llena y envía en dos
 * segundos sin ningún problema, y se quedaba con la página de gracias y sin
 * correo enviado. Ahora se le pide reintentar, que es la verdad.
 *
 * No debilita mucho la defensa: al robot le siguen quedando el campo trampa,
 * la firma del token y la heurística de contenido, que son las capas que de
 * verdad filtran.
 */
if ($motivo_token === 'demasiado_rapido') {
    $fc['errores']['general'] = 'Se envió demasiado rápido y el sistema lo tomó por un robot. Revisa que los datos estén bien y vuelve a apretar «Enviar la consulta»: esta vez sale.';
    dk_bitacora('reintento:muy_rapido', $v['email']);
}

// Una pestaña abierta desde ayer: la persona es real, sólo hay que reintentar.
if ($motivo_token === 'formulario_vencido') {
    $fc['errores']['general'] = 'El formulario estuvo abierto demasiado tiempo y venció por seguridad. Vuelve a apretar «Enviar» y esta vez sí sale.';
    dk_bitacora('reintento:vencido', $v['email']);
}

if (dk_limite_alcanzado()) {
    $fc['errores']['general'] = 'Ya recibimos varios mensajes desde tu conexión en la última hora. Si es urgente, escríbenos directo por WhatsApp al ' . $DK['fono_visible'] . '.';
    dk_bitacora('frenado:limite_ip', $v['email']);
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
    // Sólo los errores de campo: los de arriba ya quedaron anotados.
    $campos = array_diff(array_keys($fc['errores']), ['general']);
    if ($campos !== []) {
        dk_bitacora('validacion:' . implode('+', $campos), $v['email']);
    }
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

// Remitente y destinatarios. El detalle de por qué es cada uno está en
// config.php, junto a los valores.
$de = $DK['email_de'];

$destinos = $DK['email'];
if ($DK['email_copia'] !== '') {
    $destinos .= ', ' . $DK['email_copia'];
}

$cabeceras = [
    'From: ' . $DK['email_de_nombre'] . ' <' . $de . '>',
    'Reply-To: ' . dk_limpia_cabecera($v['nombre']) . ' <' . $v['email'] . '>',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: dekarter.cl',
];

$asunto_mime = '=?UTF-8?B?' . base64_encode($asunto) . '?=';
$cabeceras_txt = implode("\r\n", $cabeceras);

/*
 * El 5.º parámetro fija el remitente del sobre (Return-Path). Ayuda con los
 * filtros, pero hay hosting que lo prohíbe y hace fallar la llamada completa.
 * Se intenta con él y, si falla, se reintenta sin él: vale más un correo
 * entregado que un Return-Path prolijo.
 */
$enviado = @mail($destinos, $asunto_mime, $cuerpo, $cabeceras_txt, '-f' . $de);

if (!$enviado) {
    $enviado = @mail($destinos, $asunto_mime, $cuerpo, $cabeceras_txt);
    if ($enviado) {
        dk_bitacora('enviado:sin_envelope', $v['email']);
    }
}

if (!$enviado) {
    // Nunca se pierde un contacto: si el correo falla, queda en disco.
    if (!is_dir(DK_VAR) ) { @mkdir(DK_VAR, 0700, true); }
    @file_put_contents(
        DK_VAR . '/contactos-no-enviados.txt',
        "\n===== " . date('c') . " =====\n" . $cuerpo,
        FILE_APPEND | LOCK_EX
    );

    dk_bitacora('FALLO:mail', $v['email']);

    $fc['errores']['general'] = 'No pudimos enviar el mensaje por un problema del servidor de correo. '
        . 'Escríbenos por WhatsApp al ' . $DK['fono_visible'] . ' o a ' . $DK['email'] . '.';
    return;
}

dk_anota_envio();
dk_bitacora('enviado', $v['email']);

header('Location: ' . u('gracias'), true, 303);
exit;
