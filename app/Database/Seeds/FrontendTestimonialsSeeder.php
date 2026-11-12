<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FrontendTestimonialsSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('frontend_testimonials')) {
            return;
        }

        $table = $this->db->table('frontend_testimonials');
        $count = (int) $table->countAllResults();
        if ($count > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $table->insertBatch([
            [
                'name' => 'Muhammad Aamir',
                'role' => 'Umrah Traveler',
                'message' => 'Everything was smooth from visa to hotel check-in. The support team guided us at every step.',
                'rating' => 5,
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => $now,
            ],
            [
                'name' => 'Sana Tariq',
                'role' => 'Family Group Lead',
                'message' => 'Best value package and very close hotel to Haram. Our family had a peaceful and organized journey.',
                'rating' => 5,
                'is_active' => 1,
                'sort_order' => 2,
                'created_at' => $now,
            ],
            [
                'name' => 'Al-Huda Travels',
                'role' => 'B2B Agent',
                'message' => 'Reliable operations and transparent rates. A strong partner for consistent agency bookings.',
                'rating' => 5,
                'is_active' => 1,
                'sort_order' => 3,
                'created_at' => $now,
            ],
            [
                'name' => 'Faizan Ahmed',
                'role' => 'Hajj Pilgrim',
                'message' => 'From ticketing to transport, every service was on time. Highly recommended for first-time pilgrims.',
                'rating' => 4,
                'is_active' => 1,
                'sort_order' => 4,
                'created_at' => $now,
            ],
        ]);
    }
}

