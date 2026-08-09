# Svad(p)ička

Svadobný web Janka a Paťky. Technický základ: PHP 8.2, Bootstrap 5, vanilla JavaScript a Google Sheets ako jednoduché úložisko RSVP.

## Lokálne alebo na hostingu

1. Webroot domény nastav na priečinok `public/`.
2. Spusť `composer install --no-dev --optimize-autoloader`.
3. Skopíruj `.env.example` na `.env` a doplň doménu a cestu ku Google service-account JSON súboru.
4. Service-account e-mail pridaj do Google Sheetu ako editora.
5. Do záložky `RSVP` vlož hostí: unikátne `guest_id`, náhodný `token`, meno, priezvisko, oslovenie a `TRUE` v stĺpci `aktivny`.
6. Osobný odkaz má tvar `https://domena.sk/p/TOKEN`.

JSON kľúč ani `.env` nepatria do repozitára alebo verejného priečinka.

