<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('role');
            if ($role === 'admin') {
                return redirect()->to(base_url('dashboard'));
            } elseif ($role === 'enforcer') {
                return redirect()->to(base_url('officer'));
            } elseif ($role === 'driver' || $role === 'user') {
                return redirect()->to(base_url('user/dashboard'));
            } else {
                session()->destroy();
                return redirect()->to(base_url('login'))->with('error', 'This account role is not allowed to sign in.');
            }
        }
        return view('auth/login');
    }

    public function loginPost()
    {
        $session = session();
        $model = new UserModel();

        $email = trim($this->request->getVar('email'));
        $password = $this->request->getVar('password');

        if (empty($email) || empty($password)) {
            $session->setFlashdata('error', 'Please fill in all fields.');
            return redirect()->back()->withInput();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $session->setFlashdata('error', 'Please enter a valid email address.');
            return redirect()->back()->withInput();
        }

        $user = $model->where('email', $email)->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $sessionData = [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                    'role'     => $user['role'],
                    'isLoggedIn' => true,
                ];
                $session->set($sessionData);
                $session->setFlashdata('success', 'Welcome back!');
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    return redirect()->to(base_url('dashboard'));
                } elseif ($user['role'] === 'enforcer') {
                    return redirect()->to(base_url('officer'));
                } elseif ($user['role'] === 'driver' || $user['role'] === 'user') {
                    return redirect()->to(base_url('user/dashboard'));
                } else {
                    $session->destroy();
                    return redirect()->to(base_url('login'))->with('error', 'This account role is not allowed to sign in.');
                }
            } else {
                $session->setFlashdata('warning', 'Incorrect password. Please try again.');
                return redirect()->back()->withInput();
            }
        } else {
            $session->setFlashdata('error', 'No account found with that email address.');
            return redirect()->back()->withInput();
        }
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/register');
    }

    public function registerPost()
    {
        $session = session();
        $model = new UserModel();

        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'firstname'=> 'required|min_length[2]|max_length[100]',
            'lastname' => 'required|min_length[2]|max_length[100]',
            'middle_initial' => 'permit_empty|max_length[10]',
            'age'      => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[120]',
            'birthdate'=> 'required|valid_date',
            'address'  => 'required|max_length[255]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            $session->setFlashdata('errors', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }

        $data = [
            'username'  => trim((string) $this->request->getVar('username')),
            'email'     => trim((string) $this->request->getVar('email')),
            'firstname' => trim((string) $this->request->getVar('firstname')),
            'lastname'  => trim((string) $this->request->getVar('lastname')),
            'middle_initial' => trim((string) $this->request->getVar('middle_initial')),
            'age'       => (int) $this->request->getVar('age'),
            'birthdate' => $this->request->getVar('birthdate'),
            'address'   => trim((string) $this->request->getVar('address')),
            'password'  => (string) $this->request->getVar('password'),
            'role'      => 'driver',
            'status'    => 'active'
        ];

        if ($model->save($data)) {
            $session->setFlashdata('success', 'Account created successfully! Please log in with your credentials.');
            return redirect()->to(base_url('login'));
        } else {
            $session->setFlashdata('errors', $model->errors());
            return redirect()->back()->withInput();
        }
    }

    public function logout()
    {
        $session = session();
        $username = $session->get('username');
        $session->destroy();
        return redirect()->to(base_url('login'))->with('success', 'You have been logged out successfully.');
    }
}