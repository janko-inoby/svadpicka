<?php

declare(strict_types=1);

namespace Svadpicka;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Google\Service\Sheets\ValueRange;

final class RsvpRepository
{
    private Sheets $sheets;
    private string $spreadsheetId;
    private string $tab;

    public function __construct()
    {
        $client = new Client();
        $client->setAuthConfig(Config::get('GOOGLE_CREDENTIALS_PATH'));
        $client->setScopes([Sheets::SPREADSHEETS]);

        $this->sheets = new Sheets($client);
        $this->spreadsheetId = Config::get('GOOGLE_SHEET_ID');
        $this->tab = Config::get('GOOGLE_SHEET_TAB');
    }

    /** @return array<string,mixed>|null */
    public function findActiveGuest(string $token): ?array
    {
        $rows = $this->sheets->spreadsheets_values
            ->get($this->spreadsheetId, "'{$this->tab}'!A2:AB1000")
            ->getValues() ?? [];

        foreach ($rows as $index => $row) {
            if (($row[1] ?? '') !== $token || !$this->toBool($row[4] ?? false)) {
                continue;
            }

            return [
                'row' => $index + 2,
                'guest_id' => (string) ($row[0] ?? ''),
                'token' => (string) $row[1],
                'url' => (string) ($row[2] ?? ''),
                'prezyvka' => (string) ($row[3] ?? ''),
                'aktivny' => true,
                'odpoved_odoslana' => $this->toBool($row[5] ?? false),
                'odoslane_at' => (string) ($row[6] ?? ''),
                'ucast' => (string) ($row[7] ?? ''),
                'strava' => (string) ($row[8] ?? ''),
                'alergia_lepok' => $this->toBool($row[9] ?? false),
                'alergia_laktoza' => $this->toBool($row[10] ?? false),
                'alergie_ine' => (string) ($row[11] ?? ''),
                'alkohol' => array_values(array_filter([
                    $this->toBool($row[12] ?? false) ? 'pivo' : null,
                    $this->toBool($row[13] ?? false) ? 'biele_vino' : null,
                    $this->toBool($row[14] ?? false) ? 'cervene_vino' : null,
                    $this->toBool($row[15] ?? false) ? 'prosecco' : null,
                    $this->toBool($row[16] ?? false) ? 'rum' : null,
                    $this->toBool($row[17] ?? false) ? 'whiskey' : null,
                    $this->toBool($row[18] ?? false) ? 'gin' : null,
                    $this->toBool($row[19] ?? false) ? 'tequila' : null,
                    $this->toBool($row[20] ?? false) ? 'vodka' : null,
                    $this->toBool($row[21] ?? false) ? 'nepijem' : null,
                ])),
                'alkohol_ine' => (string) ($row[22] ?? ''),
                'ubytovanie' => (string) ($row[23] ?? ''),
                'koleso_suhlas' => $this->toBool($row[24] ?? false),
                'dzubox' => (string) ($row[25] ?? ''),
                'poznamka' => (string) ($row[26] ?? ''),
            ];
        }

        return null;
    }

    /** @param array<string,mixed> $data */
    public function save(int $row, array $data): void
    {
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Bratislava')))
            ->format('Y-m-d H:i:s');
        $alcohol = array_fill_keys($data['alkohol'], true);

        $values = [[
            true,
            $now,
            $data['ucast'],
            $data['strava'],
            $data['alergia_lepok'],
            $data['alergia_laktoza'],
            $data['alergie_ine'],
            isset($alcohol['pivo']),
            isset($alcohol['biele_vino']),
            isset($alcohol['cervene_vino']),
            isset($alcohol['prosecco']),
            isset($alcohol['rum']),
            isset($alcohol['whiskey']),
            isset($alcohol['gin']),
            isset($alcohol['tequila']),
            isset($alcohol['vodka']),
            isset($alcohol['nepijem']),
            $data['alkohol_ine'],
            $data['ubytovanie'],
            $data['koleso_suhlas'],
            $data['dzubox'],
            $data['poznamka'],
            $now,
        ]];

        $this->sheets->spreadsheets_values->update(
            $this->spreadsheetId,
            "'{$this->tab}'!F{$row}:AB{$row}",
            new ValueRange(['values' => $values]),
            ['valueInputOption' => 'USER_ENTERED']
        );
    }

