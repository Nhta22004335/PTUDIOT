<?php
require_once "config.php";

try {
    $conn = connectDatabase();
    
    // Nhận dữ liệu từ AJAX
    $idtb = $_POST['idtb'] ?? null;
    $trangthai = $_POST['trangthai'] ?? null;

    if ($idtb === null || $trangthai === null) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin idtb hoặc trangthai']);
        exit;
    }

    // Kiểm tra xem idtb đã tồn tại trong nhatkytb chưa
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM nhatkytb WHERE idtb = ?");
    $checkStmt->execute([$idtb]);
    $exists = $checkStmt->fetchColumn() > 0;

    if ($exists) {
        // Cập nhật trạng thái và thời gian nếu idtb đã tồn tại
        $stmt = $conn->prepare("UPDATE nhatkytb SET trangthai = ?, thoigian = NOW() WHERE idtb = ?");
        $stmt->execute([$trangthai, $idtb]);
        echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
    } else {
        // Thêm bản ghi mới nếu idtb chưa tồn tại
        $stmt = $conn->prepare("INSERT INTO nhatkytb (idtb, trangthai, thoigian) VALUES (?, ?, NOW())");
        $stmt->execute([$idtb, $trangthai]);
        echo json_encode(['success' => true, 'message' => 'Thêm trạng thái thành công']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>