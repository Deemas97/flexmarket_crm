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

class CustomerStorageApiController extends ControllerAbstract
{
    private string $imagesDir = 'uploads/images/';
    private array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager,
        private FileUploader $fileUploader
    ) {
        $this->fileUploader->setAllowedMimeTypes($this->allowedMimeTypes);
    }

    public function apiGetImages(): ControllerResponseInterface
    {
        if (!$this->checkAuth()) {
            return $this->initJsonResponse(['error' => 'Неверный API токен'], 403);
        }

        $pathDir = $this->imagesDir . '/' . $_GET['directory'];

        if (!is_dir($pathDir) || !is_readable($pathDir)) {
            return $this->initJsonResponse(['error' => 'Файловая директория не существует'], 400);
        }

        $matchedFiles = [];
        $iterator = new \DirectoryIterator($pathDir);

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getPathname();
            $mimeType = mime_content_type($filePath);

            if (!in_array($mimeType, $this->allowedMimeTypes)) {
                continue;
            }

            $matchedFiles[] = $file->getFilename();
        }

        return $this->initJsonResponse(['images' => $matchedFiles], 200);
    }

    private function checkAuth(): bool
    {
        $token = ($_POST['api_token'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : null));

        if (!$token) {
            return false;
        }

        $db = $this->dbManager->getConnection();
        $user = $db->query("
            SELECT c.id, c.status, c.api_token
            FROM customers AS c
            WHERE c.api_token = '{$db->escape($token)}'
        ")[0];

        if (!$user) {
            return false;
        }

        if (!isset($user)) {
            return false;
        }

        if ($user['status'] !== 'active') {
            return false;
        }
        
        return ($token === $user['api_token']);
    }
}