    /**
     * Doplní chýbajúce guest_id a tokeny aktívnym hosťom a vráti ich osobné odkazy.
     * Existujúce identifikátory ani tokeny nikdy nemení.
     *
     * @return list<array{row:int,prezyvka:string,guest_id:string,token:string,url:string,created:bool}>
     */
    public function generateInviteLinks(string $appUrl, bool $write = true): array
    {
        $rows = $this->sheets->spreadsheets_values
            ->get($this->spreadsheetId, "'{$this->tab}'!A2:E1000")
            ->getValues() ?? [];

        $usedGuestIds = [];
        $usedTokens = [];
        foreach ($rows as $row) {
            if (($row[0] ?? '') !== '') {
                $usedGuestIds[(string) $row[0]] = true;
            }
            if (($row[1] ?? '') !== '') {
                $usedTokens[(string) $row[1]] = true;
            }
        }

        $updates = [];
        $links = [];
        $baseUrl = rtrim($appUrl, '/');

        foreach ($rows as $index => $row) {
            $sheetRow = $index + 2;
            $prezyvka = trim((string) ($row[3] ?? ''));
            if ($prezyvka === '') {
                continue;
            }

            $activeCell = $row[4] ?? '';
            $active = $activeCell === '' || $this->toBool($activeCell);
            if (!$active) {
                continue;
            }

            $guestId = trim((string) ($row[0] ?? ''));
            $token = trim((string) ($row[1] ?? ''));
            $currentUrl = trim((string) ($row[2] ?? ''));
            $created = false;

            if ($guestId === '') {
                $baseId = $this->slugify($prezyvka);
                $baseId = $baseId !== '' ? $baseId : 'host';
                $guestId = $baseId;
                $suffix = 2;
                while (isset($usedGuestIds[$guestId])) {
                    $guestId = $baseId . '-' . $suffix++;
                }
                $usedGuestIds[$guestId] = true;
                $created = true;
            }

            if ($token === '') {
                do {
                    $token = bin2hex(random_bytes(24));
                } while (isset($usedTokens[$token]));
                $usedTokens[$token] = true;
                $created = true;
            }

            $url = $baseUrl . '/p/' . rawurlencode($token);

            if ($created || $activeCell === '' || $currentUrl !== $url) {
                $updates[] = new ValueRange([
                    'range' => "'{$this->tab}'!A{$sheetRow}:E{$sheetRow}",
                    'values' => [[$guestId, $token, $url, $prezyvka, true]],
                ]);
            }

            $links[] = [
                'row' => $sheetRow,
                'prezyvka' => $prezyvka,
                'guest_id' => $guestId,
                'token' => $token,
                'url' => $url,
                'created' => $created,
            ];
        }

        if ($write && $updates !== []) {
            $this->sheets->spreadsheets_values->batchUpdate(
                $this->spreadsheetId,
                new BatchUpdateValuesRequest([
                    'valueInputOption' => 'USER_ENTERED',
                    'data' => $updates,
                ])
            );
        }

        return $links;
    }

    private function slugify(string $value): string
    {
        $value = strtr(mb_strtolower($value), [
            'á'=>'a','ä'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ĺ'=>'l','ľ'=>'l',
            'ň'=>'n','ó'=>'o','ô'=>'o','ŕ'=>'r','ř'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ů'=>'u',
            'ý'=>'y','ž'=>'z',
        ]);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    private function toBool(mixed $value): bool
    {
        return $value === true || in_array(strtolower((string) $value), ['true', '1', 'áno', 'ano'], true);
    }
}
