<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Редактирование категории</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/categories">Категории</a></li>
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
                                <form id="editCategoryForm" action="/api/category/<?= $data['category']['id'] ?>/edit" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Название*</label>
                                        <input type="text" class="form-control" name="name" 
                                               value="<?= $this->escape($data['category']['name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Изображение</label>
                                        <?php if (!empty($data['category']['image'])): ?>
                                        <div class="mb-2">
                                            <div class="position-relative" style="width: 200px;">
                                                <img src="/uploads/categories/<?= $this->escape($data['category']['image']) ?>" 
                                                     alt="Текущее изображение" 
                                                     class="img-thumbnail cursor-zoom"
                                                     style="max-height: 200px;"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#imageModal"
                                                     data-img-src="/uploads/categories/<?= $this->escape($data['category']['image']) ?>">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage">
                                                    <label class="form-check-label" for="removeImage">Удалить изображение</label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" name="image" accept="image/jpeg, image/png, image/webp">
                                        <small class="text-muted">Максимальный размер: 5MB. Допустимые форматы: JPEG, PNG, WebP</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Родительская категория</label>
                                        <select class="form-select" name="parent_category_id">
                                            <option value="">Нет</option>
                                            <?php foreach ($data['categories'] as $cat): ?>
                                                <?php if ($cat['id'] != $data['category']['id']): ?>
                                                    <option value="<?= $cat['id'] ?>" 
                                                        <?= $cat['id'] == $data['category']['parent_category_id'] ? 'selected' : '' ?>>
                                                        <?= $this->escape($cat['name']) ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Описание</label>
                                        <textarea class="form-control" name="description" rows="4"><?= $this->escape($data['category']['description']) ?></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="/categories" class="btn btn-secondary me-2">Отмена</a>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/categories.css">
<?php $this->endSection() ?>