<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Service/AuthService.php';

use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponseInterface;
use App\Service\AuthService;
use Exception;

class UserAuthController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth
    )
    {}

    public function apiAuth(): ControllerResponseInterface
    {
        if (!isset($_POST['email'])) {
            return $this->initJsonResponse(['error', 'Email is required']);
        }

        $login    = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);

        if ($login !== $_POST['email'] || $password !== $_POST['password']) {
            $response = $this->initJsonResponse(['error', 'Invalid input data']);
            return $response;
        }

        $remember = ($_POST['remember'] === true) ?? false;

        $this->auth->setUserTable('employees');

        if ($this->auth->login($login, $password, $remember)) {
            switch ($this->auth->getUserStatus()) {
                case 'active':
                    $this->redirect('/');
                case 'premoderation':
                    $this->redirect('/premoderation_info');
                case 'banned':
                    $this->redirect('/ban_info');
                default:
                    $this->redirect('/crash');
            }
        } else {
            $this->redirect('/login');
        }

        return $this->initJsonResponse();
    }

    public function apiRegister(): ControllerResponseInterface
    {        
        $requiredFields = ['email', 'password', 'f', 'i', 'password_confirmation', 'agree_terms'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field])) {
                return $this->initJsonResponse(['error', "Missing required field: {$field}"]);
            }
        }

        $this->auth->setUserTable('employees');
        
        $additionalData = [
            'f' => htmlspecialchars($_POST['f']), // Фамилия
            'i' => htmlspecialchars($_POST['i']), // Имя
            'role_id' => 'manager',
            'status' => 'premoderation'
        ];
    
        if (isset($_POST['o'])) {
            $additionalData['o'] = htmlspecialchars($_POST['o']); // Отчество
        }

        $success = $this->auth->register(
            htmlspecialchars($_POST['email']),
            htmlspecialchars($_POST['password']),
            $additionalData
        );

        if ($success === false) {
            return $this->initJsonResponse(['error', 'Registration failed. Email may already be in use.']);
        }

        $this->redirect('/premoderation_info');

        return $this->initJsonResponse();
    }

    public function apiLogout(): ControllerResponseInterface
    {
        $this->auth->setUserTable('employees');

        $this->auth->logout();
        $this->redirect('/login');

        return $this->initJsonResponse();
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}