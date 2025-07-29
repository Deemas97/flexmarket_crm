<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerRendering.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Core/Service/Renderer.php';
include_once '../src/Service/AuthService.php';
include_once '../src/Service/DBConnectionManager.php';

use App\Core\Controller\ControllerRendering;
use App\Core\Controller\ControllerResponseInterface;
use App\Core\Service\Renderer;
use App\Service\AuthService;
use App\Service\DBConnectionManager;

class ReportsController extends ControllerRendering
{
    private array $statusConfig = [
        'order' => [
            'pending' => ['color' => '#4e73df', 'text' => 'Ожидание'],
            'processing' => ['color' => '#36b9cc', 'text' => 'В обработке'],
            'completed' => ['color' => '#f6c23e', 'text' => 'Готово'],
            'cancelled' => ['color' => '#e74a3b', 'text' => 'Отменено']
        ],
        'user' => [
            'premoderation' => ['color' => '#6c757d', 'text' => 'На модерации'],
            'active' => ['color' => '#1cc88a', 'text' => 'Активный'],
            'banned' => ['color' => '#e74a3b', 'text' => 'Заблокирован']
        ],
        'role' => [
            'simple' => ['color' => '#858796', 'text' => 'Обычный'],
            'subscriber' => ['color' => '#4e73df', 'text' => 'Подписчик']
        ]
    ];

    public function __construct(
        Renderer $renderer,
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        parent::__construct($renderer);
    }

    public function index(): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Отчеты',
            'company_name' => 'ФлексМаркет',
            'status_config' => $this->statusConfig,
            'user_session' => $this->getUserSessionData()
        ];

        return $this->render('pages/reports.html.php', $data);
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

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}