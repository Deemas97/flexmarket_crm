<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerRendering.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Core/Service/Renderer.php';

use App\Core\Controller\ControllerRendering;
use App\Core\Controller\ControllerResponseInterface;
use App\Core\Service\Renderer;

class StatusInfoController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer
    )
    {
        parent::__construct($renderer);
    }

    public function premoderationInfo(): ControllerResponseInterface
    {
        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Заблокировано'
        ];

        return $this->render('lock/premoderation_info.html.php', $data);
    }

    public function banInfo(): ControllerResponseInterface
    {
        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Заблокировано'
        ];

        return $this->render('lock/ban_info.html.php', $data);
    }
}