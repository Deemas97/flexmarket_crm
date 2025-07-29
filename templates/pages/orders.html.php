<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="crm-container">
            <div class="container-fluid py-4">
                <!-- Orders Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Управление заказами</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Заказы</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <div class="input-group">
                            <select class="form-select" id="statusFilter">
                                <option value="">Все статусы</option>
                                <?php foreach ($data['statuses'] as $value => $label): ?>
                                    <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список заказов</h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control search" placeholder="Поиск заказов...">
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
                                        <th>Клиент</th>
                                        <th>Сумма</th>
                                        <th>Дата создания</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['orders'] as $order): ?>
                                    <tr>
                                        <td><?= $this->escape($order['id']) ?></td>
                                        <td>
                                            <div><a href="/customer/<?= $this->escape($order['customer_id']) ?>/edit"><?= $this->escape($order['customer_i'] . ' ' . $order['customer_f']) ?></a></div>
                                            <small class="text-muted"><?= $this->escape($order['customer_email']) ?></small>
                                        </td>
                                        <td><?= number_format($order['sum'], 2, '.', ' ') ?> ₽</td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <span class="badge 
                                                <?= $order['status'] === 'pending'    ? 'bg-warning' : '' ?>
                                                <?= $order['status'] === 'processing' ? 'bg-info' : '' ?>
                                                <?= $order['status'] === 'completed'  ? 'bg-success' : '' ?>
                                                <?= $order['status'] === 'cancelled'  ? 'bg-danger' : '' ?>
                                            ">
                                                <?= $data['statuses'][$order['status']] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/order/<?= $order['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
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
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/orders.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Фильтрация по статусу
    $('#statusFilter').change(function() {
        const status = $(this).val();
        window.location.href = '/orders' + (status ? '?status=' + status : '');
    });

    // Установка выбранного статуса из URL
    const urlParams = new URLSearchParams(window.location.search);
    const statusParam = urlParams.get('status');
    if (statusParam) {
        $('#statusFilter').val(statusParam);
    }
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