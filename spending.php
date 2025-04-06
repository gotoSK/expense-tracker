<?php
session_start();
require_once 'inc/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get total spending per category
$stmt = $pdo->prepare("SELECT e.category_id, c.name AS category_name, SUM(e.amount) AS total_spent
                       FROM expenses e
                       JOIN categories c ON e.category_id = c.id
                       WHERE e.user_id = ?
                       GROUP BY e.category_id");
$stmt->execute([$user_id]);
$spending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Spending Per Category | Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Expense Tracker</a>
        <div>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Spending Per Category</h3>

    <!-- Spending Per Category Table -->
    <div class="card">
        <div class="card-header">Your Spending Per Category</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Total Spending</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($spending)): ?>
                        <tr>
                            <td colspan="2">No spending recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($spending as $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['category_name']) ?></td>
                                <td>$<?= number_format($category['total_spent'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
