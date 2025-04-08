<?php
session_start();

// in case user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="text-center">
            <h1 class="mb-4">Your expense Tracker System.</h1>
            <p class="lead"> Track spending habits, analyze budget, and make more informed financial decisions!</p>
            <div class="mt-4">
                <a href="login.php" class="btn btn-primary me-2">Login</a>
                <a href="register.php" class="btn btn-outline-primary">Register</a>
            </div>
        </div>
    </div>
</body>
</html>
