#!/bin/bash
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
if [ "$1" = "l" ]; then
  TARGET_ROOT="/opt/lampp/apache2/htdocs/hallenbuch"
  if [ ! -d "$TARGET_ROOT" ]; then
    echo "Zielverzeichnis nicht gefunden, auf dem richtigen Server?"
    exit 1
  fi
  sed -i "s/\$hesk_settings\['debug'\] = false;/\$hesk_settings['debug'] = true;/" /opt/lampp/apache2/htdocs/hallenbuch/config.php
elif [ "$1" = "o" ]; then
  TARGET_ROOT="$(dirname "$SCRIPT_DIR"|sed 's@git/FTD-Hallenbuch@HESK/hallenbuch@g')"
  if [ "$SOURCE_ROOT" = "$TARGET_ROOT" ]; then
	  echo "Fehler beim Aufruf (SOURCE_ROOT und TARGET_ROOT), auf dem richtigen Server?" >&2
    exit 1
  fi
  sed -i "s/\$hesk_settings\['debug'\] = false;/\$hesk_settings['debug'] = true;/" /home/risse/HESK/hallenbuch/config.php
else
  echo "Aufruf sollte '$0 l' für Lokales Testen, oder '$0 o' für Online-Server sein." >&2
  exit 0
fi
