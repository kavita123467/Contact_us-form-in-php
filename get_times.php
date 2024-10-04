<?php
// get_times.php

// Database connection
$mysqli = new mysqli("localhost", "newuser", "root123", "contact_form_db"); 

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get selected date and timezone
$date = $_GET['date'];
$timezone = 'Asia/Kolkata';  // Default to Indian Standard Time (IST)

// Query to check available time slots
$query = "SELECT time FROM contact_form WHERE date = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $date);
$stmt->execute();
$result = $stmt->get_result();

// Generate time slots (10 AM to 6 PM in half-hour intervals in IST)
$timeSlots = [];
$time_slots_in_half_hour = [];

// Generate time slots for 10 AM to 6 PM
for ($hour = 10; $hour < 18; $hour++) {
    foreach (['00', '30'] as $minute) {
        $time_slots_in_half_hour[] = sprintf("%02d:%s", $hour, $minute);
    }
}

// Check for booked slots
$booked_times = [];
while ($row = $result->fetch_assoc()) {
    $booked_times[] = $row['time'];
}

// Determine availability
foreach ($time_slots_in_half_hour as $time) {
    $available = !in_array($time, $booked_times);
    $timeSlots[] = ['time' => $time, 'available' => $available];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($timeSlots);

$mysqli->close();