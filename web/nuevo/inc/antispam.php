<?php
/**
 * Defensas del formulario de contacto.
 *
 * Cinco capas, todas del lado del servidor y sin depender de ningún servicio
 * externo. No hay CAPTCHA a propósito: un cliente que necesita cotizar un buje
 * a las 7 de la mañana no debería tener que resolver un rompecabezas de
 * semáforos, y para el volumen de spam de un sitio de maestranza estas capas
 * alcanzan de sobra.
 *
 *   1. Campo trampa (honeypot). Invisible para personas, irresistible para bots.
 *   2. Token firmado con HMAC. Sin él el POST no se procesa: mata el envío
 *      directo desde un script que nunca cargó la página.
 *   3. Ventana de tiempo. Menos de 4 segundos es un robot; más de 3 horas es un
 *      formulario rancio.
 *   4. Límite por IP. Máximo 5 envíos por hora.
 *   5. Heurística de contenido. Exceso de enlaces, alfabetos que no
 *      corresponden y el catálogo clásico de spam de posicionamiento.
 *
 * Además se sanea todo lo que va a las cabeceras del correo, que es la vía por
 * la que un formulario mal escrito se convierte en un relay de spam ajeno.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

const DK_ESPERA_MINIMA   = 3;      // segundos
const DK_ESPERA_MAXIMA   = 10800;  // 3 horas
const DK_LIMITE_ENVIOS   = 5;      // por IP
const DK_LIMITE_VENTANA  = 3600;   // segundos
const DK_MAX_ENLACES     = 2;

// ---------------------------------------------------------------------------
// Clave de firma
// ---------------------------------------------------------------------------

/**
 * Clave para el HMAC del token. Se genera sola la primera vez y queda en un
 * archivo fuera del alcance web. No se versiona: cada instalación tiene la suya.
 */
function dk_clave(): string
{
    static $clave = null;
    if ($clave !== null) {
        return $clave;
    }

    $ruta = DK_VAR . '/clave.php';

    if (is_readable($ruta)) {
        $guardada = require $ruta;
        if (is_string($guardada) && strlen($guardada) >= 32) {
            return $clave = $guardada;
        }
    }

    if (!is_dir(DK_VAR)) {
        @mkdir(DK_VAR, 0700, true);
    }

    $nueva = bin2hex(random_bytes(32));
    @file_put_contents($ruta, "<?php return '" . $nueva . "';\n", LOCK_EX);
    @chmod($ruta, 0600);

    return $clave = $nueva;
}

/** Token que viaja escondido en el formulario: momento de emisión + firma. */
function dk_token(): string
{
    $ahora = (string) time();
    return $ahora . '.' . hash_hmac('sha256', $ahora, dk_clave());
}

/**
 * Verifica el token. Devuelve null si está bien, o el motivo del rechazo.
 */
function dk_token_invalido(string $token): ?string
{
    $partes = explode('.', $token, 2);
    if (count($partes) !== 2 || !ctype_digit($partes[0])) {
        return 'token_malformado';
    }

    [$emitido, $firma] = $partes;

    // hash_equals compara en tiempo constante: no filtra información por el
    // tiempo que tarda en fallar.
    if (!hash_equals(hash_hmac('sha256', $emitido, dk_clave()), $firma)) {
        return 'firma_invalida';
    }

    $edad = time() - (int) $emitido;

    if ($edad < DK_ESPERA_MINIMA) {
        return 'demasiado_rapido';
    }
    if ($edad > DK_ESPERA_MAXIMA) {
        return 'formulario_vencido';
    }

    return null;
}

// ---------------------------------------------------------------------------
// Límite por IP
// ---------------------------------------------------------------------------

/** La IP del visitante, sin confiar en cabeceras que cualquiera puede inventar. */
function dk_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/** Identificador estable de la IP, sin guardar la IP misma. */
function dk_huella_ip(): string
{
    return substr(hash_hmac('sha256', dk_ip(), dk_clave()), 0, 16);
}

/**
 * Recorre el registro de envíos descartando los vencidos.
 *
 * $sumar mete un envío nuevo antes de reescribir. Devuelve cuántos envíos
 * vigentes tiene esta IP (sin contar el que se acaba de sumar).
 */
