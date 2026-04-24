<?php

namespace App\Models;

use CodeIgniter\Model;

class ViolationTypeModel extends Model
{
    protected $table            = 'violation_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['violation_name', 'description', 'fine_amount', 'points', 'status'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'violation_name' => 'required|min_length[3]|is_unique[violation_types.violation_name]',
        'description'    => 'permit_empty',
        'fine_amount'    => 'required|decimal',
        'points'         => 'permit_empty|integer',
        'status'         => 'permit_empty|in_list[active,inactive]',
    ];
}
