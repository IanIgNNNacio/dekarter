</main>

<footer class="sitio-pie">
  <div class="contenedor">
    <div class="pie-grilla">

      <div>
        <div class="pie-marca">
          <img src="<?= e(img('marca-dekarter-negativo.svg')) ?>" width="34" height="37"
               alt="" aria-hidden="true">
          <span class="marca-texto">
            <span class="marca-nombre">DEKARTER</span>
            <span class="marca-bajada">Maestranza</span>
          </span>
        </div>
        <p><?= e($DK['descripcion']) ?></p>
      </div>

      <div>
        <h4>Contacto</h4>
        <ul class="pie-lista">
          <li><a href="tel:<?= e($DK['fono_e164']) ?>"><?= e($DK['fono_visible']) ?></a></li>
          <li><a href="mailto:<?= e($DK['email']) ?>"><?= e($DK['email']) ?></a></li>
          <li>
            <a href="<?= e($DK['mapa_url']) ?>" target="_blank" rel="noopener">
              <?= e($DK['calle']) ?>, <?= e($DK['comuna']) ?>
            </a>
          </li>
          <li><?= e($DK['horario_texto']) ?></li>
        </ul>
      </div>

      <div>
        <h4>Servicios</h4>
        <ul class="pie-lista">
          <?php /* Prefijo `pie_` para no pisar variables de la página. */ ?>
          <?php foreach ($DK_SERVICIOS as $pie_s): ?>
            <li><a href="<?= e(u($pie_s['archivo'])) ?>"><?= e($pie_s['nombre']) ?></a></li>
          <?php endforeach; ?>
          <?php foreach ($DK_EQUIPOS as $pie_q): ?>
            <li><a href="<?= e(u($pie_q['archivo'])) ?>"><?= e($pie_q['modelo']) ?></a></li>
          <?php endforeach; ?>
          <li><a href="<?= e(u('nosotros')) ?>">Nosotros</a></li>
        </ul>
      </div>

    </div>

    <div class="pie-legal">
      <span>&copy; <?= date('Y') ?> <?= e($DK['razon_social']) ?></span>
      <span>RUT <?= e($DK['rut']) ?></span>
      <span><?= e($DK['calle']) ?>, <?= e($DK['comuna']) ?>, <?= e($DK['region']) ?></span>
    </div>
  </div>
</footer>

<a class="wsp-flotante" href="<?= e($DK['whatsapp']) ?>" target="_blank" rel="noopener">
  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
    <path d="M17.5 14.4c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.1-.7.1l-.7.9c-.1.2-.3.2-.6.1a8 8 0 0 1-2.3-1.4 8.7 8.7 0 0 1-1.6-2c-.1-.2-.1-.4.1-.5l.7-.8c.2-.2.2-.4.1-.6l-.9-2.1c-.2-.5-.4-.5-.6-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 1.9 3 4.7 4.1 2.3 1 2.8.8 3.3.7.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3 0-.2-.2-.3-.4-.4zM12 22a10 10 0 0 1-5.1-1.4L2 22l1.4-4.8A10 10 0 1 1 12 22z"/>
  </svg>
  <span>Cotizar por WhatsApp</span>
</a>

<script src="<?= e(u('assets/js/dekarter.js')) ?>?v=1" defer></script>
</body>
</html>
