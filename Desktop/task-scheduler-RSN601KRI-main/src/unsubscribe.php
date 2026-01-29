<?php
require_once 'functions.php';

$message = '';

if (isset($_GET['email'])) {
    $email = $_GET['email'];

    if (unsubscribeEmail($email)) {
        $message = "🗑️ $email has been successfully unsubscribed.";
    } else {
        $message = "❌ Could not unsubscribe. Email not found or already removed.";
    }
} else {
    $message = "⚠️ Email parameter missing in request.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
        }
        .message {
            margin-top: 20px;
            font-size: 18px;
            color: #555;
        }
    </style>
</head>
<body>
    <h2 id="unsubscribe-heading">Unsubscribe from Task Reminders</h2>
    <div class="message"><?= htmlspecialchars($message) ?></div>
</body>
</html>
