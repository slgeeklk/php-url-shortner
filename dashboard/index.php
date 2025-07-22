<?php
session_start();
require_once '../includes/dbh.inc.php';

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['userId'];
$userName = $_SESSION['userName'] ?? '';

// Get total clicks and link count
$sqlStats = "SELECT COUNT(*) as totalLinks, IFNULL(SUM(webClicks + otherClicks), 0) as totalClicks FROM linkuserlinks WHERE userId = ?";
$stmtStats = $conn->prepare($sqlStats);
$stmtStats->bind_param("i", $userId);
$stmtStats->execute();
$resultStats = $stmtStats->get_result()->fetch_assoc();

$totalLinks = $resultStats['totalLinks'];
$totalClicks = $resultStats['totalClicks'];

// Get latest links
$sqlLinks = "SELECT * FROM linkuserlinks WHERE userId = ? ORDER BY createdTime DESC LIMIT 10";
$stmtLinks = $conn->prepare($sqlLinks);
$stmtLinks->bind_param("i", $userId);
$stmtLinks->execute();
$linksResult = $stmtLinks->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - SL Geek Links</title>
  <link rel="icon" href="../images/fav.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f9f9f9;
    }
    .header {
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      color: #fff;
      padding: 20px;
    }
    .stats {
      display: flex;
      gap: 30px;
      justify-content: center;
      margin-top: 30px;
    }
    .stat-box {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      flex: 1;
      text-align: center;
    }
    .table-container {
      padding: 30px;
    }
    .logout {
      position: absolute;
      right: 20px;
      top: 20px;
    }
  </style>
</head>
<body>

<div class="header text-center position-relative">
  <img src="../images/logo.png" style="width: 140px;" class="my-2">
  <a href="../includes/logout.inc.php" class="btn btn-danger logout">Logout</a>
  <h2>Welcome, <?= htmlspecialchars($userName) ?>!</h2>
  <p>Your personal short link dashboard</p>
</div>

<div class="stats container">
  <div class="stat-box">
    <h4>Total Links</h4>
    <p class="fs-3 text-primary fw-bold"><?= $totalLinks ?></p>
  </div>
  <div class="stat-box">
    <h4>Total Clicks</h4>
    <p class="fs-3 text-success fw-bold"><?= $totalClicks ?></p>
  </div>
</div>

<div class="text-center my-4">
  <a href="add-link.php" class="btn btn-warning">+ Add New Link</a>
</div>

<div class="container table-container">
  <h5 class="mb-3">Latest Links</h5>
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Short Code</th>
          <th>Destination</th>
          <th>Clicks</th>
          <th>Expiry</th>
          <th>Password</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="linkTableBody">
        <?php while ($row = $linksResult->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['shortCode']) ?></td>
          <td class="text-truncate" style="max-width: 250px;"><?= htmlspecialchars($row['destinationLink']) ?></td>
          <td><?= $row['webClicks'] + $row['otherClicks'] ?></td>
          <td><?= $row['linkExpiry'] !== '0' ? htmlspecialchars($row['linkExpiry']) : 'Never' ?></td>
          <td><?= $row['linkPassword'] !== '0' ? 'Yes' : 'No' ?></td>
          <td>
            <a href="#" class="btn btn-sm btn-primary">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>