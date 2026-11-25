
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("receptionist");

$search = trim($_GET["search"] ?? "");
$status = trim($_GET["status"] ?? "");
$date = trim($_GET["date"] ?? "");

$allowedStatuses = ["Booked", "Arrived", "Cancelled", "No-show"];

$sql = "SELECT
            a.id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            p.name AS patient_name,
            p.phone
        FROM appointments a
        INNER JOIN patients p ON a.patient_id = p.id
        WHERE 1=1";

$params = [];

if ($search !== "") {
    $sql .= " AND (p.name LIKE ? OR p.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (in_array($status, $allowedStatuses, true)) {
    $sql .= " AND a.status = ?";
    $params[] = $status;
}

if ($date !== "") {
    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    if ($dateObject && $dateObject->format('Y-m-d') === $date) {
        $sql .= " AND a.appointment_date = ?";
        $params[] = $date;
    }
}

$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

$pageTitle = "Appointments";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="page-title">Appointments</h2>
        <p class="page-subtitle">View and manage patient appointments.</p>
    </div>

    <a href="add.php" class="btn btn-clinic">
        <i class="bi bi-calendar-plus"></i> Book Appointment
    </a>
</div>

<?php if (isset($_GET["added"])): ?>
    <div class="alert alert-success">Appointment booked successfully.</div>
<?php endif; ?>

<?php if (isset($_GET["arrived"])): ?>
    <div class="alert alert-success">Patient marked as arrived and added to today's queue.</div>
<?php endif; ?>

<?php if (isset($_GET["cancelled"])): ?>
    <div class="alert alert-success">Appointment cancelled successfully.</div>
<?php endif; ?>

<?php if (isset($_GET["error"])): ?>
    <div class="alert alert-danger">
        <?php
        $messages = [
            "linked" => "This appointment has already been added to the queue and cannot be cancelled.",
            "failed" => "Unable to complete the requested action.",
            "date" => "Only appointments scheduled for today can be marked as arrived.",
            "status" => "This appointment is no longer available for that action."
        ];
        echo e($messages[$_GET["error"]] ?? "Something went wrong.");
        ?>
    </div>
<?php endif; ?>

<div class="content-card">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0">Appointment Records</h5>
        <span class="text-muted small">
            <?= count($appointments) ?> appointment(s) found
        </span>
    </div>

    <form method="GET" class="row g-2 mb-4">

        <div class="col-md-4">
            <input type="search" name="search"
                   class="form-control"
                   placeholder="Search patient name or phone"
                   value="<?= e($search) ?>">
        </div>

        <div class="col-md-3">
            <input type="date" name="date"
                   class="form-control"
                   value="<?= e($date) ?>">
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <?php foreach ($allowedStatuses as $option): ?>
                    <option value="<?= e($option) ?>"
                        <?= $status === $option ? "selected" : "" ?>>
                        <?= e($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-clinic flex-grow-1">
                <i class="bi bi-search"></i>
            </button>
            <a href="list.php" class="btn btn-secondary">Reset</a>
        </div>

    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Phone</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($appointments)): ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        No appointments found.
                    </td>
                </tr>
            <?php else: ?>

                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= (int)$appointment["id"] ?></td>
                        <td><?= e($appointment["patient_name"]) ?></td>
                        <td><?= e($appointment["phone"]) ?></td>
                        <td><?= e(date("d M Y", strtotime($appointment["appointment_date"]))) ?></td>
                        <td><?= e(date("h:i A", strtotime($appointment["appointment_time"]))) ?></td>

                        <td>
                            <?php
                            $badgeClass = [
                                "Booked" => "status-waiting",
                                "Arrived" => "status-consultation",
                                "Cancelled" => "status-cancelled",
                                "No-show" => "status-cancelled"
                            ][$appointment["status"]] ?? "status-waiting";
                            ?>
                            <span class="status-badge <?= $badgeClass ?>">
                                <?= e($appointment["status"]) ?>
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-2 flex-wrap appointment-actions">

                                <?php if ($appointment["status"] === "Booked"
                                    && $appointment["appointment_date"] === date("Y-m-d")): ?>

                                    <form method="POST" action="arrive.php"
                                          data-confirm="Do you want to mark this patient as arrived and add them to today's queue?"
                                          data-confirm-title="Confirm Patient Arrival"
                                          data-confirm-action="Mark Arrived"
                                          data-confirm-tone="success">

                                        <input type="hidden" name="csrf_token"
                                               value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$appointment["id"] ?>">

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-clinic">
                                            <i class="bi bi-person-check"></i> Arrived
                                        </button>
                                    </form>

                                <?php endif; ?>

                                <?php if ($appointment["status"] === "Booked"): ?>

                                    <form method="POST" action="cancel.php"
                                         data-confirm="Are you sure you want to cancel this appointment?"
                                         data-confirm-title="Cancel Appointment?"
                                         data-confirm-action="Cancel Appointment"
                                         data-confirm-tone="danger">
                                         
                                        <input type="hidden" name="csrf_token"
                                               value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$appointment["id"] ?>">

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </form>

                                <?php endif; ?>

                                <?php if ($appointment["status"] === "Arrived"): ?>
                                    <span class="text-muted small">In queue</span>
                                <?php endif; ?>

                                <?php if (in_array($appointment["status"], ["Cancelled", "No-show"], true)): ?>
                                    <span class="text-muted small">No actions</span>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
