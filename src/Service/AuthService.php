<?php

namespace App\Service;

include_once '../src/Core/ServiceInterface.php';
include_once '../src/Service/MySQLConnector.php';
include_once '../src/Service/DBConnectionManager.php';

use App\Core\ServiceInterface;

class AuthService implements ServiceInterface
{
    private string $userTable = '';
    private string $sessionKey = 'auth_token';
    private string $rememberCookieName = 'remember_token';
    private int $rememberExpire = 30 * 24 * 60 * 60;
    private bool $sessionStarted = false;

    public function __construct(
        private DBConnectionManager $dbManager
    )
    {}

    public function getUser(): ?array
    {
        return $this->isAuthenticated() ? $_SESSION[$this->sessionKey] : null;
    }

    public function getUserId(): ?int
    {
        return $this->getUser()['id'] ?? null;
    }

    public function getUserStatus(): ?string
    {
        return $this->getUser()['status'] ?? null;
    }

    public function getUserRole(): ?string
    {
        return $this->getUser()['role'] ?? null;
    }

    public function getUserEmail(): ?string
    {
        return $this->getUser()['email'] ?? null;
    }

    public function setUserName(string $name): void
    {
        if (isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey]['name'] = $name;
        }
    }

    public function setUserEmail(string $email): void
    {
        if (isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey]['email'] = $email;
        }
    }

    public function setUserAvatar(string $imagePath): void
    {
        if (isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey]['avatar'] = $imagePath;
        }
    }

    public function setUserTable(string $tableName): void
    {
        $this->userTable = $tableName;
    }

    public function login(string $login, string $password, bool $remember = false): bool
    {
        $db = $this->dbManager->getConnection();
        $safeLogin = $db->escape($login);
        
        $sql = "SELECT id, role_id, f, i, email, password_hash, salt, status, avatar FROM {$this->userTable} 
                WHERE email = '{$safeLogin}' LIMIT 1";
        $result = $db->query($sql);
    
        if (empty($result)) return false;
    
        $user = $result[0];
    
        // Хэшируем введенный пароль с солью из БД
        $hashedPassword = hash('sha256', $password . $user['salt']);
    
        if (!password_verify($hashedPassword, $user['password_hash'])) {
            return false;
        }

        $this->initSession();
        $_SESSION[$this->sessionKey] = [
            'id' => $user['id'],
            'role' => $user['role_id'],
            'name' => $user['i'] . ' ' . $user['f'],
            'email' => $user['email'],
            'status' => $user['status'],
            'avatar' => $user['avatar']
        ];

        // Объединяем обновление времени входа и установку remember token в одном запросе
        $currentTime = date('Y-m-d H:i:s');
        $updateSql = "UPDATE {$this->userTable} SET logined_at = '{$currentTime}'";
        
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $updateSql .= ", remember_token = '" . $db->escape($token) . "'";
            setcookie(
                $this->rememberCookieName,
                $token,
                time() + $this->rememberExpire,
                '/',
                '',
                false,
                true
            );
        }
        
        $updateSql .= " WHERE id = {$user['id']}";
        $db->query($updateSql);

        return true;
    }

    public function logout(): void
    {
        $this->initSession();

        if (isset($_COOKIE[$this->rememberCookieName])) {
            $token = $_COOKIE[$this->rememberCookieName];
            $this->clearRememberToken($token);
            setcookie($this->rememberCookieName, '', time() - 3600, '/');
        }

        unset($_SESSION[$this->sessionKey]);
        session_destroy();
        $this->sessionStarted = false;
    }

    public function isAuthenticated(): bool
    {
        if (!$this->sessionStarted) {
            $this->initSession();
        }

        if (isset($_SESSION[$this->sessionKey])) {
            return true;
        }

        if (isset($_COOKIE[$this->rememberCookieName])) {
            return $this->loginByRememberToken($_COOKIE[$this->rememberCookieName]);
        }

        return false;
    }

    public function register(string $login, string $password, array $additionalData = []): bool
    {
        $db = $this->dbManager->getConnection();
        
        $safeLogin = $db->escape($login);
        $sql = "SELECT id FROM {$this->userTable} WHERE email = '{$safeLogin}' LIMIT 1";
        $result = $db->query($sql);
    
        if ($result !== false && !empty($result)) {
            return false;
        }
    
        $dateTimeCurrent = date('Y-m-d H:i:s');
        
        // Генерируем уникальную соль для каждого пользователя
        $salt = bin2hex(random_bytes(16)); // 32 символа
        
        // Хэшируем пароль с солью
        $passwordHash = password_hash(hash('sha256', $password . $salt), PASSWORD_DEFAULT);
    
        $data = [
            'email' => $login,
            'password_hash' => $passwordHash,
            'salt' => $salt,
            'created_at' => $dateTimeCurrent,
            'updated_at' => $dateTimeCurrent
        ];
    
        foreach ($additionalData as $key => $value) {
            $data[$key] = $value;
        }
    
        $escapedData = [];
        foreach ($data as $key => $value) {
            $escapedData[$key] = $db->escape($value);
        }
    
        $columns = implode(', ', array_keys($escapedData));
        $values = "'" . implode("', '", array_values($escapedData)) . "'";
        $sql = "INSERT INTO {$this->userTable} ({$columns}) VALUES ({$values})";
    
        $result = $db->query($sql);
    
        return $result !== false;
    }

    public function changePassword(array $data): bool
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return false;
        }

        $db = $this->dbManager->getConnection();

        // Получаем текущий хэш и соль из БД
        $sql = "SELECT password_hash, salt FROM {$this->userTable} WHERE id = {$userId} LIMIT 1";
        $result = $db->query($sql);

        if (empty($result)) {
            return false;
        }

        $user = $result[0];
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        // Валидация
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return false;
        }

        if ($newPassword !== $confirmPassword) {
            return false;
        }

        if (strlen($newPassword) < 8) {
            return false;
        }

        // Хэшируем текущий пароль с солью из БД
        $hashedCurrentPassword = hash('sha256', $currentPassword . $user['salt']);

        // Проверяем текущий пароль
        if (!password_verify($hashedCurrentPassword, $user['password_hash'])) {
            return false;
        }

        // Хэшируем новый пароль
        $hashedNewPassword = hash('sha256', $newPassword . $user['salt']);
        $newPasswordHash = password_hash($hashedNewPassword, PASSWORD_DEFAULT);

        // Обновляем пароль в БД
        $updateSql = "UPDATE {$this->userTable} SET 
                    password_hash = '{$db->escape($newPasswordHash)}', 
                    updated_at = NOW() 
                    WHERE id = {$userId}";

        return $db->query($updateSql) !== false;
    }

    private function loginByRememberToken(string $token): bool
    {
        $db = $this->dbManager->getConnection();
        $safeToken = $db->escape($token);
        
        $sql = "SELECT id, status FROM {$this->userTable} WHERE remember_token = '{$safeToken}' LIMIT 1";
        $result = $db->query($sql);

        if (empty($result)) return false;

        $user = $result[0];
        $this->initSession();
        $_SESSION[$this->sessionKey] = [
            'id' => $user['id'],
            'role' => $user['role_id'],
            'name' => $user['i'] . ' ' . $user['f'],
            'email' => $user['email'],
            'status' => $user['status'],
            'avatar' => $user['avatar']
        ];

        // Обновляем время входа
        $currentTime = date('Y-m-d H:i:s');
        $db->query("UPDATE {$this->userTable} SET logined_at = '{$currentTime}' WHERE id = {$user['id']}");

        return true;
    }

    private function initSession(): void
    {
        if (!$this->sessionStarted && (session_status() === PHP_SESSION_NONE)) {
            session_start();
            $this->sessionStarted = true;
        }
    }

    private function clearRememberToken(string $token): void
    {
        $this->dbManager->getConnection()->query(
            "UPDATE {$this->userTable} SET remember_token = NULL WHERE remember_token = '" . 
            $this->dbManager->getConnection()->escape($token) . "'"
        );
    }
}