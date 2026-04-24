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
    protected $allowedFields    = ['ticket_id', 'driver_name', 'license_plate', 'officer_id', 'violation_type', 'penalty_amount', 'status', 'violation_date', 'created_by', 'paid_date', 'payment_method', 'receipt_number'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'id'             => 'permit_empty|integer',
        'ticket_id'      => 'permit_empty',
        'driver_name'    => 'required|min_length[3]',
        'license_plate'  => 'required|min_length[3]',
        'officer_id'     => 'permit_empty|integer',
        'violation_type' => 'required',
        'penalty_amount' => 'required|decimal',
        'status'         => 'required|in_list[Pending,Paid,Cancelled]',
        'violation_date' => 'required',
        'created_by'     => 'permit_empty|integer',
        'paid_date'      => 'permit_empty',
        'payment_method' => 'permit_empty',
        'receipt_number' => 'permit_empty',
    ];
}
