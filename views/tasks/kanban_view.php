<?php
$title = 'Kanban Board';
include __DIR__ . '/../partials/header.php';
$statusLabels = taskStatusOptions();
?>
<h3 class="mb-4">Bảng Kanban</h3>
<div class="kanban-board">
    <?php foreach (['pending','in_progress','completed'] as $statusKey): ?>
        <div class="kanban-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-uppercase"><?= sanitize($statusLabels[$statusKey]) ?></h5>
                <span class="badge bg-secondary"><?= count($columns[$statusKey]) ?></span>
            </div>
            <?php if (empty($columns[$statusKey])): ?>
                <p class="text-muted small mb-0">Chưa có task.</p>
            <?php endif; ?>
            <?php foreach ($columns[$statusKey] as $task): ?>
                <?php
                    $stats = $progressMap[$task['id']] ?? ['total' => 0, 'done' => 0];
                    $progress = calculateProgress($stats['done'], $stats['total']);
                    $taskTags = $tagsMap[$task['id']] ?? [];
                ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="card-title mb-1"><?= sanitize($task['title']) ?></h6>
                            <span class="badge priority <?= priorityBadgeClass($task['priority']) ?>">
                                <?= sanitize(priorityLabel($task['priority'])) ?>
                            </span>
                        </div>
                        <p class="card-text small text-muted"><?= sanitize($task['description'] ?? '') ?></p>
                        <div class="mb-2">
                            <?php foreach ($taskTags as $tag): ?>
                                <span class="tag-chip"><?= sanitize($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small>Hạn: <?= formatDate($task['due_date']) ?></small>
                            <small><?= $progress ?>%</small>
                        </div>
                        <div class="progress my-2" style="height: 6px;">
                            <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= url('public/task_edit.php?id=' . (int)$task['id']) ?>" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
