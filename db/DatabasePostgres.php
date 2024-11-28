<?php

namespace Utils;

use PDO;
use PDOException;
class DatabasePostgres
{
    private string $dsn;
    private ?PDO $connection = null;

    public function __construct(string $host, int $port, string $dbname, string $user, string $password) {
        $this->dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    }

    public function getConnection(): PDO {
        if ($this->connection === null) {
            try {
                $this->connection = new PDO($this->dsn);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }

        return $this->connection;
    }
}