<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Products Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Управление товарами</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Товары</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <a href="/product/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Добавить товар
                        </a>
                    </div>
                </div>
                
                <!-- Products Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список товаров</h5>
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
                                        <th>Компания</th>
                                        <th>Изображение</th>
                                        <th>Название</th>
                                        <th>Цена</th>
                                        <th>Количество</th>
                                        <th>Категории</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['products'] as $product): ?>
                                    <tr>
                                        <td><?= $this->escape($product['id']) ?></td>
                                        <td><a href="/company/<?= $product['company_id'] ?>/edit"><?= $this->escape($product['company_name']) ?></a></td>
                                        <td>
                                            <?php if (!empty($product['image_main'])): ?>
                                            <img src="/uploads/images/products/main/<?= $this->escape($product['image_main']) ?>" 
                                                 alt="<?= $this->escape($product['name']) ?>" style="max-height: 50px;">
                                            <?php else: ?>
                                            <span class="text-muted">Нет</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $this->escape($product['name']) ?></td>
                                        <td><?= $this->escape(number_format($product['price'], 2, '.', ' ')) ?> ₽</td>
                                        <td><?= $this->escape($product['stock_quantity']) ?></td>
                                        <td><?= $this->escape($product['categories_names'] ?? 'Без категории') ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/product/<?= $product['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger delete-product" data-id="<?= $product['id'] ?>" title="Удалить">
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этот товар?</p>
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
<link rel="stylesheet" href="/assets/css/pages/products.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Обработка удаления товара
    $('.delete-product').click(function() {
        const productId = $(this).data('id');
        $('#deleteProductModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: '/api/product/' + productId + '/delete',
                method: 'DELETE',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.error || 'Ошибка при удалении товара');
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