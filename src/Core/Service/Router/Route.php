<?php
namespace App\Core\Service\Router;

include_once '../src/Core/Service/Router/RouteInterface.php';

class Route implements RouteInterface
{
    private array $parameters = [];

    public function __construct(
        private readonly string $controllerName,
        private readonly string $methodName,
        private readonly string $httpMethod
    )
    {}

    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
    
    public function getParameters(): array
    {
        return $this->parameters;
    }
    
    public function getParameter(string $name, $default = null)
    {
        return ($this->parameters[$name] ?? $default);
    }
}
