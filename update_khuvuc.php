<?php
require_once "config.php";

try {
    $conn = connectDatabase();
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}

$id = $_GET['id'] ?? '';

if (!$id) {
    header("Location: quanlytb.php");
    exit;
}

// Lấy thông tin khu vực hiện tại
$stmt = $conn->prepare("SELECT * FROM khuvuc WHERE idkv = ?");
$stmt->execute([$id]);
$khuvuc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$khuvuc) {
    echo "Không tìm thấy khu vực.";
    exit;
}

// Cập nhật khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenkv = $_POST['tenkv'] ?? '';
    $mota = $_POST['mota'] ?? '';

    $stmt = $conn->prepare("UPDATE khuvuc SET tenkv = ?, mota = ? WHERE idkv = ?");
    $stmt->execute([$tenkv, $mota, $id]);
    header("Location: quanlytb.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập Nhật Khu Vực</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-xl mx-auto bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Cập Nhật Khu Vực</h1>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block mb-1 font-medium">Tên Khu Vực</label>
                <input type="text" name="tenkv" value="<?= htmlspecialchars($khuvuc['tenkv']) ?>" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Mô Tả</label>
                <textarea name="mota" rows="4" class="w-full px-3 py-2 border rounded"><?= htmlspecialchars($khuvuc['mota']) ?></textarea>
            </div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Lưu Thay Đổi</button>
        </form>
    </div>
</body>
</html>
