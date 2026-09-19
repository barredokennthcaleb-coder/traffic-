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
            $data['data']['ticket_id'] = 'TKT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        }
        return $data;
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

    public function getPendingCount()
    {
        $db = db_connect();
        $result = $db->query("SELECT COUNT(DISTINCT ticket_id) as cnt FROM violations WHERE status = 'Pending'")->getRow();
        return $result ? (int)$result->cnt : 0;
    }

    public function getPaidCount()
    {
        $db = db_connect();
        $result = $db->query("SELECT COUNT(DISTINCT ticket_id) as cnt FROM violations WHERE status = 'Paid'")->getRow();
        return $result ? (int)$result->cnt : 0;
    }

    public function getCancelledCount()
    {
        $db = db_connect();
        $result = $db->query("SELECT COUNT(DISTINCT ticket_id) as cnt FROM violations WHERE status = 'Cancelled'")->getRow();
        return $result ? (int)$result->cnt : 0;
    }

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
        $receiptNumber = $transactionId ?: 'RCP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));

        $violation = $this->find($id);
        if ($violation && !empty($violation['ticket_id'])) {
            return $this->where('ticket_id', $violation['ticket_id'])->set([
                'status'          => 'Paid',
                'paid_date'       => date('Y-m-d H:i:s'),
                'payment_method'  => $paymentMethod,
                'receipt_number'  => $receiptNumber,
                'remarks'         => $remarks,
            ])->update();
        }

        return $this->update($id, [
            'status'          => 'Paid',
            'paid_date'       => date('Y-m-d H:i:s'),
            'payment_method'  => $paymentMethod,
            'receipt_number'  => $receiptNumber,
            'remarks'         => $remarks,
        ]);
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
        $ticketCount = $db->query("SELECT COUNT(DISTINCT ticket_id) as cnt FROM violations")->getRow();
        return [
            'total_violations'    => $ticketCount ? (int)$ticketCount->cnt : 0,
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
     * Get the driver who has the most total violation records.
     */
    public function getTopViolatorPerson()
    {
        $db = db_connect();
        $query = $db->query("
            SELECT 
                driver_name,
                license_plate,
                COUNT(*) AS total_violations
            FROM violations
            WHERE license_plate IS NOT NULL AND license_plate != ''
            GROUP BY license_plate, driver_name
            ORDER BY total_violations DESC
            LIMIT 1
        ");
        return $query->getRowArray();
    }

    public function getTopViolationType()
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
            LIMIT 1
        ");
        return $query->getRowArray();
    }

    public function getTopViolatorsList($limit = 10)
    {
        $db = db_connect();
        $query = $db->query("
            SELECT 
                driver_name,
                license_plate,
                COUNT(*) AS total_violations
            FROM violations
            WHERE license_plate IS NOT NULL AND license_plate != ''
            GROUP BY license_plate, driver_name
            ORDER BY total_violations DESC
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
            $builder->select("COUNT(*) as count, IFNULL(SUM(penalty_amount), 0) as revenue, IFNULL(SUM(CASE WHEN status = 'Paid' THEN penalty_amount ELSE 0 END), 0) as paid_revenue");
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
     */
    public function getViolationNatureSummary($officerId = null)
    {
        $builder = $this->builder();
        $builder->select("IFNULL(vt.violation_name, violations.violation_type) as violation_type,
            COUNT(*) as count,
            IFNULL(SUM(violations.penalty_amount), 0) as total_amount,
            IFNULL(AVG(violations.penalty_amount), 0) as avg_amount,
            SUM(CASE WHEN violations.status = 'Paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN violations.status = 'Pending' THEN 1 ELSE 0 END) as pending_count");
        $builder->join('violation_types vt', 'vt.id = violations.violation_type_id', 'left');

        if ($officerId !== null) {
            $builder->where('violations.officer_id', $officerId);
        }

        $builder->groupBy('violations.violation_type_id, violations.violation_type, vt.violation_name');
        $builder->orderBy('count', 'DESC');
        $rows = $builder->get()->getResultArray();

        // Calculate percentage share
        $total = array_sum(array_column($rows, 'count'));
        foreach ($rows as &$row) {
            $row['count']         = (int) $row['count'];
            $row['total_amount']  = (float) $row['total_amount'];
            $row['avg_amount']    = (float) $row['avg_amount'];
            $row['paid_count']    = (int) $row['paid_count'];
            $row['pending_count'] = (int) $row['pending_count'];
            $row['percentage']    = $total > 0 ? round(($row['count'] / $total) * 100, 1) : 0;
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
        $totalViolations = count($records);
        $distinctTickets = count(array_unique(array_column($records, 'ticket_id')));
        
        $totalAssessed = 0;
        $totalPaid = 0;
        $totalPending = 0;
        $paidTicketsArr = [];

        foreach ($records as $r) {
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
        $collectionRate  = $distinctTickets > 0 ? round(($paidTicketCount / $distinctTickets) * 100, 1) : 0;

        // Nature Breakdown
        $natureMap = [];
        foreach ($records as $r) {
            $vName = $r['violation_name'] ?: $r['violation_type'];
            if (!isset($natureMap[$vName])) {
                $natureMap[$vName] = ['violation_type' => $vName, 'count' => 0, 'amount' => 0];
            }
            $natureMap[$vName]['count']++;
            $natureMap[$vName]['amount'] += (float)$r['penalty_amount'];
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
            } else {
                $dailyMap[$day]['pending'] += (float)$r['penalty_amount'];
            }
        }
        ksort($dailyMap);

        // Officer Breakdown
        $officerMap = [];
        foreach ($records as $r) {
            $offName = $r['officer_name'] ?: 'System / Admin';
            if (!isset($officerMap[$offName])) {
                $officerMap[$offName] = ['officer_name' => $offName, 'count' => 0, 'amount' => 0];
            }
            $officerMap[$offName]['count']++;
            $officerMap[$offName]['amount'] += (float)$r['penalty_amount'];
        }
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
