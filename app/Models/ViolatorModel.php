<?php

namespace App\Models;

use App\Libraries\ViolatorIdentity;
use CodeIgniter\Model;
use RuntimeException;

/**
 * Violator-centred view over the existing violations table.
 *
 * One violator row is linked to many issued tickets (each ticket can contain
 * several infraction rows). Attempts, fines and balances are always calculated
 * from the linked tickets so they stay accurate after edits, cancellations and
 * payments.
 */
class ViolatorModel extends Model
{
    protected $table            = 'violators';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'identity_key',
        'license_key',
        'name_key',
        'plate_key',
        'license_number',
        'driver_name',
        'first_name',
        'middle_name',
        'last_name',
        'age',
        'address',
        'license_plate',
        'owner_name',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Resolve the violator for an issued ticket, creating the record only when
     * no reliable match exists. A preferred violator id (selected from a
     * violator profile) always wins.
     */
    public function resolveForTicket(array $data, ?int $preferredId = null, ?string $ticketId = null): int
    {
        if ($preferredId !== null && $preferredId > 0) {
            $preferred = $this->find($preferredId);

            if ($preferred) {
                $this->updateSnapshot((int) $preferred['id'], $data);

                return (int) $preferred['id'];
            }
        }

        $licenseKey = ViolatorIdentity::licenseKey($data['license_number'] ?? null);
        $nameKey    = ViolatorIdentity::nameKey(
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['driver_name'] ?? null
        );
        $plateKey   = ViolatorIdentity::plateKey($data['license_plate'] ?? null);

        if ($licenseKey !== '') {
            $match = $this->where('license_key', $licenseKey)->orderBy('id', 'ASC')->first();

            if ($match) {
                $this->updateSnapshot((int) $match['id'], $data);

                return (int) $match['id'];
            }
        }

        if ($nameKey !== '' && $plateKey !== '') {
            $match = $this->where('name_key', $nameKey)
                ->where('plate_key', $plateKey)
                ->orderBy('id', 'ASC')
                ->first();

            if ($match) {
                $this->updateSnapshot((int) $match['id'], $data);

                return (int) $match['id'];
            }
        }

        $identityKey = ViolatorIdentity::key($data, $ticketId);
        $snapshot    = $this->snapshotData($data);

        $existing = $this->where('identity_key', $identityKey)->first();

        if ($existing) {
            $this->updateSnapshot((int) $existing['id'], $data);

            return (int) $existing['id'];
        }

        $insert = array_merge($snapshot, [
            'identity_key' => $identityKey,
            'license_key'  => $licenseKey !== '' ? $licenseKey : null,
            'name_key'     => $nameKey !== '' ? $nameKey : null,
            'plate_key'    => $plateKey !== '' ? $plateKey : null,
        ]);

        if ($this->insert($insert)) {
            return (int) $this->getInsertID();
        }

        // A concurrent request may have created the same identity. Re-read it.
        $existing = $this->where('identity_key', $identityKey)->first();

        if ($existing) {
            return (int) $existing['id'];
        }

        throw new RuntimeException('Unable to create the violator record.');
    }

    /**
     * Keep the violator snapshot aligned with the latest ticket details.
     */
    public function updateSnapshot(int $violatorId, array $data): void
    {
        $violator = $this->find($violatorId);

        if (! $violator) {
            return;
        }

        $snapshot = $this->snapshotData($data, true);

        if (empty($snapshot)) {
            return;
        }

        $licenseKey = ViolatorIdentity::licenseKey($data['license_number'] ?? null);
        $nameKey    = ViolatorIdentity::nameKey(
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['driver_name'] ?? null
        );
        $plateKey   = ViolatorIdentity::plateKey($data['license_plate'] ?? null);

        if ($licenseKey !== '' && empty($violator['license_key'])) {
            $snapshot['license_key'] = $licenseKey;
        }

        if ($nameKey !== '' && empty($violator['name_key'])) {
            $snapshot['name_key'] = $nameKey;
        }

        if ($plateKey !== '' && empty($violator['plate_key'])) {
            $snapshot['plate_key'] = $plateKey;
        }

        // Upgrade a legacy name/plate identity when the license number becomes
        // available and no other violator already owns that license.
        if ($licenseKey !== '' && strpos((string) $violator['identity_key'], 'dl:') !== 0) {
            $newIdentity = 'dl:' . $licenseKey;
            $conflict    = $this->where('identity_key', $newIdentity)
                ->where('id !=', $violatorId)
                ->countAllResults();

            if ($conflict === 0) {
                $snapshot['identity_key'] = $newIdentity;
                $snapshot['license_key']  = $licenseKey;
            }
        }

        $this->update($violatorId, $snapshot);
    }

