<?php

namespace App\Models;

use CodeIgniter\Model;

class AnalyticModel extends Model
{
    protected $table = 'violations';

    public function getStatusCounts()
    {
        return $this->select('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->findAll();
    }

    public function getTypeCounts()
    {
        return $this->select('violation_type, COUNT(*) as count')
                    ->groupBy('violation_type')
                    ->findAll();
    }

    public function getDashboardStats()
    {
        return [
            'total_violations'  => $this->countAll(),
            'pending_penalties' => $this->where('status', 'Pending')->countAllResults(),
            'paid_penalties'    => $this->where('status', 'Paid')->countAllResults(),
            'total_revenue'     => $this->selectSum('penalty_amount')
                                        ->where('status', 'Paid')
                                        ->first()['penalty_amount'] ?? 0,
        ];
    }
}
