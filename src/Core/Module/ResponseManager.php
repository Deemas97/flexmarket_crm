<?php
namespace App\Core\Module;

include_once '../src/Core/ModuleInterface.php';
include_once '../src/Core/Module/ResponseManagerInterface.php';
include_once '../src/Core/MessageBusInterface.php';
include_once '../src/Core/MessageBus/ResponseInterface.php';
include_once '../src/Core/MessageBus/Response.php';

use App\Core\ModuleInterface;
use App\Core\Module\ResponseManagerInterface;
use App\Core\MessageBusInterface;
use App\Core\MessageBus\ResponseInterface;
use App\Core\MessageBus\Response;
use RuntimeException;

class ResponseManager implements ModuleInterface, ResponseManagerInterface
{
    public function process(MessageBusInterface $messageBus): ResponseInterface
    {
        $response = new Response();
    
        // Добавляем CORS заголовки если нужно
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
    
        if ($messageBus->get('type') === 'api_response') {
            $apiResponse = $messageBus->get('response');
            
            // Проверяем, является ли ответ массивом или объектом
            $responseData = is_array($apiResponse) ? $apiResponse : $apiResponse->getAll();
            $statusCode = $messageBus->get('status') ?? 200;
    
            http_response_code($statusCode);
            header('Content-Type: application/json');
            
            // Обрабатываем заголовки, если они есть
            if (is_array($apiResponse) && isset($apiResponse['headers'])) {
                foreach ($apiResponse['headers'] as $name => $value) {
                    header("$name: $value");
                }
            } elseif (is_object($apiResponse) && method_exists($apiResponse, 'get')) {
                if ($headers = $apiResponse->get('headers')) {
                    foreach ($headers as $name => $value) {
                        header("$name: $value");
                    }
                }
            }
            
            echo json_encode($responseData);
            return $response;
            
        } elseif ($messageBus->get('type') === 'html_response') {
            $htmlResponse = $messageBus->get('response');
            if (is_array($htmlResponse)) {
                echo $htmlResponse['view_html']->getContent();
            } else {
                echo $htmlResponse->getContent();
            }
            return $response;
        }
    
        return $response;
    }
}
