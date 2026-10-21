# -*- coding: utf-8 -*-
"""
Pasa el sitio nuevo de Dekarter a la raíz de public_html, por FTP.

Uso:
    python web\\herramientas\\desplegar_raiz.py            # ensayo: muestra qué haría, no toca nada
    python web\\herramientas\\desplegar_raiz.py --aplicar  # lo hace de verdad

Credenciales, en este orden (la primera que esté completa):
  0. El archivo credenciales-ftp.local, al lado de este script, que llena el
     usuario a mano y git ignora.
  1. Una cuenta FTP dedicada, en variables de entorno:
       DK_FTP_USER, DK_FTP_PASS   (obligatorias)
       DK_FTP_HOST                (por omisión 162.241.62.4)
       DK_FTP_RAIZ                (por omisión public_html; poner "." si la
                                   cuenta está limitada a public_html)
  2. Si no están, la conexión «Dekarter» guardada en FileZilla. Con
     --sin-filezilla ese respaldo queda prohibido y el script se detiene.

La contraseña se usa dentro del proceso: no se imprime ni se escribe.

Qué hace, en este orden (para que el sitio quede caído del orden de un segundo):
  1. Crea una carpeta de respaldo. Fuera de public_html si la cuenta la ve; si
     la cuenta está limitada a public_html, adentro, como _respaldo-..., que el
     .htaccess nuevo bloquea para los navegadores.
  2. Mueve ahí el sitio viejo: index.php, contacto.php, indexfinal3.html,
     styles.css, error_log y .htaccess.
  3. Sube index.php nuevo primero, después el resto.
  4. Sube el .htaccess nuevo al final, cuando ya existen los .php que nombra.
  5. Deja var/ con permiso 755.
  6. Borra nuevo/, la copia de prueba.

No toca nunca php.ini (mitigación de PHP) ni images/ (fotos indexadas).
Si una subida falla, se detiene antes del .htaccess y del borrado de nuevo/.
"""

import base64
import datetime
import ftplib
import os
import sys
import xml.etree.ElementTree as ET

AQUI = os.path.dirname(os.path.abspath(__file__))
LOCAL = os.path.normpath(os.path.join(AQUI, "..", "nuevo"))

ARCHIVO_CRED = os.path.join(AQUI, "credenciales-ftp.local")


def leer_archivo_cred():
    """Lee credenciales-ftp.local (clave = valor). Lo llena el usuario a mano;
    git lo ignora. Devuelve {} si no existe o está vacío."""
    datos = {}
    if os.path.isfile(ARCHIVO_CRED):
        with open(ARCHIVO_CRED, encoding="utf-8") as fh:
            for linea in fh:
                linea = linea.strip()
                if not linea or linea.startswith("#") or "=" not in linea:
                    continue
                clave, valor = linea.split("=", 1)
                if valor.strip():
                    datos[clave.strip().lower()] = valor.strip()
    return datos


_CRED = leer_archivo_cred()
RAIZ = (os.environ.get("DK_FTP_RAIZ") or _CRED.get("raiz") or "public_html").strip("/") or "."

VIEJOS = ["index.php", "contacto.php", "indexfinal3.html", "styles.css", "error_log", ".htaccess"]
NO_SUBIR_EXT = (".md",)
NO_TOCAR = {"php.ini", "images"}

APLICAR = "--aplicar" in sys.argv


def log(msg=""):
    print(msg, flush=True)


def ruta(*partes):
    """Une partes de una ruta remota, ignorando vacías y '.'."""
    return "/".join(p for p in partes if p and p != ".")


# ---------------------------------------------------------------- conexión --

def credenciales():
    if os.environ.get("DK_FTP_USER") and os.environ.get("DK_FTP_PASS"):
        return (os.environ.get("DK_FTP_HOST", "162.241.62.4"),
                int(os.environ.get("DK_FTP_PORT", "21")),
                os.environ["DK_FTP_USER"], os.environ["DK_FTP_PASS"])

    if _CRED.get("usuario") and _CRED.get("contrasena"):
        return (_CRED.get("servidor", "162.241.62.4"), int(_CRED.get("puerto", "21")),
                _CRED["usuario"], _CRED["contrasena"])

    if "--sin-filezilla" in sys.argv:
        sys.exit("Falta llenar usuario y contrasena en credenciales-ftp.local. Con "
                 "--sin-filezilla no se leen las contraseñas guardadas de FileZilla.")

    archivo = os.path.join(os.environ["APPDATA"], "FileZilla", "sitemanager.xml")
    for s in ET.parse(archivo).iter("Server"):
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
    return f, modo, user


# --------------------------------------------------------------- ayudantes --

def listar(f, carpeta):
    """Nombres dentro de una carpeta remota. Ojo: muchos servidores no muestran
    los ocultos (.htaccess); para esos se usa existe()."""
    try:
        return {os.path.basename(n.rstrip("/")) for n in f.nlst(carpeta or ".")} - {".", ".."}
    except ftplib.error_perm:
        return set()


def existe(f, archivo):
    """Comprueba un archivo por nombre (SIZE), no por el listado, porque el
    listado esconde los archivos que empiezan con punto. Fiarse del listado
    habría pisado el .htaccess viejo sin respaldarlo."""
    try:
        f.size(archivo)
        return True
    except ftplib.all_errors:
        return False


