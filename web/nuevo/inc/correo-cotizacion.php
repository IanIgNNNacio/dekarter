<?php
/**
 * El correo que llega a ventas@ cuando alguien escribe por el formulario.
 *
 * Se manda en dos versiones dentro del mismo mensaje (multipart/alternative):
 * HTML para leerlo cómodo, y texto plano para los clientes de correo que no
 * muestran HTML. Tener las dos no es un adorno: un correo sólo-HTML suma
 * puntos en los filtros de spam.
 *
 * El HTML está hecho como se hacen los correos, no como una página web:
 * tablas y estilos en línea. Gmail y Outlook ignoran casi todo el CSS moderno.
 */

declare(strict_types=1);

/**
 * Arma asunto, texto plano y HTML.
 *
 * $d: nombre, empresa, email, fono (E.164), pais (clave de $DK_PAISES),
 *     equipo, mensaje.
 */
function dk_correo_cotizacion(array $d): array
{
    global $DK, $DK_PAISES;

    $pais        = $DK_PAISES[$d['pais']] ?? null;
    $fono_lindo  = dk_formato_fono($d['fono'], $d['pais']);
    $fono_pais   = $pais ? trim($pais['bandera'] . ' ' . $pais['nombre']) : '';
    $equipo      = ($d['equipo'] !== '' && $d['equipo'] !== 'No aplica') ? $d['equipo'] : '';
    $empresa     = $d['empresa'];
    $cuando_dia  = date('d-m-Y');
    $cuando_hora = date('H:i');

    // ---- Asunto: quién y para qué, legible desde la bandeja sin abrirlo ----
    $asunto = 'Cotización web: ' . $d['nombre'];
    if ($empresa !== '') {
        $asunto .= ' (' . $empresa . ')';
    }
    if ($equipo !== '') {
        $asunto .= ' — ' . $equipo;
    }

    // ---- Enlaces de acción ----
    $responder = 'mailto:' . rawurlencode($d['email'])
        . '?subject=' . rawurlencode('Re: Cotización en Maestranza Dekarter');
    $saludo_wsp = 'Hola ' . $d['nombre'] . ', le escribimos de ' . $DK['nombre']
        . ' por la cotización que nos envió desde la web.';
    $whatsapp = 'https://wa.me/' . ltrim($d['fono'], '+') . '?text=' . rawurlencode($saludo_wsp);
    $llamar   = 'tel:' . $d['fono'];

    // ---- Texto plano ----
    $texto = "NUEVA COTIZACIÓN DESDE LA WEB\n"
        . "Recibida el $cuando_dia a las $cuando_hora (hora de Chile)\n\n"
        . "CLIENTE\n"
        . '  Nombre:    ' . $d['nombre'] . "\n"
        . '  Empresa:   ' . ($empresa !== '' ? $empresa : '—') . "\n"
        . '  Correo:    ' . $d['email'] . "\n"
        . '  Teléfono:  ' . $fono_lindo . ($fono_pais !== '' ? "  ($fono_pais)" : '') . "\n"
        . '  Equipo:    ' . ($equipo !== '' ? $equipo : '—') . "\n\n"
        . "MENSAJE\n"
        . preg_replace('~^~m', '  ', $d['mensaje']) . "\n\n"
        . "RESPONDER\n"
        . "  Por correo:   usa «Responder», va directo al cliente.\n"
        . '  Por WhatsApp: ' . 'https://wa.me/' . ltrim($d['fono'], '+') . "\n"
        . '  Por teléfono: ' . $fono_lindo . "\n\n"
        . "--\nFormulario de contacto de dekarter.cl\n";

    // ---- HTML ----
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $azul   = '#0D3857';
    $tinta  = '#16202A';
    $gris   = '#5B6872';
    $linea  = '#E1E6EA';
    $papel  = '#F2F5F7';
    $verde  = '#1F7A54';
    $fuente = "font-family:Arial,Helvetica,sans-serif;";

    $fila = static function (string $rotulo, string $valor_html) use ($gris, $tinta, $linea, $fuente): string {
        return '<tr>'
            . '<td style="' . $fuente . 'padding:10px 0;border-bottom:1px solid ' . $linea . ';width:110px;'
            . 'font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:' . $gris . ';vertical-align:top;">'
            . $rotulo . '</td>'
            . '<td style="' . $fuente . 'padding:10px 0;border-bottom:1px solid ' . $linea . ';'
            . 'font-size:15px;color:' . $tinta . ';font-weight:bold;">' . $valor_html . '</td>'
            . '</tr>';
    };

    $boton = static function (string $url, string $texto_boton, string $fondo) use ($fuente): string {
        return '<td style="padding:0 8px 8px 0;">'
            . '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" style="' . $fuente
            . 'display:inline-block;background:' . $fondo . ';color:#ffffff;text-decoration:none;'
            . 'font-size:14px;font-weight:bold;padding:11px 18px;border-radius:4px;">'
            . $texto_boton . '</a></td>';
    };

    $logo = $h(DK_DOMINIO . '/assets/img/logo-correo.png');
    $previa = $h($d['nombre'] . ($empresa !== '' ? ' · ' . $empresa : '') . ($equipo !== '' ? ' · ' . $equipo : '')
        . ' — ' . mb_substr($d['mensaje'], 0, 90));

    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . $h($asunto) . '</title></head>'
        . '<body style="margin:0;padding:0;background:' . $papel . ';">'
        // Texto de vista previa: lo que Gmail muestra al lado del asunto.
        . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;">' . $previa . '</div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:' . $papel . ';">'
        . '<tr><td align="center" style="padding:24px 12px;">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" '
        . 'style="width:100%;max-width:600px;background:#ffffff;border-radius:6px;overflow:hidden;border:1px solid ' . $linea . ';">'

        // Cabecera
        . '<tr><td style="background:' . $azul . ';padding:18px 24px;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
        . '<td><img src="' . $logo . '" width="190" alt="Maestranza Dekarter SPA" '
        . 'style="display:block;border:0;width:190px;height:auto;"></td>'
        . '<td align="right" style="' . $fuente . 'color:#C9D6E3;font-size:12px;letter-spacing:.08em;text-transform:uppercase;">'
        . 'Cotización web</td>'
        . '</tr></table></td></tr>'

        // Título
        . '<tr><td style="padding:26px 24px 6px;">'
        . '<div style="' . $fuente . 'font-size:22px;font-weight:bold;color:' . $tinta . ';">Nueva cotización</div>'
        . '<div style="' . $fuente . 'font-size:13px;color:' . $gris . ';padding-top:4px;">'
        . 'Llegó por el formulario de dekarter.cl el ' . $cuando_dia . ' a las ' . $cuando_hora . '</div>'
        . ($equipo !== ''
            ? '<div style="padding-top:12px;"><span style="' . $fuente . 'display:inline-block;background:#E8EDF6;color:' . $azul
              . ';font-size:13px;font-weight:bold;padding:5px 12px;border-radius:20px;">Equipo: ' . $h($equipo) . '</span></div>'
            : '')
        . '</td></tr>'

        // Datos del cliente
        . '<tr><td style="padding:12px 24px 4px;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
        . $fila('Nombre', $h($d['nombre']))
        . $fila('Empresa', $empresa !== '' ? $h($empresa) : '<span style="color:' . $gris . ';font-weight:normal;">No indicó</span>')
        . $fila('Correo', '<a href="mailto:' . $h($d['email']) . '" style="color:' . $azul . ';">' . $h($d['email']) . '</a>')
        . $fila('Teléfono', '<a href="' . $h($llamar) . '" style="color:' . $azul . ';text-decoration:none;">'
            . $h($fono_lindo) . '</a>'
            . ($fono_pais !== '' ? ' <span style="color:' . $gris . ';font-weight:normal;font-size:13px;">' . $h($fono_pais) . '</span>' : ''))
        . '</table></td></tr>'

        // Mensaje
        . '<tr><td style="padding:18px 24px 6px;">'
        . '<div style="' . $fuente . 'font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:' . $gris . ';padding-bottom:8px;">Mensaje</div>'
        . '<div style="' . $fuente . 'font-size:15px;line-height:1.55;color:' . $tinta . ';background:' . $papel
        . ';border-left:3px solid ' . $azul . ';padding:14px 16px;border-radius:0 4px 4px 0;">'
        . nl2br($h($d['mensaje'])) . '</div>'
        . '</td></tr>'

        // Acciones
        . '<tr><td style="padding:18px 24px 10px;">'
        . '<div style="' . $fuente . 'font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:' . $gris . ';padding-bottom:10px;">Responder</div>'
        . '<table role="presentation" cellpadding="0" cellspacing="0"><tr>'
        . $boton($responder, 'Responder por correo', $azul)
        . $boton($whatsapp, 'Escribir por WhatsApp', $verde)
        . $boton($llamar, 'Llamar', '#3D4A54')
        . '</tr></table>'
        . '</td></tr>'

        // Pie
        . '<tr><td style="' . $fuente . 'padding:16px 24px 22px;font-size:12px;line-height:1.5;color:' . $gris
        . ';border-top:1px solid ' . $linea . ';">'
        . 'Este aviso lo genera el formulario de contacto de dekarter.cl. Al apretar «Responder» en tu correo, '
        . 'la respuesta va directo al cliente.'
        . '</td></tr>'

        . '</table></td></tr></table></body></html>';

    return ['asunto' => $asunto, 'texto' => $texto, 'html' => $html];
}

