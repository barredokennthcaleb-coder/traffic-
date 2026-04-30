<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['username', 'firstname', 'lastname', 'age', 'address', 'profile_image', 'email', 'password', 'role', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'id'       => 'permit_empty',
        'username' => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username,id,{id}]',
        'firstname'=> 'permit_empty|min_length[2]|max_length[100]',
        'lastname' => 'permit_empty|min_length[2]|max_length[100]',
        'age'      => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[120]',
        'address'  => 'permit_empty|max_length[255]',
        'profile_image' => 'permit_empty|max_length[255]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        // Dev convenience: allow short default passwords (e.g. 00000). Increase for production.
        'password' => 'permit_empty|min_length[5]',
        'role'     => 'required|in_list[admin,driver,enforcer]',
    ];
    
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (!isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

        return $data;
    }
}
