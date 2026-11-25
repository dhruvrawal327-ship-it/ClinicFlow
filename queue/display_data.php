
<?php

require_once __DIR__ . "/../config/database.php";

date_default_timezone_set("Asia/Kolkata");

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

try {

    $today = date("Y-m-d");

    // Current consultation
    $currentStmt = $pdo->prepare(
        "SELECT token_number
         FROM queue
         WHERE queue_date = ?
         AND queue_status = 'In Consultation'
         ORDER BY check_in_at ASC, token_number ASC
         LIMIT 1"
    );

    $currentStmt->execute([$today]);
    $current = $currentStmt->fetch();

    // Waiting patients
    $waitingStmt = $pdo->prepare(
        "SELECT token_number
         FROM queue
         WHERE queue_date = ?
         AND queue_status = 'Waiting'
         ORDER BY check_in_at ASC, token_number ASC"
    );

    $waitingStmt->execute([$today]);

    $waiting = [];

    foreach ($waitingStmt->fetchAll() as $patient) {
        $waiting[] = (int)$patient["token_number"];
    }

    echo json_encode([
        "success" => true,
        "date" => date("d M Y"),
        "current" => $current
            ? (int)$current["token_number"]
            : null,
        "waiting" => $waiting,
        "waiting_count" => count($waiting),
        "updated_at" => date("h:i:s A")
    ]);

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load clinic queue."
    ]);
}
