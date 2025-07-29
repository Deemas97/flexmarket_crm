<?php
require_once __DIR__ . '/../configs/config.php';

require_once __DIR__ . '/includes/Service/SimilarProductsUpdater.php';
require_once __DIR__ . '/includes/Service/DBConnectionManager.php';

use App\Cron\Service\DBConnectionManager;
use App\Cron\Service\SimilarProductsUpdater;

// Проверка, что скрипт запущен из CLI
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Обработка аргументов командной строки
$options = getopt("", ["force"]); // --force для принудительного обновления

try {
    $dbManager = new DBConnectionManager();
    $updater = new SimilarProductsUpdater($dbManager->getConnection());
    $updater->updateAllSimilarProducts(isset($options['force']));
    echo "Similar products updated successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);