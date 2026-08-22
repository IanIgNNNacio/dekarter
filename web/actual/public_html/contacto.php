<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = $_POST["nombre"];
  $email = $_POST["email"];
  $telefono = $_POST["telefono"];
  $mensaje = $_POST["mensaje"];

  $para = "test@dekarter.cl";
  $asunto = "Nuevo mensaje desde la web";
  $contenido = "Nombre: $nombre\n";
  $contenido .= "Correo: $email\n";
  $contenido .= "Teléfono: $telefono\n";
  $contenido .= "Mensaje:\n$mensaje\n";
  $headers = "From: $email";

  if (mail($para, $asunto, $contenido, $headers)) {
    echo "Mensaje enviado correctamente.";
  } else {
    echo "Error al enviar el mensaje. Intenta nuevamente.";
  }
}
?>
