<?php
session_start();
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middlewares/auth.php';
checkLogin();
$formData = [
    'title' => '',
    'description' => '',
    'due_date' => '',
    'status' => 'pending',
    'priority' => 'medium',
    'auto_priority' => 1,
    'tags' => '',
];
require_once __DIR__ . '/../views/tasks/create_view.php';
