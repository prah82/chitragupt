<?php
require './includes/db.php';
require_once __DIR__ . '/includes/auth.php';


$username = 'admin';
$password = password_hash('admin09', PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    echo "Admin created successfully";
} else {
    echo "Error: " . $stmt->error;
}