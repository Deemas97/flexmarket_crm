<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <!-- Search Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Результаты поиска</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Поиск</li>
                </ol>
            </nav>
            
            <!-- Search Form -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form action="/search" method="GET" class="d-flex">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" placeholder="Введите поисковый запрос..." value="<?= $this->escape($query) ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Поиск
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search Results -->
    <div class="row">
        <div class="col">
            <?php if (empty($query)): ?>
                <div class="alert alert-info">
                    Введите поисковый запрос в поле выше
                </div>
            <?php elseif (empty($results)): ?>
                <div class="alert alert-warning">
                    По запросу "<?= $this->escape($query) ?>" ничего не найдено
                </div>
            <?php else: ?>
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Найдено <?= count($results) ?> результатов по запросу "<?= $this->escape($query) ?>"
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($results as $item): ?>
                                <a href="<?= $this->escape($item['url']) ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?= $this->escape($item['title']) ?></h5>
                                        <span class="badge bg-primary"><?= $this->escape($item['type']) ?></span>
                                    </div>
                                    <p class="mb-1"><?= $this->escape($item['description']) ?></p>
                                    <div class="mt-2">
                                        <?php foreach ($item['meta'] as $key => $value): ?>
                                            <small class="d-block text-muted">
                                                <strong><?= $key ?>:</strong> <?= $value ?>
                                            </small>
                                        <?php endforeach; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Автодополнение поиска
    $('input[name="q"]').autocomplete({
        source: function(request, response) {
            $.get('/api/search', { q: request.term }, function(data) {
                response(data.suggestions);
            });
        },
        minLength: 2,
        select: function(event, ui) {
            window.location.href = ui.item.url;
        }
    }).autocomplete('instance')._renderItem = function(ul, item) {
        return $('<li>')
            .append(`<div>${item.label}<br><small class="text-muted">${item.category}</small></div>`)
            .appendTo(ul);
    };
});
</script>
<?php $this->endSection() ?>