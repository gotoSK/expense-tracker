<?php
session_start();
require_once 'inc/db.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Validate the form inputs
    if (empty($email) || empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_new_password) {
        $error = 'New password and confirm password do not match.';
    } else {
        // Check if the email exists and get the user's current password
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND email = ?");
        $stmt->execute([$user_id, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($old_password, $user['password'])) {
            // Update the password if the old password is correct
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_new_password, $user_id])) {
                $message = 'Password updated successfully!';
            } else {
                $error = 'Failed to update the password. Please try again later.';
            }
        } else {
            $error = 'Incorrect old password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Expense Tracker</a>
        <div>
            <a href="logout.php" class="btn btn-warning">Logout</a>
            <a href="dashboard.php" class="btn btn-outline-light">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Reset Password</h3>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Reset Password Form -->
    <div class="card mb-4">
        <div class="card-header">Reset Your Password</div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="old_password" class="form-label">Old Password</label>
                    <input type="password" name="old_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_new_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Reset Password</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
