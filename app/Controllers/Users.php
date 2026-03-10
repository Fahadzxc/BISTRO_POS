<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    /**
     * List all users (admin only).
     */
    public function index()
    {
        helper('url');
        $model = new UserModel();
        return view('templates/template', [
            'title'     => 'Users | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('users/index', [
                'users' => $model->getAllUsers(),
            ]),
        ]);
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        helper(['form', 'url']);
        return view('templates/template', [
            'title'     => 'Create User | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('users/create', [
                'roles' => UserModel::allowedRoles(),
            ]),
        ]);
    }

    /**
     * Store new user.
     */
    public function store()
    {
        helper(['form', 'url']);
        $rules = [
            'name' => [
                'rules'  => 'required|max_length[100]',
                'errors' => ['required' => 'Name is required.'],
            ],
            'email' => [
                'rules'  => 'required|valid_email|max_length[150]',
                'errors' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Enter a valid email address.',
                ],
            ],
            'password' => [
                'rules'  => 'required|min_length[6]',
                'errors' => [
                    'required'   => 'Password is required.',
                    'min_length' => 'Password must be at least 6 characters.',
                ],
            ],
            'role' => [
                'rules'  => 'required|in_list[admin,cashier,staff]',
                'errors' => ['required' => 'Role is required.'],
            ],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new UserModel();
        $email = trim($this->request->getPost('email'));
        if ($model->findByEmail($email)) {
            return redirect()->back()->withInput()->with('error', 'Email is already in use.');
        }

        $name     = trim($this->request->getPost('name'));
        $password = $this->request->getPost('password');
        $role     = $this->request->getPost('role');

        try {
            $model->insert([
                'name'     => $name,
                'email'    => $email,
                'password' => $password,
                'role'     => $role,
                'status'   => 'active',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Users::store - ' . $e->getMessage());
            $err = $e->getMessage();
            // Fallback: try insert without status/created_at (old or minimal schema)
            try {
                $db = \Config\Database::connect();
                $db->table('users')->insert([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role'     => $role,
                ]);
            } catch (\Throwable $e2) {
                log_message('error', 'Users::store fallback - ' . $e2->getMessage());
                return redirect()->back()->withInput()->with('error', 'Could not create user. Error: ' . $e2->getMessage());
            }
        }
        return redirect()->to(site_url('users'))->with('success', 'User created.');
    }

    /**
     * Show edit user form. Logged-in admin cannot edit themselves.
     */
    public function edit(int $id)
    {
        helper(['form', 'url']);
        $currentUserId = (int) session()->get('user_id');
        if ($id === $currentUserId) {
            return redirect()->to(site_url('users'))->with('error', 'You cannot edit your own account. Another admin can edit your profile.');
        }

        $model = new UserModel();
        $user = $model->find($id);
        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User not found.');
        }

        return view('templates/template', [
            'title'     => 'Edit User | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('users/edit', [
                'user'  => $user,
                'roles' => UserModel::allowedRoles(),
            ]),
        ]);
    }

    /**
     * Update user. Prevent updating self (role/status/email/name).
     */
    public function update(int $id)
    {
        helper(['form', 'url']);
        $currentUserId = (int) session()->get('user_id');
        if ($id === $currentUserId) {
            return redirect()->to(site_url('users'))->with('error', 'You cannot edit your own account. Another admin can edit your profile.');
        }

        $model = new UserModel();
        $user = $model->find($id);
        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User not found.');
        }

        $rules = [
            'name' => [
                'rules'  => 'required|max_length[100]',
                'errors' => ['required' => 'Name is required.'],
            ],
            'email' => [
                'rules'  => 'required|valid_email|max_length[150]',
                'errors' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Enter a valid email address.',
                ],
            ],
            'role' => [
                'rules'  => 'required|in_list[admin,cashier,staff]',
                'errors' => ['required' => 'Role is required.'],
            ],
            'status' => [
                'rules'  => 'required|in_list[active,inactive]',
                'errors' => ['required' => 'Status is required.'],
            ],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = trim($this->request->getPost('email'));
        if ($model->emailExistsForAnotherUser($id, $email)) {
            return redirect()->back()->withInput()->with('error', 'Email is already in use by another user.');
        }

        $model->update($id, [
            'name'   => trim($this->request->getPost('name')),
            'email'  => $email,
            'role'   => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
        ]);
        return redirect()->to(site_url('users'))->with('success', 'User updated.');
    }

    /**
     * Toggle user status (active/inactive). Cannot disable self.
     */
    public function disable(int $id)
    {
        helper('url');
        $currentUserId = (int) session()->get('user_id');
        if ($id === $currentUserId) {
            return redirect()->to(site_url('users'))->with('error', 'You cannot disable your own account.');
        }

        $model = new UserModel();
        $user = $model->find($id);
        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User not found.');
        }

        $newStatus = ($user['status'] ?? 'active') === 'active' ? 'inactive' : 'active';
        $model->update($id, ['status' => $newStatus]);
        $msg = $newStatus === 'inactive' ? 'User disabled.' : 'User activated.';
        return redirect()->to(site_url('users'))->with('success', $msg);
    }

    /**
     * Delete user. Cannot delete self.
     */
    public function delete(int $id)
    {
        helper('url');
        $currentUserId = (int) session()->get('user_id');
        if ($id === $currentUserId) {
            return redirect()->to(site_url('users'))->with('error', 'You cannot delete your own account.');
        }

        $model = new UserModel();
        $user = $model->find($id);
        if (! $user) {
            return redirect()->to(site_url('users'))->with('error', 'User not found.');
        }

        $model->delete($id);
        return redirect()->to(site_url('users'))->with('success', 'User deleted.');
    }
}
