
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("receptionist");

$errors = [];

$mode = "existing";
$patientId = "";
$name = "";
$age = "";
$gender = "";
$phone = "";

// Fetch active patients only.
$patients = $pdo->query(
    "SELECT id, name, phone
     FROM patients
     WHERE deleted_at IS NULL
     ORDER BY name ASC"
)->fetchAll();

// Fetch successful registration details.
$successRecord = null;

if (isset($_GET["queue_id"])) {
    $queueId = filter_input(INPUT_GET, "queue_id", FILTER_VALIDATE_INT);

    if ($queueId) {
        $successStmt = $pdo->prepare(
            "SELECT q.id, q.token_number, q.queue_date, q.check_in_at,
                    p.name, q.patient_type
             FROM queue q
             INNER JOIN patients p ON q.patient_id = p.id
             WHERE q.id = ? AND q.patient_type = 'Walk-in'"
        );

        $successStmt->execute([$queueId]);
        $successRecord = $successStmt->fetch();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verifyCsrfToken()) {
        $errors[] = "Invalid request. Please refresh the page.";

    } else {

        $mode = $_POST["patient_mode"] ?? "existing";

        if ($mode === "existing") {

            $patientId = trim($_POST["patient_id"] ?? "");

            if (
                filter_var($patientId, FILTER_VALIDATE_INT) === false
                || (int)$patientId < 1
            ) {
                $errors[] = "Please select a patient.";
            }

        } elseif ($mode === "new") {

            $name = trim($_POST["name"] ?? "");
            $age = trim($_POST["age"] ?? "");
            $gender = trim($_POST["gender"] ?? "");
            $phone = trim($_POST["phone"] ?? "");

            if ($name === "" || strlen($name) > 100) {
                $errors[] = "Please enter a valid patient name.";
            }

            if (
                filter_var($age, FILTER_VALIDATE_INT) === false
                || (int)$age < 0
                || (int)$age > 120
            ) {
                $errors[] = "Please enter a valid age between 0 and 120.";
            }

            if (!in_array($gender, ["Male", "Female", "Other"], true)) {
                $errors[] = "Please select a valid gender.";
            }

            if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
                $errors[] = "Please enter a valid 10-digit Indian mobile number.";
            }

        } else {
            $errors[] = "Please select a valid registration option.";
        }

        if (empty($errors)) {

            $lockName = "clinicflow_daily_token_" . date("Ymd");
            $lockAcquired = false;

            try {

                // Prevent simultaneous token generation conflicts.
                $lockStmt = $pdo->prepare("SELECT GET_LOCK(?, 10)");
                $lockStmt->execute([$lockName]);

                if ((int)$lockStmt->fetchColumn() !== 1) {
                    throw new RuntimeException(
                        "Could not generate a token. Please try again."
                    );
                }

                $lockAcquired = true;

                $pdo->beginTransaction();

                // Get existing patient or register a new one.
                if ($mode === "existing") {

                    // Only active patients can be selected.
                    $checkPatient = $pdo->prepare(
                        "SELECT id
                         FROM patients
                         WHERE id = ?
                         AND deleted_at IS NULL"
                    );

                    $checkPatient->execute([(int)$patientId]);
                    $patient = $checkPatient->fetch();

                    if (!$patient) {
                        throw new RuntimeException(
                            "Selected patient does not exist or is no longer active."
                        );
                    }

                    $finalPatientId = (int)$patient["id"];

                } else {

                    /*
                     * IMPORTANT:
                     * Check the mobile number BEFORE inserting
                     * a new patient.
                     *
                     * Deleted patients are excluded from this check.
                     */
                    $duplicateCheck = $pdo->prepare(
                        "SELECT id, name
                         FROM patients
                         WHERE phone = ?
                         AND deleted_at IS NULL
                         LIMIT 1"
                    );

                    $duplicateCheck->execute([$phone]);
                    $duplicatePatient = $duplicateCheck->fetch();

                    if ($duplicatePatient) {
                        throw new RuntimeException(
                            "This mobile number is already registered to " .
                            $duplicatePatient["name"] .
                            " (Patient ID: " . $duplicatePatient["id"] .
                            "). Please select this patient under Existing Patient."
                        );
                    }

                    // Insert only when the mobile number is unique.
                    $insertPatient = $pdo->prepare(
                        "INSERT INTO patients (name, age, gender, phone)
                         VALUES (?, ?, ?, ?)"
                    );

                    $insertPatient->execute([
                        $name,
                        (int)$age,
                        $gender,
                        $phone
                    ]);

                    $finalPatientId = (int)$pdo->lastInsertId();
                }

                // Prevent a patient with an existing appointment today
                // from being accidentally registered as a walk-in.
                $appointmentCheck = $pdo->prepare(
                    "SELECT id FROM appointments
                     WHERE patient_id = ?
                     AND appointment_date = ?
                     AND status = 'Booked'
                     LIMIT 1"
                );

                $today = date("Y-m-d");

                $appointmentCheck->execute([
                    $finalPatientId,
                    $today
                ]);

                if ($appointmentCheck->fetch()) {
                    throw new RuntimeException(
                        "This patient already has an appointment today. Please mark the appointment as Arrived instead."
                    );
                }

                // Prevent duplicate active queue entries.
                $queueCheck = $pdo->prepare(
                    "SELECT id FROM queue
                     WHERE patient_id = ?
                     AND queue_date = ?
                     AND queue_status IN ('Waiting', 'In Consultation')
                     LIMIT 1"
                );

                $queueCheck->execute([
                    $finalPatientId,
                    $today
                ]);

                if ($queueCheck->fetch()) {
                    throw new RuntimeException(
                        "This patient is already in today's queue. Please check the existing token."
                    );
                }

                // Generate next available token for today.
                $tokenStmt = $pdo->prepare(
                    "SELECT COALESCE(MAX(token_number), 0) + 1
                     FROM queue
                     WHERE queue_date = ?"
                );

                $tokenStmt->execute([$today]);
                $tokenNumber = (int)$tokenStmt->fetchColumn();

                // Add walk-in patient to the queue.
                $insertQueue = $pdo->prepare(
                    "INSERT INTO queue
                     (patient_id, appointment_id, token_number,
                      patient_type, queue_date, queue_status)
                     VALUES (?, NULL, ?, 'Walk-in', ?, 'Waiting')"
                );

                $insertQueue->execute([
                    $finalPatientId,
                    $tokenNumber,
                    $today
                ]);

                $queueId = (int)$pdo->lastInsertId();

                $pdo->commit();

                header("Location: add.php?queue_id=" . $queueId);
                exit;

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log($e->getMessage());

                $errors[] = $e instanceof RuntimeException
                    ? $e->getMessage()
                    : "Unable to register the walk-in patient. Please try again.";

            } finally {

                if ($lockAcquired) {
                    $release = $pdo->prepare("SELECT RELEASE_LOCK(?)");
                    $release->execute([$lockName]);
                }
            }
        }
    }
}

