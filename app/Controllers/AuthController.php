<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            if (session()->get('role') === 'admin') {
                return redirect()->to('/dashboard');
            } else {
                return redirect()->to('/user/dashboard');
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
                    return redirect()->to('/dashboard');
                } else {
                    return redirect()->to('/user/dashboard');
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
            return redirect()->to('/dashboard');
        }
        return view('auth/register');
    }

    public function registerPost()
    {
        $session = session();
        $model = new UserModel();

        $username = trim($this->request->getVar('username'));
        $email = trim($this->request->getVar('email'));
        $password = $this->request->getVar('password');
        $confirmPassword = $this->request->getVar('confirm_password');

        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $session->setFlashdata('errors', ['All fields are required.']);
            return redirect()->back()->withInput();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $session->setFlashdata('errors', ['Please enter a valid email address.']);
            return redirect()->back()->withInput();
        }

        if ($password !== $confirmPassword) {
            $session->setFlashdata('errors', ['Password and confirm password do not match.']);
            return redirect()->back()->withInput();
        }

        if (strlen($password) < 8) {
            $session->setFlashdata('errors', ['Password must be at least 8 characters long.']);
            return redirect()->back()->withInput();
        }

        if (!preg_match('/[a-zA-Z]/', $password)) {
            $session->setFlashdata('errors', ['Password must contain at least one letter.']);
            return redirect()->back()->withInput();
        }

        if (!preg_match('/[0-9]/', $password)) {
            $session->setFlashdata('errors', ['Password must contain at least one number.']);
            return redirect()->back()->withInput();
        }

        $existingEmail = $model->where('email', $email)->first();
        if ($existingEmail) {
            $session->setFlashdata('errors', ['This email is already registered. Please use a different email or try logging in.']);
            return redirect()->back()->withInput();
        }

        $existingUsername = $model->where('username', $username)->first();
        if ($existingUsername) {
            $session->setFlashdata('errors', ['This username is already taken. Please choose a different username.']);
            return redirect()->back()->withInput();
        }

        $data = [
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => 'admin', // Default role for self-registration
            'status'   => 'active'
        ];

        if ($model->save($data)) {
            $session->setFlashdata('success', 'Account created successfully! Please log in with your credentials.');
            return redirect()->to('/login');
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
        return redirect()->to('/login')->with('success', 'You have been logged out successfully.');
    }
}