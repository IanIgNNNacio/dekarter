# Pasar el sitio nuevo a la raíz

Esto reemplaza la web actual por la nueva en `dekarter.cl`. El sitio anterior
queda respaldado, fuera del alcance de los navegadores.

Ya no hay que editar ningún archivo antes de subir: el sitio detecta solo dónde
está instalado. En la raíz se activa como sitio real; en una subcarpeta se pone
en modo borrador con `noindex`. Esa era la parte del traslado con más riesgo.

---

## Antes de empezar

El respaldo completo del sitio actual ya está en este repositorio, en
`web/actual/public_html/`. No depende de nada que pase en el servidor.

El cambio tarda alrededor de un minuto, y durante ese minuto el sitio queda a
medias. Conviene hacerlo fuera del horario de atención.

---

## 1 · Guardar el sitio anterior en el servidor

Los archivos viejos se mueven **fuera de `public_html`**, a la carpeta de arriba.
Ahí siguen en el servidor pero ningún navegador los alcanza.

En FileZilla, panel remoto:

1. Subir un nivel desde `public_html`, hasta ver `public_html`, `mail`, `logs`, etc.
2. Crear ahí una carpeta: **`respaldo-sitio-anterior`**
3. Entrar a `public_html` y **mover** estos cinco archivos a esa carpeta
   (arrastrándolos dentro del panel remoto):

   ```
   index.php
   contacto.php
   indexfinal3.html
   styles.css
   error_log
   ```

> **La carpeta `images/` se deja donde está.** Google tiene indexadas varias de
> esas fotos; si se borran, quedan enlaces rotos. No estorban: el sitio nuevo usa
> `assets/img/` y nunca las toca.

## 2 · Subir el sitio nuevo

1. Menú **Servidor → Forzar mostrar archivos ocultos** (para que suba el `.htaccess`).
2. Panel local: entrar a `F:\saile\clientes\dekarter\web\nuevo\`
3. Seleccionar **todo el contenido** —no la carpeta— y arrastrarlo a `public_html/`
4. Cuando pregunte por archivos que ya existen: **Sobrescribir**

Son 49 archivos, 2,2 MB. Acá es donde el sitio cambia.

## 3 · Permiso de escritura en `var/`

Clic derecho sobre `public_html/var` → **Permisos de archivo** → **755**.

Si el formulario rechaza todos los envíos, es esto: sin poder escribir, el sitio
no puede guardar la clave con la que firma el formulario.

## 4 · Borrar la carpeta de pruebas

Borrar `public_html/nuevo/` completa. Ya no sirve para nada y evita tener dos
copias del mismo sitio.

---

## 5 · Comprobar

| Dirección | Qué tiene que pasar |
|---|---|
| `https://dekarter.cl` | La portada nueva |
| `https://dekarter.cl/contacto` | Carga. **Si da 404, el `.htaccess` no subió** |
| `http://www.dekarter.cl/index.php` | Salta a `https://dekarter.cl/` |
| `https://dekarter.cl/sitemap.xml` | Un XML con 8 direcciones |
| `https://dekarter.cl/no-existe` | La página de error propia, no la de HostGator |
| `https://dekarter.cl/inc/config.php` | 403 |

Y mandar un mensaje de prueba desde el formulario. Esperar unos segundos antes de
apretar Enviar: los envíos hechos en menos de 3 segundos se descartan por ser
comportamiento de robot.

## 6 · Avisarle a Google

Esto es lo que hace que el trabajo sirva de algo. Sin esto, Google puede tardar
meses en darse cuenta de que el sitio cambió.

1. Entrar a **Google Search Console** y verificar el dominio `dekarter.cl`.
2. En *Sitemaps*, agregar: **`sitemap.xml`**
3. En *Inspección de URLs*, pegar `https://dekarter.cl/` y pedir la indexación.
   Repetir con las dos páginas de equipos, que son la apuesta de posicionamiento:
   `https://dekarter.cl/sandvik-lh410` y `https://dekarter.cl/sandvik-dd321`

---

## Si algo sale mal

| Síntoma | Causa y arreglo |
|---|---|
| Error 500 en todo el sitio | Alguna regla del `.htaccess` que el servidor no soporta. **Borrar el `.htaccess`**: el sitio vuelve al instante, sin URLs limpias pero funcionando. |
| `/contacto` da 404 y `/` funciona | El `.htaccess` no subió. Activar «Forzar mostrar archivos ocultos» y subirlo. |
| Todo sin estilos | Falta `assets/`. Revisar que esté completa. |
| El formulario rechaza todo | `var/` sin permiso de escritura. Poner 755. |
| Página en blanco | Poner `DK_DEPURAR` en `true` en `inc/config.php`, recargar, leer el error **y devolverlo a `false`**. |

### Volver atrás del todo

1. Borrar de `public_html` lo que se subió en el paso 2.
2. Devolver los cinco archivos desde `respaldo-sitio-anterior` a `public_html`.
3. Subir `web/actual/public_html/index.php` si hiciera falta.

El sitio anterior vuelve a estar como estaba. Nada de esto es irreversible.
