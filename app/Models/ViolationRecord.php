<?php

namespace App\Models;

use CodeIgniter\Model;

class ViolationRecord extends Model
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
        'license_plate', 
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
        'notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id'              => 'permit_empty|integer',
        'ticket_id'       => 'permit_empty',
        'driver_name'     => 'required|min_length[2]|max_length[255]',
        'license_plate'   => 'required|min_length[2]|max_length[20]',
        'officer_id'      => 'permit_empty|integer',
        'violation_type_id' => 'permit_empty|integer',
        'violation_type'  => 'required|min_length[2]|max_length[255]',
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
        'driver_name' => [
            'required' => 'Driver name is required.',
            'min_length' => 'Driver name must be at least 2 characters.',
        ],
        'license_plate' => [
            'required' => 'License plate is required.',
            'min_length' => 'License plate must be at least 2 characters.',
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
        return $this->where('status', 'Pending')->countAllResults();
    }

    public function getPaidCount()
    {
        return $this->where('status', 'Paid')->countAllResults();
    }

    public function getCancelledCount()
    {
        return $this->where('status', 'Cancelled')->countAllResults();
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
        return $this->select('violations.*, users.username as officer_name')
                    ->join('users', 'users.id = violations.officer_id', 'left')
                    ->orderBy('violations.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function recordPayment($id, $paymentMethod)
    {
        $receiptNumber = 'RCP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));

        return $this->update($id, [
            'status'          => 'Paid',
            'paid_date'       => date('Y-m-d H:i:s'),
            'payment_method'  => $paymentMethod,
            'receipt_number'  => $receiptNumber,
        ]);
    }

    public function cancelViolation($id, $reason = null)
    {
        return $this->update($id, [
            'status'  => 'Cancelled',
            'remarks'  => $reason,
        ]);
    }

    public function searchViolations($keyword)
    {
        return $this->select('violations.*, users.username as officer_name')
                    ->join('users', 'users.id = violations.officer_id', 'left')
                    ->groupStart()
                        ->like('violations.ticket_id', $keyword)
                        ->orLike('violations.driver_name', $keyword)
                        ->orLike('violations.license_plate', $keyword)
                        ->orLike('violations.violation_type', $keyword)
                        ->orLike('violations.receipt_number', $keyword)
                    ->groupEnd()
                    ->orderBy('violations.violation_date', 'DESC')
                    ->findAll();
    }

    public function getStatistics()
    {
        return [
            'total_violations'    => $this->countAll(),
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
        return $this->select('violation_type, COUNT(*) as count, SUM(penalty_amount) as total_amount')
                    ->groupBy('violation_type')
                    ->orderBy('count', 'DESC')
                    ->findAll();
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
}
