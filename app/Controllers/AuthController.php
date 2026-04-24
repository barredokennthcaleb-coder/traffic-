<?php

namespace App\Controllers;

use App\Models\AdminModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function loginPost()
    {
        $session = session();
        $model = new AdminModel();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        
        $user = $model->where('email', $email)->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $sessionData = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'isLoggedIn' => true,
                ];
                $session->set($sessionData);
                return redirect()->to('/dashboard');
            } else {
                $session->setFlashdata('error', 'Invalid password.');
                return redirect()->back()->withInput();
            }
        } else {
            $session->setFlashdata('error', 'Email not found.');
            return redirect()->back()->withInput();
        }
    }

    public function register()
    {
        return view('auth/register');
    }

    public function registerPost()
    {
        $model = new AdminModel();
        $data = [
            'username' => $this->request->getVar('username'),
            'email'    => $this->request->getVar('email'),
            'password' => $this->request->getVar('password'),
        ];

        if ($model->save($data)) {
            return redirect()->to('/login')->with('success', 'Registration successful. Please login.');
        } else {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
