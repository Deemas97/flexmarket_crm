<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="crm-container">
    <div class="container-fluid py-4">
        <!-- Orders Positions Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-0">Позиции товаров в заказах</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Главная</a></li>
                        <li class="breadcrumb-item"><a href="/orders">Заказы</a></li>
                        <li class="breadcrumb-item active">Позиции товаров</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <div class="input-group">
                    <select class="form-select" id="statusFilter">
                        <option value="">Все статусы</option>
                        <?php foreach ($data['statuses'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= isset($_GET['status']) && $_GET['status'] === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Orders Positions Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Список позиций товаров</h5>
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control search" placeholder="Поиск товаров...">
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
                                <th>Товар</th>
                                <th>Продавец</th>
                                <th>Покупатель</th>
                                <th>Заказ</th>
                                <th>Цена</th>
                                <th>Количество</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['positions'] as $position): ?>
                            <tr>
                                <td><?= $this->escape($position['id']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($position['product_image']): ?>
                                            <img src="/uploads/images/products/main/<?= $this->escape($position['product_image']) ?>" class="rounded me-2" width="40" height="40" alt="<?= $this->escape($position['product_name']) ?>">
                                        <?php endif; ?>
                                        <div>
                                            <div><?= $this->escape($position['product_name']) ?></div>
                                            <small class="text-muted">ID: <?= $this->escape($position['product_id']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $this->escape($position['company_name']) ?></td>
                                <td>
                                    <div><?= $this->escape($position['customer_f'] . ' ' . $this->escape($position['customer_i'])) ?></div>
                                    <small class="text-muted"><?= $this->escape($position['customer_email']) ?></small>
                                </td>
                                <td>
                                    <a href="/order/<?= $this->escape($position['order_id']) ?>/edit">Заказ #<?= $this->escape($position['order_id']) ?></a>
                                    <div class="text-muted small"><?= date('d.m.Y H:i', strtotime($position['order_created_at'])) ?></div>
                                </td>
                                <td><?= number_format($position['price'], 2, '.', ' ') ?> ₽</td>
                                <td><?= $this->escape($position['count']) ?></td>
                                <td><?= number_format($position['price'] * $position['count'], 2, '.', ' ') ?> ₽</td>
                                <td>
                                    <span class="badge 
                                        <?= $position['status'] === 'pending'    ? 'bg-warning' : '' ?>
                                        <?= $position['status'] === 'processing' ? 'bg-info' : '' ?>
                                        <?= $position['status'] === 'shipped'    ? 'bg-primary' : '' ?>
                                        <?= $position['status'] === 'delivered'  ? 'bg-success' : '' ?>
                                        <?= $position['status'] === 'cancelled'  ? 'bg-danger' : '' ?>
                                    ">
                                        <?= $data['statuses'][$position['status']] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary update-status-btn" 
                                            data-position-id="<?= $position['id'] ?>" 
                                            title="Изменить статус">
                                            <i class="fas fa-sync-alt"></i>
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
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Изменение статуса позиции</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" name="position_id" id="positionId">
                    <div class="mb-3">
                        <label for="statusSelect" class="form-label">Новый статус</label>
                        <select class="form-select" id="statusSelect" name="status">
                            <?php foreach ($data['statuses'] as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveStatusBtn">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/orders_positions.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Фильтрация по статусу
    $('#statusFilter').change(function() {
        const status = $(this).val();
        window.location.href = '/orders/positions' + (status ? '?status=' + status : '');
    });

    // Поиск в таблицах
    $('.search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Обработка клика по кнопке изменения статуса
    $('.update-status-btn').click(function() {
        const positionId = $(this).data('position-id');
        $('#positionId').val(positionId);
        $('#statusModal').modal('show');
    });

    // Сохранение статуса
    $('#saveStatusBtn').click(function() {
        const formData = $('#statusForm').serialize();
        const positionId = $('#positionId').val();
        
        if (!positionId) {
            alert('Ошибка: ID позиции не указан');
            return;
        }

        $.ajax({
            url: '/api/order_position/' + positionId + '/update_status',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.error || 'Ошибка при обновлении статуса');
                }
            },
            error: function() {
                alert('Ошибка соединения с сервером');
            }
        });
    });
});
</script>
<?php $this->endSection() ?>