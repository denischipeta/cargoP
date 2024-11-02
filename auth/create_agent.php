<?php
require __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auth";

header('Content-Type: application/json');
ob_start(); // Start output buffering to capture unwanted output

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    ob_clean();
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed."
    ]);
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rawData = file_get_contents('php://input');
    $input = json_decode($rawData, true);

    if ($input === null) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Invalid JSON data."]);
        exit;
    }

    $agentName = trim($input['agent_name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');
    $businessRegNumber = trim($input['business_reg_number'] ?? '');
    $contactAddress = trim($input['contact_address'] ?? '');
    $referralContact = trim($input['referral_contact'] ?? '');

    if (empty($agentName) || empty($phone) || empty($email) || empty($businessRegNumber) || empty($contactAddress) || empty($referralContact)) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
        exit;
    }

    $checkQuery = "SELECT * FROM agents WHERE phone = ? OR business_reg_number = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $phone, $businessRegNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        ob_clean();
        echo json_encode([
            "status" => "error",
            "message" => "Agent with this phone number or business registration number already exists."
        ]);
        $stmt->close();
        exit;
    }

    $stmt->close();

    $generatedPassword = bin2hex(random_bytes(4));
    $hashedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);

    // Here, include booth_number in your insert query and bind parameters
    $insertQuery = "INSERT INTO agents (agent_name, phone, email, business_reg_number, contact_address, referral_contact, password, booth_number) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($insertQuery)) {
        // Assuming you want to use businessRegNumber as booth number
        $stmt->bind_param("ssssssss", $agentName, $phone, $email, $businessRegNumber, $contactAddress, $referralContact, $hashedPassword, $businessRegNumber);

        if ($stmt->execute()) {
            sendSMS($phone, $generatedPassword, $businessRegNumber);

            ob_clean();
            echo json_encode([
                "status" => "success",
                "message" => "Agent added successfully. Password sent to phone.",
                "password" => $generatedPassword,
                "booth_number" => $businessRegNumber
            ]);
        } else {
            ob_clean();
            echo json_encode(["status" => "error", "message" => "Failed to add agent."]);
        }

        $stmt->close();
    } else {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Database query error."]);
    }
}

$conn->close();

function sendSMS($toPhone, $password, $boothNumber) {
    // Twilio credentials
    $twilioSid = 'AC540dc9d6aeb633781b6ab7624765a99f'; // Your Twilio Account SID
    $twilioToken = '59be5da3d4a50e72f9aa7a1d96d09788'; // Your Twilio Auth Token
    $twilioFrom = '+12058139731'; 
    if (!$twilioSid || !$twilioToken || !$twilioFrom) {
        error_log("Twilio credentials are missing.");
        return;
    }

    $client = new Client($twilioSid, $twilioToken);
    $message = "Hello, your account has been created. Booth Number: $boothNumber, Password: $password";

    try {
        $client->messages->create(
            $toPhone,
            [
                'from' => $twilioFrom,
                'body' => $message
            ]
        );
    } catch (Exception $e) {
        error_log("Failed to send SMS: " . $e->getMessage());
    }
}
?>
