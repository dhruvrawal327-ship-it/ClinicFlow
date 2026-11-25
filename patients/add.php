
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("receptionist");

$errors = [];
$name = "";
$age = "";
$gender = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verifyCsrfToken()) {
        $errors[] = "Invalid request. Please refresh the page.";
    } else {

        $name = trim($_POST["name"] ?? "");
        $age = trim($_POST["age"] ?? "");
        $gender = $_POST["gender"] ?? "";
        $phone = trim($_POST["phone"] ?? "");

        // Name validation
        if ($name === "" || mb_strlen($name) > 100) {
            $errors[] = "Enter a patient name (maximum 100 characters).";
        }

        // Age validation
        if (
            filter_var($age, FILTER_VALIDATE_INT) === false
            || (int)$age < 0
            || (int)$age > 120
        ) {
            $errors[] = "Enter a valid age between 0 and 120.";
        }

        // Gender validation
        if (!in_array($gender, ["Male", "Female", "Other"], true)) {
            $errors[] = "Please select a valid gender.";
        }

        // Mobile number validation
        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            $errors[] = "Enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.";
        }

        /*
         * DUPLICATE MOBILE NUMBER CHECK
         *
         * Check active patients before inserting.
         * Deleted patients are excluded.
         */
        if (empty($errors)) {

            $duplicateStmt = $pdo->prepare(
                "SELECT id, name
                 FROM patients
                 WHERE phone = ?
                 AND deleted_at IS NULL
                 LIMIT 1"
            );

            $duplicateStmt->execute([$phone]);

            $existingPatient = $duplicateStmt->fetch();

            if ($existingPatient) {
                $errors[] =
                    "This mobile number is already registered to " .
                    $existingPatient["name"] .
                    " (Patient ID: " . $existingPatient["id"] .
                    "). Please use the existing patient record.";
            }
        }

        // Insert only if all validations pass
        if (empty($errors)) {

            try {

                $stmt = $pdo->prepare(
                    "INSERT INTO patients (name, age, gender, phone)
                     VALUES (?, ?, ?, ?)"
                );

                $stmt->execute([
                    $name,
                    (int)$age,
                    $gender,
                    $phone
                ]);

                header("Location: list.php?added=1");
                exit;

            } catch (PDOException $e) {

                error_log($e->getMessage());

                $errors[] = "Unable to register the patient. Please try again.";
            }
        }
    }
}

$pageTitle = "Register Patient";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="page-title">Register Patient</h2>
        <p class="page-subtitle">Add a new patient to the clinic records.</p>
    </div>

    <a href="list.php" class="btn btn-outline-clinic">
        <i class="bi bi-arrow-left"></i> Back to Patients
    </a>
</div>

<div class="content-card">

    <h5 class="mb-4">Patient Information</h5>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="csrf_token"
               value="<?= e(csrfToken()) ?>">

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Patient Name</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       maxlength="100"
                       placeholder="Enter full name"
                       value="<?= e($name) ?>"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Age</label>

                <input type="number"
                       name="age"
                       class="form-control"
                       min="0"
                       max="120"
                       placeholder="Enter age"
                       value="<?= e($age) ?>"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Gender</label>

                <select name="gender" class="form-select" required>

                    <option value="">Select gender</option>

                    <?php foreach (["Male", "Female", "Other"] as $option): ?>

                        <option value="<?= e($option) ?>"
                            <?= $gender === $option ? "selected" : "" ?>>

                            <?= e($option) ?>

                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <div class="col-md-6">

                <label class="form-label">Mobile Number</label>

                <div class="input-group">

                    <span class="input-group-text">+91</span>

                    <input type="tel"
                           name="phone"
                           id="phone"
                           class="form-control"
                           minlength="10"
                           maxlength="10"
                           pattern="[6-9][0-9]{9}"
                           inputmode="numeric"
                           placeholder="Enter 10-digit mobile number"
                           value="<?= e($phone) ?>"
                           required>

                </div>

                <small class="text-muted">
                    Enter a 10-digit Indian mobile number.
                </small>

                <div class="invalid-feedback">
                    Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.
                </div>

            </div>

        </div>

        <div class="d-flex gap-2 mt-4">

            <button type="submit" class="btn btn-clinic">
                <i class="bi bi-person-plus"></i> Register Patient
            </button>

            <a href="list.php" class="btn btn-secondary">
                Cancel
            </a>

        </div>

    </form>

</div>

<script>
const phoneInput = document.getElementById("phone");

phoneInput.addEventListener("input", function () {

    this.value = this.value
        .replace(/[^0-9]/g, "")
        .slice(0, 10);

    if (/^[6-9][0-9]{9}$/.test(this.value)) {
        this.classList.remove("is-invalid");
        this.classList.add("is-valid");
    } else {
        this.classList.remove("is-valid");
        this.classList.add("is-invalid");
    }
});

phoneInput.addEventListener("blur", function () {

    if (
        this.value !== ""
        && !/^[6-9][0-9]{9}$/.test(this.value)
    ) {
        this.classList.add("is-invalid");
    }
});
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
