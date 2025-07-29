<?php
namespace App\Core\Service\Router;

interface RouteInterface
{
    public function getControllerName(): string;
    public function getMethodName(): string;
    public function getHttpMethod(): string;
    
    public function setParameters(array $parameters): void;
    public function getParameters(): array;
    public function getParameter(string $name, $default = null);
}
