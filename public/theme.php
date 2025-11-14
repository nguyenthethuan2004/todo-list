<?php
session_start();
require_once __DIR__ . '/../app/helpers/functions.php';
$current = currentTheme();
$newTheme = $current === 'light' ? 'dark' : 'light';
$_SESSION['theme'] = $newTheme;
if (!empty($_SESSION['user'])) {
    require_once __DIR__ . '/../app/config/db.php';
    $stmt = $pdo->prepare('UPDATE users SET theme = :theme WHERE id = :id');
    $stmt->execute([':theme' => $newTheme, ':id' => $_SESSION['user']['id']]);
    $_SESSION['user']['theme'] = $newTheme;
}
$referer = $_SERVER['HTTP_REFERER'] ?? url('public/tasks.php');
redirect($referer);
