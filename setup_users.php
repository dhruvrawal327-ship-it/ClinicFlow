
<?php

require_once "includes/bootstrap.php";

$users = [
    [
        "name" => "Clinic Receptionist",
        "email" => "reception@clinicflow.com",
        "password" => "Reception@123",
        "role" => "receptionist"
    ],
    [
        "name" => "Clinic Doctor",
        "email" => "doctor@clinicflow.com",
        "password" => "Doctor@123",
        "role" => "doctor"
    ]
];

foreach ($users as $user) {

    $check = $pdo->prepare(
        "SELECT id FROM users WHERE email = ?"
    );

    $check->execute([$user['email']]);

    if (!$check->fetch()) {

        $hashedPassword = password_hash(
            $user['password'],
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $user['name'],
            $user['email'],
            $hashedPassword,
            $user['role']
        ]);
    }
}

echo "Test accounts created successfully. Delete setup_users.php now.";
