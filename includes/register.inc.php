<?php
// includes/register.inc.php
require_once './dbh.inc.php';
require_once '../send-email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$fName = trim($_POST['fname'] ?? '');
$lName = trim($_POST['lname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$fName || !$lName || !$email || !$password) {
    echo "$fName || !$lName || !$email || !$password";
    die('Please fill in all fields.');
    
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email format.');
}

if (strlen($password) < 8) {
    die('Password must be at least 8 characters long.');
}

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM linkusers WHERE userEmail = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    die('User already exists.');
}

$hashedPWD = password_hash($password, PASSWORD_DEFAULT);
$sToken = bin2hex(random_bytes(16));
$userStatus = 0;

$stmt = $conn->prepare("INSERT INTO linkusers (fName, lName, userEmail, hashedPWD, userStatus, sToken) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssis', $fName, $lName, $email, $hashedPWD, $userStatus, $sToken);
$stmt->execute();

// Email Verification
$sessionKey = bin2hex(random_bytes(16));
$createdTime = date('Y-m-d H:i:s');
$sessionStatus = 0;

$stmt = $conn->prepare("INSERT INTO linkuserval (userEmail, sessionKey, createdTime, sessionStatus) VALUES (?, ?, ?, ?)");
$stmt->bind_param('sssi', $email, $sessionKey, $createdTime, $sessionStatus);
$stmt->execute();

$verifyLink = 'https://link.slgeek.lk/validate-user.php?session=' . $sessionKey;

$mailSubject = "Verify Your SL Geek Link Account";
$mailContent = "<div style='font-family:Arial,sans-serif;padding:20px;'>
    <img src='https://link.slgeek.lk/images/logo.png' style='width:120px;margin-bottom:20px;'>
    <h2>Welcome to SL Geek Links!</h2>
    <p>Hi $fName,</p>
    <p>Thanks for signing up. Please click the link below to verify your email address:</p>
    <p><a href='$verifyLink' style='background:#4CAF50;color:white;padding:10px 20px;text-decoration:none;'>Verify Email</a></p>
    <p>If you didn’t sign up, you can ignore this email.</p>
    <br><p>SL Geek Team</p>
</div>";

sendEmail($email, "$fName $lName", $mailContent, $mailSubject);

header('Location: ../login.php?verify=1');
exit;