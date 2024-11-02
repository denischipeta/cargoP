<?php

// Include the database connection file
require '../../Authentica/db.php';

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data for Line Graph
$lineGraphQuery = "SELECT month, revenue FROM monthly_revenue ORDER BY month";
$lineGraphData = [];
if ($result = $conn->query($lineGraphQuery)) {
    while ($row = $result->fetch_assoc()) {
        $lineGraphData[] = [
            "month" => $row["month"],
            "revenue" => $row["revenue"]
        ];
    }
    $result->free();
} else {
    // Handle query error
    $lineGraphData = [
        "error" => "Error fetching line graph data: " . $conn->error
    ];
}

// Fetch data for Pie Chart
$pieChartQuery = "SELECT booth_name, revenue FROM booth_revenue";
$pieChartData = [];
if ($result = $conn->query($pieChartQuery)) {
    while ($row = $result->fetch_assoc()) {
        $pieChartData[] = [
            "booth_name" => $row["booth_name"],
            "revenue" => $row["revenue"]
        ];
    }
    $result->free();
} else {
    // Handle query error
    $pieChartData = [
        "error" => "Error fetching pie chart data: " . $conn->error
    ];
}

// Combine the data
$response = [
    "lineGraphData" => $lineGraphData,
    "pieChartData" => $pieChartData
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$conn->close();
?>
