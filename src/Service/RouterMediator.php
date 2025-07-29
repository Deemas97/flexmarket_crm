<?php
namespace App\Service;

include_once '../src/Core/ServiceInterface.php';
include_once '../src/Core/Service/Router.php';

use App\Core\ServiceInterface;
use App\Core\Service\Router;
use App\Core\Service\Router\RouteInterface;
use App\Core\Service\Router\RoutesContainer;

class RouterMediator implements ServiceInterface
{
    public function __construct(
        private Router $router
    )
    {}
    
    public function getCurrentRoute(): ?RouteInterface
    {
        return $this->router->getCurrentRoute();
    }

    public function register(string $routeName, string $controllerName, string $methodName): self
    {
        $this->router->register($routeName, $controllerName, $methodName);

        return $this;
    }

    public function resolve(string $routeId): ?RouteInterface
    {
        return $this->router->resolve($routeId);
    }

    public function getRoutes(): RoutesContainer
    {
        return $this->router->getRoutes();
    }
}