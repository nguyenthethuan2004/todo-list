<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Tag.php';
require_once __DIR__ . '/../models/TaskCollaborator.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/mail.php';
require_once __DIR__ . '/../middlewares/auth.php';

session_start();
checkLogin();
$taskModel = new Task($pdo);
$tagModel = new Tag($pdo);
$collabModel = new TaskCollaborator($pdo);
$userId = (int) $_SESSION['user']['id'];

function normalizeTaskPayload(array $post): array
{
    $title = trim($post['title'] ?? '');
    $description = $post['description'] !== '' ? trim($post['description']) : null;
    $dueDate = $post['due_date'] !== '' ? $post['due_date'] : null;
    $statusInput = $post['status'] ?? 'pending';
    $status = in_array($statusInput, ['pending', 'in_progress', 'completed'], true) ? $statusInput : 'pending';
    $priorityInput = $post['priority'] ?? 'medium';
    $autoPriority = !empty($post['auto_priority']) ? 1 : 0;
    if ($autoPriority || !in_array($priorityInput, ['high','medium','low'], true)) {
        $priorityInput = suggestPriority($dueDate);
        $autoPriority = 1;
    }
    $manualTags = array_filter(array_map(fn ($tag) => strtoupper(trim($tag)), explode(',', $post['tags'] ?? '')));
    $autoTags = suggestTagsFromText(($title ?? '') . ' ' . ($description ?? ''));
    $tags = array_values(array_unique(array_filter(array_merge($manualTags, $autoTags))));

    return [
        'title' => $title,
        'description' => $description,
        'due_date' => $dueDate,
        'status' => $status,
        'priority' => $priorityInput,
        'auto_priority' => $autoPriority,
        'team_id' => null,
        'tags' => $tags,
    ];
}

function ensureTask(int $taskId, int $userId, bool $needOwner = false): array
{
    global $taskModel, $collabModel;
    $task = $taskModel->getByIdWithAccess($taskId, $userId);
    if (!$task) {
        setFlash('error', 'Không tìm thấy hoặc không có quyền với task.');
        redirect(url('public/tasks.php'));
    }
    if ($needOwner && (int)$task['user_id'] !== $userId) {
        setFlash('error', 'Chỉ chủ sở hữu mới được thao tác này.');
        redirect(url('public/tasks.php'));
    }
    return $task;
}

function createTask(array $post): void
{
    global $taskModel, $tagModel, $userId;
    $payload = normalizeTaskPayload($post);
    if ($payload['title'] === '') {
        setFlash('error', 'Tiêu đề không được bỏ trống.');
        redirect(url('public/task_create.php'));
    }
    $data = $payload;
    $tags = $data['tags'];
    unset($data['tags']);
    $data['user_id'] = $userId;
    $taskId = $taskModel->create($data);
    if (!$taskId) {
        setFlash('error', 'Không thể tạo task, thử lại sau.');
        redirect(url('public/task_create.php'));
    }
    $tagModel->syncForTask($taskId, $tags);
    if (!empty($_SESSION['user']['email'])) {
        $emailBody = '<p>Xin chào ' . sanitize($_SESSION['user']['username']) . ',</p>'
            . '<p>Bạn vừa tạo task mới: <strong>' . sanitize($data['title']) . '</strong>.</p>'
            . '<p>Hạn: ' . sanitize($data['due_date'] ?: 'Chưa đặt') . '<br>'
            . 'Ưu tiên: ' . sanitize(priorityLabel($data['priority'])) . '</p>'
            . '<p><a href="' . url('public/task_edit.php?id=' . $taskId) . '">Xem chi tiết</a></p>';
        sendMail($_SESSION['user']['email'], 'Bạn đã tạo task mới', $emailBody);
    }
    setFlash('success', 'Đã tạo công việc mới.');
    redirect(url('public/tasks.php'));
}

function updateTask(int $id, array $post): void
{
    global $taskModel, $tagModel, $collabModel, $userId;
    $task = ensureTask($id, $userId);
    $isOwner = (int)$task['user_id'] === $userId;
    if (!$isOwner && !$collabModel->userCanEdit($id, $userId)) {
        setFlash('error', 'Bạn chỉ có quyền xem task này.');
        redirect(url('public/tasks.php'));
    }
    $payload = normalizeTaskPayload($post);
    if ($payload['title'] === '') {
        setFlash('error', 'Tiêu đề không được bỏ trống.');
        redirect(url('public/task_edit.php?id=' . $id));
    }
    $data = $payload;
    $tags = $data['tags'];
    unset($data['tags']);
    $taskModel->update($id, $data);
    $tagModel->syncForTask($id, $tags);
    setFlash('success', 'Đã cập nhật task.');
    redirect(url('public/tasks.php'));
}

function deleteTask(int $id): void
{
    global $taskModel, $userId;
    $task = ensureTask($id, $userId, true);
    $taskModel->delete($task['id']);
    setFlash('success', 'Đã xóa task.');
    redirect(url('public/tasks.php'));
}

$action = $_GET['action'] ?? '';
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    createTask($_POST);
} elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_GET['id'] ?? 0);
    updateTask($id, $_POST);
} elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_GET['id'] ?? 0);
    deleteTask($id);
} else {
    redirect(url('public/tasks.php'));
}
