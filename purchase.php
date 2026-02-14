<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$comic_id = intval($_POST['comic_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);

// Insert purchase record
$stmt = $conn->prepare("INSERT INTO purchases (user_id, comic_id, amount_paid) VALUES (?,?,?)");
$stmt->bind_param("iid", $user_id, $comic_id, $amount);
$stmt->execute();

// Redirect to invoice page
$purchase_id = $stmt->insert_id;
header("Location: purchase_invoice.php?id=".$purchase_id);
exit;