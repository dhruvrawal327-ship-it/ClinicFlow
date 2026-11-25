
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("receptionist");

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $stmt = $pdo->prepare(
        "SELECT * FROM patients
         WHERE deleted_at IS NULL
         AND (name LIKE ? OR phone LIKE ?)
         ORDER BY id DESC"
    );
    $term = "%" . $search . "%";
    $stmt->execute([$term, $term]);
} else {
    $stmt = $pdo->query(
        "SELECT * FROM patients
         WHERE deleted_at IS NULL
         ORDER BY id DESC"
    );
}

$patients = $stmt->fetchAll();

$pageTitle = "Patients";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="page-title">Patients</h2>
        <p class="page-subtitle">Manage registered clinic patients.</p>
    </div>

    <a href="add.php" class="btn btn-clinic">
        <i class="bi bi-person-plus"></i> Add Patient
    </a>
</div>

<?php if (isset($_GET["added"])): ?>
    <div class="alert alert-success">Patient registered successfully.</div>
<?php endif; ?>

<?php if (isset($_GET["updated"])): ?>
    <div class="alert alert-success">Patient information updated successfully.</div>
<?php endif; ?>

<?php if (isset($_GET["deleted"])): ?>
    <div class="alert alert-success">Patient deleted successfully.</div>
<?php endif; ?>

<?php if (isset($_GET["error"])): ?>
    <div class="alert alert-danger">
        <?= $_GET["error"] === "linked"
            ? "This patient cannot be deleted because appointment or queue records exist."
            : "Unable to delete this patient." ?>
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0">Patient Records</h5>
        <span class="text-muted small">
            <?= count($patients) ?> patient(s) found
        </span>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-9">
            <input type="search" name="search"
                   class="form-control"
                   placeholder="Search by patient name or phone..."
                   value="<?= e($search) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-clinic flex-grow-1">
                <i class="bi bi-search"></i> Search
            </button>
            <a href="list.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No patients found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?= (int)$patient["id"] ?></td>
                            <td><?= e($patient["name"]) ?></td>
                            <td><?= (int)$patient["age"] ?></td>
                            <td><?= e($patient["gender"]) ?></td>
                            <td><?= e($patient["phone"]) ?></td>
                            <td>
                                <?= e(date(
                                    "d M Y",
                                    strtotime($patient["created_at"])
                                )) ?>
                            </td>
                            <td>

                               <div class="d-flex gap-2">
                                   <a href="edit.php?id=<?= (int)$patient["id"] ?>"
                                      class="btn btn-action btn-edit"
                                      title="Edit patient">
                                      <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="delete.php"
                                          data-confirm="Are you sure you want to delete this patient? This action will move the patient to deleted records."
                                          data-confirm-title="Delete Patient?"
                                          data-confirm-action="Delete Patient"
                                          data-confirm-tone="danger">
                                          
                                      <input type="hidden" name="csrf_token"
                                             value="<?= e(csrfToken()) ?>">
                                      <input type="hidden" name="id"
                                             value="<?= (int)$patient["id"] ?>">

                                      <button type="submit"
                                              class="btn btn-action btn-delete"
                                              title="Delete patient">
                                              <i class="bi bi-trash"></i>
                                      </button>
                                    </form>
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
