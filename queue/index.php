
<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();

$today = date("Y-m-d");

$stmt = $pdo->prepare("
    SELECT
        q.id,
        q.token_number,
        q.patient_type,
        q.queue_status,
        q.check_in_at,
        p.name AS patient_name,
        p.phone,
        a.appointment_time
    FROM queue q
    INNER JOIN patients p ON p.id = q.patient_id
    LEFT JOIN appointments a ON a.id = q.appointment_id
    WHERE q.queue_date = ?
    ORDER BY q.check_in_at ASC, q.token_number ASC
");
$stmt->execute([$today]);
$queueItems = $stmt->fetchAll();

$total = count($queueItems);
$waiting = 0;
$inConsultation = 0;
$completed = 0;

foreach ($queueItems as $item) {
    if ($item['queue_status'] === 'Waiting') $waiting++;
    if ($item['queue_status'] === 'In Consultation') $inConsultation++;
    if ($item['queue_status'] === 'Completed') $completed++;
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="page-heading">
    <div>
        <h2>Patient Queue</h2>
        <p>Today's unified appointment and walk-in queue.</p>
    </div>

    <?php if ($_SESSION['user_role'] === 'receptionist'): ?>
    <a href="/ClinicFlow/walkins/add.php" class="btn btn-clinic mt-4">
        <i class="bi bi-person-plus"></i> Register Walk-in
    </a>
    <?php endif; ?>
    </div></br>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div>
                <small>Total Patients</small>
                <h3><?= $total ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <small>Waiting</small>
                <h3><?= $waiting ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-video3"></i></div>
            <div>
                <small>In Consultation</small>
                <h3><?= $inConsultation ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <small>Completed</small>
                <h3><?= $completed ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="mb-1">Today's Queue</h5>
            <small class="text-muted"><?= date("d M Y") ?></small>
        </div>
        <a href="/ClinicFlow/queue/index.php" class="btn btn-outline-clinic btn-sm">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Token</th>
                    <th>Patient</th>
                    <th>Type</th>
                    <th>Check-in</th>
                    <th>Appointment</th>
                    <th>Status</th>
                    <th>Token Slip</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($queueItems)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            No patients have joined today's queue yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($queueItems as $item): ?>
                        <tr>
                            <td>
                                <strong class="queue-token">
                                    T<?= str_pad($item['token_number'], 3, "0", STR_PAD_LEFT) ?>
                                </strong>
                            </td>

                            <td>
                                <strong><?= e($item['patient_name']) ?></strong>
                                <small class="d-block text-muted"><?= e($item['phone']) ?></small>
                            </td>

                            <td><?= e($item['patient_type']) ?></td>

                            <td><?= date("h:i A", strtotime($item['check_in_at'])) ?></td>

                            <td>
                                <?php if ($item['appointment_time']): ?>
                                    <?= date("h:i A", strtotime($item['appointment_time'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php
                                $statusClass = [
                                    'Waiting' => 'status-waiting',
                                    'In Consultation' => 'status-in-progress',
                                    'Completed' => 'status-completed'
                                ][$item['queue_status']] ?? '';
                                ?>
                                <span class="status-badge <?= e($statusClass) ?>">
                                    <?= e($item['queue_status']) ?>
                                </span>
                            </td>

                            <td>
                                <a href="/ClinicFlow/queue/token.php?id=<?= (int)$item['id'] ?>"
                                   class="btn btn-outline-clinic btn-sm" target="_blank">
                                    <i class="bi bi-printer"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
