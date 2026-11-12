<?php

namespace App\Models\Frontend;

use CodeIgniter\Model;

class TicketFrontendModel extends Model
{
    protected $table = 'flights';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function searchTickets(array $filters = []): array
    {
        $builder = $this->builder()
            ->select('id, airline, flight_no, departure_airport, arrival_airport, departure_at, arrival_at, fare')
            ->orderBy('departure_at', 'ASC')
            ->orderBy('id', 'DESC');

        $fromCity = trim((string) ($filters['from_city'] ?? ''));
        if ($fromCity !== '') {
            $builder->like('departure_airport', $fromCity);
        }

        $toCity = trim((string) ($filters['to_city'] ?? ''));
        if ($toCity !== '') {
            $builder->like('arrival_airport', $toCity);
        }

        $departureDate = trim((string) ($filters['departure_date'] ?? ''));
        if ($departureDate !== '') {
            $builder->where('DATE(departure_at)', $departureDate);
        }

        $limit = (int) ($filters['limit'] ?? 100);
        if ($limit > 0) {
            $builder->limit(min($limit, 200));
        }

        $rows = $builder->get()->getResultArray();
        $ticketClass = trim((string) ($filters['travel_class'] ?? 'economy'));
        if ($ticketClass === '') {
            $ticketClass = 'economy';
        }

        foreach ($rows as &$row) {
            $row['travel_class'] = ucfirst($ticketClass);
            $row['fare'] = (float) ($row['fare'] ?? 0);
        }

        return $rows;
    }
}