/**
 * Envía un correo multipart (texto + HTML).
 *
 * Devuelve true si mail() lo aceptó. Las cabeceras ya vienen saneadas por quien
 * llama: acá no entra nada del visitante sin pasar por dk_limpia_cabecera().
 */
function dk_enviar_multipart(string $destinos, string $de, string $de_nombre,
                             string $responder_a, string $asunto, string $texto, string $html): bool
{
    [$asunto_mime, $cuerpo, $cabeceras] = dk_armar_multipart($de, $de_nombre, $responder_a, $asunto, $texto, $html);

    // Con -f (Return-Path propio) primero; si el hosting lo prohíbe, sin él.
    return @mail($destinos, $asunto_mime, $cuerpo, $cabeceras, '-f' . $de)
        || @mail($destinos, $asunto_mime, $cuerpo, $cabeceras);
}

/**
 * Arma asunto codificado, cuerpo MIME y cabeceras. Separado del envío para
 * poder probar el mensaje completo sin mandar un correo de verdad.
 */
function dk_armar_multipart(string $de, string $de_nombre, string $responder_a,
                            string $asunto, string $texto, string $html): array
{
    $limite = 'dk-' . bin2hex(random_bytes(12));
    $dominio = substr(strrchr($de, '@'), 1);

    $cabeceras = implode("\r\n", [
        'From: ' . $de_nombre . ' <' . $de . '>',
        'Reply-To: ' . $responder_a,
        'Message-ID: <' . time() . '.' . bin2hex(random_bytes(8)) . '@' . $dominio . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $limite . '"',
        'X-Mailer: dekarter.cl',
    ]);

    // base64 en las dos partes: el HTML tiene líneas larguísimas, y el estándar
    // de correo corta en 998 caracteres. Un corte a la mitad de una etiqueta
    // rompe el mensaje, y una línea demasiado larga también suma puntos de spam.
    $cuerpo = "This is a multi-part message in MIME format.\r\n\r\n"
        . '--' . $limite . "\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($texto), 76, "\r\n") . "\r\n"
        . '--' . $limite . "\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html), 76, "\r\n") . "\r\n"
        . '--' . $limite . "--\r\n";

    $asunto_mime = '=?UTF-8?B?' . base64_encode($asunto) . '?=';

    return [$asunto_mime, $cuerpo, $cabeceras];
}
