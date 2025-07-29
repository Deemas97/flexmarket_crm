<?php
namespace App\Service;

include_once '../src/Core/ServiceInterface.php';

use App\Core\ServiceInterface;

class FileUploader implements ServiceInterface
{
    private array $allowedMimeTypes = [];
    private int $maxFileSize = 10 * 1024 * 1024;

    public function __construct() {}

    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    public function setAllowedMimeTypes(array $mimeTypes): void
    {
        $this->allowedMimeTypes = $mimeTypes;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    public function setMaxFileSize(int $maxFileSize): void
    {
        $this->maxFileSize = $maxFileSize;
    }

    public function resizeImage(string $filePath, int $maxWidth, int $maxHeight): bool
    {
        $imageInfo = getimagesize($filePath);
        $mimeType = $imageInfo['mime'];
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filePath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }
        
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($newImage, $filePath, 90);
                break;
            case 'image/png':
                imagepng($newImage, $filePath, 9);
                break;
            case 'image/webp':
                imagewebp($newImage, $filePath, 90);
                break;
        }
        
        imagedestroy($image);
        imagedestroy($newImage);
        
        return true;
    }

    public function upload(array $file, string $targetDir, array $allowedTypes = []): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Ошибка загрузки файла: ' . $this->getUploadError($file['error']));
        }

        $mimeType = $this->getFileMimeType($file['tmp_name']);
        $allowedTypes = empty($allowedTypes) ? $this->allowedMimeTypes : $allowedTypes;
        
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            throw new \RuntimeException('Недопустимый тип файла: ' . $mimeType);
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new \RuntimeException('Файл слишком большой. Максимальный размер: ' . 
                round($this->maxFileSize / 1024 / 1024, 2) . 'MB');
        }

        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new \RuntimeException('Не удалось создать директорию: ' . $targetDir);
            }
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . $extension;
        $targetPath = rtrim($targetDir, '/') . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Не удалось сохранить файл');
        }

        if (str_starts_with($mimeType, 'image/')) {
            $this->resizeImage($targetPath, 1200, 1200);
        }

        return $filename;
    }

    public function uploadMultiple(array $files, string $targetDir, array $allowedTypes = []): array
    {
        $uploadedFiles = [];
        
        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            $file = [
                'name' => $name,
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];

            try {
                $uploadedFiles[] = $this->upload($file, $targetDir, $allowedTypes);
            } catch (\RuntimeException $e) {
                error_log($e->getMessage());
            }
        }

        return $uploadedFiles;
    }

    private function getFileMimeType(string $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return $mimeType;
    }

    private function getUploadError(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Файл превышает размер, указанный в upload_max_filesize в php.ini',
            UPLOAD_ERR_FORM_SIZE => 'Файл превышает размер, указанный в MAX_FILE_SIZE в HTML-форме',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен только частично',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
            UPLOAD_ERR_EXTENSION => 'Загрузка файла остановлена расширением PHP',
        ];

        return $errors[$errorCode] ?? 'Неизвестная ошибка';
    }
}