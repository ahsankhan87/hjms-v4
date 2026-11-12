<?php

namespace App\Services\Frontend;

use App\Models\AgentModel;
use App\Models\UserModel;
use DomainException;

class FrontendAuthService
{
    private const TYPE_B2C = 'b2c';
    private const TYPE_B2B = 'b2b';

    public function register(array $payload): array
    {
        $accountType = strtolower(trim((string) ($payload['account_type'] ?? self::TYPE_B2C)));
        if (! in_array($accountType, [self::TYPE_B2C, self::TYPE_B2B], true)) {
            throw new DomainException('Invalid account type selected.');
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $password = (string) ($payload['password'] ?? '');
        $phone = trim((string) ($payload['phone'] ?? ''));
        $agencyName = trim((string) ($payload['agency_name'] ?? ''));

        if ($name === '' || $email === '' || $password === '') {
            throw new DomainException('Name, email, and password are required.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Please provide a valid email address.');
        }

        if (strlen($password) < 8) {
            throw new DomainException('Password must be at least 8 characters.');
        }

        $userModel = new UserModel();
        $existing = $userModel->where('email', $email)->first();
        if ($existing !== null) {
            throw new DomainException('This email is already registered.');
        }

        $db = db_connect();
        $db->transStart();

        $agentModel = new AgentModel();
        $agentName = $accountType === self::TYPE_B2B ? ($agencyName !== '' ? $agencyName : $name) : $name;

        $agentId = (int) $agentModel->insert([
            'branch_id' => null,
            'code' => $this->generateAgentCode($accountType),
            'type' => $accountType === self::TYPE_B2B ? 'agent' : 'customer',
            'name' => $agentName,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'commission_type' => null,
            'commission_value' => null,
            'credit_limit' => 0,
            'current_balance' => 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], true);

        $userId = (int) $userModel->insert([
            'name' => $name,
            'email' => $email,
            'user_agent_id' => $agentId > 0 ? $agentId : null,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], true);

        $db->transComplete();
        if (! $db->transStatus() || $userId < 1) {
            throw new DomainException('Unable to create account right now. Please try again.');
        }

        return $this->login($email, $password, $accountType);
    }

    public function login(string $email, string $password, string $portal = 'any'): array
    {
        $portal = strtolower(trim($portal));
        if (! in_array($portal, ['any', self::TYPE_B2B, self::TYPE_B2C], true)) {
            $portal = 'any';
        }

        $userModel = new UserModel();
        $user = $userModel
            ->where('email', strtolower(trim($email)))
            ->where('is_active', 1)
            ->first();

        if ($user === null || ! password_verify($password, (string) $user['password_hash'])) {
            throw new DomainException('Invalid login credentials.');
        }

        $agent = $this->resolveUserAgent((int) ($user['id'] ?? 0), (int) ($user['user_agent_id'] ?? 0));
        $userType = $this->resolveUserType($agent);

        if ($portal !== 'any' && $portal !== $userType) {
            throw new DomainException('This account is not allowed for the selected portal.');
        }

        $userModel->update((int) $user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $profile = [
            'id' => (int) $user['id'],
            'name' => (string) ($user['name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'type' => $userType,
            'agent' => $agent,
        ];

        session()->set([
            'frontend_is_logged_in' => true,
            'frontend_user_id' => $profile['id'],
            'frontend_user_name' => $profile['name'],
            'frontend_user_email' => $profile['email'],
            'frontend_user_type' => $profile['type'],
            'frontend_agent_id' => (int) ($agent['id'] ?? 0),
        ]);

        return $profile;
    }

    public function logout(): void
    {
        session()->remove([
            'frontend_is_logged_in',
            'frontend_user_id',
            'frontend_user_name',
            'frontend_user_email',
            'frontend_user_type',
            'frontend_agent_id',
        ]);
        session()->regenerate(true);
    }

    public function currentUser(): ?array
    {
        if (! session('frontend_is_logged_in') || (int) session('frontend_user_id') < 1) {
            return null;
        }

        $userId = (int) session('frontend_user_id');
        $agentId = (int) session('frontend_agent_id');

        $userRow = (new UserModel())->select('id, name, email')->where('id', $userId)->first();
        if ($userRow === null) {
            return null;
        }

        $agentRow = null;
        if ($agentId > 0) {
            $agentRow = (new AgentModel())
                ->select('id, branch_id, type, name, email, phone, commission_type, commission_value, credit_limit, current_balance, is_active')
                ->where('id', $agentId)
                ->first();
        }

        return [
            'id' => (int) $userRow['id'],
            'name' => (string) ($userRow['name'] ?? ''),
            'email' => (string) ($userRow['email'] ?? ''),
            'type' => (string) session('frontend_user_type'),
            'agent' => $agentRow,
        ];
    }

    private function resolveUserAgent(int $userId, int $userAgentId): ?array
    {
        if ($userId < 1) {
            return null;
        }

        $agentModel = new AgentModel();
        $query = $agentModel->select('id, branch_id, type, name, email, phone, commission_type, commission_value, credit_limit, current_balance, is_active');

        $agent = null;
        if ($userAgentId > 0) {
            $agent = $query->where('id', $userAgentId)->where('is_active', 1)->first();
        }

        if ($agent !== null) {
            return $agent;
        }

        if (db_connect()->fieldExists('user_id', 'agents')) {
            return $agentModel
                ->select('id, branch_id, type, name, email, phone, commission_type, commission_value, credit_limit, current_balance, is_active')
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->orderBy('id', 'ASC')
                ->first();
        }

        return null;
    }

    private function resolveUserType(?array $agent): string
    {
        $agentType = strtolower(trim((string) ($agent['type'] ?? '')));
        if ($agentType === 'agent') {
            return self::TYPE_B2B;
        }

        return self::TYPE_B2C;
    }

    private function generateAgentCode(string $accountType): string
    {
        $prefix = $accountType === self::TYPE_B2B ? 'AG' : 'CU';
        $stamp = date('ymd');
        $rand = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix . '-' . $stamp . '-' . $rand;
    }
}

