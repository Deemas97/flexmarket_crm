<?php
// DEV
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
////

require_once '../src/Kernel.php';

use App\Kernel;
use Exception;

try {
    $response = (new Kernel())->run();
} catch(Exception $error) {
    echo '[Критическая ошибка]: ' . $error->getMessage();
}