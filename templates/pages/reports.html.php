<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Reports Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Отчеты</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Отчеты</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" id="generateReportBtn">
                            <i class="fas fa-download me-2"></i>Сформировать отчет
                        </button>
                    </div>
                </div>
                
                <!-- Reports Content -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Доступные отчеты</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                    id="reportTypeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Тип отчета
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="reportTypeDropdown">
                                <li><a class="dropdown-item" href="#" data-type="sales">Отчет по продажам</a></li>
                                <li><a class="dropdown-item" href="#" data-type="products">Отчет по товарам</a></li>
                                <li><a class="dropdown-item" href="#" data-type="customers">Отчет по клиентам</a></li>
                                <li><a class="dropdown-item" href="#" data-type="inventory">Отчет по складу</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="reportPeriod" class="form-label">Период</label>
                                <select class="form-select" id="reportPeriod">
                                    <option value="30days">Последние 30 дней</option>
                                    <option value="3months">3 месяца</option>
                                    <option value="6months">6 месяцев</option>
                                    <option value="12months">12 месяцев</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="reportFormat" class="form-label">Формат</label>
                                <select class="form-select" id="reportFormat">
                                    <option value="csv">CSV</option>
                                    <option value="txt">TXT</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Название отчета</th>
                                        <th>Тип</th>
                                        <th>Дата создания</th>
                                        <th>Размер</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="reportsTable"> </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/reports.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    let currentReportType = 'sales';
    let currentPeriod = '30days';
    let currentFormat = 'csv';

    // Load existing reports
    function loadReports() {
        $.get('/api/reports/get_list', function(response) {
            if (response.reports && response.reports.length > 0) {
                updateReportsTable(response.reports);
            } else {
                $('#reportsTable').html('<tr><td colspan="5" class="text-center">Нет доступных отчетов</td></tr>');
            }
        }).fail(function() {
            $('#reportsTable').html('<tr><td colspan="5" class="text-center text-danger">Ошибка загрузки отчетов</td></tr>');
        });
    }

    // Update reports table
    function updateReportsTable(reports) {
        let html = '';
        reports.forEach(report => {
            html += `
                <tr>
                    <td>${report.name}</td>
                    <td>${report.type}</td>
                    <td>${report.created_at}</td>
                    <td>${formatFileSize(report.size)}</td>
                    <td>
                        <a href="/api/report/${report.id}/download" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-1"></i>Скачать
                        </a>
                        <button class="btn btn-sm btn-outline-danger delete-report" data-id="${report.id}">
                            <i class="fas fa-trash me-1"></i>Удалить
                        </button>
                    </td>
                </tr>
            `;
        });
        $('#reportsTable').html(html);
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Event handlers
    $('[data-type]').click(function(e) {
        e.preventDefault();
        currentReportType = $(this).data('type');
        $('#reportTypeDropdown').text($(this).text());
    });

    $('#reportPeriod').change(function() {
        currentPeriod = $(this).val();
    });

    $('#reportFormat').change(function() {
        currentFormat = $(this).val();
    });

    $('#generateReportBtn').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Формирование...');
        
        $.post('/api/report/create', {
            type: currentReportType,
            period: currentPeriod,
            format: currentFormat
        }, function(response) {
            if (response.success) {
                loadReports();
                showAlert('success', 'Отчет успешно сформирован');
            } else {
                showAlert('danger', response.error || 'Ошибка при формировании отчета');
            }
        }).fail(function() {
            showAlert('danger', 'Ошибка сервера при формировании отчета');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-download me-2"></i>Сформировать отчет');
        });
    });

    $(document).on('click', '.delete-report', function() {
        const reportId = $(this).data('id');
        if (confirm('Вы уверены, что хотите удалить этот отчет?')) {
            $.ajax({
                url: `/api/report/${reportId}/delete`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                        showAlert('success', 'Отчет успешно удален');
                    } else {
                        showAlert('danger', response.error || 'Ошибка при удалении отчета');
                    }
                }
            });
        }
    });

    // Show alert message
    function showAlert(type, message) {
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        $('.card-body').prepend(alert);
        setTimeout(() => alert.alert('close'), 5000);
    }

    // Initial load
    loadReports();
});
</script>
<?php $this->endSection() ?>