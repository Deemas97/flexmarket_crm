<?php
namespace App\Core\Service\ControllerHandler;

include_once '../src/Core/Service/ControllerHandler/ControllerHandlerResponseInterface.php';

use App\Core\Service\ControllerHandler\ControllerHandlerResponseInterface;

class ControllerHandlerResponse implements ControllerHandlerResponseInterface
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