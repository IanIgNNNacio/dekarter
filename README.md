# Dekarter

Repositorio de todo lo de **Maestranza Dekarter SPA** (RUT 76.102.503-1), cliente
de maestranza para minería en Conchalí, Santiago.

La carpeta principal queda libre a propósito: `web/` es sólo el sitio, y lo demás
(correos, documentos, migraciones) va en carpetas hermanas.

```
web/
├── actual/                     Copia intacta de lo que hay en producción hoy
│   └── public_html/            No se edita nunca. Es el punto de retorno.
├── nuevo/                      El rediseño
└── htaccess-raiz-produccion.txt   Arreglo listo para el sitio actual, sin instalar
```

---

## El sitio actual

`dekarter.cl` en HostGator. Una sola página, `public_html/index.php`, con
Bootstrap 5.3 desde un CDN. El formulario **funciona**: hay un bloque PHP al
inicio de `index.php` que manda un `mail()` a `ventas@dekarter.cl`.

Estado medido el 21-08-2026:

| Problema | Detalle |
|---|---|
| **Inyección de cabeceras en el formulario** | `$headers = "From: $email"` con el POST sin validar. Un `\r\n` en el campo correo permite agregar un `Bcc:` y usar el formulario para enviar spam desde el dominio. |
| Peso de la portada | 3.985 KB. `images/lh410.png` sola pesa 2.710.951 bytes. |
| Sin `robots.txt`, sin `sitemap.xml`, sin favicon | Los tres devuelven 404. |
| Sin meta description, sin `<h1>`, sin canonical, sin Open Graph, sin datos estructurados | Nada. |
| Cuatro direcciones válidas | `http`/`https` × con/sin `www`, todas 200, sin redirección. Contenido duplicado y formulario enviable sin cifrar. |
| Sin cabeceras de caché | Las imágenes se descargan de nuevo en cada visita. |
| `contacto.php` huérfano | Duplica el handler pero apunta a `test@dekarter.cl`. Nadie lee ese buzón. |
| Archivos de trabajo públicos | `images/engranajes.psd` (20 MB) y `dekarter-logo.psd` se pueden descargar desde el navegador. |
| Fotos de terceros | `lh410.png` y `dd321.png` son fotos de prensa de Sandvik, con su marca visible. |

En `public_html/error_log` se ven POST de robots al formulario cada pocas horas,
sin ningún campo relleno.

---

## El sitio nuevo

PHP plano, sin framework ni dependencias. Nueve páginas, un CSS escrito a mano
(19 KB) y 2 KB de JavaScript. Todo dato de la empresa vive en un solo archivo:
`web/nuevo/inc/config.php`.

```
nuevo/
├── index.php  mecanizados.php  centros-de-mecanizado.php
├── repuestos-a-medida.php  sandvik-lh410.php  sandvik-dd321.php
├── nosotros.php  contacto.php  gracias.php  404.php
├── sitemap.php                 Se genera desde config.php: no se desincroniza
├── robots.txt  .htaccess
├── inc/
│   ├── config.php              Datos de la empresa, servicios, equipos, ayudantes
│   ├── head.php                <head>, Open Graph y datos estructurados
│   ├── header.php  footer.php  Barra superior, migas de pan y pie
│   ├── antispam.php            Las cinco capas de defensa del formulario
│   ├── proceso-contacto.php    Procesa el POST y manda el correo
│   ├── bloque-contacto.php     Formulario + datos + mapa (portada y contacto)
│   └── otros-servicios.php     Enlazado interno entre páginas
├── assets/{css,js,img}/
└── var/                        Estado del servidor. No se versiona.
```

### Convención que hay que respetar

Los `include` de PHP **comparten el ámbito de variables** con la página que los
llama. Por eso los bucles dentro de `inc/` usan variables con prefijo
(`$nav_s`, `$pie_q`, `$sel_q`) y no `$s` ni `$q`. Sin el prefijo, el bucle del
menú le pisa a la página el servicio que está mostrando y el `<h1>` sale con el
texto de otra página. Ya pasó una vez.

