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

        $username = trim($this->request->getVar('username'));
        $email = trim($this->request->getVar('email'));
        $envDefaultPassword = (string) (env('auth.defaultUserPassword') ?? '');
        $passwordInput = (string) $this->request->getVar('password');
        $confirmPasswordInput = (string) $this->request->getVar('confirm_password');

        // Only fall back to env default when the user leaves it blank.
        $password = $passwordInput !== '' ? $passwordInput : $envDefaultPassword;
        $confirmPassword = $confirmPasswordInput !== '' ? $confirmPasswordInput : $envDefaultPassword;

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

        // If NO default password is set in .env, enforce stronger rules.
        if ($envDefaultPassword === '') {
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
            'role'     => 'driver', // Default role for self-registration
            'status'   => 'active'
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