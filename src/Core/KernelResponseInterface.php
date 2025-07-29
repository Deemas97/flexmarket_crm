<?php
namespace App\Core;

interface KernelResponseInterface
{
    public function get(string $id);
    public function getAll(): array;
    public function addItem(string $id, $value): void;
    public function reset(): void;
}