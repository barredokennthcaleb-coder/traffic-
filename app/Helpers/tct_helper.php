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
            $midWords = array_values(array_filter(preg_split('/\s+/', $middle) ?: [], static fn($w) => $w !== ''));
            if (count($midWords) >= 2 && mb_strlen(trim((string) end($midWords), " .")) === 1) {
                $lastMid = (string) array_pop($midWords);
                $first = trim($first . ' ' . implode(' ', $midWords));
                $middle = $lastMid;
            }
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

        $lastWord = $parts[count($parts) - 1];
        $penultimate = $parts[count($parts) - 2];
        // If penultimate is a single-letter initial (e.g. ['kenneth', 'caleb', 't.', 'barredo'])
        if (count($parts) >= 4 && mb_strlen(trim((string) $penultimate, " .")) === 1) {
            return [
                'first'  => implode(' ', array_slice($parts, 0, -2)),
                'middle' => $penultimate,
                'last'   => $lastWord,
            ];
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

if (! function_exists('sanction_label')) {
    /**
     * Name how many sanctions a violator carries, one per apprehension.
     *
     * "Offense" belongs to a violation type on a ticket, so a person's history
     * reads as a sanction record ("2nd Sanction") rather than an offense count.
     */
    function sanction_label(int $count): string
    {
        if ($count <= 0) {
            return 'No Sanction Record';
        }

        $lastTwo = $count % 100;

        $suffix = ($lastTwo >= 11 && $lastTwo <= 13)
            ? 'th'
            : match ($count % 10) {
                1       => 'st',
                2       => 'nd',
                3       => 'rd',
                default => 'th',
            };

        return $count . $suffix . ' Sanction';
    }
}

if (! function_exists('tct_standard_violations')) {
    /**
     * The offences printed on the official CTRAMO citation ticket (TCT).
     *
     * This is the single source of truth for the printed checklist: the
     * enforcer view, the print/roll layouts, the admin copy and the driver
     * copy all render this list, so they can never drift apart. It mirrors
     * the pre-printed C.O. # 2023-006 form, tricycle offences only.
     *
     * @return list<string>
     */
    function tct_standard_violations(): array
    {
        return [
            'Unlicensed driver',
            'Unregistered MV',
            'Colorum/Unfranchised Operation',
            'Invalid or Suspended/revoked/expired CR',
            'Out of Route',
            'Discourteous driver/conduct',
            'CR/OR not carried',
            'Unauthorized improvised plates',
            'No required MV parts/acc.',
            'No body (plate) number, for hire MV',
            'Allowing passenger on top of MV',
            'Reckless driving',
            'Obstruction',
            'Other Violations',
        ];
    }
}

if (! function_exists('tct_offence_matches')) {
    /**
     * Does a recorded offence name refer to the same offence as a printed
     * checklist line? Names are compared case-insensitively, and either side
     * may be the shorter form ("CR/OR Not Carried" vs "CR/OR not carried").
     */
    function tct_offence_matches(string $offence, string $standard): bool
    {
        $offence  = strtolower(trim($offence));
        $standard = strtolower(trim($standard));

        if ($offence === '' || $standard === '') {
            return false;
        }

        return $offence === $standard
            || strpos($offence, $standard) !== false
            || strpos($standard, $offence) !== false;
    }
}

if (! function_exists('tct_has_unlisted_violation')) {
    /**
     * True when a ticket carries an offence that has no line of its own on the
     * printed form, so it has to be reported under "Other Violations".
     *
     * @param list<string> $activeNames Offence names recorded on the ticket.
     */
    function tct_has_unlisted_violation(array $activeNames): bool
    {
        foreach ($activeNames as $offence) {
            $matchesAStandardLine = false;

            foreach (tct_standard_violations() as $standard) {
                if ($standard === 'Other Violations') {
                    continue;
                }

                if (tct_offence_matches((string) $offence, $standard)) {
                    $matchesAStandardLine = true;
                    break;
                }
            }

            if (! $matchesAStandardLine) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('tct_is_checked')) {
    /**
     * Whether one printed checklist line is ticked for this ticket.
     *
     * An offence outside the form is reported as "Other Violations", so that
     * line is ticked whenever the ticket carries anything unlisted — otherwise
     * a citation for an unlisted offence would print with no box ticked at all.
     *
     * @param list<string> $activeNames Offence names recorded on the ticket.
     */
    function tct_is_checked(string $standard, array $activeNames): bool
    {
        foreach ($activeNames as $offence) {
            if (tct_offence_matches((string) $offence, $standard)) {
                return true;
            }
        }

        return $standard === 'Other Violations' && tct_has_unlisted_violation($activeNames);
    }
}

if (! function_exists('tct_known_suffixes')) {
    /**
     * The name suffixes the ticket recognises after a last name.
     */
    function tct_known_suffixes(): array
    {
        return ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
    }
}

if (! function_exists('tct_split_name_suffix')) {
    /**
     * Pull a trailing suffix off a last name.
     *
     * "Dimagiba Jr." becomes ['last' => 'Dimagiba', 'suffix' => 'Jr.'], and
     * "Dimagiba" stays as it is with an empty suffix. The ticket form shows
     * the two parts in separate boxes, so the stored full name has to be
     * split again whenever it is loaded back into the form.
     *
     * @return array{last: string, suffix: string}
     */
    function tct_split_name_suffix(?string $lastName): array
    {
        $lastName = trim(preg_replace('/\s+/', ' ', (string) $lastName));

        foreach (tct_known_suffixes() as $suffix) {
            $bare    = rtrim($suffix, '.');
            $pattern = '/^(.*?)[\s,]+' . preg_quote($bare, '/') . '\.?$/i';

            if (preg_match($pattern, $lastName, $matches) === 1 && trim($matches[1]) !== '') {
                return ['last' => trim($matches[1]), 'suffix' => $suffix];
            }
        }

        return ['last' => $lastName, 'suffix' => ''];
    }
}

if (! function_exists('tct_join_name_suffix')) {
    /**
     * Join a last name and its suffix without ever writing the suffix twice.
     *
     * "Dimagiba" + "Jr" -> "Dimagiba Jr." while "Dimagiba Jr." + "" keeps the
     * suffix it already carries, so saving an older ticket does not drop it.
     */
    function tct_join_name_suffix(?string $lastName, ?string $suffix): string
    {
        $parts = tct_split_name_suffix($lastName);
        $suffix = trim((string) $suffix);

        if ($suffix === '') {
            $suffix = $parts['suffix'];
        } else {
            $bare = strtoupper(trim(rtrim($suffix, '.')));
            foreach (tct_known_suffixes() as $known) {
                if (strtoupper(rtrim($known, '.')) === $bare) {
                    $suffix = $known;
                    break;
                }
            }
        }

        return trim($parts['last'] . ' ' . $suffix);
    }
}

if (! function_exists('tricycle_icon')) {
    /**
     * Inline tricycle icon for plate pills, plate badges and hero plates.
     *
     * Bootstrap Icons has no tricycle glyph, so this is the system's own
     * sidecar-tricycle SVG (motorcycle front with a steering column and a
     * windowed sidecar cabin under a canopy). It draws in currentColor, so
     * it inherits the pill's text color exactly like the icon font it
     * replaced, and it sizes to 1em so existing font-size rules keep working.
     *
     * @param string $extraClass Optional spacing classes, e.g. "me-1".
     */
    function tricycle_icon(string $extraClass = ''): string
    {
        return '<svg class="tricycle-icon' . ($extraClass !== '' ? ' ' . esc($extraClass, 'attr') : '') . '"'
            . ' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="1em" height="1em"'
            . ' fill="currentColor" style="vertical-align:-.125em" aria-hidden="true" focusable="false">'
            . '<circle cx="3.9" cy="12.5" r="2.05" fill="none" stroke="currentColor" stroke-width="1.4"/>'
            . '<circle cx="11.9" cy="12.5" r="2.05" fill="none" stroke="currentColor" stroke-width="1.4"/>'
            . '<path d="M3.9 10.4 5.5 6.3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>'
            . '<path d="M4.6 6.3h1.7" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>'
            . '<path fill-rule="evenodd" d="M7.6 5.9h5.6c.6 0 1 .4 1 1v3.2c0 .6-.4 1-1 1H7.6c-.6 0-1-.4-1-1V6.9c0-.6.4-1 1-1Zm1.1 1.2v2h2.5v-2H8.7Z"/>'
            . '<rect x="6.3" y="4.3" width="7.7" height="1.6" rx=".8"/>'
            . '</svg>';
    }
}
