<?php
session_start();
require_once 'db_connect.php';

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

// Check database connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => "DB Connection failed: " . $conn->connect_error]);
    exit();
}

// Get and validate input data
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data)) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit();
}

// Validate required fields
$required = ['source', 'destination', 'transport_type', 'transport_details', 
            'hotel_name', 'hotel_booking_link', 'total_cost'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing or empty field: $field"]);
        exit();
    }
}

// Prepare statement with error handling
$sql = "INSERT INTO trips (user_id, source, destination, transport_type, transport_details, 
                          hotel_name, hotel_booking_link, total_cost, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

if (!$stmt = $conn->prepare($sql)) {
    echo json_encode(['status' => 'error', 'message' => "Prepare failed: " . $conn->error]);
    exit();
}

// Bind parameters
$user_id = $_SESSION['user_id'];
$source = $data['source'];
$destination = $data['destination'];
$transport_type = $data['transport_type'];
$transport_details = $data['transport_details'];
$hotel_name = $data['hotel_name'];
$hotel_booking_link = $data['hotel_booking_link'];
$total_cost = (float)$data['total_cost'];

if (!$stmt->bind_param("issssssd", $user_id, $source, $destination, $transport_type, 
                      $transport_details, $hotel_name, $hotel_booking_link, $total_cost)) {
    echo json_encode(['status' => 'error', 'message' => "Bind failed: " . $stmt->error]);
    exit();
}

// In save_trip.php, add this before saving
$requiredFields = ['source', 'destination', 'transport_type', 'hotel_name', 'total_cost'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Cannot save incomplete trip']);
        exit();
    }
}

// Execute and respond
if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success', 
        'message' => 'Trip saved successfully',
        'trip_id' => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to save trip',
        'mysql_error' => $stmt->error,
        'error_code' => $stmt->errno
    ]);
}

$stmt->close();
$conn->close();
?>