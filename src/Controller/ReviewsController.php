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

class ReviewsController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer,
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        parent::__construct($renderer);
    }

    public function index(): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Управление отзывами',
            'company_name' => 'ФлексМаркет'
        ];

        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $db = $this->dbManager->getConnection();
        
        // Получаем список отзывов с информацией о клиентах и товарах
        $reviews = $db->query("
            SELECT r.*,
                   c.i as customer_i, c.f as customer_f,
                   p.name as product_name
            FROM reviews r
            LEFT JOIN customers c ON r.customer_id = c.id
            LEFT JOIN products p ON r.product_id = p.id
            ORDER BY r.created_at DESC
        ");
        
        $data['reviews'] = $reviews === false ? [] : $reviews;

        return $this->render('pages/reviews.html.php', $data);
    }

    public function review(int $id): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Редактирование отзыва',
            'company_name' => 'ФлексМаркет'
        ];

        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $db = $this->dbManager->getConnection();
        
        // Получаем информацию о отзыве
        $review = $db->query("
            SELECT r.*,
                   c.i as customer_i, c.f as customer_f,
                   p.name as product_name
            FROM reviews r
            LEFT JOIN customers c ON r.customer_id = c.id
            LEFT JOIN products p ON r.product_id = p.id
            WHERE r.id = {$id}
            LIMIT 1
        ");
        
        if (empty($review)) {
            $this->redirect('/error_404');
        }
        
        $data['review'] = $review[0];

        return $this->render('pages/review_edit.html.php', $data);
    }

    protected function checkAuth()
    {
        $this->auth->setUserTable('employees');

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/login');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}