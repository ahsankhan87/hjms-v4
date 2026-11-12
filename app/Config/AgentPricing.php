<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AgentPricing extends BaseConfig
{
    /**
     * Supported values: discount, markup
     */
    public string $fallbackMode = 'discount';

    /**
     * Percentage for fallback mode when agent_price is not available.
     */
    public float $fallbackPercentage = 5.0;
}

