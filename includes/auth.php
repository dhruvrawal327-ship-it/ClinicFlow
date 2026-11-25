
<?php
require_once __DIR__ . "/bootstrap.php";

function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header("Location: /ClinicFlow/login.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();

    if ($_SESSION['user_role'] !== $role) {
        header("Location: /ClinicFlow/dashboard.php");
        exit;
    }
}
