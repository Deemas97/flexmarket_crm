<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Service/AuthService.php';
include_once '../src/Service/DBConnectionManager.php';

use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponseInterface;
use App\Service\AuthService;
use App\Service\DBConnectionManager;

class StatisticsActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        $this->checkAuth();
    }

    public function apiGetSalesStats(): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        $salesStats = $db->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS month,
                COUNT(*) AS orders_count,
                SUM(sum) AS total_amount,
                AVG(sum) AS avg_order_value
            FROM orders
            WHERE status != 'cancelled'
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ") ?? [];

        return $this->initJsonResponse(['salesStats' => $salesStats]);
    }

    public function apiGetProductsStats(): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        // Статистика по товарам
        $productsStats = $db->query("
            SELECT 
                p.id,
                p.name,
                p.company_id,
                cm.name AS company_name,
                COUNT(op.id) AS orders_count,
                SUM(op.count) AS total_sold,
                SUM(op.count * op.price) AS total_revenue
            FROM products p
            LEFT JOIN companies cm ON p.company_id = cm.id
            LEFT JOIN orders_products op ON p.id = op.product_id
            LEFT JOIN orders o ON op.order_id = o.id AND o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 20
        ") ?? [];

        return $this->initJsonResponse(['productsStats' => $productsStats]);
    }

    public function apiGetCustomersStats(): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        // Статистика по клиентам
        $customersStats = $db->query("
            SELECT 
                c.id,
                CONCAT(c.f, ' ', c.i) AS name,
                c.role_id,
                COUNT(o.id) AS orders_count,
                SUM(o.sum) AS total_spent
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id AND o.status != 'cancelled'
            GROUP BY c.id
            ORDER BY total_spent DESC
            LIMIT 20
        ") ?? [];

        return $this->initJsonResponse(['customersStats' => $customersStats]);
    }

    public function apiGetInventoryStats(): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        // Статистика по складу
        $inventoryStats = $db->query("
            SELECT 
                p.id,
                p.name,
                p.stock_quantity,
                SUM(op.count) AS total_sold
            FROM products p
            LEFT JOIN orders_products op ON p.id = op.product_id
            LEFT JOIN orders o ON op.order_id = o.id AND o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY p.stock_quantity ASC
            LIMIT 20
        ") ?? [];

        return $this->initJsonResponse(['inventoryStats' => $inventoryStats]);
    }

    private function checkAuth(): void
    {
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