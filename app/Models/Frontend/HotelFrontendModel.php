<?php

namespace App\Models\Frontend;

use CodeIgniter\Model;

class HotelFrontendModel extends Model
{
    protected $table = 'hotels';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function searchHotels(array $filters = []): array
    {
        $builder = $this->db->table('hotels h')
            ->select('h.id, h.name, h.city, h.distance_m, h.star_rating, h.address, h.image_url, h.map_url, MIN(hr.room_cost) AS price_per_night, MAX(hr.beds) AS max_beds')
            ->join('hotel_rooms hr', 'hr.hotel_id = h.id', 'left')
            ->groupBy('h.id')
            ->orderBy('h.star_rating', 'DESC')
            ->orderBy('h.id', 'DESC');

        $city = trim((string) ($filters['city'] ?? ''));
        if ($city !== '') {
            $builder->where('LOWER(TRIM(h.city))', strtolower($city));
        }

        $guests = (int) ($filters['guests'] ?? 0);
        if ($guests > 0) {
            $builder->having('MAX(hr.beds) >=', $guests);
        }

        $limit = (int) ($filters['limit'] ?? 100);
        if ($limit > 0) {
            $builder->limit(min($limit, 200));
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['price_per_night'] = (float) ($row['price_per_night'] ?? 0);
            $row['distance_m'] = (int) ($row['distance_m'] ?? 0);
            $row['star_rating'] = (int) ($row['star_rating'] ?? 0);
            $row['max_beds'] = (int) ($row['max_beds'] ?? 0);
            $row['amenities'] = $this->resolveAmenities($row);
        }

        return $rows;
    }

    private function resolveAmenities(array $row): array
    {
        $items = ['WiFi', 'Air Conditioning'];
        if ((int) ($row['star_rating'] ?? 0) >= 4) {
            $items[] = 'Premium Service';
        }
        if ((int) ($row['distance_m'] ?? 0) > 0 && (int) ($row['distance_m'] ?? 0) <= 1000) {
            $items[] = 'Near Haram';
        }
        if (trim((string) ($row['map_url'] ?? '')) !== '') {
            $items[] = 'Map Access';
        }

        return $items;
    }
}

