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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      color: #fff;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      text-align: center;
    }

    .logo img {
      width: 180px;
      max-width: 80%;
    }

    .hero {
      padding: 60px 20px 30px;
    }

    .hero h1 {
      font-size: 2.5rem;
      font-weight: bold;
      margin-bottom: 15px;
    }

    .hero p {
      font-size: 1.1rem;
      margin-bottom: 25px;
    }

    .btn-custom {
      padding: 10px 25px;
      font-size: 1rem;
      margin: 5px;
    }

    .shortener {
      margin: 20px auto 40px;
      max-width: 650px;
      width: 90%;
    }

    .features {
      margin-top: 40px;
    }

    .features i {
      font-size: 2.2rem;
      margin-bottom: 10px;
      display: block;
    }

    footer {
      margin-top: auto;
      padding: 15px;
      background-color: rgba(255, 255, 255, 0.1);
    }

    @media (max-width: 576px) {
      .hero h1 {
        font-size: 2rem;
      }
      .hero p {
        font-size: 1rem;
      }
      .btn-custom {
        display: block;
        width: 80%;
        margin: 10px auto;
      }
    }
  </style>
</head>
<body>

  <div class="logo mt-4">
    <img src="images/logo.png" alt="SL Geek Logo" class="img-fluid">
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
      <div class="col-6 col-md-3 mb-4">
        <i class="bi bi-link-45deg"></i>
        <h5>Short Links</h5>
        <p>Generate short 8-character URLs easily.</p>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <i class="bi bi-eye"></i>
        <h5>Click Tracking</h5>
        <p>Know who clicks your links.</p>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <i class="bi bi-shield-lock"></i>
        <h5>Password Lock</h5>
        <p>Protect links with secure access.</p>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <i class="bi bi-calendar-x"></i>
        <h5>Set Expiry</h5>
        <p>Auto-disable after a date.</p>
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