<?php
namespace App\Core;

interface MessageBusInterface
{
    public function get(string $id);
    public function getAll();
    public function addItem(string $id, $value): void;
}