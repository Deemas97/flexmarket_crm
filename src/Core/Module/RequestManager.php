<?php
namespace App\Core\Module;

include_once '../src/Core/ModuleInterface.php';
include_once '../src/Core/Module/RequestManagerInterface.php';
include_once '../src/Core/MessageBusInterface.php';
include_once '../src/Core/MessageBus/ActionsInterface.php';
include_once '../src/Core/MessageBus/Actions.php';
include_once '../src/Core/Service/Router.php';
include_once '../src/Core/Service/Router/RouteInterface.php';

include_once '../src/Controller/AcceptancesController.php';
include_once '../src/Controller/AuthController.php';
include_once '../src/Controller/CategoriesController.php';
include_once '../src/Controller/CompaniesController.php';
include_once '../src/Controller/CustomersController.php';
include_once '../src/Controller/EmployeesController.php';
include_once '../src/Controller/ErrorController.php';
include_once '../src/Controller/MainController.php';
include_once '../src/Controller/OrdersController.php';
include_once '../src/Controller/OrdersPositionsController.php';
include_once '../src/Controller/ProductsController.php';
include_once '../src/Controller/RecommendationsController.php';
include_once '../src/Controller/ReportsController.php';
include_once '../src/Controller/ReviewsController.php';
include_once '../src/Controller/SettingsController.php';
include_once '../src/Controller/SearchController.php';
include_once '../src/Controller/StatisticsController.php';
include_once '../src/Controller/StatusInfoController.php';
include_once '../src/Controller/TermsController.php';
include_once '../src/Controller/UserController.php';
include_once '../src/Controller/VendorsController.php';
include_once '../src/Controller/API/AcceptancesActionsController.php';
include_once '../src/Controller/API/CategoriesActionsController.php';
include_once '../src/Controller/API/CompaniesActionsController.php';
include_once '../src/Controller/API/CustomersActionsController.php';
include_once '../src/Controller/API/DashboardActionsController.php';
include_once '../src/Controller/API/EmployeesActionsController.php';
include_once '../src/Controller/API/OrdersActionsController.php';
include_once '../src/Controller/API/ProductsActionsController.php';
include_once '../src/Controller/API/RecommendationsActionsController.php';
include_once '../src/Controller/API/ReportsActionsController.php';
include_once '../src/Controller/API/SettingsActionsController.php';
include_once '../src/Controller/API/StatisticsActionsController.php';
include_once '../src/Controller/API/ReviewsActionsController.php';
include_once '../src/Controller/API/UserActionsController.php';
include_once '../src/Controller/API/UserAuthController.php';
include_once '../src/Controller/API/VendorsActionsController.php';

include_once '../src/Controller/API/Storage/CustomerStorageApiController.php';
include_once '../src/Controller/API/Storage/VendorStorageApiController.php';

use App\Controller\AcceptancesActionsController;
use App\Controller\AcceptancesController;
use App\Core\ModuleInterface;
use App\Core\MessageBusInterface;
use App\Core\MessageBus\ActionsInterface;
use App\Core\MessageBus\Actions;
use App\Core\Service\Router;
use App\Core\Service\Router\RouteInterface;

use App\Controller\AuthController;
use App\Controller\CategoriesActionsController;
use App\Controller\CategoriesController;
use App\Controller\CompaniesActionsController;
use App\Controller\CompaniesController;
use App\Controller\CustomersActionsController;
use App\Controller\CustomersController;
use App\Controller\DashboardActionsController;
use App\Controller\EmployeesActionsController;
use App\Controller\MainController;
use App\Controller\TermsController;
use App\Controller\UserAuthController;
use App\Controller\EmployeesController;
use App\Controller\ErrorController;
use App\Controller\OrdersActionsController;
use App\Controller\OrdersController;
use App\Controller\OrdersPositionsController;
use App\Controller\ProductsActionsController;
use App\Controller\ProductsController;
use App\Controller\RecommendationsActionsController;
use App\Controller\RecommendationsController;
use App\Controller\ReportsActionsController;
use App\Controller\ReportsController;
use App\Controller\ReviewsActionsController;
use App\Controller\ReviewsController;
use App\Controller\SearchActionsController;
use App\Controller\SearchController;
use App\Controller\SettingsActionsController;
use App\Controller\SettingsController;
use App\Controller\StatisticsActionsController;
use App\Controller\StatisticsController;
use App\Controller\StatusInfoController;
use App\Controller\UserActionsController;
use App\Controller\UserController;
use App\Controller\VendorsActionsController;
use App\Controller\VendorsController;

use App\Controller\CustomerStorageApiController;
use App\Controller\VendorStorageApiController;

class RequestManager implements ModuleInterface, RequestManagerInterface
{
    public function __construct(
        protected Router $router
    )
    {
        $this->initRouter();
    }

