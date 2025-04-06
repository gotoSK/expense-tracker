<?php
session_start();
require_once 'inc/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's expenses
$stmt = $pdo->prepare("SELECT e.*, c.name AS category FROM expenses e 
                       JOIN categories c ON e.category_id = c.id
                       WHERE e.user_id = ?");
$stmt->execute([$user_id]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="expenses_report.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV column headers
    fputcsv($output, ['Date', 'Amount', 'Category', 'Description']);
    
    // Write each expense as a CSV row
    foreach ($expenses as $expense) {
        fputcsv($output, [
            $expense['expense_date'],
            number_format($expense['amount'], 2),
            $expense['category'],
            $expense['description']
        ]);
    }
    
    fclose($output);
    exit();
}
?>
