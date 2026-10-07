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

if (! function_exists('active_agent_id')) {
    function active_agent_id()
    {
        static $resolved = false;
        static $linkedAgentId = null;

        if ($resolved) {
            return $linkedAgentId;
        }

        $resolved = true;

        if (! session('is_logged_in')) {
            return null;
        }

        if (function_exists('auth_is_super_admin') && auth_is_super_admin()) {
            return null;
        }

        $sessionAgentId = session('user_agent_id');
        if (is_numeric($sessionAgentId) && (int) $sessionAgentId > 0) {
            $linkedAgentId = (int) $sessionAgentId;

            return $linkedAgentId;
        }

        $userId = (int) (session('user_id') ?? 0);
        if ($userId < 1) {
            return null;
        }

        try {
            $db = db_connect();

            if ($db->fieldExists('user_agent_id', 'users')) {
                $userRow = $db->table('users')
                    ->select('user_agent_id')
                    ->where('id', $userId)
                    ->get()
                    ->getRowArray();

                $resolvedAgentId = (int) ($userRow['user_agent_id'] ?? 0);
                if ($resolvedAgentId > 0) {
                    $linkedAgentId = $resolvedAgentId;
                    session()->set('user_agent_id', $linkedAgentId);

                    return $linkedAgentId;
                }
            }

            if ($db->tableExists('agents') && $db->fieldExists('user_id', 'agents')) {
                $agentRow = $db->table('agents')
                    ->select('id')
                    ->where('user_id', $userId)
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getRowArray();

                $resolvedAgentId = (int) ($agentRow['id'] ?? 0);
                if ($resolvedAgentId > 0) {
                    $linkedAgentId = $resolvedAgentId;
                    session()->set('user_agent_id', $linkedAgentId);

                    return $linkedAgentId;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
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

if (! function_exists('company_table_ready')) {
    function company_table_ready(): bool
    {
        try {
            $db = db_connect();
            $db->query('SELECT 1 FROM companies LIMIT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (! function_exists('active_company')) {
    function active_company()
    {
        static $company = null;
        static $resolved = false;

        if ($resolved) {
            return $company;
        }

        $resolved = true;
        if (! company_table_ready()) {
            return null;
        }

        $db = db_connect();
        $row = $db->table('companies')
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($row === null) {
            $row = $db->table('companies')->orderBy('id', 'DESC')->get()->getRowArray();
        }

        $company = is_array($row) ? $row : null;

        return $company;
    }
}

if (! function_exists('main_company_table_ready')) {
    function main_company_table_ready(): bool
    {
        try {
            $db = db_connect();
            $db->query('SELECT 1 FROM main_company LIMIT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (! function_exists('main_company')) {
    function main_company()
    {
        static $company = null;
        static $resolved = false;

        if ($resolved) {
            return $company;
        }

        $resolved = true;

        if (main_company_table_ready()) {
            $row = db_connect()->table('main_company')->orderBy('id', 'ASC')->get()->getRowArray();
            if (is_array($row)) {
                $company = $row;

                return $company;
            }
        }

        $company = [
            'name' => 'KARWAN-E-TAIF PVT LTD',
            'tagline' => 'Hajj & Umrah Management',
            'address' => '',
            'phone' => '',
            'email' => '',
            'website' => '',
            'logo_url' => '',
            'ntn' => '',
            'strn' => '',
        ];

        return $company;
    }
}

if (! function_exists('asset_upload_dir')) {
    /**
     * Absolute filesystem path to an assets/uploads sub-directory (created when missing).
     *
     * All user uploads live under assets/uploads so they stay publicly reachable
     * (the writable/ directory is blocked from web access by its .htaccess).
     */
    function asset_upload_dir(string $subdir = ''): string
    {
        $path = rtrim((string) FCPATH, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'uploads';

        $subdir = trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $subdir), DIRECTORY_SEPARATOR);
        if ($subdir !== '') {
            $path .= DIRECTORY_SEPARATOR . $subdir;
        }

        if (! is_dir($path)) {
            @mkdir($path, 0775, true);
        }

        return $path;
    }
}

if (! function_exists('asset_upload_url')) {
    /**
     * Resolve a stored upload value (bare filename or relative path) to a public URL.
     */
    function asset_upload_url($stored, string $subdir = ''): string
    {
        $stored = trim((string) $stored);
        if ($stored === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $stored) === 1 || str_starts_with($stored, 'data:')) {
            return $stored;
        }

        $normalized = ltrim(str_replace('\\', '/', $stored), '/');

        if (str_starts_with($normalized, 'assets/uploads/')) {
            return base_url($normalized);
        }

        if (str_starts_with($normalized, 'uploads/')) {
            return base_url('assets/' . $normalized);
        }

        // Bare filename: stored inside the module sub-directory under assets/uploads.
        $subdir = trim(str_replace('\\', '/', $subdir), '/');

        return base_url('assets/uploads/' . ($subdir !== '' ? $subdir . '/' : '') . $normalized);
    }
}

if (! function_exists('asset_upload_file')) {
    /**
     * Resolve a stored upload value to an absolute filesystem path.
     *
     * Prefers the new assets/uploads location and falls back to the legacy
     * writable/uploads folder so existing records keep working.
     */
    function asset_upload_file($stored, string $subdir = ''): string
    {
        $stored = trim((string) $stored);
        if ($stored === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $stored) === 1) {
            $path = ltrim(str_replace('\\', '/', (string) parse_url($stored, PHP_URL_PATH)), '/');
            // Strip any base-path prefix (app may run inside a sub-folder such as /kasbook/hjms-v4/).
            $assetsPos = strpos($path, 'assets/uploads/');
            if ($assetsPos !== false) {
                $path = substr($path, $assetsPos);
            } else {
                $uploadsPos = strpos($path, 'uploads/');
                if ($uploadsPos !== false) {
                    $path = substr($path, $uploadsPos);
                }
            }
            $stored = $path;
        }

        $normalized = ltrim(str_replace('\\', '/', $stored), '/');
        $fcpath = rtrim((string) FCPATH, DIRECTORY_SEPARATOR);
        $writepath = rtrim((string) WRITEPATH, DIRECTORY_SEPARATOR);

        $toLocal = static function (string $base, string $relative): string {
            return rtrim($base, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        };

        $candidates = [];
        if (str_starts_with($normalized, 'assets/uploads/')) {
            $candidates[] = $toLocal($fcpath, $normalized);
        } elseif (str_starts_with($normalized, 'uploads/')) {
            $candidates[] = $toLocal($fcpath, 'assets/' . $normalized);
            $candidates[] = $toLocal($writepath, $normalized);
        } else {
            $subdir = trim(str_replace('\\', '/', $subdir), '/');
            $candidates[] = $toLocal($fcpath, 'assets/uploads/' . ($subdir !== '' ? $subdir . '/' : '') . $normalized);
            $candidates[] = $toLocal($writepath, 'uploads/' . $normalized);
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $candidates[0] ?? '';
    }
}

if (! function_exists('asset_upload_remove')) {
    /**
     * Delete an uploaded file (resolved from a stored value) if it exists on disk.
     */
    function asset_upload_remove($stored, string $subdir = ''): bool
    {
        $path = asset_upload_file($stored, $subdir);
        if ($path !== '' && is_file($path)) {
            return @unlink($path);
        }

        return false;
    }
}

if (! function_exists('pilgrim_availed_sql')) {
    /**
     * Raw SQL condition, usable inside a query on the `pilgrims` table, that is TRUE
     * when the pilgrim is currently available (NOT availed) for the given season.
     *
     * A pilgrim is considered "availed" when either:
     *   - a confirmed booking exists for the season created after their last release, or
     *   - a non-voided sale exists for the season created after their last release
     *     (when $includeSales is true).
     *
     * Releasing a pilgrim (pilgrims.sale_released_at) lets a returning pilgrim be
     * sold/booked again for a new trip while keeping past trips in history.
     *
     * @param string $seasonIdExpr        SQL expression for the season id (e.g. "12")
     * @param string $pilgrimIdExpr       SQL expression for the pilgrim id of the outer row
     * @param string $releasedExpr        SQL expression for the pilgrim's last release datetime
     * @param string $extraCondition      Extra AND condition appended inside the booking subquery
     *                                    (must reference the booking alias `b_av`)
     * @param bool   $includeBookings     Whether to treat confirmed bookings as availed
     * @param bool   $includeSales        Whether to treat non-voided sales as availed
     * @param bool   $salesHasPilgrimIds  Whether the sales table has a `pilgrim_ids` CSV column
     */
    function pilgrim_availed_sql(
        string $seasonIdExpr,
        string $pilgrimIdExpr = 'pilgrims.id',
        string $releasedExpr = 'pilgrims.sale_released_at',
        string $extraCondition = '',
        bool $includeBookings = true,
        bool $includeSales = false,
        bool $salesHasPilgrimIds = true
    ): string {
        $released = 'COALESCE(' . $releasedExpr . ', "1970-01-01 00:00:00")';
        $extra = trim($extraCondition) !== '' ? ' ' . trim($extraCondition) : '';
        $conditions = [];

        if ($includeBookings) {
            $conditions[] = 'NOT EXISTS (
                SELECT 1
                FROM booking_pilgrims bp_av
                INNER JOIN bookings b_av ON b_av.id = bp_av.booking_id
                WHERE bp_av.pilgrim_id = ' . $pilgrimIdExpr . '
                  AND b_av.season_id = ' . $seasonIdExpr . '
                  AND b_av.status = "confirmed"
                  AND b_av.created_at > ' . $released . $extra . '
            )';
        }

        if ($includeSales) {
            $saleMatch = 's_av.pilgrim_id = ' . $pilgrimIdExpr;
            if ($salesHasPilgrimIds) {
                $saleMatch = '(s_av.pilgrim_id = ' . $pilgrimIdExpr . ' OR FIND_IN_SET(' . $pilgrimIdExpr . ', s_av.pilgrim_ids))';
            }

            $conditions[] = 'NOT EXISTS (
                SELECT 1
                FROM sales s_av
                WHERE s_av.season_id = ' . $seasonIdExpr . '
                  AND IFNULL(s_av.status, "posted") != "voided"
                  AND ' . $saleMatch . '
                  AND s_av.created_at > ' . $released . '
            )';
        }

        if ($conditions === []) {
            return '1 = 1';
        }

        return implode(' AND ', $conditions);
    }
}
