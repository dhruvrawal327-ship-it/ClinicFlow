
<?php

require_once "includes/auth.php";
requireLogin();

$role = $_SESSION['user_role'];
$today = date('Y-m-d');

// Dashboard statistics
$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM patients WHERE DATE(created_at) = ?"
);
$stmt->execute([$today]);
$totalPatients = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM appointments WHERE appointment_date = ?"
);
$stmt->execute([$today]);
$totalAppointments = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM queue WHERE queue_date = ? AND queue_status = 'Waiting'"
);
$stmt->execute([$today]);
$waitingPatients = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM queue WHERE queue_date = ? AND queue_status = 'In Consultation'"
);
$stmt->execute([$today]);
$inConsultation = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM queue WHERE queue_date = ? AND queue_status = 'Completed'"
);
$stmt->execute([$today]);
$completedPatients = $stmt->fetchColumn();

// Doctor's current patient
$currentPatient = null;
$waitingList = [];

if ($role === 'doctor') {

    $currentStmt = $pdo->prepare(
        "SELECT q.id, q.token_number, q.patient_type,
                q.check_in_at, p.name, p.age, p.gender, p.phone
         FROM queue q
         INNER JOIN patients p ON p.id = q.patient_id
         WHERE q.queue_date = ?
         AND q.queue_status = 'In Consultation'
         ORDER BY q.check_in_at ASC
         LIMIT 1"
    );

    $currentStmt->execute([$today]);
    $currentPatient = $currentStmt->fetch();

    // Fetch waiting patients
    $waitingStmt = $pdo->prepare(
        "SELECT q.id, q.token_number, q.patient_type,
                q.check_in_at, p.name, p.age, p.gender, p.phone
         FROM queue q
         INNER JOIN patients p ON p.id = q.patient_id
         WHERE q.queue_date = ?
         AND q.queue_status = 'Waiting'
         ORDER BY q.check_in_at ASC, q.token_number ASC"
    );

    $waitingStmt->execute([$today]);
    $waitingList = $waitingStmt->fetchAll();
}

require_once "includes/header.php";
?>

<div class="page-heading mb-4">
    <div>
        <h3>Good day, <?= e($_SESSION['user_name']) ?></h3>
        <p class="text-muted mb-0">
            Here's your clinic overview for today.
        </p>
    </div>

    <span class="date-label"><?= date('d M Y') ?></span>
</div>

<?php if (isset($_GET['called'])): ?>
    <div class="alert alert-success">
        Patient called successfully.
    </div>
<?php elseif (isset($_GET['completed'])): ?>
    <div class="alert alert-success">
        Consultation marked as completed.
    </div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-warning">
        <?php
        $messages = [
            'current' => 'A patient is already in consultation. Complete that consultation first.',
            'empty' => 'There are no waiting patients in the queue.',
            'failed' => 'Something went wrong. Please try again.'
        ];
        echo e($messages[$_GET['error']] ?? 'Unable to perform this action.');
        ?>
    </div>
<?php endif; ?>


<?php if ($role === 'receptionist'): ?>

    <!-- RECEPTIONIST DASHBOARD -->

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="bi bi-people"></i>
                </div>
                <p>Total Patients Registered Today</p>
                <h3><?= $totalPatients ?></h3>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon teal">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <p>Today's Appointments</p>
                <h3><?= $totalAppointments ?></h3>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="bi bi-clock-history"></i>
                </div>
                <p>Waiting Patients</p>
                <h3><?= $waitingPatients ?></h3>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="bi bi-check-circle"></i>
                </div>
                <p>Completed Consultations</p>
                <h3><?= $completedPatients ?></h3>
            </div>
        </div>

    </div>

