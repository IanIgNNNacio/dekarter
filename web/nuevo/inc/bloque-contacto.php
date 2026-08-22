<?php
/**
 * Bloque de contacto: formulario, datos y mapa. Lo comparten la portada y la
 * página de contacto, así que el formulario es uno solo y se arregla en un
 * solo lugar.
 *
 * Requiere que la página haya incluido antes inc/proceso-contacto.php.
 */

$err = $fc['errores'] ?? [];
$val = $fc['valores'] ?? [];

/** Devuelve el valor escrito, para no hacer que la persona lo tipee de nuevo. */
$antes = static fn(string $campo): string => e($val[$campo] ?? '');

/** Atributos de un campo con error, para que el lector de pantalla lo anuncie. */
$marca = static function (string $campo) use ($err): string {
    return isset($err[$campo])
        ? ' aria-invalid="true" aria-describedby="error-' . $campo . '"'
        : '';
};
?>
<div class="contacto-grilla">

  <div>
    <h2 id="formulario">Pide tu cotización</h2>
    <p class="entrada" style="margin-top:12px">
      Cuéntanos qué pieza necesitas. Si tienes el plano, la pieza de muestra o
      el número de parte, mejor todavía: con eso la cotización sale al primer
      intento.
    </p>

    <?php if (isset($err['general'])): ?>
      <div class="aviso aviso--error" role="alert" style="margin-top:22px">
        <?= e($err['general']) ?>
      </div>
    <?php endif; ?>

    <form class="formulario" method="post"
          action="<?= e(u('contacto')) ?>#formulario"
          data-formulario="contacto" style="margin-top:22px">

      <input type="hidden" name="formulario" value="contacto">
      <input type="hidden" name="ficha" value="<?= e(dk_token()) ?>">

      <!-- Campo trampa: las personas no lo ven, los robots no se resisten. -->
      <div class="trampa" aria-hidden="true">
        <label for="sitio_web">No escribas nada acá</label>
        <input type="text" name="sitio_web" id="sitio_web" tabindex="-1" autocomplete="off">
      </div>

      <div class="campo">
        <label for="nombre">Nombre <span class="obligatorio" aria-hidden="true">*</span></label>
        <input type="text" name="nombre" id="nombre" required autocomplete="name"
               value="<?= $antes('nombre') ?>"<?= $marca('nombre') ?>>
        <?php if (isset($err['nombre'])): ?>
          <span class="ayuda" id="error-nombre" style="color:var(--error)"><?= e($err['nombre']) ?></span>
        <?php endif; ?>
      </div>

      <div class="campo">
        <label for="empresa">Empresa</label>
        <input type="text" name="empresa" id="empresa" autocomplete="organization"
               value="<?= $antes('empresa') ?>">
        <span class="ayuda">Opcional.</span>
      </div>

      <div class="campo">
        <label for="email">Correo <span class="obligatorio" aria-hidden="true">*</span></label>
        <input type="email" name="email" id="email" required autocomplete="email"
               inputmode="email" value="<?= $antes('email') ?>"<?= $marca('email') ?>>
        <?php if (isset($err['email'])): ?>
          <span class="ayuda" id="error-email" style="color:var(--error)"><?= e($err['email']) ?></span>
        <?php endif; ?>
      </div>

      <div class="campo">
        <label for="telefono">Teléfono <span class="obligatorio" aria-hidden="true">*</span></label>
        <input type="tel" name="telefono" id="telefono" required autocomplete="tel"
               inputmode="tel" placeholder="9 1234 5678"
               value="<?= $antes('telefono') ?>"<?= $marca('telefono') ?>>
        <?php if (isset($err['telefono'])): ?>
          <span class="ayuda" id="error-telefono" style="color:var(--error)"><?= e($err['telefono']) ?></span>
        <?php else: ?>
          <span class="ayuda">Para responderte por WhatsApp si es más rápido.</span>
        <?php endif; ?>
      </div>

      <div class="campo">
        <label for="equipo">Equipo</label>
        <select name="equipo" id="equipo">
          <option value="">Selecciona si aplica</option>
          <?php /* Prefijo `sel_` para no pisar variables de la página. */ ?>
          <?php foreach ($DK_EQUIPOS as $sel_q): ?>
            <option value="<?= e($sel_q['modelo']) ?>"<?= ($val['equipo'] ?? '') === $sel_q['modelo'] ? ' selected' : '' ?>>
              <?= e($sel_q['modelo']) ?>
            </option>
          <?php endforeach; ?>
          <option value="Otro equipo"<?= ($val['equipo'] ?? '') === 'Otro equipo' ? ' selected' : '' ?>>Otro equipo</option>
          <option value="No aplica"<?= ($val['equipo'] ?? '') === 'No aplica' ? ' selected' : '' ?>>No aplica</option>
        </select>
      </div>

      <div class="campo">
        <label for="mensaje">Qué necesitas <span class="obligatorio" aria-hidden="true">*</span></label>
        <textarea name="mensaje" id="mensaje" required
                  placeholder="Ej.: necesito 8 bujes de bronce para el brazo de un jumbo. Tengo la pieza original para copiar."
                  <?= $marca('mensaje') ?>><?= $antes('mensaje') ?></textarea>
        <?php if (isset($err['mensaje'])): ?>
          <span class="ayuda" id="error-mensaje" style="color:var(--error)"><?= e($err['mensaje']) ?></span>
        <?php endif; ?>
      </div>

      <button class="btn btn--principal btn--bloque" type="submit">Enviar la consulta</button>

      <p class="legal">
        Los datos se usan sólo para responder tu consulta. No se comparten con
        terceros ni se usan para enviarte publicidad.
      </p>
    </form>
  </div>

  <div>
    <h2>Dónde estamos</h2>

    <dl class="datos" style="margin-top:22px">
      <div class="dato">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        <dt class="dato-rotulo">Taller</dt>
        <dd class="dato-valor">
          <a href="<?= e($DK['mapa_url']) ?>" target="_blank" rel="noopener">
            <?= e($DK['calle']) ?>, <?= e($DK['comuna']) ?>
          </a>
        </dd>
        <dd class="dato-nota"><?= e($DK['region']) ?></dd>
      </div>

      <div class="dato">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.6 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.8.6a2 2 0 0 1 1.7 2z"/>
        </svg>
        <dt class="dato-rotulo">Teléfono y WhatsApp</dt>
        <dd class="dato-valor dato-mono">
          <a href="tel:<?= e($DK['fono_e164']) ?>"><?= e($DK['fono_visible']) ?></a>
        </dd>
      </div>

      <div class="dato">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>
        </svg>
        <dt class="dato-rotulo">Correo</dt>
        <dd class="dato-valor">
          <a href="mailto:<?= e($DK['email']) ?>"><?= e($DK['email']) ?></a>
        </dd>
      </div>

      <div class="dato">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>
        </svg>
        <dt class="dato-rotulo">Horario</dt>
        <dd class="dato-valor" style="font-size:1rem"><?= e($DK['horario_texto']) ?></dd>
      </div>

      <div class="dato">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>
        </svg>
        <dt class="dato-rotulo">Datos de facturación</dt>
        <dd class="dato-valor" style="font-size:1rem"><?= e($DK['razon_social']) ?></dd>
        <dd class="dato-nota dato-mono">RUT <?= e($DK['rut']) ?></dd>
      </div>
    </dl>

    <a class="btn btn--wsp btn--bloque" style="margin-top:24px"
       href="<?= e($DK['whatsapp']) ?>" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M17.5 14.4c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.1-.7.1l-.7.9c-.1.2-.3.2-.6.1a8 8 0 0 1-2.3-1.4 8.7 8.7 0 0 1-1.6-2c-.1-.2-.1-.4.1-.5l.7-.8c.2-.2.2-.4.1-.6l-.9-2.1c-.2-.5-.4-.5-.6-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 1.9 3 4.7 4.1 2.3 1 2.8.8 3.3.7.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3 0-.2-.2-.3-.4-.4zM12 22a10 10 0 0 1-5.1-1.4L2 22l1.4-4.8A10 10 0 1 1 12 22z"/>
      </svg>
      Escribir por WhatsApp
    </a>

    <div class="mapa">
      <button type="button" class="mapa-aviso"
              data-src="https://www.google.com/maps?q=<?= e((string) $DK['lat']) ?>,<?= e((string) $DK['lng']) ?>&hl=es&z=16&output=embed">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        <strong>Ver el mapa</strong>
        <span>Se carga al apretar, para que la página no arrastre el mapa de Google en cada visita.</span>
      </button>
    </div>
  </div>

</div>
