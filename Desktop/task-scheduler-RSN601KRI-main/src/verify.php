<?php
require_once 'functions.php';

// ✅ Implement verification logic
$message = '';

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $code = $_GET['code'];

    if (verifySubscription($email, $code)) {
        $message = "✅ Your email ($email) has been successfully verified and subscribed.";
    } else {
        $message = "❌ Verification failed. Either the email or verification code is invalid.";
    }
} else {
    $message = "⚠️ Missing email or verification code.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
        }
        #verification-heading {
            color: #333;
        }
        .message {
            margin-top: 20px;
            font-size: 18px;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- Do not modify the ID of the heading -->
    <h2 id="verification-heading">Subscription Verification</h2>
    
    <div class="message"><?= htmlspecialchars($message) ?></div>
</body>
</html>