<?php elseif ($role === 'doctor'): ?>

    <!-- DOCTOR DASHBOARD -->

    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <p>Patients Waiting</p>
                <h3><?= $waitingPatients ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon teal">
                    <i class="bi bi-person-check"></i>
                </div>
                <p>In Consultation</p>
                <h3><?= $inConsultation ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="bi bi-check-circle"></i>
                </div>
                <p>Completed Today</p>
                <h3><?= $completedPatients ?></h3>
            </div>
        </div>

    </div>

    <!-- CURRENT PATIENT -->

    <div class="content-card mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Current Consultation</h5>
                <p class="text-muted small mb-0">
                    Patient currently being attended.
                </p>
            </div>

            <span class="status-badge status-in-progress">
                In Consultation
            </span>
        </div>

        <?php if ($currentPatient): ?>

            <div class="current-patient-card">

                <div class="current-patient-token">
                    T<?= str_pad($currentPatient['token_number'], 3, '0', STR_PAD_LEFT) ?>
                </div>

                <div class="flex-grow-1">
                    <h4><?= e($currentPatient['name']) ?></h4>

                    <p class="text-muted mb-1">
                        <?= e($currentPatient['age']) ?> years
                        · <?= e($currentPatient['gender']) ?>
                        · <?= e($currentPatient['phone']) ?>
                    </p>

                    <small class="text-muted">
                        <?= e($currentPatient['patient_type']) ?>
                        · Checked in at
                        <?= date('h:i A', strtotime($currentPatient['check_in_at'])) ?>
                    </small>
                </div>

                <form method="POST" action="/ClinicFlow/queue/actions.php"
                      data-confirm="Are you sure you want to mark this consultation as completed?"
                      data-confirm-title="Complete Consultation?"
                      data-confirm-action="Complete Consultation"
                      data-confirm-tone="success">

                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrfToken()) ?>">

                    <input type="hidden" name="action" value="complete">

                    <input type="hidden" name="queue_id"
                           value="<?= (int)$currentPatient['id'] ?>">

                    <button type="submit" class="btn btn-clinic">
                        <i class="bi bi-check-circle"></i>
                        Complete Consultation
                    </button>

                </form>

            </div>

        <?php else: ?>

            <div class="text-center py-4">
                <i class="bi bi-person-check fs-1 brand-text"></i>
                <h5 class="mt-3">No Active Consultation</h5>
                <p class="text-muted mb-0">
                    Call the next patient to begin a consultation.
                </p>
            </div>

        <?php endif; ?>

    </div>


    <!-- WAITING QUEUE -->

    <div class="content-card">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <div>
                <h5 class="mb-1">Waiting Queue</h5>
                <p class="text-muted small mb-0">
                    Patients waiting for consultation.
                </p>
            </div>

            <form method="POST" action="/ClinicFlow/queue/actions.php">

                <input type="hidden" name="csrf_token"
                       value="<?= e(csrfToken()) ?>">

                <input type="hidden" name="action" value="call_next">

                <button type="submit" class="btn btn-clinic"
                    <?= ($currentPatient || empty($waitingList)) ? 'disabled' : '' ?>>

                    <i class="bi bi-megaphone"></i>
                    Call Next Patient

                </button>

            </form>

        </div>

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Patient Name</th>
                        <th>Age / Gender</th>
                        <th>Patient Type</th>
                        <th>Check-in Time</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($waitingList)): ?>

                        <tr>
                            <td colspan="6" class="text-center py-4">
                                No patients are currently waiting.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($waitingList as $patient): ?>

                            <tr>

                                <td>
                                    <strong class="queue-token">
                                        T<?= str_pad($patient['token_number'], 3, '0', STR_PAD_LEFT) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong><?= e($patient['name']) ?></strong>
                                    <small class="d-block text-muted">
                                        <?= e($patient['phone']) ?>
                                    </small>
                                </td>

                                <td>
                                    <?= e($patient['age']) ?> /
                                    <?= e($patient['gender']) ?>
                                </td>

                                <td><?= e($patient['patient_type']) ?></td>

                                <td>
                                    <?= date('h:i A', strtotime($patient['check_in_at'])) ?>
                                </td>

                                <td>
                                    <span class="status-badge status-waiting">
                                        Waiting
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<?php endif; ?>


<div class="content-card mt-4">

    <div class="d-flex align-items-center justify-content-between mb-3">

        <div>
            <h5 class="mb-1">Welcome to ClinicFlow</h5>
            <p class="text-muted small mb-0">
                Your clinic management workspace.
            </p>
        </div>

        <i class="bi bi-heart-pulse fs-2 brand-text"></i>

    </div>

    <?php if ($role === 'receptionist'): ?>

        <p class="text-muted mb-0">
            Use the navigation menu to manage patient records,
            appointments and daily registrations.
        </p>

    <?php else: ?>

        <p class="text-muted mb-0">
            Manage your daily consultations and attend patients
            in their queue order.
        </p>

    <?php endif; ?>

</div>

<?php require_once "includes/footer.php"; ?>