def es_carpeta(f, carpeta):
    actual = f.pwd()
    try:
        f.cwd(carpeta)
        return True
    except ftplib.error_perm:
        return False
    finally:
        f.cwd(actual)


def crear_carpeta(f, carpeta):
    if APLICAR:
        try:
            f.mkd(carpeta)
        except ftplib.error_perm as e:
            if not str(e).startswith("550"):    # 550 = ya existe
                raise


def borrar_arbol(f, carpeta):
    for oculto in (".htaccess", ".user.ini"):
        if existe(f, ruta(carpeta, oculto)):
            f.delete(ruta(carpeta, oculto))
    for nombre in listar(f, carpeta):
        hijo = ruta(carpeta, nombre)
        if es_carpeta(f, hijo):
            borrar_arbol(f, hijo)
        else:
            f.delete(hijo)
    f.rmd(carpeta)


def archivos_locales():
    """(local, remota) de todo lo que se sube, sin index.php ni .htaccess, que
    van aparte para respetar el orden."""
    salida = []
    for carpeta, _, archivos in os.walk(LOCAL):
        rel = os.path.relpath(carpeta, LOCAL).replace("\\", "/")
        rel = "" if rel == "." else rel
        if rel == "var" or rel.startswith("var/"):
            continue                    # var/ se crea vacía en el servidor
        for a in sorted(archivos):
            if a.endswith(NO_SUBIR_EXT):
                continue
            if rel == "" and a in ("index.php", ".htaccess"):
                continue
            salida.append((os.path.join(carpeta, a), ruta(RAIZ, rel, a)))
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

    f, modo, usuario = conectar()
    log(f"\nConectado por {modo} como {usuario}. Raíz del sitio para esta cuenta: '{RAIZ}'")

    en_raiz = listar(f, "" if RAIZ == "." else RAIZ)
    if not en_raiz:
        sys.exit("No pude leer la carpeta del sitio. No sigo.")
    if "index.php" not in en_raiz and not existe(f, ruta(RAIZ, "index.php")):
        sys.exit("No hay index.php en la raíz del sitio: ¿DK_FTP_RAIZ es correcto? No sigo.")
    log(f"La raíz del sitio tiene {len(en_raiz)} elementos visibles.")
    for intocable in sorted(NO_TOCAR & en_raiz):
        log(f"  · {intocable}: se deja tal cual")

    sello = datetime.datetime.now().strftime("%Y%m%d-%H%M")
    respaldo = (ruta(RAIZ, f"_respaldo-sitio-anterior-{sello}") if RAIZ == "."
                else f"respaldo-sitio-anterior-{sello}")
    subidas = archivos_locales()
    carpetas = sorted({r.rsplit("/", 1)[0] for _, r in subidas if "/" in r}
                      | {ruta(RAIZ, "var")}, key=len)

    # 1-2. respaldo
    log(f"\n[1] Carpeta de respaldo: /{respaldo}/")
    crear_carpeta(f, respaldo)
    log("[2] Mover el sitio viejo al respaldo:")
    for v in VIEJOS:
        origen = ruta(RAIZ, v)
        if existe(f, origen):
            log(f"      {v}")
            if APLICAR:
                f.rename(origen, ruta(respaldo, v))
        else:
            log(f"      {v}  (no está, se omite)")

    # 3. subir
    log(f"\n[3] Subir el sitio nuevo: index.php + {len(subidas)} archivos")
    for c in carpetas:
        if c not in (RAIZ, "."):
            crear_carpeta(f, c)
    fallas = []
    for local, remota in [(os.path.join(LOCAL, "index.php"), ruta(RAIZ, "index.php"))] + subidas:
        try:
            subir(f, local, remota)
        except Exception as e:
            fallas.append(remota)
            log(f"      FALLÓ {remota}: {e}")
    if fallas:
        log(f"\n{len(fallas)} subidas fallaron. Me detengo: NO subo el .htaccess ni borro nuevo/.")
        log(f"El sitio viejo está entero en /{respaldo}/. Correr de nuevo para reintentar.")
        f.quit()
        sys.exit(1)
    log("      todo subido" if APLICAR else "      (ensayo)")

    # 4. .htaccess al final
    log("\n[4] Subir .htaccess nuevo")
    subir(f, os.path.join(LOCAL, ".htaccess"), ruta(RAIZ, ".htaccess"))

    # 5. permisos de var/
    log("[5] var/ con permiso 755")
    if APLICAR:
        try:
            f.sendcmd("SITE CHMOD 755 " + ruta(RAIZ, "var"))
        except ftplib.error_perm as e:
            log(f"      no se pudo fijar el permiso ({e}). Hacerlo a mano en FileZilla.")

    # 6. borrar la copia de prueba
    log("[6] Borrar nuevo/ (copia de prueba)")
    if "nuevo" in en_raiz:
        if APLICAR:
            borrar_arbol(f, ruta(RAIZ, "nuevo"))
    else:
        log("      no existe, se omite")

    f.quit()
    log("\n" + ("LISTO. Abrir https://dekarter.cl para comprobar." if APLICAR
               else "Ensayo terminado. Si todo se ve bien, correr lo mismo con --aplicar."))
    log(f"Para volver atrás: los archivos viejos están en /{respaldo}/.")


if __name__ == "__main__":
    main()
