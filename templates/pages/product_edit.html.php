<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="crm-container">
            <div class="container-fluid py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Редактирование товара</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/products">Товары</a></li>
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
                            <form id="editProductForm" action="/api/product/<?= $data['product']['id'] ?>/edit" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Название*</label>
                                        <input type="text" class="form-control" name="name" 
                                               value="<?= $this->escape($data['product']['name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Описание</label>
                                        <textarea class="form-control" name="description" rows="4"><?= $this->escape($data['product']['description']) ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Цена*</label>
                                            <input type="number" step="0.01" class="form-control" name="price" 
                                                   value="<?= $this->escape($data['product']['price']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Категории</label>
                                        <select class="form-select" name="categories[]" multiple>
                                            <?php foreach ($data['categories'] as $category): ?>
                                                <option value="<?= $category['id'] ?>" 
                                                    <?= in_array($category['id'], $data['selectedCategories']) ? 'selected' : '' ?>>
                                                    <?= $this->escape($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Для выбора нескольких категорий удерживайте Ctrl (Windows) или Command (Mac)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Главное изображение</label>
                                        <?php if (!empty($data['product']['image_main'])): ?>
                                        <div class="mb-2">
                                            <div class="position-relative" style="width: 200px;">
                                                <img src="/uploads/images/products/main/<?= $this->escape($data['product']['image_main']) ?>" 
                                                     alt="Текущее изображение" 
                                                     class="img-thumbnail cursor-zoom"
                                                     style="max-height: 200px;"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#imageModal"
                                                     data-img-src="/uploads/images/products/main/<?= $this->escape($data['product']['image_main']) ?>">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                                                    <label class="form-check-label" for="remove_image">Удалить изображение</label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" name="image_main" accept="image/jpeg, image/png, image/webp">
                                        <small class="text-muted">Максимальный размер: 5MB. Допустимые форматы: JPEG, PNG, WebP</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Галерея изображений</label>

                                        <?php if (!empty($data['product']['gallery_path'])): 
                                            $galleryPath = "uploads/images/products/gallery/{$data['product']['gallery_path']}";
                                            $galleryFiles = is_dir($galleryPath) ? array_diff(scandir($galleryPath), ['.', '..']) : [];
                                            
                                            if (!empty($galleryFiles)): ?>
                                            <div class="mb-3">
                                                <h6>Текущие изображения:</h6>
                                                
                                                <!-- Слайдер галереи -->
                                                <div id="gallerySlider" class="carousel slide mb-3" data-bs-ride="carousel">
                                                    <div class="carousel-inner">
                                                        <?php $i = 0; ?>
                                                        <?php foreach ($galleryFiles as $index => $filename): 
                                                            if (!is_dir("$galleryPath/$filename")): ?>
                                                                <div class="carousel-item">
                                                                    <img src="/<?= $this->escape($galleryPath) ?>/<?= $this->escape($filename) ?>" 
                                                                         class="d-block w-100 img-thumbnail" 
                                                                         style="max-height: 400px; object-fit: contain;">
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php if (count($galleryFiles) > 1): ?>
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#gallerySlider" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                            <span class="visually-hidden">Previous</span>
                                                        </button>
                                                        <button class="carousel-control-next" type="button" data-bs-target="#gallerySlider" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                            <span class="visually-hidden">Next</span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Миниатюры для навигации -->
                                                <div class="row g-2 mb-3" id="galleryThumbs">
                                                    <?php foreach ($galleryFiles as $index => $filename): 
                                                        if (!is_dir("$galleryPath/$filename")): ?>
                                                            <div class="col-2 position-relative gallery-thumb" data-bs-target="#gallerySlider" data-bs-slide-to="<?= $index ?>">
                                                                <img src="/<?= $this->escape($galleryPath) ?>/<?= $this->escape($filename) ?>" 
                                                                     class="img-thumbnail w-100" style="height: 80px; object-fit: cover;">
                                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-image-btn">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                                        
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" name="remove_gallery" id="removeGallery">
                                                    <label class="form-check-label" for="removeGallery">Удалить всю галерею</label>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                                        
                                        <div class="mb-3">
                                            <label for="galleryUpload" class="form-label">Добавить новые изображения:</label>
                                            <input type="file" class="form-control" id="galleryUpload" name="gallery[]" multiple 
                                                   accept="image/jpeg, image/png, image/webp">
                                            <div id="galleryFilesList" class="mt-2 small text-muted"></div>
                                        </div>
                                        <small class="text-muted">Можно выбрать несколько файлов (до 10). Максимальный размер каждого: 5MB</small>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="/products" class="btn btn-secondary me-2">Отмена</a>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<!-- Модальное окно для увеличенного просмотра изображений -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" style="max-height: 80vh;">
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
    // Инициализация модального окна для просмотра изображений
    $('#imageModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const imageUrl = button.data('img-src');
        $('#modalImage').attr('src', imageUrl);
    });

    // Обработка загрузки файлов
    $('#galleryUpload').change(function() {
        let files = this.files;
        let list = $('#galleryFilesList');
        list.empty();
        
        if (files.length > 10) {
            list.html('<span class="text-danger">Можно загрузить не более 10 файлов</span>');
            this.value = '';
            return;
        }
        
        if (files.length > 0) {
            list.html('Выбрано файлов: ' + files.length);
        } else {
            list.html('Файлы не выбраны');
        }
    });

    // Удаление отдельных изображений
    $(document).on('click', '.remove-image-btn', function(e) {
        e.stopPropagation(); // Предотвращаем срабатывание клика по миниатюре
        let imageBlock = $(this).closest('.gallery-thumb');
        let filename = imageBlock.find('img').attr('src').split('/').pop();
        
        if (confirm('Удалить это изображение из галереи?')) {
            $.ajax({
                url: `/api/product/<?= $data['product']['id'] ?>/remove_gallery_image`,
                method: 'POST',
                data: JSON.stringify({ filename: filename }),
                contentType: 'application/json',
                success: function(response) {
                    // Перезагрузка страницы после удаления
                    location.reload();
                },
                error: function() {
                    alert('Произошла ошибка');
                }
            });
        }
    });

    // Подсветка активной миниатюры
    $('#gallerySlider').on('slid.bs.carousel', function () {
        const activeIndex = $(this).find('.carousel-item.active').index();
        $('.gallery-thumb').removeClass('active').eq(activeIndex).addClass('active');
    });

    // Инициализация активной миниатюры при загрузке
    $('.carousel-item').first().addClass('active');
});
</script>
<?php $this->endSection() ?>