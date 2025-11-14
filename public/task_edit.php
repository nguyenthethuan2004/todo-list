<?php
session_start();
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middlewares/auth.php';
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/models/Task.php';
require_once __DIR__ . '/../app/models/Subtask.php';
require_once __DIR__ . '/../app/models/Reminder.php';
require_once __DIR__ . '/../app/models/TaskComment.php';
require_once __DIR__ . '/../app/models/TaskAttachment.php';
require_once __DIR__ . '/../app/models/Tag.php';
require_once __DIR__ . '/../app/models/TaskCollaborator.php';
checkLogin();
$taskModel = new Task($pdo);
$subtaskModel = new Subtask($pdo);
$reminderModel = new Reminder($pdo);
$commentModel = new TaskComment($pdo);
$attachmentModel = new TaskAttachment($pdo);
$tagModel = new Tag($pdo);
$collabModel = new TaskCollaborator($pdo);
$userId = (int) $_SESSION['user']['id'];
$taskId = (int) ($_GET['id'] ?? 0);
$task = $taskModel->getByIdWithAccess($taskId, $userId);
if (!$task) {
    setFlash('error', 'Task không tồn tại.');
    redirect(url('public/tasks.php'));
}
$subtasks = $subtaskModel->getByTask($taskId);
$subtaskStats = $subtaskModel->statsForTask($taskId);
$reminders = $reminderModel->getByTask($taskId);
$comments = $commentModel->getByTask($taskId);
$attachments = $attachmentModel->getByTask($taskId);
$taskTags = $tagModel->getForTask($taskId);
$collaborators = $collabModel->getCollaborators($taskId);
$isOwner = (int)$task['user_id'] === $userId;
$formData = array_merge($task, [
    'tags' => implode(', ', array_column($taskTags, 'name')),
    'auto_priority' => (int)$task['auto_priority'],
]);
require_once __DIR__ . '/../views/tasks/edit_view.php';
