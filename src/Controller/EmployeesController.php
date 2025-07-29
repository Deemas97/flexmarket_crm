<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerRendering.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Core/Service/Renderer.php';
include_once '../src/Service/AuthService.php';
include_once '../src/Service/DBConnectionManager.php';

use App\Core\Controller\ControllerRendering;
use App\Core\Controller\ControllerResponseInterface;
use App\Core\Service\Renderer;
use App\Service\AuthService;
use App\Service\DBConnectionManager;

class EmployeesController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer,
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {
        parent::__construct($renderer);
    }

    public function index(): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Сотрудники',
            'company_name' => 'ФлексМаркет'
        ];

        $userSessionData = $this->auth->getUser();

        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $dbConnection = $this->dbManager->getConnection();
        
        $sqlGetEmployees = "SELECT * FROM employees LIMIT 25";
        $result = $dbConnection->query($sqlGetEmployees);

        if (($result === false) || empty($result)) {
            $data['employees'] = [];
        } else {
            foreach ($result as $user) {
                unset($user['password_hash'], $user['salt'], $user['remember_token']);
                $data['employees'][] = $user;
            }
        }

        return $this->render('pages/employees.html.php', $data);
    }

    public function employee(int $id): ControllerResponseInterface
    {
        $this->checkAuth();
        $this->checkAdminAccess();
        
        $data = [
            'title' => 'Сотрудники',
            'company_name' => 'ФлексМаркет'
        ];

        $userSessionData = $this->auth->getUser();

        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $dbConnection = $this->dbManager->getConnection();
        
        $sqlGetEmployee = "SELECT * FROM employees WHERE id = {$id} LIMIT 1";
        $result = $dbConnection->query($sqlGetEmployee);
        $employee = $result[0];

        if (empty($employee)) {
            $this->redirect('/error_404');
        }

        $data['employee'] = $employee;

        return $this->render('pages/admin_employee_edit.html.php', $data);
    }

    protected function checkAuth()
    {
        $this->auth->setUserTable('employees');

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
