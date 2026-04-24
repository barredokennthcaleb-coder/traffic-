<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'users' => $this->userModel->findAll(),
            'title' => 'User Management'
        ];
        return view('admin/users/index', $data);
    }

    public function create()
    {
        $data = ['title' => 'Add New User'];
        return view('admin/users/create', $data);
    }

    public function store()
    {
        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role'     => $this->request->getPost('role'),
        ];

        // Ensure password is required for new users
        $this->userModel->setValidationRule('password', 'required|min_length[8]');

        if ($this->userModel->save($data)) {
            return redirect()->to('/users')->with('success', 'User created successfully.');
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
            'user'  => $user,
            'title' => 'Edit User'
        ];
        return view('admin/users/edit', $data);
    }

    public function update($id = null)
    {
        $data = [
            'id'       => $id,
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'role'     => $this->request->getPost('role'),
        ];

        // Only update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = $password;
        }

        if ($this->userModel->save($data)) {
            return redirect()->to('/users')->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }
    }

    public function delete($id = null)
    {
        if ($this->userModel->delete($id)) {
            return redirect()->to('/users')->with('success', 'User deleted successfully.');
        } else {
            return redirect()->to('/users')->with('error', 'Failed to delete user.');
        }
    }
}
