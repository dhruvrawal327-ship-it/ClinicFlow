
<?php

require_once "includes/bootstrap.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
} else {
    header("Location: home.php");
    exit;
}
