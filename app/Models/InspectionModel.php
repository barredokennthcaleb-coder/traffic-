<?php

namespace App\Models;

use CodeIgniter\Model;

class InspectionModel extends Model
{
    protected $table            = 'inspections';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'mtop_no',
        'operator_name',
        'operator_cellphone',
        'driver_cellphone',
        'mv_make_type',
        'cert_attendance',
        'outside_route',
        'city_proper',
        'trash_can',
        'mtop_8x16_route',
        'mtop_8x8_route',
        'mtop_dashboard',
        'signal_light_left',
        'signal_light_right',
        'head_light',
        'stop_light',
        'side_mirror',
        'horn',
        'standard_muffler',
        'color_coding',
        'tarifa',
        'inspected_by',
        'inspection_date'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'mtop_no'       => 'required|max_length[50]',
        'operator_name' => 'required|max_length[255]',
        'inspected_by'  => 'required|max_length[255]',
        'inspection_date' => 'required|valid_date',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
