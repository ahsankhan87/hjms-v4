<?php

namespace App\Models\Frontend;

use CodeIgniter\Model;

class TransportFrontendModel extends Model
{
    protected $table = 'transport_legs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function listRoutes(array $filters = []): array
    {
        $builder = $this->db->table('transport_legs tl')
            ->select('tl.id, tl.transport_id, tl.from_code, tl.to_code, tl.notes, t.transport_name, t.provider_name, t.vehicle_type, t.seat_capacity, AVG(pt.cost_amount) AS avg_price')
            ->join('transports t', 't.id = tl.transport_id', 'left')
            ->join('package_transports pt', 'pt.transport_id = tl.transport_id', 'left')
            ->groupBy('tl.id')
            ->orderBy('tl.id', 'DESC');

        $fromCity = trim((string) ($filters['from_city'] ?? ''));
        if ($fromCity !== '') {
            $builder->like('tl.from_code', $fromCity);
        }

        $toCity = trim((string) ($filters['to_city'] ?? ''));
        if ($toCity !== '') {
            $builder->like('tl.to_code', $toCity);
        }

        $limit = (int) ($filters['limit'] ?? 100);
        if ($limit > 0) {
            $builder->limit(min($limit, 200));
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['avg_price'] = round((float) ($row['avg_price'] ?? 0), 2);
            $row['seat_capacity'] = (int) ($row['seat_capacity'] ?? 0);
        }

        return $rows;
    }
}

