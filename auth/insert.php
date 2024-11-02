<?php
require '../db.php';

$userid = 'admin123';
$password = password_hash('00000000', PASSWORD_DEFAULT); // Hash password

$insertUserQuery = "INSERT INTO users (userid, password) VALUES (?, ?)";
if ($stmt = $conn->prepare($insertUserQuery)) {
    $stmt->bind_param("ss", $userid, $password);
    $stmt->execute();
    echo "User inserted successfully.";
    $stmt->close();
} else {
    echo "Error inserting user.";
}

$conn->close();
?>
