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

class DashboardActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        $this->checkAuth();
    }

    public function apiGetSalesData(): ControllerResponseInterface
    {    
        $db = $this->dbManager->getConnection();
        
        $salesData = $db->query("
            SELECT 
                DATE(created_at) AS date,
                COUNT(*) AS order_count,
                SUM(sum) AS total_amount
            FROM orders
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            AND status != 'cancelled'
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ") ?? [];
    
        // Преобразуем даты в формат 'd.m.Y' для отображения
        $formattedData = array_map(function($item) {
            return [
                'date' => date('d.m.Y', strtotime($item['date'])),
                'order_count' => (int)$item['order_count'],
                'total_amount' => (float)$item['total_amount']
            ];
        }, $salesData);
    
        return $this->initJsonResponse(['salesData' => $formattedData]);
    }

    public function apiGetInventoryStatus(): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();
        
        // Получаем статус инвентаризации
        $inventoryData = $db->query("
            SELECT 
                SUM(stock_quantity) AS total_in_stock,
                SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) AS out_of_stock,
                SUM(CASE WHEN stock_quantity <= 5 AND stock_quantity > 0 THEN 1 ELSE 0 END) AS low_stock
            FROM products
        ") ?? [];

        return $this->initJsonResponse(['inventoryData' => $inventoryData[0] ?? []]);
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