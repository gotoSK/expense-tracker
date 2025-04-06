<?php
session_start();
require_once 'inc/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle budget form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $budget_amount = $_POST['budget_amount'];

    // Validate the inputs
    if (empty($category_id) || empty($budget_amount)) {
        $error = 'Please select a category and enter a budget amount.';
    } else {
        // Check if the budget already exists for the user and category
        $stmt = $pdo->prepare("SELECT * FROM budgets WHERE user_id = ? AND category_id = ?");
        $stmt->execute([$user_id, $category_id]);
        $existing_budget = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_budget) {
            // Update the existing budget
            $stmt = $pdo->prepare("UPDATE budgets SET limit_amount = ? WHERE user_id = ? AND category_id = ?");
            $stmt->execute([$budget_amount, $user_id, $category_id]);
            $message = 'Budget updated successfully!';
        } else {
            // Insert new budget
            $stmt = $pdo->prepare("INSERT INTO budgets (user_id, category_id, limit_amount) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $category_id, $budget_amount]);
            $message = 'Budget set successfully!';
        }
    }
}

// Get current budgets for the user
$stmt = $pdo->prepare("SELECT b.*, c.name AS category_name FROM budgets b
                       JOIN categories c ON b.category_id = c.id
                       WHERE b.user_id = ?");
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total spending per category
$spending = [];
$stmt = $pdo->prepare("SELECT e.category_id, SUM(e.amount) AS total_spent
                       FROM expenses e
                       WHERE e.user_id = ?
                       GROUP BY e.category_id");
$stmt->execute([$user_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $spending[$row['category_id']] = $row['total_spent'];
}

// Fetch unique categories for the current user
$stmt = $pdo->prepare("SELECT id, name FROM categories
                       WHERE user_id = ?");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Budget | Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Expense Tracker</a>
        <div>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
            <a href="reset-password.php" class="btn btn-warning">Reset Password</a>
            <a href="dashboard.php" class="btn btn-outline-light">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Manage Your Budget</h3>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Set Budget Form -->
    <div class="card mb-4">
        <div class="card-header">Set or Update Budget</div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="budget_amount" class="form-label">Budget Amount</label>
                    <input type="number" step="0.01" name="budget_amount" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Set Budget</button>
            </form>
        </div>
    </div>

    <!-- Current Budgets Overview -->
    <div class="card">
        <div class="card-header">Your Current Budgets</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Budget Amount</th>
                        <th>Spent Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($budgets as $budget): ?>
                        <?php
                            $category_id = $budget['category_id'];
                            $total_spent = isset($spending[$category_id]) ? $spending[$category_id] : 0;
                            $status = ($total_spent >= $budget['limit_amount']) ? 'Exceeded' : ($total_spent >= $budget['limit_amount'] * 0.8 ? 'Warning' : 'Under Budget');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($budget['category_name']) ?></td>
                            <td>$<?= number_format($budget['limit_amount'], 2) ?></td>
                            <td>$<?= number_format($total_spent, 2) ?></td>
                            <td class="<?= $status === 'Exceeded' ? 'text-danger' : ($status === 'Warning' ? 'text-warning' : 'text-success') ?>"><?= $status ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
