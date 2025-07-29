<?php
namespace App\Cron\Service;

include_once __DIR__ . '/../ServiceInterface.php';
include_once __DIR__ . '/MySQLConnector.php';

use App\Cron\ServiceInterface;
use DateTime;
use Exception;

class RecommendationsUpdater implements ServiceInterface
{
    public function __construct(
        private MySQLConnector $dbConnection
    )
    {}
    
    public function updateAllRecommendations(bool $force = false): void
    {
        // Проверяем, когда последний раз обновлялись рекомендации
        if (!$force) {
            $lastUpdate = $this->getLastUpdateTime();
            if ($lastUpdate && $lastUpdate->format('Y-m-d') === date('Y-m-d')) {
                throw new Exception("Recommendations were already updated today");
            }
        }
        
        // Начинаем транзакцию
        $this->dbConnection->beginTransaction();
        
        try {
            // Обновляем глобальные рекомендации
            $this->updateGlobalRecommendations();
            
            // Обновляем персонализированные рекомендации для всех пользователей
            $this->updatePersonalRecommendations();
            
            // Фиксируем изменения
            $this->dbConnection->commit();
            
            // Записываем время последнего обновления
            $this->setLastUpdateTime();
        } catch (Exception $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }
    }
    
    private function updateGlobalRecommendations(): void
    {
        // Удаляем старые рекомендации
        $this->dbConnection->query("TRUNCATE TABLE recommendations");
        
        // Алгоритм расчета глобальных рекомендаций:
        // 1. Самые популярные товары (по количеству покупок)
        // 2. Лучшие по рейтингу товары
        // 3. Новинки (последние добавленные товары)
        
        $query = "
            INSERT INTO recommendations (product_id, recommendation_score, created_at, updated_at)
            SELECT 
                p.id AS product_id,
                (
                    (COALESCE(op.popularity_score, 0) * 0.5) + 
                    (COALESCE(r.rating_score, 0) * 0.3) + 
                    (COALESCE(p.newness_score, 0) * 0.2)
                ) AS recommendation_score,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM products p
            LEFT JOIN (
                SELECT product_id, COUNT(*) as purchase_count, 
                       (COUNT(*) / (SELECT MAX(count) FROM (SELECT COUNT(*) as count FROM orders_products GROUP BY product_id) t)) as popularity_score
                FROM orders_products op
                JOIN orders o ON op.order_id = o.id
                WHERE o.status = 'delivered'
                GROUP BY product_id
            ) op ON p.id = op.product_id
            LEFT JOIN (
                SELECT product_id, AVG(rating) as avg_rating,
                       (AVG(rating) / 5) as rating_score
                FROM reviews
                GROUP BY product_id
            ) r ON p.id = r.product_id
            LEFT JOIN (
                SELECT id, 
                       (DATEDIFF(NOW(), created_at) < 30) as is_new,
                       (1 - (DATEDIFF(NOW(), created_at) / 30)) as newness_score
                FROM products
            ) n ON p.id = n.id
            WHERE p.stock_quantity > 0
            ORDER BY recommendation_score DESC
            LIMIT 100
        ";
        
        $this->dbConnection->query($query);
    }
    
    private function updatePersonalRecommendations(): void
    {
        // Получаем всех активных пользователей
        $customers = $this->dbConnection->query("
            SELECT id FROM customers 
            WHERE status = 'active'
        ");
        
        foreach ($customers as $customer) {
            $this->updateRecommendationsForCustomer($customer['id']);
        }
    }
    
    private function updateRecommendationsForCustomer(int $customerId): void
    {
        // Удаляем старые рекомендации для пользователя
        $this->dbConnection->query("
            DELETE FROM personal_recommendations 
            WHERE customer_id = {$customerId}
        ");
        
        // Алгоритм расчета персонализированных рекомендаций:
        // 1. На основе истории покупок (похожие товары)
        // 2. На основе оценок (похожие товары по рейтингам)
        // 3. Глобальные рекомендации с поправкой на предпочтения
        
        $query = "
            INSERT INTO personal_recommendations 
                (customer_id, product_id, recommendation_score, created_at, updated_at)
            SELECT 
                {$customerId} AS customer_id,
                p.id AS product_id,
                (
                    (COALESCE(similar_purchases.score, 0) * 0.6) +
                    (COALESCE(similar_ratings.score, 0) * 0.3) +
                    (COALESCE(global.score, 0) * 0.1)
                ) AS recommendation_score,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM products p
            LEFT JOIN (
                -- Похожие товары на основе покупок
                SELECT op2.product_id, 
                       COUNT(*) as similarity_score,
                       (COUNT(*) / (SELECT COUNT(DISTINCT op1.product_id) 
                                    FROM orders_products op1
                                    JOIN orders o ON op1.order_id = o.id
                                    WHERE o.customer_id = {$customerId})) as score
                FROM orders_products op1
                JOIN orders o1 ON op1.order_id = o1.id
                JOIN orders_products op2 ON op1.order_id != op2.order_id AND op1.product_id != op2.product_id
                JOIN orders o2 ON op2.order_id = o2.id
                WHERE o1.customer_id = {$customerId} AND o2.customer_id != {$customerId}
                GROUP BY op2.product_id
            ) similar_purchases ON p.id = similar_purchases.product_id
            LEFT JOIN (
                -- Похожие товары на основе оценок
                SELECT r2.product_id,
                       COUNT(*) as similarity_count,
                       (COUNT(*) / (SELECT COUNT(*) FROM reviews WHERE customer_id = {$customerId})) as score
                FROM reviews r1
                JOIN reviews r2 ON r1.customer_id = r2.customer_id AND r1.product_id != r2.product_id
                WHERE r1.customer_id = {$customerId}
                GROUP BY r2.product_id
            ) similar_ratings ON p.id = similar_ratings.product_id
            LEFT JOIN (
                -- Глобальные рекомендации
                SELECT product_id, recommendation_score as score
                FROM recommendations
            ) global ON p.id = global.product_id
            WHERE p.stock_quantity > 0
            ORDER BY recommendation_score DESC
            LIMIT 50
        ";
        
        if (!$this->dbConnection->query($query)) {
            echo $this->dbConnection->getLastError();
        }
    }
    
    private function getLastUpdateTime(): ?DateTime
    {
        $result = $this->dbConnection->query("
            SELECT value FROM system_settings 
            WHERE name = 'recommendations_last_update'
        ");
        
        return (isset($result[0]) ? new DateTime($result[0]['value']) : null);
    }
    
    private function setLastUpdateTime(): void
    {
        $result = $this->dbConnection->query("
            INSERT INTO system_settings (name, value, updated_at)
            VALUES ('recommendations_last_update', NOW(), NOW())
            ON DUPLICATE KEY UPDATE value = NOW(), updated_at = NOW()
        ");

        if (!$result) {
            echo $this->dbConnection->getLastError();
        }
    }
}