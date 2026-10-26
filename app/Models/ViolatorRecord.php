<?php

namespace App\Models;

use CodeIgniter\Model;

class ViolatorRecord extends Model
{
    protected $table            = 'violations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'violator_id',
        'ticket_id', 
        'driver_name',
        'first_name',
        'middle_name',
        'last_name',
        'age',
        'address',
        'license_number',
        'license_plate', 
        'mtop_number',
        'owner_address',
        'owner_name',
        'acknowledgment',
        'officer_id', 
        'violation_type_id',
        'violation_type', 
        'penalty_amount', 
        'points',
        'status', 
        'violation_date', 
        'created_by', 
        'paid_date', 
        'payment_method', 
        'receipt_number',
        'remarks',
        'location',
        'notes',
        'driver_signature',
        'officer_signature'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id'              => 'permit_empty|integer',
        'violator_id'     => 'permit_empty|integer',
        'ticket_id'       => 'permit_empty',
        'first_name'      => 'required|max_length[100]',
        'middle_name'     => 'permit_empty|max_length[100]',
        'last_name'       => 'required|max_length[100]',
        'age'             => 'required|integer|greater_than_equal_to[16]|less_than_equal_to[120]',
        'address'         => 'required|max_length[255]',
        'driver_name'     => 'required|max_length[255]',
        'license_plate'   => 'required|max_length[20]',
        'license_number'  => 'permit_empty|max_length[50]',
        'mtop_number'     => 'permit_empty|max_length[50]',
        'owner_address'   => 'permit_empty|max_length[255]',
        'owner_name'      => 'permit_empty|max_length[150]',
        'acknowledgment'  => 'permit_empty|max_length[50]',
        'officer_id'      => 'permit_empty|integer',
        'violation_type_id' => 'permit_empty|integer',
        'violation_type'  => 'required|max_length[255]',
        'penalty_amount'  => 'required|decimal',
        'points'          => 'permit_empty|integer',
        'status'          => 'required|in_list[Pending,Paid,Cancelled]',
        'violation_date'  => 'required|valid_date',
        'created_by'      => 'permit_empty|integer',
        'paid_date'       => 'permit_empty|valid_date',
        'payment_method'  => 'permit_empty',
        'receipt_number'  => 'permit_empty',
        'remarks'         => 'permit_empty',
        'location'        => 'permit_empty',
        'notes'           => 'permit_empty',
    ];

    protected $validationMessages = [
        'first_name' => [
            'required' => 'First name is required.',
        ],
        'last_name' => [
            'required' => 'Last name is required.',
        ],
        'age' => [
            'required' => 'Age is required.',
            'integer' => 'Age must be a valid number.',
            'greater_than_equal_to' => 'Age must be at least 16.',
            'less_than_equal_to' => 'Age must be 120 or below.',
        ],
        'address' => [
            'required' => 'Address is required.',
        ],
        'driver_name' => [
            'required' => 'Driver name is required.',
        ],
        'license_plate' => [
            'required' => 'License plate is required.',
        ],
        'license_number' => [
            'max_length' => 'Driver license number must not exceed 50 characters.',
        ],
        'violation_type' => [
            'required' => 'Violation type is required.',
        ],
        'penalty_amount' => [
            'required' => 'Penalty amount is required.',
            'decimal' => 'Penalty amount must be a valid number.',
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be Pending, Paid, or Cancelled.',
        ],
    ];

    protected $beforeInsert = ['generateTicketId'];
    protected $beforeUpdate = [];

    protected function generateTicketId(array $data)
    {
        if (empty($data['data']['ticket_id'])) {
            $year = ! empty($data['data']['violation_date'])
                ? (int) date('Y', strtotime((string) $data['data']['violation_date']))
                : (int) date('Y');

            $data['data']['ticket_id'] = $this->generateNextTctNumber($year);
        }

        return $data;
    }

    /**
     * Highest serial already used for a given year from stored ticket ids.
     */
    public function getMaxTctSequenceForYear(int $year): int
    {
        // 1. Search for ANY ticket containing the year and a hyphen in ticket_id
        $builder = $this->db->table('violations');
        $builder->select("ticket_id");
        $builder->like('ticket_id', (string)$year, 'after');
        $query = $builder->get();
        
        $max = 0;
        foreach ($query->getResult() as $row) {
            $tid = (string)$row->ticket_id;
            // Format is YYYY-XXXXXXX
            if (preg_match('/^' . $year . '-(\d+)$/', $tid, $matches)) {
                $serial = (int) $matches[1];
                if ($serial > $max) {
                    $max = $serial;
                }
            }
        }

        return $max;
    }

    /**
     * Preview the next TCT number without reserving it.
     */
    public function peekNextTctNumber(?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $this->ensureSequenceTable();

        $row = $this->db->table('tct_sequences')->where('year', $year)->get()->getRowArray();
        $sequence = $row ? (int) ($row['last_sequence'] ?? 0) : 0;
        
        // Always verify against actual records to ensure we follow up the LATEST
        $maxStored = $this->getMaxTctSequenceForYear($year);
        $current = max($sequence, $maxStored);

        return sprintf('%d-%07d', $year, $current + 1);
    }

    /**
     * Reserve and return the next official TCT number for the year.
     */
    public function generateNextTctNumber(?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $this->ensureSequenceTable();

        $db = $this->db;
        $db->transStart();

        $row = $db->query('SELECT last_sequence FROM tct_sequences WHERE year = ? FOR UPDATE', [$year])->getRowArray();
        $sequence = $row ? (int) ($row['last_sequence'] ?? 0) : 0;
        
        // Always verify against actual records to ensure we follow up the LATEST
        $maxStored = $this->getMaxTctSequenceForYear($year);
        $current = max($sequence, $maxStored);
        
        $next = $current + 1;

        if (! $row) {
            $db->table('tct_sequences')->insert([
                'year'          => $year,
                'last_sequence' => $next,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        } else {
            $db->table('tct_sequences')
                ->where('year', $year)
                ->update([
                    'last_sequence' => $next,
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
        }

        $db->transComplete();

        return sprintf('%d-%07d', $year, $next);
    }

    /**
     * Keep the sequence table aligned when a manual booklet number is used.
     */
    public function syncSequenceIfHigher(int $year, int $sequence): void
    {
        $this->ensureSequenceTable();

        $maxStored = $this->getMaxTctSequenceForYear($year);
        $finalSequence = max($sequence, $maxStored);

        $row = $this->db->table('tct_sequences')->where('year', $year)->get()->getRowArray();
        if (! $row) {
            $this->db->table('tct_sequences')->insert([
                'year'          => $year,
                'last_sequence' => $finalSequence,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            return;
        }

        if ($finalSequence > (int) $row['last_sequence']) {
            $this->db->table('tct_sequences')
                ->where('year', $year)
                ->update([
                    'last_sequence' => $finalSequence,
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
        }
    }

    public function ticketIdExists(string $ticketId): bool
    {
        return $this->where('ticket_id', $ticketId)->countAllResults() > 0;
    }

    protected function ensureSequenceTable(): void
    {
        if ($this->db->tableExists('tct_sequences')) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'year' => [
                'type'     => 'SMALLINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'last_sequence' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $forge->addKey('year', true);
        $forge->createTable('tct_sequences', true);
    }

    public function getDetailedViolation($id = null)
    {
        if ($id !== null) {
            return $this->select('violations.*, users.username as officer_name')
                        ->join('users', 'users.id = violations.officer_id', 'left')
                        ->where('violations.id', $id)
                        ->first();
        }
        return null;
    }

    /**
     * Infractions printed on one ticket, each carrying its own fine.
     * Cancelled infractions are left out: nothing is collected for them.
     *
     * @return list<array<string, mixed>>
     */
    public function getTicketInfractions($ticketId)
    {
        if (empty($ticketId)) {
            return [];
        }

        return $this->select('id, ticket_id, violation_type, penalty_amount, status')
                    ->where('ticket_id', $ticketId)
                    ->where('status !=', 'Cancelled')
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    public function getAllDetailed($status = null, $limit = null, $offset = 0)
    {
        $builder = $this->select('violations.*, users.username as officer_name')
                        ->join('users', 'users.id = violations.officer_id', 'left');

        if ($status !== null) {
            $builder->where('violations.status', $status);
        }

        $builder->orderBy('violations.violation_date', 'DESC');

        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    /**
     * Returns the count of distinct tickets for a given status.
     * Used internally by getPendingCount(), getPaidCount(), and getCancelledCount()
     * to avoid three near-identical raw queries.
     */
    public function getCountByStatus(string $status): int
    {
        $result = db_connect()
            ->query('SELECT COUNT(DISTINCT ticket_id) as cnt FROM violations WHERE status = ?', [$status])
            ->getRow();
        return $result ? (int) $result->cnt : 0;
    }

    public function getPendingCount(): int   { return $this->getCountByStatus('Pending');   }
    public function getPaidCount(): int      { return $this->getCountByStatus('Paid');      }
    public function getCancelledCount(): int { return $this->getCountByStatus('Cancelled'); }

    public function getTotalRevenue()
    {
        return $this->selectSum('penalty_amount')
                    ->where('status', 'Paid')
                    ->findAll()[0]['penalty_amount'] ?? 0;
    }

    public function getByDriver($licensePlate = null, $driverName = null)
    {
        $builder = $this->builder();

        if ($licensePlate !== null) {
            $builder->where('license_plate', $licensePlate);
        }

        if ($driverName !== null) {
            $builder->where('driver_name', $driverName);
        }

        return $builder->orderBy('violation_date', 'DESC')->get()->getResultArray();
    }

    public function getRecentViolations($limit = 10)
    {
        return $this->select('violations.*, users.username as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum')
                    ->join('users', 'users.id = violations.officer_id', 'left')
                    ->where('violations.status !=', 'Cancelled')
                    ->groupBy('violations.ticket_id')
                    ->orderBy('violations.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function recordPayment($id, $paymentMethod, $remarks = null, $transactionId = null)
    {
        $violation = $this->find($id);

        if (! $violation) {
            return false;
        }

        $receiptNumber = $transactionId ?: 'RCP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
        $payload       = [
            'status'          => 'Paid',
            'paid_date'       => date('Y-m-d H:i:s'),
            'payment_method'  => $paymentMethod,
            'receipt_number'  => $receiptNumber,
            'remarks'         => $remarks,
        ];

        $db = $this->db;
        $db->transBegin();

        try {
            // A payment settles the whole ticket, but cancelled infractions must
            // stay cancelled for historical accuracy.
            if (! empty($violation['ticket_id'])) {
                $updated = $this->where('ticket_id', $violation['ticket_id'])
                    ->where('status !=', 'Cancelled')
                    ->set($payload)
                    ->update();
            } else {
                $updated = $this->where('id', $id)
                    ->where('status !=', 'Cancelled')
                    ->set($payload)
                    ->update();
            }

            if (! $updated || $db->transStatus() === false) {
                throw new \RuntimeException('Unable to record the payment.');
            }

            $db->transCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Payment recording failed for violation #{id}: {message}', [
                'id'      => (int) $id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function cancelViolation($id, $reason = null)
    {
        $violation = $this->find($id);
        if ($violation && !empty($violation['ticket_id'])) {
            return $this->where('ticket_id', $violation['ticket_id'])->set([
                'status'  => 'Cancelled',
                'remarks'  => $reason,
            ])->update();
        }
        return $this->update($id, [
            'status'  => 'Cancelled',
            'remarks'  => $reason,
        ]);
    }

    public function searchViolations($keyword)
    {
        return $this->select('violations.*, users.username as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum')
                    ->join('users', 'users.id = violations.officer_id', 'left')
                    ->where('violations.status !=', 'Cancelled')
                    ->groupStart()
                        ->like('violations.ticket_id', $keyword)
                        ->orLike('violations.driver_name', $keyword)
                        ->orLike('violations.license_plate', $keyword)
                        ->orLike('violations.license_number', $keyword)
                        ->orLike('violations.violation_type', $keyword)
                        ->orLike('violations.receipt_number', $keyword)
                    ->groupEnd()
                    ->groupBy('violations.ticket_id')
                    ->orderBy('violations.violation_date', 'DESC')
                    ->findAll();
    }

    public function getStatistics()
    {
        $db = db_connect();
        $ticketCount  = $db->query("SELECT COUNT(DISTINCT ticket_id) as cnt FROM violations")->getRow();
        $violatorCount = $db->query("SELECT COUNT(DISTINCT violator_id) as cnt FROM violations WHERE violator_id IS NOT NULL")->getRow();

        return [
            'total_violations'    => $ticketCount ? (int)$ticketCount->cnt : 0,
            'total_violators'     => $violatorCount ? (int)$violatorCount->cnt : 0,
            'pending'            => $this->getPendingCount(),
            'paid'               => $this->getPaidCount(),
            'cancelled'          => $this->getCancelledCount(),
            'total_revenue'      => $this->getTotalRevenue(),
        ];
    }

    public function getByOfficer($officerId)
    {
        return $this->where('officer_id', $officerId)
                    ->orWhere('created_by', $officerId)
                    ->orderBy('violation_date', 'DESC')
                    ->findAll();
    }

    public function getViolationTypesSummary()
    {
        // Group purely by the resolved violation name so all records for the
        // same type are counted together regardless of violation_type_id.
        $db = db_connect();
        $query = $db->query("
            SELECT 
                IFNULL(vt.violation_name, v.violation_type) AS violation_type,
                COUNT(*) AS count,
                SUM(v.penalty_amount) AS total_amount
            FROM violations v
            LEFT JOIN violation_types vt ON vt.id = v.violation_type_id
            GROUP BY IFNULL(vt.violation_name, v.violation_type)
            ORDER BY count DESC
        ");
        return $query->getResultArray();
    }

    /**
     * Get the violator with the most distinct tickets.
     */
    public function getTopViolatorPerson()
    {
        $rows = $this->getTopViolatorsList(1);

        return $rows[0] ?? null;
    }

    /**
     * Returns the single most-recorded violation type.
     * Delegates to getTopViolationsList(1) to avoid duplicating the same SQL.
     */
    public function getTopViolationType(): ?array
    {
        $result = $this->getTopViolationsList(1);
        return $result[0] ?? null;
    }

    /**
     * Top violators grouped by their stable violator record.
     *
     * A ticket with several infractions counts as one attempt, matching the
     * attempt definition used across violator profiles and the violator list.
     */
    public function getTopViolatorsList($limit = 10)
    {
        $db = db_connect();
        $query = $db->query("
            SELECT
                v.id AS violator_id,
                v.driver_name,
                v.license_number,
                v.license_plate,
                COUNT(DISTINCT vi.ticket_id) AS total_violations,
                MAX(vi.violation_date) AS last_violation_at
            FROM violators v
            INNER JOIN violations vi ON vi.violator_id = v.id
            GROUP BY v.id, v.driver_name, v.license_number, v.license_plate
            ORDER BY total_violations DESC, last_violation_at DESC, v.driver_name ASC
            LIMIT ?
        ", [(int)$limit]);

        return $query->getResultArray();
    }

    public function getTopViolationsList($limit = 10)
    {
        $db = db_connect();
        $query = $db->query("
            SELECT 
                IFNULL(vt.violation_name, v.violation_type) AS violation_type,
                COUNT(*) AS total_count
            FROM violations v
            LEFT JOIN violation_types vt ON vt.id = v.violation_type_id
            GROUP BY IFNULL(vt.violation_name, v.violation_type)
            ORDER BY total_count DESC
            LIMIT ?
        ", [(int)$limit]);
        return $query->getResultArray();
    }


    public function getMonthlyTrend($year = null)
    {
        $year = $year ?? date('Y');
        
        // Initialize 12 months with 0
        $trend = array_fill(1, 12, ['count' => 0, 'revenue' => 0]);

        $results = $this->select("MONTH(violation_date) as month, COUNT(*) as count, SUM(CASE WHEN status = 'Paid' THEN penalty_amount ELSE 0 END) as revenue")
                        ->where('YEAR(violation_date)', $year)
                        ->groupBy('MONTH(violation_date)')
                        ->orderBy('month', 'ASC')
                        ->findAll();

        foreach ($results as $row) {
            $trend[(int)$row['month']] = [
                'count' => (int)$row['count'],
                'revenue' => (float)$row['revenue']
            ];
        }

        return $trend;
    }

    public function getStatusDistribution()
    {
        return $this->select('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->findAll();
    }

    /**
     * Overall payment status per violator, derived from all linked tickets.
     */
    public function getViolatorStatusDistribution()
    {
        $db = db_connect();

        return $db->query("
            SELECT overall_status AS status, COUNT(*) AS count
            FROM (
                SELECT
                    v.id,
                    CASE
                        WHEN SUM(CASE WHEN vi.status <> 'Cancelled' THEN 1 ELSE 0 END) = 0 THEN 'Cancelled'
                        WHEN SUM(CASE WHEN vi.status = 'Pending' THEN 1 ELSE 0 END) > 0 THEN 'Pending'
                        ELSE 'Paid'
                    END AS overall_status
                FROM violators v
                INNER JOIN violations vi ON vi.violator_id = v.id
                GROUP BY v.id
            ) AS grouped
            GROUP BY overall_status
            ORDER BY FIELD(overall_status, 'Pending', 'Paid', 'Cancelled')
        ")->getResultArray();
    }

    public function getOutstandingFines()
    {
        return $this->selectSum('penalty_amount')
                    ->where('status', 'Pending')
                    ->findAll()[0]['penalty_amount'] ?? 0;
    }

    /**
     * Get weekly violation trend for the last N weeks.
     */
    public function getWeeklyTrend($weeks = 12, $officerId = null)
    {
        $result = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = date('Y-m-d', strtotime("monday this week -$i weeks"));
            $weekEnd   = date('Y-m-d', strtotime("sunday this week -$i weeks"));

            $builder = $this->builder();
            // Assessed value ignores cancelled (void) citations; paid_revenue is
            // the money actually collected, matching the Revenue Report.
            $builder->select("COUNT(*) as count, IFNULL(SUM(CASE WHEN status <> 'Cancelled' THEN penalty_amount ELSE 0 END), 0) as revenue, IFNULL(SUM(CASE WHEN status = 'Paid' THEN penalty_amount ELSE 0 END), 0) as paid_revenue");
            $builder->where('DATE(violation_date) >=', $weekStart);
            $builder->where('DATE(violation_date) <=', $weekEnd);

            if ($officerId !== null) {
                $builder->where('officer_id', $officerId);
            }

            $row = $builder->get()->getRowArray();

            $result[] = [
                'label'        => date('M d', strtotime($weekStart)),
                'week_start'   => $weekStart,
                'week_end'     => $weekEnd,
                'count'        => (int) ($row['count'] ?? 0),
                'revenue'      => (float) ($row['revenue'] ?? 0),
                'paid_revenue' => (float) ($row['paid_revenue'] ?? 0),
            ];
        }
        return $result;
    }

    /**
     * Get weekly violation trend for a specific month of a year.
     * Divides the month into Week 1 (1-7), Week 2 (8-14), Week 3 (15-21), Week 4 (22-28), and Week 5 (29-end).
     */
    public function getWeeklyTrendForMonth($year, $month, $officerId = null)
    {
        $year  = (int) $year;
        $month = (int) $month;
        if ($year <= 0) {
            $year = (int) date('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $numDays    = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $monthShort = date('M', mktime(0, 0, 0, $month, 1, $year));

        $weekBrackets = [
            ['start' => 1, 'end' => 7],
            ['start' => 8, 'end' => 14],
            ['start' => 15, 'end' => 21],
            ['start' => 22, 'end' => 28],
        ];
        if ($numDays > 28) {
            $weekBrackets[] = ['start' => 29, 'end' => $numDays];
        }

        $result = [];
        foreach ($weekBrackets as $idx => $wb) {
            $weekNum     = $idx + 1;
            $startDayPad = sprintf('%02d', $wb['start']);
            $endDayPad   = sprintf('%02d', $wb['end']);

            $startDate = sprintf('%04d-%02d-%02d', $year, $month, $wb['start']);
            $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $wb['end']);

            $builder = $this->builder();
            $builder->select("COUNT(*) as count, IFNULL(SUM(CASE WHEN status <> 'Cancelled' THEN penalty_amount ELSE 0 END), 0) as revenue, IFNULL(SUM(CASE WHEN status = 'Paid' THEN penalty_amount ELSE 0 END), 0) as paid_revenue");
            $builder->where('DATE(violation_date) >=', $startDate);
            $builder->where('DATE(violation_date) <=', $endDate);

            if ($officerId !== null) {
                $builder->where('officer_id', $officerId);
            }

            $row = $builder->get()->getRowArray();

            $result[] = [
                'week_num'     => $weekNum,
                'label'        => "Week {$weekNum} ({$monthShort} {$startDayPad}-{$endDayPad})",
                'short_label'  => "W{$weekNum} ({$startDayPad}-{$endDayPad})",
                'date_range'   => "{$monthShort} {$startDayPad} - {$monthShort} {$endDayPad}, {$year}",
                'week_start'   => $startDate,
                'week_end'     => $endDate,
                'count'        => (int) ($row['count'] ?? 0),
                'revenue'      => (float) ($row['revenue'] ?? 0),
                'paid_revenue' => (float) ($row['paid_revenue'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get monthly breakdown with status counts for a given year.
     */
    public function getMonthlyBreakdown($year = null, $officerId = null)
    {
        $year = $year ?? date('Y');
        $trend = [];

        for ($m = 1; $m <= 12; $m++) {
            $trend[$m] = [
                'month'     => $m,
                'count'     => 0,
                'revenue'   => 0,
                'pending'   => 0,
                'paid'      => 0,
                'cancelled' => 0,
            ];
        }

        $builder = $this->builder();
        $builder->select("MONTH(violation_date) as month, COUNT(*) as count,
            IFNULL(SUM(CASE WHEN status = 'Paid' THEN penalty_amount ELSE 0 END), 0) as revenue,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled");
        $builder->where('YEAR(violation_date)', $year);

        if ($officerId !== null) {
            $builder->where('officer_id', $officerId);
        }

        $builder->groupBy('MONTH(violation_date)');
        $builder->orderBy('month', 'ASC');
        $results = $builder->get()->getResultArray();

        foreach ($results as $row) {
            $m = (int) $row['month'];
            $trend[$m] = [
                'month'     => $m,
                'count'     => (int) $row['count'],
                'revenue'   => (float) $row['revenue'],
                'pending'   => (int) $row['pending'],
                'paid'      => (int) $row['paid'],
                'cancelled' => (int) $row['cancelled'],
            ];
        }

        return $trend;
    }

    /**
     * Get violation nature summary with counts, revenue, and percentage share.
     *
     * Fields returned per row:
     *  - total_amount   : SUM of penalty_amount for Paid+Pending (assessed total)
     *  - paid_amount    : SUM of penalty_amount for Paid ONLY (collected revenue — matches KPI card)
     *  - pending_amount : SUM of penalty_amount for Pending ONLY
     */
    public function getViolationNatureSummary($officerId = null, $startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        $builder->select("IFNULL(vt.violation_name, violations.violation_type) as violation_type,
            COUNT(*) as count,
            IFNULL(SUM(violations.penalty_amount), 0) as total_amount,
            IFNULL(SUM(CASE WHEN violations.status = 'Paid'    THEN violations.penalty_amount ELSE 0 END), 0) as paid_amount,
            IFNULL(SUM(CASE WHEN violations.status = 'Pending' THEN violations.penalty_amount ELSE 0 END), 0) as pending_amount,
            IFNULL(AVG(violations.penalty_amount), 0) as avg_amount,
            SUM(CASE WHEN violations.status = 'Paid'    THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN violations.status = 'Pending' THEN 1 ELSE 0 END) as pending_count");
        $builder->join('violation_types vt', 'vt.id = violations.violation_type_id', 'left');

        $builder->where('violations.status !=', 'Cancelled');

        if ($officerId !== null) {
            $builder->where('violations.officer_id', $officerId);
        }

        // Optional calendar window, so the dashboard tallies the same year its
        // KPI cards and the Revenue Report describe.
        if ($startDate !== null && $endDate !== null) {
            $builder->where('DATE(violations.violation_date) >=', $startDate);
            $builder->where('DATE(violations.violation_date) <=', $endDate);
        }

        $builder->groupBy('IFNULL(vt.violation_name, violations.violation_type)');
        $builder->orderBy('count', 'DESC');
        $rows = $builder->get()->getResultArray();

        // Calculate percentage share
        $total = array_sum(array_column($rows, 'count'));
        foreach ($rows as &$row) {
            $row['count']          = (int)   $row['count'];
            $row['total_amount']   = (float) $row['total_amount'];
            $row['paid_amount']    = (float) $row['paid_amount'];
            $row['pending_amount'] = (float) $row['pending_amount'];
            $row['avg_amount']     = (float) $row['avg_amount'];
            $row['paid_count']     = (int)   $row['paid_count'];
            $row['pending_count']  = (int)   $row['pending_count'];
            $row['percentage']     = $total > 0 ? round(($row['count'] / $total) * 100, 1) : 0;
        }

        return $rows;
    }

    /**
     * Aggregate report data for weekly/monthly reporting with summaries and breakdowns.
     */
    public function getReportData($startDate, $endDate, $status = null, $typeId = null)
    {
        $db = db_connect();

        // Base builder for records
        $builder = $this->select('violations.*, users.username as officer_name, vt.violation_name')
                        ->join('users', 'users.id = violations.officer_id', 'left')
                        ->join('violation_types vt', 'vt.id = violations.violation_type_id', 'left')
                        ->where('DATE(violations.violation_date) >=', $startDate)
                        ->where('DATE(violations.violation_date) <=', $endDate);

        if (!empty($status)) {
            $builder->where('violations.status', $status);
        }

        if (!empty($typeId)) {
            $builder->where('violations.violation_type_id', $typeId);
        }

        $records = $builder->orderBy('violations.violation_date', 'DESC')->findAll();

        // Summary Calculations
        //
        // A cancelled citation is void: it was still issued (and stays in the
        // detailed records for audit), but it carries no assessable value, so
        // it must never inflate the assessed, collected or outstanding money.
        // Only Paid + Pending rows add amount, which keeps
        // assessed = collected + pending, and the collection rate is measured
        // against that same assessable value.
        $totalViolations = count($records);
        $distinctTickets = count(array_unique(array_column($records, 'ticket_id')));

        $totalAssessed = 0;
        $totalPaid = 0;
        $totalPending = 0;
        $paidTicketsArr = [];

        foreach ($records as $r) {
            if (($r['status'] ?? '') === 'Cancelled') {
                continue;
            }

            $amt = (float)$r['penalty_amount'];
            $totalAssessed += $amt;
            if ($r['status'] === 'Paid') {
                $totalPaid += $amt;
                if (!empty($r['ticket_id'])) {
                    $paidTicketsArr[$r['ticket_id']] = true;
                }
            } elseif ($r['status'] === 'Pending') {
                $totalPending += $amt;
            }
        }

        $paidTicketCount = count($paidTicketsArr);
        $collectionRate  = $totalAssessed > 0 ? round(($totalPaid / $totalAssessed) * 100, 1) : 0;

        // Nature Breakdown
        $natureMap = [];
        foreach ($records as $r) {
            $vName = $r['violation_name'] ?: $r['violation_type'];
            if (!isset($natureMap[$vName])) {
                $natureMap[$vName] = ['violation_type' => $vName, 'count' => 0, 'amount' => 0];
            }
            $natureMap[$vName]['count']++;
            if (($r['status'] ?? '') !== 'Cancelled') {
                $natureMap[$vName]['amount'] += (float)$r['penalty_amount'];
            }
        }
        usort($natureMap, function($a, $b) { return $b['count'] <=> $a['count']; });

        // Daily Breakdown
        $dailyMap = [];
        foreach ($records as $r) {
            $day = date('Y-m-d', strtotime($r['violation_date']));
            if (!isset($dailyMap[$day])) {
                $dailyMap[$day] = ['date' => $day, 'count' => 0, 'revenue' => 0, 'pending' => 0];
            }
            $dailyMap[$day]['count']++;
            if ($r['status'] === 'Paid') {
                $dailyMap[$day]['revenue'] += (float)$r['penalty_amount'];
            } elseif (($r['status'] ?? '') === 'Pending') {
                $dailyMap[$day]['pending'] += (float)$r['penalty_amount'];
            }
        }
        ksort($dailyMap);

        // Officer Breakdown
        // A citation is a ticket, not an infraction line: one ticket carrying
        // four offences is still one citation issued by that officer, so the
        // count is taken over distinct ticket numbers.
        $officerMap = [];
        foreach ($records as $r) {
            $offName = $r['officer_name'] ?: 'System / Admin';
            if (!isset($officerMap[$offName])) {
                $officerMap[$offName] = ['officer_name' => $offName, 'count' => 0, 'amount' => 0, 'tickets' => []];
            }
            $officerMap[$offName]['tickets'][$r['ticket_id'] ?: ('#' . $r['id'])] = true;
            if (($r['status'] ?? '') !== 'Cancelled') {
                $officerMap[$offName]['amount'] += (float)$r['penalty_amount'];
            }
        }
        foreach ($officerMap as &$officerRow) {
            $officerRow['count'] = count($officerRow['tickets']);
            unset($officerRow['tickets']);
        }
        unset($officerRow);
        usort($officerMap, function($a, $b) { return $b['count'] <=> $a['count']; });

        return [
            'records'          => $records,
            'summary'          => [
                'total_tickets'    => $distinctTickets,
                'total_violations' => $totalViolations,
                'total_assessed'   => $totalAssessed,
                'total_paid'       => $totalPaid,
                'total_pending'    => $totalPending,
                'paid_tickets'     => $paidTicketCount,
                'collection_rate'  => $collectionRate,
            ],
            'nature_breakdown'  => array_values($natureMap),
            'daily_breakdown'   => array_values($dailyMap),
            'officer_breakdown' => array_values($officerMap),
            'start_date'        => $startDate,
            'end_date'          => $endDate,
        ];
    }
}
