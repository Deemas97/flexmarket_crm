<?php
namespace App\Core\Controller;

include_once '../src/Core/Controller/ControllerResponse.php';

abstract class ControllerAbstract
{
    public function __construct() {}

    protected function initResponse(): ControllerResponse
    {
        return new ControllerResponse();
    }

    protected function initJsonResponse(array $data = [], int $statusCode = 200, array $headers = []): ControllerResponse
    {
        $response = new ControllerResponse();
        $response->setStatusCode($statusCode);
        
        foreach ($data as $key => $value) {
            $response->addItem($key, $value);
        }
        
        $response->addItem('is_json', true);
        
        if (!empty($headers)) {
            $response->addItem('headers', $headers);
        }
        
        return $response;
    }
}
