<?php

namespace App\Shared\Infrastructure\Repositories;

use App\Shared\Domain\Contracts\RepositoryInterface;
use CodeIgniter\Database\ConnectionInterface;

abstract class BaseRepository implements RepositoryInterface
{
    protected ConnectionInterface $db;

    protected string $table;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function findById(int $id): ?array
    {
        return $this->db->table($this->table)->where('id', $id)->get()->getRowArray();
    }

    public function create(array $data): int
    {
        $this->db->table($this->table)->insert($data);

        return (int) $this->db->insertID();
    }

    public function updateById(int $id, array $data): bool
    {
        return (bool) $this->db->table($this->table)->where('id', $id)->update($data);
    }

    public function deleteById(int $id): bool
    {
        return (bool) $this->db->table($this->table)->where('id', $id)->delete();
    }
}
