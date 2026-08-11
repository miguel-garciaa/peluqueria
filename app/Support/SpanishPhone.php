<?php

namespace App\Support;

class SpanishPhone
{
    public static function format(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (str_starts_with($digits, '0034')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '34') && (str_starts_with($value, '+') || strlen($digits) > 9)) {
            $digits = substr($digits, 2);
        }

        if (! preg_match('/^[6789]\d{8}$/', $digits)) {
            return $value;
        }

        return sprintf(
            '+34 %s %s %s %s',
            substr($digits, 0, 3),
            substr($digits, 3, 2),
            substr($digits, 5, 2),
            substr($digits, 7, 2),
        );
    }
}
