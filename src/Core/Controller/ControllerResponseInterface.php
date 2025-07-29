<?php
namespace App\Core\Controller;

interface ControllerResponseInterface
{
    public function get(string $id);
    public function getAll(): array;
    public function addItem(string $id, $value): void;
    public function reset(): void;

    public function setStatusCode(int $code): void;
    public function getStatusCode(): int;
}