$pageTitle = "Walk-in Registration";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="page-title">Walk-in Registration</h2>
        <p class="page-subtitle">
            Register patients who visit without an appointment.
        </p>
    </div>

    <a href="/ClinicFlow/dashboard.php" class="btn btn-outline-clinic">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
</div>

<?php if ($successRecord): ?>

    <div class="content-card text-center py-5">

        <div class="mb-3">
            <i class="bi bi-check-circle-fill"
               style="font-size:55px;color:#22C58B;"></i>
        </div>

        <h3 class="text-white">Walk-in Registered Successfully!</h3>

        <p class="text-muted mb-4">
            The patient has been added to today's waiting queue.
        </p>

        <div class="mx-auto p-4 rounded-3"
             style="max-width:350px;background:#101D30;border:1px solid #293B52;">

            <small class="text-muted">PATIENT NAME</small>

            <h5 class="text-white mt-1">
                <?= e($successRecord["name"]) ?>
            </h5>

            <small class="text-muted d-block mt-3">QUEUE TOKEN</small>

            <h1 class="my-2"
                style="font-size:48px;color:#20C6C8;">
                <?= str_pad(
                    (string)$successRecord["token_number"],
                    3,
                    "0",
                    STR_PAD_LEFT
                ) ?>
            </h1>

            <span class="status-badge status-waiting">Waiting</span>

            <p class="text-muted small mt-3 mb-0">
                <?= e(date(
                    "d M Y",
                    strtotime($successRecord["queue_date"])
                )) ?>
            </p>

        </div>

        <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">

            <a href="/ClinicFlow/queue/token.php?id=<?= (int)$successRecord['id'] ?>"
               class="btn btn-clinic" target="_blank">
                <i class="bi bi-printer"></i> View / Print Token
            </a>

            <a href="/ClinicFlow/walkins/add.php"
               class="btn btn-outline-clinic">
                <i class="bi bi-person-plus"></i> Register Another Patient
            </a>

            <a href="/ClinicFlow/queue/index.php"
               class="btn btn-outline-clinic">
                <i class="bi bi-list-ol"></i> View Queue
            </a>

        </div>

    </div>

