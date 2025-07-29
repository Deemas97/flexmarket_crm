<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Service/AuthService.php';
include_once '../src/Service/DBConnectionManager.php';
include_once '../src/Service/FileUploader.php';

use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponseInterface;
use App\Service\AuthService;
use App\Service\DBConnectionManager;
use App\Service\FileUploader;

class UserActionsController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager,
        private FileUploader $fileUploader
    )
    {
        $this->fileUploader->setAllowedMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->fileUploader->setMaxFileSize(5 * 1024 * 1024);
    }

    public function apiChangePassword(): ControllerResponseInterface
    {
        $this->checkAuth();

        $this->auth->setUserTable('employees');

        if ($this->auth->changePassword($_POST)) {
            $this->redirect('/profile/edit');
        }

        return $this->initJsonResponse();
    }

    public function apiUpdate(): ControllerResponseInterface
    {    
        $this->checkAuth();
    
        $userId = $this->auth->getUserId();
        $db = $this->dbManager->getConnection();
    
        $phone = $this->splitPhoneNumber(htmlspecialchars($_POST['phone']));

        // Prepare update data
        $updateData = [
            'f' => htmlspecialchars($_POST['f']) ?? '',
            'i' => htmlspecialchars($_POST['i']) ?? '',
            'o' => htmlspecialchars($_POST['o']) ?? '',
            'phone_code'   => $phone['country_code'] ?? '',
            'phone_number' => $phone['phone_number'] ?? '',
            'updated_at'   => date('Y-m-d H:i:s')
        ];
    
        // Basic validation
        if (empty($updateData['f']) || empty($updateData['i'])) {
            return $this->initJsonResponse();
        }
    
        // Handle image upload
        if (!empty($_FILES['avatar']['name'])) {
            try {
                $uploadDir = 'uploads/avatars/';
                $updateData['avatar'] = $this->fileUploader->upload($_FILES['avatar'], $uploadDir);
            } catch (\RuntimeException $e) {
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    
        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "{$key} = '{$db->escape($value)}'";
        }
    
        $updateSql = "UPDATE employees SET " . implode(', ', $updateParts) . " WHERE id = {$userId}";
        $result = $db->query($updateSql);
    
        if ($result === false) {
            return $this->initJsonResponse();
        }
    
        $userSession = $this->auth->getUser();
        if ($userSession) {
            $userName = $updateData['i'] . ' ' . $updateData['f'];
            $this->auth->setUserName($userName);

            if (isset($updateData['avatar']) && !empty($updateData['avatar'])) {
                $this->auth->setUserAvatar($updateData['avatar']);
            }
        }

        $this->redirect('/profile');
    
        return $this->initJsonResponse();
    }

    private function splitPhoneNumber($phoneNumber) {
        $digitsOnly = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (strlen($digitsOnly) < 11) {
            return ['error' => 'Номер телефона должен содержать как минимум 11 цифр (код страны + 10 цифр номера)'];
        }
        
        $countryCode = substr($digitsOnly, 0, -10);
        $phoneNumber = substr($digitsOnly, -10);
        
        return [
            'country_code' => $countryCode,
            'phone_number' => $phoneNumber
        ];
    }

    private function checkAuth()
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

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}