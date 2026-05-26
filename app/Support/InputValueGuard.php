<?php

namespace App\Support;

class InputValueGuard
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $ignoredFields
     * @return array{attribute: string, message: string}|null
     */
    public static function findFirstViolation(array $payload, array $ignoredFields = []): ?array
    {
        foreach ($payload as $key => $value) {
            $violation = self::inspectValue($value, (string) $key, $ignoredFields);
            if ($violation !== null) {
                return $violation;
            }
        }

        return null;
    }

    public static function containsForbiddenMarkup(mixed $value): bool
    {
        if (!is_scalar($value) && $value !== null) {
            return false;
        }

        $stringValue = trim((string) ($value ?? ''));
        if ($stringValue === '') {
            return false;
        }

        $decodedValue = html_entity_decode($stringValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_match('/<\s*\/?\s*[a-z!][^>]*>/iu', $decodedValue) === 1
            || preg_match('/&lt;\s*\/?\s*[a-z!][^&]*&gt;/iu', $stringValue) === 1
            || preg_match('/\b(?:javascript|vbscript)\s*:/iu', $decodedValue) === 1
            || preg_match('/\bon[a-z]{3,}\s*=\s*/iu', $decodedValue) === 1;
    }

    public static function violationMessage(string $attribute): string
    {
        $label = str_replace(['.', '_'], [' ', ' '], $attribute);

        return ucfirst($label) . ' contains disallowed HTML or script content.';
    }

    /**
     * @param  array<int, string>  $ignoredFields
     * @return array{attribute: string, message: string}|null
     */
    private static function inspectValue(mixed $value, string $path, array $ignoredFields): ?array
    {
        if (self::shouldIgnore($path, $ignoredFields)) {
            return null;
        }

        if (is_array($value)) {
            foreach ($value as $key => $nestedValue) {
                $nestedPath = $path . '.' . (string) $key;
                $violation = self::inspectValue($nestedValue, $nestedPath, $ignoredFields);
                if ($violation !== null) {
                    return $violation;
                }
            }

            return null;
        }

        if (!is_scalar($value) && $value !== null) {
            return null;
        }

        if (!self::containsForbiddenMarkup($value)) {
            return null;
        }

        return [
            'attribute' => $path,
            'message' => self::violationMessage($path),
        ];
    }

    /**
     * @param  array<int, string>  $ignoredFields
     */
    private static function shouldIgnore(string $path, array $ignoredFields): bool
    {
        $segments = explode('.', $path);

        foreach ($segments as $segment) {
            if (in_array($segment, $ignoredFields, true)) {
                return true;
            }
        }

        return false;
    }
}