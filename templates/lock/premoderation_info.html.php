<?php $this->extend('lock/info_base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="error-container">
    <nav class="navbar navbar-expand-lg navbar-dark crm-navbar">
        <div class="container-fluid">
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

    <div class="error-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1 class="error-title">Аккаунт на премодерации</h1>
                    <p class="error-text">
                        Ваш аккаунт станет доступен после прохождения премодерации.<br>
                        О статусе аккаунта мы сообщим по электронной почте, указанной при регистрации.<br><br>
                        
                        По всем вопросам обращаться по электронной почте info@crm.flexmarket.ru
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>