
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("receptionist");

$errors = [];
$patientId = "";
$appointmentDate = "";
$appointmentTime = "";

$patients = $pdo->query(
    "SELECT id, name, phone
     FROM patients
     WHERE deleted_at IS NULL
     ORDER BY name ASC"
)->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCsrfToken()) {
        $errors[] = "Invalid request. Please refresh the page.";
    } else {
        $patientId = trim($_POST["patient_id"] ?? "");
        $appointmentDate = trim($_POST["appointment_date"] ?? "");
        $appointmentTime = trim($_POST["appointment_time"] ?? "");

        if (filter_var($patientId, FILTER_VALIDATE_INT) === false
            || (int)$patientId < 1) {
            $errors[] = "Please select a valid patient.";
        } else {
            $checkPatient = $pdo->prepare(
                "SELECT id FROM patients
                 WHERE id = ? AND deleted_at IS NULL"
            );
            
            $checkPatient->execute([(int)$patientId]);

            if (!$checkPatient->fetch()) {
                $errors[] = "Selected patient does not exist.";
            }
        }

        $dateObject = DateTime::createFromFormat('!Y-m-d', $appointmentDate);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $appointmentDate) {
            $errors[] = "Please select a valid appointment date.";
        } elseif ($appointmentDate < date('Y-m-d')) {
            $errors[] = "Appointment date cannot be in the past.";
        }

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $appointmentTime)) {
            $errors[] = "Please select a valid appointment time.";
        } elseif ($appointmentDate === date('Y-m-d')
            && $appointmentTime < date('H:i')) {
            $errors[] = "Appointment time cannot be in the past.";
        }

        if (empty($errors)) {
            $checkDuplicate = $pdo->prepare(
                "SELECT id FROM appointments
                 WHERE patient_id = ?
                 AND appointment_date = ?
                 AND appointment_time = ?
                 AND status IN ('Booked', 'Arrived')
                 LIMIT 1"
            );

            $checkDuplicate->execute([
                (int)$patientId,
                $appointmentDate,
                $appointmentTime
            ]);

            if ($checkDuplicate->fetch()) {
                $errors[] = "This patient already has an active appointment at this date and time.";
            }
        }

        if (empty($errors)) {
            $insert = $pdo->prepare(
                "INSERT INTO appointments
                 (patient_id, appointment_date, appointment_time)
                 VALUES (?, ?, ?)"
            );

            $insert->execute([
                (int)$patientId,
                $appointmentDate,
                $appointmentTime
            ]);

            header("Location: list.php?added=1");
            exit;
        }
    }
}

$pageTitle = "Book Appointment";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="page-title">Book Appointment</h2>
        <p class="page-subtitle">Schedule an appointment for a registered patient.</p>
    </div>

    <a href="list.php" class="btn btn-outline-clinic">
        <i class="bi bi-arrow-left"></i> Back to Appointments
    </a>
</div>

<div class="content-card">
    <h5 class="mb-4">Appointment Information</h5>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($patients)): ?>
        <div class="alert alert-warning">
            No patients are registered yet.
            <a href="/ClinicFlow/patients/add.php">Register a patient first.</a>
        </div>
    <?php else: ?>

    <form method="POST">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrfToken()) ?>">

        <div class="row g-3">

            <div class="col-md-12">
                <label class="form-label">Select Patient</label>
                <select name="patient_id" class="form-select" required>
                    <option value="">Choose a patient</option>

                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= (int)$patient["id"] ?>"
                            <?= (string)$patientId === (string)$patient["id"] ? "selected" : "" ?>>
                            <?= e($patient["name"]) ?> -
                            <?= e($patient["phone"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Appointment Date</label>
                <input type="date"
                       name="appointment_date"
                       class="form-control"
                       min="<?= date('Y-m-d') ?>"
                       value="<?= e($appointmentDate) ?>"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Appointment Time</label>
                <input type="time"
                       name="appointment_time"
                       class="form-control"
                       value="<?= e($appointmentTime) ?>"
                       required>
            </div>

        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-clinic">
                <i class="bi bi-calendar-plus"></i> Book Appointment
            </button>

            <a href="list.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
