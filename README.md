# Svad(p)ička

Svadobný web Janka a Paťky. Technický základ: PHP 8.2, Bootstrap 5, vanilla JavaScript a Google Sheets ako jednoduché úložisko RSVP.

## Lokálne alebo na hostingu

1. Webroot domény nastav na priečinok `public/`.
2. Spusť `composer install --no-dev --optimize-autoloader`.
3. Skopíruj `.env.example` na `.env` a doplň doménu a cestu ku Google service-account JSON súboru.
4. Service-account e-mail pridaj do Google Sheetu ako editora.
5. Do záložky `RSVP` vlož každého hosťa na samostatný riadok. Stačí vyplniť `prezyvka` (stĺpec D). Generátor doplní `guest_id`, `token`, `url` a hosťa nastaví ako aktívneho.
6. Spusť `php bin/generate-invite-links.php`. Príkaz bezpečne doplní chýbajúce `guest_id` a náhodné tokeny, zapíše ich do Sheetu a vypíše zoznam mien s osobnými odkazmi. Existujúce odkazy nemení, takže ho možno spúšťať opakovane.
7. Kontrolný náhľad bez zápisu spustíš cez `php bin/generate-invite-links.php --dry-run`.

Koreň domény `/` je verejný informačný onepager. Osobný odkaz v tvare `/p/TOKEN` vedie na samostatnú RSVP stránku s priamym oslovením hosťa. Ak už hosť odpovedal, formulár sa z jeho riadka v Sheete automaticky predvyplní.

JSON kľúč ani `.env` nepatria do repozitára alebo verejného priečinka.
