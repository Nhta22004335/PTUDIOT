<?php
// Kết nối CSDL
include 'config.php';
$pdo = connectDatabase();
try {
    // Truy vấn danh sách cảm biến
    $sql_cambien = "SELECT idcb AS ID, tencb AS Ten, trangthai AS TrangThai, thoigian AS ThoiGian FROM cambien;";
    $stmt_cb = $pdo->prepare($sql_cambien);
    $stmt_cb->execute();
    $data_cb = $stmt_cb->fetchAll(PDO::FETCH_ASSOC);

    // Truy vấn danh sách thiết bị điều khiển
    $sql_dieukhien = "SELECT iddk AS ID, tendk AS Ten, trangthai AS TrangThai, thoigian AS ThoiGian FROM dieukhien;";
    $stmt_dk = $pdo->prepare($sql_dieukhien);
    $stmt_dk->execute();
    $data_dk = $stmt_dk->fetchAll(PDO::FETCH_ASSOC);

    // Truy vấn danh sách vị trí lắp đặt
    $sql_vitrilapdat = "SELECT idvt AS ID, idtb AS ID_ThietBi, loaitb AS LoaiThietBi, khuvuc AS KhuVuc, soluong AS SoLuong, thoigian AS ThoiGian FROM vitrilapdat;";
    $stmt_vt = $pdo->prepare($sql_vitrilapdat);
    $stmt_vt->execute();
    $data_vt = $stmt_vt->fetchAll(PDO::FETCH_ASSOC);

    // Hiển thị bảng cảm biến
    echo "<h2>Danh Sách Cảm Biến</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên Cảm Biến</th><th>Trạng Thái</th><th>Thời Gian</th></tr>";

    foreach ($data_cb as $row) {
        $trangthai = ($row['TrangThai'] == 1) ? "<span style='color: green;'>ON</span>" : "<span style='color: red;'>OFF</span>";
        echo "<tr>
                <td>{$row['ID']}</td>
                <td>{$row['Ten']}</td>
                <td>{$trangthai}</td>
                <td>{$row['ThoiGian']}</td>
              </tr>";
    }
    echo "</table>";

    // Hiển thị bảng thiết bị điều khiển
    echo "<h2>Danh Sách Thiết Bị Điều Khiển</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên Thiết Bị</th><th>Trạng Thái</th><th>Thời Gian</th></tr>";

    foreach ($data_dk as $row) {
        $trangthai = ($row['TrangThai'] == 1) ? "<span style='color: green;'>ON</span>" : "<span style='color: red;'>OFF</span>";
        echo "<tr>
                <td>{$row['ID']}</td>
                <td>{$row['Ten']}</td>
                <td>{$trangthai}</td>
                <td>{$row['ThoiGian']}</td>
              </tr>";
    }
    echo "</table>";

    // Hiển thị bảng vị trí lắp đặt
    echo "<h2>Danh Sách Vị Trí Lắp Đặt</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>ID Thiết Bị</th><th>Loại Thiết Bị</th><th>Khu Vực</th><th>Số Lượng</th><th>Thời Gian</th></tr>";

    foreach ($data_vt as $row) {
        echo "<tr>
                <td>{$row['ID']}</td>
                <td>{$row['ID_ThietBi']}</td>
                <td>{$row['LoaiThietBi']}</td>
                <td>{$row['KhuVuc']}</td>
                <td>{$row['SoLuong']}</td>
                <td>{$row['ThoiGian']}</td>
              </tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>
