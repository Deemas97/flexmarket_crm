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

class OrdersPositionsController extends ControllerRendering
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
            'title' => 'Позиции товаров в заказах',
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
        
        // Base query for positions
        $query = "
            SELECT 
                op.id, op.order_id, op.product_id, op.price, op.count, op.status,
                p.name as product_name, p.image_main as product_image,
                c.f as customer_f, c.i as customer_i, c.email as customer_email,
                o.created_at as order_created_at,
                comp.name as company_name
            FROM orders_products op
            LEFT JOIN products p ON op.product_id = p.id
            LEFT JOIN orders o ON op.order_id = o.id
            LEFT JOIN customers c ON o.customer_id = c.id
            LEFT JOIN companies comp ON p.company_id = comp.id
        ";

        // Add status filter if provided
        $status = $_GET['status'] ?? '';
        if ($status && in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $query .= " WHERE op.status = '{$db->escape($status)}'";
        }

        $query .= " ORDER BY o.created_at DESC, op.id ASC";

        $positions = $db->query($query);
        $data['positions'] = $positions === false ? [] : $positions;

        // Statuses for filtering and display
        $data['statuses'] = [
            'pending' => 'Ожидает',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменен'
        ];

        return $this->render('pages/orders_positions.html.php', $data);
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