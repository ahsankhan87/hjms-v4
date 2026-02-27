<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DisableAdminAccess extends BaseCommand
{
    protected $group       = 'RBAC';
    protected $name        = 'rbac:disable-admin';
    protected $description = 'Safely remove admin role mapping and optionally deactivate user idempotently.';
    protected $usage       = 'rbac:disable-admin [--email admin@example.com] [--role super_admin] [--remove-role 1] [--deactivate-user 1] [--dry-run 0]';
    protected $arguments   = [];
    protected $options     = [
        '--email'           => 'User email to process (required)',
        '--role'            => 'Role name mapping to remove (default: super_admin)',
        '--remove-role'     => 'Set 1 to remove user-role mapping (default: 1)',
        '--deactivate-user' => 'Set 1 to deactivate user account (default: 1)',
        '--dry-run'         => 'Set 1 to preview changes only (default: 0)',
    ];

    public function run(array $params)
    {
        try {
            $this->assertRequiredTables();
        } catch (\DomainException $e) {
            CLI::error($e->getMessage());
            return;
        }

        $emailOption = CLI::getOption('email');
        $roleOption = CLI::getOption('role');

        $email = $emailOption !== null ? (string) $emailOption : '';
        $roleName = $roleOption !== null ? (string) $roleOption : 'super_admin';
        $removeRole = $this->optionBool('remove-role', true);
        $deactivateUser = $this->optionBool('deactivate-user', true);
        $dryRun = $this->optionBool('dry-run', false);

        if ($email === '') {
            CLI::error('Option --email is required.');
            return;
        }

        if (! $removeRole && ! $deactivateUser) {
            CLI::error('Nothing to do. Enable --remove-role=1 and/or --deactivate-user=1.');
            return;
        }

        $db = db_connect();
        $now = date('Y-m-d H:i:s');

        $user = $db->table('users')
            ->select('id, is_active')
            ->where('email', $email)
            ->get()
            ->getRowArray();

        if ($user === null) {
            CLI::write('No user found for email. Nothing changed.', 'yellow');
            return;
        }

        $userId = (int) $user['id'];

        $role = $db->table('roles')
            ->select('id')
            ->where('name', $roleName)
            ->get()
            ->getRowArray();

        $roleId = $role !== null ? (int) $role['id'] : 0;

        $mappingExists = false;
        if ($roleId > 0) {
            $mapping = $db->table('user_roles')
                ->select('id')
                ->where('user_id', $userId)
                ->where('role_id', $roleId)
                ->get()
                ->getRowArray();
            $mappingExists = $mapping !== null;
        }

        CLI::write('Admin rollback preview', 'yellow');
        CLI::write('User: ' . $email . ' (id=' . $userId . ')');
        CLI::write('Role: ' . $roleName . ' (exists=' . ($roleId > 0 ? 'yes' : 'no') . ', mapped=' . ($mappingExists ? 'yes' : 'no') . ')');
        CLI::write('remove-role=' . ($removeRole ? '1' : '0') . ', deactivate-user=' . ($deactivateUser ? '1' : '0') . ', dry-run=' . ($dryRun ? '1' : '0'));

        if ($dryRun) {
            CLI::write('Dry run complete. No changes applied.', 'green');
            return;
        }

        $removed = 0;
        if ($removeRole && $mappingExists) {
            $removed = $db->table('user_roles')
                ->where('user_id', $userId)
                ->where('role_id', $roleId)
                ->delete() ? 1 : 0;
        }

        $deactivated = 0;
        if ($deactivateUser && (int) $user['is_active'] !== 0) {
            $deactivated = $db->table('users')
                ->where('id', $userId)
                ->update([
                    'is_active'  => 0,
                    'updated_at' => $now,
                ]) ? 1 : 0;
        }

        CLI::write('Admin rollback completed.', 'green');
        CLI::write('Role mappings removed: ' . $removed);
        CLI::write('Users deactivated: ' . $deactivated);
    }

    private function assertRequiredTables()
    {
        $db = db_connect();
        $required = ['users', 'roles', 'user_roles'];

        foreach ($required as $table) {
            if (! $db->tableExists($table)) {
                throw new \DomainException('Required RBAC table missing: ' . $table . '. Run `php spark rbac:bootstrap` first.');
            }
        }
    }

    private function optionBool($name, $default)
    {
        $value = CLI::getOption($name);

        if ($value === null) {
            return $default;
        }

        return (string) $value === '1';
    }
}
