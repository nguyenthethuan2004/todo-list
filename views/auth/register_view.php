<?php
$title = 'Đăng ký';
include __DIR__ . '/../partials/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h3 class="mb-3">Tạo tài khoản</h3>
        <?php if ($error = getFlash('error')): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($msg = getFlash('success')): ?>
            <div class="alert alert-success"><?= sanitize($msg) ?></div>
        <?php endif; ?>
        <form method="POST" action="<?= url('app/controllers/authController.php?action=register') ?>">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required minlength="3">
            </div>
            <div class="mb-3">
                <label class="form-label">Email (tuỳ chọn)</label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <button class="btn btn-success w-100">Đăng ký</button>
            <div class="text-center mt-3">
                <a href="<?= url('public/login.php') ?>">Đã có tài khoản?</a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
