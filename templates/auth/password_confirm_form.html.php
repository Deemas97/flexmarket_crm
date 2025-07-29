<?php $this->extend('auth/auth_base.html.php') ?>

<?php $this->startSection('auth_form') ?>
<div class="auth-header text-center mb-4">
    <h2 class="auth-subtitle">Новый пароль</h2>
    <p class="text-muted">Введите новый пароль для вашего аккаунта</p>
</div>

<form id="passwordResetConfirmForm" class="auth-form" method="POST" action="/password/reset/confirm">
    <input type="hidden" name="token" value="<?= $this->escape($token ?? '') ?>">
    
    <?php if (isset($data['errors'])): ?>
    <div class="alert alert-danger">
        <?php foreach ($data['errors'] as $error): ?>
        <div><?= $this->escape($error) ?></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="mb-3 form-floating">
        <input type="password" class="form-control" id="password" name="password" 
               placeholder="Новый пароль" required>
        <label for="password">Новый пароль</label>
        <div class="form-text">Минимум 8 символов</div>
    </div>
    
    <div class="mb-3 form-floating">
        <input type="password" class="form-control" id="password_confirmation" 
               name="password_confirmation" placeholder="Подтвердите пароль" required>
        <label for="password_confirmation">Подтвердите пароль</label>
    </div>
    
    <div class="password-strength mb-3">
        <div class="progress" style="height: 5px;">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
        <small class="text-muted strength-feedback">Надежность пароля: очень слабый</small>
    </div>
    
    <button type="submit" class="btn btn-primary w-100 mb-3">Сохранить пароль</button>
    
    <div class="text-center">
        <a href="/login" class="auth-switch-form">
            <i class="fas fa-arrow-left me-2"></i>Вернуться к входу
        </a>
    </div>
</form>

<script>
$(document).ready(function() {
    // Валидация формы
    $('#passwordResetConfirmForm').validate({
        rules: {
            password: {
                required: true,
                minlength: 8
            },
            password_confirmation: {
                required: true,
                equalTo: "#password"
            }
        },
        messages: {
            password: {
                required: "Пожалуйста, введите пароль",
                minlength: "Пароль должен быть не менее 8 символов"
            },
            password_confirmation: {
                required: "Пожалуйста, подтвердите пароль",
                equalTo: "Пароли не совпадают"
            }
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

    // Индикатор сложности пароля
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;
        var feedback = '';
        
        if (password.length >= 8) strength += 1;
        if (password.match(/[a-z]/)) strength += 1;
        if (password.match(/[A-Z]/)) strength += 1;
        if (password.match(/[0-9]/)) strength += 1;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
        
        var progress = strength * 25;
        if (progress > 100) progress = 100;
        
        var progressBar = $('.progress-bar');
        var feedbackText = $('.strength-feedback');
        
        progressBar.css('width', progress + '%');
        
        switch(strength) {
            case 0:
            case 1:
                progressBar.removeClass('bg-warning bg-success').addClass('bg-danger');
                feedback = 'Очень слабый';
                break;
            case 2:
                progressBar.removeClass('bg-danger bg-success').addClass('bg-warning');
                feedback = 'Слабый';
                break;
            case 3:
                progressBar.removeClass('bg-danger bg-success').addClass('bg-warning');
                feedback = 'Средний';
                break;
            case 4:
                progressBar.removeClass('bg-danger bg-warning').addClass('bg-success');
                feedback = 'Сильный';
                break;
            case 5:
                progressBar.removeClass('bg-danger bg-warning').addClass('bg-success');
                feedback = 'Очень сильный';
                break;
        }
        
        feedbackText.text('Надежность пароля: ' + feedback);
    });
});
</script>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
.password-strength {
    margin-top: -10px;
}

.progress-bar {
    transition: width 0.3s ease;
}

.strength-feedback {
    display: block;
    margin-top: 5px;
    font-size: 0.8rem;
}
</style>
<?php $this->endSection() ?>