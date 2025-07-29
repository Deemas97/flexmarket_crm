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

class ProductsActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager,
        private FileUploader $fileUploader
    ) {
        $this->checkAdminAccess();
        $this->fileUploader->setAllowedMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->fileUploader->setMaxFileSize(5 * 1024 * 1024);
    }

    public function apiCreate(): ControllerResponseInterface
    {    
        $data = $_POST;
        $db = $this->dbManager->getConnection();
    
        // Валидация
        if (empty($data['name'])) {
            return $this->initJsonResponse(['error' => 'Название товара обязательно'], 400);
        }
        
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            return $this->initJsonResponse(['error' => 'Укажите корректную цену товара'], 400);
        }

        // Создаем уникальное имя для директории галереи
        $galleryDir = uniqid('gallery_', true);
        $galleryPath = "uploads/images/products/gallery/$galleryDir";
    
        // Загрузка изображения
        $imageName = null;
        if (!empty($_FILES['image_main']['name'])) {
            try {
                $uploadDir = 'uploads/images/products/main/';
                $imageName = $this->fileUploader->upload($_FILES['image_main'], $uploadDir);
            } catch (\RuntimeException $e) {
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }


        // Загрузка галереи изображений
        $galleryFiles = [];
        if (!empty($_FILES['gallery']['name'][0])) {
            try {
                if (!file_exists($galleryPath)) {
                    mkdir($galleryPath, 0755, true);
                }
                $galleryFiles = $this->fileUploader->uploadMultiple($_FILES['gallery'], $galleryPath);
            } catch (\RuntimeException $e) {
                // Удаляем созданную директорию при ошибке
                if (file_exists($galleryPath)) {
                    array_map('unlink', glob("$galleryPath/*"));
                    rmdir($galleryPath);
                }
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    
        // Создание товара
        $insertData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => (float)$data['price'],
            'stock_quantity' => (int)$data['stock_quantity'],
            'image_main' => $imageName,
            'gallery_path' => !empty($galleryFiles) ? $galleryDir : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    
        // Экранирование данных
        $escapedData = [];
        foreach ($insertData as $key => $value) {
            $escapedData[$key] = $value === null ? 'NULL' : "'{$db->escape($value)}'";
        }
    
        $columns = implode(', ', array_keys($escapedData));
        $values = implode(', ', array_values($escapedData));
    
        $result = $db->query("INSERT INTO products ($columns) VALUES ($values)");
    
        if ($result === false) {
            // Удаляем загруженное изображение при ошибке
            if ($imageName) {
                @unlink("uploads/images/products/main/$imageName");
            }
            return $this->initJsonResponse(['error' => 'Ошибка при создании товара'], 500);
        }
        
        $productId = $db->getLastInsertId();
        
        // Обработка категорий
        if (!empty($data['categories']) && is_array($data['categories'])) {
            $this->updateProductCategories($productId, $data['categories']);
        }
    
        $this->redirect('/products');
        return $this->initJsonResponse();
    }

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Проверка существования товара
        $product = $db->query("SELECT * FROM products WHERE id = {$id} LIMIT 1");
        if (empty($product)) {
            return $this->initJsonResponse(['error' => 'Товар не найден'], 404);
        }
        $product = $product[0];

        // Валидация
        if (empty($data['name'])) {
            return $this->initJsonResponse(['error' => 'Название товара обязательно'], 400);
        }
        
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            return $this->initJsonResponse(['error' => 'Укажите корректную цену товара'], 400);
        }

        // Обработка изображения
        $imageName = $product['image_main'];

        // Обработка галереи
        $galleryDir = $product['gallery_path'];
        $galleryPath = $galleryDir ? "uploads/images/products/gallery/$galleryDir" : null;

        // Удаление галереи при запросе
        if (isset($data['remove_gallery']) && $data['remove_gallery'] && $galleryPath) {
            array_map('unlink', glob("$galleryPath/*"));
            rmdir($galleryPath);
            $galleryDir = null;
        }

        try {
            $uploadDir = 'uploads/images/products/main';

            if ($data['remove_image'] === 'on') {
                if ($imageName) {
                    @unlink($uploadDir . '/' . $imageName);
                    $imageName = null;
                }
            } elseif (!empty($_FILES['image_main']['name'])) {

                $newImageName = $this->fileUploader->upload($_FILES['image_main'], $uploadDir . '/');
            
                // Удаляем старое изображение
                if ($imageName) {
                    @unlink($uploadDir . '/' . $imageName);
                }

                $imageName = $newImageName;
            }
        } catch (\RuntimeException $e) {
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }

        // Загрузка новых изображений в галерею
        if (!empty($_FILES['gallery']['name'][0])) {
            // Создаем новую директорию, если не было старой
            if (!$galleryPath) {
                $galleryDir = uniqid('gallery_', true);
                $galleryPath = "uploads/images/products/gallery/$galleryDir";
            }
            
            if (!file_exists($galleryPath)) {
                mkdir($galleryPath, 0755, true);
            }

            try {
                $this->fileUploader->uploadMultiple($_FILES['gallery'], $galleryPath);
            } catch (\RuntimeException $e) {
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }

        // Обновление данных
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => (float)$data['price'],
            'image_main' => $imageName,
            'gallery_path' => $galleryDir,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = " . ($value === null ? 'NULL' : "'{$db->escape($value)}'");
        }

        $result = $db->query("UPDATE products SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при обновлении товара'], 500);
        }
        
        // Обработка категорий
        if (isset($data['categories']) && is_array($data['categories'])) {
            $this->updateProductCategories($id, $data['categories']);
        }

        $this->redirect('/products');
        return $this->initJsonResponse();
    }

    public function apiDelete(int $id): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        // Получаем товар для удаления файлов
        $product = $db->query("SELECT image_main, gallery_path FROM products WHERE id = {$id} LIMIT 1");
        if (!empty($product)) {
            $product = $product[0];
            
            // Удаляем главное изображение
            $imgMainPath = "uploads/images/products/main/{$product['image_main']}";
            if ($product['image_main'] && file_exists($imgMainPath)) {
                @unlink($imgMainPath);
            }
            
            // Удаляем галерею
            $galleryPath = "uploads/images/products/gallery/{$product['gallery_path']}";
            if ($product['gallery_path'] && file_exists($galleryPath)) {
                array_map('unlink', glob("$galleryPath/*"));
                @rmdir($galleryPath);
            }
        }        

        // Удаляем связи с категориями
        $db->query("DELETE FROM products_categories WHERE product_id = {$id}");

        // Удаляем сам товар
        $result = $db->query("DELETE FROM products WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при удалении товара'], 500);
        }

        return $this->initJsonResponse(['success' => true]);
    }

    public function apiRemoveGalleryImage(int $id): ControllerResponseInterface
    {
        try {    
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON input', 400);
            }
    
            if (empty($input['filename'])) {
                throw new \RuntimeException('Filename is required', 400);
            }
    
            $filename = basename($input['filename']); // Защита от directory traversal
            if ($filename !== $input['filename']) {
                throw new \RuntimeException('Invalid filename', 400);
            }
    
            $db = $this->dbManager->getConnection();
            $product = $db->query("SELECT gallery_path FROM products WHERE id = {$id} LIMIT 1");
            
            if (empty($product)) {
                throw new \RuntimeException('Product not found', 404);
            }
    
            $product = $product[0];
            if (empty($product['gallery_path'])) {
                throw new \RuntimeException('Product has no gallery', 400);
            }
    
            $galleryPath = "uploads/images/products/gallery/{$product['gallery_path']}";
            $filePath = "$galleryPath/$filename";
    
            if (!file_exists($galleryPath) || !is_dir($galleryPath)) {
                throw new \RuntimeException('Gallery directory not found', 404);
            }
    
            if (!file_exists($filePath)) {
                throw new \RuntimeException('File not found in gallery', 404);
            }
    
            if (!@unlink($filePath)) {
                throw new \RuntimeException('Failed to delete file', 500);
            }
    
            $remainingFiles = array_diff(scandir($galleryPath), ['.', '..']);
            if (count($remainingFiles) === 0) {
                if (!@rmdir($galleryPath)) {
                    throw new \RuntimeException('Failed to remove empty gallery directory', 500);
                }
                
                if (!$db->query("UPDATE products SET gallery_path = NULL WHERE id = {$id}")) {
                    throw new \RuntimeException('Failed to update product gallery path', 500);
                }
            }
    
            return $this->initJsonResponse(['success' => true]);
    
        } catch (\RuntimeException $e) {
            error_log('Gallery image deletion error: ' . $e->getMessage());
            
            return $this->initJsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?? 500);
            
        } catch (\Exception $e) {
            error_log('Unexpected error in apiRemoveGalleryImage: ' . $e->getMessage());
            
            return $this->initJsonResponse([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    private function updateProductCategories(int $productId, array $categoryIds): void
    {
        $db = $this->dbManager->getConnection();
        
        $db->query("DELETE FROM products_categories WHERE product_id = {$productId}");
        
        foreach ($categoryIds as $categoryId) {
            $categoryId = (int)$categoryId;
            if ($categoryId > 0) {
                $db->query("INSERT INTO products_categories (product_id, category_id) VALUES ({$productId}, {$categoryId})");
            }
        }
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