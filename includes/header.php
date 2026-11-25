
<?php
$pageTitle = "ClinicFlow Dashboard";
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

    <link href="/ClinicFlow/assets/css/style.css" rel="stylesheet">
    <link href="/ClinicFlow/assets/css/clinicflow-popups.css?v=1" rel="stylesheet">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/sidebar.php"; ?>

    <main class="main-content">
