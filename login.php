<?php
session_start();

include "config.php";
$conn = connectDatabase();

$tendn = $_POST['tendn'];
$matkhau = $_POST['matkhau'];

try {
    // Tìm người dùng theo tên đăng nhập
    $stmt = $conn->prepare("SELECT * FROM nguoidung WHERE tendn = :tendn");
    $stmt->bindParam(':tendn', $tendn);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // So sánh mật khẩu người dùng nhập với mật khẩu đã hash
        if (password_verify($matkhau, $user['matkhau'])) {
            $_SESSION['idnd'] = $user['idnd'];
            $_SESSION['tendn'] = $tendn;
            $_SESSION['matkhau'] = $matkhau;
            $_SESSION['hoten'] = $user['hoten'];
            $_SESSION['email'] = $user['email'];
            header("Location: trangchu.php");
            exit();
        } else {
            echo "Sai mật khẩu. <a href='login.html'>Thử lại</a>";
        }
    } else {
        echo "Tài khoản không tồn tại. <a href='login.html'>Thử lại</a>";
    }
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

$conn = null;
?>
