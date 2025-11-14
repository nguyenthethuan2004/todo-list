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

$filters = [
    'status' => $_GET['status'] ?? '',
    'sort' => $_GET['sort'] ?? 'asc',
    'priority' => $_GET['priority'] ?? '',
    'search' => trim($_GET['q'] ?? ''),
];
$tagName = trim($_GET['tag'] ?? '');
if ($tagName !== '') {
    $tagId = $tagModel->findIdByName($tagName);
    if ($tagId) {
        $filters['tag_ids'] = [$tagId];
    }
}
$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$totalTasks = $taskModel->countAllByUser($userId, $filters);
$totalPages = max(1, (int)ceil($totalTasks / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;
$tasks = $taskModel->getAllByUser($userId, $filters, $perPage, $offset);
$taskIds = array_column($tasks, 'id');
$progressMap = $subtaskModel->statsForTasks($taskIds);
$tagsMap = $tagModel->getForTasks($taskIds);
$status = $filters['status'];
$sort = $filters['sort'];
$priorityFilter = $filters['priority'];
$searchKeyword = $filters['search'];
$selectedTag = $tagName;
$currentPage = $page;
$pages = $totalPages;
$limit = $perPage;

require_once __DIR__ . '/../views/tasks/list_view.php';