    /**
     * Ensure every infraction row on a ticket points at the same violator.
     */
    public function syncTicket(?string $ticketId, int $violatorId, array $data = []): void
    {
        if ($violatorId <= 0) {
            return;
        }

        if ($ticketId !== null && trim($ticketId) !== '') {
            $this->db->table('violations')
                ->where('ticket_id', trim($ticketId))
                ->update(['violator_id' => $violatorId]);
        }

        if (! empty($data)) {
            $this->updateSnapshot($violatorId, $data);
        }
    }

    public function pruneIfOrphan(int $violatorId): void
    {
        if ($violatorId <= 0) {
            return;
        }

        $tickets = $this->db->table('violations')
            ->where('violator_id', $violatorId)
            ->countAllResults();

        if ($tickets === 0) {
            $this->delete($violatorId);
        }
    }

    /**
     * Paginated violator list with live totals and overall payment status.
     *
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int, stats: array<string, mixed>}
     */
    public function getViolatorList(array $filters = [], int $perPage = 10, int $page = 1, ?int $officerId = null): array
    {
        $db       = $this->db;
        $where    = [];
        $having   = [];
        $params   = [];

        if ($officerId !== null && $officerId > 0) {
            $where[]  = 'EXISTS (SELECT 1 FROM violations ov WHERE ov.violator_id = v.id AND ov.officer_id = ?)';
            $params[] = $officerId;
        }

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $like     = '%' . $search . '%';
            $where[]  = '(v.driver_name LIKE ? OR v.license_number LIKE ? OR v.license_plate LIKE ? OR v.address LIKE ? OR EXISTS (SELECT 1 FROM violations sv WHERE sv.violator_id = v.id AND sv.ticket_id LIKE ?))';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $status = trim((string) ($filters['status'] ?? ''));

        $hideSettled = false;

        if (in_array($status, ['Paid', 'Pending', 'Cancelled'], true)) {
            $having[] = 'overall_status = ?';
            $params[] = $status;
        } else {
            // Working list: once every ticket of a violator is settled they drop
            // off the default view. Paid status filter brings them back.
            $hideSettled = true;
        }

        $attempts = trim((string) ($filters['attempts'] ?? ''));

        if ($attempts === '3plus') {
            $having[] = 'COUNT(DISTINCT vi.ticket_id) >= 3';
        } elseif (in_array($attempts, ['1', '2'], true)) {
            $having[] = 'COUNT(DISTINCT vi.ticket_id) = ?';
            $params[] = (int) $attempts;
        }

        $aggregateSelect = "
            v.id,
            v.identity_key,
            v.license_number,
            v.driver_name,
            v.first_name,
            v.middle_name,
            v.last_name,
            v.age,
            v.address,
            v.license_plate,
            v.owner_name,
            COUNT(DISTINCT vi.ticket_id) AS attempt_count,
            COUNT(DISTINCT CASE WHEN vi.status <> 'Cancelled' THEN vi.ticket_id END) AS active_ticket_count,
            COUNT(DISTINCT CASE WHEN vi.status = 'Cancelled' THEN vi.ticket_id END) AS cancelled_ticket_count,
            COUNT(DISTINCT CASE WHEN vi.status = 'Paid' THEN vi.ticket_id END) AS paid_ticket_count,
            COUNT(*) AS infraction_count,
            COALESCE(SUM(CASE WHEN vi.status <> 'Cancelled' THEN vi.penalty_amount ELSE 0 END), 0) AS total_fines,
            COALESCE(SUM(CASE WHEN vi.status = 'Paid' THEN vi.penalty_amount ELSE 0 END), 0) AS total_paid,
            COALESCE(SUM(CASE WHEN vi.status = 'Pending' THEN vi.penalty_amount ELSE 0 END), 0) AS balance,
            MAX(vi.violation_date) AS last_violation_at,
            CASE
                WHEN SUM(CASE WHEN vi.status <> 'Cancelled' THEN 1 ELSE 0 END) = 0 THEN 'Cancelled'
                WHEN SUM(CASE WHEN vi.status = 'Pending' THEN 1 ELSE 0 END) > 0 THEN 'Pending'
                ELSE 'Paid'
            END AS overall_status
        ";

        $baseSql = 'FROM violators v INNER JOIN violations vi ON vi.violator_id = v.id';

        if ($where !== []) {
            $baseSql .= ' WHERE ' . implode(' AND ', $where);
        }

        $baseSql .= ' GROUP BY v.id';

        if ($having !== []) {
            $baseSql .= ' HAVING ' . implode(' AND ', $having);
        }

        // Counting and paging use the working list (settled violators removed);
        // the KPI statistics below keep reporting on the whole population.
        $rowBaseSql = $baseSql;

        if ($hideSettled) {
            $rowBaseSql .= ($having !== [] ? ' AND ' : ' HAVING ') . "overall_status <> 'Paid'";
        }

        $countRow = $db->query(
            'SELECT COUNT(*) AS total FROM (SELECT ' . $aggregateSelect . ' ' . $rowBaseSql . ') AS grouped',
            $params
        )->getRowArray();

        $total = (int) ($countRow['total'] ?? 0);

        $sort = trim((string) ($filters['sort'] ?? 'last'));
        $orderBy = match ($sort) {
            'name'     => 'v.driver_name ASC, v.id ASC',
            'balance'  => 'balance DESC, last_violation_at DESC',
            'attempts' => 'attempt_count DESC, last_violation_at DESC',
            default    => 'last_violation_at DESC, v.driver_name ASC',
        };

        $perPage = max(0, $perPage);
        $page    = max(1, $page);
        $sql     = 'SELECT ' . $aggregateSelect . ' ' . $rowBaseSql . ' ORDER BY ' . $orderBy;

        if ($perPage > 0) {
            $offset = ($page - 1) * $perPage;
            $sql .= ' LIMIT ' . $perPage . ' OFFSET ' . $offset;
        }

        $rows = $db->query($sql, $params)->getResultArray();

        foreach ($rows as &$row) {
            $row['id']               = (int) $row['id'];
            $row['attempt_count']    = (int) $row['attempt_count'];
            $row['active_ticket_count'] = (int) $row['active_ticket_count'];
            $row['cancelled_ticket_count'] = (int) $row['cancelled_ticket_count'];
            $row['infraction_count'] = (int) $row['infraction_count'];
            $row['total_fines']      = (float) $row['total_fines'];
            $row['total_paid']       = (float) $row['total_paid'];
            $row['balance']          = (float) $row['balance'];
        }
        unset($row);

        $statsRow = $db->query(
            'SELECT COUNT(*) AS violators,
                    COALESCE(SUM(attempt_count), 0) AS attempts,
                    COALESCE(SUM(total_fines), 0) AS fines,
                    COALESCE(SUM(total_paid), 0) AS paid,
                    COALESCE(SUM(paid_ticket_count), 0) AS paid_tickets,
                    COALESCE(SUM(balance), 0) AS balance
             FROM (SELECT ' . $aggregateSelect . ' ' . $baseSql . ') AS stats',
            $params
        )->getRowArray();

        return [
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'stats'    => [
                'violators'   => (int) ($statsRow['violators'] ?? 0),
                'attempts'    => (int) ($statsRow['attempts'] ?? 0),
                'fines'       => (float) ($statsRow['fines'] ?? 0),
                'paid'        => (float) ($statsRow['paid'] ?? 0),
                'paid_tickets' => (int) ($statsRow['paid_tickets'] ?? 0),
                'balance'     => (float) ($statsRow['balance'] ?? 0),
            ],
        ];
    }

