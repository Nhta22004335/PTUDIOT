<?php
include 'config.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
$conn = connectDatabase();

if (isset($_POST['submit'])) {
    $hoten = $_POST['hoten'];
    $tendn = $_POST['tendn'];
    $matkhau = password_hash($_POST['matkhau'], PASSWORD_DEFAULT);
    $sdt = $_POST['sdt'];
    $email = $_POST['email'];
    $anh = 'picture/avttree.png';

    // Kiểm tra tài khoản đã tồn tại
    $stmt = $conn->prepare("SELECT * FROM nguoidung WHERE tendn = :tendn OR email = :email");
    $stmt->execute(['tendn' => $tendn, 'email' => $email]);

    if ($stmt->rowCount() > 0) {
        echo "Tên đăng nhập hoặc email đã tồn tại!";
    } else {
        // Thêm tài khoản mới
        $sql = "INSERT INTO nguoidung (hoten, tendn, matkhau, sdt, email, anh)
                VALUES (:hoten, :tendn, :matkhau, :sdt, :email, :anh)";

        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            'hoten' => $hoten,
            'tendn' => $tendn,
            'matkhau' => $matkhau,
            'sdt' => $sdt,
            'email' => $email,
            'anh' => $anh
        ]);

        if ($success) {
            header("Location: login.html");
            exit();
        } else {
            echo "Đã xảy ra lỗi khi đăng ký!";
        }
    }
}
?>
