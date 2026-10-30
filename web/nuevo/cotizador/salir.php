<?php
declare(strict_types=1);

require __DIR__ . '/../inc/cotizador.php';

cot_salir();
header('Location: ' . u('cotizador/'), true, 303);
exit;
