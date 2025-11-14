<?php
session_start();
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/models/Task.php';
require_once __DIR__ . '/../app/middlewares/auth.php';

checkLogin();
$taskModel = new Task($pdo);
$userId = (int) $_SESSION['user']['id'];
$summary = $taskModel->getAnalytics($userId);
$statusBreakdown = $taskModel->getStatusBreakdown($userId);
$priorityBreakdown = $taskModel->getPriorityBreakdown($userId);
$monthlyCompletion = $taskModel->getMonthlyCompletion($userId);
require_once __DIR__ . '/../views/tasks/analytics_view.php';
