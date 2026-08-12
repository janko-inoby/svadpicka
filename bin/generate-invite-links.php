<?php

declare(strict_types=1);

use Svadpicka\Config;
use Svadpicka\RsvpRepository;

require dirname(__DIR__) . '/src/bootstrap.php';

$dryRun = in_array('--dry-run', $argv, true);

try {
    $links = (new RsvpRepository())->generateInviteLinks(
        Config::get('APP_URL'),
        !$dryRun
    );
} catch (Throwable $error) {
    fwrite(STDERR, "Generovanie zlyhalo: {$error->getMessage()}\n");
    exit(1);
}

if ($links === []) {
    fwrite(STDOUT, "V záložke RSVP nie sú žiadni aktívni hostia s vyplneným menom.\n");
    exit(0);
}

fwrite(STDOUT, $dryRun ? "NÁHĽAD — Sheet nebol zmenený\n\n" : "Odkazy sú pripravené a chýbajúce údaje boli zapísané do Sheetu.\n\n");
fwrite(STDOUT, "Prezývka\tOsobný odkaz\n");
foreach ($links as $link) {
    fwrite(STDOUT, $link['prezyvka'] . "\t" . $link['url'] . "\n");
}
