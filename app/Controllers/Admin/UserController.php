<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ViolationRecord;

class UserController extends BaseController
{
    protected $userModel;
    protected $violationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->violationModel = new ViolationRecord();
    }

    public function index()
    {
        return redirect()->to('/users/drivers');
    }

    public function enforcers()
    {
        $data = [
            'title' => 'Traffic Enforcers',
            'users' => $this->userModel->where('role', 'traffic_officer')->findAll(),
        ];
        return view('admin/users/enforcers', $data);
    }

    public function drivers()
    {
        $data = [
            'title' => 'Driver Management',
            'users' => $this->userModel->where('role', 'user')->findAll(),
        ];
        return view('admin/users/drivers', $data);
    }

    public function viewDriver($id = null)
    {
        $user = $this->userModel->find($id);
        if (!$user || $user['role'] !== 'user') {
            return redirect()->to('/users/drivers')->with('error', 'Driver not found.');
        }

        $data = [
            'title' => 'Driver Details: ' . $user['username'],
            'user'  => $user,
            'violations' => $this->violationModel->where('driver_name', $user['username'])->findAll(),
        ];
        return view('admin/users/view_driver', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Create New User',
        ];
        return view('admin/users/create', $data);
    }

    public function store()
    {
        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[admin,user,traffic_officer]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role'     => $this->request->getPost('role'),
            'status'   => 'active',
        ];

        if ($this->userModel->save($data)) {
            $redirect = ($data['role'] === 'traffic_officer') ? '/users/enforcers' : '/users/drivers';
            return redirect()->to($redirect)->with('success', 'User created successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }
    }

    public function edit($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        $data = [
            'title' => 'Edit User',
            'user'  => $user,
        ];
        return view('admin/users/edit', $data);
    }

    public function update($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        $rules = [
            'username' => "required|alpha_numeric_space|min_length[3]|is_unique[users.username,id,{$id}]",
            'email'    => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role'     => 'required|in_list[admin,user,traffic_officer]',
            'status'   => 'required|in_list[active,inactive,suspended]',
        ];

        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'       => $id,
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'role'     => $this->request->getPost('role'),
            'status'   => $this->request->getPost('status'),
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        if ($this->userModel->save($data)) {
            $redirect = ($data['role'] === 'traffic_officer') ? '/users/enforcers' : '/users/drivers';
            return redirect()->to($redirect)->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }
    }

    public function delete($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('/users')->with('success', 'User deleted successfully.');
        } else {
            return redirect()->to('/users')->with('error', 'Failed to delete user.');
        }
    }

    public function resetPassword($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        $newPassword = bin2hex(random_bytes(8)); // Generate an 8-character random password

        if ($this->userModel->update($id, ['password' => $newPassword])) {
            // In a real application, you would email this password to the user.
            // For now, we'll just flash it to the session.
            return redirect()->to('/users')->with('success', "Password for {$user['username']} reset to: <strong>{$newPassword}</strong>. Please inform the user.");
        } else {
            return redirect()->back()->with('error', 'Failed to reset password.');
        }
    }
}
