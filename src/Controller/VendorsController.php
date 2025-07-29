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

class VendorsController extends ControllerRendering
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
            'title' => 'Продавцы',
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
        
        $sqlGetVendors = "
            SELECT *, v.id, cm.id AS company_id, cm.name AS company_name
            FROM vendors AS v
            LEFT JOIN companies AS cm ON v.company_id = cm.id
            LIMIT 25
        ";

        $result = $dbConnection->query($sqlGetVendors);

        if (($result === false) || empty($result)) {
            $data['vendors'] = [];
        } else {
            foreach ($result as $vendor) {
                unset($vendor['password_hash'], $vendor['salt'], $vendor['remember_token'], $vendor['api_token']);
                $data['vendors'][] = $vendor;
            }
        }

        $sqlGetCompanies = "SELECT * FROM companies";
        $result = $dbConnection->query($sqlGetCompanies);

        $data['companies'] = $result;

        return $this->render('pages/vendors.html.php', $data);
    }

    public function vendor(int $id): ControllerResponseInterface
    {
        $this->checkAuth();
        $this->checkAdminAccess();

        $data = [
            'title' => 'Продавцы',
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
        
        $sqlGetVendor = "SELECT * FROM vendors WHERE id = {$id} LIMIT 1";
        $result = $dbConnection->query($sqlGetVendor);
        $vendor = $result[0];

        if (empty($vendor)) {
            $this->redirect('/error_404');
        }

        $data['vendor'] = $vendor;

        $sqlGetCompanies = "SELECT * FROM companies";
        $result = $dbConnection->query($sqlGetCompanies);

        $data['companies'] = $result;

        return $this->render('pages/admin_vendor_edit.html.php', $data);
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