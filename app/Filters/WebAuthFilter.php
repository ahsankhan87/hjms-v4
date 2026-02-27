<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class WebAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('is_logged_in') || ! session('user_id')) {
            return redirect()->to('/app/login');
        }

        if (function_exists('auth_is_super_admin') && auth_is_super_admin()) {
            return null;
        }

        $permissions = session('auth_permissions');
        if ($this->rbacTablesExist()) {
            service('authService')->refreshAuthorization((int) session('user_id'));
            $permissions = session('auth_permissions');
        }

        if (! empty($arguments)) {
            $required = (string) ($arguments[0] ?? '');
            if ($required !== '') {
                $items = is_array($permissions) ? $permissions : [];
                $allowed = in_array($required, $items, true);

                if (! $allowed) {
                    return redirect()->to('/app/unauthorized')
                        ->with('error', 'You do not have permission to access this section.');
                }
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function rbacTablesExist(): bool
    {
        $db = db_connect();

        return $db->tableExists('roles')
            && $db->tableExists('permissions')
            && $db->tableExists('user_roles')
            && $db->tableExists('role_permissions');
    }
}
