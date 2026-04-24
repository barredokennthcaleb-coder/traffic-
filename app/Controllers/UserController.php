<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ViolationModel;

class UserController extends BaseController
{
    public function index()
    {
        $session = session();
        $violationModel = new ViolationModel();
        
        // Example: Only show violations related to this user (using license plate if available)
        // For now, let's just show some basic user-level data
        $data = [
            'title' => 'User Dashboard',
            'username' => $session->get('username'),
            'role' => $session->get('role')
        ];
        
        return view('user/dashboard', $data);
    }
}
