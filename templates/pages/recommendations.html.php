<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Управление рекомендациями</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Рекомендации</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="recommendationsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="global-tab" data-bs-toggle="tab" 
                                data-bs-target="#global-tab-pane" type="button" role="tab" 
                                aria-controls="global-tab-pane" aria-selected="true">
                            Глобальные рекомендации
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="personal-tab" data-bs-toggle="tab" 
                                data-bs-target="#personal-tab-pane" type="button" role="tab" 
                                aria-controls="personal-tab-pane" aria-selected="false">
                            Персонализированные рекомендации
                        </button>
                    </li>
                </ul>
                
                <!-- Tabs Content -->
                <div class="tab-content" id="recommendationsTabsContent">
                    <!-- Global Recommendations Tab -->
                    <div class="tab-pane fade show active" id="global-tab-pane" role="tabpanel" 
                         aria-labelledby="global-tab" tabindex="0">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Глобальные рекомендации</h5>
                                <div class="input-group" style="width: 300px;">
                                    <input type="text" class="form-control global-search" placeholder="Поиск...">
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
                                                <th>Рейтинг</th>
                                                <th>Дата создания</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['global_recommendations'] as $rec): ?>
                                            <tr>
                                                <td><?= $this->escape($rec['id']) ?></td>
                                                <td><?= $this->escape($rec['product_name']) ?></td>
                                                <td><?= number_format($rec['recommendation_score'], 5) ?></td>
                                                <td><?= date('d.m.Y', strtotime($rec['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary edit-recommendation" 
                                                                data-id="<?= $rec['id'] ?>"
                                                                data-type="global"
                                                                data-recommendation_score="<?= $rec['recommendation_score'] ?>"
                                                                data-product="<?= $this->escape($rec['product_name']) ?>"
                                                                title="Редактировать">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger delete-recommendation" 
                                                                data-id="<?= $rec['id'] ?>"
                                                                data-type="global"
                                                                data-product="<?= $this->escape($rec['product_name']) ?>"
                                                                title="Удалить">
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
                    
                    <!-- Personal Recommendations Tab -->
                    <div class="tab-pane fade" id="personal-tab-pane" role="tabpanel" 
                         aria-labelledby="personal-tab" tabindex="0">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Персонализированные рекомендации</h5>
                                <div class="input-group" style="width: 300px;">
                                    <input type="text" class="form-control personal-search" placeholder="Поиск...">
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
                                                <th>Клиент</th>
                                                <th>Товар</th>
                                                <th>Рейтинг</th>
                                                <th>Дата создания</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['personal_recommendations'] as $rec): ?>
                                            <tr>
                                                <td><?= $this->escape($rec['id']) ?></td>
                                                <td><?= $this->escape($rec['customer_i'] . ' ' . $rec['customer_f']) ?></td>
                                                <td><?= $this->escape($rec['product_name']) ?></td>
                                                <td><?= number_format($rec['recommendation_score'], 5) ?></td>
                                                <td><?= date('d.m.Y', strtotime($rec['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary edit-recommendation" 
                                                                data-id="<?= $rec['id'] ?>"
                                                                data-type="personal"
                                                                data-recommendation_score="<?= $rec['recommendation_score'] ?>"
                                                                data-product="<?= $this->escape($rec['product_name']) ?>"
                                                                data-customer="<?= $this->escape($rec['customer_i'] . ' ' . $rec['customer_f']) ?>"
                                                                title="Редактировать">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger delete-recommendation" 
                                                                data-id="<?= $rec['id'] ?>"
                                                                data-type="personal"
                                                                data-product="<?= $this->escape($rec['product_name']) ?>"
                                                                data-customer="<?= $this->escape($rec['customer_i'] . ' ' . $rec['customer_f']) ?>"
                                                                title="Удалить">
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
                </div>
            </div>

<!-- Edit Recommendation Modal -->
<div class="modal fade" id="editRecommendationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование рекомендации</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRecommendationForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="recommendationId" name="id">
                    <input type="hidden" id="recommendationType" name="type">
                    
                    <div class="mb-3">
                        <label class="form-label">Товар</label>
                        <input type="text" class="form-control" id="recommendationProduct" readonly>
                    </div>
                    
                    <div class="mb-3" id="customerFieldContainer">
                        <label class="form-label">Клиент</label>
                        <input type="text" class="form-control" id="recommendationCustomer" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Рейтинг*</label>
                        <input type="number" step="0.00001" class="form-control" id="recommendationScore" name="recommendation_score" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteRecommendationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить рекомендацию для товара <strong id="deleteProductName"></strong>?</p>
                <p id="deleteCustomerInfo" class="mb-0"></p>
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
<link rel="stylesheet" href="/assets/css/pages/recommendations.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Инициализация вкладок
    const recommendationsTab = new bootstrap.Tab(document.getElementById('global-tab'));
    recommendationsTab.show();
    
    // Обработка редактирования рекомендации
    $('.edit-recommendation').click(function() {
        const id = $(this).data('id');
        const type = $(this).data('type');
        const recommendation_score = $(this).data('recommendation_score');
        const product = $(this).data('product');
        const customer = $(this).data('customer');
        
        $('#recommendationId').val(id);
        $('#recommendationType').val(type);
        $('#recommendationProduct').val(product);
        $('#recommendationScore').val(recommendation_score);
        
        if (type === 'personal') {
            $('#customerFieldContainer').show();
            $('#recommendationCustomer').val(customer);
        } else {
            $('#customerFieldContainer').hide();
        }
        
        $('#editRecommendationModal').modal('show');
    });
    
    $('#editRecommendationForm').submit(function(e) {
        e.preventDefault();
        
        const id = $('#recommendationId').val();
        const type = $('#recommendationType').val();
        const url = type === 'global' 
            ? '/api/recommendation/' + id + '/edit' 
            : '/api/personal_recommendation/' + id + '/edit';
        
        // Собираем данные формы
        const formData = {
            recommendation_score: $('#recommendationScore').val()
        };
        
        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function() {
                location.reload();
            },
            error: function(xhr) {
                let errorMsg = 'Ошибка при обновлении рекомендации';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                alert(errorMsg);
            }
        });
    });
    
    // Обработка удаления рекомендации
    $('.delete-recommendation').click(function() {
        const id = $(this).data('id');
        const type = $(this).data('type');
        const product = $(this).data('product');
        const customer = $(this).data('customer');
        
        $('#deleteProductName').text(product);
        if (type === 'personal') {
            $('#deleteCustomerInfo').html('<strong>Клиент:</strong> ' + customer);
        } else {
            $('#deleteCustomerInfo').html('<em>Глобальная рекомендация</em>');
        }
        
        $('#deleteRecommendationModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            const url = type === 'global' 
                ? '/api/recommendation/' + id + '/delete' 
                : '/api/personal_recommendation/' + id + '/delete';
            
            $.ajax({
                url: url,
                method: 'DELETE',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.error || 'Ошибка при удалении рекомендации');
                }
            });
        });
    });
    
    // Поиск в таблицах
    $('.global-search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#global-tab-pane table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    $('.personal-search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#personal-tab-pane table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
<?php $this->endSection() ?>