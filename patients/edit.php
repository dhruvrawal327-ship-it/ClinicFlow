
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("receptionist");

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: list.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    header("Location: list.php");
    exit;
}

$errors = [];

$name = $patient["name"];
$age = $patient["age"];
$gender = $patient["gender"];
$phone = $patient["phone"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCsrfToken()) {
        $errors[] = "Invalid request. Please refresh the page.";
    } else {
        $name = trim($_POST["name"] ?? "");
        $age = trim($_POST["age"] ?? "");
        $gender = $_POST["gender"] ?? "";
        $phone = trim($_POST["phone"] ?? "");

        if ($name === "" || mb_strlen($name) > 100) {
            $errors[] = "Enter a patient name (maximum 100 characters).";
        }

        if (filter_var($age, FILTER_VALIDATE_INT) === false
            || (int)$age < 0 || (int)$age > 120) {
            $errors[] = "Enter a valid age between 0 and 120.";
        }

        if (!in_array($gender, ["Male", "Female", "Other"], true)) {
            $errors[] = "Please select a valid gender.";
        }

        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            $errors[] = "Phone number must contain 10 to 15 digits.";
        }

        if (empty($errors)) {
            $update = $pdo->prepare(
                "UPDATE patients
                 SET name = ?, age = ?, gender = ?, phone = ?
                 WHERE id = ?"
            );

            $update->execute([
                $name,
                (int)$age,
                $gender,
                $phone,
                $id
            ]);

            header("Location: list.php?updated=1");
            exit;
        }
    }
}

$pageTitle = "Edit Patient";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="page-title">Edit Patient</h2>
        <p class="page-subtitle">Update patient information.</p>
    </div>
    <a href="list.php" class="btn btn-outline-clinic">
        <i class="bi bi-arrow-left"></i> Back to Patients
    </a>
</div>

<div class="content-card">
    <h5 class="mb-4">Patient #<?= (int)$id ?></h5>

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
                <input type="text" name="name"
                       class="form-control" maxlength="100"
                       value="<?= e($name) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Age</label>
                <input type="number" name="age"
                       class="form-control" min="0" max="120"
                       value="<?= e($age) ?>" required>
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
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone"
                       class="form-control" maxlength="15"
                       pattern="[0-9]{10,15}"
                       value="<?= e($phone) ?>" required>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-clinic">
                <i class="bi bi-check-circle"></i> Save Changes
            </button>
            <a href="list.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
