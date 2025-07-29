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

class SearchActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        $this->checkAuth();
    }

    public function apiSearchSuggest(): ControllerResponseInterface
    {
        $query = trim($_GET['q'] ?? '');
        $suggestions = [];

        if (strlen($query) >= 2) {
            $db = $this->dbManager->getConnection();
            
            // Товары
            $products = $db->query("
                SELECT id, name AS label, 'Товар' AS category
                FROM products 
                WHERE name LIKE '%{$query}%'
                LIMIT 3
            ");
            
            foreach ($products as $product) {
                $product['url'] = "/product/{$product['id']}/edit";
                $suggestions[] = $product;
            }
            
            // Заказы
            $orders = $db->query("
                SELECT id, CONCAT('Заказ #', id) AS label, 'Заказ' AS category
                FROM orders
                WHERE id LIKE '%{$query}%'
                LIMIT 2
            ");
            
            foreach ($orders as $order) {
                $order['url'] = "/order/{$order['id']}/edit";
                $suggestions[] = $order;
            }
            
            // Клиенты
            $customers = $db->query("
                SELECT id, CONCAT(f, ' ', i) AS label, 'Клиент' AS category
                FROM customers
                WHERE f LIKE '%{$query}%' OR i LIKE '%{$query}%'
                LIMIT 3
            ");
            
            foreach ($customers as $customer) {
                $customer['url'] = "/customer/{$customer['id']}/edit";
                $suggestions[] = $customer;
            }
            
            // Продавцы
            $vendors = $db->query("
                SELECT id, CONCAT(f, ' ', i) AS label, 'Поставщик' AS category
                FROM vendors
                WHERE f LIKE '%{$query}%' OR i LIKE '%{$query}%'
                LIMIT 2
            ");
            
            foreach ($vendors as $vendor) {
                $vendor['url'] = "/vendor/{$vendor['id']}/edit";
                $suggestions[] = $vendor;
            }
        }

        return $this->initJsonResponse(['suggestions' => $suggestions]);
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