<?php
$title = 'Đăng nhập';
include __DIR__ . '/../partials/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <h3 class="mb-3">Đăng nhập</h3>
        <?php if ($error = getFlash('error')): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($msg = getFlash('success')): ?>
            <div class="alert alert-success"><?= sanitize($msg) ?></div>
        <?php endif; ?>
        <form method="POST" action="<?= url('app/controllers/authController.php?action=login') ?>">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Đăng nhập</button>
            <div class="text-center mt-3">
                <a href="<?= url('public/register.php') ?>">Chưa có tài khoản?</a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
