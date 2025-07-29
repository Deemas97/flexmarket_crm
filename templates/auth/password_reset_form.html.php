<?php $this->extend('auth/auth_base.html.php') ?>

<?php $this->startSection('auth_form') ?>
<div class="auth-header text-center mb-4">
    <h2 class="auth-subtitle">Восстановление пароля</h2>
    <p class="text-muted">Введите email, указанный при регистрации</p>
</div>

<form id="passwordResetForm" class="auth-form" method="POST" action="/password/reset">
    <?php if (isset($data['error'])): ?>
    <div class="alert alert-danger"><?= $this->escape($data['error']) ?></div>
    <?php endif; ?>
    
    <?php if (isset($data['success'])): ?>
    <div class="alert alert-success"><?= $this->escape($data['success']) ?></div>
    <?php endif; ?>
    
    <div class="mb-3 form-floating">
        <input type="email" class="form-control" id="email" name="email" 
               placeholder="Email" required value="<?= $this->escape($email ?? '') ?>">
        <label for="email">Email</label>
    </div>
    
    <button type="submit" class="btn btn-primary w-100 mb-3">Отправить ссылку</button>
    
    <div class="text-center">
        <a href="/login" class="auth-switch-form">
            <i class="fas fa-arrow-left me-2"></i>Вернуться к входу
        </a>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#passwordResetForm').validate({
        rules: {
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            email: "Пожалуйста, введите корректный email"
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        }
    });
});
</script>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
.auth-subtitle {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--secondary-color);
}

.auth-header {
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.auth-form .alert {
    border-radius: 8px;
}
</style>
<?php $this->endSection() ?>