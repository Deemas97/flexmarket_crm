<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Statistics Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Статистика</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                                <li class="breadcrumb-item active">Статистика</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <select class="form-select" id="statsPeriod">
                            <option value="30days">Последние 30 дней</option>
                            <option value="3months">3 месяца</option>
                            <option value="6months">6 месяцев</option>
                            <option value="12months">12 месяцев</option>
                        </select>
                    </div>
                </div>
                
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="statsTabs" role="tablist">
                    <?php foreach ($data['tabs'] as $tabId => $tabName): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $data['active_tab'] === $tabId ? 'active' : '' ?>" 
                                id="<?= $tabId ?>-tab" data-bs-toggle="tab" 
                                data-bs-target="#<?= $tabId ?>" type="button" 
                                role="tab" aria-controls="<?= $tabId ?>" 
                                aria-selected="<?= $data['active_tab'] === $tabId ? 'true' : 'false' ?>">
                            <?= $tabName ?>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <!-- Tabs Content -->
                <div class="tab-content" id="statsTabsContent">
                    <!-- Sales Tab -->
                    <div class="tab-pane fade <?= $data['active_tab'] === 'sales' ? 'show active' : '' ?>" 
                         id="sales" role="tabpanel" aria-labelledby="sales-tab">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Динамика продаж</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-area">
                                            <canvas id="salesChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Показатели</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Период</th>
                                                        <th>Заказы</th>
                                                        <th>Выручка</th>
                                                        <th>Ср. чек</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="salesStatsTable"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Products Tab -->
                    <div class="tab-pane fade <?= $data['active_tab'] === 'products' ? 'show active' : '' ?>" 
                         id="products" role="tabpanel" aria-labelledby="products-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Топ товаров</h6>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                            id="productsSortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Сортировка
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="productsSortDropdown">
                                        <li><a class="dropdown-item" href="#" data-sort="total_sold">По количеству продаж</a></li>
                                        <li><a class="dropdown-item" href="#" data-sort="total_revenue">По выручке</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Товар</th>
                                                <th>Компания</th>
                                                <th>Продано</th>
                                                <th>Выручка</th>
                                                <th>Заказов</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productsStatsTable"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customers Tab -->
                    <div class="tab-pane fade <?= $data['active_tab'] === 'customers' ? 'show active' : '' ?>" 
                         id="customers" role="tabpanel" aria-labelledby="customers-tab">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Топ клиентов</h6>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                            id="customersSortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Сортировка
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="customersSortDropdown">
                                        <li><a class="dropdown-item" href="#" data-sort="total_spent">По сумме заказов</a></li>
                                        <li><a class="dropdown-item" href="#" data-sort="orders_count">По количеству заказов</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Клиент</th>
                                                <th>Тип</th>
                                                <th>Заказов</th>
                                                <th>Сумма</th>
                                            </tr>
                                        </thead>
                                        <tbody id="customersStatsTable"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Inventory Tab -->
                    <div class="tab-pane fade <?= $data['active_tab'] === 'inventory' ? 'show active' : '' ?>" 
                         id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Остатки на складе</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-bar">
                                            <canvas id="inventoryChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Товары с низким запасом</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Товар</th>
                                                        <th>Остаток</th>
                                                        <th>Продано</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="inventoryStatsTable"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/statistics.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    let salesChart, inventoryChart;
    let currentPeriod = '30days';
    let currentSort = {
        products: 'total_sold',
        customers: 'total_spent'
    };

    // Инициализация статистики
    function initStatistics() {
        currentPeriod = $('#statsPeriod').val();
        loadSalesStats();
        loadProductsStats();
        loadCustomersStats();
        loadInventoryStats();
    }

    // Загрузка статистики продаж
    function loadSalesStats() {
        $.get(`/api/statistics/sales?period=${currentPeriod}`, function(response) {
            if (response.salesStats && response.salesStats.length > 0) {
                updateSalesChart(response.salesStats);
                updateSalesStatsTable(response.salesStats);
            } else {
                $('#salesChart').parent().html('<div class="alert alert-info">Нет данных для отображения</div>');
                $('#salesStatsTable').html('<tr><td colspan="4" class="text-center">Нет данных</td></tr>');
            }
        }).fail(function() {
            $('#salesChart').parent().html('<div class="alert alert-danger">Ошибка загрузки данных</div>');
        });
    }

    // Загрузка статистики товаров
    function loadProductsStats() {
        $.get(`/api/statistics/products?sort=${currentSort.products}`, function(response) {
            if (response.productsStats && response.productsStats.length > 0) {
                updateProductsStatsTable(response.productsStats);
            } else {
                $('#productsStatsTable').html('<tr><td colspan="4" class="text-center">Нет данных</td></tr>');
            }
        });
    }

    // Загрузка статистики клиентов
    function loadCustomersStats() {
        $.get(`/api/statistics/customers?sort=${currentSort.customers}`, function(response) {
            if (response.customersStats && response.customersStats.length > 0) {
                updateCustomersStatsTable(response.customersStats);
            } else {
                $('#customersStatsTable').html('<tr><td colspan="4" class="text-center">Нет данных</td></tr>');
            }
        });
    }

    // Загрузка статистики склада
    function loadInventoryStats() {
        $.get('/api/statistics/inventory', function(response) {
            if (response.inventoryStats && response.inventoryStats.length > 0) {
                updateInventoryChart(response.inventoryStats);
                updateInventoryStatsTable(response.inventoryStats);
            } else {
                $('#inventoryChart').parent().html('<div class="alert alert-info">Нет данных для отображения</div>');
                $('#inventoryStatsTable').html('<tr><td colspan="3" class="text-center">Нет данных</td></tr>');
            }
        });
    }

    // Обновление графика продаж
    function updateSalesChart(data) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const labels = data.map(item => item.month);
        const ordersData = data.map(item => item.orders_count);
        const revenueData = data.map(item => item.total_amount);

        if (salesChart) {
            salesChart.destroy();
        }

        salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Количество заказов',
                        data: ordersData,
                        backgroundColor: 'rgba(78, 115, 223, 0.7)',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Выручка (₽)',
                        data: revenueData,
                        backgroundColor: 'rgba(28, 200, 138, 0.7)',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Заказы'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Выручка (₽)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }

    // Обновление таблицы статистики продаж
    function updateSalesStatsTable(data) {
        let html = '';
        data.forEach(item => {
            html += `
                <tr>
                    <td>${item.month}</td>
                    <td>${item.orders_count}</td>
                    <td>${formatCurrency(item.total_amount)}</td>
                    <td>${formatCurrency(item.avg_order_value)}</td>
                </tr>
            `;
        });
        $('#salesStatsTable').html(html);
    }

    // Обновление таблицы статистики товаров
    function updateProductsStatsTable(data) {
        let html = '';
        data.forEach(item => {
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td><a href="/company/${item.company_id}/edit">${item.company_name}</a></td>
                    <td>${item.total_sold || 0}</td>
                    <td>${formatCurrency(item.total_revenue || 0)}</td>
                    <td>${item.orders_count || 0}</td>
                </tr>
            `;
        });
        $('#productsStatsTable').html(html);
    }

    // Обновление таблицы статистики клиентов
    function updateCustomersStatsTable(data) {
        let html = '';
        data.forEach(item => {
            console.log(item);
            let color = '';
            color = (item.role_id === 'subscriber') ? `#3498db` : `#2c3e50`;
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td>
                        <span class="badge" style="background-color: ${color}">${item.role_id}</span>
                    </td>
                    <td>${item.orders_count || 0}</td>
                    <td>${formatCurrency(item.total_spent || 0)}</td>
                </tr>
            `;
        });
        $('#customersStatsTable').html(html);
    }

    // Обновление графика склада
    function updateInventoryChart(data) {
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        const labels = data.map(item => item.name);
        const stockData = data.map(item => item.stock_quantity);
        const soldData = data.map(item => item.total_sold || 0);

        if (inventoryChart) {
            inventoryChart.destroy();
        }

        inventoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Остаток на складе',
                        data: stockData,
                        backgroundColor: 'rgba(78, 115, 223, 0.7)'
                    },
                    {
                        label: 'Продано',
                        data: soldData,
                        backgroundColor: 'rgba(28, 200, 138, 0.7)'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: false // или true для stacked chart
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Обновление таблицы статистики склада
    function updateInventoryStatsTable(data) {
        let html = '';
        data.slice(0, 5).forEach(item => {
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td class="${item.stock_quantity <= 5 ? 'text-danger fw-bold' : ''}">
                        ${item.stock_quantity}
                    </td>
                    <td>${item.total_sold || 0}</td>
                </tr>
            `;
        });
        $('#inventoryStatsTable').html(html);
    }

    // Форматирование валюты
    function formatCurrency(amount) {
        return new Intl.NumberFormat('ru-RU', { 
            style: 'currency', 
            currency: 'RUB',
            minimumFractionDigits: 0
        }).format(amount).replace('₽', '₽');
    }

    // Обработчики событий
    $('#statsPeriod').change(function() {
        currentPeriod = $(this).val();
        loadSalesStats();
    });

    $('[data-sort]').click(function(e) {
        e.preventDefault();
        const sortField = $(this).data('sort');
        const tab = $(this).closest('.dropdown-menu').attr('aria-labelledby').replace('SortDropdown', '');
        
        currentSort[tab] = sortField;
        $(`#${tab}StatsTable`).html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Загрузка...</td></tr>');
        
        if (tab === 'products') {
            loadProductsStats();
        } else {
            loadCustomersStats();
        }
    });

    // Инициализация
    initStatistics();
});
</script>
<?php $this->endSection() ?>