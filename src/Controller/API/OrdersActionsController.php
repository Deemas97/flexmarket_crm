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

class OrdersActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        $this->checkAdminAccess();
    }

    public function apiUpdateStatus(int $id): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            // Validate required fields
            if (empty($data['status'])) {
                throw new RuntimeException('Статус заказа обязателен');
            }

            // Check if order exists
            $order = $db->query("SELECT id, status FROM orders WHERE id = {$id} LIMIT 1");
            if (empty($order)) {
                throw new RuntimeException('Заказ не найден');
            }

            $currentStatus = $order[0]['status'];

            // Validate status value
            $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new RuntimeException('Недопустимый статус заказа');
            }

            $updateData = [
                'status' => $data['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updateParts = [];
            foreach ($updateData as $key => $value) {
                $updateParts[] = "$key = '{$db->escape($value)}'";
            }

            $result = $db->query("UPDATE orders SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении статуса заказа');
            }

            if (in_array($data['status'], ['cancelled', 'completed'])) {
                $newPositionStatus = $data['status'] === 'cancelled' ? 'cancelled' : 'delivered';
                $this->updateAllOrderPositionsStatus($id, $newPositionStatus);
            }

            if ($data['status'] === 'cancelled' && $currentStatus !== 'pending') {
                $this->restoreStock($id);
            }

            $db->commit();
            $this->redirect('/orders');
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiUpdatePositionStatus(int $positionId): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            if (empty($data['status'])) {
                throw new RuntimeException('Статус позиции обязателен');
            }

            $position = $db->query("
                SELECT op.id, op.order_id, op.status, o.status as order_status 
                FROM orders_products op
                JOIN orders o ON op.order_id = o.id
                WHERE op.id = {$positionId}
                LIMIT 1
            ");
            
            if (empty($position)) {
                throw new RuntimeException('Позиция заказа не найдена');
            }

            $position = $position[0];
            $orderId = $position['order_id'];
            $currentStatus = $position['status'];
            $orderStatus = $position['order_status'];

            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new RuntimeException('Недопустимый статус позиции');
            }

            if (in_array($orderStatus, ['completed', 'cancelled'])) {
                throw new RuntimeException('Нельзя изменить статус позиции в завершенном или отмененном заказе');
            }

            $updateData = [
                'status' => $data['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updateParts = [];
            foreach ($updateData as $key => $value) {
                $updateParts[] = "$key = '{$db->escape($value)}'";
            }

            $result = $db->query("
                UPDATE orders_products 
                SET " . implode(', ', $updateParts) . " 
                WHERE id = {$positionId}
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении статуса позиции');
            }

            // Automatically update order status based on position statuses
            $this->updateOrderStatusBasedOnPositions($orderId);

            $db->commit();

            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function updateOrderStatusBasedOnPositions(int $orderId): void
    {
        $db = $this->dbManager->getConnection();

        // Get current order status
        $order = $db->query("SELECT status FROM orders WHERE id = {$orderId} LIMIT 1");
        if (empty($order)) {
            return;
        }
        $currentOrderStatus = $order[0]['status'];

        // If order is already completed or cancelled, don't change it
        if (in_array($currentOrderStatus, ['completed', 'cancelled'])) {
            return;
        }

        // Get all positions for the order
        $positions = $db->query("
            SELECT status 
            FROM orders_products 
            WHERE order_id = {$orderId}
        ");

        if (empty($positions)) {
            return;
        }

        $allCancelled = true;
        $allDelivered = true;
        $hasActivePositions = false;

        foreach ($positions as $position) {
            if ($position['status'] !== 'cancelled') {
                $allCancelled = false;
            }
            
            if ($position['status'] !== 'delivered') {
                $allDelivered = false;
            }
            
            if (!in_array($position['status'], ['cancelled', 'delivered'])) {
                $hasActivePositions = true;
            }
        }

        $newStatus = null;
        
        if ($allCancelled) {
            $newStatus = 'cancelled';
        } elseif ($allDelivered) {
            $newStatus = 'completed';
        } elseif ($hasActivePositions) {
            $newStatus = 'processing';
        } else {
            // If some delivered and some cancelled, set to processing
            $newStatus = 'processing';
        }

        if ($newStatus !== null && $newStatus !== $currentOrderStatus) {
            $db->query("
                UPDATE orders 
                SET status = '{$db->escape($newStatus)}', 
                    updated_at = NOW() 
                WHERE id = {$orderId}
            ");
            
            // If order is cancelled, restore stock
            if ($newStatus === 'cancelled') {
                $this->restoreStock($orderId);
            }
        }
    }

    private function updateAllOrderPositionsStatus(int $orderId, string $status): void
    {
        $db = $this->dbManager->getConnection();
        $db->query("
            UPDATE orders_products 
            SET status = '{$db->escape($status)}', 
                updated_at = NOW() 
            WHERE order_id = {$orderId}
        ");
    }

    public function apiCancel(int $id): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            $order = $db->query("SELECT id, status FROM orders WHERE id = {$id} LIMIT 1");
            if (empty($order)) {
                throw new RuntimeException('Заказ не найден');
            }

            $currentStatus = $order[0]['status'];
            if ($currentStatus === 'completed') {
                throw new RuntimeException('Нельзя отменить уже завершенный заказ');
            }

            if ($currentStatus === 'cancelled') {
                throw new RuntimeException('Заказ уже отменен');
            }

            $updateData = [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updateParts = [];
            foreach ($updateData as $key => $value) {
                $updateParts[] = "$key = '{$db->escape($value)}'";
            }

            $result = $db->query("UPDATE orders SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

            if ($result === false) {
                throw new RuntimeException('Ошибка при отмене заказа');
            }

            if ($currentStatus !== 'pending') {
                $this->restoreStock($id);
            }

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function restoreStock(int $orderId): void
    {
        $db = $this->dbManager->getConnection();

        // Get all products from the order
        $products = $db->query("SELECT product_id, count FROM orders_products WHERE order_id = {$orderId}");

        if (!empty($products)) {
            foreach ($products as $product) {
                // Update product stock
                $result = $db->query("
                    UPDATE products 
                    SET stock_quantity = stock_quantity + {$product['count']} 
                    WHERE id = {$product['product_id']}
                ");

                if ($result === false) {
                    throw new RuntimeException('Ошибка при возврате товара на склад');
                }
            }
        }
    }

    public function apiAddProduct(int $orderId): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            // Validate required fields
            if (empty($data['product_id']) || empty($data['count'])) {
                throw new RuntimeException('ID товара и количество обязательны');
            }

            // Check if order exists and can be modified
            $order = $db->query("SELECT id, status FROM orders WHERE id = {$orderId} LIMIT 1");
            if (empty($order)) {
                throw new RuntimeException('Заказ не найден');
            }

            $orderStatus = $order[0]['status'];
            if ($orderStatus !== 'pending') {
                throw new RuntimeException('Можно добавлять товары только в заказы со статусом "Ожидает"');
            }

            // Check if product exists and has enough stock
            $product = $db->query("SELECT id, price, stock_quantity FROM products WHERE id = {$db->escape($data['product_id'])} LIMIT 1");
            if (empty($product)) {
                throw new RuntimeException('Товар не найден');
            }

            $product = $product[0];
            $count = (int)$data['count'];

            if ($count <= 0) {
                throw new RuntimeException('Количество должно быть больше нуля');
            }

            if ($product['stock_quantity'] < $count) {
                throw new RuntimeException('Недостаточно товара на складе');
            }

            // Check if product already exists in order
            $existingProduct = $db->query("
                SELECT id, count 
                FROM orders_products 
                WHERE order_id = {$orderId} AND product_id = {$product['id']}
                LIMIT 1
            ");

            if (!empty($existingProduct)) {
                // Update existing product count
                $newCount = $existingProduct[0]['count'] + $count;
                $result = $db->query("
                    UPDATE orders_products 
                    SET count = {$newCount} 
                    WHERE id = {$existingProduct[0]['id']}
                ");
            } else {
                // Add new product to order
                $result = $db->query("
                    INSERT INTO orders_products (order_id, product_id, price, count)
                    VALUES (
                        {$orderId},
                        {$product['id']},
                        {$product['price']},
                        {$count}
                    )
                ");
            }

            if ($result === false) {
                throw new RuntimeException('Ошибка при добавлении товара в заказ');
            }

            // Update product stock
            $result = $db->query("
                UPDATE products 
                SET stock_quantity = stock_quantity - {$count} 
                WHERE id = {$product['id']}
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении количества товара');
            }

            // Update order total
            $this->updateOrderTotal($orderId);

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiRemoveProduct(int $orderId, int $productId): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            // Check if order exists and can be modified
            $order = $db->query("SELECT id, status FROM orders WHERE id = {$orderId} LIMIT 1");
            if (empty($order)) {
                throw new RuntimeException('Заказ не найден');
            }

            $orderStatus = $order[0]['status'];
            if ($orderStatus !== 'pending') {
                throw new RuntimeException('Можно удалять товары только из заказов со статусом "Ожидает"');
            }

            // Check if product exists in order
            $productInOrder = $db->query("
                SELECT id, count 
                FROM orders_products 
                WHERE order_id = {$orderId} AND product_id = {$productId}
                LIMIT 1
            ");

            if (empty($productInOrder)) {
                throw new RuntimeException('Товар не найден в заказе');
            }

            $count = $productInOrder[0]['count'];

            // Remove product from order
            $result = $db->query("
                DELETE FROM orders_products 
                WHERE order_id = {$orderId} AND product_id = {$productId}
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при удалении товара из заказа');
            }

            // Return product to stock
            $result = $db->query("
                UPDATE products 
                SET stock_quantity = stock_quantity + {$count} 
                WHERE id = {$productId}
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при возврате товара на склад');
            }

            // Update order total
            $this->updateOrderTotal($orderId);

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function updateOrderTotal(int $orderId): void
    {
        $db = $this->dbManager->getConnection();

        // Calculate new order total
        $total = $db->query("
            SELECT SUM(price * count) as total 
            FROM orders_products 
            WHERE order_id = {$orderId}
        ");

        if (!empty($total)) {
            $newTotal = $total[0]['total'] ?? 0;
            $result = $db->query("UPDATE orders SET sum = {$newTotal} WHERE id = {$orderId}");
            
            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении суммы заказа');
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