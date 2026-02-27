<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('auth_permissions')) {
    function auth_permissions(): array
    {
        $items = session('auth_permissions');

        return is_array($items) ? $items : [];
    }
}

if (! function_exists('auth_roles')) {
    function auth_roles(): array
    {
        $items = session('auth_roles');

        return is_array($items) ? $items : [];
    }
}

if (! function_exists('auth_is_super_admin')) {
    function auth_is_super_admin(): bool
    {
        foreach (auth_roles() as $role) {
            if (strtolower((string) $role) === 'super_admin') {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('auth_can')) {
    function auth_can(string $permission): bool
    {
        if (auth_is_super_admin()) {
            return true;
        }

        $permissions = auth_permissions();

        return in_array($permission, $permissions, true);
    }
}

if (! function_exists('season_table_ready')) {
    function season_table_ready(): bool
    {
        try {
            $db = db_connect();
            $db->query('SELECT 1 FROM seasons LIMIT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (! function_exists('active_season')) {
    function active_season()
    {
        static $active = null;
        static $resolved = false;

        if ($resolved) {
            return $active;
        }

        $resolved = true;
        if (! season_table_ready()) {
            return null;
        }

        $row = db_connect()->table('seasons')
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        $active = is_array($row) ? $row : null;

        return $active;
    }
}

if (! function_exists('active_season_id')) {
    function active_season_id()
    {
        $season = active_season();
        if (! is_array($season) || empty($season['id'])) {
            return null;
        }

        return (int) $season['id'];
    }
}

if (! function_exists('all_seasons')) {
    function all_seasons(): array
    {
        if (! season_table_ready()) {
            return [];
        }

        return db_connect()->table('seasons')->orderBy('year_start', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
    }
}

if (! function_exists('activate_season')) {
    function activate_season(int $seasonId): bool
    {
        if ($seasonId < 1 || ! season_table_ready()) {
            return false;
        }

        $db = db_connect();
        $exists = $db->table('seasons')->select('id')->where('id', $seasonId)->get()->getRowArray();
        if (empty($exists)) {
            return false;
        }

        $db->transStart();
        $db->table('seasons')->set(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')])->update();
        $db->table('seasons')->where('id', $seasonId)->update(['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        $db->transComplete();

        return $db->transStatus();
    }
}
