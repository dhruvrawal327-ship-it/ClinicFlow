
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

$lockName = "clinicflow_daily_token_" . date("Ymd");
$lockAcquired = false;

try {
    // Lock token generation for today's queue.
    $lockStmt = $pdo->prepare("SELECT GET_LOCK(?, 10)");
    $lockStmt->execute([$lockName]);

    if ((int)$lockStmt->fetchColumn() !== 1) {
        throw new RuntimeException("Unable to acquire token lock.");
    }

    $lockAcquired = true;
    $pdo->beginTransaction();

    // Find and lock the appointment.
    $stmt = $pdo->prepare(
        "SELECT id, patient_id, appointment_date, status
         FROM appointments
         WHERE id = ?
         FOR UPDATE"
    );
    $stmt->execute([$id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        throw new DomainException("status");
    }

    if ($appointment["appointment_date"] !== date("Y-m-d")) {
        throw new DomainException("date");
    }

    // Check whether this appointment already has a queue entry.
    $existing = $pdo->prepare(
        "SELECT id
         FROM queue
         WHERE appointment_id = ?
         LIMIT 1"
    );
    $existing->execute([$id]);

    $existingQueue = $existing->fetch();

    // Reuse the original token instead of generating another.
    if ($existingQueue) {
        $queueId = (int)$existingQueue["id"];

        $pdo->commit();

        header("Location: /ClinicFlow/queue/token.php?id=" . $queueId);
        exit;
    }

    // Only booked appointments can receive a new token.
    if ($appointment["status"] !== "Booked") {
        throw new DomainException("status");
    }

    // Get the next token for today.
    $tokenStmt = $pdo->prepare(
        "SELECT COALESCE(MAX(token_number), 0) + 1
         FROM queue
         WHERE queue_date = ?"
    );
    $tokenStmt->execute([date("Y-m-d")]);
    $tokenNumber = (int)$tokenStmt->fetchColumn();

    // Add patient to the queue.
    $insert = $pdo->prepare(
        "INSERT INTO queue
         (patient_id, appointment_id, token_number,
          patient_type, queue_date, queue_status)
         VALUES (?, ?, ?, 'Appointment', ?, 'Waiting')"
    );

    $insert->execute([
        $appointment["patient_id"],
        $id,
        $tokenNumber,
        date("Y-m-d")
    ]);

    $queueId = (int)$pdo->lastInsertId();

    // Update appointment status.
    $update = $pdo->prepare(
        "UPDATE appointments
         SET status = 'Arrived'
         WHERE id = ?"
    );
    $update->execute([$id]);

    $pdo->commit();

    header("Location: /ClinicFlow/queue/token.php?id=" . $queueId);
    exit;

} catch (DomainException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: list.php?error=" . $e->getMessage());
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    header("Location: list.php?error=failed");
    exit;

} finally {
    if ($lockAcquired) {
        $release = $pdo->prepare("SELECT RELEASE_LOCK(?)");
        $release->execute([$lockName]);
    }
}
