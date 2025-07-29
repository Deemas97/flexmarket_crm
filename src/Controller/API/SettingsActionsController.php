<?php
namespace App\Controller;

include_once '../src/Kernel.php';
include_once '../src/Core/Config/DotEnv.php';
include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Service/AuthService.php';

use App\Kernel;
use App\Core\DotEnv;
use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponseInterface;
use App\Service\AuthService;
use RuntimeException;

class SettingsActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth
    ) {
        $this->checkAuth();
    }

    public function apiUpdateSettings(): ControllerResponseInterface
    {
        try {
            $settings = [
                'APP_NAME' => $_POST['platform_name'] ?? '',
                'ADMIN_EMAIL' => $_POST['admin_email'] ?? '',
                'TIMEZONE' => $_POST['timezone'] ?? 'UTC',
                'MAINTENANCE_MODE' => isset($_POST['maintenance_mode']) ? '1' : '0',
                'ITEMS_PER_PAGE' => $_POST['items_per_page'] ?? '20',
                'DEFAULT_CURRENCY' => $_POST['default_currency'] ?? 'RUB'
            ];

            DotEnv::update(Kernel::getRootDir() . '/configs/.env', $settings);

            return $this->initJsonResponse([
                'success' => true,
                'message' => 'Настройки успешно обновлены'
            ]);
        } catch (RuntimeException $e) {
            error_log($e->getMessage());

            return $this->initJsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiClearCache(): ControllerResponseInterface
    {
        try {
            $cacheDir = Kernel::getRootDir() . '/cache/templates';
            $this->clearDirectory($cacheDir);

            return $this->initJsonResponse([
                'success' => true,
                'message' => 'Кэш успешно очищен'
            ]);
        } catch (RuntimeException $e) {
            return $this->initJsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function clearDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            throw new RuntimeException("Директория {$dir} не существует");
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->clearDirectory($path);
                rmdir($path);
            } else {
                if (!unlink($path)) {
                    throw new RuntimeException("Не удалось удалить файл {$path}");
                }
            }
        }
    }

    private function checkAuth(): void
    {
        if (!$this->auth->isAuthenticated() || !($this->auth->getUserRole() === 'admin')) {
            $this->redirect('/login');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}