<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Profile Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Мой профиль</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Профиль</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <a href="/profile/edit" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Редактировать
                        </a>
                    </div>
                </div>
                
                <!-- Profile Card -->
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <img src="<?= $this->escape(isset($data['user_data']['avatar']) ? ('/uploads/avatars/' . $data['user_data']['avatar']) : '/assets/img/default-avatar.png'); ?>"
                                    class="rounded-circle mb-3" width="150" height="150" alt="Аватар">
                                <h4 class="mb-1"><?= $this->escape($data['user_data']['i']) ?> <?= $this->escape($data['user_data']['f']) ?></h4>
                                <p class="text-muted mb-3">
                                    <?php if ($data['user_data']['role_id'] === 'admin'): ?>
                                        <span class="badge bg-danger">Администратор</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Менеджер</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Информация о профиле</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Фамилия</p>
                                        <p><?= $this->escape($data['user_data']['f']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Имя</p>
                                        <p><?= $this->escape($data['user_data']['i']) ?></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Отчество</p>
                                        <p><?= isset($data['user_data']['o']) ? $this->escape($data['user_data']['o']) : 'Не указано' ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Email</p>
                                        <p><?= $this->escape($data['user_data']['email']) ?></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Телефон</p>
                                        <p><?= isset($data['user_data']['phone']) ? $this->escape($data['user_data']['phone']) : 'Не указан' ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Дата регистрации</p>
                                        <p><?= date('d.m.Y', strtotime($data['user_data']['created_at'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/profile.css">
<?php $this->endSection() ?>