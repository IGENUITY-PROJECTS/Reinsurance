<?php

namespace App\Support;

class DisplayDate
{
    public static function format(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d M Y');
        }
        $value = trim((string) $value);
        if ($value === '') {
            return 'Not Available';
        }
        foreach (['!Ymd', '!Y-m-d', '!Y-m-d H:i:s', '!Y-m-d H:i:s.u'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date && (! $errors || (! $errors['warning_count'] && ! $errors['error_count']))) {
                return $date->format('d M Y');
            }
        }

        return $value;
    }
}