<?php else: ?>

    <div class="content-card">

        <h5 class="mb-4">Patient Information</h5>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="walkinForm">

            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <div class="mb-4">

                <label class="form-label">Patient Registration Type</label>

                <div class="d-flex flex-wrap gap-4 mt-2">

                    <label class="d-flex align-items-center gap-2">
                        <input type="radio" name="patient_mode"
                               value="existing"
                               <?= $mode === "existing" ? "checked" : "" ?>>
                        Existing Patient
                    </label>

                    <label class="d-flex align-items-center gap-2">
                        <input type="radio" name="patient_mode"
                               value="new"
                               <?= $mode === "new" ? "checked" : "" ?>>
                        Register New Patient
                    </label>

                </div>
            </div>

            <!-- EXISTING PATIENT -->
            <div id="existingPatientSection">

                <label class="form-label">Select Existing Patient</label>

                <select name="patient_id" id="patient_id"
                        class="form-select">

                    <option value="">Choose a patient</option>

                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= (int)$patient["id"] ?>"
                            <?= (string)$patientId === (string)$patient["id"] ? "selected" : "" ?>>

                            <?= e($patient["name"]) ?>
                            - <?= e($patient["phone"]) ?>

                        </option>
                    <?php endforeach; ?>

                </select>

                <?php if (empty($patients)): ?>
                    <small class="text-muted">
                        No existing patients found. Select Register New Patient.
                    </small>
                <?php endif; ?>

            </div>

            <!-- NEW PATIENT -->
            <div id="newPatientSection" style="display:none;">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">Patient Name</label>

                        <input type="text" name="name" id="name"
                               class="form-control"
                               maxlength="100"
                               value="<?= e($name) ?>"
                               placeholder="Enter patient name">

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Age</label>

                        <input type="number" name="age" id="age"
                               class="form-control"
                               min="0" max="120"
                               value="<?= e($age) ?>"
                               placeholder="Enter age">

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Gender</label>

                        <select name="gender" id="gender"
                                class="form-select">

                            <option value="">Select gender</option>

                            <option value="Male"
                                <?= $gender === "Male" ? "selected" : "" ?>>
                                Male
                            </option>

                            <option value="Female"
                                <?= $gender === "Female" ? "selected" : "" ?>>
                                Female
                            </option>

                            <option value="Other"
                                <?= $gender === "Other" ? "selected" : "" ?>>
                                Other
                            </option>

                        </select>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Phone Number</label>

                        <div class="input-group">
                            <span class="input-group-text">+91</span>

                            <input type="tel" name="phone" id="phone"
                                   class="form-control"
                                   minlength="10"
                                   maxlength="10"
                                   pattern="[6-9][0-9]{9}"
                                   inputmode="numeric"
                                   value="<?= e($phone) ?>"
                                   placeholder="10-digit mobile number">
                        </div>

                        <small class="text-muted">
                            Enter a 10-digit Indian mobile number.
                        </small>

                    </div>

                </div>

            </div>

            <div class="mt-4">

                <button type="submit" class="btn btn-clinic">
                    <i class="bi bi-person-check"></i>
                    Register Walk-in & Generate Token
                </button>

            </div>

        </form>

    </div>

<?php endif; ?>

<script>
const modeInputs = document.querySelectorAll(
    'input[name="patient_mode"]'
);

if (modeInputs.length > 0) {

    const existingSection = document.getElementById(
        "existingPatientSection"
    );

    const newSection = document.getElementById(
        "newPatientSection"
    );

    const existingInput = document.getElementById("patient_id");

    const phoneInput = document.getElementById("phone");

    const newInputs = [
        document.getElementById("name"),
        document.getElementById("age"),
        document.getElementById("gender"),
        phoneInput
    ];

    function updatePatientMode() {

        const selectedRadio = document.querySelector(
            'input[name="patient_mode"]:checked'
        );

        const selectedMode = selectedRadio
            ? selectedRadio.value
            : "existing";

        const isNew = selectedMode === "new";

        existingSection.style.display = isNew ? "none" : "block";
        newSection.style.display = isNew ? "block" : "none";

        existingInput.disabled = isNew;
        existingInput.required = !isNew;

        newInputs.forEach(input => {
            input.disabled = !isNew;
            input.required = isNew;
        });
    }

    modeInputs.forEach(input => {
        input.addEventListener("change", updatePatientMode);
    });

    // Allow only 10 numeric digits in the mobile field.
    if (phoneInput) {
        phoneInput.addEventListener("input", function () {
            this.value = this.value
                .replace(/[^0-9]/g, "")
                .slice(0, 10);
        });
    }

    updatePatientMode();
}
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
