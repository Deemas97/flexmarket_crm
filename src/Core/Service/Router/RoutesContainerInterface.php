<?php
namespace App\Core\Service\Router;

include_once '../src/Core/ContainerInterface.php';
include_once '../src/Core/Service/Router/RouteInterface.php';

use App\Core\ContainerInterface;

interface RoutesContainerInterface extends ContainerInterface
{
    public function set(string $id, RouteInterface $route): void;
}
