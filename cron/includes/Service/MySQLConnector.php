<?php
namespace App\Cron\Service;

include_once __DIR__ . '/../ServiceInterface.php';

use App\Cron\ServiceInterface;
use mysqli;
use RuntimeException;

class MySQLConnector implements ServiceInterface
{
    private ?mysqli $connection = null;
    private $lastError = null;

    public function __construct()
    {}

    public function __destruct()
    {}

    public function init(
        string $host,
        string $username,
        string $password,
        string $database,
        int $port = 3306,
        string $charset = 'utf8mb4'
    )
    {
        $this->connection = new mysqli($host, $username, $password, $database, $port);

        if ($this->connection->connect_error) {
            $this->lastError = $this->connection->connect_error;
            throw new RuntimeException("Connection failed: " . $this->lastError);
        }

        $this->connection->set_charset($charset);
    }

    public function query(string $sql): array|bool
    {
        $this->lastError = null;
        $result = $this->connection->query($sql);

        if ($result === false) {
            $this->lastError = $this->connection->error;
            return false;
        }

        // Для INSERT, UPDATE, DELETE и др. без возврата
        if ($result === true) {
            return true;
        }

        // Для SELECT и остальных
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();

        return $rows;
    }

    public function escape(string $value): string
    {
        return $this->connection->real_escape_string($value);
    }

    public function getLastInsertId(): int
    {
        return $this->connection->insert_id;
    }

    public function getAffectedRows(): int
    {
        return $this->connection->affected_rows;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function beginTransaction(): bool
    {
        return $this->connection->begin_transaction();
    }

    public function checkTransaction(): bool
    {
        $result = $this->connection->query("SELECT @@autocommit");

        if ($result === false) {
            $this->lastError = $this->connection->error;
            return false;
        }

        $row = $result->fetch_row();
        $result->free();
        
        return isset($row[0]) && $row[0] == 0;
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    public function close(): void
    {
        if ($this->connection) {
            $this->connection->close();
            unset($this->connection);
        }
    }
}