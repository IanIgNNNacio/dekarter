# -*- coding: utf-8 -*-
"""
Pone (o cambia) la contraseña del cotizador.

Uso:
    python web\\herramientas\\clave_cotizador.py

Pide la contraseña dos veces, sin mostrarla en pantalla, y guarda SÓLO su hash
(bcrypt, el mismo formato que usa PHP) en web/nuevo/inc/cotizador.local.php.
Ese archivo no se sube a GitHub. La contraseña en sí no queda escrita en
ningún lado.

Después hay que desplegar para que el hash llegue al servidor:
    python -I web\\herramientas\\desplegar_raiz.py --sin-filezilla --aplicar --reanudar
"""

import getpass
import os
import subprocess
import sys

DESTINO = os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)),
                                        "..", "nuevo", "inc", "cotizador.local.php"))

clave = getpass.getpass("Nueva contraseña del cotizador: ")
if len(clave) < 10:
    sys.exit("Muy corta: usa al menos 10 caracteres.")
if getpass.getpass("Repítela: ") != clave:
    sys.exit("No coinciden. No se cambió nada.")

# El hash lo genera el propio PHP, así queda exactamente en el formato que
# después verifica password_verify(). La clave viaja por la entrada estándar,
# no como argumento: los argumentos de un proceso los puede ver cualquiera.
r = subprocess.run(
    ["php", "-r", 'echo password_hash(stream_get_contents(STDIN), PASSWORD_DEFAULT);'],
    input=clave.encode("utf-8"), capture_output=True,
)
hash_ = r.stdout.decode().strip()
if r.returncode != 0 or not hash_.startswith("$2y$"):
    sys.exit("No se pudo generar el hash con PHP: " + r.stderr.decode())

with open(DESTINO, "w", encoding="utf-8", newline="\n") as f:
    f.write("<?php\n// Hash de la contraseña del cotizador. Generado por clave_cotizador.py.\n"
            "// No se versiona. Para cambiar la clave, volver a correr ese script.\n"
            f"define('DK_COT_CLAVE_HASH', '{hash_}');\n")

print("Listo. Contraseña guardada (sólo su hash) en", DESTINO)
print("Falta desplegar para que llegue al servidor.")
