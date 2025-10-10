#!/bin/bash
#
# kopiert die php.txt Dateien vom Repo ins html-Verzeichnis und benennt sie als php Dateien.
TARGET_USER="www-data"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_ROOT="$(dirname "$SCRIPT_DIR")"
TARGET_ROOT="$(dirname "$SCRIPT_DIR"|sed 's@git/FTD-Hallenbuch@HESK/hallenbuch@g')"
GIT_ROOT="$(dirname "$REPO_ROOT")"
dirs=("" "includes" "admin")

for dir in "${dirs[@]}"; do
  TARGET_DIR="$SOURCE_ROOT/$dir"
  #echo "SCRIPT_DIR: $SCRIPT_DIR, BASH_SOURCE: ${BASH_SOURCE[0]}, REPO_ROOT: $REPO_ROOT, TARGET_DIR: $TARGET_DIR, pwd: $(pwd)"
  if ls "$TARGET_DIR"/*.php.txt >/dev/null 2>&1; then
    for file in "$TARGET_DIR"/*.php.txt; do
      SOURCE_FILE=$(echo "$SOURCE_ROOT/$dir/${file##*/}"|sed 's@\/\/@\/@g')
      TARGET_FILE=$(echo "$TARGET_ROOT/$dir/${file##*/}"|sed 's/php.txt/php/g;s@\/\/@\/@g')
      sudo -u $TARGET_USER cp $SOURCE_FILE $TARGET_FILE
      sudo -u $TARGET_USER ls -l $SOURCE_FILE $TARGET_FILE
#     echo -e "$SOURCE_FILE\n$TARGET_FILE\n$file"
    done
    #echo "File is: $(ls "$TARGET_DIR"/*.php.txt | grep -v "config.php")_:" #> /dev/null
  fi
done
sudo chown -R www-data:www-data $TARGET_ROOT

exit 0

