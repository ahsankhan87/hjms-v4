<?php

namespace App\Shared\Application\Services;

abstract class BaseService
{
    protected function transaction(callable $callback): mixed
    {
        $db = db_connect();
        $db->transStart();

        $result = $callback();

        $db->transComplete();

        if (! $db->transStatus()) {
            throw new \RuntimeException('Database transaction failed.');
        }

        return $result;
    }
}
