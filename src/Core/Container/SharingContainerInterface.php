<?php
namespace App\Core\Container;

include_once '../src/Core/ContainerInterface.php';
include_once '../src/Core/Container/GlobalContainerInterface.php';

use App\Core\ContainerInterface;

interface SharingContainerInterface extends ContainerInterface
{
    public function init(GlobalContainerInterface $containerGlobal): void;
    public function set(string $id, $item): void;
}