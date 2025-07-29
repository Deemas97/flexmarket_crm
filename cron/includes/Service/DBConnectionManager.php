<?php
namespace App\Cron\Service;

include_once __DIR__ . '/../ServiceInterface.php';
include_once __DIR__ . '/MySQLConnector.php';

use App\Cron\ServiceInterface;
use RuntimeException;

class DBConnectionManager implements ServiceInterface
{
    private $connections = [];
    private $defaultConnection = null;

    public function __construct()
    {
        $this->initDefaultConnection();
    }

    public function __destruct()
    {
        $this->closeAll();
    }

    public function initDefaultConnection(): MySQLConnector
    {
        if (isset($this->connections['default'])) {
            throw new RuntimeException("Connection with name 'default' already exists");
        }

        $connectorDefault = new MySQLConnector();
        $connectorDefault->init('127.0.0.1', 'root', 'Saratov_1997', 'marketplace_platform');

        $this->connections['default'] = $connectorDefault;
        $this->defaultConnection = 'default';

        return $connectorDefault;
    }

    public function getConnection($name = null): MySQLConnector
    {
        $name = $name ?? $this->defaultConnection;

        if (!isset($this->connections[$name])) {
            throw new RuntimeException("Connection with name '{$name}' not found");
        }

        return $this->connections[$name];
    }

    public function setDefaultConnection($name): void
    {
        if (!isset($this->connections[$name])) {
            throw new RuntimeException("Connection with name '{$name}' not found");
        }

        $this->defaultConnection = $name;
    }

    public function closeAll(): void
    {
        foreach ($this->connections as $connection) {
            $connection->close();
        }
        $this->connections = [];
        $this->defaultConnection = null;
    }

    public function closeConnection($name): void
    {
        if (!isset($this->connections[$name])) {
            throw new RuntimeException("Connection with name '{$name}' not found");
        }

        $this->connections[$name]->close();
        unset($this->connections[$name]);

        if ($this->defaultConnection === $name) {
            $this->defaultConnection = !empty($this->connections) ? array_key_first($this->connections) : null;
        }
    }

    public function hasConnection($name): bool
    {
        return isset($this->connections[$name]);
    }

    public function listConnections(): array
    {
        return array_keys($this->connections);
    }
}