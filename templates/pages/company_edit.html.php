<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="crm-container">
            <div class="container-fluid py-4">
                <!-- Profile Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Редактирование компании</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item"><a href="/companies">Компании</a></li>
                                <li class="breadcrumb-item active">Редактирование</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <!-- Edit Form -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Основная информация</h5>
                            </div>
                            <div class="card-body">
                                <form id="editCompanyForm" action="/api/company/<?= $data['company']['id'] ?>/update" method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Название*</label>
                                            <input type="text" class="form-control" name="name" 
                                                   value="<?= $this->escape($data['company']['name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Полное название*</label>
                                            <input type="text" class="form-control" name="full_name" 
                                                   value="<?= $this->escape($data['company']['full_name']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">ИНН*</label>
                                            <input type="text" class="form-control" name="INN" 
                                                   value="<?= $this->escape($data['company']['INN']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ОГРН*</label>
                                            <input type="text" class="form-control" name="OGRN" 
                                                   value="<?= $this->escape($data['company']['OGRN']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Адрес*</label>
                                            <input type="text" class="form-control" name="address" 
                                                   value="<?= $this->escape($data['company']['address']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Описание</label>
                                            <textarea class="form-control" name="description" rows="3"><?= $this->escape($data['company']['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="/companies" class="btn btn-secondary me-2">Отмена</a>
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
<link rel="stylesheet" href="/assets/css/pages/companies.css">
<?php $this->endSection() ?>