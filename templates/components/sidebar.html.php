<aside class="crm-sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center">
        <h5>Меню</h5>
        <button class="sidebar-close d-lg-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link active" href="/">
                <i class="fas fa-home me-2"></i>Главная
            </a>
        </li>
        
        <li class="nav-item">
            <div class="nav-section d-flex w-10">
                <div class="sidebar-header d-flex w-8">
                    <i class="fas fa-warehouse me-2"></i>
                    <span>Склады</span>
                </div>
                <a class="arrow w-2" data-bs-toggle="collapse" href="#warehousesMenu">
                    <i class="fas fa-angle-down ms-auto"></i>
                </a>
            </div>
            <div class="collapse" id="warehousesMenu">
                <ul class="nav flex-column ps-4">
                <li class="nav-item">
                        <a class="nav-link" href="/vendors">
                            <i class="fas fa-users me-2"></i>Продавцы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/companies">
                            <i class="fas fa-flag me-2"></i>Компании
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/categories">
                            <i class="fas fa-list me-2"></i>Категории
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products">
                            <i class="fas fa-cubes me-2"></i>Товары
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/acceptances">
                            <i class="fas fa-truck me-2"></i>Поступления
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <div class="nav-section d-flex w-10">
                <div class="sidebar-header d-flex w-8">
                    <i class="fas fa-exchange me-2"></i>
                    <span>Продажи</span>
                </div>
                <a class="arrow w-2" data-bs-toggle="collapse" href="#customersMenu">
                    <i class="fas fa-angle-down ms-auto"></i>
                </a>
            </div>
            <div class="collapse" id="customersMenu">
                <ul class="nav flex-column ps-4">
                    <li class="nav-item">
                        <a class="nav-link" href="/customers">
                            <i class="fas fa-user-friends me-2"></i>Покупатели
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/orders">
                            <i class="fas fa-tasks me-2"></i>Заказы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/orders_positions">
                            <i class="fas fa-cubes me-2"></i>Позиции заказов
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/reviews">
                            <i class="fas fa-comment me-2"></i>Отзывы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/recommendations">
                            <i class="fas fa-tasks me-2"></i>Рекомендации
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <div class="nav-section d-flex w-10">
                <div class="sidebar-header d-flex w-8">
                    <i class="fas fa-server me-2"></i>
                    <span>Организация</span>
                </div>
                <a class="arrow w-2" data-bs-toggle="collapse" href="#crmMenu">
                    <i class="fas fa-angle-down ms-auto"></i>
                </a>
            </div>
            <div class="collapse" id="crmMenu">
                <ul class="nav flex-column ps-4">
                    <li class="nav-item">
                        <a class="nav-link" href="/employees">
                            <i class="fas fa-users-cog me-2"></i>Сотрудники
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/stats">
                            <i class="fas fa-line-chart me-2"></i>Статистика
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/reports">
                            <i class="fas fa-chart-bar me-2"></i>Отчеты
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <?php if (isset($data['user_session']['role']) && $data['user_session']['role'] === 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link" href="/settings">
                <i class="fas fa-cog me-2"></i>Настройки
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>

<style>
.crm-sidebar {
    width: var(--crm-sidebar-width);
    background: white;
    border-right: 1px solid #e3e6f0;
    display: flex;
    flex-direction: column;
    height: calc(100vh - var(--crm-navbar-height));
    position: fixed;
    left: 0;
    top: var(--crm-navbar-height);
    transition: transform 0.3s ease;
    z-index: 1000;
    overflow-y: auto;
}

.sidebar-header {
    padding: 1rem 1.5rem;
    font-weight: 600;
    color: var(--crm-secondary);
    border-bottom: 1px solid #eee;
}

.sidebar-close {
    background: none;
    border: none;
    color: #999;
    font-size: 1.25rem;
}

.nav-link {
    color: #6e707e;
    padding: 0.75rem 1.5rem;
    border-left: 3px solid transparent;
    display: flex;
    align-items: center;
}

.nav-link.active {
    color: var(--crm-accent);
    font-weight: 600;
    border-left-color: var(--crm-accent);
    background: rgba(52, 152, 219, 0.1);
}

.nav-link:hover {
    color: var(--crm-accent);
    background: rgba(52, 152, 219, 0.05);
}

.nav-section.d-flex.w-10 {
    width: 100%;
    border-top: 1px solid #eee;
}

.sidebar-header.d-flex.w-8 {
    width: 80%
}

.arrow.w-2 {
    width:         20%;
    padding-top:   15px;
    text-align:    center;
    border-left:   1px solid #eee;
    border-bottom: 1px solid #eee;
}

@media (max-width: 992px) {
    .crm-sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar-open .crm-sidebar {
        transform: translateX(0);
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        display: none;
    }
    
    .sidebar-open .sidebar-overlay {
        display: block;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для кнопки закрытия сайдбара
    document.querySelector('.sidebar-close')?.addEventListener('click', function() {
        document.querySelector('.crm-wrapper').classList.remove('sidebar-open');
    });
    
    // Инициализация состояния подменю
    const navLinks = document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');
    navLinks.forEach(link => {
        const target = link.getAttribute('href');
        const collapse = document.querySelector(target);
        
        if (link.classList.contains('active') || 
            collapse.querySelector('.nav-link.active')) {
            link.classList.remove('collapsed');
            collapse.classList.add('show');
        }
    });
});
</script>