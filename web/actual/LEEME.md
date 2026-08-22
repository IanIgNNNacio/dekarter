# Snapshot del sitio en producción

Esta carpeta guarda una copia **exacta e intacta** de lo que hay hoy en
`public_html/` del hosting de HostGator. Es la línea base: no se edita nunca.
Si el rediseño sale mal, esto es lo que se restaura.

## Cómo llenarla

FileZilla → sitio guardado **Dekarter** → menú *Servidor → Forzar mostrar
archivos ocultos* → entrar a `public_html/` en el panel remoto → seleccionar
todo y arrastrar a esta carpeta.

Sólo `public_html/`. No traer `mail/`, `ssl/`, `.htpasswds/`, `.cpanel/`,
`etc/`, `logs/` ni `.trash/`: contienen correo, claves privadas del
certificado y hashes de contraseñas.

## Qué se espera encontrar (verificado por HTTP el 2026-08-21)

| Archivo | Notas |
|---|---|
| `index.php` | One-pager completo. Bootstrap 5.3 por CDN. El `<form>` postea a `#contacto`, así que el handler tiene que estar en PHP dentro de este archivo. |
| `contacto.php` | Existe: responde 409 con el desafío `humans_21909` de HostGator, no 404. Es el envío del formulario. |
| `.htaccess` | Contenido desconocido — devuelve 403 por HTTP, como corresponde. |
| `images/logo2.png` | 1024 × 339 px · 113 KB |
| `images/1.jpeg` … `4.jpeg` | Carrusel. 359 / 158 / 272 / 138 KB |
| `images/lh410.png` | **2.710.951 bytes.** El mayor problema de peso del sitio. |
| `images/dd321.png` | 205 KB |

## Antes del primer commit

Revisar `index.php` y `contacto.php` en busca de credenciales en texto claro
(contraseñas SMTP, claves de API). Si aparecen, se sacan del archivo y se
reemplazan por una referencia a `config.local.php`, que está en `.gitignore`.
