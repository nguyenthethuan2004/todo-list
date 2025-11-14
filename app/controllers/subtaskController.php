<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Subtask.php';
require_once __DIR__ . '/../models/TaskCollaborator.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../middlewares/auth.php';

session_start();
checkLogin();
$taskModel = new Task($pdo);
$subtaskModel = new Subtask($pdo);
$collabModel = new TaskCollaborator($pdo);
$userId = (int) $_SESSION['user']['id'];

function ensureTaskAccess(Task $taskModel, int $taskId, int $userId): array
{
    $task = $taskModel->getByIdWithAccess($taskId, $userId);
    if (!$task) {
        setFlash('error', 'Bạn không có quyền với công việc này.');
        redirect(url('public/tasks.php'));
    }
    return $task;
}

$action = $_GET['action'] ?? '';
$taskId = (int) ($_GET['task_id'] ?? 0);
$redirectUrl = $_POST['redirect'] ?? url('public/task_edit.php?id=' . $taskId);

if ($taskId <= 0) {
    setFlash('error', 'Thiếu task_id.');
    redirect(url('public/tasks.php'));
}
$task = ensureTaskAccess($taskModel, $taskId, $userId);
$canEdit = (int)$task['user_id'] === $userId || $collabModel->userCanEdit($taskId, $userId);
if (!$canEdit) {
    setFlash('error', 'Bạn không có quyền cập nhật checklist.');
    redirect($redirectUrl);
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    if ($title === '') {
        setFlash('error', 'Tên checklist không được để trống.');
    } else {
        $subtaskModel->create($taskId, $title);
        setFlash('success', 'Đã thêm checklist.');
    }
    redirect($redirectUrl);
}

if ($action === 'toggle') {
    $subtaskId = (int) ($_GET['id'] ?? 0);
    $subtaskModel->toggle($subtaskId, $taskId);
    redirect($redirectUrl);
}

if ($action === 'delete') {
    $subtaskId = (int) ($_GET['id'] ?? 0);
    $subtaskModel->delete($subtaskId, $taskId);
    redirect($redirectUrl);
}

redirect($redirectUrl);
