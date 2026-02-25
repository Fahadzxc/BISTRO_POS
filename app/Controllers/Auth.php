<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    /**
     * Role-based redirect targets.
     */
    private const ROLE_REDIRECT = [
        'admin'   => '/dashboard',
        'cashier' => '/pos',
        'staff'   => '/ktv-rooms',
    ];

    /**
     * Show login form.
     */
    public function login()
    {
        helper(['form', 'url']);

        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole(session()->get('role'));
        }

        return view('templates/template', [
            'title'     => 'Login | KTV Bistro POS',
            'bodyClass' => 'layout-login',
            'content'   => view('auth/login'),
        ]);
    }

    /**
     * Handle login submission.
     */
    public function authenticate()
    {
        helper(['form', 'url']); // url needed for site_url in redirectByRole

        $session = session();
        $rules   = [
            'email'    => [
                'rules'  => 'required|valid_email',
                'errors' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                ],
            ],
            'password' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Password is required.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return view('templates/template', [
                'title'     => 'Login | KTV Bistro POS',
                'bodyClass' => 'layout-login',
                'content'   => view('auth/login', ['validation' => $this->validator]),
            ]);
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->findByEmail($email);

        if (! $user) {
            $session->setFlashdata('error', 'Invalid email or password.');
            return redirect()->back()->withInput();
        }

        if (($user['status'] ?? 'active') !== 'active') {
            $session->setFlashdata('error', 'Your account has been deactivated. Contact administrator.');
            return redirect()->back()->withInput();
        }

        if (! password_verify($password, $user['password'])) {
            $session->setFlashdata('error', 'Invalid email or password.');
            return redirect()->back()->withInput();
        }

        $session->set([
            'user_id'    => $user['user_id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'isLoggedIn' => true,
        ]);

        return $this->redirectByRole($user['role']);
    }

    /**
     * Dashboard (admin only by default; filter can allow others).
     */
    public function dashboard()
    {
        helper('url');
        $productModel = new ProductModel();
        $inventoryStats = [
            'low_stock_count'   => $productModel->getLowStockCount(),
            'out_of_stock_count'=> $productModel->getOutOfStockCount(),
        ];

        return view('templates/template', [
            'title'     => 'Dashboard | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('dashboard', $inventoryStats),
        ]);
    }

    /**
     * Logout.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }

    /**
     * Redirect user to their role-specific landing page.
     */
    private function redirectByRole(string $role)
    {
        $target = self::ROLE_REDIRECT[$role] ?? '/dashboard';
        return redirect()->to(site_url($target));
    }
}
