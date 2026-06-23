<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Verify admin role using your auth system
checkAdmin();

// Get PDO connection from db.php
$pdo = getPDO();  // Add this line to get the database connection

// Validate report parameters
if (!isset($_POST['report_type'], $_POST['format'])) {
    header("Location: dashboard.php?error=Missing+parameters");
    exit();
}

$allowed_types = ['sales', 'users', 'products'];
$allowed_formats = ['csv', 'pdf'];

$report_type = $_POST['report_type'];
$format = $_POST['format'];
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;

// Validate inputs
if (!in_array($report_type, $allowed_types) || !in_array($format, $allowed_formats)) {
    header("Location: dashboard.php?error=Invalid+parameters");
    exit();
}

// Base SQL queries with prepared statements
switch ($report_type) {
    case 'sales':
        $sql = "SELECT p.id, p.title, p.price, p.created_at, u.name AS seller 
                FROM products p
                JOIN users u ON p.user_id = u.id
                WHERE p.sold = 1";
        $filename = "sales-report";
        break;
        
    case 'users':
        $sql = "SELECT u.id, u.name, u.email, u.created_at, 
                COUNT(p.id) AS listings, 
                SUM(p.sold) AS sales
                FROM users u
                LEFT JOIN products p ON u.id = p.user_id
                GROUP BY u.id";
        $filename = "user-activity";
        break;
        
    case 'products':
        $sql = "SELECT p.*, u.name AS seller 
                FROM products p
                JOIN users u ON p.user_id = u.id";
        $filename = "product-listings";
        break;
}

// Add date filter if provided
$params = [];
if ($start_date && $end_date) {
    if (!validateDate($start_date) || !validateDate($end_date)) {
        header("Location: dashboard.php?error=Invalid+date+format");
        exit();
    }
    
    $clause = str_contains($sql, 'WHERE') ? 'AND' : 'WHERE';
    $sql .= " $clause p.created_at BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date . ' 23:59:59';
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($data)) {
        header("Location: dashboard.php?error=No+data+found");
        exit();
    }

    if ($format === 'csv') {
        generateCSV($data, $filename);
    } elseif ($format === 'pdf') {
        generatePDF($data, $filename);
    }

} catch (PDOException $e) {
    error_log("Report generation error: " . $e->getMessage());
    header("Location: dashboard.php?error=Report+generation+failed");
    exit();
}

function generateCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write headers
    fputcsv($output, array_keys($data[0]));
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

function generatePDF($data, $filename) {
    require_once __DIR__ . '/../libs/fpdf/fpdf.php';
    
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);
    
    // PDF Title
    $pdf->Cell(0,10,ucfirst(str_replace('-', ' ', $filename)).' Report',0,1,'C');
    $pdf->Ln(10);
    
    // Headers
    $headers = array_keys($data[0]);
    foreach ($headers as $header) {
        $pdf->Cell(40,10,ucwords(str_replace('_', ' ', $header)),1);
    }
    $pdf->Ln();
    
    // Data Rows
    foreach ($data as $row) {
        foreach ($row as $value) {
            $pdf->Cell(40,10,$value,1);
        }
        $pdf->Ln();
    }
    
    $pdf->Output('D', $filename.'.pdf');
    exit();
}

function validateDate($date) {
    return (bool)strtotime($date);
}