function dk_registro_envios(bool $sumar): int
{
    if (!is_dir(DK_VAR) && !@mkdir(DK_VAR, 0700, true)) {
        return 0;   // sin carpeta de trabajo no se bloquea a nadie
    }

    $mango = @fopen(DK_VAR . '/envios.log', 'c+');
    if ($mango === false) {
        return 0;
    }

    $huella = dk_huella_ip();
    $corte  = time() - DK_LIMITE_VENTANA;
    $propios = 0;

    if (flock($mango, LOCK_EX)) {
        $vigentes = [];

        while (($linea = fgets($mango)) !== false) {
            $campos = explode(' ', trim($linea));
            if (count($campos) !== 2 || (int) $campos[0] < $corte) {
                continue;   // vencido: no se vuelve a escribir
            }
            $vigentes[] = $campos;
            if ($campos[1] === $huella) {
                $propios++;
            }
        }

        if ($sumar) {
            $vigentes[] = [(string) time(), $huella];
        }

        $texto = '';
        foreach ($vigentes as $c) {
            $texto .= $c[0] . ' ' . $c[1] . "\n";
        }

        ftruncate($mango, 0);
        rewind($mango);
        fwrite($mango, $texto);
        fflush($mango);
        flock($mango, LOCK_UN);
    }

    fclose($mango);

    return $propios;
}

/**
 * ¿Esta IP ya mandó demasiados mensajes en la última hora?
 *
 * Sólo consulta, no anota. Es a propósito: si se anotara en cada intento,
 * alguien que se equivoca cinco veces escribiendo su correo quedaría bloqueado
 * sin haber enviado nunca nada. Se anota recién cuando el correo sale.
 */
function dk_limite_alcanzado(): bool
{
    return dk_registro_envios(false) >= DK_LIMITE_ENVIOS;
}

/** Anota un envío que sí salió. */
function dk_anota_envio(): void
{
    dk_registro_envios(true);
}

// ---------------------------------------------------------------------------
// Bitácora
// ---------------------------------------------------------------------------

/**
 * Deja constancia de cómo terminó cada envío.
 *
 * Sin esto, cuando alguien dice «el formulario no funciona» no hay manera de
 * saber si el correo falló, si la validación lo rechazó o si las defensas lo
 * tomaron por robot: los tres casos se ven igual desde el navegador. Con la
 * bitácora, basta leer una línea.
 *
 * Guarda el correo de quien escribió, porque es justamente el dato que se
 * necesita para recuperar un contacto perdido. No guarda el mensaje ni la IP.
 */
function dk_bitacora(string $resultado, string $correo = ''): void
{
    if (!is_dir(DK_VAR) && !@mkdir(DK_VAR, 0700, true)) {
        return;
    }

    $linea = sprintf(
        "%s  %-22s  %s  %s\n",
        date('Y-m-d H:i:s'),
        $resultado,
        dk_huella_ip(),
        $correo
    );

    @file_put_contents(DK_VAR . '/registro.log', $linea, FILE_APPEND | LOCK_EX);
}

// ---------------------------------------------------------------------------
// Heurística de contenido
// ---------------------------------------------------------------------------

/**
 * ¿El texto huele a spam? Devuelve el motivo, o null si pasa.
 *
 * El sesgo es deliberadamente conservador: es mucho peor descartar la
 * cotización de un cliente real que dejar pasar un correo basura al día.
 */
