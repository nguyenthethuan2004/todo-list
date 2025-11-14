<?php
if (!defined('BASE_URL')) {
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/../..'));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $basePath = '';
    if ($projectRoot && $docRoot && strpos($projectRoot, $docRoot) === 0) {
        $basePath = trim(substr($projectRoot, strlen($docRoot)), '/');
    }
    define('BASE_URL', $basePath ? '/' . $basePath : '');
}

function url(string $path = ''): string {
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    if ($base === '') {
        return '/' . $path;
    }
    if ($path === '') {
        return $base ?: '/';
    }
    return $base . '/' . $path;
}

function asset(string $path = ''): string {
    return url($path);
}

function formatDate(?string $date, string $format = 'd/m/Y'): string {
    if (empty($date)) {
        return '-';
    }
    $dt = date_create($date);
    return $dt ? $dt->format($format) : $date;
}

function taskStatusOptions(): array {
    return [
        'pending' => 'Chờ xử lý',
        'in_progress' => 'Đang thực hiện',
        'completed' => 'Hoàn thành',
    ];
}

function taskStatusLabel(string $status): string {
    $options = taskStatusOptions();
    return $options[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function priorityOptions(): array {
    return [
        'high' => 'Cao',
        'medium' => 'Trung bình',
        'low' => 'Thấp',
    ];
}

function priorityLabel(string $priority): string {
    $options = priorityOptions();
    return $options[$priority] ?? ucfirst($priority);
}

function priorityBadgeClass(string $priority): string {
    return match ($priority) {
        'high' => 'bg-danger',
        'medium' => 'bg-warning text-dark',
        'low' => 'bg-success',
        default => 'bg-secondary',
    };
}

function suggestPriority(?string $dueDate): string {
    if (empty($dueDate)) {
        return 'medium';
    }
    $today = new DateTimeImmutable('today');
    $due = DateTimeImmutable::createFromFormat('Y-m-d', $dueDate);
    if (!$due) {
        return 'medium';
    }
    $diff = $today->diff($due)->days ?? 0;
    if ($due < $today || $diff <= 2) {
        return 'high';
    }
    if ($diff <= 7) {
        return 'medium';
    }
    return 'low';
}

function calculateProgress(int $done, int $total): int {
    if ($total === 0) {
        return 0;
    }
    return (int) round(($done / $total) * 100);
}

function formatDateTime(?string $value, string $format = 'd/m/Y H:i'): string {
    if (empty($value)) {
        return '-';
    }
    $dt = date_create($value);
    return $dt ? $dt->format($format) : $value;
}

function suggestTagsFromText(string $text): array {
    $map = [
        'design' => ['design', 'ui', 'ux', 'giao diện'],
        'backend' => ['api', 'backend', 'server', 'logic'],
        'frontend' => ['frontend', 'react', 'vue', 'html', 'css'],
        'marketing' => ['ads', 'marketing', 'campaign'],
        'urgent' => ['khẩn', 'gấp', 'urgent'],
        'meeting' => ['meeting', 'họp'],
        'report' => ['report', 'báo cáo', 'thống kê'],
    ];
    $textLower = mb_strtolower($text, 'UTF-8');
    $tags = [];
    foreach ($map as $tag => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($textLower, $keyword)) {
                $tags[] = strtoupper($tag);
                break;
            }
        }
    }
    return array_values(array_unique($tags));
}

function currentTheme(): string {
    if (!empty($_SESSION['user']['theme'])) {
        return $_SESSION['user']['theme'];
    }
    if (!empty($_SESSION['theme'])) {
        return $_SESSION['theme'];
    }
    return 'light';
}

function bodyClass(): string {
    return currentTheme() === 'dark' ? 'theme-dark' : 'theme-light';
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}
function sanitize(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
function setFlash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}
function getFlash(string $key): ?string {
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
