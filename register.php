<?php
// register.php
session_start();
if (isset($_SESSION['userId'])) {
    header("Location: dashboard/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - SL Geek Links</title>
  <link rel="icon" href="images/fav.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #1f1c2c, #928dab);
      color: #fff;
      font-family: "Segoe UI", sans-serif;
    }
    .container {
      max-width: 450px;
      margin-top: 80px;
    }
  </style>
</head>
<body>
  <div class="container text-center">
    <img src="images/logo.png" alt="SL Geek Logo" class="mb-4" style="width: 150px">
    <h2>Create an Account</h2>
    <form action="includes/register.inc.php" method="post" class="mt-4">
      <div class="form-floating mb-3">
        <input type="text" name="fname" class="form-control" required>
        <label>First Name</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" name="lname" class="form-control" required>
        <label>Last Name</label>
      </div>
      <div class="form-floating mb-3">
        <input type="email" name="email" class="form-control" required>
        <label>Email address</label>
      </div>
      <div class="form-floating mb-3">
        <input type="password" name="password" class="form-control" required>
        <label>Password</label>
      </div>
      <button class="btn btn-success w-100" type="submit">Register</button>
    </form>
    <p class="mt-3">Already have an account? <a href="login.php" class="text-light text-decoration-underline">Login here</a></p>
  </div>
</body>
</html>
