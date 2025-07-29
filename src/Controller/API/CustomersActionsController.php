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

class CustomersActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
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
            || empty($data['role_id'])
            || empty($data['address'])) {
            return $this->initJsonResponse();
        }

        if ($data['password'] !== $data['password_confirm']) {
            return $this->initJsonResponse();
        }

        // Проверка существования email
        $emailCheck = $db->query("SELECT id FROM customers WHERE email = '{$db->escape($data['email'])}'");
        if (!empty($emailCheck)) {
            return $this->initJsonResponse();
        }

        // Создание пользователя
        $salt = bin2hex(random_bytes(16));
        $passwordHash = password_hash($data['password'] . $salt, PASSWORD_DEFAULT);

        $apiToken = bin2hex(random_bytes(32));

        $insertData = [
            'f' => $data['f'],
            'i' => $data['i'],
            'o' => $data['o'] ?? null,
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'address' => $data['address'],
            'status' => 'premoderation',
            'password_hash' => $passwordHash,
            'salt' => $salt,
            'api_token' => $apiToken,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $escapedData = array_map([$db, 'escape'], $insertData);
        $columns = implode(', ', array_keys($escapedData));
        $values = "'" . implode("', '", array_values($escapedData)) . "'";

        $result = $db->query("INSERT INTO customers ($columns) VALUES ($values)");

        if ($result === false) {
            return $this->initJsonResponse();
        }

        $this->redirect('/customers');

        return $this->initJsonResponse();
    }

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Валидация
        if (empty($data['f']) || empty($data['i'])) {
            return $this->initJsonResponse();
        }

        // Проверка существования пользователя
        $user = $db->query("SELECT id FROM customers WHERE id = {$id} LIMIT 1");
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
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = '{$db->escape($value)}'";
        }

        $result = $db->query("UPDATE customers SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse();
        }

        $this->redirect('/customers');

        return $this->initJsonResponse();
    }

    public function apiChangePassword(int $id): ControllerResponseInterface
    {
        $data = $_POST;
        $db = $this->dbManager->getConnection();

        if (empty($data['new_password']) || empty($data['confirm_password'])) {
            return $this->initJsonResponse();
        }

        if ($data['new_password'] !== $data['confirm_password']) {
            return $this->initJsonResponse();
        }

        // Получаем соль пользователя
        $user = $db->query("SELECT salt FROM customers WHERE id = {$id} LIMIT 1");
        if (empty($user)) {
            return $this->initJsonResponse();
        }

        $newPasswordHash = password_hash($data['new_password'] . $user[0]['salt'], PASSWORD_DEFAULT);

        $result = $db->query("UPDATE customers SET password_hash = '{$db->escape($newPasswordHash)}' WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse();
        }

        $this->redirect('/customer/' . $id . '/edit');

        return $this->initJsonResponse();
    }

    public function apiDelete(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $db = $this->dbManager->getConnection();

        $result = $db->query("DELETE FROM customers WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse();
        }

        return $this->initJsonResponse();
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