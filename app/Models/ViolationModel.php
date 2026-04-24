<?php

namespace App\Models;

use CodeIgniter\Model;

class ViolationModel extends ViolationRecord
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
}
