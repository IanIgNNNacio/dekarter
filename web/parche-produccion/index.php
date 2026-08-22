<?php
/**
 * Formulario de contacto — version endurecida.
 *
 * Parche del 22-08-2026. Cambia solo la logica del formulario; el HTML del
 * sitio queda identico. Es temporal: cuando el rediseno pase a la raiz, este
 * archivo se reemplaza completo.
 *
 * Que arregla, en orden de gravedad:
 *
 * 1. INYECCION DE CABECERAS. Antes: $headers = "From: $email", con el POST sin
 *    validar. Un salto de linea dentro del campo correo permitia agregar
 *    cabeceras propias —por ejemplo un "Bcc:" con cien direcciones— y el correo
 *    salia desde el servidor de dekarter.cl. Asi es como un dominio termina en
 *    listas negras y despues los correos legitimos de ventas@ caen en spam.
 *
 * 2. SPAM. No habia ninguna defensa. Se agregan un campo trampa invisible y
 *    controles de contenido. El spam de mostbet que llego el 22-08 traia
 *    cirilico y una etiqueta <a href>: cualquiera de las dos reglas lo descarta.
 *
 * 3. TILDES ROTAS. Sin cabecera de juego de caracteres, los correos llegaban
 *    con "TelAcfono" en vez de "Telefono". Ahora se declara UTF-8.
 *
 * 4. AVISOS DE PHP. Los robots postean sin ningun campo, y el error_log estaba
 *    lleno de "Undefined array key". Con ?? '' se termina.
 *
 * 5. REMITENTE. Pasa a web@dekarter.cl en vez del correo del visitante, que va
 *    en Reply-To. Poner un dominio ajeno en From hace que el servidor de
 *    destino vea una IP que su SPF no autoriza y mande el mensaje a spam.
 */