function dk_huele_a_spam(string $mensaje, string $nombre): ?string
{
    $todo = $nombre . ' ' . $mensaje;

    // Más de dos enlaces en un mensaje de cotización no tiene sentido.
    if (preg_match_all('~https?://|www\.~i', $todo) > DK_MAX_ENLACES) {
        return 'demasiados_enlaces';
    }

    // Etiquetas HTML o BBCode: nadie escribe eso a mano en un formulario.
    if (preg_match('~<\s*a\s|\[url|\[link~i', $todo)) {
        return 'marcado_de_enlaces';
    }

    // Alfabetos que no corresponden a un cliente chileno: cirílico y CJK.
    if (preg_match('~[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}]~u', $todo)) {
        return 'alfabeto_inesperado';
    }

    // Catálogo clásico del spam de posicionamiento y estafa.
    $senuelos = [
        'seo', 'backlink', 'link building', 'guest post', 'casino', 'viagra',
        'cialis', 'crypto', 'bitcoin', 'forex', 'binary option', 'loan offer',
        'work from home', 'make money', 'investment opportunity', 'nft',
        'посетить', 'traffic to your website', 'rank your website',
        'first page of google', 'web design services', 'increase your sales',
    ];
    $plano = mb_strtolower($todo, 'UTF-8');
    foreach ($senuelos as $senuelo) {
        if (mb_strpos($plano, $senuelo) !== false) {
            return 'senuelo:' . $senuelo;
        }
    }

    // Un mensaje que es casi todo mayúsculas y largo: casi siempre difusión.
    $letras = preg_replace('~[^\p{L}]~u', '', $mensaje) ?? '';
    if (mb_strlen($letras) > 40) {
        $mayus = preg_replace('~[^\p{Lu}]~u', '', $letras) ?? '';
        if (mb_strlen($mayus) / mb_strlen($letras) > 0.75) {
            return 'todo_mayusculas';
        }
    }

    return null;
}

// ---------------------------------------------------------------------------
// Saneado
// ---------------------------------------------------------------------------

/**
 * Limpia un valor que va a una CABECERA del correo (asunto, nombre, Reply-To).
 *
 * Los saltos de línea son el vector de inyección de cabeceras: con un \r\n
 * metido en el nombre, un atacante agrega un Bcc: y convierte el formulario en
 * un enviador de spam a nombre del dominio. Se eliminan, no se escapan.
 */
function dk_limpia_cabecera(string $valor, int $maximo = 120): string
{
    $valor = str_replace(["\r", "\n", "\t", "\0", '%0a', '%0d'], ' ', $valor);
    $valor = preg_replace('~\s+~u', ' ', $valor) ?? '';

    return mb_substr(trim($valor), 0, $maximo, 'UTF-8');
}

/**
 * Prepara un nombre para el trozo visible de un `Reply-To` o un `From`.
 *
 * Quitar los saltos de línea ya evita la inyección de cabeceras, pero no basta
 * para que la cabecera quede bien formada: los dos puntos, las comillas y los
 * ángulos tienen significado propio ahí. Sin esto, un nombre como
 * `Atacante\r\nBcc: alguien@x.cl` quedaba convertido en el texto
 * `Atacante Bcc: alguien@x.cl` dentro del nombre visible — inofensivo, pero
 * feo y capaz de confundir a algún cliente de correo antiguo.
 *
 * Se limpia y se entrecomilla, que es la forma correcta de encerrar un nombre.
 */
function dk_nombre_cabecera(string $nombre): string
{
    $limpio = dk_limpia_cabecera($nombre, 78);
    $limpio = str_replace(['"', '<', '>', '\\', ':', ';', ','], ' ', $limpio);
    $limpio = trim(preg_replace('~\s+~u', ' ', $limpio) ?? '');

    return '"' . $limpio . '"';
}

/** Limpia un valor que va al CUERPO del correo: sólo se recorta y se normaliza. */
function dk_limpia_cuerpo(string $valor, int $maximo = 4000): string
{
    $valor = str_replace("\0", '', $valor);
    $valor = preg_replace('~\R~u', "\n", $valor) ?? '';

    return mb_substr(trim($valor), 0, $maximo, 'UTF-8');
}

/**
 * Normaliza un teléfono chileno a +56XXXXXXXXX.
 * Devuelve null si no parece un número chileno válido.
 */
function dk_normaliza_fono(string $entrada): ?string
{
    $digitos = preg_replace('~\D~', '', $entrada) ?? '';

    // 56 9 1234 5678  →  ya viene con el código de país
    if (strlen($digitos) === 11 && str_starts_with($digitos, '56')) {
        return '+' . $digitos;
    }
    // 9 1234 5678  →  móvil sin código de país
    if (strlen($digitos) === 9 && $digitos[0] === '9') {
        return '+56' . $digitos;
    }
    // 2 2345 6789  →  fijo con código de área, sin código de país
    if (strlen($digitos) === 9) {
        return '+56' . $digitos;
    }

    // 8 dígitos es ambiguo: falta el código de área o el 9 del móvil. No se
    // adivina — se le pide al visitante que lo complete.
    return null;
}
