<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Acceptances Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Управление приёмками</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Приёмки</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAcceptanceModal">
                            <i class="fas fa-plus me-2"></i>Добавить приёмку
                        </button>
                    </div>
                </div>
                
                <!-- Acceptances Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список приёмок</h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control search" placeholder="Поиск приёмок...">
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
                                        <th>Продукт</th>
                                        <th>Количество</th>
                                        <th>Дата приёмки</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['acceptances'] as $acceptance): ?>
                                    <tr>
                                        <td><?= $this->escape($acceptance['id']) ?></td>
                                        <td><a href="/company/<?= $this->escape($acceptance['company_id']) ?>/edit"><?= $this->escape($acceptance['company_name'] ?? 'N/A') ?></a></td>
                                        <td><a href="/product/<?= $this->escape($acceptance['product_id']) ?>/edit"><?= $this->escape($acceptance['product_name'] ?? 'N/A') ?></a></td>
                                        <td><?= $this->escape($acceptance['count']) ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($acceptance['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/acceptance/<?= $acceptance['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger delete-acceptance" data-id="<?= $acceptance['id'] ?>" title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

<!-- Add Acceptance Modal -->
<div class="modal fade" id="addAcceptanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить приёмку</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAcceptanceForm" action="/api/acceptance/create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Компания*</label>
                        <select class="form-select" name="company_id" required>
                            <?php foreach ($data['companies'] as $company): ?>
                                <option value="<?= $company['id'] ?>"><?= $this->escape($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Продукт*</label>
                        <select class="form-select" name="product_id" required>
                            <?php foreach ($data['products'] as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= $this->escape($product['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Количество*</label>
                        <input type="number" class="form-control" name="count" min="1" required>
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
<div class="modal fade" id="deleteAcceptanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить эту приёмку?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Удалить</button>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/acceptances.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Обработка удаления приёмки
    $('.delete-acceptance').click(function() {
        const acceptanceId = $(this).data('id');
        $('#deleteAcceptanceModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: '/api/acceptance/' + acceptanceId + '/delete',
                method: 'DELETE',
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('Ошибка при удалении приёмки');
                }
            });
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
</script>
<?php $this->endSection() ?>