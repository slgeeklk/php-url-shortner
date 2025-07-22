<?php
require_once './includes/dbh.inc.php';
session_start();

$key = $_GET['session'] ?? '';
$valid = false;
$message = "";
$alert = "danger";

// Basic key format check
if (!preg_match("/^[a-f0-9]{32}$/", $key)) {
    $message = "Invalid session key.";
} else {
    $stmt = $conn->prepare("SELECT * FROM linkuserval WHERE sessionKey = ? AND sessionStatus = 0");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows < 1) {
        $message = "This verification link is invalid or already used.";
    } else {
        $row = $res->fetch_assoc();
        $email = $row['userEmail'];
        $created = strtotime($row['createdTime']);
        $now = time();

        if ($now - $created > 12 * 60 * 60) { // 12 hours expiry
            $message = "This verification link has expired. Please try logging in again to receive a new one.";
        } else {
            // Set userStatus to 1
            $updateUser = $conn->prepare("UPDATE linkusers SET userStatus = 1 WHERE userEmail = ?");
            $updateUser->bind_param("s", $email);
            $updateUser->execute();

            // Mark this session as used
            $updateSession = $conn->prepare("UPDATE linkuserval SET sessionStatus = 1 WHERE sessionKey = ?");
            $updateSession->bind_param("s", $key);
            $updateSession->execute();

            $valid = true;
            $message = "Your account has been successfully verified! You can now log in.";
            $alert = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Verification - SL Geek</title>
  <link rel="icon" href="images/fav.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      color: white;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      text-align: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      background: #2c2f4a;
      padding: 30px;
      border-radius: 12px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }
    .logo {
      width: 150px;
      margin-bottom: 20px;
    }
    .btn-custom {
      background-color: #ffc107;
      border: none;
    }
  </style>
</head>
<body>

  <img src="images/logo.png" class="logo" alt="SL Geek Logo">

  <div class="card">
    <h3>Email Verification</h3>
    <div class="alert alert-<?= $alert ?> mt-3">
      <?= $message ?>
    </div>

    <?php if ($valid): ?>
      <a href="login.php" class="btn btn-custom mt-3">Go to Login</a>
    <?php else: ?>
      <a href="index.php" class="btn btn-light mt-3">Go Back</a>
    <?php endif; ?>
  </div>

</body>
</html>