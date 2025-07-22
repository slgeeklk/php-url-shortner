<?php
session_start();

// Redirect if a short code is accessed
$requestURI = $_SERVER['REQUEST_URI'];
$trimmed = trim($requestURI, "/");

if (!empty($trimmed) && !preg_match("/\.php$/", $trimmed)) {
    header("Location: redirect.php?code=" . $trimmed);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SL Geek Links - Shorten & Track</title>
  <link rel="icon" href="images/fav.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      color: #fff;
      text-align: center;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .logo img {
      width: 140px;
    }

    .hero {
      padding: 80px 20px 40px;
    }

    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .hero p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }

    .btn-custom {
      padding: 10px 30px;
      font-size: 1rem;
      margin: 0 10px;
    }

    .shortener {
      margin: 30px auto;
      max-width: 600px;
    }

    .features {
      margin-top: 60px;
    }

    .features .col-md-3 {
      margin-bottom: 30px;
    }

    .features i {
      font-size: 2.5rem;
      margin-bottom: 15px;
      display: block;
    }

    footer {
      margin-top: auto;
      padding: 20px;
      background-color: rgba(255, 255, 255, 0.1);
    }
  </style>
</head>
<body>

  <div class="logo mt-4">
    <img src="images/logo.png" alt="SL Geek Logo">
  </div>

  <div class="hero">
    <h1>Create Short Links Effortlessly</h1>
    <p>Track clicks, set expiry dates, and protect with passwords — all for free!</p>
    <a href="login.php" class="btn btn-light btn-custom">Login</a>
    <a href="register.php" class="btn btn-outline-light btn-custom">Register</a>
  </div>

  <!-- Link Shortener Input -->
  <div class="shortener px-3">
    <form id="shortForm" class="input-group">
      <input type="url" class="form-control" placeholder="Paste your long URL here..." name="destinationLink" required>
      <button type="submit" class="btn btn-warning">Shorten</button>
    </form>
  </div>

  <!-- Features -->
  <div class="container features">
    <div class="row text-center">
      <div class="col-md-3">
        <i class="bi bi-link-45deg"></i>
        <h5>Custom Short Links</h5>
        <p>Create 8-character URLs in seconds.</p>
      </div>
      <div class="col-md-3">
        <i class="bi bi-eye"></i>
        <h5>Click Tracking</h5>
        <p>Know who clicks, from where, and how.</p>
      </div>
      <div class="col-md-3">
        <i class="bi bi-shield-lock"></i>
        <h5>Password Protection</h5>
        <p>Restrict access to links securely.</p>
      </div>
      <div class="col-md-3">
        <i class="bi bi-calendar-x"></i>
        <h5>Set Expiry</h5>
        <p>Automatically disable links after a set time.</p>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="copiedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header">
          <h5 class="modal-title">Short Link Created</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="shortLinkText" class="text-break"></p>
          <p class="text-success">Link copied to clipboard!</p>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; <?= date("Y") ?> SL Geek | All rights reserved</p>
  </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $('#shortForm').on('submit', function(e) {
      e.preventDefault();
      const url = $(this).find('input[name="destinationLink"]').val();

      $.ajax({
        url: 'includes/link.ajax.php',
        method: 'POST',
        data: { destinationLink: url },
        success: function(response) {
          if (response.startsWith('http')) {
            navigator.clipboard.writeText(response);
            $('#shortLinkText').text(response);
            $('#copiedModal').modal('show');
            $('#shortForm')[0].reset();
          } else {
            alert("Something went wrong: " + response);
          }
        }
      });
    });
  </script>
</body>
</html>