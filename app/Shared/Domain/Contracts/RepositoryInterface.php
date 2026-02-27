<?php

namespace App\Shared\Domain\Contracts;

interface RepositoryInterface
{
    public function findById(int $id): ?array;

    public function create(array $data): int;

    public function updateById(int $id, array $data): bool;

    public function deleteById(int $id): bool;
}
