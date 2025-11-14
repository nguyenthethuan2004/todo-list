<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/mail.php';

session_start();
$userModel = new User($pdo);

function handleRegister(array $post): void
{
    global $userModel;
    $username = trim($post['username'] ?? '');
    $email = $post['email'] !== '' ? trim($post['email']) : null;
    $password = $post['password'] ?? '';

    if (strlen($username) < 3 || strlen($password) < 6) {
        setFlash('error', 'Username ≥ 3 ký tự và mật khẩu ≥ 6 ký tự.');
        redirect(url('public/register.php'));
    }
    if ($userModel->findByUsername($username)) {
        setFlash('error', 'Username đã tồn tại.');
        redirect(url('public/register.php'));
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $userModel->createUser($username, $email, $hash);
    if ($email) {
        $body = '<p>Xin chào ' . sanitize($username) . ',</p>'
            . '<p>Bạn đã đăng ký tài khoản thành công tại Todo App.</p>'
            . '<p>Đăng nhập để bắt đầu quản lý công việc của bạn.</p>';
        sendMail($email, 'Chào mừng bạn đến với Todo App', $body);
    }
    setFlash('success', 'Đăng ký thành công, hãy đăng nhập.');
    redirect(url('public/login.php'));
}

function handleLogin(array $post): void
{
    global $userModel;
    $username = trim($post['username'] ?? '');
    $password = $post['password'] ?? '';

    $user = $userModel->findByUsername($username);
    if (!$user || !password_verify($password, $user['password'])) {
        setFlash('error', 'Sai username hoặc password.');
        redirect(url('public/login.php'));
    }
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'theme' => $user['theme'] ?? 'light',
    ];
    $_SESSION['theme'] = $_SESSION['user']['theme'];
    redirect(url('public/tasks.php'));
}

function handleLogout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    redirect(url('public/login.php'));
}

$action = $_GET['action'] ?? '';
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handleRegister($_POST);
} elseif ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handleLogin($_POST);
} elseif ($action === 'logout') {
    handleLogout();
} else {
    redirect(url('public/login.php'));
}
