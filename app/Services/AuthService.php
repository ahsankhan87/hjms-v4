<?php

namespace App\Services;

use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;

class AuthService
{
    private $rolesHasIsActive;

    public function login(string $email, string $password): void
    {
        $userModel = new UserModel();
        $user = $userModel
            ->where('email', $email)
            ->where('is_active', 1)
            ->first();

        if ($user === null || ! password_verify($password, (string) $user['password_hash'])) {
            throw new \DomainException('Invalid credentials.');
        }

        $userModel->update((int) $user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $authz = $this->buildAuthorization((int) $user['id']);

        session()->set([
            'is_logged_in'       => true,
            'user_id'            => (int) $user['id'],
            'user_email'         => (string) $user['email'],
            'auth_roles'         => $authz['roles'],
            'auth_permissions'   => $authz['permissions'],
        ]);
    }

    public function logout(): void
    {
        session()->remove(['is_logged_in', 'user_id', 'user_email', 'auth_roles', 'auth_permissions']);
        session()->regenerate(true);
    }

    public function refreshAuthorization(int $userId): array
    {
        $authz = $this->buildAuthorization($userId);
        session()->set([
            'auth_roles'       => $authz['roles'],
            'auth_permissions' => $authz['permissions'],
        ]);

        return $authz;
    }

    public function requestPasswordReset(string $email)
    {
        $userModel = new UserModel();
        $user = $userModel
            ->where('email', $email)
            ->where('is_active', 1)
            ->first();

        if ($user === null) {
            return null;
        }

        $tokenModel = new PasswordResetTokenModel();
        $now = date('Y-m-d H:i:s');

        $tokenModel
            ->where('user_id', (int) $user['id'])
            ->where('used_at', null)
            ->set(['used_at' => $now])
            ->update();

        $plainToken = bin2hex(random_bytes(32));
        $tokenModel->insert([
            'user_id'    => (int) $user['id'],
            'email'      => $email,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'created_at' => $now,
        ]);

        return $plainToken;
    }

    public function resetPasswordByToken(string $token, string $newPassword): bool
    {
        $tokenHash = hash('sha256', $token);
        $tokenModel = new PasswordResetTokenModel();
        $row = $tokenModel
            ->where('token_hash', $tokenHash)
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();

        if ($row === null) {
            return false;
        }

        $userModel = new UserModel();
        $updated = $userModel
            ->where('id', (int) $row['user_id'])
            ->set([
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'updated_at'    => date('Y-m-d H:i:s'),
            ])
            ->update();

        if (! $updated) {
            return false;
        }

        $tokenModel->update((int) $row['id'], ['used_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    private function buildAuthorization(int $userId): array
    {
        $db = db_connect();

        $rolesHasIsActive = $this->rolesHasIsActive;
        if ($rolesHasIsActive === null) {
            $rolesHasIsActive = $db->fieldExists('is_active', 'roles');
            $this->rolesHasIsActive = $rolesHasIsActive;
        }

        $rolesQuery = $db->table('user_roles ur')
            ->select('r.name')
            ->join('roles r', 'r.id = ur.role_id', 'inner')
            ->where('ur.user_id', $userId);

        if ($rolesHasIsActive) {
            $rolesQuery->where('r.is_active', 1);
        }

        $rolesRows = $rolesQuery->get()->getResultArray();

        $permissionsQuery = $db->table('user_roles ur')
            ->select('p.name')
            ->join('roles r', 'r.id = ur.role_id', 'inner')
            ->join('role_permissions rp', 'rp.role_id = r.id', 'inner')
            ->join('permissions p', 'p.id = rp.permission_id', 'inner')
            ->where('ur.user_id', $userId);

        if ($rolesHasIsActive) {
            $permissionsQuery->where('r.is_active', 1);
        }

        $permissionsRows = $permissionsQuery->get()->getResultArray();

        $roles = [];
        foreach ($rolesRows as $item) {
            $roles[] = (string) $item['name'];
        }

        $permissions = [];
        foreach ($permissionsRows as $item) {
            $permissions[] = (string) $item['name'];
        }

        return [
            'roles'       => array_values(array_unique($roles)),
            'permissions' => array_values(array_unique($permissions)),
        ];
    }
}
