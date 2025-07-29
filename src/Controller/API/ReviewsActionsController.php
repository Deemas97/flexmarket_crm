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

class ReviewsActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        $this->checkAdminAccess();
    }

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Проверка существования отзыва
        $review = $db->query("SELECT id FROM reviews WHERE id = {$id} LIMIT 1");
        if (empty($review)) {
            return $this->initJsonResponse(['error' => 'Отзыв не найден'], 404);
        }

        // Валидация рейтинга
        if (!isset($data['rating']) || !is_numeric($data['rating']) || 
            $data['rating'] < 0 || $data['rating'] > 5) {
            return $this->initJsonResponse(['error' => 'Рейтинг должен быть числом от 0 до 5'], 400);
        }

        // Обновление данных
        $updateData = [
            'rating' => (int)$data['rating'],
            'comment' => $data['comment'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = " . ($value === null ? "NULL" : "'{$db->escape($value)}'");
        }

        $result = $db->query("UPDATE reviews SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при обновлении отзыва'], 500);
        }

        $this->redirect('/reviews');
        return $this->initJsonResponse();
    }

    public function apiDelete(int $id): ControllerResponseInterface
    {
        $db = $this->dbManager->getConnection();

        // Проверка существования отзыва
        $review = $db->query("SELECT id FROM reviews WHERE id = {$id} LIMIT 1");
        if (empty($review)) {
            return $this->initJsonResponse(['error' => 'Отзыв не найден'], 404);
        }

        $result = $db->query("DELETE FROM reviews WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при удалении отзыва'], 500);
        }

        return $this->initJsonResponse(['success' => true]);
    }

    private function checkAdminAccess(): void
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $user = $this->auth->getUser();
        if ($user['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}