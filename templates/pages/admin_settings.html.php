<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Настройки платформы</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Основные настройки</h6>
        </div>
        <div class="card-body">
            <form id="platformSettingsForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="platformName" class="form-label">Название платформы</label>
                        <input type="text" class="form-control" id="platformName" 
                               name="platform_name" value="<?= $data['settings']['APP_NAME'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="adminEmail" class="form-label">Email администратора</label>
                        <input type="email" class="form-control" id="adminEmail" 
                               name="admin_email" value="<?= $data['settings']['ADMIN_EMAIL'] ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="timezone" class="form-label">Часовой пояс</label>
                        <select class="form-select" id="timezone" name="timezone">
                            <?php foreach ($data['timezones'] as $tz): ?>
                                <option value="<?= $tz ?>" <?= $tz === $data['settings']['TIMEZONE'] ? 'selected' : '' ?>>
                                    <?= $tz ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="maintenanceMode" class="form-label">Режим обслуживания</label>
                        <select class="form-select" id="maintenanceMode" name="maintenance_mode">
                            <option value="0" <?= !$data['settings']['MAINTENANCE_MODE'] ? 'selected' : '' ?>>Выключен</option>
                            <option value="1" <?= $data['settings']['MAINTENANCE_MODE'] ? 'selected' : '' ?>>Включен</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="itemsPerPage" class="form-label">Элементов на странице</label>
                        <input type="number" class="form-control" id="itemsPerPage" 
                               name="items_per_page" value="<?= $data['settings']['ITEMS_PER_PAGE'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="defaultCurrency" class="form-label">Валюта по умолчанию</label>
                        <select class="form-select" id="defaultCurrency" name="default_currency">
                            <?php foreach ($data['currencies'] as $code => $name): ?>
                                <option value="<?= $code ?>" <?= $code === $data['settings']['DEFAULT_CURRENCY'] ? 'selected' : '' ?>>
                                    <?= $name ?> (<?= $code ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" id="saveSettingsBtn">
                            Сохранить настройки
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mt-4">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold text-dark">Опасная зона</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Кэш платформы</h5>
                    <p>Очистка кэша может временно увеличить нагрузку на сервер</p>
                    <button class="btn btn-outline-danger" id="clearCacheBtn">
                        Очистить кэш
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Сохранение основных настроек
    $('#platformSettingsForm').submit(function(e) {
        e.preventDefault();
        const btn = $('#saveSettingsBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Сохранение...');

        $.post('/api/settings/edit', $(this).serialize(), function(response) {
            if (response.success) {
                showAlert('success', 'Настройки успешно сохранены');
            } else {
                showAlert('danger', response.error || 'Ошибка при сохранении настроек');
            }
        }).fail(function() {
            showAlert('danger', 'Ошибка сервера при сохранении настроек');
        }).always(function() {
            btn.prop('disabled', false).html('Сохранить настройки');
        });
    });

    // Очистка кэша
    $('#clearCacheBtn').click(function() {
        if (confirm('Вы уверены, что хотите очистить кэш платформы?')) {
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Очистка...');

            $.post('/api/settings/clear_cache', {}, function(response) {
                if (response.success) {
                    showAlert('success', 'Кэш успешно очищен');
                } else {
                    showAlert('danger', response.error || 'Ошибка при очистке кэша');
                }
            }).always(function() {
                btn.prop('disabled', false).html('Очистить кэш');
            });
        }
    });

    function showAlert(type, message) {
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        $('#platformSettingsForm').after(alert);
        setTimeout(() => alert.alert('close'), 5000);
    }
});
</script>
<?php $this->endSection() ?>