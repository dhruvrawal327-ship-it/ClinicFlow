
<?php
$pageTitle = "ClinicFlow - Smart Clinic Management";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            background: #0B1423;
            color: #F1F5F9;
            font-family: Arial, sans-serif;
        }

        .home-navbar {
            background: #101B2D;
            border-bottom: 1px solid #293B52;
            padding: 17px 0;
        }

        .home-logo {
            font-size: 25px;
            font-weight: 800;
            color: #55D6D9;
            text-decoration: none;
        }

        .home-logo i {
            margin-right: 8px;
        }

        .hero-section {
            padding: 95px 20px 85px;
            text-align: center;
            background:
                radial-gradient(circle at top, #173C50 0%, transparent 65%);
        }

        .hero-tag {
            display: inline-block;
            padding: 9px 17px;
            border-radius: 30px;
            background: #173448;
            color: #55D6D9;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .hero-section h1 {
            font-size: clamp(36px, 6vw, 62px);
            font-weight: 800;
            line-height: 1.2;
            max-width: 850px;
            margin: auto;
        }

        .hero-section h1 span {
            color: #55D6D9;
        }

        .hero-description {
            color: #9BAEC2;
            max-width: 650px;
            margin: 22px auto 30px;
            font-size: 17px;
            line-height: 1.8;
        }

        .btn-home {
            background: #087E8B;
            color: white;
            border: none;
            padding: 13px 25px;
            border-radius: 9px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-home:hover {
            background: #096D78;
            color: white;
        }

        .section-title {
            font-size: 32px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 12px;
        }

        .section-description {
            color: #9BAEC2;
            text-align: center;
            margin-bottom: 40px;
        }

        .section-space {
            padding: 65px 0;
        }

        .role-card,
        .feature-card {
            background: #17263B;
            border: 1px solid #293B52;
            border-radius: 16px;
            padding: 30px;
            height: 100%;
            transition: 0.2s;
        }

        .role-card:hover,
        .feature-card:hover {
            border-color: #087E8B;
            transform: translateY(-4px);
        }

        .role-icon,
        .feature-icon {
            width: 55px;
            height: 55px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #173C50;
            color: #55D6D9;
            border-radius: 13px;
            font-size: 25px;
            margin-bottom: 20px;
        }

        .role-card p,
        .feature-card p {
            color: #9BAEC2;
            line-height: 1.7;
        }

        .feature-card h5,
        .role-card h4 {
            font-weight: 700;
            margin-bottom: 12px;
        }

        .workflow-step {
            text-align: center;
            padding: 20px 10px;
        }

        .workflow-number {
            width: 45px;
            height: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #087E8B;
            font-weight: 800;
        }

        .workflow-step p {
            color: #9BAEC2;
            font-size: 14px;
        }

        .home-footer {
            background: #101B2D;
            border-top: 1px solid #293B52;
            padding: 25px 0;
            text-align: center;
            color: #9BAEC2;
        }

        @media(max-width: 576px) {
            .hero-section {
                padding: 65px 15px;
            }

            .section-space {
                padding: 45px 0;
            }

            .section-title {
                font-size: 27px;
            }
        }

        .home-footer .home-logo {
            justify-content: center;
            width: 100%;
            text-align: center;
        }

        .home-footer {
            text-align: center !important;
        }

        .home-footer .container {
            width: 100%;
            max-width: 100%;
            text-align: center !important;
        }

        .home-footer-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            gap: 10px;
            margin-bottom: 8px;
            color: #55D6D9;
            font-size: 25px;
            font-weight: 800;
        }

        .home-footer-brand .brand-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="home-navbar">
    <div class="container d-flex justify-content-between align-items-center">

        <a href="home.php" class="home-logo d-flex align-items-center">
           <span class="brand-icon me-2">
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
            </span>
             ClinicFlow
        </a>

        <a href="#login" class="btn-home">
            Login <i class="bi bi-arrow-right"></i>
        </a>

    </div>
</nav>


<!-- HERO SECTION -->
<section class="hero-section">

    <div class="container">

        <div class="hero-tag">
            <i class="bi bi-heart-pulse-fill"></i>
            Smart Clinic Management System
        </div>

        <h1>
            Smarter Clinic Management,
            <span>Better Patient Care.</span>
        </h1>

        <p class="hero-description">
            ClinicFlow simplifies daily clinic operations by
            organizing patient registration, appointments,
            queue management and doctor consultations
            in one centralized system.
        </p>

        <a href="#login" class="btn-home">
            Get Started <i class="bi bi-arrow-down"></i>
        </a>

    </div>

</section>


<!-- LOGIN SECTION -->
<section class="section-space" id="login">

    <div class="container">

        <h2 class="section-title">Access Your Dashboard</h2>

        <p class="section-description">
            Select your role to continue to the ClinicFlow system.
        </p>

        <div class="row g-4 justify-content-center">

            <div class="col-md-5">

                <div class="role-card">

                    <div class="role-icon">
                        <i class="bi bi-person-workspace"></i>
                    </div>

                    <h4>Receptionist</h4>

                    <p>
                        Manage patient registrations, book appointments,
                        register walk-in patients and manage the
                        daily clinic queue.
                    </p>

                <a href="login.php?role=receptionist" class="btn-home w-100 text-center">
                   Receptionist Login
                  <i class="bi bi-arrow-right"></i>
                </a>
                    

                </div>

            </div>

            <div class="col-md-5">

                <div class="role-card">

                    <div class="role-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>

                    <h4>Doctor</h4>

                    <p>
                        View the patient queue, call the next patient,
                        manage ongoing consultations and complete
                        patient consultations.
                    </p>

                <a href="login.php?role=doctor" class="btn-home w-100 text-center">
                   Doctor Login
                  <i class="bi bi-arrow-right"></i>
                </a>
                

                </div>

            </div>

        </div>

    </div>

</section>


<!-- FEATURES SECTION -->
<section class="section-space">

    <div class="container">

        <h2 class="section-title">Our Key Features</h2>

        <p class="section-description">
            Everything needed to organize everyday clinic operations.
        </p>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5>Patient Management</h5>
                    <p>
                        Maintain and manage patient information
                        in one organized place.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5>Appointment Management</h5>
                    <p>
                        Schedule and manage daily patient
                        appointments efficiently.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                    <h5>Queue Management</h5>
                    <p>
                        Generate daily queue tokens and
                        organize waiting patients.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-person-walking"></i>
                    </div>
                    <h5>Walk-in Registration</h5>
                    <p>
                        Register patients who visit the clinic
                        without a prior appointment.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>
                    <h5>Doctor Consultation</h5>
                    <p>
                        Allow doctors to manage their patient
                        queue and consultations.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-display"></i>
                    </div>
                    <h5>Live Queue Display</h5>
                    <p>
                        Display the currently serving token
                        and waiting patient numbers.
                    </p>
                </div>
            </div>

        </div>

    </div>

</section>


<!-- HOW IT WORKS -->
<section class="section-space">

    <div class="container">

        <h2 class="section-title">How ClinicFlow Works</h2>

        <p class="section-description">
            A simple workflow for managing daily clinic visits.
        </p>

        <div class="row g-3">

            <div class="col-6 col-lg-3">
                <div class="workflow-step">
                    <div class="workflow-number">1</div>
                    <h5>Registration</h5>
                    <p>Receptionist registers the patient.</p>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="workflow-step">
                    <div class="workflow-number">2</div>
                    <h5>Appointment / Walk-in</h5>
                    <p>Patient is added to the daily queue.</p>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="workflow-step">
                    <div class="workflow-number">3</div>
                    <h5>Token Generation</h5>
                    <p>A queue token is generated.</p>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="workflow-step">
                    <div class="workflow-number">4</div>
                    <h5>Consultation</h5>
                    <p>Doctor calls and consults the patient.</p>
                </div>
            </div>

        </div>

    </div>

</section>


<!-- FOOTER -->

<footer class="home-footer">

    <div class="container">

        <div class="home-footer-brand">
            <span class="brand-icon">
                <svg viewBox="0 0 48 48" width="25" height="25"
                     fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 6H29V19H42V29H29V42H19V29H6V19H19V6Z"
                          fill="white"/>
                    <path d="M7 24H15L19 17L25 31L30 22L34 24H41"
                          stroke="#087E8B"
                          stroke-width="2.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
            </span>

            <span>ClinicFlow</span>
        </div>

        <p class="mb-2">
            Smart Clinic Queue & Appointment Management System
        </p>

        <small>
            &copy; <?= date("Y") ?> ClinicFlow. All Rights Reserved.
        </small>

    </div>

</footer>
</body>
</html>
