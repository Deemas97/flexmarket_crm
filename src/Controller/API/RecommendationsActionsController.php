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

class RecommendationsActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {}

    public function apiUpdateGlobal(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (empty($data)) {
            return $this->initJsonResponse(['error' => 'No data provided'], 400);
        }

        $db = $this->dbManager->getConnection();

        // Проверка существования рекомендации
        $recommendation = $db->query("SELECT id FROM recommendations WHERE id = {$id} LIMIT 1");

        if (empty($recommendation)) {
            return $this->initJsonResponse(['error' => 'Рекомендация не найдена'], 404);
        }

        // Валидация score
        if (!isset($data['recommendation_score']) || !is_numeric($data['recommendation_score']) || $data['recommendation_score'] < 0) {
            return $this->initJsonResponse(['error' => 'Рейтинг должен быть положительным числом'], 400);
        }

        // Обновление данных
        $updateData = [
            'recommendation_score' => (float)$data['recommendation_score'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = " . (is_numeric($value) ? $value : "'{$db->escape($value)}'");
        }

        $result = $db->query("UPDATE recommendations SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при обновлении рекомендации'], 500);
        }

        return $this->initJsonResponse(['success' => true, 'message' => 'Рекомендация успешно обновлена'], 200);
    }

    public function apiUpdatePersonal(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Проверка существования рекомендации
        $recommendation = $db->query("SELECT id FROM personal_recommendations WHERE id = {$id} LIMIT 1");
        if (empty($recommendation)) {
            return $this->initJsonResponse(['error' => 'Рекомендация не найдена'], 404);
        }

        // Валидация score
        if (!isset($data['recommendation_score']) || !is_numeric($data['recommendation_score']) || $data['recommendation_score'] < 0) {
            return $this->initJsonResponse(['error' => 'Рейтинг должен быть положительным числом'], 400);
        }

        // Обновление данных
        $updateData = [
            'recommendation_score' => (float)$data['recommendation_score'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = '{$db->escape($value)}'";
        }

        $result = $db->query("UPDATE personal_recommendations SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при обновлении рекомендации'], 500);
        }

        return $this->initJsonResponse(['success' => true, 'message' => 'Рекомендация успешно обновлена'], 200);
    }

    public function apiDeleteGlobal(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $db = $this->dbManager->getConnection();

        // Проверка существования рекомендации
        $recommendation = $db->query("SELECT id FROM recommendations WHERE id = {$id} LIMIT 1");
        if (empty($recommendation)) {
            return $this->initJsonResponse(['error' => 'Рекомендация не найдена'], 404);
        }

        $result = $db->query("DELETE FROM recommendations WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при удалении рекомендации'], 500);
        }

        return $this->initJsonResponse(['success' => true, 'message' => 'Рекомендация успешно удалена'], 200);
    }

    public function apiDeletePersonal(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $db = $this->dbManager->getConnection();

        // Проверка существования рекомендации
        $recommendation = $db->query("SELECT id FROM personal_recommendations WHERE id = {$id} LIMIT 1");
        if (empty($recommendation)) {
            return $this->initJsonResponse(['error' => 'Рекомендация не найдена'], 404);
        }

        $result = $db->query("DELETE FROM personal_recommendations WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при удалении рекомендации'], 500);
        }

        return $this->initJsonResponse(['success' => true, 'message' => 'Рекомендация успешно удалена'], 200);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}