$mensajeEnviado = false;
$mensajeError   = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $nombre   = trim((string) ($_POST['nombre']    ?? ''));
    $email    = trim((string) ($_POST['email']     ?? ''));
    $telefono = trim((string) ($_POST['telefono']  ?? ''));
    $mensaje  = trim((string) ($_POST['mensaje']   ?? ''));
    $trampa   = trim((string) ($_POST['sitio_web'] ?? ''));

    // Quita saltos de linea de todo lo que va a una cabecera del correo.
    // Se eliminan, no se escapan: es el arreglo del punto 1.
    $limpia = static function (string $valor): string {
        $valor = str_replace(["\r", "\n", "\0", '%0a', '%0d'], ' ', $valor);
        return trim(preg_replace('~\s+~u', ' ', $valor) ?? '');
    };

    $todo = $nombre . ' ' . $mensaje;
    $sospechoso = false;

    // Campo trampa: invisible para personas, irresistible para robots.
    if ($trampa !== '') {
        $sospechoso = true;
    }
    // Cirilico y CJK: no corresponden a un cliente de una maestranza chilena.
    if (preg_match('~[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}]~u', $todo)) {
        $sospechoso = true;
    }
    // Etiquetas de enlace: nadie las escribe a mano en un formulario.
    if (preg_match('~<\s*a\s|\[url|\[link~i', $todo)) {
        $sospechoso = true;
    }
    // Mas de dos enlaces en una consulta de cotizacion no tiene sentido.
    if (preg_match_all('~https?://|www\.~i', $todo) > 2) {
        $sospechoso = true;
    }
    // Catalogo clasico del spam de posicionamiento y apuestas.
    $senuelos = ['seo', 'backlink', 'link building', 'guest post', 'casino',
                 'mostbet', '1xbet', 'betting', 'viagra', 'cialis', 'crypto',
                 'bitcoin', 'forex', 'make money', 'rank your website'];
    foreach ($senuelos as $senuelo) {
        if (mb_stripos($todo, $senuelo) !== false) {
            $sospechoso = true;
            break;
        }
    }

    if ($sospechoso) {
        // Se le muestra el "gracias" y no se envia nada. Que crea que funciono:
        // si le devolvieramos un error, ajustaria el script hasta pasar.
        $mensajeEnviado = true;

    } elseif ($nombre === '' || mb_strlen($mensaje) < 5 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensajeError = 'Revisa los datos: falta el nombre o el mensaje, o el correo esta mal escrito.';

    } else {
        $de   = 'web@dekarter.cl';
        $para = 'ventas@dekarter.cl';

        $asunto = 'Nuevo mensaje desde la web';

        $contenido = "Nombre:   " . $nombre . "\n"
            . "Correo:   " . $email . "\n"
            . "Telefono: " . $telefono . "\n\n"
            . "Mensaje:\n" . $mensaje . "\n";

        // El nombre visible se entrecomilla y se le quitan los caracteres que
        // tienen significado propio dentro de una cabecera. Quitar los saltos
        // de linea ya evita la inyeccion; esto es para que la cabecera quede
        // bien formada y no como 'Atacante Bcc: alguien@x.cl' suelto.
        $nombreCabecera = '"' . str_replace(
            array('"', '<', '>', '\\', ':', ';', ','), ' ', $limpia($nombre)
        ) . '"';

        $cabeceras = implode("\r\n", [
            'From: Web Dekarter <' . $de . '>',
            'Reply-To: ' . $nombreCabecera . ' <' . $limpia($email) . '>',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ]);

        $asuntoMime = '=?UTF-8?B?' . base64_encode($asunto) . '?=';

        if (@mail($para, $asuntoMime, $contenido, $cabeceras, '-f' . $de)
            || @mail($para, $asuntoMime, $contenido, $cabeceras)) {
            $mensajeEnviado = true;
        } else {
            $mensajeError = 'No pudimos enviar el mensaje. Escribenos a ventas@dekarter.cl '
                . 'o al +56 9 3903 9509.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Maestranza DEKARTER</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    #carouselContainer {
      max-width: 1300px;
    }

    #carouselExampleIndicators {
      width: 100%;
      height: 500px;
      overflow: hidden;
    }

    .carousel-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: bottom;
    }

    .card-hover {
      position: relative;
      overflow: hidden;
      cursor: pointer;
    }

    .card-hover img {
      transition: transform 0.3s ease;
    }

    .card-hover:hover img {
      transform: scale(1.1);
    }

    .card-hover .overlay {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background: rgba(0, 0, 0, 0.6);
      color: #fff;
      opacity: 0;
      transition: opacity 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 1rem;
    }

    .card-hover:hover .overlay {
      opacity: 1;
    }

    .navbar-nav .nav-link {
      transition: background-color 0.3s, color 0.3s;
      padding: 8px 16px;
      border-radius: 5px;
    }

    .navbar-nav .nav-link:hover {
      background-color: #000000; /* Color de fondo al hacer hover */
      color: #ffffff !important; /* Cambia el color del texto si deseas */
    }

    body {
      padding-top: 120px;
    }

    /* ------ MEDIA QUERIES PARA RESPONSIVIDAD ------ */

    @media (max-width: 767px) {
      #carouselExampleIndicators {
        height: 250px;
      }

      body {
        padding-top: 90px; /* navbar más pequeña en móviles */
      }

      .card-hover {
        width: 100% !important;
      }

      .card-hover img {
        height: auto;
      }
    }

    @media (max-width: 480px) {
      #carouselExampleIndicators {
        height: 200px;
      }

      .navbar-brand img {
        height: 60px; /* Logo más chico */
      }
    }
  </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="images/logo2.png" alt="Maestranza Minera" height="100">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link text-dark" href="#inicio">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-dark" href="#servicios">Servicios</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-dark" href="#nosotros">Nosotros</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-dark" href="#contacto">Contacto</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
<!-- Navbar -->

<!-- Carousel -->
  <div class="container mt-4">
    <div id="carouselContainer">
      <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
          <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"></li>
          <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"></li>
          <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"></li>
          <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3"></li>
        </div>
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="images/1.jpeg" class="d-block" alt="Primera slide">
          </div>
          <div class="carousel-item">
            <img src="images/2.jpeg" class="d-block" alt="Segunda slide">
          </div>
          <div class="carousel-item">
            <img src="images/3.jpeg" class="d-block" alt="Tercera slide">
          </div>
          <div class="carousel-item">
            <img src="images/4.jpeg" class="d-block" alt="Cuarta slide">
          </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Anterior</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Siguiente</span>
        </a>
      </div>
    </div>
</div>
<!-- Fin Carousel -->

