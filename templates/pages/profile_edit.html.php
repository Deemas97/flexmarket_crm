<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Profile Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Редактирование профиля</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/profile">Профиль</a></li>
                                <li class="breadcrumb-item active">Редактирование</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <!-- Edit Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Основная информация</h5>
                            </div>
                            <div class="card-body">
                                <form id="profileForm" enctype="multipart/form-data" action="/api/profile/edit" method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Фамилия</label>
                                            <input type="text" class="form-control" name="f" 
                                                   value="<?= $this->escape($data['user_data']['f']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Имя</label>
                                            <input type="text" class="form-control" name="i" 
                                                   value="<?= $this->escape($data['user_data']['i']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Отчество</label>
                                            <input type="text" class="form-control" name="o" 
                                                   value="<?= isset($data['user_data']['o']) ? $this->escape($data['user_data']['o']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Телефон</label>
                                            <input type="tel" class="form-control" name="phone" 
                                                   value="<?= isset($data['user_data']['phone_code'], $data['user_data']['phone_number']) ? $this->escape($data['user_data']['phone_code'] . $data['user_data']['phone_number']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Аватар</label>
                                        <input type="file" class="form-control" name="avatar">
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="/profile" class="btn btn-secondary me-2">Отмена</a>
                                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Безопасность</h5>
                            </div>
                            <div class="card-body">
                                <form id="passwordForm" action="/api/profile/change_password" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Текущий пароль</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Новый пароль</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Подтвердите пароль</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Изменить пароль</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/profile.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Валидация форм
    $('#profileForm, #passwordForm').validate({
        rules: {
            confirm_password: {
                equalTo: "#new_password"
            }
        },
        messages: {
            confirm_password: {
                equalTo: "Пароли должны совпадать"
            }
        }
    });
});
</script>
<?php $this->endSection() ?>