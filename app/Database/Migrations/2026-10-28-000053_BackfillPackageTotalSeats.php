<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Seats moved from package_hotels to packages. Backfill packages.total_seats from
 * the old hotel-level allocation so existing packages keep their sellable capacity.
 */
class BackfillPackageTotalSeats extends Migration
{
    public function up(): void
    {
        $this->db->query(
            'UPDATE packages p
             INNER JOIN (
                 SELECT package_id, MIN(total_seats) AS seats
                 FROM package_hotels
                 WHERE total_seats > 0
                 GROUP BY package_id
             ) h ON h.package_id = p.id
             SET p.total_seats = h.seats
             WHERE p.total_seats IS NULL OR p.total_seats = 0'
        );
    }

    public function down(): void
    {
        // Data backfill only; nothing to revert.
    }
}
