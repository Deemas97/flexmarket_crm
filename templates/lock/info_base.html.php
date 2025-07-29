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
        <link rel="stylesheet" href="/assets/css/lock/styles.css">
        <?= $this->section('styles') ?>

        <!-- Libs from CDNs -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <?= $this->section('content') ?>
    </body>
</html>