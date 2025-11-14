<?php
session_start();
require_once __DIR__ . '/../app/helpers/functions.php';
if (!empty($_SESSION['user'])) {
    redirect(url('public/tasks.php'));
}
redirect(url('public/login.php'));
