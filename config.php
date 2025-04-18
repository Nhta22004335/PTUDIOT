<?php
    function connectDatabase() {
        $host = "localhost"; // Địa chỉ máy chủ MySQL
        $dbname = "iotgraden"; // Tên cơ sở dữ liệu
        $username = "root"; // Tên người dùng MySQL
        $password = ""; // Mật khẩu MySQL (để trống nếu dùng XAMPP)
        try {
            // Tạo kết nối PDO
            $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            // Thiết lập chế độ báo lỗi
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Kết nối thất bại: " . $e->getMessage());
        }
    }
?>
