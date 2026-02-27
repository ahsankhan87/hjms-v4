<?php

namespace App\Config;

class VisaOptions
{
    const TYPES = [
        'hajj' => 'Hajj',
        'umrah' => 'Umrah',
        'tourist' => 'Tourist',
        'business' => 'Business',
        'other' => 'Other',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public static function typeKeys(): array
    {
        return array_keys(self::TYPES);
    }

    public static function statusKeys(): array
    {
        return array_keys(self::STATUSES);
    }
}
