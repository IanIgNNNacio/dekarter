# -*- coding: utf-8 -*-
"""
Pasa el sitio nuevo de Dekarter a la raíz de public_html, por FTP.

Uso:
    python web\\herramientas\\desplegar_raiz.py            # ensayo: muestra qué haría, no toca nada
    python web\\herramientas\\desplegar_raiz.py --aplicar  # lo hace de verdad

Usa la conexión «Dekarter» guardada en FileZilla. La contraseña se lee y se usa
dentro del proceso: no se imprime ni se escribe en ningún lado.

Qué hace, en este orden (pensado para que el sitio quede caído el menor tiempo
posible, del orden de un segundo):

  1. Crea /respaldo-sitio-anterior-AAAAMMDD-HHMM/ FUERA de public_html, donde
     ningún navegador llega.
  2. Mueve ahí los archivos del sitio viejo: index.php, contacto.php,
     indexfinal3.html, styles.css, error_log y el .htaccess.
  3. Sube index.php nuevo primero, y después el resto del sitio.
  4. Sube el .htaccess nuevo al final, cuando ya están todos los .php que nombra.
  5. Deja var/ con permiso 755.
  6. Borra public_html/nuevo/, que era la copia de prueba.

No toca nunca:  public_html/php.ini (mitigación de PHP),  public_html/images/
(Google tiene esas fotos indexadas),  ni nada fuera de public_html salvo crear
la carpeta de respaldo.

Si una subida falla, se detiene ANTES de subir el .htaccess y de borrar
nuevo/, y dice exactamente en qué quedó.
"""

import base64
import datetime
import ftplib
import os
import sys
import xml.etree.ElementTree as ET

AQUI = os.path.dirname(os.path.abspath(__file__))
LOCAL = os.path.normpath(os.path.join(AQUI, "..", "nuevo"))
RAIZ = "public_html"

VIEJOS = ["index.php", "contacto.php", "indexfinal3.html", "styles.css", "error_log", ".htaccess"]
NO_SUBIR_EXT = (".md",)          # documentación interna: no va al servidor
NO_TOCAR = {"php.ini", "images"}

APLICAR = "--aplicar" in sys.argv


def log(msg):
    print(msg, flush=True)


# ---------------------------------------------------------------- conexión --

def credenciales():
    ruta = os.path.join(os.environ["APPDATA"], "FileZilla", "sitemanager.xml")
    for s in ET.parse(ruta).iter("Server"):
        if (s.findtext("Name") or "").strip() == "Dekarter":
            p = s.find("Pass")
            pw = p.text or ""
            if p.get("encoding") == "base64":
                pw = base64.b64decode(pw).decode("utf-8")
            return s.findtext("Host"), int(s.findtext("Port") or 21), s.findtext("User"), pw
    sys.exit("No encontré el sitio «Dekarter» en el Gestor de sitios de FileZilla.")


def conectar():
    host, port, user, pw = credenciales()
    try:
        f = ftplib.FTP_TLS(timeout=60)
        f.connect(host, port)
        f.login(user, pw)
        f.prot_p()
        modo = "FTPS (cifrado)"
    except Exception:
        f = ftplib.FTP(timeout=60)
        f.connect(host, port)
        f.login(user, pw)
        modo = "FTP sin cifrar"
    f.encoding = "utf-8"
    return f, modo


# --------------------------------------------------------------- ayudantes --

def listar(f, ruta):
    """Nombres dentro de una carpeta remota (sin . ni ..)."""
    try:
        return {os.path.basename(n.rstrip("/")) for n in f.nlst(ruta)} - {".", ".."}
    except ftplib.error_perm:
        return set()


def existe(f, ruta):
    """¿Existe ese archivo? Se pregunta por nombre (SIZE): los listados FTP de
    muchos servidores esconden los archivos que empiezan con punto, como
    .htaccess, y fiarse del listado haría pisar el .htaccess viejo sin
    respaldarlo."""
    try:
        f.size(ruta)
        return True
    except ftplib.all_errors:
        return False


def es_carpeta(f, ruta):
    actual = f.pwd()
    try:
        f.cwd(ruta)
        return True
    except ftplib.error_perm:
        return False
    finally:
        f.cwd(actual)


def crear_carpeta(f, ruta):
    if APLICAR:
        try:
            f.mkd(ruta)
        except ftplib.error_perm as e:
            if not str(e).startswith("550"):
                raise


def borrar_arbol(f, ruta):
    # Los ocultos no salen en el listado: se borran por nombre, si están.
    for oculto in (".htaccess", ".user.ini"):
        if existe(f, ruta + "/" + oculto):
            f.delete(ruta + "/" + oculto)
    for nombre in listar(f, ruta):
        hijo = ruta + "/" + nombre
        if es_carpeta(f, hijo):
            borrar_arbol(f, hijo)
        else:
            f.delete(hijo)
    f.rmd(ruta)


