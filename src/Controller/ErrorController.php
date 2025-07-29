<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Core/Service/Renderer.php';
include_once '../src/Core/Controller/ControllerRendering.php';

use App\Core\Controller\ControllerResponseInterface;
use App\Core\Service\Renderer;
use App\Core\Controller\ControllerRendering;

class ErrorController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer
    )
    {
        parent::__construct($renderer);
    }

    public function error403(): ControllerResponseInterface
    {
        $this->renderer->enableCaching(true);

        $data = [
            'title' => '403 Error'
        ];

        return $this->render('errors/error_403.html.php', $data);
    }

    public function error404(): ControllerResponseInterface
    {
        $this->renderer->enableCaching(true);

        $data = [
            'title' => '404 Error'
        ];

        return $this->render('errors/error_404.html.php', $data);
    }

    public function error405(): ControllerResponseInterface
    {
        $this->renderer->enableCaching(true);

        $data = [
            'title' => '405 Error'
        ];

        return $this->render('errors/error_405.html.php', $data);
    }

    public function error405xhr(): ControllerResponseInterface
    {
        $this->renderer->enableCaching(true);

        return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
    }

    public function error500(): ControllerResponseInterface
    {
        $this->renderer->enableCaching(true);

        $data = [
            'title' => '500 Error'
        ];

        return $this->render('errors/error_500.html.php', $data);
    }
}