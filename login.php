<?php

require_once "includes/bootstrap.php";

// Identify the selected login role
$allowedRoles = ['receptionist', 'doctor'];
$selectedRole = $_POST['role'] ?? $_GET['role'] ?? '';

if (!in_array($selectedRole, $allowedRoles, true)) {
    $selectedRole = '';
}

// If already logged in, go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verifyCsrfToken()) {
        $error = "Invalid request. Please refresh the page.";
    } else {

        $email = trim($_POST['email'] ?? "");
        $password = $_POST['password'] ?? "";

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
            $error = "Please enter a valid email and password.";
        } elseif ($selectedRole === '') {
            $error = "Invalid login role. Please return to the homepage and select your login option.";
        } else {

            $stmt = $pdo->prepare(
                "SELECT * FROM users WHERE email = ? LIMIT 1"
            );

            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {

                // Check whether the account matches the selected login role
                if ($user['role'] !== $selectedRole) {

                    $error = "These credentials are not registered for "
                           . ucfirst($selectedRole)
                           . " login. Please select the correct login option.";

                } else {

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    header("Location: dashboard.php");
                    exit;
                }

            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}

// Display the selected role
$roleTitle = $selectedRole
    ? ucfirst($selectedRole) . " Login"
    : "Staff Login";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($roleTitle) ?> | ClinicFlow</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="login-body">

<div class="login-container">

    <div class="login-card">

        <div class="text-end mb-3">
            <a href="home.php" class="btn btn-outline-clinic btn-sm">
                <i class="bi bi-house-door"></i> Home
            </a>
        </div>

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

        <h2>ClinicFlow</h2>

        <p class="text-muted mb-4">
            Smart Clinic Management
        </p>

        <h5 class="text-start mb-1">
            <?= e($roleTitle) ?>
        </h5>

        <p class="text-muted small text-start mb-4">
            Sign in to access your <?= e($selectedRole ?: 'staff') ?> dashboard.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <input type="hidden"
                   name="role"
                   value="<?= e($selectedRole) ?>">

            <div class="mb-3 text-start">
                <label class="form-label">Email address</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       placeholder="Enter your email"
                       value="<?= e($_POST['email'] ?? '') ?>"
                       required>
            </div>

            <div class="mb-4 text-start">
                <label class="form-label">Password</label>

                <div class="position-relative">
                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control pe-5"
                           placeholder="Enter your password"
                           required>

                    <button type="button"
                            id="togglePassword"
                            class="btn position-absolute top-50 end-0 translate-middle-y border-0"
                            style="color: black;"
                            aria-label="Show password">
                        <i class="bi bi-eye" id="passwordIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-clinic w-100">
                Sign In
            </button>

        </form>

        <p class="small text-muted mt-4 mb-0">
            ClinicFlow · Smart Clinic Management
        </p>

    </div>

</div>

<script>
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");
const passwordIcon = document.getElementById("passwordIcon");

togglePassword.addEventListener("click", function () {

    const isPasswordHidden = password.type === "password";

    password.type = isPasswordHidden ? "text" : "password";

    passwordIcon.classList.toggle("bi-eye", !isPasswordHidden);
    passwordIcon.classList.toggle("bi-eye-slash", isPasswordHidden);

    togglePassword.setAttribute(
        "aria-label",
        isPasswordHidden ? "Hide password" : "Show password"
    );
});
</script>

</body>
</html>

</body>
</html>