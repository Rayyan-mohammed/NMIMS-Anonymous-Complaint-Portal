<?php

namespace App\Support;

class Validator
{
    public static function intInRange(mixed $value, int $min, int $max): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return null;
        }

        $intVal = (int) $value;
        if ($intVal < $min || $intVal > $max) {
            return null;
        }

        return $intVal;
    }

    public static function enumValue(mixed $value, array $allowed, ?string $default = null): ?string
    {
        $candidate = is_string($value) ? trim($value) : '';
        if (in_array($candidate, $allowed, true)) {
            return $candidate;
        }

        return $default;
    }

    public static function text(mixed $value, int $min = 0, int $max = 65535): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $text = trim($value);
        $len = mb_strlen($text);
        if ($len < $min || $len > $max) {
            return null;
        }

        return $text;
    }

    public static function email(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $email = trim($value);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }
}
