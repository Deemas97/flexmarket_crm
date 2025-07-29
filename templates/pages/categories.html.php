<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Categories Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Управление категориями</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Категории</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-2"></i>Добавить категорию
                        </button>
                    </div>
                </div>
                
                <!-- Categories Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список категорий</h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control search" placeholder="Поиск категорий...">
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
                                        <th>Название</th>
                                        <th>Изображение</th>
                                        <th>Родительская категория</th>
                                        <th>Описание</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['categories'] as $category): ?>
                                    <tr>
                                        <td><?= $this->escape($category['id']) ?></td>
                                        <td><?= $this->escape($category['name']) ?></td>
                                        <td>
                                            <?php if (!empty($category['image'])): ?>
                                            <img src="/uploads/images/categories/<?= $this->escape($category['image']) ?>" 
                                                 alt="<?= $this->escape($category['name']) ?>" style="max-height: 50px;">
                                            <?php else: ?>
                                            <span class="text-muted">Нет</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($category['parent_category_id']): ?>
                                                <?= $this->escape($data['categoriesMap'][$category['parent_category_id']]['name'] ?? 'Неизвестно') ?>
                                            <?php else: ?>
                                                <span class="text-muted">Нет</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $this->escape(mb_substr($category['description'], 0, 50)) ?><?= mb_strlen($category['description']) > 50 ? '...' : '' ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/category/<?= $category['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger delete-category" data-id="<?= $category['id'] ?>" title="Удалить">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить категорию</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm" action="/api/category/create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Название*</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Родительская категория</label>
                        <select class="form-select" name="parent_category_id">
                            <option value="">Нет</option>
                            <?php foreach ($data['categories'] as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= $this->escape($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
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
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить эту категорию?</p>
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
<link rel="stylesheet" href="/assets/css/pages/categories.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Обработка удаления категории
    $('.delete-category').click(function() {
        const categoryId = $(this).data('id');
        $('#deleteCategoryModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: '/api/category/' + categoryId + '/delete',
                method: 'DELETE',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.error || 'Ошибка при удалении категории');
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