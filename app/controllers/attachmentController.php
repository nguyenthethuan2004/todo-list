<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/TaskAttachment.php';
require_once __DIR__ . '/../models/TaskCollaborator.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../middlewares/auth.php';

session_start();
checkLogin();
$taskModel = new Task($pdo);
$attachmentModel = new TaskAttachment($pdo);
$collabModel = new TaskCollaborator($pdo);
$userId = (int) $_SESSION['user']['id'];
$action = $_GET['action'] ?? '';
$taskId = (int) ($_GET['task_id'] ?? 0);
$redirectUrl = $_POST['redirect'] ?? $_GET['redirect'] ?? url('public/task_edit.php?id=' . $taskId);

if ($taskId <= 0) {
    redirect(url('public/tasks.php'));
}
$task = $taskModel->getByIdWithAccess($taskId, $userId);
if (!$task) {
    setFlash('error', 'Không có quyền.');
    redirect(url('public/tasks.php'));
}
$canEdit = (int)$task['user_id'] === $userId || $collabModel->userCanEdit($taskId, $userId);
if (!$canEdit) {
    setFlash('error', 'Bạn không có quyền chỉnh sửa file.');
    redirect($redirectUrl);
}

if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        setFlash('error', 'Tải file thất bại.');
        redirect($redirectUrl);
    }
    $file = $_FILES['file'];
    $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $file['name']);
    $targetDir = __DIR__ . '/../../uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }
    $newFileName = uniqid('att_') . '_' . $safeName;
    $targetPath = $targetDir . $newFileName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        setFlash('error', 'Không thể lưu file.');
        redirect($redirectUrl);
    }
    $relativePath = 'uploads/' . $newFileName;
    $attachmentModel->create($taskId, $userId, $safeName, $relativePath, $file['type'] ?? '');
    setFlash('success', 'Đã đính kèm file.');
    redirect($redirectUrl);
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $attachments = $attachmentModel->getByTask($taskId);
    foreach ($attachments as $attachment) {
        if ((int)$attachment['id'] === $id) {
            $absolute = __DIR__ . '/../../' . $attachment['file_path'];
            if (file_exists($absolute)) {
                @unlink($absolute);
            }
            break;
        }
    }
    $attachmentModel->delete($id, $taskId);
    setFlash('success', 'Đã xoá file.');
    redirect($redirectUrl);
}

redirect($redirectUrl);
