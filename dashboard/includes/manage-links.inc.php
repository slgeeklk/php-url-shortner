<?php
session_start();
header('Content-Type: application/json');
require_once '../../includes/dbh.inc.php';

if (!isset($_SESSION['userId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['userId'];
$action = $_POST['action'] ?? '';

// DELETE
if ($action === 'delete' && isset($_POST['linkId'])) {
    $linkId = intval($_POST['linkId']);
    $stmt = $conn->prepare("DELETE FROM linkuserlinks WHERE id = ? AND userId = ?");
    $stmt->bind_param("ii", $linkId, $userId);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Link deleted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Deletion failed']);
    }
    exit();
}

// UPDATE
if ($action === 'update' && isset($_POST['linkId'])) {
    $linkId = intval($_POST['linkId']);

    // Normalize expiry
    $expiry = trim($_POST['expiry'] ?? '');
    if ($expiry === '') {
        $expiry = '0';
    } else {
        // datetime-local comes as "YYYY-MM-DDTHH:MM", convert to MySQL DATETIME
        $expiry = str_replace('T', ' ', $expiry);
    }

    // Normalize password
    $password = trim($_POST['password'] ?? '');
    if ($password === '') {
        $password = '0';
    }

    $stmt = $conn->prepare("
      UPDATE linkuserlinks
      SET linkExpiry   = ?,
          linkPassword = ?
      WHERE id = ? AND userId = ?
    ");
    $stmt->bind_param("ssii", $expiry, $password, $linkId, $userId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Link updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }
    exit();
}

// Fallback for invalid/missing action
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
exit();