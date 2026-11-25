<?php
$currentPath = $_SERVER['PHP_SELF'];

$isPatientsPage = strpos($currentPath, '/patients/') !== false;
$isAppointmentsPage = strpos($currentPath, '/appointments/') !== false;

$isDashboardPage = (
    basename($currentPath) === 'dashboard.php'
    && strpos($currentPath, '/doctor/') === false
);
?>

<aside class="sidebar">

    <div class="sidebar-brand">
        <div class="brand-icon">
            <svg viewBox="0 0 48 48" width="30" height="30"
                 fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 6H29V19H42V29H29V42H19V29H6V19H19V6Z"
                      fill="white"/>
                <path d="M7 24H15L19 17L25 31L30 22L34 24H41"
                      stroke="#087E8B"
                      stroke-width="2.5"
                      stroke-linecap="round"
                      stroke-linejoin="round"/>
            </svg>
        </div>

        <div>
            <h5>ClinicFlow</h5>
            <small>Smart Clinic Management</small>
        </div>
    </div>

    <div class="sidebar-section">WORKSPACE</div>

    <a href="/ClinicFlow/dashboard.php"
       class="sidebar-link <?= $isDashboardPage ? 'active' : '' ?>">
        <i class="bi bi-grid-1x2"></i>
        <span>Dashboard</span>
    </a>

    <div class="sidebar-section mt-4">MANAGEMENT</div>

    <?php if ($_SESSION['user_role'] === 'receptionist'): ?>

        <a href="/ClinicFlow/patients/list.php"
           class="sidebar-link <?= $isPatientsPage ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <span>Patients</span>
        </a>

        <a href="/ClinicFlow/appointments/list.php"
           class="sidebar-link <?= $isAppointmentsPage ? 'active' : '' ?>">
            <i class="bi bi-calendar-check"></i>
            <span>Appointments</span>
        </a>

        <a href="/ClinicFlow/walkins/add.php"
           class="sidebar-link <?= strpos($currentPath, '/walkins/') !== false ? 'active' : '' ?>">
            <i class="bi bi-person-walking"></i>
            <span>Walk-in Registration</span>
        </a>

    <?php endif; ?>

    <a href="/ClinicFlow/queue/index.php"
       class="sidebar-link <?= strpos($currentPath, '/queue/') !== false ? 'active' : '' ?>">

       <i class="bi bi-list-ol"></i>
       <span>Patient Queue</span>

    </a>

    <div class="sidebar-bottom">

        <div class="user-profile">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
            </div>

            <div class="user-info">
                <strong><?= e($_SESSION['user_name']) ?></strong>
                <small><?= ucfirst(e($_SESSION['user_role'])) ?></small>
            </div>
        </div>

        <a href="/ClinicFlow/logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right"></i>
            Sign Out
        </a>

    </div>

</aside>
