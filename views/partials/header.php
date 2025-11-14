<?php
$title = $title ?? 'To-Do App';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sanitize($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body class="<?= bodyClass() ?>">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= url('public/tasks.php') ?>">Quản lý công việc</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= url('public/tasks.php') ?>">Danh sách</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('public/kanban.php') ?>">Bảng Kanban</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('public/analytics.php') ?>">Phân tích</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('public/theme.php') ?>">
                        <?= currentTheme() === 'light' ? 'Chế độ tối' : 'Chế độ sáng' ?>
                    </a>
                </li>
                <?php if (!empty($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <span class="nav-link text-white-50">Hi, <?= sanitize($_SESSION['user']['username']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('public/logout.php') ?>" class="btn btn-outline-light btn-sm ms-lg-2">Đăng xuất</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
