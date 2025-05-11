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

// Get current data
$stmt = $conn->prepare("SELECT * FROM cambien WHERE idcb = ?");
$stmt->execute([$id]);
$cambien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cambien) {
    echo "Không tìm thấy cảm biến.";
    exit;
}

// Get all areas
$stmt = $conn->query("SELECT idkv, tenkv FROM khuvuc");
$khuvucs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tencb = $_POST['tencb'] ?? '';
    $trangthai = $_POST['trangthai'] ?? '';
    $idvt = $_POST['idvt'] ?? '';

    $stmt = $conn->prepare("UPDATE cambien SET tencb = ?, trangthai = ?, idvt = ? WHERE idcb = ?");
    $stmt->execute([$tencb, $trangthai, $idvt, $id]);
    header("Location: quanlytb.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập Nhật Cảm Biến</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-xl mx-auto bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Cập Nhật Cảm Biến</h1>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block mb-1 font-medium">Tên Cảm Biến</label>
                <input type="text" name="tencb" value="<?= htmlspecialchars($cambien['tencb']) ?>" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Trạng Thái</label>
                <input type="text" name="trangthai" value="<?= htmlspecialchars($cambien['trangthai']) ?>" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Khu Vực</label>
                <select name="idvt" required class="w-full px-3 py-2 border rounded">
                    <?php foreach ($khuvucs as $kv): ?>
                        <option value="<?= $kv['idkv'] ?>" <?= $cambien['idvt'] == $kv['idkv'] ? 'selected' : '' ?>><?= $kv['tenkv'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Lưu Thay Đổi</button>
        </form>
    </div>
</body>
</html>
