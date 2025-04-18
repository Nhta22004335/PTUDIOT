<?php
include "config.php";

if (isset($_POST['sensors'])) {
    $data = json_decode($_POST['sensors'], true);
    
    try {
        $conn = connectDatabase();
        $sql = "INSERT INTO nhatkycb (idcb, giatricb, thoigian) VALUES (:idcb, :giatricb, NOW())";
        $stmt = $conn->prepare($sql);

        foreach ($data as $sensor) {
            $stmt->execute([
                ':idcb' => $sensor['idcb'],
                ':giatricb' => $sensor['value']
            ]);
        }

        echo "Đã cập nhật " . count($data) . " cảm biến.";
    } catch (PDOException $e) {
        echo "Lỗi CSDL: " . $e->getMessage();
    } finally {
        $conn = null;
    }
}
?>
