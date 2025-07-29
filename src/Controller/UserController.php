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

class UserController extends ControllerRendering
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
            'title' => 'Профиль',
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

        $userId = $this->auth->getUserId();
        
        $sqlGetEmployees = "SELECT * FROM employees WHERE id = {$userId} LIMIT 1";
        $result = $dbConnection->query($sqlGetEmployees);

        $userData = $result[0];
        $data['user_data'] = $userData;

        return $this->render('pages/profile.html.php', $data);
    }

    public function edit(): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Профиль',
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

        $userId = $this->auth->getUserId();
        
        $sqlGetEmployees = "SELECT * FROM employees WHERE id = {$userId} LIMIT 1";
        $result = $dbConnection->query($sqlGetEmployees);

        $userData = $result[0];
        $data['user_data'] = $userData;

        return $this->render('pages/profile_edit.html.php', $data);
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

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
