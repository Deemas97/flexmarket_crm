<?php
namespace App\Cron\Service;

include_once __DIR__ . '/../ServiceInterface.php';
include_once __DIR__ . '/MySQLConnector.php';

use App\Cron\ServiceInterface;
use App\Cron\Service\MySQLConnector;
use DateTime;
use Exception;

class SimilarProductsUpdater implements ServiceInterface
{
    private int $minCountPurchases = 2;
    private int $minCountRatings = 2;
    private int $minRating = 4;
    private int $maxSimilarProducts = 20;

    public function __construct(
        private MySQLConnector $dbConnection
    ) {}

    public function updateAllSimilarProducts(bool $force = false): void
    {
        try {
            if (!$force && $this->wasUpdatedRecently()) {
                throw new Exception("Similar products were updated less than 7 days ago");
            }

            $this->dbConnection->beginTransaction();
            
            $this->updateSimilarByRatings();
            
            $this->dbConnection->commit();
            $this->setLastUpdateTime();
            
        } catch (Exception $e) {
            if ($this->dbConnection->checkTransaction()) {
                $this->dbConnection->rollBack();
            }
            throw new Exception("Failed to update similar products: " . $e->getMessage());
        }
    }

    private function wasUpdatedRecently(): bool
    {
        $lastUpdate = $this->getLastUpdateTime();
        return ($lastUpdate && $lastUpdate->diff(new DateTime())->days < 7);
    }

    private function updateSimilarByRatings(): void
    {
        $this->dbConnection->query("TRUNCATE TABLE similar_products_ratings");
    
        $query = "
            INSERT INTO similar_products_ratings 
                (product_id, similar_product_id, similarity_score, updated_at)
            SELECT DISTINCT * FROM (
                SELECT 
                    r1.product_id,
                    r2.product_id AS similar_product_id,
                    COUNT(*) / (
                        SELECT COUNT(*) 
                        FROM reviews r 
                        WHERE r.product_id = r1.product_id
                    ) AS similarity_score,
                    NOW() AS updated_at
                FROM 
                    reviews r1
                    JOIN reviews r2 ON r1.customer_id = r2.customer_id 
                        AND r1.product_id != r2.product_id
                WHERE 
                    r1.rating >= {$this->minRating} AND r2.rating >= {$this->minRating}
                GROUP BY 
                    r1.product_id, r2.product_id
                HAVING 
                    COUNT(*) >= {$this->minCountRatings}
                ORDER BY 
                    r1.product_id, similarity_score DESC
            ) ranked
            WHERE (
                SELECT COUNT(*) 
                FROM similar_products_ratings spr 
                WHERE spr.product_id = ranked.product_id
            ) < {$this->maxSimilarProducts} OR {$this->maxSimilarProducts} = 0
        ";
    
        if (!$this->dbConnection->query($query)) {
            echo $this->dbConnection->getLastError();
        }
    }

    private function getLastUpdateTime(): ?DateTime
    {
        $result = $this->dbConnection->query("
            SELECT value FROM system_settings 
            WHERE name = 'similar_products_last_update'
        ");
        
        return (isset($result[0]) ? new DateTime($result[0]['value']) : null);
    }

    private function setLastUpdateTime(): void
    {
        $result = $this->dbConnection->query("
            INSERT INTO system_settings (name, value, updated_at)
            VALUES ('similar_products_last_update', NOW(), NOW())
            ON DUPLICATE KEY UPDATE value = NOW(), updated_at = NOW()
        ");

        if (!$result) {
            echo $this->dbConnection->getLastError();
        }
    }
}