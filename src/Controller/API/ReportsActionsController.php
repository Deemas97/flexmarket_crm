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

class ReportsActionsController extends ControllerAbstract
{
    private string $reportsDir = 'storage/reports/';

    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        $this->checkAuth();
        $this->ensureReportsDirExists();
    }

    public function apiGenerateReport(): ControllerResponseInterface
    {
        $type = $_POST['type'] ?? 'sales';
        $period = $_POST['period'] ?? '30days';
        $format = $_POST['format'] ?? 'csv';

        try {
            $reportData = $this->getReportData($type, $period);
            $fileName = $this->generateFileName($type, $format);
            $filePath = $this->reportsDir . $fileName;

            if ($format === 'csv') {
                $this->generateCSV($filePath, $reportData);
            } else {
                $this->generateTXT($filePath, $reportData);
            }

            $fileSize = filesize($filePath);
            $this->saveReportToDatabase($type, $fileName, $fileSize, $this->auth->getUserId());

            return $this->initJsonResponse([
                'success' => true,
                'message' => 'Report generated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->initJsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiListReports(): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();
        $reports = $db->query("
            SELECT id, name, type, created_at, size 
            FROM reports
            WHERE employee_id = {$this->auth->getUserId()}
            ORDER BY created_at DESC
            LIMIT 50
        ") ?? [];

        return $this->initJsonResponse(['reports' => $reports]);
    }

    public function apiDownloadReport(int $id): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();
        $report = $db->query("SELECT name, type FROM reports WHERE id = {$id} AND employee_id = {$this->auth->getUserId()}")[0];

        if (!$report) {
            return $this->initJsonResponse(['error' => 'Report not found'], 404);
        }

        $filePath = $this->reportsDir . $report['name'];
        if (!file_exists($filePath)) {
            return $this->initJsonResponse(['error' => 'Report file not found'], 404);
        }

        $mimeType = $this->getMimeType($report['type']);
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function apiDeleteReport(int $id): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();
        $report = $db->query("SELECT name FROM reports WHERE id = {$id} AND employee_id = {$this->auth->getUserId()} LIMIT 1")[0];

        if (!$report) {
            return $this->initJsonResponse(['error' => 'Report not found'], 404);
        }

        $filePath = $this->reportsDir . $report['name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $db->query("DELETE FROM reports WHERE id = {$id}");

        return $this->initJsonResponse(['success' => true]);
    }

    private function getReportData(string $type, string $period): array
    {
        $db = $this->dbManager->getConnection();
        
        $dateRange = $this->getDateRange($period);
        
        switch ($type) {
            case 'sales':
                return $db->query("
                    SELECT 
                        DATE_FORMAT(o.created_at, '%Y-%m-%d') AS date,
                        COUNT(*) AS orders_count,
                        SUM(o.sum) AS total_amount,
                        AVG(o.sum) AS avg_order_value
                    FROM orders o
                    WHERE o.status != 'cancelled'
                    AND o.created_at >= {$dateRange['start']}
                    GROUP BY date
                    ORDER BY date DESC
                ") ?? [];
                
            case 'products':
                return $db->query("
                    SELECT 
                        p.id,
                        p.name,
                        cm.name AS company_name,
                        SUM(op.count) AS total_sold,
                        SUM(op.count * op.price) AS total_revenue,
                        COUNT(DISTINCT o.id) AS orders_count
                    FROM products p
                    LEFT JOIN companies cm ON p.company_id = cm.id
                    LEFT JOIN orders_products op ON p.id = op.product_id
                    LEFT JOIN orders o ON op.order_id = o.id AND o.status != 'cancelled'
                    WHERE o.created_at >= {$dateRange['start']}
                    GROUP BY p.id, p.name, cm.name
                    ORDER BY total_sold DESC
                    LIMIT 100
                ") ?? [];
                
            case 'customers':
                return $db->query("
                    SELECT 
                        c.id,
                        CONCAT(c.f, ' ', c.i) AS customer_name,
                        c.role_id,
                        COUNT(o.id) AS orders_count,
                        SUM(o.sum) AS total_spent
                    FROM customers c
                    LEFT JOIN orders o ON c.id = o.customer_id AND o.status != 'cancelled'
                    WHERE o.created_at >= {$dateRange['start']}
                    GROUP BY c.id, c.f, c.i, c.role_id
                    ORDER BY total_spent DESC
                    LIMIT 100
                ") ?? [];
                
            case 'inventory':
                return $db->query("
                    SELECT 
                        p.id,
                        p.name,
                        p.stock_quantity,
                        SUM(IFNULL(op.count, 0)) AS total_sold
                    FROM products p
                    LEFT JOIN orders_products op ON p.id = op.product_id
                    LEFT JOIN orders o ON op.order_id = o.id AND o.status != 'cancelled'
                    WHERE o.created_at IS NULL OR o.created_at >= {$dateRange['start']}
                    GROUP BY p.id, p.name, p.stock_quantity
                    ORDER BY p.stock_quantity ASC
                    LIMIT 100
                ") ?? [];
                
            default:
                throw new \Exception("Unknown report type: $type");
        }
    }
    
    private function getDateRange(string $period): array
    {
        $intervals = [
            '30days' => '1 MONTH',
            '3months' => '3 MONTH',
            '6months' => '6 MONTH',
            '12months' => '12 MONTH'
        ];
        
        $interval = $intervals[$period] ?? '1 MONTH';
        
        return [
            'start' => date('Y-m-d', strtotime("-$interval")),
            'end' => date('Y-m-d')
        ];
    }

    private function generateCSV(string $filePath, array $data): void
    {
        $file = fopen($filePath, 'w');
        
        if (!empty($data)) {
            fputcsv($file, array_keys($data[0]));
            
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        }
        
        fclose($file);
    }

    private function generateTXT(string $filePath, array $data): void
    {
        $file = fopen($filePath, 'w');
        
        if (!empty($data)) {
            fwrite($file, implode("\t", array_keys($data[0])) . "\n");
            
            foreach ($data as $row) {
                fwrite($file, implode("\t", $row) . "\n");
            }
        }
        
        fclose($file);
    }

    private function generateFileName(string $type, string $format): string
    {
        $date = date('Y-m-d_H-i-s');
        return "report_{$type}_{$date}.{$format}";
    }

    private function saveReportToDatabase(string $type, string $fileName, int $fileSize, int $userId): void
    {
        $db = $this->dbManager->getConnection();
        $reportName = $this->getReportDisplayName($type);

        $insertData = [
            'name' => $fileName,
            'type' => $reportName,
            'created_at' => date('Y-m-d H:i:s'),
            'size' => $fileSize,
            'employee_id' => $userId
        ];

        $escapedData = array_map([$db, 'escape'], $insertData);
        $columns = implode(', ', array_keys($escapedData));
        $values = "'" . implode("', '", array_values($escapedData)) . "'";

        $db->query("INSERT INTO reports ($columns) VALUES ($values)");
    }

    private function getReportDisplayName(string $type): string
    {
        $names = [
            'sales' => 'Отчет по продажам',
            'products' => 'Отчет по товарам',
            'customers' => 'Отчет по клиентам',
            'inventory' => 'Отчет по складу'
        ];
        
        return $names[$type] ?? 'Неизвестный отчет';
    }

    private function getMimeType(string $reportType): string
    {
        if (strpos($reportType, 'csv') !== false) {
            return 'text/csv';
        }
        return 'text/plain';
    }

    private function ensureReportsDirExists(): void
    {
        if (!is_dir($this->reportsDir)) {
            mkdir($this->reportsDir, 0755, true);
        }
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