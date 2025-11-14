<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/TaskComment.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../middlewares/auth.php';

session_start();
checkLogin();
$taskModel = new Task($pdo);
$commentModel = new TaskComment($pdo);
$userId = (int) $_SESSION['user']['id'];
$action = $_GET['action'] ?? '';
$taskId = (int) ($_GET['task_id'] ?? 0);
$redirectUrl = $_POST['redirect'] ?? url('public/task_edit.php?id=' . $taskId);

if ($taskId <= 0) {
    redirect(url('public/tasks.php'));
}
$task = $taskModel->getByIdWithAccess($taskId, $userId);
if (!$task) {
    setFlash('error', 'Không có quyền.');
    redirect(url('public/tasks.php'));
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        setFlash('error', 'Nội dung bình luận trống.');
    } else {
        $commentModel->create($taskId, $userId, $content);
        setFlash('success', 'Đã thêm bình luận.');
    }
    redirect($redirectUrl);
}

redirect($redirectUrl);
