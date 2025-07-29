<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="error-container">
    <!-- Навигация как в основной CRM -->
    <nav class="navbar navbar-expand-lg navbar-dark crm-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <img src="/assets/img/logo.png" alt="CRM Logo" height="30">
            </a>
            
            <div class="collapse navbar-collapse">                
                <div class="d-flex">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="/api/logout"><i class="fas fa-sign-in-alt me-1"></i> Выход</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Основное содержимое страницы 404 -->
    <div class="error-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1 class="error-title">404 - Страница не найдена</h1>
                    <p class="error-text">
                        Запрашиваемая страница не существует или была перемещена.
                        Пожалуйста, проверьте URL или воспользуйтесь поиском.
                    </p>
                    
                    <div class="error-actions mt-5">
                        <div class="row g-3 justify-content-center">
                            <div class="col-md-4">
                                <a href="/" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-tachometer-alt me-2"></i>На главную
                                </a>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-link btn-lg w-100" onclick="history.back()">
                                    <i class="fas fa-arrow-left me-2"></i>Назад
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="error-search mt-5">
                        <form class="d-flex justify-content-center">
                            <div class="input-group" style="max-width: 500px;">
                                <input type="text" class="form-control form-control-lg" placeholder="Поиск по CRM...">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
.error-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background-color: #f8f9fc;
}

.crm-navbar {
    background-color: #4e73df;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.error-content {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 3rem 0;
}

.error-icon {
    font-size: 5rem;
    color: #e74a3b;
    margin-bottom: 2rem;
}

.error-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #5a5c69;
    margin-bottom: 1.5rem;
}

.error-text {
    font-size: 1.25rem;
    color: #6e707e;
    max-width: 700px;
    margin: 0 auto 2rem;
}

.error-actions .btn {
    border-radius: 0.35rem;
    padding: 0.75rem 1.5rem;
}

@media (max-width: 768px) {
    .error-title {
        font-size: 2rem;
    }
    
    .error-text {
        font-size: 1.1rem;
    }
    
    .error-actions .btn {
        padding: 0.5rem 1rem;
        font-size: 1rem;
    }
}
</style>
<?php $this->endSection() ?>