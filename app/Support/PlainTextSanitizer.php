<?php

namespace App\Support;

class PlainTextSanitizer
{
    public static function sanitize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $stringValue = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $stringValue = preg_replace('/<\s*(script|style|iframe|object|embed|svg|math)[^>]*>.*?<\s*\/\s*\1\s*>/isu', ' ', $stringValue) ?? $stringValue;
        $stringValue = preg_replace('/\bon[a-z]{3,}\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/iu', ' ', $stringValue) ?? $stringValue;
        $stringValue = preg_replace('/\b(?:javascript|vbscript|data:text\/html)\s*:/iu', ' ', $stringValue) ?? $stringValue;
        $stringValue = strip_tags($stringValue);
        $stringValue = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $stringValue) ?? $stringValue;
        $stringValue = trim(preg_replace('/\s+/u', ' ', $stringValue) ?? $stringValue);

        return $stringValue === '' ? null : $stringValue;
    }

    /**
     * @param  array<int, string>  $columns
     */
    public static function sanitizeColumns(array $attributes, array $columns): array
    {
        foreach ($columns as $column) {
            if (!array_key_exists($column, $attributes)) {
                continue;
            }

            $attributes[$column] = self::sanitize($attributes[$column]);
        }

        return $attributes;
    }
}