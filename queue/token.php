
<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: /ClinicFlow/queue/index.php");
    exit;
}

$queueId = (int) $_GET['id'];

$stmt = $pdo->prepare("
    SELECT
        q.id,
        q.token_number,
        q.patient_type,
        q.queue_date,
        q.queue_status,
        q.check_in_at,
        p.name AS patient_name,
        p.phone,
        a.appointment_time
    FROM queue q
    INNER JOIN patients p ON p.id = q.patient_id
    LEFT JOIN appointments a ON a.id = q.appointment_id
    WHERE q.id = ?
");
$stmt->execute([$queueId]);
$token = $stmt->fetch();

if (!$token) {
    header("Location: /ClinicFlow/queue/index.php");
    exit;
}

// Prepare token display values
$displayToken = "T" . str_pad($token['token_number'], 3, "0", STR_PAD_LEFT);
$displayStatus = $token['queue_status'];

require_once __DIR__ . "/../includes/header.php";
?>


<div class="page-heading">
    <div>
        <h2>Token Confirmation</h2>
        <p>Your queue token has been generated successfully.</p>
    </div>
    <a href="/ClinicFlow/queue/index.php" class="btn btn-outline-clinic">
        <i class="bi bi-list-ol"></i> View Queue
    </a>
</div>

<div class="token-confirmation-wrapper">
    <div class="content-card token-slip" id="tokenSlip">

        <div class="token-slip-brand">
            <div class="brand-icon">
                <i class="bi bi-hospital"></i>
            </div>
            <div>
                <h3>ClinicFlow</h3>
                <p>Smart Clinic Management</p>
            </div>
        </div>

        <div class="token-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>Token Generated Successfully</span>
        </div>

        <div class="token-number-area">
            <small>YOUR QUEUE TOKEN</small>
            <h1><?= e($displayToken) ?></h1>
            <span class="status-badge"><?= e($displayStatus) ?></span>
        </div>

        <div class="token-details">
            <div>
                <small>Patient Name</small>
                <strong><?= e($token['patient_name']) ?></strong>
            </div>

            <div>
                <small>Patient Type</small>
                <strong><?= e($token['patient_type']) ?></strong>
            </div>

            <div>
                <small>Appointment Time</small>
                <strong>
                    <?php if (!empty($token['appointment_time'])): ?>
                        <?= date("h:i A", strtotime($token['appointment_time'])) ?>
                    <?php else: ?>
                        Walk-in
                    <?php endif; ?>
                </strong>
            </div>

            <div>
                <small>Check-in Time</small>
                <strong><?= date("h:i A", strtotime($token['check_in_at'])) ?></strong>
            </div>

            <div>
                <small>Date</small>
                <strong><?= date("d M Y", strtotime($token['queue_date'])) ?></strong>
            </div>

            <div>
                <small>Queue Status</small>
                <strong><?= e($displayStatus) ?></strong>
            </div>
        </div>

        <div class="token-slip-note">
            Please wait until your token number is called.
            Thank you for visiting!
        </div>

    </div>

    <div class="token-actions no-print">
        <button type="button" class="btn btn-clinic" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Token
        </button>

        <a href="/ClinicFlow/queue/index.php" class="btn btn-outline-clinic">
            <i class="bi bi-list-ol"></i> Open Queue
        </a>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
