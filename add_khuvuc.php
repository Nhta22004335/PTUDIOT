<?php
require_once "config.php";

try {
    $conn = connectDatabase();
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenkv = $_POST['tenkv'] ?? '';
    $mota = $_POST['mota'] ?? '';

    $stmt = $conn->prepare("INSERT INTO khuvuc (tenkv, mota) VALUES (?, ?)");
    $stmt->execute([$tenkv, $mota]);
    header("Location: quanlytb.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Khu Vực</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-xl mx-auto bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Thêm Khu Vực</h1>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block mb-1 font-medium">Tên Khu Vực</label>
                <input type="text" name="tenkv" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Mô Tả</label>
                <textarea name="mota" rows="4" class="w-full px-3 py-2 border rounded" placeholder="Mô tả thêm về khu vực..."></textarea>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Thêm</button>
        </form>
    </div>
</body>
</html>
