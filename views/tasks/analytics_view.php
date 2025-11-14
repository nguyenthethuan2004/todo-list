<?php
$title = 'Analytics';
include __DIR__ . '/../partials/header.php';
$statusData = [
    'labels' => array_values(array_map('taskStatusLabel', array_keys($statusBreakdown))),
    'values' => array_values($statusBreakdown),
    'label' => 'Số lượng',
];
$priorityData = [
    'labels' => array_values(array_map('priorityLabel', array_keys($priorityBreakdown))),
    'values' => array_values($priorityBreakdown),
    'label' => 'Số lượng',
];
$monthlyLabels = array_map(function ($ym) {
    $dt = DateTime::createFromFormat('Y-m', $ym . '-01');
    return $dt ? 'Tháng ' . $dt->format('m/Y') : $ym;
}, array_column($monthlyCompletion, 'ym'));
$monthlyData = [
    'labels' => $monthlyLabels,
    'values' => array_map('intval', array_column($monthlyCompletion, 'total')),
    'colors' => ['#0d6efd'],
    'label' => 'Số task hoàn thành',
];
?>
<h3 class="mb-4">Báo cáo tổng quan</h3>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Tổng công việc</h6>
                <div class="display-6"><?= (int)($summary['total'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Hoàn thành</h6>
                <div class="display-6 text-success"><?= (int)($summary['completed'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Quá hạn</h6>
                <div class="display-6 text-danger"><?= (int)($summary['overdue'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Thời gian trung bình (ngày)</h6>
                <div class="display-6">
                    <?= !empty($summary['avg_duration']) ? number_format($summary['avg_duration'], 1) : '0' ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm chart-card">
            <div class="card-body">
                <h5 class="card-title">Tỷ lệ theo trạng thái</h5>
                <canvas data-chart="doughnut" data-chart-dataset='<?= json_encode($statusData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>'></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm chart-card">
            <div class="card-body">
                <h5 class="card-title">Ưu tiên</h5>
                <canvas data-chart="doughnut" data-chart-dataset='<?= json_encode($priorityData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>'></canvas>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm chart-card mb-4">
    <div class="card-body">
        <h5 class="card-title">Xu hướng hoàn thành theo tháng</h5>
        <canvas data-chart="line" data-chart-dataset='<?= json_encode($monthlyData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>'></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
