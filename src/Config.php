<?php

declare(strict_types=1);

namespace Svadpicka;

final class Config
{
    public static function get(string $key): string
    {
        $value = $_ENV[$key] ?? getenv($key) ?: '';
        if ($value === '') {
            throw new \RuntimeException("Chýba nastavenie {$key}.");
        }

        return $value;
    }
}

