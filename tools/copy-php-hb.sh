#!/bin/bash
#
if [ "$(id -u)" -ne 0 ]; then
  if command -v sudo >/dev/null 2>&1; then
    exec sudo bash "$0" "$@"
  else
    echo "Dieses Script benötigt root-Rechte, aber sudo ist nicht verfügbar." >&2
    exit 1
  fi
fi
# kopiert die php.txt Dateien vom Repo ins html-Verzeichnis und benennt sie als php Dateien.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_ROOT="$(dirname "$SCRIPT_DIR")"
#GIT_ROOT="$(dirname "$REPO_ROOT")"
dirs=("" "includes" "admin")
# für lokale TEST wird bei ''./<script.sh> l' das lokale TARTGET_ROOT gesetzt

if [ "$1" = "l" ]; then
  TARGET_ROOT="/opt/lampp/apache2/htdocs/hallenbuch"
  if [ ! -d "$TARGET_ROOT" ]; then
    echo "Zielverzeichnis nicht gefunden, auf dem richtigen Server?"
    exit 1
  fi
  TARGET_USER="root:daemon"
elif [ "$1" = "o" ]; then
  TARGET_ROOT="$(dirname "$SCRIPT_DIR"|sed 's@git/FTD-Hallenbuch@HESK/hallenbuch@g')"
  if [ "$SOURCE_ROOT" = "$TARGET_ROOT" ]; then
	  echo "Fehler beim Aufruf (SOURCE_ROOT und TARGET_ROOT), auf dem richtigen Server?" >&2
    exit 1
  fi  
  TARGET_USER="www-data:"
else
  echo "Aufruf sollte '$0 l' für Lokales Testen, oder '$0 o' für Online-Server sein." >&2
  exit 0
fi

for dir in "${dirs[@]}"; do
  #TARGET_DIR="$TARGET_ROOT/$dir"
  if [ ! -d "$TARGET_ROOT/$dir" ]; then
    mkdir -p "$TARGET_ROOT/$dir" || { echo "Fehler: Konnte $TARGET_ROOT/$dir nicht anlegen" >&2; exit 1; }
  fi
  #echo "SCRIPT_DIR: $SCRIPT_DIR, BASH_SOURCE: ${BASH_SOURCE[0]}, REPO_ROOT: $REPO_ROOT, TARGET_DIR: $TARGET_ROOT/$dir, pwd: $(pwd)"
  if ls "$SOURCE_ROOT/$dir"/*.php >/dev/null 2>&1; then
    for file in "$SOURCE_ROOT/$dir"/*.php; do
      SOURCE_FILE=$(echo "$SOURCE_ROOT/$dir/${file##*/}"|sed 's@\/\/@\/@g')
      TARGET_FILE=$(echo "$TARGET_ROOT/$dir/${file##*/}"|sed 's/php.txt/php/g;s@\/\/@\/@g')
      cp $SOURCE_FILE $TARGET_FILE
      #sudo -u $TARGET_USER cp $SOURCE_FILE $TARGET_FILE
      ls -l $SOURCE_FILE $TARGET_FILE
      #sudo -u $TARGET_USER ls -l $SOURCE_FILE $TARGET_FILE
#     echo -e "$SOURCE_FILE\n$TARGET_FILE\n$file"
    done
    #echo "File is: $(ls "$TARGET_ROOT/$dir"/*.php.txt | grep -v "config.php")_:" #> /dev/null
  fi
done
chown -R $TARGET_USER $TARGET_ROOT

exit 0

