<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../includes/dbh.inc.php';
date_default_timezone_set("Asia/Colombo");

if (!isset($_SESSION['userId'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinationLink = trim($_POST['destinationLink']);
    $customCode     = trim($_POST['customCode']);
    $expiryInput    = trim($_POST['expiry'] ?? '');
    $passwordInput  = trim($_POST['password'] ?? '');
    $userId         = $_SESSION['userId'];

    // Validate URL
    if (!filter_var($destinationLink, FILTER_VALIDATE_URL)) {
        $_SESSION['link_status'] = "Invalid destination URL.";
        header("Location: ../add-link.php");
        exit();
    }

    // Prepare expiry
    if ($expiryInput === '') {
        $expiry = '0';
    } else {
        // expect datetime-local format "YYYY-MM-DDTHH:MM"
        $expiry = date("Y-m-d H:i:s", strtotime(str_replace('T', ' ', $expiryInput)));
    }

    // Prepare password
    if ($passwordInput === '') {
        $password = '0';
    } else {
        $password = password_hash($passwordInput, PASSWORD_DEFAULT);
    }

    // Determine shortCode
    if ($customCode !== '') {
        // check uniqueness
        $stmt = $conn->prepare("SELECT 1 FROM linkuserlinks WHERE shortCode = ?");
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
        // auto-generate
        do {
            $shortCode = substr(str_shuffle(
              "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
            ), 0, 8);
            $stmt = $conn->prepare("SELECT 1 FROM linkuserlinks WHERE shortCode = ?");
            $stmt->bind_param("s", $shortCode);
            $stmt->execute();
            $stmt->store_result();
        } while ($stmt->num_rows > 0);
    }

    // Insert into DB
    $stmt = $conn->prepare("
      INSERT INTO linkuserlinks 
        (shortCode, destinationLink, createdTime, webClicks, otherClicks, linkExpiry, linkPassword, userId)
      VALUES (?, ?, NOW(), 0, 0, ?, ?, ?)
    ");
    // 5 placeholders => 5 vars: 4 strings + 1 int
    $stmt->bind_param(
      "ssssi",
      $shortCode,
      $destinationLink,
      $expiry,
      $password,
      $userId
    );

    if ($stmt->execute()) {
    $_SESSION['shortCode'] = $shortCode;
    $fullUrl = "https://link.slgeek.lk/{$shortCode}";

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Redirecting…</title>
</head>
<body>
<script>
(function() {
  const text = "$fullUrl";

  function fallbackCopy(t) {
    const ta = document.createElement('textarea');
    ta.value = t;
    // avoid scrolling to bottom
    ta.style.position = 'fixed';
    ta.style.top = 0;
    ta.style.left = 0;
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
  }

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text)
      .catch(() => fallbackCopy(text))
      .finally(() => window.location.href = "../index.php");
  } else {
    fallbackCopy(text);
    window.location.href = "../index.php";
  }
})();
</script>
</body>
</html>
HTML;
    exit();
} else {
        $_SESSION['link_status'] = "Failed to create short link. Please try again.";
        header("Location: ../add-link.php");
        exit();
    }
}