
<?php
require_once __DIR__ . "/../includes/bootstrap.php";

$pageTitle = "ClinicFlow - Live Queue Display";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet"
          href="/ClinicFlow/assets/css/style.css">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, #173C50 0%, transparent 35%),
                #0B1423;
        }

        .display-wrapper {
            max-width: 1250px;
            margin: auto;
            padding: 35px 25px;
        }

        .display-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 35px;
        }

        .display-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .display-brand h2 {
            margin: 0;
            font-weight: 800;
        }

        .display-brand p,
        .display-date {
            color: #9BAEC2;
        }

        .display-card {
            background: #17263B;
            border: 1px solid #293B52;
            border-radius: 20px;
            padding: 30px;
            height: 100%;
        }

        .display-label {
            color: #9BAEC2;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .current-token {
            font-size: clamp(65px, 10vw, 115px);
            font-weight: 800;
            color: #55D6D9;
            text-align: center;
            margin: 20px 0;
        }

        .current-icon {
            font-size: 30px;
            color: #55D6D9;
        }

        .waiting-count {
            font-size: 42px;
            font-weight: 800;
            color: #F5C65E;
        }

        .waiting-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 25px;
        }

        .waiting-token {
            background: #101D30;
            border: 1px solid #344B65;
            border-radius: 13px;
            padding: 18px 10px;
            text-align: center;
            color: #F1F5F9;
            font-size: 25px;
            font-weight: 700;
        }

        .empty-queue {
            text-align: center;
            color: #9BAEC2;
            padding: 35px 10px;
        }

        .display-footer {
            text-align: center;
            color: #9BAEC2;
            margin-top: 35px;
            font-size: 14px;
        }

        .live-indicator {
            color: #4ADCA9;
            font-size: 13px;
            font-weight: 600;
        }

        .live-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            background: #22C58B;
            border-radius: 50%;
            margin-right: 6px;
        }

        @media(max-width: 650px) {
            .waiting-list {
                grid-template-columns: repeat(2, 1fr);
            }

            .display-wrapper {
                padding: 20px 12px;
            }

            .display-card {
                padding: 22px 16px;
            }
        }
    </style>
</head>

<body>

<div class="display-wrapper">

    <!-- HEADER -->
    <div class="display-header">

        <div class="display-brand">
            <div class="brand-icon">
                <i class="bi bi-hospital fs-3"></i>
            </div>

            <div>
                <h2>ClinicFlow</h2>
                <p>Smart Clinic Queue Display</p>
            </div>
        </div>

        <div class="text-end">
            <div class="display-date" id="displayDate">
                Loading date...
            </div>

            <div class="live-indicator mt-2">
                <span class="live-dot"></span>
                LIVE QUEUE
            </div>
        </div>

    </div>

    <!-- CURRENT TOKEN AND WAITING COUNT -->
    <div class="row g-4">

        <div class="col-lg-7">
            <div class="display-card text-center">

                <div class="current-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </div>

                <div class="display-label mt-3">
                    Now Serving
                </div>

                <div class="current-token" id="currentToken">
                    ---
                </div>

                <h5 class="text-muted" id="currentMessage">
                    Waiting for next patient
                </h5>

            </div>
        </div>

        <div class="col-lg-5">
            <div class="display-card text-center">

                <div class="display-label">
                    Patients Waiting
                </div>

                <div class="waiting-count mt-4" id="waitingCount">
                    0
                </div>

                <p class="text-muted mb-0">
                    Patients in today's queue
                </p>

                <hr style="border-color:#344B65">

                <div class="display-label">
                    Queue Status
                </div>

                <h4 class="mt-3" id="queueStatus">
                    Loading...
                </h4>

            </div>
        </div>

    </div>

    <!-- WAITING QUEUE -->
    <div class="display-card mt-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>
                <h4 class="mb-1">Waiting Queue</h4>

                <p class="text-muted mb-0">
                    Please wait until your token number is called.
                </p>
            </div>

            <span class="badge bg-warning text-dark">
                Waiting Patients
            </span>

        </div>

        <div class="waiting-list" id="waitingList">
            <div class="empty-queue">
                Loading queue...
            </div>
        </div>

    </div>

    <div class="display-footer">
        <p class="mb-1">
            <i class="bi bi-heart-pulse"></i>
            Thank you for choosing ClinicFlow.
        </p>

        <small id="lastUpdated">
            Connecting to clinic queue...
        </small>
    </div>

</div>

<script>

const dataUrl = "/ClinicFlow/queue/display_data.php";

function formatToken(number) {
    return "T" + String(number).padStart(3, "0");
}

async function updateDisplay() {

    try {

        const response = await fetch(
            dataUrl + "?time=" + Date.now(),
            { cache: "no-store" }
        );

        if (!response.ok) {
            throw new Error("Queue request failed.");
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error("Queue data unavailable.");
        }

        // Current token
        if (data.current !== null) {

            document.getElementById("currentToken").textContent =
                formatToken(data.current);

            document.getElementById("currentMessage").textContent =
                "Patient currently in consultation";

        } else {

            document.getElementById("currentToken").textContent = "---";

            document.getElementById("currentMessage").textContent =
                "Waiting for next patient";
        }

        // Waiting count
        document.getElementById("waitingCount").textContent =
            data.waiting_count;

        // Queue status
        document.getElementById("queueStatus").textContent =
            data.waiting_count > 0
                ? "Patients Waiting"
                : "Queue Clear";

        // Date
        document.getElementById("displayDate").textContent =
            data.date;

        // Waiting token list
        const waitingList =
            document.getElementById("waitingList");

        waitingList.replaceChildren();

        if (data.waiting.length === 0) {

            const empty = document.createElement("div");

            empty.className = "empty-queue";
            empty.style.gridColumn = "1 / -1";
            empty.textContent = "No patients are currently waiting.";

            waitingList.appendChild(empty);

        } else {

            data.waiting.forEach(function(token) {

                const item = document.createElement("div");

                item.className = "waiting-token";
                item.textContent = formatToken(token);

                waitingList.appendChild(item);

            });
        }

        // Last updated
        document.getElementById("lastUpdated").textContent =
            "Last updated: " + data.updated_at;

    } catch (error) {

        document.getElementById("lastUpdated").textContent =
            "Unable to connect. Retrying automatically...";

    }
}

// Initial loading
updateDisplay();

// Automatic refresh every 5 seconds
setInterval(updateDisplay, 5000);

</script>

</body>
</html>
