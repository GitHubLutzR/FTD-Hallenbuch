# FTD-Hallenbuch

Dieses Repository enthält das Hallenbuch-System der Freien Turner Dörnigheim 06 e.V.  
**Hinweis:** Die Dateiendungen `.php.txt` dienen nur dem Upload zu [https://copilot.microsoft.com/](https://copilot.microsoft.com/).  
**Vor dem Einsatz auf dem Webserver müssen alle `.php.txt`-Dateien in `.php` umbenannt werden!**

## Installation

1. **Repository herunterladen**  
   Lade das Repository herunter und entpacke es auf deinem Webserver.

2. **Dateien umbenennen**  
   Benenne alle Dateien mit der Endung `.php.txt` in `.php` um.  
   Beispiel:  
   `login.php.txt` → `login.php`

3. **Konfiguration**  
   Passe die Datei `config.php` an deine Umgebung an.  
   Die Zugangsdaten zur Datenbank werden aus `/srv/hesk_settings.inc.php` geladen.

4. **Datenbank einrichten**  
   Importiere die SQL-Dateien (`hallenbuch.sql`, `gruppen.sql`, `users.sql`, `hb_user.sql`) in deine MySQL/MariaDB-Datenbank.

5. **Berechtigungen setzen**  
   Stelle sicher, dass der Webserver Schreibrechte auf benötigte Verzeichnisse hat (z.B. für Datei-Uploads oder temporäre Dateien).

## Verzeichnisstruktur

- `*.php.txt` – PHP-Quellcode (vor dem Einsatz umbenennen!)
- `admin/` – Admin-Funktionen und Benutzerverwaltung
- `includes/` – Wiederverwendbare Komponenten und Hilfsfunktionen
- `*.sql` – SQL-Skripte zur Datenbankstruktur

## Wichtige Hinweise

- **Login:** Das Admin-Login erfolgt über `login.php`.
- **Passwort-Hashes:** Neue Passworthashes können mit `includes/generate_passwd.php` erzeugt werden.
- **Sicherheit:** Die Zugangsdaten und Passwörter werden sicher gehasht gespeichert.
- **Export:** Einträge können als PDF exportiert werden (`export.php`).

## Support

Bei Fragen oder Problemen:  
E-Mail: [it@freieturner.com](mailto:it@freieturner.com)

---

© FTD Dörnigheim 06 e.V.
