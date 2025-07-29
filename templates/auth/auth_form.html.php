<?php $this->extend('auth/auth_base.html.php') ?>

<?php $this->startSection('auth_form') ?>
<form id="loginForm" class="auth-form" method="POST" action="/api/login">
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $this->escape($error) ?></div>
    <?php endif; ?>
    
    <div class="mb-3 form-floating">
        <input type="email" class="form-control" id="email" name="email" 
               placeholder="Email" required value="<?= $this->escape($email ?? '') ?>">
        <label for="email">Email</label>
    </div>
    
    <div class="mb-3 form-floating">
        <input type="password" class="form-control" id="password" name="password" 
               placeholder="Пароль" required>
        <label for="password">Пароль</label>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">Запомнить меня</label>
    </div>
    
    <button type="submit" class="btn btn-primary w-100">Войти</button>
    
    <div class="auth-actions">
        <a href="/password/reset" class="text-muted">Забыли пароль?</a>
        <a href="/signup" class="auth-switch-form">Регистрация</a>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#loginForm').validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6
            }
        },
        messages: {
            email: "Пожалуйста, введите корректный email",
            password: {
                required: "Пожалуйста, введите пароль",
                minlength: "Пароль должен быть не менее 6 символов"
            }
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        }
    });
});
</script>
<?php $this->endSection() ?>