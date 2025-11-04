# FTD‑Hallenbuch

Dieses Repository enthält das Hallenbuch‑System der Freien Turner Dörnigheim 06 e.V.

Wichtiger Hinweis
- Die vorherige Anweisung, Dateien umbenennen zu müssen, ist veraltet. Die Dateien liegen jetzt direkt als `.php` vor — kein Umbenennen mehr notwendig.
- Die Anwendung bietet inzwischen auch öffentliche Listen (ohne Login). Admin‑Funktionen benötigen weiterhin eine Authentifizierung. Die Benutzerverwaltung wird ist HESK integriert.

## Installation (Kurzfassung)

1. Repository auf den Webserver kopieren
   - In das Web‑Root oder einen geeigneten Unterordner deployen (z. B. `/var/www/html/hallenbuch` oder `/opt/lampp/htdocs/hallenbuch`).

2. PHP / Server Voraussetzungen
   - PHP 7.4+ / 8.x empfohlen (iconsv, mysqli verfügbar).
   - Webserver (Apache / Nginx) mit PHP‑FPM oder mod_php.

3. Konfiguration
   - Prüfe und bearbeite `config.php` entsprechend deiner Umgebung (DB‑Zugang, Basispfad).
   - Standardmäßig werden einige Einstellungen aus `/srv/hesk_settings.inc.php` geladen — passe das an, falls nicht vorhanden.

4. Datenbank
   - Importiere die SQL‑Skripte in deine MySQL/MariaDB‑Datenbank:
     - `hallenbuch.sql`
     - `gruppen.sql`
     - `users.sql`
     - `hb_user.sql`
   - Stelle in `config.php` sicher, dass das Präfix und die Tabellennamen stimmen.

5. FPDF (PDF‑Export)
   - Für PDF‑Export wird FPDF verwendet. Das Projekt enthält `fpdf186/` — prüfe, dass `includes/export.php` darauf zugreifen kann.
   - Falls du Composer nutzt, kann statt FPDF ein Composer‑Package verwendet werden; Standard ist jedoch die im Projekt enthaltene `fpdf186`.

6. Dateirechte
   - Stelle sicher, dass der Webserver Lesezugriff auf die Projektdateien hat.
   - Falls temporäre Dateien oder Uploads erforderlich sind, setze Schreibrechte nur auf die benötigten Verzeichnisse.

## Nutzung

- Öffentliche Ansichten:
  - Listen für Gruppen, Trainer und Einträge sind auch ohne Login sichtbar (je nach Installation / Konfiguration).
- Admin / Login:
  - Admin‑Funktionen (Anlegen/Ändern/Löschen, Export für Admins) erfordern Login über `login.php`.
- Export:
  - PDF‑Export ist über die Listen erreichbar; Export respektiert die gesetzten Filter (Woche/Monat/Datum).

## Entwicklung / Deploy‑Hinweise

- Git: Änderungen über Git verwalten; das Repo enthält das Deployment‑Script der Verteilung.
- Lokaler Test: LAMPP / XAMPP funktioniert zum Testen (Pfad‑Unterschiede beachten).
- Fehlerdiagnose: PHP/Apache‑Error‑Logs nutzen (`/var/log/apache2/error.log` bzw. `/opt/lampp/logs/error_log`).

## Support

Bei Fragen oder Problemen:  
E‑Mail: it@freieturner.com

© FTD Dörnigheim 06 e.V.
