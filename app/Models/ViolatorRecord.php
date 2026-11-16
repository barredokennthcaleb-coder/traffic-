<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

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
        'vehicle_type',
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
        'vehicle_type'    => 'permit_empty|max_length[100]',
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

        if (! $db->transComplete()) {
            // The reservation was rolled back; returning the number anyway
            // would hand out a serial that was never persisted.
            throw new RuntimeException('Unable to reserve the next TCT number.');
        }

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

        // Insert the row when missing (ignored if a concurrent request won the
        // race), then move the sequence forward in a single atomic statement so
        // two manual-number saves cannot move it backwards.
        $this->db->table('tct_sequences')->ignore(true)->insert([
            'year'          => $year,
            'last_sequence' => $finalSequence,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('tct_sequences')
            ->where('year', $year)
            ->set('last_sequence', 'CASE WHEN last_sequence < ' . (int) $finalSequence . ' THEN ' . (int) $finalSequence . ' ELSE last_sequence END', false)
            ->set('updated_at', date('Y-m-d H:i:s'))
            ->update();
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
            return $this->select('violations.*, ' . self::officerNameSql() . ' as officer_name')
                        ->join('users', 'users.id = COALESCE(violations.officer_id, violations.created_by)', 'left')
                        ->where('violations.id', $id)
                        ->first();
        }
        return null;
    }

    /**
     * SQL that resolves the name an apprehending officer is printed under.
     *
     * A citation carries the officer's real name (first name, middle initial
     * and last name) so the printed ticket shows "Janrey L. Calamba" instead
     * of the bare login handle. If the account has no name on file the
     * username is used as the fallback, so the ticket never prints blank.
     */
    public static function officerNameSql(string $table = 'users'): string
    {
        return "COALESCE(NULLIF(TRIM(CONCAT_WS("
            . "' ', "
            . "NULLIF(TRIM({$table}.firstname), ''), "
            . "NULLIF(CONCAT(NULLIF(TRIM({$table}.middle_initial), ''), '.'), '.'), "
            . "NULLIF(TRIM({$table}.lastname), '')"
            . ")), ''), {$table}.username)";
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
        $builder = $this->select('violations.*, ' . self::officerNameSql() . ' as officer_name')
                        ->join('users', 'users.id = COALESCE(violations.officer_id, violations.created_by)', 'left');

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

    /**
     * Citations belonging to a driver account: every ticket linked to one of
     * the driver's violator records, plus legacy rows that were never linked
     * but carry the driver's exact full name.
     *
     * @param list<int> $violatorIds
     */
    public function getForDriver(array $violatorIds, string $fullName = ''): array
    {
        $violatorIds = array_values(array_filter(array_map('intval', $violatorIds)));
        $fullName    = trim($fullName);

        if ($violatorIds === [] && $fullName === '') {
            return [];
        }

        $builder = $this->builder();
        $builder->groupStart();

        if ($violatorIds !== []) {
            $builder->whereIn('violator_id', $violatorIds);
        }

        if ($fullName !== '') {
            if ($violatorIds !== []) {
                $builder->orGroupStart()
                    ->where('violator_id IS NULL', null, false)
                    ->where('driver_name', $fullName)
                    ->groupEnd();
            } else {
                $builder->where('violator_id IS NULL', null, false)
                    ->where('driver_name', $fullName);
            }
        }

        $builder->groupEnd();

        return $builder->orderBy('violation_date', 'DESC')->get()->getResultArray();
    }

    public function getRecentViolations($limit = 10)
    {
        return $this->select('violations.*, ' . self::officerNameSql() . ' as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum')
                    ->join('users', 'users.id = violations.officer_id', 'left')
                    ->where('violations.status !=', 'Cancelled')
                    ->groupBy('violations.ticket_id')
                    ->orderBy('violations.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function recordPayment($id, $paymentMethod, $remarks = null, $requestedReceiptNumber = null)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $lockSuffix = strcasecmp($db->DBDriver, 'MySQLi') === 0 ? ' FOR UPDATE' : '';
            $sequence = $db->query(
                'SELECT last_number, number_width FROM orcr_sequences WHERE id = 1' . $lockSuffix
            )->getRowArray();

            if (! $sequence) {
                throw new \RuntimeException('Official Receipt (OR) sequence is not initialized. Run the database migrations.');
            }

            $violation = $db->query(
                'SELECT id, ticket_id, status FROM violations WHERE id = ?' . $lockSuffix,
                [(int) $id]
            )->getRowArray();

            if (! $violation || ($violation['status'] ?? '') !== 'Pending') {
                throw new \RuntimeException('Only a pending citation can receive a payment.');
            }

            if (
                $requestedReceiptNumber !== null
                && (! ctype_digit((string) $requestedReceiptNumber) || strlen((string) $requestedReceiptNumber) > 18)
            ) {
                throw new \RuntimeException('Official Receipt (OR) number must contain 1 to 18 digits.');
            }

            $width = max(6, (int) $sequence['number_width']);
            $lastNumber = (int) $sequence['last_number'];
            $nextNumber = $requestedReceiptNumber === null
                ? $lastNumber + 1
                : (int) $requestedReceiptNumber;
            if ($nextNumber < 1) {
                throw new \RuntimeException('Official Receipt (OR) number must be greater than zero.');
            }

            if ($requestedReceiptNumber === null) {
                while ($this->isOrcrNumberInUse($nextNumber, $violation)) {
                    $nextNumber++;
                }
            } elseif ($this->isOrcrNumberInUse($nextNumber, $violation)) {
                throw new \RuntimeException('That Official Receipt (OR) number is already assigned to another citation.');
            }

            $receiptNumber = str_pad(
                (string) $nextNumber,
                max($width, strlen((string) $requestedReceiptNumber)),
                '0',
                STR_PAD_LEFT
            );

            $db->table('orcr_sequences')->where('id', 1)->update([
                'last_number'  => max($lastNumber, $nextNumber),
                'number_width' => max($width, strlen($receiptNumber)),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

            $payload = [
                'status'         => 'Paid',
                'paid_date'      => date('Y-m-d H:i:s'),
                'payment_method' => $paymentMethod,
                'receipt_number' => $receiptNumber,
                'remarks'        => $remarks,
            ];

            // A payment settles the whole ticket, but cancelled infractions must
            // stay cancelled for historical accuracy.
            if (! empty($violation['ticket_id'])) {
                $updated = $this->where('ticket_id', $violation['ticket_id'])
                    ->where('status', 'Pending')
                    ->set($payload)
                    ->update();
            } else {
                $updated = $this->where('id', $id)
                    ->where('status', 'Pending')
                    ->set($payload)
                    ->update();
            }

            if (! $updated || $db->affectedRows() === 0 || $db->transStatus() === false) {
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

    public function getNextOrcrNumber()
    {
        $sequence = $this->db->table('orcr_sequences')
            ->select('last_number, number_width')
            ->where('id', 1)
            ->get()
            ->getRowArray();

        if (! $sequence) {
            return null;
        }

        $nextNumber = (int) $sequence['last_number'] + 1;
        while ($this->isOrcrNumberInUse($nextNumber)) {
            $nextNumber++;
        }

        return str_pad(
            (string) $nextNumber,
            max(6, (int) $sequence['number_width']),
            '0',
            STR_PAD_LEFT
        );
    }

    private function isOrcrNumberInUse(int $number, array $violation = []): bool
    {
        $query = $this->db->table('violations')->where('status', 'Paid');
        if (! empty($violation['ticket_id'])) {
            $query->where('ticket_id !=', $violation['ticket_id']);
        } elseif (! empty($violation['id'])) {
            $query->where('id !=', (int) $violation['id']);
        }

        $numericReceiptColumn = strcasecmp($this->db->DBDriver, 'MySQLi') === 0
            ? 'CAST(receipt_number AS UNSIGNED)'
            : 'CAST(receipt_number AS INTEGER)';

        return $query->where($numericReceiptColumn . ' =', $number, false)->countAllResults() > 0;
    }

    public function cancelViolation($id, $reason = null)
    {
        $violation = $this->find($id);

        if (! $violation) {
            return false;
        }

        $reason = is_string($reason) ? trim($reason) : null;

        if (! empty($violation['ticket_id'])) {
            // Only Pending infractions may be cancelled. This keeps a payment
            // that lands between the caller's check and this update from being
            // silently voided, and leaves already-Paid/Cancelled rows alone.
            return $this->where('ticket_id', $violation['ticket_id'])
                ->where('status', 'Pending')
                ->set([
                    'status'  => 'Cancelled',
                    'remarks' => $reason,
                ])->update();
        }

        if (($violation['status'] ?? '') !== 'Pending') {
            return false;
        }

        return $this->update($id, [
            'status'  => 'Cancelled',
            'remarks' => $reason,
        ]);
    }

    public function searchViolations($keyword)
    {
        return $this->select('violations.*, ' . self::officerNameSql() . ' as officer_name, GROUP_CONCAT(CONCAT(violation_type, "::", penalty_amount) SEPARATOR "||") as concatenated_violations, SUM(penalty_amount) as total_penalty_sum')
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

        // Counted from the registry side, exactly like the Violators directory
        // joins them: a person exists only while a citation points at them.
        // Deleting an enforcer's last ticket prunes the violator, and this count
        // drops with it on the very next page load instead of holding a stale
        // head-count from the violations table.
        $violatorCount = $db->query(
            'SELECT COUNT(DISTINCT v.id) as cnt FROM violators v INNER JOIN violations vi ON vi.violator_id = v.id'
        )->getRow();

        return [
            'total_violations'    => $ticketCount ? (int)$ticketCount->cnt : 0,
            'total_violators'     => $violatorCount ? (int)$violatorCount->cnt : 0,
            'pending'            => $this->getPendingCount(),
            'paid'               => $this->getPaidCount(),
            'cancelled'          => $this->getCancelledCount(),
            'total_revenue'      => $this->getTotalRevenue(),
        ];
    }

    /**
     * Distinct violators with at least one citation inside the given calendar
     * year — the same window the dashboard's year selector uses elsewhere, so
     * its KPI card stays accurate when the reader switches years.
     */
    public function getViolatorCountForYear($year)
    {
        $db  = db_connect();
        $row = $db->query(
            'SELECT COUNT(DISTINCT v.id) as cnt FROM violators v INNER JOIN violations vi ON vi.violator_id = v.id WHERE YEAR(vi.violation_date) = ?',
            [(int) $year]
        )->getRow();

        return $row ? (int) $row->cnt : 0;
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
        $rows = $this->getTopViolatorsList(1, 1);

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
     * Only repeat offenders (drivers with 2 or more sanctions) are considered
     * top apprehended drivers. First-time violators (1 sanction) are excluded.
     *
     * A ticket with several infractions counts as one attempt, matching the
     * attempt definition used across violator profiles and the violator list.
     * Pass a year to rank only sanctions issued inside that calendar year.
     */
    public function getTopViolatorsList($limit = 10, $minViolations = 2, $year = null)
    {
        $db = db_connect();
        $yearFilter = $year !== null ? 'WHERE YEAR(vi.violation_date) = ' . (int) $year : '';
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
            {$yearFilter}
            GROUP BY v.id, v.driver_name, v.license_number, v.license_plate
            HAVING total_violations >= ?
            ORDER BY total_violations DESC, last_violation_at DESC, v.driver_name ASC
            LIMIT ?
        ", [(int)$minViolations, (int)$limit]);

        return $query->getResultArray();
    }

    /**
     * Most-recorded offense types. Pass a year to scope the ranking to that
     * calendar year — the same window the dashboard's year filter and the
     * Revenue Report use — or leave it null for the all-time list.
     *
     * Ties break on the offense name so the dashboard card, the rankings
     * modal and the printed report always name the same top offense.
     */
    public function getTopViolationsList($limit = 10, $year = null)
    {
        $db = db_connect();
        $yearFilter = $year !== null ? 'WHERE YEAR(v.violation_date) = ' . (int) $year : '';
        $query = $db->query("
            SELECT 
                IFNULL(vt.violation_name, v.violation_type) AS violation_type,
                COUNT(*) AS total_count
            FROM violations v
            LEFT JOIN violation_types vt ON vt.id = v.violation_type_id
            {$yearFilter}
            GROUP BY IFNULL(vt.violation_name, v.violation_type)
            ORDER BY total_count DESC, violation_type ASC
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
     * Pass a year to count only tickets issued inside that calendar year.
     */
    public function getViolatorStatusDistribution($year = null)
    {
        $db = db_connect();
        $yearFilter = $year !== null ? 'WHERE YEAR(vi.violation_date) = ' . (int) $year : '';

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
                {$yearFilter}
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
     * Get weekly violation trend for the last N weeks. Pass an end date to
     * anchor the window (a past year ends on its own Dec 31); with none it
     * keeps rolling up to today, and the final week is never allowed to run
     * past a supplied end date.
     */
    public function getWeeklyTrend($weeks = 12, $officerId = null, $endDate = null)
    {
        $result = [];
        $anchor         = ($endDate !== null && $endDate !== '') ? strtotime($endDate) : time();
        $mondayThisWeek = strtotime('monday this week', $anchor);

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = date('Y-m-d', strtotime("-$i weeks", $mondayThisWeek));
            $weekEndTs = strtotime('+6 days', strtotime($weekStart));
            if ($endDate !== null && $endDate !== '' && $weekEndTs > $anchor) {
                $weekEndTs = $anchor;
            }
            $weekEnd = date('Y-m-d', $weekEndTs);

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
     * Get the day-by-day apprehension trend for the last N days, ending on the
     * anchor date (today when none is supplied, a past year's Dec 31 when a
     * past year is being read).
     *
     * Every calendar day in the window is returned, including days with no
     * citations, so the chart reads as a continuous daily series. Each day
     * carries the process status split (Pending / Paid / Cancelled) as well as
     * the money figures used by the Revenue Report: revenue is the assessed
     * value and ignores cancelled (void) citations, paid_revenue is the money
     * actually collected. Pending + Paid + Cancelled always adds up to count.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDailyTrend($days = 14, $officerId = null, $endDate = null)
    {
        $days = max(1, (int) $days);

        $anchor    = ($endDate !== null && $endDate !== '') ? strtotime($endDate) : time();
        $anchor    = strtotime(date('Y-m-d', $anchor));
        $startDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days', $anchor));
        $endDateYmd = date('Y-m-d', $anchor);

        $builder = $this->builder();
        $builder->select("DATE(violation_date) as day,
            COUNT(*) as count,
            COUNT(DISTINCT ticket_id) as ticket_count,
            IFNULL(SUM(CASE WHEN status <> 'Cancelled' THEN penalty_amount ELSE 0 END), 0) as revenue,
            IFNULL(SUM(CASE WHEN status = 'Paid' THEN penalty_amount ELSE 0 END), 0) as paid_revenue,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_count,
            IFNULL(SUM(CASE WHEN status = 'Pending' THEN penalty_amount ELSE 0 END), 0) as pending_amount");
        $builder->where('DATE(violation_date) >=', $startDate);
        $builder->where('DATE(violation_date) <=', $endDateYmd);

        if ($officerId !== null) {
            $builder->where('officer_id', $officerId);
        }

        $builder->groupBy('DATE(violation_date)');
        $rows = $builder->get()->getResultArray();

        $byDate = [];
        foreach ($rows as $row) {
            $byDate[(string) $row['day']] = $row;
        }

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime('+' . $i . ' days', strtotime($startDate)));
            $row  = $byDate[$date] ?? [];

            $result[] = [
                'date'            => $date,
                'label'           => date('D, M d', strtotime($date)),
                'short_label'     => date('M d', strtotime($date)),
                'count'           => (int) ($row['count'] ?? 0),
                // One apprehension per ticket: the Daily Report's sheet counts
                // tickets, so the dashboard bar reads the same figure.
                'ticket_count'    => (int) ($row['ticket_count'] ?? 0),
                'revenue'         => (float) ($row['revenue'] ?? 0),
                'paid_revenue'    => (float) ($row['paid_revenue'] ?? 0),
                'pending_count'   => (int) ($row['pending_count'] ?? 0),
                'paid_count'      => (int) ($row['paid_count'] ?? 0),
                'cancelled_count' => (int) ($row['cancelled_count'] ?? 0),
                'pending_amount'  => (float) ($row['pending_amount'] ?? 0),
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
     *  - count          : every recorded infraction for that offense, cancelled
     *                     ones included, so the dashboard tally equals the
     *                     "Total Violations" KPI and the generated report
     *  - total_amount   : SUM of penalty_amount for Paid+Pending (assessed total;
     *                     a cancelled citation is void and adds no money)
     *  - paid_amount    : SUM of penalty_amount for Paid ONLY (collected revenue — matches KPI card)
     *  - pending_amount : SUM of penalty_amount for Pending ONLY
     */
    public function getViolationNatureSummary($officerId = null, $startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        $builder->select("IFNULL(vt.violation_name, violations.violation_type) as violation_type,
            COUNT(*) as count,
            IFNULL(SUM(CASE WHEN violations.status <> 'Cancelled' THEN violations.penalty_amount ELSE 0 END), 0) as total_amount,
            IFNULL(SUM(CASE WHEN violations.status = 'Paid'    THEN violations.penalty_amount ELSE 0 END), 0) as paid_amount,
            IFNULL(SUM(CASE WHEN violations.status = 'Pending' THEN violations.penalty_amount ELSE 0 END), 0) as pending_amount,
            IFNULL(AVG(violations.penalty_amount), 0) as avg_amount,
            SUM(CASE WHEN violations.status = 'Paid'    THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN violations.status = 'Pending' THEN 1 ELSE 0 END) as pending_count");
        $builder->join('violation_types vt', 'vt.id = violations.violation_type_id', 'left');

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
        // Ties are broken by name so the "top offense" answer is the same one
        // the generated report prints, not whichever row MySQL happened to
        // return first.
        $builder->orderBy('violation_type', 'ASC');
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
    public function getReportData($startDate, $endDate, $status = null, $typeId = null, $officerId = null)
    {
        $db = db_connect();

        // Base builder for records
        $builder = $this->select('violations.*, ' . self::officerNameSql() . ' as officer_name, vt.violation_name')
                        ->join('users', 'users.id = COALESCE(violations.officer_id, violations.created_by)', 'left')
                        ->join('violation_types vt', 'vt.id = violations.violation_type_id', 'left')
                        ->where('DATE(violations.violation_date) >=', $startDate)
                        ->where('DATE(violations.violation_date) <=', $endDate);

        if (!empty($status)) {
            $builder->where('violations.status', $status);
        }

        if (!empty($typeId)) {
            $builder->where('violations.violation_type_id', $typeId);
        }

        if (!empty($officerId)) {
            $builder->where('COALESCE(violations.officer_id, violations.created_by)', (int) $officerId);
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
        // Highest count first, alphabetical on a tie: the report's "Most
        // Prevalent Offense" and the dashboard's "Top" badge must name the same
        // offense when several share the same count.
        usort($natureMap, static function ($a, $b) {
            return ($b['count'] <=> $a['count']) ?: strcasecmp((string) $a['violation_type'], (string) $b['violation_type']);
        });

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
        // count is taken over distinct ticket numbers. Cancelled offences are
        // void, so a ticket whose offences are all cancelled is not counted
        // and adds no money — the same rule the officer's own enforcement
        // record uses. When the report itself was asked for cancelled
        // citations ($status === 'Cancelled'), those tickets are the subject
        // of the report and stay in the count, still carrying no money.
        $officerMap = [];
        foreach ($records as $r) {
            $offName = $r['officer_name'] ?: 'System / Admin';
            if (!isset($officerMap[$offName])) {
                $officerMap[$offName] = ['officer_name' => $offName, 'rows' => []];
            }
            $officerMap[$offName]['rows'][] = $r;
        }

        $officerBreakdown = [];
        foreach ($officerMap as $officerRow) {
            $tally = self::summarizeTicketActivity($officerRow['rows'], $status === 'Cancelled');

            $officerBreakdown[] = [
                'officer_name' => $officerRow['officer_name'],
                'count'        => $tally['issued'],
                'amount'       => $tally['amount'],
                'paid'         => $tally['paid'],
                'pending'      => $tally['pending'],
            ];
        }
        // Most citations first; equal counts keep alphabetical order so the
        // table never reshuffles between identical builds.
        usort($officerBreakdown, static function ($a, $b) {
            return ($b['count'] <=> $a['count']) ?: strcasecmp((string) $a['officer_name'], (string) $b['officer_name']);
        });

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
            'officer_breakdown' => $officerBreakdown,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
        ];
    }

    /**
     * Tallies one officer's citation rows the way every enforcer activity
     * figure reads them:
     *
     * - One ticket is one citation no matter how many offences it carries.
     * - A cancelled offence is void: it adds no money, and a ticket whose
     *   offences are all cancelled is not counted as issued at all.
     * - A ticket counts as paid only when it still has at least one live
     *   offence and none of its live offences is pending (a payment settles
     *   the whole ticket); it counts as pending when any live offence awaits
     *   payment.
     *
     * Pass $cancelledTicketsAreSubject = true when the dataset itself is the
     * cancelled-citations report: those tickets are then the subject of the
     * tally and are counted, still without adding money.
     *
     * @param array<int|string, array<string, mixed>> $rows Violation rows (any mix of statuses).
     *
     * @return array{issued:int, paid:int, pending:int, amount:float}
     */
    public static function summarizeTicketActivity(array $rows, bool $cancelledTicketsAreSubject = false): array
    {
        $tickets = [];

        foreach ($rows as $index => $row) {
            $key = (string) ($row['ticket_id'] ?? '');
            if ($key === '') {
                $key = '#' . ($row['id'] ?? $index);
            }

            if (!isset($tickets[$key])) {
                $tickets[$key] = ['live' => false, 'paid' => false, 'pending' => false, 'amount' => 0.0];
            }

            if (($row['status'] ?? 'Pending') === 'Cancelled') {
                if ($cancelledTicketsAreSubject) {
                    $tickets[$key]['live'] = true;
                }

                continue;
            }

            $tickets[$key]['live']    = true;
            $tickets[$key]['amount'] += (float) ($row['penalty_amount'] ?? 0);

            if (($row['status'] ?? 'Pending') === 'Paid') {
                $tickets[$key]['paid'] = true;
            } else {
                $tickets[$key]['pending'] = true;
            }
        }

        $issued  = 0;
        $paid    = 0;
        $pending = 0;
        $amount  = 0.0;

        foreach ($tickets as $ticket) {
            if (! $ticket['live']) {
                continue;
            }

            $issued++;
            $amount += $ticket['amount'];

            if ($ticket['pending']) {
                $pending++;
            } elseif ($ticket['paid']) {
                $paid++;
            }
        }

        return [
            'issued'  => $issued,
            'paid'    => $paid,
            'pending' => $pending,
            'amount'  => round($amount, 2),
        ];
    }

    /**
     * Ranks repeat offenders from a report's citation rows.
     *
     * One citation is one ticket, and the same void rule as every other
     * activity figure applies: a ticket whose offences are all cancelled
     * does not count — unless the dataset itself was filtered to cancelled
     * citations ($cancelledCitationsAreSubject), where those voided tickets
     * are the subject of the ranking and are counted without amounts.
     *
     * Rows are grouped by the stable violator record when present and fall
     * back to the normalized driver name + plate for legacy anonymous rows.
     * Ranking order: most citations first, then most recent citation, then
     * name — the same order getTopViolatorsList() uses.
     *
     * @param array<int|string, array<string, mixed>> $rows Violation rows for one reporting period.
     *
     * @return array<int, array{violator_id:int|null, driver_name:string, license_plate:string, license_number:string, last_violation_at:string, citations:int}>
     */
    public static function rankRepeatViolators(array $rows, int $limit = 10, int $minCitations = 2, bool $cancelledCitationsAreSubject = false): array
    {
        $violators = [];

        foreach ($rows as $row) {
            $violatorId = (int) ($row['violator_id'] ?? 0);
            $name       = trim((string) ($row['driver_name'] ?? ''));
            $plate      = trim((string) ($row['license_plate'] ?? ''));

            $key = $violatorId > 0
                ? 'v:' . $violatorId
                : 'np:' . strtolower($name) . '|' . strtolower($plate);

            if (!isset($violators[$key])) {
                $violators[$key] = [
                    'violator_id'       => $violatorId > 0 ? $violatorId : null,
                    'driver_name'       => '',
                    'license_plate'     => '',
                    'license_number'    => '',
                    'last_violation_at' => '',
                    'rows'              => [],
                ];
            }

            $violators[$key]['rows'][] = $row;

            // Keep the first non-empty spelling of each field: report rows
            // arrive newest first, so the latest snapshot wins.
            foreach (['driver_name' => $name, 'license_plate' => $plate, 'license_number' => trim((string) ($row['license_number'] ?? ''))] as $field => $value) {
                if ($violators[$key][$field] === '' && $value !== '') {
                    $violators[$key][$field] = $value;
                }
            }

            $date = (string) ($row['violation_date'] ?? '');
            if ($date > $violators[$key]['last_violation_at']) {
                $violators[$key]['last_violation_at'] = $date;
            }
        }

        $ranked = [];
        foreach ($violators as $violator) {
            $tally = self::summarizeTicketActivity($violator['rows'], $cancelledCitationsAreSubject);

            if ($tally['issued'] < $minCitations) {
                continue;
            }

            unset($violator['rows']);
            $violator['citations'] = $tally['issued'];
            $ranked[] = $violator;
        }

        usort($ranked, static function ($a, $b) {
            return ($b['citations'] <=> $a['citations'])
                ?: strcmp((string) $b['last_violation_at'], (string) $a['last_violation_at'])
                ?: strcasecmp((string) $a['driver_name'], (string) $b['driver_name']);
        });

        return $limit > 0 ? array_slice($ranked, 0, $limit) : array_values($ranked);
    }
}
