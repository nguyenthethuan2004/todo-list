<?php
$title = 'Danh sách công việc';
include __DIR__ . '/../partials/header.php';
$statusOptions = taskStatusOptions();
$priorityOptions = priorityOptions();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Công việc của bạn</h3>
        <small class="text-muted">Quản lý tiến độ hằng ngày</small>
    </div>
    <a href="<?= url('public/task_create.php') ?>" class="btn btn-success">+ Thêm công việc</a>
</div>
<form class="row g-2 mb-3" method="GET" action="<?= url('public/tasks.php') ?>">
    <input type="hidden" name="page" value="1">
    <div class="col-md-6 col-lg-3">
        <select class="form-select" name="status">
            <option value="">-- Tất cả trạng thái --</option>
            <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= $value ?>" <?= ($status===$value) ? 'selected' : '' ?>>
                    <?= sanitize($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 col-lg-3">
        <select class="form-select" name="priority">
            <option value="">-- Tất cả ưu tiên --</option>
            <?php foreach ($priorityOptions as $value => $label): ?>
                <option value="<?= $value ?>" <?= ($priorityFilter===$value) ? 'selected' : '' ?>>
                    <?= sanitize($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 col-lg-3">
        <select class="form-select" name="sort">
            <option value="asc" <?= ($sort==='asc') ? 'selected' : '' ?>>Ngày đến hạn ↑</option>
            <option value="desc" <?= ($sort==='desc') ? 'selected' : '' ?>>Ngày đến hạn ↓</option>
        </select>
    </div>
    <div class="col-md-6 col-lg-3">
        <input type="text" name="q" class="form-control" placeholder="Từ khóa..." value="<?= sanitize($searchKeyword) ?>">
    </div>
    <div class="col-md-6 col-lg-3">
        <input type="text" name="tag" class="form-control" placeholder="Tag (VD: DESIGN)" value="<?= sanitize($selectedTag) ?>">
    </div>
    <div class="col-md-6 col-lg-2">
        <button class="btn btn-outline-primary w-100">Lọc</button>
    </div>
</form>
<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success flash-auto"><?= sanitize($msg) ?></div>
<?php endif; ?>
<?php if ($error = getFlash('error')): ?>
    <div class="alert alert-danger flash-auto"><?= sanitize($error) ?></div>
<?php endif; ?>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Tiêu đề & thẻ</th>
                <th>Ưu tiên</th>
                <th>Hạn</th>
                <th>Trạng thái</th>
                <th>Tiến độ</th>
                <th class="text-end">Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($tasks)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted">Chưa có công việc nào.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <?php
                    $stats = $progressMap[$task['id']] ?? ['total' => 0, 'done' => 0];
                    $progress = calculateProgress($stats['done'], $stats['total']);
                    $taskTags = $tagsMap[$task['id']] ?? [];
                ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= sanitize($task['title']) ?></div>
                        <div class="text-muted small"><?= sanitize($task['description'] ?? '') ?></div>
                        <div class="mt-1">
                            <?php foreach ($taskTags as $tag): ?>
                                <span class="tag-chip"><?= sanitize($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge priority <?= priorityBadgeClass($task['priority']) ?>">
                            <?= sanitize(priorityLabel($task['priority'])) ?>
                        </span>
                    </td>
                    <td><?= formatDate($task['due_date']) ?></td>
                    <td>
                        <span class="badge bg-secondary text-uppercase">
                            <?= sanitize(taskStatusLabel($task['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%;"></div>
                        </div>
                        <div class="progress-info mt-1"><?= $progress ?>% | <?= $stats['done'] ?>/<?= $stats['total'] ?></div>
                    </td>
                    <td class="text-end">
                        <a href="<?= url('public/task_edit.php?id=' . (int)$task['id']) ?>" class="btn btn-sm btn-primary">Sửa</a>
                        <form method="POST" action="<?= url('app/controllers/taskController.php?action=delete&id=' . (int)$task['id']) ?>"
                              class="d-inline" onsubmit="return confirm('Bạn chắc chắn xóa?');">
                            <button class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (($pages ?? 1) > 1): ?>
    <?php $baseQuery = $_GET; unset($baseQuery['page']); ?>
    <nav aria-label="Task pagination" class="mt-3">
        <ul class="pagination justify-content-center">
            <?php $prev = max(1, ($currentPage ?? 1) - 1); ?>
            <?php $next = min($pages, ($currentPage ?? 1) + 1); ?>
            <li class="page-item <?= ($currentPage ?? 1) <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= url('public/tasks.php?' . http_build_query(array_merge($baseQuery, ['page' => $prev]))) ?>">«</a>
            </li>
            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <li class="page-item <?= ($currentPage ?? 1) === $p ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('public/tasks.php?' . http_build_query(array_merge($baseQuery, ['page' => $p]))) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($currentPage ?? 1) >= $pages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= url('public/tasks.php?' . http_build_query(array_merge($baseQuery, ['page' => $next]))) ?>">»</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
<?php include __DIR__ . '/../partials/footer.php'; ?>
