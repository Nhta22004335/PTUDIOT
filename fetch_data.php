<?php
include "config.php";
$pdo = connectDatabase();
// Get report type from POST request
$reportType = isset($_POST['reportType']) ? $_POST['reportType'] : '';
$data = [];

switch ($reportType) {
    case 'device':
        $stmt = $pdo->prepare("SELECT idtb, tentb, trangthai FROM thietbi");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'sensor':
        $stmt = $pdo->prepare("SELECT idcb, tencb, idvt, trangthai FROM cambien");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'sensorHistory':
        $stmt = $pdo->prepare("SELECT idcb, thoigian, giatricb FROM nhatkycb");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'deviceHistory':
        $stmt = $pdo->prepare("SELECT idtb, thoigian, trangthai FROM nhatkytb");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid report type']);
        exit;
}

// Output JSON data
header('Content-Type: application/json');
echo json_encode($data);
?>