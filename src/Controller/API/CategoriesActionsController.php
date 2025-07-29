<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Service/AuthService.php';
include_once '../src/Service/DBConnectionManager.php';
include_once '../src/Service/FileUploader.php';

use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponseInterface;
use App\Service\AuthService;
use App\Service\DBConnectionManager;
use App\Service\FileUploader;

class CategoriesActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager,
        private FileUploader $fileUploader
    ) {
        $this->checkAdminAccess();
    }

    public function apiCreate(): ControllerResponseInterface
    {    
        $data = $_POST;
        $db = $this->dbManager->getConnection();
    
        // Валидация
        if (empty($data['name'])) {
            return $this->initJsonResponse(['error' => 'Название категории обязательно'], 400);
        }

        // Загрузка изображения
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $uploadDir = 'uploads/images/categories/';
                $imageName = $this->fileUploader->upload($_FILES['image'], $uploadDir);
            } catch (\RuntimeException $e) {
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    
        // Обработка parent_category_id
        $parentCategoryId = null;
        if (!empty($data['parent_category_id'])) {
            // Проверка существования родительской категории
            $parentCheck = $db->query("SELECT id FROM categories WHERE id = {$db->escape($data['parent_category_id'])} LIMIT 1");
            if (empty($parentCheck)) {
                return $this->initJsonResponse(['error' => 'Родительская категория не существует'], 400);
            }
            $parentCategoryId = (int)$data['parent_category_id'];
        }
    
        // Проверка уникальности имени
        $nameCheck = $db->query(
            "SELECT id FROM categories WHERE name = '{$db->escape($data['name'])}' LIMIT 1"
        );
        if (!empty($nameCheck)) {
            return $this->initJsonResponse(['error' => 'Категория с таким именем уже существует'], 400);
        }
    
        // Создание категории
        $insertData = [
            'name' => $data['name'],
            'parent_category_id' => $parentCategoryId,
            'description' => $data['description'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'image' => $imageName,
        ];
    
        // Экранирование данных с учетом NULL значений
        $escapedData = [];
        foreach ($insertData as $key => $value) {
            $escapedData[$key] = $value === null ? 'NULL' : "'{$db->escape($value)}'";
        }
    
        $columns = implode(', ', array_keys($escapedData));
        $values = implode(', ', array_values($escapedData));
    
        $result = $db->query("INSERT INTO categories ($columns) VALUES ($values)");
    
        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при создании категории'], 500);
        }
    
        $this->redirect('/categories');
        return $this->initJsonResponse();
    }

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Валидация
        if (empty($data['name'])) {
            return $this->initJsonResponse(['error' => 'Название категории обязательно'], 400);
        }

        // Проверка существования категории
        $category = $db->query("SELECT id FROM categories WHERE id = {$id} LIMIT 1");
        if (empty($category)) {
            return $this->initJsonResponse(['error' => 'Категория не найдена'], 404);
        }

        // Загрузка изображения
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $uploadDir = 'uploads/images/categories/';
                $imageName = $this->fileUploader->upload($_FILES['image'], $uploadDir);
            } catch (\RuntimeException $e) {
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }

        // Проверка существования родительской категории
        if (!empty($data['parent_category_id'])) {
            if ($data['parent_category_id'] == $id) {
                return $this->initJsonResponse(['error' => 'Категория не может быть родительской для самой себя'], 400);
            }

            $parentCheck = $db->query("SELECT id FROM categories WHERE id = {$db->escape($data['parent_category_id'])} LIMIT 1");
            if (empty($parentCheck)) {
                return $this->initJsonResponse(['error' => 'Родительская категория не существует'], 400);
            }
        }

        // Проверка уникальности имени
        $nameCheck = $db->query(
            "SELECT id FROM categories 
            WHERE name = '{$db->escape($data['name'])}' 
            AND id != {$id} 
            LIMIT 1"
        );
        if (!empty($nameCheck)) {
            return $this->initJsonResponse(['error' => 'Категория с таким именем уже существует'], 400);
        }

        // Обновление данных
        $updateData = [
            'name' => $data['name'],
            'parent_category_id' => !empty($data['parent_category_id']) ? $data['parent_category_id'] : null,
            'description' => $data['description'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
            'image' => $imageName
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = " . ($value === null ? "NULL" : "'{$db->escape($value)}'");
        }

        $result = $db->query("UPDATE categories SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при обновлении категории'], 500);
        }

        $this->redirect('/categories');
        return $this->initJsonResponse();
    }

    public function apiDelete(int $id): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        // Проверка, что категория не используется как родительская
        $childCheck = $db->query("SELECT id FROM categories WHERE parent_category_id = {$id} LIMIT 1");
        if (!empty($childCheck)) {
            return $this->initJsonResponse(['error' => 'Невозможно удалить категорию, так как она является родительской для других категорий'], 400);
        }

        // Проверка, что категория не используется в товарах
        $productCheck = $db->query("SELECT product_id FROM products_categories WHERE category_id = {$id} LIMIT 1");
        if (!empty($productCheck)) {
            return $this->initJsonResponse(['error' => 'Невозможно удалить категорию, так как она привязана к товарам'], 400);
        }

        $result = $db->query("DELETE FROM categories WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при удалении категории'], 500);
        }

        return $this->initJsonResponse(['success' => true]);
    }

    private function checkAdminAccess(): void
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $user = $this->auth->getUser();
        if ($user['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}