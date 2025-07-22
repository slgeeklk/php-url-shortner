<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Short Link - SL Geek Links</title>
  <link rel="icon" href="../images/fav.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      color: #fff;
      min-height: 100vh;
    }
    .container {
      max-width: 600px;
      margin-top: 80px;
      background-color: rgba(255,255,255,0.05);
      padding: 30px;
      border-radius: 10px;
    }
    h2 {
      margin-bottom: 20px;
    }
    .form-label {
      font-weight: 500;
    }
    .btn {
      width: 100%;
    }
    a {
      color: #ffc107;
    }
  </style>
</head>
<body>

<div class="container text-white">
  <h2 class="text-center">Create a New Short Link</h2>
  <?php if (isset($_SESSION['link_status'])): ?>
    <div class="alert alert-info"><?= $_SESSION['link_status']; unset($_SESSION['link_status']); ?></div>
  <?php endif; ?>
  <form action="./includes/add-link.inc.php" method="post">
    <div class="mb-3">
      <label for="destinationLink" class="form-label">Destination URL</label>
      <input type="url" class="form-control" id="destinationLink" name="destinationLink" placeholder="https://example.com" required>
    </div>

    <div class="mb-3">
      <label for="expiry" class="form-label">Expiry Date & Time (Optional)</label>
      <input type="datetime-local" class="form-control" id="expiry" name="expiry">
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password (Optional)</label>
      <input type="text" class="form-control" id="password" name="password" placeholder="Leave blank for no password">
    </div>

    <button type="submit" class="btn btn-warning">Shorten</button>
    <div class="mt-3 text-center">
      <a href="index.php">← Back to Dashboard</a>
    </div>
  </form>
</div>

</body>
</html>