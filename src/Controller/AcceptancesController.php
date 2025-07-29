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

class AcceptancesController extends ControllerRendering
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
            'title' => 'Приёмка товаров',
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

        // Получаем товары с информацией о категориях
        $result = $dbConnection->query("
            SELECT a.*, c.name as company_name, p.name as product_name
            FROM acceptances a
            LEFT JOIN companies c ON a.company_id = c.id
            LEFT JOIN products p ON a.product_id = p.id
            ORDER BY a.created_at DESC
            LIMIT 25
        ");

        if (($result === false) || empty($result)) {
            $data['acceptances'] = [];
        } else {
            $data['acceptances'] = $result;
        }

        $sqlGetCompanies = "SELECT * FROM companies";
        $result = $dbConnection->query($sqlGetCompanies);

        $data['companies'] = $result;

        $sqlGetCompanies = "SELECT * FROM products";
        $result = $dbConnection->query($sqlGetCompanies);

        $data['products'] = $result;

        return $this->render('pages/acceptances.html.php', $data);
    }

    public function acceptance(int $id): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Приёмка товаров',
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
        
        $sqlGetAcceptance = "SELECT * FROM acceptances WHERE id = {$id} LIMIT 1";
        $result = $dbConnection->query($sqlGetAcceptance);
        $acceptance = $result[0];

        if (empty($acceptance)) {
            $this->redirect('/error_404');
        }

        $data['acceptance'] = $acceptance;

        $sqlGetCompanies = "SELECT * FROM companies";
        $result = $dbConnection->query($sqlGetCompanies);
        $data['companies'] = $result;

        $sqlGetProducts = "SELECT * FROM products";
        $result = $dbConnection->query($sqlGetProducts);
        $data['products'] = $result;

        return $this->render('pages/acceptance_edit.html.php', $data);
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