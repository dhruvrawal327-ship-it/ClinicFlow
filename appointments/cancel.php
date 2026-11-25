
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("receptionist");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !verifyCsrfToken()) {
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
        "UPDATE appointments
         SET status = 'Cancelled'
         WHERE id = ? AND status = 'Booked'"
    );

    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        header("Location: list.php?error=status");
        exit;
    }

    header("Location: list.php?cancelled=1");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    header("Location: list.php?error=failed");
    exit;
}
