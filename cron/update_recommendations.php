<?php
require_once __DIR__ . '/../configs/config.php';

require_once __DIR__ . '/includes/Service/RecommendationsUpdater.php';
require_once __DIR__ . '/includes/Service/DBConnectionManager.php';

use App\Cron\Service\DBConnectionManager;
use App\Cron\Service\RecommendationsUpdater;

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

$options = getopt("", ["force"]); // --force для принудительного обновления

try {
    $dbManager = new DBConnectionManager();
    $updater = new RecommendationsUpdater($dbManager->getConnection());
    $updater->updateAllRecommendations(isset($options['force']));
    echo "Recommendations updated successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
