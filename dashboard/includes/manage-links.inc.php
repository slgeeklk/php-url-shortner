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

// Optional: fallback if no valid action
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
exit();