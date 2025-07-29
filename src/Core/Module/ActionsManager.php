<?php
namespace App\Core\Module;

include_once '../src/Core/ModuleInterface.php';
include_once '../src/Core/Module/ActionsManagerInterface.php';
include_once '../src/Core/MessageBusInterface.php';
include_once '../src/Core/MessageBus/ResponseInterface.php';
include_once '../src/Core/MessageBus/Response.php';
include_once '../src/Core/Service/ControllerHandler.php';

use App\Core\MessageBusInterface;
use App\Core\MessageBus\ResponseInterface;
use App\Core\MessageBus\Response;
use App\Core\ModuleInterface;
use App\Core\Service\ControllerHandler;

// CONTINUE (INTERCEPT MESSAGE_BUS)
class ActionsManager implements ModuleInterface, ActionsManagerInterface
{
    public function __construct(
        private readonly ControllerHandler $controllerHandler
    )
    {}

    public function process(MessageBusInterface $messageBus): ResponseInterface
    {
        $response = new Response();

        if (!$controllerName = $messageBus->get('controller')) {
            return $response;
        }

        if (!$methodName = $messageBus->get('method')) {
            return $response;
        }

        $routeParameters = $messageBus->get('route_parameters');
        $queryParameters = $messageBus->get('query_parameters');

        $controllerHandlerResponse = $this->controllerHandler->run(
            $controllerName,
            $methodName,
            $routeParameters,
            $queryParameters
        );

        $controllerHandlerResponseDump = $controllerHandlerResponse->getAll();

        $responseType = ($controllerHandlerResponseDump['type'] === 'api_json') ? 'api_response' : 'html_response';

        $response->addItem('type',     $responseType);
        $response->addItem('response', $controllerHandlerResponseDump['controller_response']);
        $response->addItem('status',   $controllerHandlerResponseDump['status']);

        return $response;
    }
}