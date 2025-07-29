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

class CategoriesController extends ControllerRendering
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
            'title' => 'Категории',
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
        $categories = $db->query("SELECT * FROM categories ORDER BY name");
        
        if ($categories === false) {
            $data['categories'] = [];
            $data['categoriesMap'] = [];
        } else {
            $data['categories'] = $categories;
            $data['categoriesMap'] = array_column($categories, null, 'id');
        }

        return $this->render('pages/categories.html.php', $data);
    }

    public function category(int $id): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Редактирование категории',
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
        
        // Получаем текущую категорию
        $category = $db->query("SELECT * FROM categories WHERE id = {$id} LIMIT 1");
        if (empty($category)) {
            $this->redirect('/error_404');
        }
        $data['category'] = $category[0];

        // Получаем все категории для выпадающего списка
        $categories = $db->query("SELECT id, name FROM categories WHERE id != {$id} ORDER BY name");
        $data['categories'] = $categories === false ? [] : $categories;

        return $this->render('pages/category_edit.html.php', $data);
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