    /**
     * Head-counts behind the ledger filter chips: everyone still on the working
     * list (not fully settled) and, among them, those with a pending ticket.
     *
     * @return array{active: int, pending: int}
     */
    public function statusCounts(): array
    {
        $row = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN overall_status <> 'Paid' THEN 1 ELSE 0 END), 0) AS active_violators,
                COALESCE(SUM(CASE WHEN overall_status = 'Pending' THEN 1 ELSE 0 END), 0) AS pending_violators
             FROM (
                SELECT CASE
                    WHEN SUM(CASE WHEN status <> 'Cancelled' THEN 1 ELSE 0 END) = 0 THEN 'Cancelled'
                    WHEN SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) > 0 THEN 'Pending'
                    ELSE 'Paid'
                END AS overall_status
                FROM violations
                GROUP BY violator_id
             ) AS grouped"
        )->getRowArray();

        return [
            'active'  => (int) ($row['active_violators'] ?? 0),
            'pending' => (int) ($row['pending_violators'] ?? 0),
        ];
    }

    /**
     * Full violator dossier: profile, summary and ticket history.
     */
    public function getProfile(int $violatorId): ?array
    {
        $violator = $this->find($violatorId);

        if (! $violator) {
            return null;
        }

        $summary = $this->db->query(
            "SELECT
                COUNT(DISTINCT ticket_id) AS attempt_count,
                COUNT(DISTINCT CASE WHEN status <> 'Cancelled' THEN ticket_id END) AS active_ticket_count,
                COUNT(DISTINCT CASE WHEN status = 'Cancelled' THEN ticket_id END) AS cancelled_ticket_count,
                COUNT(*) AS infraction_count,
                COALESCE(SUM(CASE WHEN status <> 'Cancelled' THEN penalty_amount ELSE 0 END), 0) AS total_fines,
                COALESCE(SUM(CASE WHEN status = 'Paid' THEN penalty_amount ELSE 0 END), 0) AS total_paid,
                COALESCE(SUM(CASE WHEN status = 'Pending' THEN penalty_amount ELSE 0 END), 0) AS balance,
                MAX(violation_date) AS last_violation_at
             FROM violations
             WHERE violator_id = ?",
            [$violatorId]
        )->getRowArray();

        $summary = $summary ?: [];
        $summary['attempt_count']        = (int) ($summary['attempt_count'] ?? 0);
        $summary['active_ticket_count']  = (int) ($summary['active_ticket_count'] ?? 0);
        $summary['cancelled_ticket_count'] = (int) ($summary['cancelled_ticket_count'] ?? 0);
        $summary['infraction_count']     = (int) ($summary['infraction_count'] ?? 0);
        $summary['total_fines']          = (float) ($summary['total_fines'] ?? 0);
        $summary['total_paid']           = (float) ($summary['total_paid'] ?? 0);
        $summary['balance']              = (float) ($summary['balance'] ?? 0);
        $summary['overall_status']       = $this->overallStatus($summary);

        $rows = $this->db->query(
            "SELECT vi.*,
                    u.username AS officer_username,
                    TRIM(CONCAT(COALESCE(u.firstname, ''), ' ', COALESCE(u.lastname, ''))) AS officer_fullname
             FROM violations vi
             LEFT JOIN users u ON u.id = vi.officer_id
             WHERE vi.violator_id = ?
             ORDER BY vi.violation_date DESC, vi.id ASC",
            [$violatorId]
        )->getResultArray();

        // Settled tickets live in Payment History, so the dossier lists
        // outstanding citations only - the same rule the officer-facing
        // record applies. The summary and vehicles above/below still see
        // every row, so attempts and paid totals stay complete.
        $openRows = array_filter(
            $rows,
            static fn (array $row): bool => ($row['status'] ?? '') !== 'Paid'
        );

        $tickets  = $this->groupTickets($openRows);
        $vehicles = $this->collectVehicles($rows);

        return [
            'violator' => $violator,
            'summary'  => $summary,
            'tickets'  => $tickets,
            'vehicles' => $vehicles,
        ];
    }

    /**
     * Everything known about a violator for the record form: the person's own
     * details plus the vehicle/owner fields, which live on the citations.
     */
    public function getForPrefill(int $violatorId): ?array
    {
        $violator = $this->find($violatorId);

        if (! $violator) {
            return null;
        }

        $lastTicket = $this->db->table('violations')
            ->select('mtop_number, owner_name, owner_address, license_plate, age')
            ->where('violator_id', $violatorId)
            ->orderBy('violation_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($lastTicket) {
            $violator['mtop_number']   = (string) ($lastTicket['mtop_number'] ?? '');
            $violator['owner_address'] = (string) ($lastTicket['owner_address'] ?? '');

            if (trim((string) ($violator['owner_name'] ?? '')) === '') {
                $violator['owner_name'] = (string) ($lastTicket['owner_name'] ?? '');
            }
        }

        return $violator;
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array<string, mixed>>
     */
    private function groupTickets(array $rows): array
    {
        $tickets = [];

        foreach ($rows as $row) {
            $ticketId = trim((string) ($row['ticket_id'] ?? ''));
            $key      = $ticketId !== '' ? $ticketId : 'ID-' . (int) $row['id'];

            if (! isset($tickets[$key])) {
                $tickets[$key] = [
                    'id'             => (int) $row['id'],
                    'ticket_id'      => $ticketId !== '' ? $ticketId : 'N/A',
                    'violation_date' => $row['violation_date'] ?? null,
                    'location'       => trim((string) ($row['location'] ?? '')),
                    'officer_name'   => trim((string) ($row['officer_fullname'] ?? '')) ?: ($row['officer_username'] ?? ''),
                    'receipt_number' => '',
                    'payment_method' => '',
                    'paid_date'      => '',
                    'notes'          => trim((string) ($row['notes'] ?? '')),
                    'violations'     => [],
                    'total_amount'   => 0.0,
                    'paid_amount'    => 0.0,
                    'balance'        => 0.0,
                    'statuses'       => [],
                ];
            }

            $amount = (float) ($row['penalty_amount'] ?? 0);

            $tickets[$key]['violations'][] = [
                'id'              => (int) $row['id'],
                'type'            => (string) ($row['violation_type'] ?? 'Traffic Infraction'),
                'amount'          => $amount,
                'status'          => (string) ($row['status'] ?? 'Pending'),
                'violation_date'  => $row['violation_date'] ?? null,
            ];
            $tickets[$key]['total_amount'] += $amount;
            $tickets[$key]['statuses'][]   = (string) ($row['status'] ?? 'Pending');

            if (! empty($row['receipt_number'])) {
                $tickets[$key]['receipt_number'] = (string) $row['receipt_number'];
            }

            if (! empty($row['payment_method'])) {
                $tickets[$key]['payment_method'] = (string) $row['payment_method'];
            }

            if (! empty($row['paid_date'])) {
                $tickets[$key]['paid_date'] = (string) $row['paid_date'];
            }

            if (strtotime((string) ($row['violation_date'] ?? '')) > strtotime((string) ($tickets[$key]['violation_date'] ?? ''))) {
                $tickets[$key]['violation_date'] = $row['violation_date'];
            }

            if ($tickets[$key]['location'] === '' && ! empty($row['location'])) {
                $tickets[$key]['location'] = (string) $row['location'];
            }
        }

        foreach ($tickets as &$ticket) {
            $statuses = array_values(array_unique($ticket['statuses']));

            if ($statuses === ['Cancelled']) {
                $status = 'Cancelled';
            } elseif (in_array('Pending', $statuses, true) || $statuses === []) {
                $status = 'Pending';
            } elseif (in_array('Paid', $statuses, true)) {
                $status = 'Paid';
            } else {
                $status = 'Pending';
            }

            $ticket['status'] = $status;
            unset($ticket['statuses']);

            if ($status === 'Paid') {
                $ticket['paid_amount'] = $ticket['total_amount'];
                $ticket['balance']     = 0.0;
            } elseif ($status === 'Pending') {
                $ticket['paid_amount'] = 0.0;
                $ticket['balance']     = $ticket['total_amount'];
            } else {
                $ticket['paid_amount'] = 0.0;
                $ticket['balance']     = 0.0;
            }
        }
        unset($ticket);

        $tickets = array_values($tickets);

        usort($tickets, static function (array $a, array $b): int {
            return strtotime((string) ($b['violation_date'] ?? '0')) <=> strtotime((string) ($a['violation_date'] ?? '0'));
        });

        return $tickets;
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array{plate: string, count: int, last_seen: string}>
     */
    private function collectVehicles(array $rows): array
    {
        $vehicles = [];

        foreach ($rows as $row) {
            $plate = ViolatorIdentity::clean($row['license_plate'] ?? '');

            if ($plate === '' || ViolatorIdentity::isPlaceholder($plate)) {
                continue;
            }

            $key = ViolatorIdentity::plateKey($plate);

            if (! isset($vehicles[$key])) {
                $vehicles[$key] = ['plate' => strtoupper($plate), 'count' => 0, 'last_seen' => ''];
            }

            $vehicles[$key]['count']++;

            if (strtotime((string) ($row['violation_date'] ?? '')) > strtotime((string) $vehicles[$key]['last_seen'])) {
                $vehicles[$key]['last_seen'] = (string) ($row['violation_date'] ?? '');
            }
        }

        $vehicles = array_values($vehicles);

        usort($vehicles, static fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        return $vehicles;
    }

    private function overallStatus(array $summary): string
    {
        if (($summary['attempt_count'] ?? 0) === 0) {
            return 'No Records';
        }

        if (($summary['active_ticket_count'] ?? 0) === 0) {
            return 'Cancelled';
        }

        return ($summary['balance'] ?? 0) > 0 ? 'Pending' : 'Paid';
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotData(array $data, bool $onlyFilled = false): array
    {
        $snapshot = [
            'driver_name'    => ViolatorIdentity::clean($data['driver_name'] ?? ''),
            'first_name'     => ViolatorIdentity::clean($data['first_name'] ?? ''),
            'middle_name'    => ViolatorIdentity::clean($data['middle_name'] ?? ''),
            'last_name'      => ViolatorIdentity::clean($data['last_name'] ?? ''),
            'age'            => isset($data['age']) && $data['age'] !== '' ? (int) $data['age'] : null,
            'address'        => ViolatorIdentity::clean($data['address'] ?? ''),
            'license_number' => ViolatorIdentity::clean($data['license_number'] ?? ''),
            'license_plate'  => strtoupper(ViolatorIdentity::clean($data['license_plate'] ?? '')),
            'owner_name'     => ViolatorIdentity::clean($data['owner_name'] ?? ''),
        ];

        if (! $snapshot['driver_name']) {
            $snapshot['driver_name'] = trim(implode(' ', array_filter([
                $snapshot['first_name'],
                $snapshot['middle_name'],
                $snapshot['last_name'],
            ])));
        }

        if ($onlyFilled) {
            return array_filter($snapshot, static function ($value): bool {
                return $value !== null && $value !== '';
            });
        }

        return $snapshot;
    }
}
