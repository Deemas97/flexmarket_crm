<?php
namespace App\Controller;

include_once '../src/Core/Config/DotEnv.php';
include_once '../src/Core/Controller/ControllerRendering.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Core/Service/Renderer.php';
include_once '../src/Service/AuthService.php';

use App\Core\DotEnv;
use App\Core\Controller\ControllerRendering;
use App\Core\Controller\ControllerResponseInterface;
use App\Core\Service\Renderer;
use App\Service\AuthService;

class SettingsController extends ControllerRendering
{
    private array $timezones;
    private array $currencies;

    public function __construct(
        Renderer $renderer,
        private AuthService $auth
    ) {
        parent::__construct($renderer);
        $this->checkAuth();
        $this->initTimezones();
        $this->initCurrencies();
    }

    public function index(): ControllerResponseInterface
    {
        $env = DotEnv::getData();
        
        $data = [
            'title' => 'Настройки платформы',
            'settings' => $env,
            'timezones' => $this->timezones,
            'currencies' => $this->currencies,
            'user_session' => $this->getUserSessionData()
        ];

        return $this->render('pages/admin_settings.html.php', $data);
    }

    private function initTimezones(): void
    {
        $this->timezones = \DateTimeZone::listIdentifiers();
    }

    private function initCurrencies(): void
    {
        $this->currencies = [
            'RUB' => 'Российский рубль',
            'USD' => 'Доллар США',
            'EUR' => 'Евро',
            'CNY' => 'Китайский юань'
        ];
    }

    private function getUserSessionData(): array
    {
        $userSessionData = $this->auth->getUser();
        return [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];
    }

    protected function checkAuth(): void
    {
        $this->auth->setUserTable('employees');

        if (!$this->auth->isAuthenticated() || !($this->auth->getUserRole() === 'admin')) {
            $this->redirect('/login');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}