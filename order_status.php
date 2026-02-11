<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /po_alina/orders.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$allowed = ['draft', 'confirmed', 'shipped', 'completed', 'cancelled'];
if (!$id || !in_array($status, $allowed)) {
    header('Location: /po_alina/orders.php');
    exit;
}

$pdo = getDb();
$stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
$stmt->execute([$status, $id]);
header('Location: /po_alina/order_view.php?id=' . $id);
exit;
