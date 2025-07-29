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

class VendorStorageApiController extends ControllerAbstract
{
    private string $baseDir = 'uploads/images';
    private array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private int $maxFileSize = 10 * 1024 * 1024;

    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager,
        private FileUploader $fileUploader
    ) {
        $this->fileUploader->setAllowedMimeTypes($this->allowedMimeTypes);
        $this->fileUploader->setMaxFileSize($this->maxFileSize);
    }

    public function apiGetImages(): ControllerResponseInterface
    {
        try {
            if (!$this->checkAuth()) {
                throw new \RuntimeException('Invalid API token', 403);
            }

            $directory = $this->sanitizePath($_GET['directory'] ?? '');
            $path = $this->getFullPath($directory);

            if (!is_dir($path)) {
                throw new \RuntimeException('Directory not found', 404);
            }

            $images = $this->scanDirectoryForImages($path);

            return $this->initJsonResponse([
                'success' => true,
                'directory' => $directory,
                'images' => $images
            ], 200);

        } catch (\RuntimeException $e) {
            return $this->initJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function apiUploadImage(): ControllerResponseInterface
    {
        try {
            if (!$this->checkAuth()) {
                throw new \RuntimeException('Invalid API token', 403);
            }

            if (!empty($_FILES['image_main']['name'])) {
                $uploadDir = 'uploads/images/products/main/';
                $imageName = $this->fileUploader->upload($_FILES['image_main'], $uploadDir);

                return $this->initJsonResponse([
                    'success' => true,
                    'image_main' => $imageName,
                ], 201);
            }

            throw new \RuntimeException('No image file provided', 400);

        } catch (\RuntimeException $e) {
            return $this->initJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function apiUploadGallery(): ControllerResponseInterface
    {
        try {        
            if (!$this->checkAuth()) {
                throw new \RuntimeException('Invalid API token', 403);
            }
        
            $galleryDir = $_POST['gallery_dir'] ?? 'products/gallery/' . uniqid('gallery_', true);
            $fullPath = $this->getFullPath($galleryDir);
        
            $this->ensureDirectoryExists($fullPath);
        
            $uploadedFiles = [];
        
            if (!empty($_FILES['files'])) {
                $uploadedFiles = $this->fileUploader->uploadMultiple($_FILES['files'], $fullPath);
            }
        
            if (empty($uploadedFiles)) {
                if (strpos($galleryDir, 'gallery_') !== false && $this->isDirectoryEmpty($fullPath)) {
                    rmdir($fullPath);
                }
                throw new \RuntimeException('File upload failed', 400);
            }
        
            return $this->initJsonResponse([
                'success' => true,
                'gallery_dir' => $galleryDir,
                'uploaded_files' => $uploadedFiles
            ], 201);
        
        } catch (\RuntimeException $e) {
            return $this->initJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function apiDeleteGallery(): ControllerResponseInterface
    {
        try {
            if (!$this->checkAuth()) {
                throw new \RuntimeException('Invalid API token', 403);
            }

            $input = $this->getJsonInput();
            
            if (empty($input['gallery_dir'])) {
                throw new \RuntimeException('Gallery directory not specified', 400);
            }

            $galleryDir = $this->sanitizePath($input['gallery_dir']);
            $fullPath = $this->getFullPath($galleryDir);

            if (!is_dir($fullPath)) {
                throw new \RuntimeException('Gallery directory not found', 404);
            }

            $result = $this->deleteDirectoryContents($fullPath);

            if (!empty($result['errors'])) {
                return $this->initJsonResponse([
                    'success' => false,
                    'deleted_count' => $result['deleted_count'],
                    'errors' => $result['errors']
                ], 207);
            }

            return $this->initJsonResponse([
                'success' => true,
                'deleted_count' => $result['deleted_count'],
                'message' => 'Gallery deleted successfully'
            ], 200);

        } catch (\RuntimeException $e) {
            return $this->initJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    private function sanitizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        
        $path = trim($path, '/');
        
        $parts = explode('/', $path);
        $parts = array_filter($parts, function($part) {
            return $part !== '.' && $part !== '..';
        });
        
        return implode('/', $parts);
    }

    private function getFullPath(string $relativePath): string
    {
        return $this->baseDir . '/' . $relativePath;
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \RuntimeException('Failed to create directory', 500);
            }
        }
    }

    private function isDirectoryEmpty(string $path): bool
    {
        return count(scandir($path)) <= 2;
    }

    private function scanDirectoryForImages(string $path): array
    {
        $images = [];
        $iterator = new \DirectoryIterator($path);

        foreach ($iterator as $file) {
            if ($file->isDir()) continue;

            try {
                $mimeType = mime_content_type($file->getPathname());
                if (in_array($mimeType, $this->allowedMimeTypes)) {
                    $images[] = $file->getFilename();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $images;
    }

    private function deleteDirectoryContents(string $path): array
    {
        $result = ['deleted_count' => 0, 'errors' => []];
        $iterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            try {
                if ($file->isDir()) {
                    if (!rmdir($file->getPathname())) {
                        $result['errors'][] = "Failed to remove directory: {$file->getFilename()}";
                    }
                } else {
                    if (unlink($file->getPathname())) {
                        $result['deleted_count']++;
                    } else {
                        $result['errors'][] = "Failed to delete file: {$file->getFilename()}";
                    }
                }
            } catch (\Exception $e) {
                $result['errors'][] = $e->getMessage();
            }
        }

        if (!rmdir($path)) {
            $result['errors'][] = "Failed to remove main directory";
        }

        return $result;
    }

    private function getJsonInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON input', 400);
        }
        return $input;
    }

    private function checkAuth(): bool
    {
        $token = ($_POST['api_token'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : null));
        
        if (!$token) {
            return false;
        }

        $db = $this->dbManager->getConnection();
        $user = $db->query("
            SELECT v.id, v.status, v.api_token
            FROM vendors AS v
            WHERE v.api_token = '{$db->escape($token)}'
        ")[0] ?? null;

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