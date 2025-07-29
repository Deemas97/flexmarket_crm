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

class SearchController extends ControllerRendering
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

        $query = trim($_GET['q'] ?? '');
        $results = [];

        if (!empty($query)) {
            $results = $this->performSearch($query);
        }

        $data = [
            'title' => 'Результаты поиска',
            'query' => $query,
            'results' => $results,
            'user_session' => $this->auth->getUser()
        ];

        return $this->render('pages/search.html.php', $data);
    }

    private function performSearch(string $query): array
    {
        $db = $this->dbManager->getConnection();
        $results = [];

        // Поиск товаров
        $products = $db->query("
            SELECT id, name, description, price, stock_quantity 
            FROM products 
            WHERE name LIKE '%{$query}%' OR description LIKE '%{$query}%'
            LIMIT 5
        ");

        foreach ($products as $product) {
            $results[] = [
                'type' => 'Товар',
                'title' => $product['name'],
                'description' => $this->shortenDescription($product['description']),
                'url' => "/product/{$product['id']}/edit",
                'meta' => [
                    'Цена' => number_format($product['price'], 2) . ' ₽',
                    'На складе' => $product['stock_quantity'] . ' шт.'
                ]
            ];
        }

        // Поиск заказов
        $orders = $db->query("
            SELECT o.id, o.sum, o.status, o.created_at, 
                   CONCAT(c.f, ' ', c.i) AS customer_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            WHERE o.id LIKE '%{$query}%' OR c.f LIKE '%{$query}%' OR c.i LIKE '%{$query}%'
            LIMIT 5
        ");

        foreach ($orders as $order) {
            $results[] = [
                'type' => 'Заказ',
                'title' => "Заказ #{$order['id']}",
                'description' => "Клиент: {$order['customer_name']}",
                'url' => "/order/{$order['id']}/edit",
                'meta' => [
                    'Сумма' => number_format($order['sum'], 2) . ' ₽',
                    'Статус' => $this->getOrderStatusText($order['status']),
                    'Дата' => date('d.m.Y', strtotime($order['created_at']))
                ]
            ];
        }

        // Поиск клиентов
        $customers = $db->query("
            SELECT id, f, i, email, status, created_at
            FROM customers
            WHERE f LIKE '%{$query}%' OR i LIKE '%{$query}%' OR email LIKE '%{$query}%'
            LIMIT 5
        ");

        foreach ($customers as $customer) {
            $results[] = [
                'type' => 'Клиент',
                'title' => "{$customer['f']} {$customer['i']}",
                'description' => $customer['email'],
                'url' => "/customer/{$customer['id']}/edit",
                'meta' => [
                    'Телефон' => $customer['phone'] ?? 'не указан',
                    'Статус' => $this->getCustomerStatusText($customer['status']),
                    'Дата регистрации' => date('d.m.Y', strtotime($customer['created_at']))
                ]
            ];
        }

        // Поиск поставщиков
        $vendors = $db->query("
            SELECT v.id, v.f, v.i, v.email, c.name AS company_name
            FROM vendors v
            LEFT JOIN companies c ON v.company_id = c.id
            WHERE v.f LIKE '%{$query}%' OR v.i LIKE '%{$query}%' OR v.email LIKE '%{$query}%'
            LIMIT 5
        ");

        foreach ($vendors as $vendor) {
            $results[] = [
                'type' => 'Поставщик',
                'title' => "{$vendor['f']} {$vendor['i']}",
                'description' => $vendor['company_name'] ?? 'Без компании',
                'url' => "/vendor/{$vendor['id']}/edit",
                'meta' => [
                    'Email' => $vendor['email'],
                    'Телефон' => $vendor['phone'] ?? 'не указан'
                ]
            ];
        }

        return $results;
    }

    private function getOrderStatusText(string $status): string
    {
        $statuses = [
            'pending' => 'Ожидание',
            'processing' => 'В обработке',
            'completed' => 'Готово',
            'cancelled' => 'Отменено'
        ];
        return $statuses[$status] ?? $status;
    }

    private function getCustomerStatusText(string $status): string
    {
        $statuses = [
            'premoderation' => 'На модерации',
            'active' => 'Активный',
            'banned' => 'Заблокирован'
        ];
        return $statuses[$status] ?? $status;
    }

    private function shortenDescription(string $text, int $length = 100): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '...';
    }

    private function checkAuth(): void
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