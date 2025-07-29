<nav class="navbar navbar-expand-lg crm-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <button class="navbar-toggler sidebar-toggle d-lg-none me-2" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <a class="navbar-brand" href="/">
                <img src="/assets/img/logo.png" alt="CRM Logo" height="40">
                <span class="ms-2 brand-text">ФлексМаркет CRM</span>
            </a>
        </div>
        
        <div class="d-flex align-items-center">
            <!-- Поисковая строка для десктопа -->
            <form class="input-group crm-search d-none d-lg-flex me-3" action="/search" method="GET">
                <input type="text" class="form-control" name="q" placeholder="Поиск..." value="<?= $this->escape($_GET['q'] ?? '') ?>">
                <button class="btn" type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <div class="dropdown">
                <a class="nav-link dropdown-toggle user-dropdown nav-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= $this->escape(isset($data['user_session']['avatar']) ? ('/uploads/avatars/' . $data['user_session']['avatar']) : '/assets/img/default-avatar.png'); ?>" class="rounded-circle" width="40" height="40" alt="User">
                    <span class="ms-2 d-none d-lg-inline"><?= $this->escape($data['user_session']['name'] ?? 'Пользователь') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><h6 class="dropdown-header"><?= $this->escape($data['user_session']['email'] ?? 'email@example.com') ?></h6></li>
                    <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i>Профиль</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/api/logout"><i class="fas fa-sign-out-alt me-2"></i>Выход</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="sidebar-overlay"></div>

<style>
.crm-navbar {
    background-color: var(--crm-primary);
    height: auto;
    min-height: var(--crm-navbar-height);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    padding: 0.5rem 1rem;
}

.navbar-brand {
    display: flex;
    align-items: center;
}

.brand-text {
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.sidebar-toggle {
    color: rgba(255,255,255,0.8);
    border: none;
    background: none;
    font-size: 1.25rem;
    padding: 0.5rem;
}

.crm-search .form-control {
    background-color: white;
    border: none;
    color: var(--crm-dark);
    min-width: 250px;
    border-radius: 20px 0 0 20px;
}

.crm-search .form-control::placeholder {
    color: #aaa;
}

.crm-search .btn {
    background-color: var(--crm-accent);
    color: white;
    border-radius: 0 20px 20px 0;
}

.nav-link {
    color: rgba(255,255,255,0.8);
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
}

.nav-white {
    color: #FFFFFF !important
}

.nav-link:hover, .nav-link:focus {
    color: white;
}

.user-dropdown {
    padding: 0.25rem 0.5rem;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-top: 0.5rem;
}

.dropdown-header {
    color: var(--crm-secondary);
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
}

@media (max-width: 992px) {
    .crm-navbar {
        flex-wrap: wrap;
    }
    
    .crm-search {
        width: 100%;
        margin: 0.5rem 0;
    }
    
    .crm-search .form-control {
        min-width: auto;
    }
    
    .sidebar-toggle {
        display: block;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для кнопки переключения сайдбара
    document.querySelector('.sidebar-toggle')?.addEventListener('click', function() {
        document.querySelector('.crm-wrapper').classList.toggle('sidebar-open');
    });
    
    // Закрытие сайдбара при клике на оверлей
    document.querySelector('.sidebar-overlay')?.addEventListener('click', function() {
        document.querySelector('.crm-wrapper').classList.remove('sidebar-open');
    });
    
    // Фиксация для dropdown меню
    document.querySelector('.user-dropdown')?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const menu = this.nextElementSibling;
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
});
</script>