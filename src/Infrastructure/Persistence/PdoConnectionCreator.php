<?php

namespace SoftplanTasksApi\Infrastructure\Persistence;

use PDO;

class PdoConnectionCreator
{
    public static function createConnection(
        string $host,
        string $dbname,
        string $username = 'root',
        string $password = ''
    ): PDO {
        $connection = new PDO(
            "mysql:host=$host;dbname=$dbname",
            $username,
            $password,
        );

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $connection;
    }
}
