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

class MainController extends ControllerRendering
{
    private array $statusConfig = [
        'order' => [
            'pending' => ['color' => '#f6c23e', 'text' => 'Ожидание'],
            'processing' => ['color' => '#4e73df', 'text' => 'В обработке'],
            'completed' => ['color' => '#1cc88a', 'text' => 'Готово'],
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
            'title' => 'Главная панель',
            'company_name' => 'ФлексМаркет'
        ];

        // Данные пользователя
        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        // Получаем статистику для дашборда
        $db = $this->dbManager->getConnection();
        
        // 1. Общая статистика
        $data['stats'] = [
            'products' => $this->getCount($db, 'products'),
            'categories' => $this->getCount($db, 'categories'),
            'orders' => $this->getCount($db, 'orders'),
            'customers' => $this->getCount($db, 'customers')
        ];

        // 2. Последние заказы
        $data['recent_orders'] = $db->query("
            SELECT o.id, o.sum, o.status, o.created_at, 
                   CONCAT(c.f, ' ', c.i) AS customer_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            ORDER BY o.created_at DESC
            LIMIT 5
        ") ?? [];

        // 3. Популярные товары
        $data['popular_products'] = $db->query("
            SELECT p.id, p.name, p.price, 
                   SUM(op.count) AS total_sold,
                   COUNT(DISTINCT op.order_id) AS order_count
            FROM products p
            LEFT JOIN orders_products op ON p.id = op.product_id
            LEFT JOIN orders o ON op.order_id = o.id AND o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 5
        ") ?? [];

        // 4. Статистика по статусам заказов
        $data['orders_status_stats'] = $db->query("
            SELECT status, COUNT(*) as count
            FROM orders
            GROUP BY status
        ") ?? [];

        $data['status_config'] = $this->statusConfig;

        return $this->render('pages/main.html.php', $data);
    }

    private function getCount($db, $table): int
    {
        $result = $db->query("SELECT COUNT(*) as count FROM {$table}");
        return $result[0]['count'] ?? 0;
    }

    protected function checkAuth()
    {
        $this->auth->setUserTable('employees');

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $this->checkStatus();
    }

    private function checkStatus()
    {
        $userStatus = $this->auth->getUserStatus();
        switch ($userStatus) {
            case 'premoderation':
                $this->redirect('/premoderation_info');
            case 'banned':
                $this->redirect('/ban_info');
            case null:
            case "":
                $this->redirect('/crash');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
