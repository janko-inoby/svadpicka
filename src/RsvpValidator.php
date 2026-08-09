<?php

declare(strict_types=1);

namespace Svadpicka;

final class RsvpValidator
{
    private const DIETS = ['masozravec', 'bylinozravec', 'vsezravec'];
    private const ALCOHOL = ['pivo','biele_vino','cervene_vino','prosecco','rum','whiskey','gin','tequila','vodka','nepijem'];

    /** @return array<string,mixed> */
    public static function fromRequest(array $input): array
    {
        $attendance = (string) ($input['ucast'] ?? '');
        if (!in_array($attendance, ['pridem', 'nepridem'], true)) {
            throw new \InvalidArgumentException('Vyber, či prídeš.');
        }

        $name = trim((string) ($input['meno_a_priezvisko'] ?? ''));
        if ($name === '' || mb_strlen($name) > 120) {
            throw new \InvalidArgumentException('Skontroluj meno a priezvisko.');
        }

        $diet = (string) ($input['strava'] ?? '');
        if ($attendance === 'pridem' && !in_array($diet, self::DIETS, true)) {
            throw new \InvalidArgumentException('Vyber si stravu.');
        }

        $alcohol = array_values(array_intersect(
            self::ALCOHOL,
            array_map('strval', (array) ($input['alkohol'] ?? []))
        ));
        if (in_array('nepijem', $alcohol, true)) {
            $alcohol = ['nepijem'];
        }

        $consent = filter_var($input['koleso_suhlas'] ?? false, FILTER_VALIDATE_BOOL);
        if ($attendance === 'pridem' && !$consent) {
            throw new \InvalidArgumentException('S Kolesom neŠťastia musíš súhlasiť. Také sú pravidlá.');
        }

        return [
            'ucast' => $attendance,
            'meno_a_priezvisko' => $name,
            'strava' => $attendance === 'pridem' ? $diet : '',
            'alergia_lepok' => isset($input['alergia_lepok']),
            'alergia_laktoza' => isset($input['alergia_laktoza']),
            'alergie_ine' => self::text($input, 'alergie_ine', 500),
            'alkohol' => $attendance === 'pridem' ? $alcohol : [],
            'nealko' => self::text($input, 'nealko', 500),
            'ubytovanie' => in_array(($input['ubytovanie'] ?? ''), ['ano', 'nie'], true) ? $input['ubytovanie'] : '',
            'koleso_suhlas' => $consent,
            'dzubox' => self::text($input, 'dzubox', 500),
            'poznamka' => self::text($input, 'poznamka', 1000),
        ];
    }

    private static function text(array $input, string $key, int $max): string
    {
        return mb_substr(trim((string) ($input[$key] ?? '')), 0, $max);
    }
}

