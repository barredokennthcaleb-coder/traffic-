<?php

namespace App\Models;

use CodeIgniter\Model;

class ViolationModel extends Model
{
    protected $table            = 'violations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['driver_name', 'license_plate', 'violation_type', 'penalty_amount', 'status', 'violation_date'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'driver_name'    => 'required|min_length[3]',
        'license_plate'  => 'required|min_length[3]',
        'violation_type' => 'required',
        'penalty_amount' => 'required|decimal',
        'status'         => 'required|in_list[Pending,Paid,Cancelled]',
        'violation_date' => 'required|valid_date',
    ];
}
