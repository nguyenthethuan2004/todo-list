<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Reminder.php';
require_once __DIR__ . '/../models/TaskCollaborator.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../middlewares/auth.php';

session_start();
checkLogin();
$taskModel = new Task($pdo);
$reminderModel = new Reminder($pdo);
$collabModel = new TaskCollaborator($pdo);
$userId = (int) $_SESSION['user']['id'];
$action = $_GET['action'] ?? '';
$taskId = (int) ($_GET['task_id'] ?? 0);
$redirectUrl = $_POST['redirect'] ?? $_GET['redirect'] ?? url('public/task_edit.php?id=' . $taskId);

if ($taskId <= 0) {
    setFlash('error', 'Thiếu task.');
    redirect(url('public/tasks.php'));
}
$task = $taskModel->getByIdWithAccess($taskId, $userId);
if (!$task) {
    setFlash('error', 'Không có quyền thao tác.');
    redirect(url('public/tasks.php'));
}
$canEdit = (int)$task['user_id'] === $userId || $collabModel->userCanEdit($taskId, $userId);
if (!$canEdit) {
    setFlash('error', 'Bạn chỉ có quyền xem task này.');
    redirect($redirectUrl);
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $channel = in_array($_POST['channel'] ?? 'email', ['email', 'browser'], true) ? $_POST['channel'] : 'email';
    $remindAt = $_POST['remind_at'] ?? '';
    if ($remindAt === '') {
        setFlash('error', 'Vui lòng chọn thời gian nhắc.');
    } else {
        $formatted = str_replace('T', ' ', $remindAt);
        $reminderModel->create($taskId, $channel, $formatted);
        setFlash('success', 'Đã đặt nhắc việc.');
    }
    redirect($redirectUrl);
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $reminderModel->delete($id, $taskId);
    setFlash('success', 'Đã xoá nhắc việc.');
    redirect($redirectUrl);
}

redirect($redirectUrl);
