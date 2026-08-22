# Cómo subir el borrador — paso a paso

Sube el sitio nuevo a una subcarpeta. **No toca nada del sitio actual:**
`public_html/index.php` sigue siendo lo que ve el público. Si algo sale mal, se
borra la carpeta `nuevo/` y no pasó nada.

---

## 1. Subir los archivos

1. FileZilla → Gestor de sitios → **Dekarter** → Conectar.
2. Panel remoto: entrar a `public_html/`.
3. Crear ahí una carpeta llamada **`nuevo`** (clic derecho → Crear directorio).
4. Menú **Servidor → Forzar mostrar archivos ocultos**. Sin esto el `.htaccess`
   no sube, y sin `.htaccess` las direcciones sin `.php` no funcionan.
5. Panel local: entrar a `F:\saile\clientes\dekarter\web\nuevo\`.
6. Seleccionar **todo el contenido** de esa carpeta —no la carpeta misma— y
   arrastrarlo al panel remoto, dentro de `nuevo/`.

Son 49 archivos, 2,2 MB.

## 2. Permiso de escritura en `var/`

La carpeta `var/` es la única que el sitio necesita escribir: ahí genera la clave
con que firma el formulario y guarda el control de envíos por IP.

En FileZilla, sobre `public_html/nuevo/var` → clic derecho → **Permisos de
archivo** → poner **755**. Si el formulario no funciona, probar 775.

> Si `var/` no es escribible el sitio igual carga y se ve bien, pero el
> formulario rechaza todos los envíos: sin clave no puede validar su propio
> token. Es el primer lugar donde mirar si algo falla.

## 3. Comprobar que quedó bien

Abrir estas tres direcciones:

| Qué probar | Dirección | Qué tiene que pasar |
|---|---|---|
| Que cargue | `https://dekarter.cl/nuevo/` | Se ve la portada nueva |
| Que las URLs limpias andan | `https://dekarter.cl/nuevo/contacto` | Carga la página de contacto. **Si da 404, el `.htaccess` no subió** (volver al paso 4) |
| Que el sitio viejo sigue vivo | `https://dekarter.cl/` | Se ve el sitio de siempre, intacto |

Después, mandar un mensaje de prueba desde el formulario y revisar que llegue a
`ventas@dekarter.cl`. Escribir algo que se distinga, tipo «prueba del sitio
nuevo», y esperar unos segundos antes de apretar Enviar: el formulario descarta
los envíos hechos en menos de 4 segundos porque eso es comportamiento de robot.

---

## Las rutas que quedan

Todas bajo `https://dekarter.cl/nuevo/`.

| Página | Dirección | Archivo |
|---|---|---|
| Portada | `/nuevo/` | `index.php` |
| Mecanizados | `/nuevo/mecanizados` | `mecanizados.php` |
| Centros de mecanizado | `/nuevo/centros-de-mecanizado` | `centros-de-mecanizado.php` |
| Repuestos a medida | `/nuevo/repuestos-a-medida` | `repuestos-a-medida.php` |
| Sandvik LH410 | `/nuevo/sandvik-lh410` | `sandvik-lh410.php` |
| Sandvik DD321 | `/nuevo/sandvik-dd321` | `sandvik-dd321.php` |
| Nosotros | `/nuevo/nosotros` | `nosotros.php` |
| Contacto | `/nuevo/contacto` | `contacto.php` |
| Gracias (post-envío) | `/nuevo/gracias` | `gracias.php` |
| Página 404 | cualquier ruta inválida | `404.php` |

`sitemap.php` responde 404 a propósito mientras el sitio sea borrador: no tiene
sentido anunciarle a Google páginas marcadas como `noindex`.

Las direcciones con `.php` redirigen con un 301 a la versión limpia:
`/nuevo/contacto.php` → `/nuevo/contacto`. Una sola dirección por página.

---

## Para mostrarle al cliente

Manda directo estas cuatro, en este orden:

1. `https://dekarter.cl/nuevo/` — la portada
2. `https://dekarter.cl/nuevo/sandvik-dd321` — la jugada de posicionamiento
3. `https://dekarter.cl/nuevo/repuestos-a-medida` — el servicio que más vende
4. `https://dekarter.cl/nuevo/contacto` — el formulario, para que lo pruebe él

Conviene decirle que el sitio está en `/nuevo/` a propósito, que su web actual
sigue funcionando igual, y que las páginas están marcadas para que Google no las
indexe todavía. Si no, ve la dirección rara y pregunta.

---

## Si algo falla

| Síntoma | Causa casi segura |
|---|---|
| `/nuevo/contacto` da 404 pero `/nuevo/` funciona | El `.htaccess` no subió. Activar «Forzar mostrar archivos ocultos» y subirlo. |
| Todo se ve sin estilos | La carpeta `assets/` quedó incompleta. Revisar que estén `assets/css/dekarter.css` y la carpeta `assets/img/`. |
| El formulario rechaza todo | `var/` sin permiso de escritura. Poner 755 o 775. |
| Página en blanco | Error de PHP oculto. Poner `DK_DEPURAR` en `true` en `inc/config.php`, recargar, leer el error, **y devolverlo a `false`**. |
| Sale «El formulario estuvo abierto demasiado tiempo» | Normal: la pestaña llevaba más de 3 horas abierta. Apretar Enviar de nuevo. |
| Sale «Se envió demasiado rápido» | Normal: pasaron menos de 3 segundos entre abrir la página y enviar. Apretar Enviar otra vez y sale. |

## La bitácora del formulario

Cada envío deja una línea en `public_html/nuevo/var/registro.log`. Se baja por
FileZilla y se lee directo:

```
2026-08-22 09:14:02  enviado                 a1b2c3d4e5f6a7b8  juan@minera.cl
2026-08-22 11:03:55  descartado:trampa       9f8e7d6c5b4a3021  bot@spam.ru
2026-08-22 11:47:10  reintento:muy_rapido    a1b2c3d4e5f6a7b8  pedro@ejemplo.cl
2026-08-22 12:20:31  FALLO:mail              33aa55bb77cc99dd  ana@constructora.cl
```

Qué significa cada resultado:

| Resultado | Qué pasó |
|---|---|
| `enviado` | El correo salió. |
| `enviado:sin_envelope` | Salió, pero al segundo intento. Funciona; el hosting no acepta fijar el Return-Path. |
| `descartado:trampa` | Robot: llenó el campo invisible. |
| `descartado:token_malformado` / `firma_invalida` | Robot: POST directo, nunca cargó la página. |
| `descartado:senuelo:seo` | Spam de posicionamiento. |
| `reintento:muy_rapido` / `reintento:vencido` | Persona real, se le pidió reenviar. |
| `frenado:limite_ip` | Más de 5 envíos en una hora desde la misma conexión. |
| `validacion:email` | Persona real, dejó un campo mal. |
| **`FALLO:mail`** | **El correo no salió.** El mensaje completo queda guardado en `var/contactos-no-enviados.txt`: **ahí está el contacto, no se perdió.** |

Sólo se guarda la fecha, el resultado, un hash de la IP y el correo. El mensaje
no queda en la bitácora.

Si el formulario vuelve a fallar, bájame este archivo y sé exactamente qué pasó
sin tener que adivinar.

Para revertir todo: borrar la carpeta `public_html/nuevo/`. El sitio actual
nunca se tocó.
