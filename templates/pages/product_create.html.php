<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Создание товара</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/products">Товары</a></li>
                                <li class="breadcrumb-item active">Создание</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <!-- Create Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Основная информация</h5>
                            </div>
                            <div class="card-body">
                            <form id="createProductForm" action="/api/product/create" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Название*</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Описание</label>
                                        <textarea class="form-control" name="description" rows="4"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Цена*</label>
                                            <input type="number" step="0.01" class="form-control" name="price" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Категории</label>
                                        <select class="form-select" name="categories[]" multiple>
                                            <?php foreach ($data['categories'] as $category): ?>
                                                <option value="<?= $category['id'] ?>">
                                                    <?= $this->escape($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Для выбора нескольких категорий удерживайте Ctrl</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Главное изображение</label>
                                        <input type="file" class="form-control" name="image_main" accept="image/jpeg, image/png, image/webp">
                                        <small class="text-muted">Максимальный размер: 5MB. Допустимые форматы: JPEG, PNG, WebP</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Галерея изображений</label>
                                        <input type="file" class="form-control" name="gallery[]" multiple accept="image/jpeg, image/png, image/webp">
                                        <small class="text-muted">Можно выбрать несколько файлов. Максимальный размер каждого: 5MB</small>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="/products" class="btn btn-secondary me-2">Отмена</a>
                                        <button type="submit" class="btn btn-primary">Создать</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/products.css">
<?php $this->endSection() ?>