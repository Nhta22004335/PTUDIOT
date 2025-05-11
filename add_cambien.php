<?php
require_once "config.php";

try {
    $conn = connectDatabase();
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}

function makeSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[\p{P}\p{S}\p{Z}]/u', '-', $string); // Dấu câu, ký tự đặc biệt và khoảng trắng
    $string = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string));
    $string = preg_replace('/[^a-z0-9-]/', '', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tencb = $_POST['tencb'] ?? '';
    $trangthai = $_POST['trangthai'] ?? '';
    $idvt = $_POST['idvt'] ?? '';
    $anh = $_FILES['anh'] ?? null;

    $id = makeSlug($tencb);

    $uploadPath = 'picture/';
    $imageName = '';

    if ($anh && $anh['error'] == 0) {
        $ext = pathinfo($anh['name'], PATHINFO_EXTENSION);
        $imageName = $id . '.' . $ext;
        move_uploaded_file($anh['tmp_name'], $uploadPath . $imageName);
    }

    $stmt = $conn->prepare("INSERT INTO cambien (id, tencb, trangthai, anh, idvt) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id, $tencb, $trangthai, $imageName, $idvt]);
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
    <title>Thêm Cảm Biến</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-xl mx-auto bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Thêm Cảm Biến</h1>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block mb-1 font-medium">Tên Cảm Biến</label>
                <input type="text" name="tencb" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Trạng Thái</label>
                <input type="text" name="trangthai" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Ảnh</label>
                <input type="file" name="anh" accept="image/*" class="w-full">
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
