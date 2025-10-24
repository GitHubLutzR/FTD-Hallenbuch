#!/bin/bash
if [ "$(id -u)" -ne 0 ]; then
  if command -v sudo >/dev/null 2>&1; then
    exec sudo bash "$0" "$@"
  else
    echo "Dieses Script benötigt root-Rechte, aber sudo ist nicht verfügbar." >&2
    exit 1
  fi
fi
if [ "$1" = "l" ]; then
  sed -i "s/\$hesk_settings\['debug'\] = false;/\$hesk_settings['debug'] = true;/" /opt/lampp/apache2/htdocs/hallenbuch/config.php
elif [ "$1" = "o" ]; then
  sed -i "s/\$hesk_settings\['debug'\] = false;/\$hesk_settings['debug'] = true;/" /home/risse/HESK/hallenbuch/config.php
else
  echo "Aufruf sollte '$0 l' für Lokales Testen, oder '$0 o' für Online-Server sein." >&2
  exit 0
fi
