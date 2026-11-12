<?php

namespace App\Services\Frontend;

use Config\AgentPricing;
use CodeIgniter\Database\BaseConnection;

class AgentPricingService
{
    private BaseConnection $db;
    private AgentPricing $config;
    /**
     * @var array<string, bool>
     */
    private array $agentPriceColumnExists = [];

    public function __construct(?BaseConnection $db = null, ?AgentPricing $config = null)
    {
        $this->db = $db ?? db_connect();
        $this->config = $config ?? config('AgentPricing');
    }

    public function isCurrentUserAgent(): bool
    {
        return (int) session('user_id') > 0 && strtolower((string) session('role')) === 'agent';
    }

    public function resolveForRow(string $table, int $rowId, float $regularPrice): float
    {
        if ($rowId > 0 && $this->hasAgentPriceColumn($table)) {
            $row = $this->db->table($table)
                ->select('agent_price')
                ->where('id', $rowId)
                ->get()
                ->getRowArray();

            $agentPrice = (float) ($row['agent_price'] ?? 0);
            if ($agentPrice > 0) {
                return round($agentPrice, 2);
            }
        }

        return $this->applyFallback($regularPrice);
    }

    private function hasAgentPriceColumn(string $table): bool
    {
        if (! array_key_exists($table, $this->agentPriceColumnExists)) {
            $this->agentPriceColumnExists[$table] = $this->db->fieldExists('agent_price', $table);
        }

        return $this->agentPriceColumnExists[$table];
    }

    private function applyFallback(float $regularPrice): float
    {
        if ($regularPrice <= 0) {
            return 0.0;
        }

        $percent = max(0.0, (float) $this->config->fallbackPercentage);
        $mode = strtolower(trim((string) $this->config->fallbackMode));

        if ($mode === 'markup') {
            return round($regularPrice + (($regularPrice * $percent) / 100), 2);
        }

        return round(max(0.0, $regularPrice - (($regularPrice * $percent) / 100)), 2);
    }
}

