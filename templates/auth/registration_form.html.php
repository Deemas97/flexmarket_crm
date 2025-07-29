<?php $this->extend('auth/auth_base.html.php') ?>

<?php $this->startSection('auth_form') ?>
<form id="registerForm" class="auth-form" method="POST" action="/api/signup">
    <?php if (isset($data['errors'])): ?>
    <div class="alert alert-danger">
        <?php foreach ($data['errors'] as $error): ?>
        <div><?= $this->escape($error) ?></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6 mb-3 form-floating">
            <input type="text" class="form-control" id="firstName" name="i" 
                   placeholder="Имя" required value="<?= $this->escape($i ?? '') ?>">
            <label for="firstName">Имя</label>
        </div>
        
        <div class="col-md-6 mb-3 form-floating">
            <input type="text" class="form-control" id="lastName" name="f" 
                   placeholder="Фамилия" value="<?= $this->escape($f ?? '') ?>">
            <label for="lastName">Фамилия</label>
        </div>
    </div>
    
    <div class="mb-3 form-floating">
        <input type="email" class="form-control" id="email" name="email" 
               placeholder="Email" required value="<?= $this->escape($email ?? '') ?>">
        <label for="email">Email</label>
    </div>
    
    <div class="mb-3 form-floating">
        <input type="password" class="form-control" id="password" name="password" 
               placeholder="Пароль" required>
        <label for="password">Пароль</label>
        <div class="form-text">Минимум 8 символов</div>
    </div>
    
    <div class="mb-3 form-floating">
        <input type="password" class="form-control" id="passwordConfirm" name="password_confirmation" 
               placeholder="Подтверждение пароля" required>
        <label for="passwordConfirm">Подтверждение пароля</label>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="agreeTerms" name="agree_terms" required>
        <label class="form-check-label" for="agreeTerms">
            Я согласен с <a href="/terms">условиями использования</a>
        </label>
    </div>
    
    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
    
    <div class="auth-actions mt-3">
        <span>Уже есть аккаунт?</span>
        <a href="/login" class="auth-switch-form">Войти</a>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#registerForm').validate({
        rules: {
            i: {
                required: true,
                minlength: 2
            },
            f: {
                required: true,
                minlength: 2
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 8
            },
            password_confirmation: {
                required: true,
                equalTo: "#password"
            },
            agree_terms: {
                required: true
            }
        },
        messages: {
            i: {
                required: "Пожалуйста, введите ваше имя",
                minlength: "Имя должно быть не короче 2 символов"
            },
            f: {
                required: "Пожалуйста, введите вашу фамилию",
                minlength: "Фамилия должна быть не короче 2 символов"
            },
            email: "Пожалуйста, введите корректный email",
            password: {
                required: "Пожалуйста, введите пароль",
                minlength: "Пароль должен быть не менее 8 символов"
            },
            password_confirmation: {
                required: "Пожалуйста, подтвердите пароль",
                equalTo: "Пароли не совпадают"
            },
            agree_terms: "Вы должны принять условия использования"
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