<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Reviews Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Управление отзывами</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Отзывы</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <!-- Reviews Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список отзывов</h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control search" placeholder="Поиск отзывов...">
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
                                        <th>Клиент</th>
                                        <th>Рейтинг</th>
                                        <th>Комментарий</th>
                                        <th>Дата</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['reviews'] as $review): ?>
                                    <tr>
                                        <td><?= $this->escape($review['id']) ?></td>
                                        <td><a href="/product/<?= $this->escape($review['product_id']) ?>/edit"><?= $this->escape($review['product_name']) ?></a></td>
                                        <td><a href="/customer/<?= $this->escape($review['customer_id']) ?>/edit"><?= $this->escape($review['customer_i'] . ' ' . $review['customer_f']) ?></a></td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?= $i > $review['rating'] ? '-empty' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($review['comment'])): ?>
                                                <?= $this->escape(mb_substr($review['comment'], 0, 50)) ?>
                                                <?= mb_strlen($review['comment']) > 50 ? '...' : '' ?>
                                            <?php else: ?>
                                                <span class="text-muted">Нет комментария</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($review['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/review/<?= $review['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger delete-review" data-id="<?= $review['id'] ?>" title="Удалить">
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
<div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этот отзыв?</p>
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
<link rel="stylesheet" href="/assets/css/pages/reviews.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Обработка удаления отзыва
    $('.delete-review').click(function() {
        const reviewId = $(this).data('id');
        $('#deleteReviewModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: '/api/review/' + reviewId + '/delete',
                method: 'DELETE',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.error || 'Ошибка при удалении отзыва');
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