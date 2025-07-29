<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Редактирование приёмки</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/acceptances">Приёмки</a></li>
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
                                <form id="editAcceptanceForm" action="/api/acceptance/<?= $data['acceptance']['id'] ?>/edit" method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                        <label class="form-label">Компания*</label>
                                        <select class="form-select" name="company_id" required>
                                            <?php foreach ($data['companies'] as $company): ?>
                                                <option value="<?= $company['id'] ?>" 
                                                    <?= $company['id'] == $data['acceptance']['company_id'] ? 'selected' : '' ?>>
                                                    <?= $this->escape($company['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Продукт*</label>
                                            <select class="form-select" name="product_id" required>
                                                <?php foreach ($data['products'] as $product): ?>
                                                    <option value="<?= $product['id'] ?>" 
                                                        <?= $product['id'] == $data['acceptance']['product_id'] ? 'selected' : '' ?>>
                                                        <?= $this->escape($product['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Количество*</label>
                                            <input type="number" class="form-control" name="count" 
                                                   value="<?= $this->escape($data['acceptance']['count']) ?>" min="1" required>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="/acceptances" class="btn btn-secondary me-2">Отмена</a>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                    <input type="hidden" class="form-control" name="count_old" value="<?= $this->escape($data['acceptance']['count']) ?>" min="1" required>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/acceptances.css">
<?php $this->endSection() ?>