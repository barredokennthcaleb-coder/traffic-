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
        // Strip punctuation before the placeholder check so variants such as
        // "N/A.", "N.A." or "n.a" are recognized as placeholders too — otherwise
        // they collapse to "NA" and merge different people under one key.
        $value = (string) preg_replace('/[^A-Z0-9]/', '', strtoupper(self::clean($value)));

        if ($value === '' || self::isPlaceholder($value)) {
            return '';
        }

        return $value;
    }

    public static function plateKey(?string $value): string
    {
        $value = (string) preg_replace('/[^A-Z0-9]/', '', strtoupper(self::clean($value)));

        if ($value === '' || self::isPlaceholder($value)) {
            return '';
        }

        return $value;
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

        // Invalid UTF-8 would make json_encode() return false, collapsing all such
        // rows onto the same md5 of an empty string and merging unrelated people.
        return 'tmp:' . md5(json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE) ?: serialize($data));
    }

    /**
     * Name for ledgers and lists: first name, middle initial, last name.
     *
     * The middle name is shortened to one letter, which is how a citation
     * ledger is normally written. A recorded "batongbakal" reads as "B." and a
     * recorded "Caleb T." reads as "C." — one initial per person, not one
     * letter per word, which is what turned "Caleb T." into "C.T.".
     * The complete middle name stays on the violator's record and on the
     * printed citation. Legacy rows that carry nothing but the free-text
     * driver name are split the same way.
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

            if (count($parts) === 2) {
                return $parts[0] . ' ' . $parts[1];
            }

            $last = (string) array_pop($parts);
            $secondToLast = end($parts);
            // If the penultimate part is a single-letter initial (e.g. 't.' or 't') and earlier parts are full names
            if (count($parts) >= 2 && mb_strlen(trim((string) $secondToLast, " .")) === 1) {
                $middle = (string) array_pop($parts);
                $first  = implode(' ', $parts);
            } else {
                $first  = (string) array_shift($parts);
                $middle = implode(' ', $parts);
            }
        }

        $words = preg_split('/\s+/', $middle) ?: [];
        $words = array_values(array_filter($words, static fn ($w) => $w !== ''));

        if (count($words) >= 2) {
            $lastWord = end($words);
            $lastWordClean = trim((string) $lastWord, " .");
            // If the last word of middle is a single-letter initial (e.g. "t." or "t") and earlier words are full names (e.g. "caleb")
            // then earlier words are part of a double first name, not a middle name.
            if (mb_strlen($lastWordClean) === 1) {
                $doubleParts = array_slice($words, 0, -1);
                $first = trim($first . ' ' . implode(' ', $doubleParts));
                $middle = strtoupper($lastWordClean) . '.';
            } else {
                // Otherwise keep the first initial of the middle surname
                $firstWordClean = trim((string) ($words[0] ?? ''), " .");
                $middle = $firstWordClean !== '' ? strtoupper(mb_substr($firstWordClean, 0, 1)) . '.' : '';
            }
        } elseif (count($words) === 1) {
            $word = trim((string) $words[0], " .");
            $middle = $word !== '' ? strtoupper(mb_substr($word, 0, 1)) . '.' : '';
        } else {
            $middle = '';
        }

        // Cleanly format casing so lowercase entries become proper names
        $formatWord = static function(string $str): string {
            if ($str === '') return '';
            if (strtolower($str) === $str) {
                return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
            }
            return $str;
        };

        $firstFormatted = implode(' ', array_map($formatWord, explode(' ', $first)));
        $lastFormatted  = implode(' ', array_map($formatWord, explode(' ', $last)));

        return trim(implode(' ', array_filter([$firstFormatted, $middle, $lastFormatted])));
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
