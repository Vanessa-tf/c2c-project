<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAuth();

require('../libs/fpdf/fpdf.php');

$pdo = getPDO();
$order_id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? 'pdf';

// Get order + user + shipping_method_id
$stmt = $pdo->prepare("SELECT o.id, o.created_at, o.shipping_method_id, o.total, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get shipping method
$stmt = $pdo->prepare("SELECT name, cost FROM shipping_methods WHERE id = ?");
$stmt->execute([$order['shipping_method_id']]);
$shipping = $stmt->fetch();

// Get order items
$stmt = $pdo->prepare("SELECT p.title, p.price, oi.quantity FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

if ($type === 'pdf') {
    $pdf = new FPDF();
    $pdf->AddPage();

    // Header
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'Invoice #'.$order['id'],0,1);
    $pdf->Ln(10);

    // Customer info
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,'Customer: '.$order['email'],0,1);
    $pdf->Cell(0,10,'Date: '.$order['created_at'],0,1);
    $pdf->Cell(0,10,'Shipping Method: '.$shipping['name'].' (R '.number_format($shipping['cost'], 2).')',0,1);
    $pdf->Ln(10);

    // Table header
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(100,10,'Product',1);
    $pdf->Cell(30,10,'Price',1);
    $pdf->Cell(30,10,'Qty',1);
    $pdf->Cell(30,10,'Total',1);
    $pdf->Ln();

    // Table content
    $pdf->SetFont('Arial','',12);
    $grandTotal = 0;
    foreach ($items as $item) {
        $total = $item['price'] * $item['quantity'];
        $grandTotal += $total;

        $pdf->Cell(100,10,$item['title'],1);
        $pdf->Cell(30,10,'R '.number_format($item['price'],2),1);
        $pdf->Cell(30,10,$item['quantity'],1);
        $pdf->Cell(30,10,'R '.number_format($total,2),1);
        $pdf->Ln();
    }

    // Shipping row
    $pdf->Cell(160,10,'Shipping:',1,0,'R');
    $pdf->Cell(30,10,'R '.number_format($shipping['cost'], 2),1,1);

    // Grand total
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(160,10,'Grand Total:',1,0,'R');
    $pdf->Cell(30,10,'R '.number_format($order['total'],2),1,1);

    $pdf->Output('D','invoice_'.$order['id'].'.pdf');
    exit();

} elseif ($type === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="invoice_'.$order['id'].'.csv"');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['Product', 'Price', 'Quantity', 'Total']);

    $grandTotal = 0;
    foreach ($items as $item) {
        $total = $item['price'] * $item['quantity'];
        $grandTotal += $total;
        fputcsv($output, [
            $item['title'],
            'R '.number_format($item['price'], 2),
            $item['quantity'],
            'R '.number_format($total, 2)
        ]);
    }

    // Shipping row
    fputcsv($output, ['', '', 'Shipping:', 'R '.number_format($shipping['cost'], 2)]);

    // Total
    fputcsv($output, ['', '', 'Grand Total:', 'R '.number_format($order['total'], 2)]);

    fclose($output);
    exit();
}

// Fallback
header("Location: view_order.php?id=$order_id");
exit();
