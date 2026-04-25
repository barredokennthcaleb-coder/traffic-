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
        $role = (string) ($this->request->getGet('role') ?? '');
        $allowedRoles = ['admin', 'driver', 'enforcer'];

        $usersQuery = $this->userModel;
        if ($role !== '' && in_array($role, $allowedRoles, true)) {
            $usersQuery = $usersQuery->where('role', $role);
        } else {
            $role = '';
        }

        $data = [
            'title' => 'User Management',
            'selectedRole' => $role,
            'users' => $usersQuery->findAll(),
        ];
        return view('admin/users/index', $data);
    }

    public function viewDriver($id = null)
    {
        $user = $this->userModel->find($id);
        if (!$user || $user['role'] !== 'driver') {
            return redirect()->to(base_url('users?role=driver'))->with('error', 'Driver not found.');
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
            'defaults' => [
                'username' => (string) (env('auth.defaultAdminUsername') ?? ''),
                'password' => (string) (env('auth.defaultAdminPassword') ?? env('auth.defaultUserPassword') ?? ''),
                'role'     => 'driver',
            ],
        ];
        return view('admin/users/create', $data);
    }

    public function store()
    {
        $defaultPassword = (string) (env('auth.defaultUserPassword') ?? '');
        $defaultRole = 'driver';

        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            // allow shorter password when default env password is used
            'password' => $defaultPassword !== '' ? 'permit_empty|min_length[5]' : 'required|min_length[8]',
            'role'     => 'permit_empty|in_list[admin,driver,enforcer]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = (string) ($this->request->getPost('role') ?: $defaultRole);
        $password = (string) $this->request->getPost('password');
        if ($password === '' && $defaultPassword !== '') {
            $password = $defaultPassword;
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $password,
            'role'     => $role,
            'status'   => 'active',
        ];

        if ($this->userModel->save($data)) {
            return redirect()->to(base_url('users') . ($data['role'] ? '?role=' . $data['role'] : ''))->with('success', 'User created successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }
    }

    public function edit($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
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
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $rules = [
            'username' => "required|alpha_numeric_space|min_length[3]|is_unique[users.username,id,{$id}]",
            'email'    => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role'     => 'required|in_list[admin,driver,enforcer]',
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
            return redirect()->to(base_url('users') . ($data['role'] ? '?role=' . $data['role'] : ''))->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }
    }

    public function delete($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $role = $user['role'];
        if ($this->userModel->delete($id)) {
            return redirect()->to(base_url('users') . ($role ? '?role=' . $role : ''))->with('success', 'User deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete user.');
        }
    }

    public function resetPassword($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Generate a simple 8-character password for easier copying
        $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

        if ($this->userModel->update($id, ['password' => $newPassword])) {
            return redirect()->to(base_url('users') . ($user['role'] ? '?role=' . $user['role'] : ''))->with('success', "Password for <strong>{$user['username']}</strong> reset to: <strong class='text-danger'>{$newPassword}</strong>. Please copy this password now.");
        } else {
            return redirect()->back()->with('error', 'Failed to reset password.');
        }
    }
}
