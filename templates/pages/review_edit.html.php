<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Редактирование отзыва #<?= $data['review']['id'] ?></h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/reviews">Отзывы</a></li>
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
                                <h5 class="mb-0">Информация об отзыве</h5>
                            </div>
                            <div class="card-body">
                                <form id="editReviewForm" action="/api/review/<?= $data['review']['id'] ?>/edit" method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Товар</label>
                                            <input type="text" class="form-control" 
                                                   value="<?= $this->escape($data['review']['product_name']) ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Клиент</label>
                                            <input type="text" class="form-control" 
                                                   value="<?= $this->escape($data['review']['customer_i'] . ' ' . $data['review']['customer_f']) ?>" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Рейтинг*</label>
                                        <select class="form-select" name="rating" required>
                                            <option value="0" <?= $data['review']['rating'] == 0 ? 'selected' : '' ?>>0 звезд</option>
                                            <option value="1" <?= $data['review']['rating'] == 1 ? 'selected' : '' ?>>1 звезда</option>
                                            <option value="2" <?= $data['review']['rating'] == 2 ? 'selected' : '' ?>>2 звезды</option>
                                            <option value="3" <?= $data['review']['rating'] == 3 ? 'selected' : '' ?>>3 звезды</option>
                                            <option value="4" <?= $data['review']['rating'] == 4 ? 'selected' : '' ?>>4 звезды</option>
                                            <option value="5" <?= $data['review']['rating'] == 5 ? 'selected' : '' ?>>5 звезд</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Комментарий</label>
                                        <textarea class="form-control" name="comment" rows="4"><?= $this->escape($data['review']['comment'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Дата создания</label>
                                        <input type="text" class="form-control" 
                                               value="<?= date('d.m.Y H:i', strtotime($data['review']['created_at'])) ?>" readonly>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <a href="/reviews" class="btn btn-secondary me-2">Отмена</a>
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
<link rel="stylesheet" href="/assets/css/pages/reviews.css">
<?php $this->endSection() ?>