<?php

if (! function_exists('normalize_tct_number')) {
    /**
     * Normalize user input to official TCT format: YYYY-XXXXXXX (7-digit serial).
     */
    function normalize_tct_number(?string $value, ?int $defaultYear = null): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{1,7})$/', $value, $matches)) {
            return sprintf('%s-%07d', $matches[1], (int) $matches[2]);
        }

        if (preg_match('/^\d{1,7}$/', $value)) {
            $year = $defaultYear ?? (int) date('Y');

            return sprintf('%d-%07d', $year, (int) $value);
        }

        return null;
    }
}

if (! function_exists('parse_tct_number')) {
    /**
     * Parse a stored ticket id into prefix (YYYY-) and serial (XXXXXXX) for display.
     */
    function parse_tct_number(?string $ticketId, ?int $violationId = null, ?string $violationDate = null): array
    {
        $ticketId = trim((string) $ticketId);

        if ($ticketId !== '' && preg_match('/^(\d{4})-(\d{1,7})$/', $ticketId, $matches)) {
            return [
                'prefix' => $matches[1] . '-',
                'serial' => str_pad($matches[2], 7, '0', STR_PAD_LEFT),
                'full'   => sprintf('%s-%07d', $matches[1], (int) $matches[2]),
            ];
        }

        $year = $violationDate ? (int) date('Y', strtotime($violationDate)) : (int) date('Y');
        $serial = str_pad((string) max(0, (int) $violationId), 7, '0', STR_PAD_LEFT);

        return [
            'prefix' => $year . '-',
            'serial' => $serial,
            'full'   => $year . '-' . $serial,
        ];
    }
}

if (! function_exists('format_tct_number_display')) {
    /**
     * Render TCT number with year prefix and red serial, matching the official ticket.
     */
    function format_tct_number_display(?string $ticketId, ?int $violationId = null, ?string $violationDate = null): string
    {
        $parsed = parse_tct_number($ticketId, $violationId, $violationDate);

        return esc($parsed['prefix']) . '<span class="tct-red-no">' . esc($parsed['serial']) . '</span>';
    }
}

if (! function_exists('tct_split_name')) {
    /**
     * Split a citation name into First / Middle / Last so the printed ticket
     * can line each part up above its own (First) / (Middle) / (Last) label.
     *
     * The stored parts win whenever one of them exists; a legacy full name is
     * parsed only when they are all missing — first word = first name,
     * last word = last name, anything in between = middle name.
     *
     * @return array{first: string, middle: string, last: string}
     */
    function tct_split_name(?string $first = null, ?string $middle = null, ?string $last = null, ?string $full = null): array
    {
        $first  = trim((string) $first);
        $middle = trim((string) $middle);
        $last   = trim((string) $last);

        if ($first !== '' || $last !== '') {
            return ['first' => $first, 'middle' => $middle, 'last' => $last];
        }

        $parts = preg_split('/\s+/', trim((string) $full)) ?: [];
        $parts = array_values(array_filter($parts, static fn ($part) => $part !== ''));

        if ($parts === []) {
            return ['first' => '', 'middle' => '', 'last' => ''];
        }

        if (count($parts) === 1) {
            return ['first' => $parts[0], 'middle' => '', 'last' => ''];
        }

        if (count($parts) === 2) {
            return ['first' => $parts[0], 'middle' => '', 'last' => $parts[1]];
        }

        return [
            'first'  => $parts[0],
            'middle' => implode(' ', array_slice($parts, 1, -1)),
            'last'   => $parts[count($parts) - 1],
        ];
    }
}

if (! function_exists('kabankalan_areas')) {
    /**
     * Coverage areas (barangays) served by the CTRAMO Kabankalan stations.
     * Used by the citation place selector and the officer jurisdiction panel.
     */
    function kabankalan_areas(): array
    {
        return [
            'Barangay 1 (Poblacion)', 'Barangay 2 (Poblacion)', 'Barangay 3 (Poblacion)', 'Barangay 4 (Poblacion)',
            'Barangay 5 (Poblacion)', 'Barangay 6 (Poblacion)', 'Barangay 7 (Poblacion)', 'Barangay 8 (Poblacion)',
            'Barangay 9 (Poblacion)', 'Bantayan', 'Binicuil', 'Camansi', 'Camingawan', 'Camugao', 'Carol-an',
            'Daan Banua', 'Hilamonan', 'Inapoy', 'Linao', 'Locotan', 'Magballo', 'Oringao', 'Orong',
            'Pinaguinpinan', 'Salong', 'Tabugon', 'Tagoc', 'Tagukon', 'Talubangi', 'Tampalon', 'Tan-Awan', 'Tapi',
        ];
    }
}