def archivos_locales():
    """Lista (local, remota) de todo lo que hay que subir, sin index.php ni .htaccess."""
    salida = []
    for carpeta, subcarpetas, archivos in os.walk(LOCAL):
        rel = os.path.relpath(carpeta, LOCAL).replace("\\", "/")
        rel = "" if rel == "." else rel
        if rel == "var" or rel.startswith("var/"):
            continue        # var/ se crea vacía: su contenido lo genera el servidor
        for a in sorted(archivos):
            if a.endswith(NO_SUBIR_EXT):
                continue
            remota = "/".join(x for x in (RAIZ, rel, a) if x)
            if remota in (RAIZ + "/index.php", RAIZ + "/.htaccess"):
                continue
            salida.append((os.path.join(carpeta, a), remota))
    return salida


def subir(f, local, remota):
    if APLICAR:
        with open(local, "rb") as fh:
            f.storbinary("STOR " + remota, fh)


# ------------------------------------------------------------------ guion --

def main():
    if not os.path.isfile(os.path.join(LOCAL, "index.php")):
        sys.exit("No encuentro el sitio nuevo en " + LOCAL)

    log("=" * 64)
    log("  DEKARTER — pasar el sitio nuevo a la raíz")
    log("  " + ("MODO REAL: se van a hacer cambios en el servidor" if APLICAR
               else "ENSAYO: no se modifica nada. Para hacerlo: --aplicar"))
    log("=" * 64)

    f, modo = conectar()
    log(f"\nConectado por {modo}.")

    en_raiz = listar(f, RAIZ)
    if not en_raiz:
        sys.exit("No pude leer public_html. No sigo.")
    log(f"public_html tiene {len(en_raiz)} elementos.")
    for intocable in sorted(NO_TOCAR & en_raiz):
        log(f"  · {intocable}: se deja tal cual")

    sello = datetime.datetime.now().strftime("%Y%m%d-%H%M")
    respaldo = f"respaldo-sitio-anterior-{sello}"
    subidas = archivos_locales()
    carpetas = sorted({r.rsplit("/", 1)[0] for _, r in subidas} | {RAIZ + "/var"}, key=len)

    # 1-2. respaldo
    log(f"\n[1] Carpeta de respaldo fuera de public_html: /{respaldo}/")
    crear_carpeta(f, respaldo)
    log("[2] Mover el sitio viejo al respaldo:")
    for v in VIEJOS:
        if existe(f, f"{RAIZ}/{v}"):
            log(f"      {v}")
            if APLICAR:
                f.rename(f"{RAIZ}/{v}", f"{respaldo}/{v}")
        else:
            log(f"      {v}  (no está, se omite)")

    # 3. subir
    log(f"\n[3] Subir el sitio nuevo: index.php + {len(subidas)} archivos")
    for c in carpetas:
        if c != RAIZ:
            crear_carpeta(f, c)
    fallas = []
    for local, remota in [(os.path.join(LOCAL, "index.php"), RAIZ + "/index.php")] + subidas:
        try:
            subir(f, local, remota)
        except Exception as e:
            fallas.append((remota, str(e)))
            log(f"      FALLÓ {remota}: {e}")
    if fallas:
        log(f"\n{len(fallas)} subidas fallaron. Me detengo acá: NO subo el .htaccess ni borro nuevo/.")
        log(f"El sitio viejo está entero en /{respaldo}/. Corre el script de nuevo para reintentar.")
        f.quit()
        sys.exit(1)
    log("      todo subido" if APLICAR else "      (ensayo)")

    # 4. .htaccess al final
    log("\n[4] Subir .htaccess nuevo")
    subir(f, os.path.join(LOCAL, ".htaccess"), RAIZ + "/.htaccess")

    # 5. permisos de var/
    log("[5] var/ con permiso 755")
    if APLICAR:
        try:
            f.sendcmd(f"SITE CHMOD 755 {RAIZ}/var")
        except ftplib.error_perm as e:
            log(f"      no se pudo fijar el permiso ({e}). Hacerlo a mano en FileZilla.")

    # 6. borrar la copia de prueba
    log("[6] Borrar public_html/nuevo/ (copia de prueba)")
    if "nuevo" in en_raiz:
        if APLICAR:
            borrar_arbol(f, RAIZ + "/nuevo")
    else:
        log("      no existe, se omite")

    f.quit()
    log("\n" + ("LISTO. Abre https://dekarter.cl para comprobar." if APLICAR
               else "Ensayo terminado. Si todo se ve bien, corre lo mismo con --aplicar."))
    log(f"Para volver atrás: los archivos viejos están en /{respaldo}/.")


if __name__ == "__main__":
    main()
