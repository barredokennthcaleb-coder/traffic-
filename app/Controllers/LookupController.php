<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class LookupController extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        $queryParam = $this->request->getGet('query');
        $queryStr = is_string($queryParam) ? mb_substr(trim($queryParam), 0, 100) : '';

        $records = [];
        $searched = false;
        $stats = [
            'total'           => 0,
            'pending_count'   => 0,
            'pending_amount'  => 0.00,
            'paid_count'      => 0,
            'paid_amount'     => 0.00,
            'violator_name'   => '',
            'license_plate'   => '',
            'violation_count' => 0,
            'grand_total'     => 0.00,
        ];

        if ($queryStr !== '') {
            $searched = true;
            $builder  = $db->table('violations');

            // One row per offense is grouped to one row per ticket here; the
            // plain list stays as it was. The per-offense pairing is fetched
            // separately below, because GROUP_CONCAT's SEPARATOR only accepts a
            // string literal in MariaDB and a delimiter that collides with a
            // violation name would corrupt the list.
            // violator_id travels with each ticket so the results can be grouped
            // per person: a name search may match two different drivers, and
            // their fines must not be added together.
            $builder->select('ticket_id, MAX(violator_id) as violator_id, MAX(driver_name) as driver_name, MAX(first_name) as first_name, MAX(last_name) as last_name, MAX(address) as address, MAX(license_plate) as license_plate, MAX(location) as location, MAX(violation_date) as violation_date, MAX(created_at) as created_at, GROUP_CONCAT(violation_type SEPARATOR ", ") as violation_type, SUM(penalty_amount) as penalty_amount, MAX(status) as status, MAX(receipt_number) as receipt_number');
            $builder->groupStart()
                ->like('license_plate', $queryStr)
                ->orLike('driver_name', $queryStr)
                ->orLike('first_name', $queryStr)
                ->orLike('last_name', $queryStr)
                ->orLike('ticket_id', $queryStr)
                ->orLike('license_number', $queryStr)
                ->groupEnd();

            $builder->groupBy('ticket_id');
            $builder->orderBy('MAX(id)', 'DESC');
            // Bound the result set: the public lookup has no authentication, so
            // a broad query must not be able to materialize the whole table.
            $builder->limit(200);
            $records = $builder->get()->getResultArray();

            // Every offense on each found ticket, in the order it was written,
            // so each fine can be shown against the name it belongs to.
            if ($records !== []) {
                $ticketIds = array_values(array_filter(array_unique(array_column($records, 'ticket_id'))));

                if ($ticketIds !== []) {
                    $offenseRows = $db->table('violations')
                        ->select('ticket_id, violation_type, penalty_amount')
                        ->whereIn('ticket_id', $ticketIds)
                        ->orderBy('id', 'ASC')
                        ->get()
                        ->getResultArray();

                    $byTicket = [];

                    foreach ($offenseRows as $offense) {
                        $key = (string) ($offense['ticket_id'] ?? '');

                        if ($key !== '') {
                            $byTicket[$key][] = [
                                'type'   => (string) $offense['violation_type'],
                                'amount' => (float) $offense['penalty_amount'],
                            ];
                        }
                    }

                    foreach ($records as $i => $r) {
                        $records[$i]['breakdown'] = $byTicket[(string) ($r['ticket_id'] ?? '')] ?? [];
                    }
                }

                // Repeat-offender highlighting: how many citation tickets each
                // person matched here carries on the full register, not just
                // the rows this search happened to return. Void (cancelled)
                // citations do not count as committed records, the same rule
                // the rest of the system applies. People matched by name only
                // (legacy rows without a violator link) are counted by name.
                $violatorIds = [];
                $legacyNames = [];

                foreach ($records as $r) {
                    $violatorId = (int) ($r['violator_id'] ?? 0);

                    if ($violatorId > 0) {
                        $violatorIds[$violatorId] = true;
                    } else {
                        $legacyName = trim((string) ($r['driver_name'] ?? ''));

                        if ($legacyName !== '') {
                            $legacyNames[$legacyName] = true;
                        }
                    }
                }

                $citationsByPerson = [];
                $liveTicketCount   = "COUNT(DISTINCT CASE WHEN status <> 'Cancelled' THEN ticket_id END)";

                if ($violatorIds !== []) {
                    $countRows = $db->table('violations')
                        ->select('violator_id, ' . $liveTicketCount . ' AS total_citations', false)
                        ->whereIn('violator_id', array_keys($violatorIds))
                        ->groupBy('violator_id')
                        ->get()
                        ->getResultArray();

                    foreach ($countRows as $countRow) {
                        $citationsByPerson[(int) $countRow['violator_id']] = (int) $countRow['total_citations'];
                    }
                }

                if ($legacyNames !== []) {
                    $countRows = $db->table('violations')
                        ->select('driver_name, ' . $liveTicketCount . ' AS total_citations', false)
                        ->whereIn('driver_name', array_keys($legacyNames))
                        ->groupBy('driver_name')
                        ->get()
                        ->getResultArray();

                    foreach ($countRows as $countRow) {
                        $citationsByPerson['name:' . (string) $countRow['driver_name']] = (int) $countRow['total_citations'];
                    }
                }

                foreach ($records as $i => $r) {
                    $violatorId = (int) ($r['violator_id'] ?? 0);
                    $personKey  = $violatorId > 0
                        ? (string) $violatorId
                        : 'name:' . trim((string) ($r['driver_name'] ?? ''));

                    $records[$i]['total_citations'] = $citationsByPerson[$personKey] ?? 1;
                }
            }

            $stats['total'] = count($records);

            foreach ($records as $r) {
                $status = strtolower($r['status'] ?? '');
                $amount = (float) ($r['penalty_amount'] ?? 0);

                if ($stats['violator_name'] === '' && !empty($r['driver_name'])) {
                    $stats['violator_name'] = $r['driver_name'];
                }
                if ($stats['license_plate'] === '' && !empty($r['license_plate'])) {
                    $stats['license_plate'] = strtoupper($r['license_plate']);
                }

                if ($status === 'paid') {
                    $stats['paid_count']++;
                    $stats['paid_amount'] += $amount;
                } else if ($status === 'pending') {
                    $stats['pending_count']++;
                    $stats['pending_amount'] += $amount;
                }

                // How many offenses sit behind the figures on screen, and the
                // sum of every Fine Amount column in this result set.
                $offenses = $r['breakdown'] ?? [];

                $stats['violation_count'] += $offenses === [] ? 1 : count($offenses);
                $stats['grand_total']     += $amount;
            }
        }

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return view('lookup/index', [
            'query'    => $queryStr,
            'searched' => $searched,
            'records'  => $records,
            'stats'    => $stats,
        ]);
    }
}
