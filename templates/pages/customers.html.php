<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Customers Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Управление покупателями</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Покупатели</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Добавить покупателя
                        </button>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список покупателей</h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control search" placeholder="Поиск пользователей...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>ФИО</th>
                                        <th>Роль</th>
                                        <th>Email</th>
                                        <th>Статус</th>
                                        <th>Дата регистрации</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['customers'] as $customer): ?>
                                    <tr>
                                        <td><?= $this->escape($customer['id']) ?></td>
                                        <td>
                                            <?= $this->escape($customer['f']) ?> 
                                            <?= $this->escape($customer['i']) ?>
                                            <?= isset($customer['o']) ? $this->escape($customer['o']) : '' ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['role_id'] === 'simple'): ?>
                                                <span class="badge bg-danger">Базовый</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Подписчик</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $this->escape($customer['email']) ?></td>
                                        <td>
                                            <?php if ($customer['status'] === 'active'): ?>
                                                <span class="badge bg-success">Активен</span>
                                            <?php elseif ($customer['status'] === 'banned'): ?>
                                                <span class="badge bg-dark">Заблокирован</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">На модерации</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($customer['created_at'])) ?></td>
                                        <?php if ($data['user_session']['role'] === 'admin'): ?>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/customer/<?= $customer['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger delete-user" data-id="<?= $customer['id'] ?>" title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

<?php if ($data['user_session']['role'] === 'admin'): ?>
<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm" action="/api/customer/create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Фамилия*</label>
                        <input type="text" class="form-control" name="f" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Имя*</label>
                        <input type="text" class="form-control" name="i" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Отчество</label>
                        <input type="text" class="form-control" name="o">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email*</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Роль*</label>
                        <select class="form-select" name="role_id" required>
                            <option value="simple">Базовый</option>
                            <option value="subscriber">Подписчик</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Адрес*</label>
                        <input type="address" class="form-control" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль*</label>
                        <input id="password" type="password" class="form-control" name="password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Подтверждение пароля*</label>
                        <input id="password_confirm" type="password" class="form-control" name="password_confirm" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этого пользователя?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Удалить</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/customers.css">
<?php $this->endSection() ?>

<?php if ($data['user_session']['role'] === 'admin'): ?>
<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Инициализация валидации формы
    $('#addUserForm').validate({
        rules: {
            password_confirm: {
                equalTo: "#password"
            }
        },
        messages: {
            password_confirm: {
                equalTo: "Пароли должны совпадать"
            }
        }
    });

    // Обработка удаления пользователя
    $('.delete-user').click(function() {
        const userId = $(this).data('id');
        $('#deleteUserModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: '/api/customer/' + userId + '/delete',
                method: 'DELETE',
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('Ошибка при удалении пользователя');
                }
            });
        });
    });

    // Поиск в таблицах
    $('.search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
<?php $this->endSection() ?>
<?php endif; ?>