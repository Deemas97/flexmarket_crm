<?php
namespace App\Core\Service;

include_once '../src/Core/KernelServiceInterface.php';
include_once '../src/Core/ServiceProviderInterface.php';
include_once '../src/Core/Service/Router/Route.php';
include_once '../src/Core/Service/Router/RouteInterface.php';
include_once '../src/Core/Service/Router/RoutesContainer.php';
include_once '../src/Controller/ErrorController.php';
include_once '../src/Core/ServiceInterface.php';
include_once '../src/Service/RouterMediator.php';

use App\Controller\ErrorController;
use App\Core\KernelServiceInterface;
use App\Core\Service\Router\Route;
use App\Core\Service\Router\RouteInterface;
use App\Core\Service\Router\RoutesContainer;
use App\Core\ServiceInterface;
use App\Core\ServiceProviderInterface;
use App\Service\RouterMediator;

class Router implements KernelServiceInterface, ServiceProviderInterface
{
    private ?RouteInterface $currentRoute = null;
    private array $staticRoutes = [];
    private array $dynamicRoutes = [];

    public function __construct(
        private readonly RoutesContainer $routes
    ) {
        $this->categorizeRoutes();
    }

    public function register(
        string $routeName,
        string $controllerName,
        string $methodName,
        string $httpMethod = 'GET'
    ): self
    {
        $route = new Route($controllerName, $methodName, $httpMethod);
        $this->routes->set($routeName, $route);
        
        if (strpos($routeName, '{') !== false) {
            $this->dynamicRoutes[$routeName] = $route;
        } else {
            $this->staticRoutes[$routeName] = $route;
        }

        return $this;
    }

    public function resolve(
        string $requestUri,
        string $httpMethod,
        bool $isXhr
    ): ?RouteInterface
    {
        $uri = trim(strtok($requestUri, '?'), '/');
        $normalizedUri = '/' . $uri;

        if (isset($this->staticRoutes[$normalizedUri])) {
            $this->setCurrentRoute($this->staticRoutes[$normalizedUri]);
            return $this->checkHttpMethod($httpMethod, $this->staticRoutes[$normalizedUri], $isXhr);
        }

        foreach ($this->dynamicRoutes as $pattern => $route) {
            $normalizedPattern = trim($pattern, '/');
            if ($this->matchDynamicRoute($normalizedPattern, $uri, $matches)) {
                $filteredMatches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $route->setParameters($filteredMatches);
                $this->setCurrentRoute($route);
                return $this->checkHttpMethod($httpMethod, $route, $isXhr);
            }
        }

        $route = new Route(ErrorController::class, 'error404', 'GET');
        $this->setCurrentRoute($route);

        return $route;
    }

    public function setCurrentRoute(RouteInterface $route): void
    {
        $this->currentRoute = $route;
    }
    
    public function getCurrentRoute(): RouteInterface
    {
        return $this->currentRoute;
    }

    public function getRoutes(): RoutesContainer
    {
        return $this->routes;
    }

    public function getServiceName(): string
    {
        return RouterMediator::class;
    }

    public function getService(): ServiceInterface
    {
        return new RouterMediator($this);
    }

    private function categorizeRoutes(): void
    {
        foreach ($this->routes->getAll() as $pattern => $route) {
            if (strpos($pattern, '{') !== false) {
                $this->dynamicRoutes[$pattern] = $route;
            } else {
                $this->staticRoutes[$pattern] = $route;
            }
        }
    }

    private function matchDynamicRoute(string $pattern, string $uri, &$matches): bool
    {
        $regex = $this->convertPatternToRegex($pattern);
        return (preg_match($regex, $uri, $matches) === 1);
    }

    private function convertPatternToRegex(string $pattern): string
    {
        $pattern = preg_quote($pattern, '@');
        $pattern = str_replace(['\{', '\}'], ['{', '}'], $pattern);
        
        $regex = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^\/]+)', $pattern);
        
        return ('@^' . $regex . '$@D');
    }

    private function checkHttpMethod(
        string $httpMethod,
        RouteInterface $route,
        bool $isXhr
    ): RouteInterface
    {
        if ($httpMethod === $route->getHttpMethod()) {
            return $route;
        } else {
            $controllerMethodName = ($isXhr === true) ? 'error405xhr' : 'error405';
            return new Route(ErrorController::class, $controllerMethodName, 'GET');
        }
    }
}
