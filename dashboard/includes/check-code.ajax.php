<?php
require_once '../../includes/dbh.inc.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['code'])) {
    $code = trim($_POST['code']);

    if (strlen($code) < 3 || strlen($code) > 20) {
        echo "invalid";
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM linkuserlinks WHERE shortCode = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->store_result();

    echo $stmt->num_rows > 0 ? "taken" : "available";
    $stmt->close();
}
?>