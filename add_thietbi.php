<?php
require_once "config.php";

try {
    $conn = connectDatabase();
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tentb = $_POST['tentb'] ?? '';
    $trangthai = $_POST['trangthai'] ?? '';
    $idvt = $_POST['idvt'] ?? '';

    $stmt = $conn->prepare("INSERT INTO thietbi (tentb, trangthai, idvt) VALUES (?, ?, ?)");
    $stmt->execute([$tentb, $trangthai, $idvt]);
    header("Location: quanlytb.php");
    exit;
}

$stmt = $conn->query("SELECT idkv, tenkv FROM khuvuc");
$khuvucs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Thiết Bị</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-xl mx-auto bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Thêm Thiết Bị</h1>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block mb-1 font-medium">Tên Thiết Bị</label>
                <input type="text" name="tentb" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Trạng Thái</label>
                <input type="text" name="trangthai" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Khu Vực</label>
                <select name="idvt" required class="w-full px-3 py-2 border rounded">
                    <?php foreach ($khuvucs as $kv): ?>
                        <option value="<?= $kv['idkv'] ?>"><?= $kv['tenkv'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Thêm</button>
        </form>
    </div>
</body>
</html>
