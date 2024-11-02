<?php
// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging purposes (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require database connection file
require '../db.php'; // Ensure the path to db.php is correct

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

    // Check if userid already exists
    $checkUserQuery = "SELECT id FROM users WHERE userid = ?";
    if ($stmt = $conn->prepare($checkUserQuery)) {
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "UserID already exists."]);
            $stmt->close();
            $conn->close();
            exit;
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Database query error."]);
        $conn->close();
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user
    $insertUserQuery = "INSERT INTO users (userid, password) VALUES (?, ?)";
    if ($stmt = $conn->prepare($insertUserQuery)) {
        $stmt->bind_param("ss", $userid, $hashedPassword);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "User registered successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error occurred during registration."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Database query error."]);
    }

    $conn->close();
} else {
    // Handle incorrect request method
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
