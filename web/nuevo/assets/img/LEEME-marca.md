# Archivos de marca — logo DK

Logo pedido por Dekarter en octubre de 2026. La imagen original que mandaron
está en `web/marca/fuente-cliente/dk-original-cliente.webp`, y la marca anterior
(buje y pasador) quedó guardada en `web/marca/anterior-buje-y-pasador/`.

## Cómo se vectorizó

La imagen original es un render: textura de concreto de fondo, relleno metálico
y contorno oscuro. No se calcó píxel a píxel —eso da curvas temblorosas—, sino
que se reconstruyó con tipografías de licencia libre (OFL) que calzan con el
original, convertidas a curvas:

- **DK:** Alfa Slab One
- **MAESTRANZA DEKARTER SPA:** Barlow Bold

Todo el texto está en curvas: los archivos se ven igual en cualquier computador,
imprenta o plotter, aunque no tengan las fuentes instaladas. Se pueden mandar a
imprenta tal cual.

Se quitó la textura de concreto: era el fondo de la imagen, no parte del logo.

El contorno se dibuja como dos capas (trazo detrás, relleno encima), no con
`paint-order`, para que Illustrator, Inkscape y las imprentas lo muestren igual.

## Colores

| Rol | HEX |
|---|---|
| Azul del nombre | `#0D3857` (medido en la imagen original) |
| Acero claro / medio / oscuro (degradado) | `#CDD2D7` · `#A4AAB0` · `#7E858C` |
| Acero plano (impresión) | `#A3A9AF` |
| Contorno | `#2B3035` |
| DK en negativo | `#D9DDE1` |

## Qué archivo usar

| Archivo | Cuándo |
|---|---|
| `logo-dekarter.svg` | Versión principal, apilada, con degradado metálico. Pantalla. |
| `logo-dekarter-plano.svg` | Igual, sin degradado. **Imprenta, vinilo de corte, bordado.** |
| `logo-dekarter-negativo.svg` | Fondo oscuro. |
| `logo-dekarter-1color.svg` | Un solo color (`currentColor`). Sello, grabado, fax. Usado como imagen suelta sale negro. |
| `logo-dekarter-horizontal.svg` | Apaisado. Barra superior de la web, membrete, firma de correo. |
| `logo-dekarter-horizontal-negativo.svg` | Apaisado sobre fondo oscuro. Pie de la web. |
| `isotipo-dk.svg` | El DK solo. |
| `favicon.svg` | Pestaña del navegador. DK claro sobre azul, sin contorno fino: a 16 px se empastaría. |
| `avatar-dekarter.svg` | 512 × 512 para WhatsApp Business, Perfil de Empresa en Google y redes. |

## Reglas de uso

- **Tamaño mínimo** del logo apilado: 30 mm de ancho impreso, 120 px en pantalla.
  Más chico, el contorno del DK se pierde: usar el horizontal o el favicon.
- **Área de respeto:** alrededor de todo el logo, un espacio libre igual al
  grosor del trazo vertical de la D.
- **Nunca:** deformarlo, cambiar los colores, agregarle sombras o volver a
  ponerle la textura de concreto.
