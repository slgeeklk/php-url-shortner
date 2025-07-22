<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'dbh.inc.php';
require_once '../send-email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate inputs
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = "Invalid email format.";
    header("Location: ../login.php");
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['login_error'] = "Invalid password.";
    header("Location: ../login.php");
    exit();
}

// Check user
$stmt = $conn->prepare("SELECT * FROM linkusers WHERE userEmail = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows < 1) {
    $_SESSION['login_error'] = "User not found.";
    header("Location: ../login.php");
    exit();
}

$user = $result->fetch_assoc();

// Check if verified
if ($user['userStatus'] != 1) {
    // Check or create new validation session
    $check = $conn->prepare("SELECT * FROM linkuserval WHERE userEmail = ? AND sessionStatus = 0");
    $check->bind_param("s", $email);
    $check->execute();
    $checkRes = $check->get_result();

    if ($checkRes->num_rows === 0) {
        // Create a new session
        $sessionKey = bin2hex(random_bytes(16));
        $createdTime = date("Y-m-d H:i:s");
        $insert = $conn->prepare("INSERT INTO linkuserval (userEmail, sessionKey, createdTime, sessionStatus) VALUES (?, ?, ?, 0)");
        $insert->bind_param("sss", $email, $sessionKey, $createdTime);
        $insert->execute();

        // Send verification email
        $fullName = $user['fName'] . " " . $user['lName'];
        $verifyLink = "https://link.slgeek.lk/validate-user.php?session=$sessionKey";
        $mailSubject = "Verify Your SL Geek Link Account";
        $mailContent = "
            <div style='font-family:sans-serif; background:#f9f9f9; padding:30px; text-align:center;'>
                <img src='https://link.slgeek.lk/images/logo.png' alt='SL Geek Logo' style='width:150px; margin-bottom:20px;'>
                <h2>Welcome, $fullName!</h2>
                <p>Please verify your email by clicking the button below:</p>
                <a href='$verifyLink' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Verify Email</a>
                <p style='margin-top:20px; font-size:12px;'>Link valid for 12 hours. If you didn’t request this, you can ignore this email.</p>
            </div>
        ";
        sendEmail($email, $fullName, $mailContent, $mailSubject);
    }

    $_SESSION['login_error'] = "Please verify your email first. We've sent you a link.";
    header("Location: ../login.php");
    exit();
}

// Check password
if (!password_verify($password, $user['hashedPWD'])) {
    $_SESSION['login_error'] = "Incorrect password.";
    header("Location: ../login.php");
    exit();
}

// Set session
$_SESSION['userId'] = $user['id'];
$_SESSION['userEmail'] = $user['userEmail'];
$_SESSION['userName'] = $user['fName'];

// Set "remember me" cookie
if ($remember) {
    if (empty($user['sToken'])) {
        $newToken = bin2hex(random_bytes(32));
        $updateToken = $conn->prepare("UPDATE linkusers SET sToken = ? WHERE id = ?");
        $updateToken->bind_param("si", $newToken, $user['id']);
        $updateToken->execute();
    } else {
        $newToken = $user['sToken'];
    }

    setcookie("slg_token", $newToken, time() + (30 * 24 * 60 * 60), "/", "", true, true); // 30 days
}

header("Location: ../dashboard/index.php");
exit();