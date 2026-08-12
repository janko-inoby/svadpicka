<?php

declare(strict_types=1);

namespace Svadpicka;

final class RsvpValidator
{
    private const DIETS = ['masozravec', 'bylinozravec'];
    private const ALCOHOL = ['pivo','biele_vino','cervene_vino','prosecco','rum','whiskey','gin','tequila','vodka','nepijem'];

    /** @return array<string,mixed> */
    public static function fromRequest(array $input): array
    {
        $attendance = (string) ($input['ucast'] ?? '');
        if (!in_array($attendance, ['pridem', 'nepridem'], true)) {
            throw new \InvalidArgumentException('Vyber, či prídeš.');
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
            throw new \InvalidArgumentException('S Kolesom nieŠťastia musíš súhlasiť. Také sú pravidlá.');
        }

        $accommodation = (string) ($input['ubytovanie'] ?? '');
        if ($attendance === 'pridem' && !in_array($accommodation, ['ano', 'nie'], true)) {
            throw new \InvalidArgumentException('Vyber, či potrebuješ nocľah.');
        }

        return [
            'ucast' => $attendance,
            'strava' => $attendance === 'pridem' ? $diet : '',
            'alergia_lepok' => $attendance === 'pridem' && isset($input['alergia_lepok']),
            'alergia_laktoza' => $attendance === 'pridem' && isset($input['alergia_laktoza']),
            'alergie_ine' => $attendance === 'pridem' ? self::text($input, 'alergie_ine', 500) : '',
            'alkohol' => $attendance === 'pridem' ? $alcohol : [],
            'alkohol_ine' => $attendance === 'pridem' ? self::text($input, 'alkohol_ine', 500) : '',
            'ubytovanie' => $attendance === 'pridem' ? $accommodation : '',
            'koleso_suhlas' => $attendance === 'pridem' && $consent,
            'dzubox' => $attendance === 'pridem' ? self::text($input, 'dzubox', 500) : '',
            'poznamka' => self::text($input, 'poznamka', 1000),
        ];
    }

    private static function text(array $input, string $key, int $max): string
    {
        return mb_substr(trim((string) ($input[$key] ?? '')), 0, $max);
    }
}
