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

class RecommendationsController extends ControllerRendering
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
            'title' => 'Управление рекомендациями',
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
        
        // Глобальные рекомендации
        $globalRecommendations = $db->query("
            SELECT r.*, p.name as product_name
            FROM recommendations r
            LEFT JOIN products p ON r.product_id = p.id
            ORDER BY r.recommendation_score DESC
        ");
        
        // Персонализированные рекомендации
        $personalRecommendations = $db->query("
            SELECT pr.*, p.name as product_name, c.i as customer_i, c.f as customer_f
            FROM personal_recommendations pr
            LEFT JOIN products p ON pr.product_id = p.id
            LEFT JOIN customers c ON pr.customer_id = c.id
            ORDER BY pr.recommendation_score DESC
        ");
        
        $data['global_recommendations'] = $globalRecommendations === false ? [] : $globalRecommendations;
        $data['personal_recommendations'] = $personalRecommendations === false ? [] : $personalRecommendations;

        return $this->render('pages/recommendations.html.php', $data);
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