<!-- Servicios -->
<section id="servicios" class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-5">Nuestros Servicios</h2>
      <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card card-hover" style="width: 420px;">
                <img src="images/lh410.png" class="card-img-top mx-auto d-block" alt="lh410">
                <div class="overlay">
                <p class="mb-0">Fabricación de pasadores y bujes.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-hover" style="width: 420px;">
                <img src="images/dd321.png" class="card-img-top mx-auto d-block" alt="dd321">
                <div class="overlay">
                  <p class="mb-0">Reparación y fabricación de componentes para brazos de Perforación.</p>
                </div>
              </div>
        </div>
      </div>
  </section>
<!-- Servicios -->

<!-- Nosotros/contacto -->
  <section id="contacto" class="py-5">
    <div class="container">
      <div class="row">
        <!-- Columna Nosotros -->
        <div class="col-md-6 mb-4 mb-md-0">
          <h2 class="text-center text-md-start mb-4">Nosotros</h2>
          <p class="text-center text-md-start">
           En Dekarter, somos una maestranza especializada en la fabricación y desarrollo de proyectos industriales. Con más de 25 años de experiencia en el rubro, hemos trabajado con dedicación y compromiso para entregar soluciones a medida, adaptándonos a las necesidades de cada cliente. Nuestra trayectoria nos respalda, y nuestro enfoque está puesto en ofrecer un servicio personalizado, eficiente y con los más altos estándares de calidad.
          </p>
        </div>
  
        <!-- Columna Contacto -->
    
        <div class="col-md-6">
          <h2 class="text-center text-md-start mb-4">Contacto</h2> <!-- texto centrado en móvil, a la izquierda en desktop -->

          <form method="POST" action="#contacto" class="mb-4">

            <!-- Campo trampa. Invisible para las personas: si viene con algo
                 escrito, al otro lado hay un robot. Va con estilo en linea para
                 no tocar el CSS del sitio. -->
            <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden" aria-hidden="true">
              <label for="sitio_web">No escribas nada aqui</label>
              <input type="text" name="sitio_web" id="sitio_web" tabindex="-1" autocomplete="off">
            </div>
 <!-- margen inferior general -->
            <div class="mb-3">
              <label for="nombre" class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" id="nombre" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Correo electrónico</label>
              <input type="email" name="email" class="form-control" id="email" required>
            </div>
            <div class="mb-3">
              <label for="telefono" class="form-label">Teléfono (ej: 9-12345678)</label>
              <input type="tel" name="telefono" class="form-control" id="telefono" required>
            </div>
            <div class="mb-3">
              <label for="mensaje" class="form-label">Mensaje</label>
              <textarea name="mensaje" class="form-control" id="mensaje" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-dark w-100">Enviar Mensaje</button>
          </form>

          <?php if ($mensajeEnviado): ?>
            <div class="alert alert-success" role="alert">
              ¡Gracias! Tu mensaje fue enviado correctamente.
            </div>
          <?php endif; ?>

          <?php if ($mensajeError !== ''): ?>
            <div class="alert alert-danger" role="alert">
              <?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </section>
<!-- Nosotros/contacto -->

<!-- Clientes -->
<section class="py-5 bg-light" id="clientes">
  <div class="container">
    <h2 class="text-center mb-5">Donde nos ubicamos</h2>
    <div class="row justify-content-center align-items-center g-4">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3331.5932901318624!2d-70.6816554879468!3d-33.38168259388429!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9662c6f247a23f8d%3A0xc691cd84b14584d0!2sDos%20Sur%205010%2C%208561308%20Conchal%C3%AD%2C%20Regi%C3%B3n%20Metropolitana!5e0!3m2!1ses!2scl!4v1753837767733!5m2!1ses!2scl" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
</section>
<!-- Clientes -->

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4">
    <div class="container">
        <p class="mb-1">Maestranza DEKARTER</p>
        <a href="https://maps.app.goo.gl/bPhbXXPehSQVCGzB8" style="color: inherit; text-decoration: none;" target="_blank"><p class="mb-1">Dirección: Dos Sur 5010, Conchalí, Región Metropolitana</p></a>
        <a href="https://api.whatsapp.com/send/?phone=%2B56939039509&text&type=phone_number&app_absent=0" style="color: inherit; text-decoration: none;" target="_blank"><p class="mb-1">Teléfono: +56 9 3903 9509</p></a>
        <a href="mailto:ventas@dekarter.cl" style="color: inherit; text-decoration: none;"><p class="mb-1">Email: ventas@dekarter.cl</p></a>
        <p class="mb-0">&copy; 2025 Maestranza Dekarter. Todos los derechos reservados.</p>
    </div>
  </footer>
<!-- Footer -->

<!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>