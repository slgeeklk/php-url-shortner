<?php
session_start();
require_once '../../includes/dbh.inc.php';
date_default_timezone_set("Asia/Colombo");

if (!isset($_SESSION['userId'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinationLink = trim($_POST['destinationLink']);
    $customCode = trim($_POST['customCode']);
    $expiry = !empty($_POST['expiry']) ? date("Y-m-d H:i:s", strtotime($_POST['expiry'])) : '0';
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '0';
    $userId = $_SESSION['userId'];

    // Validate URL
    if (!filter_var($destinationLink, FILTER_VALIDATE_URL)) {
        $_SESSION['link_status'] = "Invalid destination URL.";
        header("Location: ../add-link.php");
        exit();
    }

    // Validate or generate code
    if (!empty($customCode)) {
        // Check if it's already taken
        $stmt = $conn->prepare("SELECT id FROM linkuserlinks WHERE shortCode = ?");
        $stmt->bind_param("s", $customCode);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['link_status'] = "The custom short code is already taken.";
            header("Location: ../add-link.php");
            exit();
        }
        $shortCode = $customCode;
    } else {
        // Auto generate 8-character code
        do {
            $shortCode = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);
            $stmt = $conn->prepare("SELECT id FROM linkuserlinks WHERE shortCode = ?");
            $stmt->bind_param("s", $shortCode);
            $stmt->execute();
            $stmt->store_result();
        } while ($stmt->num_rows > 0);
    }

    // Insert to DB
    $stmt = $conn->prepare("INSERT INTO linkuserlinks (shortCode, destinationLink, createdTime, webClicks, otherClicks, linkExpiry, linkPassword, userId)
                            VALUES (?, ?, NOW(), 0, 0, ?, ?, ?)");
    $stmt->bind_param("ssssi", $shortCode, $destinationLink, $expiry, $password, $userId);

    if ($stmt->execute()) {
        $_SESSION['link_status'] = "Short link created successfully! Your short code is: <strong>$shortCode</strong>";
    } else {
        $_SESSION['link_status'] = "Failed to create short link. Please try again.";
    }

    header("Location: ../add-link.php");
    exit();
}
?>