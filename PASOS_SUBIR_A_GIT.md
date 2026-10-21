# Pasos para subir el proyecto a GitHub

Esta guía explica cómo publicar el proyecto por primera vez y cómo subir cambios después. Los comandos están pensados para PowerShell en Windows y deben ejecutarse desde la carpeta raíz del proyecto.

## 1. Preparar las herramientas y GitHub

1. Instala **Git for Windows** desde [git-scm.com](https://git-scm.com/download/win), si todavía no está instalado.
2. Cierra y vuelve a abrir PowerShell. Comprueba que Git esté disponible:

   ```powershell
   git --version
   ```

3. Crea un repositorio nuevo en GitHub. Para este procedimiento, créalo vacío: no agregues README, licencia ni `.gitignore` desde GitHub.
4. Copia la URL HTTPS del repositorio. Tendrá una forma parecida a `https://github.com/USUARIO/REPOSITORIO.git`.

Configura tu nombre y correo para los commits (hazlo una sola vez en ese equipo y reemplaza los ejemplos):

```powershell
git config --global user.name "Tu nombre"
git config --global user.email "tu-correo@example.com"
```

## 2. Revisar qué se va a publicar

En PowerShell, entra a la carpeta del proyecto:

```powershell
Set-Location "C:\laragon\www\Centro_de_salud"
```

Antes de preparar archivos, confirma que `.env` esté ignorado:

```powershell
git check-ignore .env
```

El proyecto ya incluye reglas para ignorar `.env`, `vendor` y `node_modules`. **No publiques `.env`, contraseñas, llaves privadas ni credenciales.** Si `.env` no aparece como ignorado, detente y corrige `.gitignore` antes de continuar.

Revisa también `database\database.sqlite`: si contiene datos reales o información personal, no la publiques. El archivo no está cubierto por las reglas actuales de `.gitignore`; si no debe subirse, agrega `database/*.sqlite` a `.gitignore` antes de preparar los archivos.

## 3. Primer envío del proyecto

Si esta carpeta todavía no tiene un repositorio Git, inicialízalo:

```powershell
git init -b main
```

Prepara los archivos y revisa exactamente qué se incluirá:

```powershell
git add -A
git status
```

Si en la lista aparece `.env` o una base de datos que no debe publicarse, quítala del área de preparación antes de continuar:

```powershell
git restore --staged .env
git restore --staged database/database.sqlite
```

Ejecuta solo el comando correspondiente a un archivo que aparezca preparado. Vuelve a revisar `git status` y no sigas hasta que la lista contenga únicamente archivos que quieras compartir.

Crea el commit inicial y vincula el repositorio remoto. Sustituye la URL por la que copiaste de GitHub:

```powershell
git commit -m "Publica versión inicial"
git remote add origin https://github.com/USUARIO/REPOSITORIO.git
git push -u origin main
```

GitHub puede pedir que inicies sesión o que autorices Git Credential Manager. Sigue el proceso que muestre el navegador o la terminal; no escribas contraseñas ni tokens dentro de archivos del proyecto.

## 4. Subir cambios posteriores

Cada vez que quieras subir cambios:

```powershell
git status
git add -A
git status
git diff --cached
git commit -m "Describe brevemente el cambio"
git push
```

Antes del commit, revisa el segundo `git status` y `git diff --cached` para confirmar que no se incluyeron secretos, archivos generados o datos locales.

Si otras personas pudieron actualizar GitHub desde el último envío, trae primero esos cambios:

```powershell
git pull --rebase
git push
```

## 5. Comandos útiles

```powershell
git status                 # Ver cambios y archivos preparados
git remote -v              # Ver la URL del repositorio remoto
git log --oneline -5       # Ver los últimos commits
```

Si Git indica que `origin` ya existe, no lo agregues otra vez; comprueba la URL con `git remote -v`. Si la URL es incorrecta, puedes actualizarla así:

```powershell
git remote set-url origin https://github.com/USUARIO/REPOSITORIO.git
```
