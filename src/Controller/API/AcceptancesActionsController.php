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
use RuntimeException;

class AcceptancesActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {}

    public function apiCreate(): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            // Валидация
            if (empty($data['company_id']) || empty($data['product_id']) || empty($data['count'])) {
                throw new RuntimeException('Все обязательные поля должны быть заполнены');
            }

            $insertData = [
                'company_id' => $data['company_id'],
                'product_id' => $data['product_id'],
                'count' => $data['count'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $escapedData = array_map([$db, 'escape'], $insertData);
            $columns = implode(', ', array_keys($escapedData));
            $values = "'" . implode("', '", array_values($escapedData)) . "'";

            $result = $db->query("INSERT INTO acceptances ($columns) VALUES ($values)");

            if ($result === false) {
                throw new RuntimeException('Ошибка при создании приёмки');
            }

            $result = $db->query("UPDATE products SET stock_quantity = stock_quantity + {$insertData['count']} WHERE id = {$insertData['product_id']}");

            if ($result === false) {
                throw new \RuntimeException('Ошибка при обновлении количества товара');
            }

            $db->commit();
            $this->redirect('/acceptances');
            return $this->initJsonResponse();
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            // Валидация
            if (empty($data['company_id']) || empty($data['product_id']) || empty($data['count'])) {
                throw new RuntimeException('Все обязательные поля должны быть заполнены');
            }

            // Получаем текущие данные приёмки
            $currentAcceptance = $db->query("SELECT product_id, count FROM acceptances WHERE id = {$id} LIMIT 1");
            if (empty($currentAcceptance)) {
                throw new RuntimeException('Приёмка не найдена');
            }

            $currentProductId = $currentAcceptance[0]['product_id'];
            $currentCount = $currentAcceptance[0]['count'];

            // Обновление данных
            $updateData = [
                'company_id' => $data['company_id'],
                'product_id' => $data['product_id'],
                'count' => $data['count']
            ];

            $updateParts = [];
            foreach ($updateData as $key => $value) {
                $updateParts[] = "$key = '{$db->escape($value)}'";
            }

            $result = $db->query("UPDATE acceptances SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении приёмки');
            }

            // Логика обновления количества товара
            if ($currentProductId != $data['product_id']) {
                // Если изменился продукт, возвращаем количество к старому продукту
                $result = $db->query("UPDATE products SET stock_quantity = stock_quantity - {$currentCount} WHERE id = {$currentProductId}");
                if ($result === false) {
                    throw new RuntimeException('Ошибка при обновлении количества старого товара');
                }

                // Добавляем количество к новому продукту
                $result = $db->query("UPDATE products SET stock_quantity = stock_quantity + {$data['count']} WHERE id = {$data['product_id']}");
                if ($result === false) {
                    throw new RuntimeException('Ошибка при обновлении количества нового товара');
                }
            } else {
                // Если продукт не изменился, корректируем количество
                $delta = $data['count'] - $currentCount;
                $result = $db->query("UPDATE products SET stock_quantity = stock_quantity + {$delta} WHERE id = {$data['product_id']}");
                if ($result === false) {
                    throw new RuntimeException('Ошибка при обновлении количества товара');
                }
            }

            $db->commit();
            $this->redirect('/acceptances');
            return $this->initJsonResponse();
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()]);
        }
    }

    public function apiDelete(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            $result = $db->query("SELECT product_id, count FROM acceptances WHERE id = {$id} LIMIT 1");
            if (empty($result)) {
                throw new RuntimeException('Приёмка не найдена');
            }

            $productId = $result[0]['product_id'];
            $count = $result[0]['count'];
            
            $result = $db->query("DELETE FROM acceptances WHERE id = {$id}");
            if ($result === false) {
                throw new RuntimeException('Ошибка при удалении приёмки');
            }

            $result = $db->query("SELECT stock_quantity FROM products WHERE id = {$productId} LIMIT 1");
            if (empty($result)) {
                throw new RuntimeException('Товар не найден');
            }

            $stockQuantity = $result[0]['stock_quantity'];

            if ($stockQuantity < $count) {
                $result = $db->query("UPDATE products SET stock_quantity = 0 WHERE id = {$productId}");
            } else {
                $result = $db->query("UPDATE products SET stock_quantity = stock_quantity - {$count} WHERE id = {$productId}");
            }

            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении количества товара');
            }

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()]);
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}