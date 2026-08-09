<?php

declare(strict_types=1);

namespace Svadpicka;

use Google\Client;
use Google\Service\Sheets;
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

    /** @return array{row:int,guest_id:string,token:string,meno:string,priezvisko:string,oslovenie:string,aktivny:bool}|null */
    public function findActiveGuest(string $token): ?array
    {
        $rows = $this->sheets->spreadsheets_values
            ->get($this->spreadsheetId, "'{$this->tab}'!A2:G1000")
            ->getValues() ?? [];

        foreach ($rows as $index => $row) {
            if (($row[1] ?? '') !== $token || !$this->toBool($row[5] ?? false)) {
                continue;
            }

            return [
                'row' => $index + 2,
                'guest_id' => (string) ($row[0] ?? ''),
                'token' => (string) $row[1],
                'meno' => (string) ($row[2] ?? ''),
                'priezvisko' => (string) ($row[3] ?? ''),
                'oslovenie' => (string) ($row[4] ?? ''),
                'aktivny' => true,
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
            $data['meno_a_priezvisko'],
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
            $data['nealko'],
            $data['ubytovanie'],
            $data['koleso_suhlas'],
            $data['dzubox'],
            $data['poznamka'],
            $now,
        ]];

        $this->sheets->spreadsheets_values->update(
            $this->spreadsheetId,
            "'{$this->tab}'!H{$row}:AE{$row}",
            new ValueRange(['values' => $values]),
            ['valueInputOption' => 'USER_ENTERED']
        );
    }

    private function toBool(mixed $value): bool
    {
        return $value === true || in_array(strtolower((string) $value), ['true', '1', 'áno', 'ano'], true);
    }
}

