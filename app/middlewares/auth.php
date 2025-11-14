<?php
require_once __DIR__ . '/../helpers/functions.php';
function checkLogin(): void {
    if (empty($_SESSION['user'])) {
        redirect(url('public/login.php'));
    }
}
