<?php
namespace App\Core\MessageBus;

include_once '../src/Core/MessageBus/ResponseInterface.php';

use App\Core\MessageBus\ResponseInterface;

class Response implements ResponseInterface
{
    protected array $data = [];

    public function get(string $id)
    {
        return $this->data[$id] ?? null;
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function addItem(string $id, $value): void
    {
        $this->data[$id] = $value;
    }

    public function reset():void
    {
        $this->data = [];
    }
}
