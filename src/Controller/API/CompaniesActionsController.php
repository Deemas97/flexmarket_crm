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

class CompaniesActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {}

    public function apiCreate(): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Валидация обязательных полей
        if (empty($data['name']) || empty($data['full_name']) || 
            empty($data['INN']) || empty($data['OGRN']) || 
            empty($data['address'])) {
            return $this->initJsonResponse(['error' => 'Все обязательные поля должны быть заполнены']);
        }

        // Проверка уникальности полей
        $uniqueCheck = $db->query(
            "SELECT id FROM companies 
            WHERE name = '{$db->escape($data['name'])}' 
            OR full_name = '{$db->escape($data['full_name'])}' 
            OR INN = '{$db->escape($data['INN'])}' 
            OR OGRN = '{$db->escape($data['OGRN'])}' 
            LIMIT 1"
        );

        if (!empty($uniqueCheck)) {
            return $this->initJsonResponse(['error' => 'Компания с такими данными уже существует']);
        }

        $dateCurrent = date('Y-m-d H:i:s');

        $insertData = [
            'name' => $data['name'],
            'full_name' => $data['full_name'],
            'INN' => $data['INN'],
            'OGRN' => $data['OGRN'],
            'address' => $data['address'],
            'description' => $data['description'] ?? null,
            'created_at' => $dateCurrent,
            'updated_at' => $dateCurrent
        ];

        $escapedData = array_map([$db, 'escape'], $insertData);
        $columns = implode(', ', array_keys($escapedData));
        $values = "'" . implode("', '", array_values($escapedData)) . "'";

        $result = $db->query("INSERT INTO companies ($columns) VALUES ($values)");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при создании компании']);
        }

        $this->redirect('/companies');
        return $this->initJsonResponse();
    }

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Валидация обязательных полей
        if (empty($data['name']) || empty($data['full_name']) || 
            empty($data['INN']) || empty($data['OGRN']) || 
            empty($data['address'])) {
            return $this->initJsonResponse(['error' => 'Все обязательные поля должны быть заполнены']);
        }

        // Проверка уникальности полей для других компаний
        $uniqueCheck = $db->query(
            "SELECT id FROM companies 
            WHERE id != {$id} 
            AND (name = '{$db->escape($data['name'])}' 
            OR full_name = '{$db->escape($data['full_name'])}' 
            OR INN = '{$db->escape($data['INN'])}' 
            OR OGRN = '{$db->escape($data['OGRN'])}') 
            LIMIT 1"
        );

        if (!empty($uniqueCheck)) {
            return $this->initJsonResponse(['error' => 'Компания с такими данными уже существует']);
        }

        // Обновление данных
        $updateData = [
            'name' => $data['name'],
            'full_name' => $data['full_name'],
            'INN' => $data['INN'],
            'OGRN' => $data['OGRN'],
            'address' => $data['address'],
            'description' => $data['description'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = '{$db->escape($value)}'";
        }

        $result = $db->query("UPDATE companies SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при обновлении компании']);
        }

        $this->redirect('/companies');
        return $this->initJsonResponse();
    }

    public function apiDelete(int $id): ControllerResponseInterface
    {
        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }

        $db = $this->dbManager->getConnection();
        $result = $db->query("DELETE FROM companies WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при удалении компании']);
        }

        return $this->initJsonResponse(['success' => true]);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}