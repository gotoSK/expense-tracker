<?php
session_start();
require_once 'inc/db.php';

// redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$predefined_categories = ['food', 'travel', 'rent', 'entertainment'];

// expense form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['expense_date'];
    $category_id = $_POST['category_id'];
    $custom_category = $_POST['custom_category'];

    // insert category to db
    // if user selects a pre-defined category
    if (in_array($category_id, $predefined_categories)) {
        // check if the same category already exists for this user in db
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ?");
        $stmt->execute([$user_id, ucfirst($category_id)]);
        $existing_category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_category) {
            $category_id = $existing_category['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
            if ($stmt->execute([$user_id, ucfirst($category_id)])) {
                // Get the ID of the newly inserted predefined category
                $category_id = $pdo->lastInsertId();
            } else {
                $message = "Failed to create predefined category.";
                exit();
            }
        }
    } elseif ($category_id == 'custom' && !empty($custom_category)) {
        // Check if custom category already exists for the user
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ?");
        $stmt->execute([$user_id, $custom_category]);
        $existing_category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_category) {
            // If category exists, use the existing category ID
            $category_id = $existing_category['id'];
        } else {
            // Insert custom category
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
            if ($stmt->execute([$user_id, $custom_category])) {
                // Get the ID of the newly inserted custom category
                $category_id = $pdo->lastInsertId();
            } else {
                $message = "Failed to create custom category.";
                exit();
            }
        }
    }

    // Insert the expense
    $stmt = $pdo->prepare("INSERT INTO expenses (user_id, category_id, amount, description, expense_date) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $category_id, $amount, $description, $date])) {
        $message = "Expense added successfully!";
    } else {
        $message = "Failed to add expense.";
    }
}

// Get recent expenses
$expenses = $pdo->prepare("SELECT e.*, c.name as category FROM expenses e 
                           JOIN categories c ON e.category_id = c.id
                           WHERE e.user_id = ? ORDER BY e.expense_date DESC LIMIT 10");
$expenses->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard | Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Expense Tracker</a>
        <div>
            <a href="logout.php" class="btn btn-warning">Logout</a>
            <a href="reset-password.php" class="btn btn-outline-light">Reset Password</a>
            <a href="budget.php" class="btn btn-outline-light">Manage Budget</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Welcome!</h3>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- 'Add Expense' Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Expense</div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Date</label>
                        <input type="date" name="expense_date" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Category</label>
                        <select name="category_id" class="form-select" id="category-select" required>
                            <option value="">Select</option>
                            <option value="food">Food</option>
                            <option value="travel">Travel</option>
                            <option value="rent">Rent</option>
                            <option value="entertainment">Entertainment</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3" id="custom-category-input" style="display: none;">
                    <label>Enter Custom Category</label>
                    <input type="text" name="custom_category" class="form-control" placeholder="Enter custom category name">
                </div>
                <div class="mb-3">
                    <label>Description (optional)</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-success">Add Expense</button>
            </form>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="card">
        <div class="card-header">Recent Expenses</div>
        <div class="card-body">
            <!-- Export Buttons -->
            <div class="mb-3">
                <a href="report-csv.php?export=csv" class="btn btn-success">Export to CSV</a>
                <a href="report-pdf.php?export=pdf" class="btn btn-primary">Export to PDF</a>
            </div>
            <a href="category_expenses.php">Filter</a>
            <a href="spending.php">Overview</a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($exp = $expenses->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?= htmlspecialchars($exp['expense_date']) ?></td>
                            <td>$<?= number_format($exp['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($exp['category']) ?></td>
                            <td><?= htmlspecialchars($exp['description']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- show/hide custom category input -->
<script>
    document.getElementById('category-select').addEventListener('change', function () {
        var customCategoryInput = document.getElementById('custom-category-input');
        if (this.value === 'custom') {
            customCategoryInput.style.display = 'block';
        } else {
            customCategoryInput.style.display = 'none';
        }
    });
</script>

</body>
</html>