### Formulario de contacto

Cinco capas, todas del lado del servidor, sin CAPTCHA ni servicios externos:

1. **Campo trampa** invisible para personas.
2. **Token firmado con HMAC**: sin él el POST no se procesa. Mata el envío desde
   un script que nunca cargó la página.
3. **Ventana de tiempo**: menos de 4 segundos es un robot; más de 3 horas está
   vencido.
4. **Límite por IP**: 5 envíos por hora. Se anota sólo cuando el correo sale, no
   en cada intento, para no bloquear a alguien que se equivocó escribiendo.
5. **Heurística de contenido**: exceso de enlaces, alfabetos que no corresponden
   y el catálogo clásico del spam de posicionamiento.

Cuando detecta un robot muestra la página de gracias sin enviar nada: si le
devolviera un error, el operador del bot ajustaría el script hasta pasar.

El remitente es `web@dekarter.cl` y el correo del visitante va en `Reply-To`.
Al revés —el visitante en `From:`— el mensaje se va a spam porque el SPF del
dominio ajeno no autoriza al servidor de HostGator. Todo lo que va a una
cabecera pasa por `dk_limpia_cabecera()`, que elimina saltos de línea: es la
inyección que tiene hoy el sitio en producción.

Si `mail()` falla, el contacto se guarda en `var/contactos-no-enviados.txt`.
Nunca se pierde un cliente por un problema de correo.

---

## Probar en local

Necesita PHP en el PATH (acá está el de XAMPP, 8.2).

```bash
php -S localhost:8123 -t F:/saile/clientes/dekarter/web
```

Y abrir <http://localhost:8123/nuevo/>.

El servidor embebido de PHP no lee `.htaccess`, así que las URLs sin extensión
(`/nuevo/contacto`) no funcionan sin un enrutador. Con enrutador:

```bash
php -S localhost:8123 -t F:/saile/clientes/dekarter/web router.php
```

---

## Publicar

### 1. Subir el borrador (no toca el sitio actual)

Por FileZilla, crear `public_html/nuevo/` y subir todo el contenido de
`web/nuevo/`. La carpeta `var/` debe quedar con permiso de escritura (755 o 775);
si no, el formulario no puede guardar su clave de firma.

`inc/config.php` viene con `DK_BASE = '/nuevo/'` y `DK_BORRADOR = true`, así que
las páginas salen con `noindex` y Google no indexa el borrador.

### 2. Arreglar el sitio actual mientras se revisa (opcional, reversible)

Renombrar `web/htaccess-raiz-produccion.txt` a `.htaccess` y subirlo a
`public_html/`. Fuerza HTTPS, unifica las cuatro direcciones en una, agrega
caché y bloquea la descarga de los `.psd`. Para revertir: borrar el archivo.

### 3. Pasar el rediseño a la raíz

Ya no hay que editar nada antes de subir. `DK_BASE` se deduce de la ruta del
archivo que se ejecuta, y `DK_BORRADOR` es simplemente «no estoy en la raíz»:
los mismos archivos funcionan en `/nuevo/` y en `/`.

El `.htaccess` de `web/nuevo/` **ya es la versión de raíz**: hace la
canonicalización (HTTPS y sin `www`) y las URLs limpias en un solo archivo, y
reemplaza a los dos que había separados.

Pasos detallados en [`web/PASAR-A-RAIZ.md`](web/PASAR-A-RAIZ.md). En resumen:
mover los cinco archivos viejos fuera de `public_html`, subir el contenido de
`web/nuevo/` a la raíz, dejar `var/` en 755, borrar `public_html/nuevo/` y
declarar el sitemap en Search Console.

La copia en `web/actual/` sigue siendo el punto de retorno.
