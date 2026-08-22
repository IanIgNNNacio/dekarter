# Para confirmar con Dekarter antes de publicar

Todo el texto del sitio nuevo está escrito sólo con datos verificados. Lo que
sigue son los puntos donde hubo que asumir algo, o donde falta material. Nada de
esto impide mostrar el borrador; sí conviene resolverlo antes de pasarlo a la
raíz del dominio.

## 1. Cosas que asumí y hay que confirmar

| Punto | Lo que dice hoy el sitio | Por qué hay que confirmarlo |
|---|---|---|
| **Días de atención** | «Lunes a viernes, 8:30 a 18:30» | Me pasaste el horario pero no los días. Si abren sábado, hay que corregirlo en `inc/config.php` y en los datos estructurados: es lo que Google muestra en el panel de la empresa. |
| **Años de experiencia** | «Más de 25 años» | Viene del sitio actual. Si el número real es otro, se cambia en un solo lugar (`'anos'` en `config.php`). |
| **Materiales** | «Acero y bronce» | Deducido de las fotos: los bujes de la foto son claramente de bronce y de acero. Si trabajan otros materiales, conviene nombrarlos: son términos que la gente busca. |
| **Facturación** | «Facturamos como Maestranza Dekarter SPA» | Confirmar que ese es el nombre que va en la factura. |

Deliberadamente **no** escribí nada sobre plazos de entrega, tolerancias en
milímetros, certificaciones, lista de máquinas del taller ni nombres de clientes.
Son las afirmaciones que un comprador industrial verifica, y si no se cumplen
hacen daño. Si el cliente quiere sumar alguna, que la dé por escrito.

## 2. Material que falta

- **Foto de un trabajo en un LH410.** Hoy la página del LH410 usa la foto de los
  bujes terminados, que es buena pero genérica. Una foto de una pieza de LH410
  recién mecanizada, o del equipo en el taller, la haría mucho más creíble.
- **Más fotos en general.** Las cuatro que hay son buenas —sobre todo la del
  pallet de bujes de bronce— pero cuatro es poco para un sitio de nueve páginas.
  Ideal: piezas terminadas junto a la pieza gastada que reemplazan (el antes y
  después vende solo), y el taller con gente trabajando.
- **Servicios completos.** Con «mecanizados», «centros de mecanizado» y
  «repuestos a medida» alcanza para partir, pero cada servicio que se pueda
  nombrar es una página más que puede aparecer en Google. Si hacen soldadura,
  rectificado, metalizado o recuperación de ejes, conviene decirlo.

## 3. Lo urgente, que no tiene que ver con el diseño

**El formulario actual se puede usar para enviar spam desde el dominio.**
En `public_html/index.php` línea 16:

```php
$headers = "From: $email";
```

`$email` viene del POST sin ninguna validación. Un salto de línea metido en ese
campo permite agregar cabeceras propias —por ejemplo un `Bcc:` con cien
direcciones— y el correo sale desde el servidor de Dekarter. Además de la
molestia, así es como un dominio termina en las listas negras y después los
correos legítimos de `ventas@` empiezan a caer en spam.

El sitio nuevo ya lo trae resuelto. Si el rediseño se va a demorar en aprobarse,
vale la pena parchar el actual igual.

**Archivos de trabajo descargables.** `dekarter.cl/images/engranajes.psd` son
20 MB de archivo fuente de Photoshop, público. También `dekarter-logo.psd`. El
`.htaccess` que dejé listo (`web/htaccess-raiz-produccion.txt`) los bloquea.

## 4. Fotos de Sandvik: hay que sacarlas

`images/lh410.png` y `images/dd321.png` son fotos de prensa de Sandvik, con el
logo de la marca visible. No son de Dekarter y están publicadas en su sitio.

El riesgo real es bajo, pero es innecesario: las fotos propias son mejores para
vender, porque muestran el trabajo y no el equipo nuevo del catálogo del
fabricante. El sitio nuevo usa sólo fotos propias.

Lo que **sí** se puede hacer sin problema es nombrar los modelos: decir
«fabricamos repuestos para Sandvik LH410» es uso legítimo de la marca para
identificar la aplicación de la pieza, y de hecho es la mejor jugada de
posicionamiento que tiene este sitio. Por eso las dos páginas de equipos llevan
al pie la aclaración de que Dekarter no está afiliada a Sandvik AB.

## 5. Después de publicar: lo que más va a mover la aguja

Por orden de retorno sobre el esfuerzo:

1. **Perfil de Empresa en Google.** Para una maestranza de Conchalí esto rinde
   más que la web entera. Aparece en el mapa cuando alguien busca «maestranza
   cerca», muestra el teléfono con botón de llamar y acumula reseñas. Es gratis y
   toma una tarde. El sitio nuevo ya trae los datos estructurados que Google
   cruza con ese perfil (RUT, dirección, horario, teléfono).
2. **Google Search Console.** Verificar el dominio y enviar el sitemap. Sin esto
   no hay forma de saber qué busca la gente que llega.
3. **Reseñas.** Pedirle a tres o cuatro clientes conocidos que dejen una. Es el
   factor que más pesa en la búsqueda local.
4. **Fotos nuevas cada tanto.** Un sitio que suma trabajos se posiciona mejor que
   uno que queda congelado.
