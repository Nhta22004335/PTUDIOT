<?php
require_once "config.php";

try {
    $conn = connectDatabase();
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}

$id = $_GET['id'] ?? '';

if ($id) {
    $stmt = $conn->prepare("DELETE FROM cambien WHERE idcb = ?");
    $stmt->execute([$id]);
}

header("Location: quanlytb.php");
exit;