    public function process(MessageBusInterface $messageBus): ActionsInterface
    {
        $actions = new Actions();

        $route = $this->router->resolve(
            $messageBus->get('route'),
            $messageBus->get('method') ?? 'GET',
            $messageBus->get('xhr')
        );

        if ($route instanceof RouteInterface) {
            $actions->addItem('headers', $messageBus->get('headers'));
            $actions->addItem('method',  $messageBus->get('method'));

            $actions->addItem('controller', $route->getControllerName());
            $actions->addItem('method',     $route->getMethodName());

            $actions->addItem('route_parameters', $route->getParameters());
            $actions->addItem('query_parameters', ($messageBus->get('query') ?? ''));
            $actions->addItem('input', ($messageBus->get('input') ?? []));
        }

        return $actions;
    }

    protected function initRouter(): void
    {
        $this->router
            // UNAUTHORIZED
            ->register('/login',                     AuthController::class, 'authForm')
            ->register('/signup',                    AuthController::class, 'registerForm')
            ->register('/password/reset',            AuthController::class, 'resetPassForm')
            ->register('/password/reset/confirm',    AuthController::class, 'confirmPassForm')


            // AUTHORIZED ONLY
            ->register('/',                          MainController::class, 'index')

            ->register('/companies',                 CompaniesController::class, 'index')
            ->register('/company/{id}/edit',         CompaniesController::class, 'company')

            ->register('/vendors',                   VendorsController::class, 'index')
            ->register('/vendor/{id}/edit',          VendorsController::class, 'vendor')

            ->register('/acceptances',               AcceptancesController::class, 'index')
            ->register('/acceptance/{id}/edit',      AcceptancesController::class, 'acceptance')

            ->register('/products',                  ProductsController::class, 'index')
            ->register('/product/create',            ProductsController::class, 'createForm')
            ->register('/product/{id}/edit',         ProductsController::class, 'product')

            ->register('/categories',                CategoriesController::class, 'index')
            ->register('/category/{id}/edit',        CategoriesController::class, 'category')

            ->register('/customers',                 CustomersController::class, 'index')
            ->register('/customer/{id}/edit',        CustomersController::class, 'customer')

            ->register('/orders',                    OrdersController::class, 'index')
            ->register('/order/{id}/edit',           OrdersController::class, 'order')

            ->register('/orders_positions',          OrdersPositionsController::class, 'index')
            ->register('/orders_position/{id}/edit', OrdersPositionsController::class, 'orderPosition')

            ->register('/reviews',                   ReviewsController::class, 'index')
            ->register('/review/{id}/edit',          ReviewsController::class, 'review')

            ->register('/recommendations',           RecommendationsController::class, 'index')

            ->register('/employees',                 EmployeesController::class, 'index')
            ->register('/employee/{id}/edit',        EmployeesController::class, 'employee')

            ->register('/stats',                     StatisticsController::class, 'index')

            ->register('/reports',                   ReportsController::class, 'index')
            ->register('/report/{id}',               ReportsController::class, 'report')

            ->register('/settings',                  SettingsController::class, 'index')
            
            ->register('/premoderation_info',        StatusInfoController::class, 'premoderationInfo')
            ->register('/ban_info',                  StatusInfoController::class, 'banInfo')

            ->register('/profile',                   UserController::class, 'index')
            ->register('/profile/edit',              UserController::class, 'edit')

            ->register('/search',                    SearchController::class, 'index')

            ->register('/terms',                     TermsController::class, 'termsFolder')
            
            ->register('/access_denied',             ErrorController::class, 'error403')
            ->register('/not_found',                 ErrorController::class, 'error404')
            ->register('/unknown_method',            ErrorController::class, 'error405')
            ->register('/crash',                     ErrorController::class, 'error500')



            // INNER API-METHODS
            ->register('/api/dashboard/sales_data',          DashboardActionsController::class, 'apiGetSalesData')

            ->register('/api/company/create',                CompaniesActionsController::class, 'apiCreate', 'POST')
            ->register('/api/company/{id}/edit',             CompaniesActionsController::class, 'apiUpdate', 'POST')
            ->register('/api/company/{id}/delete',           CompaniesActionsController::class, 'apiDelete', 'DELETE')

            ->register('/api/vendor/create',                 VendorsActionsController::class, 'apiCreate',         'POST')
            ->register('/api/vendor/{id}/edit',              VendorsActionsController::class, 'apiUpdate',         'POST')
            ->register('/api/vendor/{id}/change_password',   VendorsActionsController::class, 'apiChangePassword', 'POST')
            ->register('/api/vendor/{id}/delete',            VendorsActionsController::class, 'apiDelete',         'DELETE')

            ->register('/api/acceptance/create',             AcceptancesActionsController::class, 'apiCreate', 'POST')
            ->register('/api/acceptance/{id}/edit',          AcceptancesActionsController::class, 'apiUpdate', 'POST')
            ->register('/api/acceptance/{id}/delete',        AcceptancesActionsController::class, 'apiDelete', 'DELETE')

            ->register('/api/product/create',                    ProductsActionsController::class, 'apiCreate',             'POST')
            ->register('/api/product/{id}/edit',                 ProductsActionsController::class, 'apiUpdate',             'POST')
            ->register('/api/product/{id}/delete',               ProductsActionsController::class, 'apiDelete',             'DELETE')
            ->register('/api/product/{id}/remove_gallery_image', ProductsActionsController::class, 'apiRemoveGalleryImage', 'POST')

            ->register('/api/category/create',               CategoriesActionsController::class, 'apiCreate', 'POST')
            ->register('/api/category/{id}/edit',            CategoriesActionsController::class, 'apiUpdate', 'POST')
            ->register('/api/category/{id}/delete',          CategoriesActionsController::class, 'apiDelete', 'DELETE')

            ->register('/api/customer/create',               CustomersActionsController::class, 'apiCreate',         'POST')
            ->register('/api/customer/{id}/edit',            CustomersActionsController::class, 'apiUpdate',         'POST')
            ->register('/api/customer/{id}/change_password', CustomersActionsController::class, 'apiChangePassword', 'POST')
            ->register('/api/customer/{id}/delete',          CustomersActionsController::class, 'apiDelete',         'DELETE')

            ->register('/api/order/{id}/update_status',          OrdersActionsController::class, 'apiUpdateStatus', 'POST')

            ->register('/api/order_position/{id}/update_status', OrdersActionsController::class, 'apiUpdatePositionStatus', 'POST')

            ->register('/api/review/{id}/edit',              ReviewsActionsController::class, 'apiUpdate', 'POST')
            ->register('/api/review/{id}/delete',            ReviewsActionsController::class, 'apiDelete', 'DELETE')

            ->register('/api/recommendation/{id}/edit',            RecommendationsActionsController::class, 'apiUpdateGlobal', 'POST')
            ->register('/api/recommendation/{id}/delete',          RecommendationsActionsController::class, 'apiDeleteGlobal', 'DELETE')
            ->register('/api/personal_recommendation/{id}/edit',   RecommendationsActionsController::class, 'apiUpdatePersonal', 'POST')
            ->register('/api/personal_recommendation/{id}/delete', RecommendationsActionsController::class, 'apiDeletePersonal', 'DELETE')

            ->register('/api/employee/create',               EmployeesActionsController::class, 'apiCreate',         'POST')
            ->register('/api/employee/{id}/edit',            EmployeesActionsController::class, 'apiUpdate',         'POST')
            ->register('/api/employee/{id}/change_password', EmployeesActionsController::class, 'apiChangePassword', 'POST')
            ->register('/api/employee/{id}/delete',          EmployeesActionsController::class, 'apiDelete',         'DELETE')

            ->register('/api/statistics/sales',              StatisticsActionsController::class, 'apiGetSalesStats')
            ->register('/api/statistics/products',           StatisticsActionsController::class, 'apiGetProductsStats')
            ->register('/api/statistics/customers',          StatisticsActionsController::class, 'apiGetCustomersStats')
            ->register('/api/statistics/inventory',          StatisticsActionsController::class, 'apiGetInventoryStats')

            ->register('/api/reports/get_list',              ReportsActionsController::class, 'apiListReports')
            ->register('/api/report/create',                 ReportsActionsController::class, 'apiGenerateReport', 'POST')
            ->register('/api/report/{id}/download',          ReportsActionsController::class, 'apiDownloadReport')
            ->register('/api/report/{id}/delete',            ReportsActionsController::class, 'apiDeleteReport',   'DELETE')

            ->register('/api/settings/edit',                 SettingsActionsController::class, 'apiUpdateSettings', 'POST')
            ->register('/api/settings/clear_cache',          SettingsActionsController::class, 'apiClearCache',     'POST')

            ->register('/api/search',                        SearchActionsController::class, 'apiSearchSuggest',    'POST')

            ->register('/api/login',                         UserAuthController::class, 'apiAuth',     'POST')
            ->register('/api/signup',                        UserAuthController::class, 'apiRegister', 'POST')
            ->register('/api/logout',                        UserAuthController::class, 'apiLogout')

            ->register('/api/profile/change_password',       UserActionsController::class, 'apiChangePassword', 'POST')
            ->register('/api/profile/edit',                  UserActionsController::class, 'apiUpdate',         'POST')
            ////


            
            // PUBLIC METHODS FOR VENDORS AND CUSTOMERS
            ->register('/api/vendor/storage/get_images',           VendorStorageApiController::class, 'apiGetImages',     'POST')
            ->register('/api/vendor/storage/upload_product_image', VendorStorageApiController::class, 'apiUploadImage',   'POST')
            ->register('/api/vendor/storage/upload_gallery',       VendorStorageApiController::class, 'apiUploadGallery', 'POST')
            ->register('/api/vendor/storage/delete_gallery',       VendorStorageApiController::class, 'apiDeleteGallery', 'DELETE')
            
            ->register('/api/customer/storage/get_images',         CustomerStorageApiController::class, 'apiGetImages', 'POST');
            ////
    }
}