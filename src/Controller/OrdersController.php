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

class OrdersController extends ControllerRendering
{
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
            'title' => 'Заказы',
            'company_name' => 'ФлексМаркет'
        ];

        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $db = $this->dbManager->getConnection();
        
        // Получаем список заказов с информацией о клиенте
        $orders = $db->query("
            SELECT o.*, c.i as customer_i, c.f as customer_f, c.email as customer_email 
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            ORDER BY o.created_at DESC
        ");
        
        $data['orders'] = $orders === false ? [] : $orders;

        // Получаем статусы для фильтрации
        $data['statuses'] = [
            'pending' => 'Ожидает',
            'processing' => 'В обработке',
            'completed' => 'Доставлен',
            'cancelled' => 'Отменен'
        ];

        return $this->render('pages/orders.html.php', $data);
    }

    public function order(int $id): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Редактирование заказа',
            'company_name' => 'ФлексМаркет'
        ];

        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $db = $this->dbManager->getConnection();
        
        $order = $db->query("
            SELECT o.*, c.i as customer_i, c.f as customer_f, c.email as customer_email 
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.id = {$id}
            LIMIT 1
        ");
        
        if (empty($order)) {
            $this->redirect('/error_404');
        }
        $data['order'] = $order[0];

        // Получаем товары в заказе
        $products = $db->query("
            SELECT op.*, p.name as product_name, p.price as product_price, p.image_main as product_image
            FROM orders_products op
            LEFT JOIN products p ON op.product_id = p.id
            WHERE op.order_id = {$id}
        ");
        $data['products'] = $products === false ? [] : $products;

        // Получаем доступные товары для добавления
        $availableProducts = $db->query("
            SELECT p.id, p.name, p.price, p.stock_quantity
            FROM products p
            WHERE p.stock_quantity > 0
            ORDER BY p.name
        ");
        $data['available_products'] = $availableProducts === false ? [] : $availableProducts;

        // Статусы заказа
        $data['statuses'] = [
            'pending' => 'Ожидает',
            'processing' => 'В обработке',
            'completed' => 'Доставлен',
            'cancelled' => 'Отменен'
        ];

        // Статусы позиций заказа
        $data['position_statuses'] = [
            'pending' => 'Ожидает',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменен'
        ];

        return $this->render('pages/order_edit.html.php', $data);
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
