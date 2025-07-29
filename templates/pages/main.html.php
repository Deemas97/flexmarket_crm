<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
    <div class="container-fluid py-4">
        <!-- Dashboard Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-0">Главная панель</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Главная</li>
                    </ol>
                </nav>
            </div>
        </div>
                
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Товары</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['stats']['products'] ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Категории</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['stats']['categories'] ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tags fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Заказы</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['stats']['orders'] ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Клиенты</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['stats']['customers'] ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Row -->
        <div class="row">
            <!-- Sales Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Продажи за последние 30 дней</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status Pie Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Статусы заказов</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="orderStatusChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <?php foreach ($data['orders_status_stats'] as $stat): ?>
                                <span class="mr-2">
                                    <i class="fas fa-circle" style="color: <?= $this->getStatusColor($stat['status'], $data['status_config']) ?>"></i> 
                                    <?= $this->getStatusText($stat['status'], $data['status_config']) ?> (<?= $stat['count'] ?>)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders and Popular Products -->
        <div class="row">
            <!-- Recent Orders -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Последние заказы</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Клиент</th>
                                        <th>Сумма</th>
                                        <th>Дата</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['recent_orders'] as $order): ?>
                                    <tr>
                                        <td><?= $this->escape($order['id']) ?></td>
                                        <td><?= $this->escape($order['customer_name']) ?></td>
                                        <td><?= number_format($order['sum'], 2) ?> ₽</td>
                                        <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                                        <td><span class="badge" style="background-color: <?= $this->getStatusColor($order['status'], $data['status_config']) ?>"><?= $this->getStatusText($order['status'], $data['status_config']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Products -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Популярные товары</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Товар</th>
                                        <th>Цена</th>
                                        <th>Продано</th>
                                        <th>Заказов</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['popular_products'] as $product): ?>
                                    <tr>
                                        <td><?= $this->escape($product['name']) ?></td>
                                        <td><?= number_format($product['price'], 2) ?> ₽</td>
                                        <td><?= $product['total_sold'] ?? 0 ?></td>
                                        <td><?= $product['order_count'] ?? 0 ?></td>
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
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/main.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Загрузка данных для графиков
    loadSalesChart();
    loadOrderStatusChart();
    
    function loadSalesChart() {
        $.get('/api/dashboard/sales_data', function(response) {
            if (response.salesData && response.salesData.length > 0) {
                const labels = response.salesData.map(item => item.date);
                const orderData = response.salesData.map(item => item.order_count);
                const amountData = response.salesData.map(item => item.total_amount);
                
                const ctx = document.getElementById('salesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Количество заказов',
                                data: orderData,
                                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                borderColor: 'rgba(78, 115, 223, 1)',
                                borderWidth: 2,
                                pointRadius: 3,
                                yAxisID: 'orders'
                            },
                            {
                                label: 'Сумма продаж (₽)',
                                data: amountData,
                                backgroundColor: 'rgba(28, 200, 138, 0.05)',
                                borderColor: 'rgba(28, 200, 138, 1)',
                                borderWidth: 2,
                                pointRadius: 3,
                                yAxisID: 'amount'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.datasetIndex === 0) {
                                            label += context.raw + ' зак.';
                                        } else {
                                            label += new Intl.NumberFormat('ru-RU', { 
                                                style: 'currency', 
                                                currency: 'RUB',
                                                maximumFractionDigits: 0
                                            }).format(context.raw);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            },
                            orders: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Количество заказов'
                                },
                                grid: {
                                    drawOnChartArea: true,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            amount: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Сумма (₽)'
                                },
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('ru-RU', { 
                                            style: 'currency', 
                                            currency: 'RUB',
                                            maximumFractionDigits: 0
                                        }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                console.error('No sales data received');
                // Показываем сообщение, если данных нет
                document.getElementById('salesChart').parentElement.innerHTML = 
                    '<div class="alert alert-info">Нет данных о продажах за последние 30 дней</div>';
            }
        }).fail(function(xhr) {
            console.error('Error loading sales data:', xhr.responseText);
            document.getElementById('salesChart').parentElement.innerHTML = 
                '<div class="alert alert-danger">Ошибка загрузки данных о продажах</div>';
        });
    }
    
    function loadOrderStatusChart() {
        const ctx = document.getElementById('orderStatusChart').getContext('2d');
        // Делаем colors глобальной переменной (или добавляем в нужную область видимости)
        window.orderStatusColors = [
            'rgba(78, 115, 223, 0.8)',    // processing
            'rgba(246, 194, 62, 0.8)',    // pending
            'rgba(28, 200, 138, 0.8)',    // completed
            'rgba(231, 74, 59, 0.8)',     // canceled
        ];

        const statusData = <?= json_encode($data['orders_status_stats']) ?>;
        const labels = statusData.map(item => {
            switch(item.status) {
                case 'pending': return 'Ожидание';
                case 'processing': return 'В обработке';
                case 'completed': return 'Готово';
                case 'cancelled': return 'Отменено';
                default: return item.status;
            }
        });
        const data = statusData.map(item => item.count);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: window.orderStatusColors,
                    hoverBackgroundColor: window.orderStatusColors.map(c => c.replace('0.8', '1')),
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%',
            },
        });
    }
});
</script>
<?php $this->endSection() ?>