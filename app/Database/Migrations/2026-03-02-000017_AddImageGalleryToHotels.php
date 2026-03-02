<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageGalleryToHotels extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('hotels')) {
            return;
        }

        if (! $this->columnExists('hotels', 'image_gallery')) {
            $this->forge->addColumn('hotels', [
                'image_gallery' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'image_url',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('hotels')) {
            return;
        }

        if ($this->columnExists('hotels', 'image_gallery')) {
            $this->forge->dropColumn('hotels', 'image_gallery');
        }
    }

    private function tableIsReadable(string $table): bool
    {
        try {
            $this->db->query('SELECT 1 FROM ' . $table . ' LIMIT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $query = $this->db->query('SHOW COLUMNS FROM ' . $table . ' LIKE ?', [$column]);

            return $query->getRowArray() !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
