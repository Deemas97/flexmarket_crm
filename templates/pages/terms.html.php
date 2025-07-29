<?php $this->extend('base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="terms-container container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <article class="terms-content">
                <header class="text-center mb-5">
                    <h1 class="display-4 mb-3">Условия использования сервиса</h1>
                    <p class="text-muted">Последнее обновление: <?= date('d.m.Y') ?></p>
                </header>

                <section class="mb-5">
                    <h2 class="h4 mb-3">1. Общие положения</h2>
                    <ol class="terms-list">
                        <li class="mb-2">Настоящие Условия регулируют использование веб-сайта <?= $this->escape($company_name ?? 'нашего сервиса') ?> (далее - "Сервис").</li>
                        <li class="mb-2">Используя Сервис, вы соглашаетесь с настоящими Условиями.</li>
                        <li>Мы оставляем право изменять Условия в любое время без предварительного уведомления.</li>
                    </ol>
                </section>

                <section class="mb-5">
                    <h2 class="h4 mb-3">2. Обязанности пользователя</h2>
                    <ol class="terms-list">
                        <li class="mb-2">Вы обязуетесь предоставлять достоверную информацию при регистрации.</li>
                        <li class="mb-2">Запрещается использовать Сервис для незаконной деятельности.</li>
                        <li class="mb-2">Не допускается передача вашей учетной записи третьим лицам.</li>
                        <li>Вы несете ответственность за сохранность ваших учетных данных.</li>
                    </ol>
                </section>

                <section class="mb-5">
                    <h2 class="h4 mb-3">3. Конфиденциальность</h2>
                    <p>Обработка персональных данных осуществляется в соответствии с нашей <a href="/privacy">Политикой конфиденциальности</a>.</p>
                </section>

                <section class="mb-5">
                    <h2 class="h4 mb-3">4. Интеллектуальная собственность</h2>
                    <p>Все материалы Сервиса, включая дизайн, текст, графику, принадлежат <?= $this->escape($company_name ?? 'компании') ?> или используются по лицензии.</p>
                </section>

                <section class="mb-5">
                    <h2 class="h4 mb-3">5. Ограничение ответственности</h2>
                    <p>Мы не несем ответственности за:</p>
                    <ul class="terms-list">
                        <li class="mb-2">Любые косвенные убытки или упущенную выгоду</li>
                        <li class="mb-2">Невозможность использования Сервиса по независящим от нас причинам</li>
                        <li>Действия третьих лиц, направленные против пользователей</li>
                    </ul>
                </section>

                <section class="mb-5">
                    <h2 class="h4 mb-3">6. Заключительные положения</h2>
                    <p>По всем вопросам обращайтесь: <a href="mailto:<?= $this->escape($support_email ?? 'support@example.com') ?>"><?= $this->escape($support_email ?? 'support@example.com') ?></a></p>
                </section>

                <footer class="text-center mt-5 pt-4 border-top">
                    <p class="text-muted">© <?= date('Y') ?> <?= $this->escape($company_name ?? 'Наша компания') ?>. Все права защищены.</p>
                </footer>
            </article>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
.terms-container {
    background-color: #f8f9fa;
    min-height: 100vh;
}

.terms-content {
    background: white;
    padding: 2.5rem;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.terms-list {
    padding-left: 1.5rem;
}

.terms-list li {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .terms-content {
        padding: 1.5rem;
    }
    
    h1.display-4 {
        font-size: 2rem;
    }
}

@media print {
    .terms-container {
        background: none;
        padding: 0;
    }
    
    .terms-content {
        box-shadow: none;
        padding: 0;
    }
}
</style>
<?php $this->endSection() ?>