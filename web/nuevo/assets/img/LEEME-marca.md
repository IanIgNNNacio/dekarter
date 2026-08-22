# Archivos de marca — Ruta A · Buje y pasador

El símbolo es el corte de un buje con su pasador montado y, a la vez, la letra D.
Geometría pura: dos formas y un círculo, sin degradados ni efectos. Espesor de
anillo constante (18 unidades sobre un radio exterior de 48).

## Colores exactos

| Rol | HEX | RGB | CMYK aprox. | Uso |
|---|---|---|---|---|
| Azul Dekarter | `#163A7D` | 22 · 58 · 125 | 96 · 78 · 15 · 3 | Anillo, titulares, color dominante |
| Bronce buje | `#A9762F` | 169 · 118 · 47 | 30 · 55 · 100 · 15 | Pasador. Acento único, nunca para texto largo |
| Bronce claro | `#D8A55F` | 216 · 165 · 95 | 15 · 35 · 70 · 2 | Sólo el pasador sobre fondo oscuro |
| Tinta grafito | `#0F1519` | 15 · 21 · 25 | 80 · 65 · 60 · 75 | Versión de un color, texto corrido |

Los CMYK son una conversión de referencia. Antes de mandar a imprenta, pedir la
prueba de color: el azul en offset tiende a oscurecerse.

## Qué archivo usar

| Archivo | Cuándo |
|---|---|
| `marca-dekarter.svg` | Uso general a color sobre fondo claro. |
| `marca-dekarter-negativo.svg` | Fondo oscuro: pie de la web, cartel retroiluminado. |
| `marca-dekarter-1color.svg` | Grabado, sello de goma, bordado, corte de vinilo, guía de despacho en blanco y negro. Hereda el color del contexto (`currentColor`), así que sirve en cualquier tinta única. |
| `favicon.svg` | Pestaña del navegador. Anillo engrosado a propósito para que aguante 16 px. |
| `avatar-dekarter.svg` | WhatsApp Business, Perfil de Empresa en Google, redes. Fondo azul lleno; se recorta bien en círculo. |

## Reglas de uso

- **Área de respeto:** un espacio libre igual al radio del pasador alrededor de todo el símbolo.
- **Tamaño mínimo:** 20 px de alto en pantalla, 8 mm en impresión. Bajo eso, usar `favicon.svg`.
- **Nunca:** agregarle sombra, bisel, degradado o contorno; deformarlo; rotarlo; cambiarle los colores; ponerlo sobre una foto con detalle donde el anillo se pierda.
- **Sobre foto:** usar la versión en negativo y sólo sobre una zona oscura y pareja.

## Pendiente: el logotipo (la palabra)

El texto «DEKARTER / MAESTRANZA» se compone en **Archivo 800** (Google Fonts).
En la web va como texto HTML real —queda nítido, seleccionable y accesible—, así
que el sitio no necesita nada más.

Para imprenta, vinilo y bordado hace falta un archivo con **el texto convertido a
curvas**, para que no dependa de que la tipografía esté instalada. Dos caminos:

1. Descargar la familia Archivo desde `fonts.google.com/specimen/Archivo`, abrir
   el SVG en Inkscape (gratis) o Illustrator y aplicar *Trazado → Objeto a trazado*.
2. Pasarme el archivo `Archivo-ExtraBold.ttf` y lo genero yo.

Mientras eso no exista, no mandar el logo a ninguna imprenta.

## Generar PNG e ICO a partir de estos SVG

Todo sale de los SVG, así que nunca hay que volver a dibujar nada:

```bash
magick -background none marca-dekarter.svg -resize 800x logo-dekarter@2x.png
magick -background none favicon.svg -resize 512x512 avatar-512.png
magick favicon.svg -define icon:auto-resize=48,32,16 favicon.ico
```
