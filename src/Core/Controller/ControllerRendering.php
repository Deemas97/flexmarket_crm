<?php
namespace App\Core\Controller;

include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponse.php';
include_once '../src/Core/Service/Renderer.php';
include_once '../src/Core/View/View.php';

use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponse;
use App\Core\Service\Renderer;
use App\Core\View;

abstract class ControllerRendering extends ControllerAbstract
{
    public function __construct(
        protected Renderer $renderer
    )
    {}

    protected function render(string $templateName, array $data = []): ControllerResponse
    {
        $response = $this->initResponse();

        $content = $this->renderer->render($templateName, $data);
        $response->addItem('view_html', new View($content));

        return $response;
    }
}
