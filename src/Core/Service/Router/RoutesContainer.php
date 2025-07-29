<?php
namespace App\Core\Service\Router;

include_once '../src/Core/Service/Router/RouteInterface.php';
include_once '../src/Core/Service/Router/RoutesContainerInterface.php';

class RoutesContainer implements RoutesContainerInterface
{
    private array $routes = [];

    public function get(string $id): ?RouteInterface
    {
        return $this->has($id) ? $this->routes[$id] : null;
    }

    public function getAll(): array
    {
        return $this->routes;
    }

    public function has(string $id): bool
    {
        return isset($this->routes[$id]);
    }

    public function set(string $id, RouteInterface $route): void
    {
        $this->routes[$id] = $route;
    }
}
