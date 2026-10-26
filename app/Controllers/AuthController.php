<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole((string) session()->get('role'));
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

        // A wrong email and a wrong password return the same message so the
        // form never reveals which accounts exist.
        if (! $user || ! password_verify($password, (string) $user['password'])) {
            $session->setFlashdata('error', 'The password you entered is incorrect.');
            return redirect()->back()->withInput();
        }

        $sessionData = [
            'id'            => $user['id'],
            'username'      => $user['username'],
            'email'         => $user['email'],
            'role'          => $user['role'],
            'profile_image' => $user['profile_image'] ?? null,
            'isLoggedIn'    => true,
        ];
        $session->set($sessionData);
        $session->setFlashdata('success', 'Welcome back!');

        return $this->redirectByRole((string) $user['role']);
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
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'You have been logged out successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Returns the correct redirect response for a given user role.
     * Centralises the role→URL mapping so login() and loginPost() share
     * one implementation instead of copy-pasting the same if/elseif block.
     */
    private function redirectByRole(string $role): \CodeIgniter\HTTP\RedirectResponse
    {
        return match ($role) {
            'admin'           => redirect()->to(base_url('dashboard')),
            'enforcer'        => redirect()->to(base_url('officer')),
            'driver', 'user'  => redirect()->to(base_url('user/dashboard')),
            default           => (function () {
                session()->destroy();
                return redirect()->to(base_url('login'))
                    ->with('error', 'This account role is not allowed to sign in.');
            })(),
        };
    }

    public function forgotPassword()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/forgot_password');
    }

    public function forgotPasswordPost()
    {
        $session = session();
        $model = new UserModel();
        $email = $this->request->getVar('email');

        $user = $model->where('email', $email)->first();

        if ($user) {
            // In a real application, you would send a reset link via email.
            // For this project, we will reset the password to the default password from .env
            $defaultPassword = (string) (env('auth.defaultUserPassword') ?? '00000');
            
            $data = [
                'password' => $defaultPassword
            ];

            if ($model->update($user['id'], $data)) {
                return redirect()->back()->with('success', "Password has been reset to the default password ('{$defaultPassword}'). Please log in and change it immediately.");
            } else {
                return redirect()->back()->with('error', 'Failed to reset password. Please try again later.');
            }
        } else {
            return redirect()->back()->with('error', 'No account found with that email address.');
        }
    }
}