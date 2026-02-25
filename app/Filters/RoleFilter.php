<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Restrict access by role.
     * $arguments = ['admin', 'cashier'] means only admin and cashier can access.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            $session->setFlashdata('error', 'Please login to continue.');
            return redirect()->to(site_url('login'));
        }

        $userRole    = $session->get('role');
        $allowedRoles = $arguments ?? [];

        if (is_string($allowedRoles)) {
            $allowedRoles = array_map('trim', explode(',', $allowedRoles));
        }
        $allowedRoles = array_filter((array) $allowedRoles);

        if (empty($allowedRoles)) {
            return;
        }

        if (! in_array($userRole, $allowedRoles, true)) {
            $session->setFlashdata('error', 'You do not have permission to access this page.');
            return redirect()->back();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
