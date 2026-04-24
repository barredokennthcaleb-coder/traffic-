<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['violation_id', 'amount_paid', 'payment_method', 'transaction_id', 'payment_date', 'remarks'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'violation_id'   => 'required|integer|is_not_unique[violations.id]',
        'amount_paid'    => 'required|decimal',
        'payment_method' => 'required|in_list[Cash,Credit Card,Bank Transfer,Online Payment]',
        'transaction_id' => 'permit_empty|is_unique[payments.transaction_id]',
    ];

    public function getPaymentWithViolation($id = null)
    {
        if ($id === null) {
            return $this->select('payments.*, violations.driver_name, violations.license_plate, violations.violation_type')
                        ->join('violations', 'violations.id = payments.violation_id')
                        ->orderBy('payments.payment_date', 'DESC')
                        ->findAll();
        }

        return $this->select('payments.*, violations.driver_name, violations.license_plate, violations.violation_type')
                    ->join('violations', 'violations.id = payments.violation_id')
                    ->where('payments.id', $id)
                    ->first();
    }
}
