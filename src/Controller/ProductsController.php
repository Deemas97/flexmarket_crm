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

class ProductsController extends ControllerRendering
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
            'title' => 'Товары',
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
        
        // Получаем товары с информацией о категориях
        $products = $db->query("
            SELECT p.*, cm.name as company_name, GROUP_CONCAT(c.name SEPARATOR ', ') as categories_names
            FROM products p
            LEFT JOIN companies cm ON p.company_id = cm.id
            LEFT JOIN products_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            GROUP BY p.id
            ORDER BY p.name
        ");
        
        $data['products'] = $products === false ? [] : $products;

        return $this->render('pages/products.html.php', $data);
    }

    public function createForm(): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Создание товара',
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
        
        // Получаем все категории для выбора
        $categories = $db->query("SELECT id, name FROM categories ORDER BY name");
        $data['categories'] = $categories === false ? [] : $categories;
        $data['selectedCategories'] = []; // Пустой массив для нового товара

        return $this->render('pages/product_create.html.php', $data);
    }

    public function product(int $id): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Редактирование товара',
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
        
        // Получаем текущий товар
        $product = $db->query("SELECT * FROM products WHERE id = {$id} LIMIT 1");
        if (empty($product)) {
            $this->redirect('/not_found');
        }
        $data['product'] = $product[0];

        // Получаем все категории для выбора
        $categories = $db->query("SELECT id, name FROM categories ORDER BY name");
        $data['categories'] = $categories === false ? [] : $categories;

        // Получаем выбранные категории для товара
        $selectedCategories = $db->query("SELECT category_id FROM products_categories WHERE product_id = {$id}");
        $data['selectedCategories'] = $selectedCategories === false ? [] : array_column($selectedCategories, 'category_id');

        return $this->render('pages/product_edit.html.php', $data);
    }

    protected function checkAuth()
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