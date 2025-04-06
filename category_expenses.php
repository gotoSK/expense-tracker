<?php
session_start();
require_once 'inc/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all categories for the user
$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ?");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare an array to hold expenses grouped by category
$category_expenses = [];

foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE user_id = ? AND category_id = ?");
    $stmt->execute([$user_id, $category['id']]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $category_expenses[$category['name']] = $expenses;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expenses by Category | Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Expense Tracker</a>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light">Dashboard</a>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Expenses Grouped by Category</h3>

    <?php foreach ($category_expenses as $category_name => $expenses): ?>
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <strong><?= htmlspecialchars($category_name) ?></strong>
            </div>
            <div class="card-body p-0">
                <?php if (count($expenses) > 0): ?>
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?= htmlspecialchars($expense['expense_date']) ?></td>
                                    <td>$<?= number_format($expense['amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($expense['description']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-3">No expenses recorded in this category.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
