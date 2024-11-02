<?php
header('Content-Type: application/json'); // Ensure JSON header is set

// Include database connection file
include '../db.php';

session_start();

// Check if the user is logged in
if (isset($_SESSION['userId'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    
    // Return a success message
    echo json_encode(array("status" => "success"));
} else {
    // Return an error message if no session found
    echo json_encode(array("status" => "error", "message" => "No active session found."));
}
?>
