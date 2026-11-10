<?php

namespace App\Libraries;

final class CitationReportGrouper
{
    public static function groupByTicket(array $records): array
    {
        $groups = [];

        foreach ($records as $index => $record) {
            $ticketId = trim((string) ($record['ticket_id'] ?? ''));
            $key = $ticketId !== ''
                ? 'ticket:' . $ticketId
                : 'record:' . (string) ($record['id'] ?? $index);

            if (! isset($groups[$key])) {
                $groups[$key] = $record;
                $groups[$key]['violations'] = [];
                $groups[$key]['status_counts'] = [];
                $groups[$key]['receipt_numbers'] = [];
                $groups[$key]['amount_total'] = 0.0;
            }

            $status = (string) ($record['status'] ?? 'Pending');
            $groups[$key]['violations'][] = [
                'name'   => (string) (! empty($record['violation_name']) ? $record['violation_name'] : ($record['violation_type'] ?? 'Unknown offense')),
                'status' => $status,
                'amount' => (float) ($record['penalty_amount'] ?? 0),
            ];
            $groups[$key]['status_counts'][$status] = ($groups[$key]['status_counts'][$status] ?? 0) + 1;

            if ($status !== 'Cancelled') {
                $groups[$key]['amount_total'] += (float) ($record['penalty_amount'] ?? 0);
            }

            $receiptNumber = trim((string) ($record['receipt_number'] ?? ''));
            if ($receiptNumber !== '' && ! in_array($receiptNumber, $groups[$key]['receipt_numbers'], true)) {
                $groups[$key]['receipt_numbers'][] = $receiptNumber;
            }
        }

        foreach ($groups as &$group) {
            $group['receipt_number'] = implode(', ', $group['receipt_numbers']);
            unset($group['receipt_numbers']);
        }
        unset($group);

        return array_values($groups);
    }

    /**
     * Group by violator, then by ticket, so one person's whole record reads as
     * a single organized block instead of being scattered across the list.
     *
     * Every offense stays its own line with its own status, fine and payment
     * date — nothing is merged into a "Paid ×N" count — while the blocks keep
     * the same order the records arrive in (most recent violation first).
     *
     * Records linked to a violator row group on that id; legacy rows fall back
     * to the shared identity key (licence, then name + plate, then ticket).
     */
    public static function groupByViolator(array $records): array
    {
        $groups = [];

        foreach ($records as $index => $record) {
            $violatorId = (int) ($record['violator_id'] ?? 0);
            $ticketId   = trim((string) ($record['ticket_id'] ?? ''));

            $key = $violatorId > 0
                ? 'violator:' . $violatorId
                : 'identity:' . ViolatorIdentity::key($record, $ticketId !== '' ? $ticketId : null);

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'violator_id'     => $violatorId > 0 ? $violatorId : null,
                    'driver_name'     => self::displayName($record),
                    'license_number'  => trim((string) ($record['license_number'] ?? '')),
                    'license_plate'   => trim((string) ($record['license_plate'] ?? '')),
                    'violation_count' => 0,
                    'amount_total'    => 0.0,
                    'paid_amount'     => 0.0,
                    'pending_amount'  => 0.0,
                    'status_counts'   => [],
                    'tickets'         => [],
                ];
            }

            $ticketKey = $ticketId !== ''
                ? 'ticket:' . $ticketId
                : 'record:' . (string) ($record['id'] ?? $index);

            if (! isset($groups[$key]['tickets'][$ticketKey])) {
                $groups[$key]['tickets'][$ticketKey] = [
                    'ticket_id'      => $ticketId,
                    'receipt_number' => '',
                    'officer_name'   => '',
                    'violation_date' => (string) ($record['violation_date'] ?? ''),
                    'location'       => trim((string) ($record['location'] ?? '')),
                    'paid_date'      => null,
                    'amount_total'   => 0.0,
                    'paid_amount'    => 0.0,
                    'pending_amount' => 0.0,
                    'status_counts'  => [],
                    'violations'     => [],
                ];
            }

            $status = (string) ($record['status'] ?? 'Pending');
            $amount = (float) ($record['penalty_amount'] ?? 0);
            $paidDate = trim((string) ($record['paid_date'] ?? ''));

            $groups[$key]['tickets'][$ticketKey]['violations'][] = [
                'name'      => (string) (! empty($record['violation_name']) ? $record['violation_name'] : ($record['violation_type'] ?? 'Unknown offense')),
                'status'    => $status,
                'amount'    => $amount,
                'paid_date' => $paidDate !== '' ? $paidDate : null,
            ];

            $groups[$key]['status_counts'][$status] = ($groups[$key]['status_counts'][$status] ?? 0) + 1;
            $groups[$key]['tickets'][$ticketKey]['status_counts'][$status] =
                ($groups[$key]['tickets'][$ticketKey]['status_counts'][$status] ?? 0) + 1;
            $groups[$key]['violation_count']++;

            // A cancelled citation is void and adds nothing to the money, the
            // same rule the report totals follow. Each ticket also keeps its
            // own paid and pending money, so a violator holding several
            // citations shows exactly which one is settled and which is not.
            if ($status !== 'Cancelled') {
                $groups[$key]['amount_total'] += $amount;
                $groups[$key]['tickets'][$ticketKey]['amount_total'] += $amount;

                if ($status === 'Paid') {
                    $groups[$key]['paid_amount'] += $amount;
                    $groups[$key]['tickets'][$ticketKey]['paid_amount'] += $amount;
                } else {
                    $groups[$key]['pending_amount'] += $amount;
                    $groups[$key]['tickets'][$ticketKey]['pending_amount'] += $amount;
                }
            }

            $receiptNumber = trim((string) ($record['receipt_number'] ?? ''));
            if ($receiptNumber !== '' && $groups[$key]['tickets'][$ticketKey]['receipt_number'] === '') {
                $groups[$key]['tickets'][$ticketKey]['receipt_number'] = $receiptNumber;
            }

            $officerName = trim((string) ($record['officer_name'] ?? ''));
            if ($officerName !== '' && $groups[$key]['tickets'][$ticketKey]['officer_name'] === '') {
                $groups[$key]['tickets'][$ticketKey]['officer_name'] = $officerName;
            }

            if ($paidDate !== '' && $groups[$key]['tickets'][$ticketKey]['paid_date'] === null) {
                $groups[$key]['tickets'][$ticketKey]['paid_date'] = $paidDate;
            }
        }

        foreach ($groups as &$group) {
            $group['tickets'] = array_values($group['tickets']);
        }
        unset($group);

        return array_values($groups);
    }

    /**
     * Display name for a grouped block: first + last name when the citation
     * carries them, otherwise the free-text driver name.
     */
    private static function displayName(array $record): string
    {
        $fullName = trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''));

        if ($fullName !== '') {
            return $fullName;
        }

        $driverName = trim((string) ($record['driver_name'] ?? ''));

        return $driverName !== '' ? $driverName : 'Unknown violator';
    }
}
