<?php
namespace App\Core\Container;

include_once '../src/Core/ContainerInterface.php';

use App\Core\ContainerInterface;

interface GlobalContainerInterface extends ContainerInterface
{
    public function set(string $id, $item): void;
}