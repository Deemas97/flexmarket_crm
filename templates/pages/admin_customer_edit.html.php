<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Profile Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Редактирование покупателей</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/customers">Покупатели</a></li>
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
                                <form id="editUserForm" action="/api/customer/<?= $data['customer']['id'] ?>/edit" method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Фамилия</label>
                                            <input type="text" class="form-control" name="f" 
                                                   value="<?= $this->escape($data['customer']['f']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Имя</label>
                                            <input type="text" class="form-control" name="i" 
                                                   value="<?= $this->escape($data['customer']['i']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Отчество</label>
                                            <input type="text" class="form-control" name="o" 
                                                   value="<?= isset($data['customer']['o']) ? $this->escape($data['customer']['o']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?= $this->escape($data['customer']['email']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Роль</label>
                                            <select class="form-select" name="role_id" required>
                                                <option value="simple" <?= $data['customer']['role_id'] === 'simple' ? 'selected' : '' ?>>Базовый</option>
                                                <option value="subscriber" <?= $data['customer']['role_id'] === 'subscriber' ? 'selected' : '' ?>>Подписчик</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Статус</label>
                                            <select class="form-select" name="status" required>
                                                <option value="premoderation" <?= $data['customer']['status'] === 'premoderation' ? 'selected' : '' ?>>На модерации</option>
                                                <option value="active" <?= $data['customer']['status'] === 'active' ? 'selected' : '' ?>>Активен</option>
                                                <option value="banned" <?= $data['customer']['status'] === 'banned' ? 'selected' : '' ?>>Заблокирован</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">API-Ключ</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="api_token" id="api_token" value="<?= $this->escape($data['customer']['api_token'] ?? '')?>" readonly>
                                                <button class="btn btn-outline-secondary" type="button" id="generateToken">Сгенерировать</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="/customers" class="btn btn-secondary me-2">Отмена</a>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Смена пароля</h5>
                            </div>
                            <div class="card-body">
                                <form id="changePasswordForm" action="/api/customer/<?= $data['customer']['id'] ?>/change_password" method="POST">
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
<link rel="stylesheet" href="/assets/css/pages/customers.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    $('#editUserForm, #changePasswordForm').validate({
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

    $('#generateToken').click(function() {
        const token = generateRandomToken(32);
        $('#api_token').val(token);
    });

    function generateRandomToken(length) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }
});
</script>
<?php $this->endSection() ?>