<?php
// Bắt đầu session để có thể thao tác với session
session_start();

// Xóa các session cụ thể
unset($_SESSION['idnd']);
unset($_SESSION['tendn']);
unset($_SESSION['matkhau']);
unset($_SESSION['hoten']);
unset($_SESSION['email']);

// Hủy session (sau khi xóa các session cần thiết)
session_destroy();

// Xóa cookie (nếu có)
if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, '/'); // Đặt thời gian hết hạn trong quá khứ để xóa cookie
}

if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/'); // Đặt thời gian hết hạn trong quá khứ để xóa cookie
}

// Chuyển hướng người dùng trở lại trang chủ
header('Location: trangchu.html');
exit();
?>
