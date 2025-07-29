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

class VendorsActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        $this->checkAuth();
        $this->checkAdminAccess();
    }

    public function apiCreate(): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Валидация
        if (empty($data['f'])
            || empty($data['i'])
            || empty($data['email'])
            || empty($data['password'])
            || empty($data['company_id'])
            || empty($data['role_id'])) {
            return $this->initJsonResponse();
        }

        if ($data['password'] !== $data['password_confirm']) {
            return $this->initJsonResponse();
        }

        // Проверка существования email
        $emailCheck = $db->query("SELECT id FROM vendors WHERE email = '{$db->escape($data['email'])}'");
        if (!empty($emailCheck)) {
            return $this->initJsonResponse();
        }

        // Создание пользователя
        $salt = bin2hex(random_bytes(16));
        $passwordHash = password_hash($data['password'] . $salt, PASSWORD_DEFAULT);

        $apiToken = bin2hex(random_bytes(16));

        $insertData = [
            'f' => $data['f'],
            'i' => $data['i'],
            'o' => $data['o'] ?? null,
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'status' => 'premoderation',
            'password_hash' => $passwordHash,
            'salt' => $salt,
            'api_token' => $apiToken,
            'company_id' => $data['company_id'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $escapedData = array_map([$db, 'escape'], $insertData);
        $columns = implode(', ', array_keys($escapedData));
        $values = "'" . implode("', '", array_values($escapedData)) . "'";

        $result = $db->query("INSERT INTO vendors ($columns) VALUES ($values)");

        if ($result === false) {
            return $this->initJsonResponse();
        }

        $this->redirect('/vendors');

        return $this->initJsonResponse();
    }

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Валидация
        if (empty($data['f']) || empty($data['i'])|| empty($data['company_id'])) {
            return $this->initJsonResponse();
        }

        // Проверка существования пользователя
        $user = $db->query("SELECT id FROM vendors WHERE id = {$id} LIMIT 1");
        if (empty($user)) {
            return $this->initJsonResponse();
        }

        // Обновление данных
        $updateData = [
            'f' => $data['f'],
            'i' => $data['i'],
            'o' => $data['o'] ?? null,
            'role_id' => $data['role_id'],
            'api_token' => $data['api_token'],
            'status' => $data['status'],
            'company_id' => $data['company_id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = '{$db->escape($value)}'";
        }

        $result = $db->query("UPDATE vendors SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse();
        }

        $this->redirect('/vendors');

        return $this->initJsonResponse();
    }

    public function apiChangePassword(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        if (empty($data['new_password']) || empty($data['confirm_password'])) {
            return $this->initJsonResponse();
        }

        if ($data['new_password'] !== $data['confirm_password']) {
            return $this->initJsonResponse();
        }

        // Получаем соль пользователя
        $user = $db->query("SELECT salt FROM vendors WHERE id = {$id} LIMIT 1");
        if (empty($user)) {
            return $this->initJsonResponse();
        }

        $newPasswordHash = password_hash($data['new_password'] . $user[0]['salt'], PASSWORD_DEFAULT);

        $result = $db->query("UPDATE vendors SET password_hash = '{$db->escape($newPasswordHash)}' WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse();
        }

        $this->redirect('/vendor/' . $id . '/edit');

        return $this->initJsonResponse();
    }

    public function apiDelete(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $db = $this->dbManager->getConnection();

        $result = $db->query("DELETE FROM vendors WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse();
        }

        return $this->initJsonResponse();
    }

    protected function checkAuth()
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $this->checkStatus();
    }

    private function checkStatus()
    {
        $userStatus = $this->auth->getUserStatus();
        switch ($userStatus) {
            case 'premoderation':
                $this->redirect('/premoderation_info');
            case 'banned':
                $this->redirect('/ban_info');
            case null:
            case "":
                $this->redirect('/crash');
        }
    }

    private function checkAdminAccess()
    {
        $userRole = $this->auth->getUserRole();
        if ($userRole !== 'admin') {
            $this->redirect('/access_denied');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}