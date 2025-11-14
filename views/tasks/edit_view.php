<?php
$title = 'Chỉnh sửa công việc';
include __DIR__ . '/../partials/header.php';
$statusOptions = taskStatusOptions();
$priorityOptions = priorityOptions();
$progressPercent = calculateProgress($subtaskStats['done'] ?? 0, $subtaskStats['total'] ?? 0);
$redirect = url('public/task_edit.php?id=' . (int)$formData['id']);
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="mb-3">Chỉnh sửa: <?= sanitize($formData['title']) ?></h3>
                <?php if ($error = getFlash('error')): ?>
                    <div class="alert alert-danger"><?= sanitize($error) ?></div>
                <?php endif; ?>
                <form method="POST" action="<?= url('app/controllers/taskController.php?action=update&id=' . (int)$formData['id']) ?>">
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề *</label>
                        <input type="text" name="title" class="form-control" required value="<?= sanitize($formData['title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="4"><?= sanitize($formData['description']) ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Hạn hoàn thành</label>
                            <input type="date" name="due_date" class="form-control" value="<?= sanitize($formData['due_date']) ?>" data-auto-priority="due-date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $formData['status']===$value?'selected':'' ?>>
                                        <?= sanitize($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Mức ưu tiên</label>
                            <select name="priority" class="form-select" data-auto-priority="select">
                                <?php foreach ($priorityOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $formData['priority']===$value?'selected':'' ?>>
                                        <?= sanitize($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="auto_priority" name="auto_priority" value="1" <?= !empty($formData['auto_priority']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_priority">Tự đề xuất theo hạn</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tags</label>
                            <input type="text" name="tags" class="form-control" value="<?= sanitize($formData['tags']) ?>" placeholder="VD: DESIGN, REPORT">
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">Cập nhật</button>
                        <a href="<?= url('public/tasks.php') ?>" class="btn btn-secondary">Huỷ</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="card mt-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Checklist (<?= $subtaskStats['done'] ?? 0 ?>/<?= $subtaskStats['total'] ?? 0 ?>)</h5>
                    <span class="badge bg-primary"><?= $progressPercent ?>%</span>
                </div>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar" style="width: <?= $progressPercent ?>%"></div>
                </div>
                <form class="d-flex gap-2 mb-3" method="POST" action="<?= url('app/controllers/subtaskController.php?action=create&task_id=' . (int)$formData['id']) ?>">
                    <input type="hidden" name="redirect" value="<?= $redirect ?>">
                    <input type="text" name="title" class="form-control" placeholder="Thêm mục checklist">
                    <button class="btn btn-outline-primary">Thêm</button>
                </form>
                <?php foreach ($subtasks as $subtask): ?>
                    <div class="subtask-item">
                        <form method="POST" action="<?= url('app/controllers/subtaskController.php?action=toggle&task_id=' . (int)$formData['id'] . '&id=' . (int)$subtask['id']) ?>">
                            <input type="hidden" name="redirect" value="<?= $redirect ?>">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" onchange="this.form.submit()" <?= $subtask['is_done'] ? 'checked' : '' ?>>
                                <label class="form-check-label <?= $subtask['is_done'] ? 'text-decoration-line-through text-muted' : '' ?>">
                                    <?= sanitize($subtask['title']) ?>
                                </label>
                            </div>
                        </form>
                        <form method="POST" action="<?= url('app/controllers/subtaskController.php?action=delete&task_id=' . (int)$formData['id'] . '&id=' . (int)$subtask['id']) ?>">
                            <input type="hidden" name="redirect" value="<?= $redirect ?>">
                            <button class="btn btn-link text-danger btn-sm">Xoá</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Nhắc việc</h5>
                <form class="row g-2 mb-3" method="POST" action="<?= url('app/controllers/reminderController.php?action=create&task_id=' . (int)$formData['id']) ?>">
                    <input type="hidden" name="redirect" value="<?= $redirect ?>">
                    <div class="col-5">
                        <select name="channel" class="form-select">
                            <option value="email">Email</option>
                            <option value="browser">Browser</option>
                        </select>
                    </div>
                    <div class="col-5">
                        <input type="datetime-local" name="remind_at" class="form-control">
                    </div>
                    <div class="col-2">
                        <button class="btn btn-primary w-100">Lưu</button>
                    </div>
                </form>
                <?php foreach ($reminders as $reminder): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <div class="fw-semibold text-uppercase small"><?= sanitize($reminder['channel']) ?></div>
                            <div class="small text-muted"><?= formatDateTime($reminder['remind_at']) ?></div>
                        </div>
                        <form method="POST" action="<?= url('app/controllers/reminderController.php?action=delete&task_id=' . (int)$formData['id'] . '&id=' . (int)$reminder['id']) ?>">
                            <input type="hidden" name="redirect" value="<?= $redirect ?>">
                            <button class="btn btn-link text-danger btn-sm">Xoá</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Đính kèm</h5>
                <form class="d-flex gap-2 mb-3" method="POST" enctype="multipart/form-data" action="<?= url('app/controllers/attachmentController.php?action=upload&task_id=' . (int)$formData['id']) ?>">
                    <input type="hidden" name="redirect" value="<?= $redirect ?>">
                    <input type="file" name="file" class="form-control" required>
                    <button class="btn btn-outline-primary">Tải lên</button>
                </form>
                <?php foreach ($attachments as $attachment): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <a href="<?= asset($attachment['file_path']) ?>" target="_blank"><?= sanitize($attachment['file_name']) ?></a>
                        <form method="POST" action="<?= url('app/controllers/attachmentController.php?action=delete&task_id=' . (int)$formData['id'] . '&id=' . (int)$attachment['id']) ?>">
                            <input type="hidden" name="redirect" value="<?= $redirect ?>">
                            <button class="btn btn-link text-danger btn-sm">Xoá</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Bình luận</h5>
                <form class="mb-3" method="POST" action="<?= url('app/controllers/commentController.php?action=create&task_id=' . (int)$formData['id']) ?>">
                    <input type="hidden" name="redirect" value="<?= $redirect ?>">
                    <textarea name="content" rows="2" class="form-control" placeholder="Nhập bình luận..."></textarea>
                    <div class="text-end mt-2">
                        <button class="btn btn-outline-secondary btn-sm">Gửi</button>
                    </div>
                </form>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="fw-semibold"><?= sanitize($comment['username']) ?></div>
                        <div class="small text-muted"><?= formatDateTime($comment['created_at']) ?></div>
                        <p class="mb-0"><?= sanitize($comment['content']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if (!empty($isOwner)): ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Chia sẻ & quyền</h5>
                    <form class="row g-2 mb-3" method="POST" action="<?= url('app/controllers/shareController.php?action=add&task_id=' . (int)$formData['id']) ?>">
                        <input type="hidden" name="redirect" value="<?= $redirect ?>">
                        <div class="col-6">
                            <input type="text" name="username" class="form-control" placeholder="Username">
                        </div>
                        <div class="col-4">
                            <select name="role" class="form-select">
                                <option value="viewer">Viewer</option>
                                <option value="editor">Editor</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <button class="btn btn-outline-primary w-100">Thêm</button>
                        </div>
                    </form>
                    <?php foreach ($collaborators as $collab): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <div class="fw-semibold"><?= sanitize($collab['username']) ?></div>
                                <div class="small text-muted text-uppercase"><?= sanitize($collab['role']) ?></div>
                            </div>
                            <form method="POST" action="<?= url('app/controllers/shareController.php?action=remove&task_id=' . (int)$formData['id'] . '&user_id=' . (int)$collab['user_id']) ?>">
                                <input type="hidden" name="redirect" value="<?= $redirect ?>">
                                <button class="btn btn-link text-danger btn-sm">Gỡ</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
