#!/bin/bash
#
# kopiert die php.txt Dateien vom Repo ins html-Verzeichnis und benennt sie als php Dateien.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_ROOT="$(dirname "$SCRIPT_DIR")"
ONLINE_DIR="/home/risse/HESK/hallenbuch"
dirs=("" "includes" "admin")

TARGET_ROOT=${ONLINE_DIR}
TARGET_USER="www-data:"
scp $SOURCE_ROOT/includes/view_entries-n.php 217.160.3.116:/home/risse/HESK/hallenbuch/includes/view_entries.php
#geht eh nicht#sed -e 's/</\&lt;/g' -e 's/>/\&gt;/g' \
#geht eh nicht#    $SOURCE_ROOT/includes/view_entries-n.php > $SOURCE_ROOT/ignored/view_entries_for_chat.txt 
#geht eh nicht#    #view_entries.php > view_entries.safe.php
#geht eh nicht#zip $SOURCE_ROOT/ignored/upload.zip $SOURCE_ROOT/ignored/view_entries_for_chat.txt
exit 0
 for dir in "${dirs[@]}"; do
  if ls "$SOURCE_ROOT/$dir"/*.php >/dev/null 2>&1; then
      SOURCE_FILE=$(echo "$SOURCE_ROOT/$dir/${file##*/}"|sed 's@\/\/@\/@g')
      echo "########## $SOURCE_ROOT/$dir ##########"
      ls -l $SOURCE_ROOT/$dir/*.php
  fi
done

#chown -R $TARGET_USER $TARGET_ROOT

exit 0
