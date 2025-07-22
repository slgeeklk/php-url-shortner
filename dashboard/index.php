<?php
session_start();
require_once '../includes/dbh.inc.php';

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

// Get paginated links
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sqlLinks = "SELECT * FROM linkuserlinks WHERE userId = ? ORDER BY createdTime DESC LIMIT ?, ?";
$stmtLinks = $conn->prepare($sqlLinks);
$stmtLinks->bind_param("iii", $userId, $offset, $limit);
$stmtLinks->execute();
$linksResult = $stmtLinks->get_result();

// Get total link count for pagination
$stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM linkuserlinks WHERE userId = ?");
$stmtCount->bind_param("i", $userId);
$stmtCount->execute();
$totalRows = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - SL Geek Links</title>
  <link rel="icon" href="../images/fav.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
      gap: 20px;
      justify-content: center;
      margin-top: 30px;
      flex-wrap: wrap;
    }
    .stat-box {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      flex: 1 1 200px;
      text-align: center;
    }
    .table-container {
      padding: 30px 15px;
    }
    .logout {
      position: absolute;
      right: 20px;
      top: 20px;
    }
    .clicks-btn {
      cursor: pointer;
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

<?php if (isset($_SESSION['link_status'])): ?>
  <div class="container mt-3">
    <div class="alert alert-success text-center">
      <?= $_SESSION['link_status']; unset($_SESSION['link_status']); ?>
    </div>
  </div>
<?php endif; ?>

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
          <th>Destination</th>
          <th>Clicks</th>
          <th>Expiry</th>
          <th>Password</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($linksResult->num_rows === 0): ?>
          <tr><td colspan="5" class="text-center">Nothing yet. Start by adding your first link!</td></tr>
        <?php endif; ?>

        <?php while ($row = $linksResult->fetch_assoc()): ?>
        <tr>
          <td class="text-truncate" style="max-width: 250px;">
            <?= htmlspecialchars($row['destinationLink']) ?>
          </td>
          <td>
            <button class="btn btn-outline-secondary btn-sm clicks-btn" 
              data-bs-toggle="modal" 
              data-bs-target="#clickModal"
              data-web="<?= $row['webClicks'] ?>" 
              data-other="<?= $row['otherClicks'] ?>">
              <?= $row['webClicks'] + $row['otherClicks'] ?> Clicks
            </button>
          </td>
          <td><?= $row['linkExpiry'] !== '0' ? htmlspecialchars($row['linkExpiry']) : 'Never' ?></td>
          <td><?= $row['linkPassword'] !== '0' ? 'Yes' : 'No' ?></td>
          <td>
            <button 
              class="btn btn-sm btn-primary edit-btn" 
              data-id="<?= $row['id'] ?>" 
              data-expiry="<?= $row['linkExpiry'] ?>" 
              data-password="<?= $row['linkPassword'] ?>"
              data-bs-toggle="modal" 
              data-bs-target="#editModal">
              Edit
            </button>
            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $row['id'] ?>">Delete</button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mt-3">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<!-- Clicks Modal -->
<div class="modal fade" id="clickModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Click Details</h5></div>
      <div class="modal-body">
        <p>Web Clicks: <span id="webClicks"></span></p>
        <p>Mobile Clicks: <span id="mobileClicks"></span></p>
        <p>Total Clicks: <span id="totalClicks"></span></p>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="includes/edit-link.inc.php">
        <div class="modal-header"><h5 class="modal-title">Edit Link</h5></div>
        <div class="modal-body">
          <input type="hidden" name="linkId" id="editLinkId">
          <div class="mb-3">
            <label>Expiry (yyyy-mm-dd or leave blank):</label>
            <input type="datetime-local" class="form-control" id="editExpiry" name="editExpiry">
          </div>
          <div class="mb-3">
            <label>Password (leave blank to remove):</label>
            <input type="text" name="linkPassword" class="form-control" id="editPassword">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).on('click', '.delete-btn', function () {
    const linkId = $(this).data('id');
    if (!confirm("Are you sure you want to delete this link?")) return;
    $.post('./includes/manage-links.inc.php', {
        action: 'delete',
        linkId: linkId
    }, function (res) {
        if (res.status === 'success') {
            location.reload();
        } else {
            alert("Error deleting the link.");
        }
    }, 'json');
});

// Handle clicks modal
$('#clickModal').on('show.bs.modal', function (event) {
  const btn = $(event.relatedTarget);
  const web = parseInt(btn.data('web')) || 0;
  const other = parseInt(btn.data('other')) || 0;
  $(this).find('#webClicks').text(web);
  $(this).find('#mobileClicks').text(other);
  $(this).find('#totalClicks').text(web + other);
});

// Handle edit modal
$(document).on('click', '.edit-btn', function () {
  $('#editLinkId').val($(this).data('id'));
  $('#editExpiry').val(data.linkExpiry !== "0" ? data.linkExpiry.replace(' ', 'T') : '');
$('#editPassword').val(data.linkPassword !== "0" ? data.linkPassword : '');
  
});
</script>
</body>
</html>