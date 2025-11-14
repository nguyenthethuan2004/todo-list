<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TaskCollaborator.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../middlewares/auth.php';

session_start();
checkLogin();
$taskModel = new Task($pdo);
$userModel = new User($pdo);
$collabModel = new TaskCollaborator($pdo);
$userId = (int) $_SESSION['user']['id'];
$taskId = (int) ($_GET['task_id'] ?? 0);
$redirectUrl = $_POST['redirect'] ?? $_GET['redirect'] ?? url('public/task_edit.php?id=' . $taskId);

if ($taskId <= 0) {
    redirect(url('public/tasks.php'));
}
$task = $taskModel->getByIdWithAccess($taskId, $userId);
if (!$task || (int)$task['user_id'] !== $userId) {
    setFlash('error', 'Chỉ chủ sở hữu mới chia sẻ được.');
    redirect($redirectUrl);
}
$action = $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $role = in_array($_POST['role'] ?? 'viewer', ['viewer','editor'], true) ? $_POST['role'] : 'viewer';
    $targetUser = $userModel->findByUsername($username);
    if (!$targetUser) {
        setFlash('error', 'Không tìm thấy người dùng.');
    } elseif ((int)$targetUser['id'] === $userId) {
        setFlash('error', 'Không thể chia sẻ cho chính bạn.');
    } else {
        $collabModel->add($taskId, (int)$targetUser['id'], $role);
        setFlash('success', 'Đã chia sẻ với ' . sanitize($username));
    }
    redirect($redirectUrl);
}

if ($action === 'remove') {
    $collabId = (int) ($_GET['user_id'] ?? 0);
    $collabModel->remove($taskId, $collabId);
    setFlash('success', 'Đã thu hồi quyền.');
    redirect($redirectUrl);
}

redirect($redirectUrl);
