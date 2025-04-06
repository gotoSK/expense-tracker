<?php
session_start();
require_once 'inc/db.php';
require_once __DIR__ . '/vendor/autoload.php';

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

if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    // Create a new PDF document
    $pdf = new TCPDF();
    $pdf->AddPage();

    // Set letterhead (You can customize this as per your needs)
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Expense Tracker Report', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'User-' . $_SESSION['user_id'], 0, 1, 'C');
    $pdf->Ln(10); // Add line break

    // Add table header
    $pdf->Cell(30, 10, 'Date', 1);
    $pdf->Cell(30, 10, 'Amount', 1);
    $pdf->Cell(50, 10, 'Category', 1);
    $pdf->Cell(80, 10, 'Description', 1);
    $pdf->Ln(); // New line

    // Write data rows
    foreach ($expenses as $expense) {
        $pdf->Cell(30, 10, $expense['expense_date'], 1);
        $pdf->Cell(30, 10, '$' . number_format($expense['amount'], 2), 1);
        $pdf->Cell(50, 10, $expense['category'], 1);
        $pdf->Cell(80, 10, $expense['description'], 1);
        $pdf->Ln(); // New line
    }

    // Output the PDF
    $pdf->Output('expenses_report.pdf', 'D');
    exit();
}
?>
