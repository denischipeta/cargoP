<?php
// Set content type to JSON
header('Content-Type: application/json');

// Disable error reporting in production
// error_reporting(0);

// Require database connection file
require '../db.php';

// Start session securely
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data."]);
        exit;
    }

    $userid = isset($input['userId']) ? trim($input['userId']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';

    // Validate input
    if (empty($userid) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Please fill all fields."]);
        exit;
    }

    // Check if the user exists and verify password
    $getUserQuery = "SELECT id, password FROM users WHERE userid = ?";
    if ($stmt = $conn->prepare($getUserQuery)) {
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();

        if ($id !== null && password_verify($password, $hashedPassword)) {
            // Set session variables
            $_SESSION['userId'] = $id;
            echo json_encode(["status" => "success", "message" => "Login successful."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Database query error."]);
    }

    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
