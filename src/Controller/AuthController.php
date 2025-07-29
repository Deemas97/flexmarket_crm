<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerRendering.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Core/Service/Renderer.php';
include_once '../src/Service/AuthService.php';

use App\Core\Controller\ControllerRendering;
use App\Core\Controller\ControllerResponseInterface;
use App\Core\Service\Renderer;
use App\Service\AuthService;

class AuthController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer,
        private AuthService $auth
    )
    {
        parent::__construct($renderer);
    }

    public function authForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Вход в систему',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/auth_form.html.php', $data);
    }

    public function registerForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Регистрация',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/registration_form.html.php', $data);
    }

    public function resetPassForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Сброс пароля',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/password_reset_form.html.php', $data);
    }

    public function confirmPassForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/');

        $data = [
            'title' => 'Подтверждение сброса',
            'company_name' => 'ФлексМаркет',
            'token' => 'abc123'
        ];

        return $this->render('auth/password_confirm_form.html.php', $data);
    }

    protected function checkAuthWithRedirection(string $path): void
    {
        $this->auth->setUserTable('employees');

        if ($this->auth->isAuthenticated()) {
            $this->redirect($path);
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}