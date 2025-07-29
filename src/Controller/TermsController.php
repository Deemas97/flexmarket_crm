<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerRendering.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Core/Service/Renderer.php';

use App\Core\Controller\ControllerRendering;
use App\Core\Controller\ControllerResponseInterface;
use App\Core\Service\Renderer;

class TermsController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer
    )
    {
        parent::__construct($renderer);
    }

    public function termsFolder(): ControllerResponseInterface
    {
        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Условия пользования',
            'company_name' => 'ФлексМаркет',
            'support_email' => 'info@flexmarket.ru'
        ];

        return $this->render('pages/terms.html.php', $data);
    }
}