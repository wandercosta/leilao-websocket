<?php

declare(strict_types=1);

namespace App;

use PDO;

final class Database
{
    public static function conectar(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            Config::get('DB_HOST'),
            Config::get('DB_PORT', '3306'),
            Config::get('DB_DATABASE'),
        );

        return new PDO($dsn, Config::get('DB_USERNAME'), Config::get('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
