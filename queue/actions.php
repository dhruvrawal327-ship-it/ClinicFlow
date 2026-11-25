
<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("doctor");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !verifyCsrfToken()) {
    header("Location: /ClinicFlow/dashboard.php?error=failed");
    exit;
}

$action = $_POST["action"] ?? "";
$today = date("Y-m-d");

try {

    if ($action === "call_next") {

        $pdo->beginTransaction();

        // Only one patient can be in consultation at a time.
        $currentStmt = $pdo->prepare(
            "SELECT id FROM queue
             WHERE queue_date = ?
             AND queue_status = 'In Consultation'
             LIMIT 1
             FOR UPDATE"
        );
        $currentStmt->execute([$today]);

        if ($currentStmt->fetch()) {
            $pdo->rollBack();
            header("Location: /ClinicFlow/dashboard.php?error=current");
            exit;
        }

        // Get the next patient in check-in order.
        $nextStmt = $pdo->prepare(
            "SELECT id FROM queue
             WHERE queue_date = ?
             AND queue_status = 'Waiting'
             ORDER BY check_in_at ASC, token_number ASC
             LIMIT 1
             FOR UPDATE"
        );
        $nextStmt->execute([$today]);

        $nextPatient = $nextStmt->fetch();

        if (!$nextPatient) {
            $pdo->rollBack();
            header("Location: /ClinicFlow/dashboard.php?error=empty");
            exit;
        }

        $updateStmt = $pdo->prepare(
            "UPDATE queue
             SET queue_status = 'In Consultation'
             WHERE id = ? AND queue_status = 'Waiting'"
        );
        $updateStmt->execute([(int)$nextPatient["id"]]);

        $pdo->commit();

        header("Location: /ClinicFlow/dashboard.php?called=1");
        exit;
    }

    if ($action === "complete") {

        $queueId = filter_input(INPUT_POST, "queue_id", FILTER_VALIDATE_INT);

        if (!$queueId || $queueId < 1) {
            throw new RuntimeException("Invalid queue record.");
        }

        $stmt = $pdo->prepare(
            "UPDATE queue
             SET queue_status = 'Completed'
             WHERE id = ?
             AND queue_date = ?
             AND queue_status = 'In Consultation'"
        );

        $stmt->execute([$queueId, $today]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException("Unable to complete this consultation.");
        }

        header("Location: /ClinicFlow/dashboard.php?completed=1");
        exit;
    }

    header("Location: /ClinicFlow/dashboard.php?error=failed");
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    header("Location: /ClinicFlow/dashboard.php?error=failed");
    exit;
}
