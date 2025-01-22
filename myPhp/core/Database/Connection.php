<?php

namespace Core\Database;

use PDO;
use PDOStatement;
use PDOException;

class Connection
{
    private static ?Connection $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $this->pdo = new PDO(
            sprintf(
                "%s:host=%s;dbname=%s;charset=utf8mb4",
                getenv('DB_CONNECTION', 'mysql'),
                getenv('DB_HOST', 'localhost'),
                getenv('DB_DATABASE', 'database')
            ),
            getenv('DB_USERNAME', 'root'),
            getenv('DB_PASSWORD', ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
} 