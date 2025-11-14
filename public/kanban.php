<?php
session_start();
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/models/Task.php';
require_once __DIR__ . '/../app/models/Subtask.php';
require_once __DIR__ . '/../app/models/Tag.php';
require_once __DIR__ . '/../app/middlewares/auth.php';

checkLogin();
$taskModel = new Task($pdo);
$subtaskModel = new Subtask($pdo);
$tagModel = new Tag($pdo);
$userId = (int) $_SESSION['user']['id'];
$columns = $taskModel->getKanban($userId);
$taskIds = array_merge(
    array_column($columns['pending'], 'id'),
    array_column($columns['in_progress'], 'id'),
    array_column($columns['completed'], 'id')
);
$progressMap = $subtaskModel->statsForTasks($taskIds);
$tagsMap = $tagModel->getForTasks($taskIds);
require_once __DIR__ . '/../views/tasks/kanban_view.php';
