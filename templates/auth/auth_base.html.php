<!DOCTYPE html>
<html lang="ru,en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $this->escape($title) ?></title>
        <link rel="icon" href="/assets/img/logo.png">

        <!-- Styles from CDNs -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <!-- Custom styles -->
        <link rel="stylesheet" href="/assets/css/auth/styles.css">
        <?= $this->section('styles') ?>

        <!-- Libs from CDNs -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="auth-wrapper">
            <div class="auth-container">
                <div class="auth-branding">
                    <img src="/assets/img/logo.png" alt="Company Logo" class="auth-logo">
                    <h1 class="auth-title"><?= $this->escape($title ?? 'Система доступа') ?></h1>
                </div>

                <div class="auth-card">
                    <?= $this->section('auth_form') ?>
                </div>

                <div class="auth-footer">
                    <p class="text-muted"><?= date('Y') ?> &copy; <?= $this->escape($company_name ?? 'Ваша компания') ?></p>
                </div>
            </div>
        </div>

        <script>
        $(document).ready(function() {
            // Анимация загрузки
            $('.auth-form').on('submit', function(e) {
                var btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Обработка...'
                );
            });
        });
        </script>
    </body>
</html>