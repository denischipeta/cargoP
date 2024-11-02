<?php
// Include db connection
require '../db.php';

header('Content-Type: application/json');

// Get the raw POST data
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Check if agent ID is provided
if (!isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Agent ID is required']);
    exit;
}

$agentId = $data['id'];

// Check if agent ID exists
$checkSql = "SELECT id FROM agents WHERE id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $agentId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Agent not found']);
    $checkStmt->close();
    $conn->close();
    exit;
}

$checkStmt->close();

// Prepare and execute the delete query
$sql = "DELETE FROM agents WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $agentId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Agent deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete agent']);
}

$stmt->close();
$conn->close();
?>
