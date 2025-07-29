<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Vendors Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Управление продавцами</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Продавцы</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Добавить продавца
                        </button>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список продавцов</h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control search" placeholder="Поиск продавцов...">
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
                                        <th>Компания</th>
                                        <th>ФИО</th>
                                        <th>Роль</th>
                                        <th>Email</th>
                                        <th>Статус</th>
                                        <th>Дата регистрации</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['vendors'] as $vendor): ?>
                                    <tr>
                                        <td><?= $this->escape($vendor['id']) ?></td>
                                        <td>
                                            <a href="/company/<?= $vendor['company_id'] ?>/edit"><?= $this->escape($vendor['company_name']) ?></a>
                                        </td>
                                        <td>
                                            <?= $this->escape($vendor['f']) ?>
                                            <?= $this->escape($vendor['i']) ?>
                                            <?= isset($vendor['o']) ? $this->escape($vendor['o']) : '' ?>
                                        </td>
                                        
                                        <td>
                                            <?php if ($vendor['role_id'] === 'admin'): ?>
                                                <span class="badge bg-danger">Администратор</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Менеджер</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $this->escape($vendor['email']) ?></td>
                                        <td>
                                            <?php if ($vendor['status'] === 'active'): ?>
                                                <span class="badge bg-success">Активен</span>
                                            <?php elseif ($vendor['status'] === 'banned'): ?>
                                                <span class="badge bg-dark">Заблокирован</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">На модерации</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($vendor['created_at'])) ?></td>
                                        <?php if ($data['user_session']['role'] === 'admin'): ?>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/vendor/<?= $vendor['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger delete-user" data-id="<?= $vendor['id'] ?>" title="Удалить">
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
<link rel="stylesheet" href="/assets/css/pages/statistics.css">
<?php $this->endSection() ?>

<?php if ($data['user_session']['role'] === 'admin'): ?>
<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    $('.delete-user').click(function() {
        const userId = $(this).data('id');
        $('#deleteUserModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: '/api/vendor/' + userId + '/delete',
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