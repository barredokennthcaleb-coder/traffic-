<?php

namespace App\Libraries;

/**
 * Normalizes the identifying details captured on a citation so records that
 * belong to the same person can be matched without relying on a display name
 * alone.
 *
 * Matching order:
 *   1. Driver's license / permit number (most reliable).
 *   2. Normalized first + last name together with a real plate number.
 *   3. A ticket-only fallback used while migrating legacy rows that have no
 *      usable identifier (each such ticket stays its own violator record).
 */
class ViolatorIdentity
{
    private const PLACEHOLDERS = [
        '', 'NO', 'NONE', 'N/A', 'NA', 'NIL', 'NULL', 'UNKNOWN', 'TEMP', 'TBA', '-', '--', '---',
    ];

    public static function clean(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' ? '' : preg_replace('/\s+/', ' ', $value);
    }

    public static function licenseKey(?string $value): string
    {
        $value = strtoupper(self::clean($value));

        if (self::isPlaceholder($value)) {
            return '';
        }

        return (string) preg_replace('/[^A-Z0-9]/', '', $value);
    }

    public static function plateKey(?string $value): string
    {
        $value = strtoupper(self::clean($value));

        if (self::isPlaceholder($value)) {
            return '';
        }

        return (string) preg_replace('/[^A-Z0-9]/', '', $value);
    }

    public static function nameKey(?string $firstName, ?string $lastName, ?string $driverName = null): string
    {
        $first = self::namePart($firstName);
        $last  = self::namePart($lastName);

        if ($first !== '' && $last !== '') {
            return substr($first . ' ' . $last, 0, 190);
        }

        $driver = self::namePart($driverName);

        if ($driver === '') {
            return '';
        }

        $parts = explode(' ', $driver);

        if (count($parts) >= 2) {
            return substr($parts[0] . ' ' . $parts[count($parts) - 1], 0, 190);
        }

        return substr($driver, 0, 190);
    }

    /**
     * Build the stable key for a violator.
     */
    public static function key(array $data, ?string $ticketId = null): string
    {
        $license = self::licenseKey($data['license_number'] ?? null);

        if ($license !== '') {
            return 'dl:' . substr($license, 0, 100);
        }

        $name  = self::nameKey($data['first_name'] ?? null, $data['last_name'] ?? null, $data['driver_name'] ?? null);
        $plate = self::plateKey($data['license_plate'] ?? null);

        if ($name !== '' && $plate !== '') {
            $key = 'np:' . $name . '|' . $plate;

            return strlen($key) <= 191 ? $key : 'np-h:' . sha1($key);
        }

        $ticketId = self::clean($ticketId);

        if ($ticketId !== '') {
            $key = 'tk:' . $ticketId;

            return strlen($key) <= 191 ? $key : 'tk-h:' . sha1($key);
        }

        return 'tmp:' . md5(json_encode($data));
    }

    /**
     * Compact name for ledgers and lists: first name, middle initial(s), last name.
     *
     * The complete middle name stays on the violator's record and on the printed
     * citation; only the listing is shortened. Legacy rows that carry nothing but
     * the free-text driver name are split the same way.
     */
    public static function shortName(?string $firstName, ?string $middleName, ?string $lastName, ?string $driverName = null): string
    {
        $first  = self::clean($firstName);
        $middle = self::clean($middleName);
        $last   = self::clean($lastName);

        if ($first === '' || $last === '') {
            $parts = array_values(array_filter(
                preg_split('/\s+/', self::clean($driverName) ?: self::clean($first . ' ' . $last)) ?: [],
                static fn ($part) => $part !== ''
            ));

            if ($parts === []) {
                return '';
            }

            if (count($parts) === 1) {
                return $parts[0];
            }

            $first  = (string) array_shift($parts);
            $last   = (string) array_pop($parts);
            $middle = implode(' ', $parts);
        }

        $initials = '';

        foreach (preg_split('/\s+/', $middle) ?: [] as $word) {
            $word = trim($word, " .");

            if ($word !== '') {
                $initials .= strtoupper(mb_substr($word, 0, 1)) . '.';
            }
        }

        return trim(implode(' ', array_filter([$first, $initials, $last])));
    }

    /**
     * Prefer the most complete display name available in a set of rows.
     */
    public static function bestName(array $rows): string
    {
        $best = '';

        foreach ($rows as $row) {
            $name = self::clean(implode(' ', array_filter([
                $row['first_name'] ?? '',
                $row['middle_name'] ?? '',
                $row['last_name'] ?? '',
            ], static fn ($part) => trim((string) $part) !== '')));

            if ($name === '') {
                $name = self::clean($row['driver_name'] ?? '');
            }

            if (strlen($name) > strlen($best)) {
                $best = $name;
            }
        }

        return $best;
    }

    public static function firstValue(array $rows, string $field, bool $requireRealValue = false): string
    {
        foreach ($rows as $row) {
            $value = self::clean($row[$field] ?? '');
            if ($value === '') {
                continue;
            }

            if ($requireRealValue && self::isPlaceholder($value)) {
                continue;
            }

            return $value;
        }

        return '';
    }

    public static function isPlaceholder(?string $value): bool
    {
        return in_array(strtoupper(self::clean($value)), self::PLACEHOLDERS, true);
    }

    private static function namePart(?string $value): string
    {
        $value = strtolower(self::clean($value));

        if ($value === '') {
            return '';
        }

        $value = str_replace(['ñ', 'é', 'è', 'á', 'à', 'í', 'ì', 'ó', 'ò', 'ú', 'ù'], ['n', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u'], $value);

        return trim((string) preg_replace('/[^a-z0-9 ]/', ' ', $value));
    }
}
