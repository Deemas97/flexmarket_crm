<?php
namespace App\Core\Service;

include_once '../src/Core/KernelServiceInterface.php';
include_once '../src/Core/Container/SharingContainer.php';
include_once '../src/Core/Service/ControllerHandler/ControllersContainer.php';
include_once '../src/Core/Service/ControllerHandler/ControllerHandlerResponseInterface.php';
include_once '../src/Core/Service/ControllerHandler/ControllerHandlerResponse.php';
include_once '../src/Service/RouterMediator.php';

use App\Core\KernelServiceInterface;
use App\Core\Container\SharingContainer;
use App\Core\Service\ControllerHandler\ControllersContainer;
use App\Core\Service\ControllerHandler\ControllerHandlerResponseInterface;
use App\Core\Service\ControllerHandler\ControllerHandlerResponse;
use App\Service\RouterMediator;
use ErrorException;
use RuntimeException;

class ControllerHandler implements KernelServiceInterface
{
    public function __construct(
        private ControllersContainer $container,
        private SharingContainer     $containerSharing
    )
    {
        $this->container->set(SharingContainer::class, $this->containerSharing);
    }

    public function run(
        string $controllerName,
        string $methodName,
        array  $routeParameters = [],
        array  $queryParameters = []
    ): ControllerHandlerResponseInterface
    {
        $response = new ControllerHandlerResponse();

        if (!class_exists($controllerName)) {
            throw new RuntimeException("Controller {$controllerName} not found");
        }
    
        if (!method_exists($controllerName, $methodName)) {
            throw new RuntimeException("Method {$methodName} not found in {$controllerName}");
        }

        $controller = $this->container->get($controllerName);
        $controllerParameters = array_merge(array_values($routeParameters), array_values($queryParameters));
        
        try {
            $controllerResponse = $controller->$methodName(...$controllerParameters);
        } catch (ErrorException $error) {
            throw $error;
        }
    
        $controllerResponseDump = $controllerResponse->getAll();

        $responseType = (isset($controllerResponseDump['is_json']) && ($controllerResponseDump['is_json'] === true)) ? 'api_json' : 'view';
        
        $response->addItem('type',                $responseType);
        $response->addItem('controller_response', $controllerResponseDump);
        $response->addItem('status',              $controllerResponse->getStatusCode());
        
        return $response;
    }
}
