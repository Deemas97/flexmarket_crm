<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Profile Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Редактирование сотрудников</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/employees">Сотрудники</a></li>
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
                                <form id="editUserForm" action="/api/employee/<?= $data['employee']['id'] ?>/edit" method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Фамилия</label>
                                            <input type="text" class="form-control" name="f" 
                                                   value="<?= $this->escape($data['employee']['f']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Имя</label>
                                            <input type="text" class="form-control" name="i" 
                                                   value="<?= $this->escape($data['employee']['i']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Отчество</label>
                                            <input type="text" class="form-control" name="o" 
                                                   value="<?= isset($data['employee']['o']) ? $this->escape($data['employee']['o']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?= $this->escape($data['employee']['email']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Роль</label>
                                            <select class="form-select" name="role_id" required>
                                                <option value="manager" <?= $data['employee']['role_id'] === 'manager' ? 'selected' : '' ?>>Менеджер</option>
                                                <option value="admin" <?= $data['employee']['role_id'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Статус</label>
                                            <select class="form-select" name="status" required>
                                                <option value="premoderation" <?= $data['employee']['status'] === 'premoderation' ? 'selected' : '' ?>>На модерации</option>
                                                <option value="active" <?= $data['employee']['status'] === 'active' ? 'selected' : '' ?>>Активен</option>
                                                <option value="banned" <?= $data['employee']['status'] === 'banned' ? 'selected' : '' ?>>Заблокирован</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="/employees" class="btn btn-secondary me-2">Отмена</a>
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
                                <form id="changePasswordForm" action="/api/employee/<?= $data['employee']['id'] ?>/change_password" method="POST">
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
<link rel="stylesheet" href="/assets/css/pages/employees.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Валидация форм
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
});
</script>
<?php $this->endSection() ?>