<?php

namespace App\Models;

use CodeIgniter\Model;

abstract class SeasonScopedModel extends Model
{
    protected $beforeFind = ['applyActiveSeasonScope'];
    protected $beforeInsert = ['attachActiveSeason'];

    protected function applyActiveSeasonScope(array $data): array
    {
        if (! $this->supportsSeasonScope()) {
            return $data;
        }

        if (! function_exists('active_season_id')) {
            return $data;
        }

        $seasonId = active_season_id();
        if ($seasonId === null || $seasonId < 1) {
            return $data;
        }

        if (! isset($data['where']) || ! is_array($data['where'])) {
            $data['where'] = [];
        }

        if (! array_key_exists('season_id', $data['where']) && ! array_key_exists($this->table . '.season_id', $data['where'])) {
            $data['where'][$this->table . '.season_id'] = $seasonId;
        }

        return $data;
    }

    protected function attachActiveSeason(array $data): array
    {
        if (! $this->supportsSeasonScope()) {
            return $data;
        }

        if (! function_exists('active_season_id')) {
            return $data;
        }

        $seasonId = active_season_id();
        if ($seasonId === null || $seasonId < 1) {
            return $data;
        }

        if (isset($data['data']) && is_array($data['data']) && ! isset($data['data']['season_id'])) {
            $data['data']['season_id'] = $seasonId;
        }

        return $data;
    }

    private function supportsSeasonScope(): bool
    {
        static $cache = [];

        if (isset($cache[$this->table])) {
            return $cache[$this->table];
        }

        try {
            $cache[$this->table] = db_connect()->fieldExists('season_id', $this->table);
        } catch (\Throwable $e) {
            $cache[$this->table] = false;
        }

        return $cache[$this->table];
    }
}
