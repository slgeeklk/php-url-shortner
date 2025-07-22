<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once './dbh.inc.php'; // DB connection
date_default_timezone_set('Asia/Colombo');

function generateShortCode($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $shortCode = '';
    for ($i = 0; $i < $length; $i++) {
        $shortCode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $shortCode;
}

// Validate input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['destinationLink'])) {
    $destinationLink = trim($_POST['destinationLink']);

    // Basic URL validation
    if (!filter_var($destinationLink, FILTER_VALIDATE_URL)) {
        echo "Invalid URL";
        exit;
    }

    // Generate unique short code
    $shortCode = generateShortCode();
    $checkSQL = "SELECT id FROM linkuserlinks WHERE shortCode = ?";
    $stmt = $conn->prepare($checkSQL);
    $stmt->bind_param("s", $shortCode);
    $stmt->execute();
    while ($stmt->get_result()->num_rows > 0) {
        $shortCode = generateShortCode(); // Regenerate if exists
        $stmt->bind_param("s", $shortCode);
        $stmt->execute();
    }
    $stmt->close();

    // Insert into DB
    $createdTime = date("Y-m-d H:i:s");
    $insertSQL = "INSERT INTO linkuserlinks (shortCode, destinationLink, createdTime) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertSQL);
    $stmt->bind_param("sss", $shortCode, $destinationLink, $createdTime);

    if ($stmt->execute()) {
        echo "https://link.slgeek.lk/" . $shortCode;
    } else {
        echo "Error: Failed to insert link.";
    }

    $stmt->close();
} else {
    echo "Invalid request";
}