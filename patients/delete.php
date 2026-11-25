
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("receptionist");

if ($_SERVER["REQUEST_METHOD"] !== "POST"
    || !verifyCsrfToken()) {
    header("Location: list.php?error=failed");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: list.php?error=failed");
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE patients
         SET deleted_at = NOW()
         WHERE id = ? AND deleted_at IS NULL"
    );

    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        header("Location: list.php?error=failed");
        exit;
    }

    header("Location: list.php?deleted=1");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    header("Location: list.php?error=failed");
    exit;
}
