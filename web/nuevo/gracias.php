<?php
require_once __DIR__ . '/inc/config.php';

$pagina = [
    'archivo' => 'gracias',
    'title'   => 'Mensaje recibido | Maestranza Dekarter',
    'meta'    => 'Recibimos tu consulta. Te respondemos en horario de taller, de lunes a viernes de 8:30 a 18:30.',
];

// Esta página no aporta nada a Google y no debe aparecer en resultados.
define('DK_SIN_INDICE', true);

require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<section class="banda">
  <div class="contenedor">
    <div class="prosa" style="max-width:56ch">
      <p class="rotulo">Listo</p>
      <h1>Recibimos tu mensaje</h1>
      <p>
        Te vamos a responder al correo y al teléfono que dejaste, en horario de
        taller: <?= e($DK['horario_texto']) ?>.
      </p>
      <p>
        Si tu equipo está detenido y no puedes esperar, escríbenos directo por
        WhatsApp. Ahí contestamos más rápido.
      </p>

      <div class="cierre-acciones" style="margin-top:12px">
        <a class="btn btn--wsp" href="<?= e($DK['whatsapp']) ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M17.5 14.4c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.1-.7.1l-.7.9c-.1.2-.3.2-.6.1a8 8 0 0 1-2.3-1.4 8.7 8.7 0 0 1-1.6-2c-.1-.2-.1-.4.1-.5l.7-.8c.2-.2.2-.4.1-.6l-.9-2.1c-.2-.5-.4-.5-.6-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 1.9 3 4.7 4.1 2.3 1 2.8.8 3.3.7.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3 0-.2-.2-.3-.4-.4zM12 22a10 10 0 0 1-5.1-1.4L2 22l1.4-4.8A10 10 0 1 1 12 22z"/>
          </svg>
          Escribir por WhatsApp
        </a>
        <a class="btn btn--linea" href="<?= e(u()) ?>">Volver a la portada</a>
      </div>

      <dl class="ficha" style="margin-top:34px">
        <div>
          <dt>Teléfono</dt>
          <dd class="dato-mono"><a href="tel:<?= e($DK['fono_e164']) ?>"><?= e($DK['fono_visible']) ?></a></dd>
        </div>
        <div>
          <dt>Correo</dt>
          <dd><a href="mailto:<?= e($DK['email']) ?>"><?= e($DK['email']) ?></a></dd>
        </div>
        <div>
          <dt>Taller</dt>
          <dd><?= e($DK['calle']) ?>, <?= e($DK['comuna']) ?></dd>
        </div>
      </dl>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
