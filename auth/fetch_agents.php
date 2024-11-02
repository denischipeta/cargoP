<?php
// Include db connection
require '../db.php'; 

// SQL query to fetch agents
$sql = "SELECT id, agent_name, phone, email, business_reg_number, contact_address, referral_contact, password, booth_number, status FROM agents";
$result = $conn->query($sql);

$agents = [];

if ($result && $result->num_rows > 0) {
    // Fetch each row and add it to the agents array
    while($row = $result->fetch_assoc()) {
        $agents[] = [
            'id' => $row['id'],
            'name' => $row['agent_name'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'business_reg_number' => $row['business_reg_number'],
            'contact_address' => $row['contact_address'],
            'referral_contact' => $row['referral_contact'],
            'password' => $row['password'],
            'booth_number' => $row['booth_number'],
            'status' => $row['status']
        ];
    }
    
    // Return the agents data as a JSON response
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'agents' => $agents]);
} else {
    // Return message when no agents are found
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'agents' => [], 'message' => 'No agents found']);
}

// Close the connection
$conn->close();
?>
