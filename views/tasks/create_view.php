<?php
$title = 'Thêm công việc';
include __DIR__ . '/../partials/header.php';
$statusOptions = taskStatusOptions();
$priorityOptions = priorityOptions();
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h3 class="mb-3">Tạo công việc mới</h3>
        <?php if ($error = getFlash('error')): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="<?= url('app/controllers/taskController.php?action=create') ?>">
            <div class="mb-3">
                <label class="form-label">Tiêu đề *</label>
                <input type="text" name="title" class="form-control" required value="<?= sanitize($formData['title']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3"><?= sanitize($formData['description']) ?></textarea>
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
                            <option value="<?= $value ?>" <?= $formData['status']===$value ? 'selected' : '' ?>>
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
                            <option value="<?= $value ?>" <?= $formData['priority']===$value ? 'selected' : '' ?>>
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
                    <label class="form-label">Tags (phân tách bằng dấu phẩy)</label>
                    <input type="text" name="tags" class="form-control" value="<?= sanitize($formData['tags']) ?>" placeholder="VD: DESIGN, REPORT">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-success">Lưu</button>
                <a href="<?= url('public/tasks.php') ?>" class="btn btn-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
