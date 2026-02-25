<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="brand-icon">
                <i class="bi bi-mic-fill"></i>
            </div>
            <h1>KTV Bistro POS</h1>
            <p>Restaurant · Bar · KTV</p>
        </div>

        <div class="login-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger-custom d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <div class="alert alert-danger-custom mb-3" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-circle-fill mt-1"></i>
                        <div>
                            <?= $validation->listErrors() ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?= form_open('/authenticate') ?>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control"
                           placeholder="Enter your email"
                           value="<?= old('email') ?>"
                           required
                           autocomplete="email">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control"
                               placeholder="Enter your password"
                               required
                               autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            <?= form_close() ?>
        </div>
    </div>

    <p class="text-center text-white-50 mt-3 mb-0 small">© KTV Bistro POS System</p>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});
</script>
