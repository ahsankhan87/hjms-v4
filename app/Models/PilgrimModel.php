<?php

namespace App\Models;

class PilgrimModel extends SeasonScopedModel
{
    protected $table = 'pilgrims';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'branch_id',
        'agent_id',
        'first_name',
        'last_name',
        'father_name',
        'cnic',
        'country',
        'gender',
        'date_of_birth',
        'passport_issue_date',
        'city',
        'mobile_no',
        'mehram',
        'description',
        'pilgrim_image_name',
        'pilgrim_image_path',
        'passport_image_name',
        'passport_image_path',
        'phone',
        'email',
        'passport_no',
        'passport_expiry_date',
        'mahram_pilgrim_id',
        'medical_notes',
        'is_active',
        'created_at',
        'updated_at',
        'reference',
        'blood_group',
        'attachment_1',
        'attachment_2',
        'sale_released_at',
        'sale_released_by',
    ];

    public static function normalizePassportNo(string $value): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', trim($value)));
    }

    // Passport numbers are unique per season; the same passport in another season is a returning pilgrim.
    public function passportExists(string $passportNo, int $seasonId, ?int $excludeId = null): bool
    {
        $passportNo = self::normalizePassportNo($passportNo);
        if ($passportNo === '') {
            return false;
        }

        $builder = $this->db->table($this->table)
            ->select('id')
            ->where('season_id', $seasonId)
            ->where("UPPER(REPLACE(passport_no, ' ', '')) = " . $this->db->escape($passportNo), null, false);
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->limit(1)->get()->getRowArray() !== null;
    }
}
