<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Редактирование заказа #<?= $data['order']['id'] ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/orders_positions">Позиции заказов</a></li>
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
                    <h5 class="mb-0">Информация о заказе</h5>
                </div>
                <div class="card-body">
                    <form id="editOrderForm" action="/api/order/<?= $data['order']['id'] ?>/update_status" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Клиент</label>
                                <input type="text" class="form-control" 
                                       value="<?= $this->escape($data['order']['customer_i'] . ' ' . $data['order']['customer_f']) ?>" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" 
                                       value="<?= $this->escape($data['order']['customer_email']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Статус*</label>
                                <select class="form-select" name="status" required>
                                    <?php foreach ($data['statuses'] as $value => $label): ?>
                                        <option value="<?= $value ?>" 
                                            <?= $value == $data['order']['status'] ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Сумма заказа</label>
                            <input type="text" class="form-control" 
                                   value="<?= number_format($data['order']['sum'], 2, '.', ' ') ?> ₽" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Дата создания</label>
                            <input type="text" class="form-control" 
                                   value="<?= date('d.m.Y H:i', strtotime($data['order']['created_at'])) ?>" readonly>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="/orders" class="btn btn-secondary me-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="row mt-4">
        <div class="col">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Товары в заказе</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th>Цена</th>
                                    <th>Количество</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['products'] as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($product['product_image']): ?>
                                                <img src="/uploads/images/products/main/<?= $this->escape($product['product_image']) ?>" 
                                                     class="rounded me-3" width="40" height="40" alt="<?= $this->escape($product['product_name']) ?>">
                                            <?php endif; ?>
                                            <div>
                                                <div><a href="/product/<?= $this->escape($product['product_id']) ?>/edit"><?= $this->escape($product['product_name']) ?></a></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($product['price'], 2, '.', ' ') ?> ₽</td>
                                    <td><?= $product['count'] ?></td>
                                    <td><?= number_format($product['price'] * $product['count'], 2, '.', ' ') ?> ₽</td>
                                    <td>
                                        <span class="badge 
                                            <?= $product['status'] === 'pending'    ? 'bg-warning' : '' ?>
                                            <?= $product['status'] === 'processing' ? 'bg-info' : '' ?>
                                            <?= $product['status'] === 'shipped'    ? 'bg-primary' : '' ?>
                                            <?= $product['status'] === 'delivered'  ? 'bg-success' : '' ?>
                                            <?= $product['status'] === 'cancelled'  ? 'bg-danger' : '' ?>
                                        ">
                                            <?= $data['position_statuses'][$product['status']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary update-position-status-btn" 
                                                data-position-id="<?= $product['id'] ?>" 
                                                title="Изменить статус">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-active">
                                    <td colspan="3" class="text-end"><strong>Итого:</strong></td>
                                    <td colspan="3"><strong><?= number_format($data['order']['sum'], 2, '.', ' ') ?> ₽</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
                            <?php foreach ($data['position_statuses'] as $value => $label): ?>
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
<link rel="stylesheet" href="/assets/css/pages/orders.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Обработка выбора товара
    $('#productSelect').change(function() {
        const selectedOption = $(this).find('option:selected');
        const stock = selectedOption.data('stock') || 0;
        $('#stockInfo').text('Доступно: ' + stock);
        $('#productCount').attr('max', stock);
    });

    // Обработка клика по кнопке изменения статуса позиции
    $('.update-position-status-btn').click(function() {
        const positionId = $(this).data('position-id');
        $('#positionId').val(positionId);
        $('#statusModal').modal('show');
    });

    // Сохранение статуса позиции
    $('#saveStatusBtn').click(function() {
        const formData = $('#statusForm').serialize();
        const positionId = $('#positionId').val();
        
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