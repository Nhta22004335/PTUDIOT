<?php
/**
 * IoT System Management Dashboard - Redesigned UI with Icons & Styled Buttons
 * Version 2.1
 */

require_once "config.php";

try {
    $conn = connectDatabase();
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });</script>
</head>
<body class="min-h-screen text-green-800">
    <div class="container mx-auto px-4 py-4 relative z-10">
        <header class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-green-900">Dashboard Quản Lý IoT</h1>
            <p class="text-green-600 mt-2">Giám sát thiết bị, cảm biến và khu vực dễ dàng</p>
        </header>
        <div class="mb-6">
            <a href="trangchu.php" class="inline-flex items-center text-sm bg-green-200 hover:bg-green-300 text-green-800 px-4 py-2 rounded">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Quay về Trang chính
            </a>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Cảm Biến -->
            <section class="bg-green-100/90 rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold flex items-center text-green-900"><i data-lucide="activity" class="w-5 h-5 mr-2"></i> Cảm Biến</h2>
                    <a href="add_cambien.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center text-sm font-medium">
                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Thêm mới
                    </a>
                </div>
                <div class="overflow-x-auto max-h-[300px]">
                    <table class="w-full text-sm">
                        <thead class="bg-green-200 text-green-800 uppercase text-xs">
                            <tr>
                                <th class="py-2 px-4 text-left">ID</th>
                                <th class="py-2 px-4 text-left">Tên</th>
                                <th class="py-2 px-4 text-left">Trạng thái</th>
                                <th class="py-2 px-4 text-left">Thời gian</th>
                                <th class="py-2 px-4 text-left">Khu vực</th>
                                <th class="py-2 px-4 text-left">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("SELECT cb.idcb, cb.tencb, cb.trangthai, cb.thoigian, kv.tenkv FROM cambien cb LEFT JOIN khuvuc kv ON cb.idvt = kv.idkv");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr class='border-b hover:bg-green-50'>
                                        <td class='py-2 px-4'>{$row['idcb']}</td>
                                        <td class='py-2 px-4'>{$row['tencb']}</td>
                                        <td class='py-2 px-4'>{$row['trangthai']}</td>
                                        <td class='py-2 px-4'>{$row['thoigian']}</td>
                                        <td class='py-2 px-4'>{$row['tenkv']}</td>
                                        <td class='py-2 px-4 space-x-2'>
                                            <a href='update_cambien.php?id={$row['idcb']}' class='inline-flex items-center text-green-600 hover:text-green-800'>
                                                <i data-lucide='edit-3' class='w-4 h-4 mr-1'></i> Cập nhật
                                            </a>
                                            <a href='delete_cambien.php?id={$row['idcb']}' class='inline-flex items-center text-green-700 hover:text-green-900'>
                                                <i data-lucide='trash-2' class='w-4 h-4 mr-1'></i> Xóa
                                            </a>
                                        </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Thiết Bị -->
            <section class="bg-green-100/90 rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold flex items-center text-green-900"><i data-lucide="cpu" class="w-5 h-5 mr-2"></i> Thiết Bị</h2>
                    <a href="add_thietbi.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center text-sm font-medium">
                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Thêm mới
                    </a>
                </div>
                <div class="overflow-x-auto max-h-[300px]">
                    <table class="w-full text-sm">
                        <thead class="bg-green-200 text-green-800 uppercase text-xs">
                            <tr>
                                <th class="py-2 px-4 text-left">ID</th>
                                <th class="py-2 px-4 text-left">Tên</th>
                                <th class="py-2 px-4 text-left">Trạng thái</th>
                                <th class="py-2 px-4 text-left">Thời gian</th>
                                <th class="py-2 px-4 text-left">Khu vực</th>
                                <th class="py-2 px-4 text-left">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("SELECT tb.idtb, tb.tentb, tb.trangthai, tb.thoigian, kv.tenkv FROM thietbi tb LEFT JOIN khuvuc kv ON tb.idvt = kv.idkv");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr class='border-b hover:bg-green-50'>
                                        <td class='py-2 px-4'>{$row['idtb']}</td>
                                        <td class='py-2 px-4'>{$row['tentb']}</td>
                                        <td class='py-2 px-4'>{$row['trangthai']}</td>
                                        <td class='py-2 px-4'>{$row['thoigian']}</td>
                                        <td class='py-2 px-4'>{$row['tenkv']}</td>
                                        <td class='py-2 px-4 space-x-2'>
                                            <a href='update_thietbi.php?id={$row['idtb']}' class='inline-flex items-center text-green-600 hover:text-green-800'>
                                                <i data-lucide='edit-3' class='w-4 h-4 mr-1'></i> Cập nhật
                                            </a>
                                            <a href='delete_thietbi.php?id={$row['idtb']}' class='inline-flex items-center text-green-700 hover:text-green-900'>
                                                <i data-lucide='trash-2' class='w-4 h-4 mr-1'></i> Xóa
                                            </a>
                                        </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Khu Vực -->
            <section class="bg-green-100/90 rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold flex items-center text-green-900"><i data-lucide="map-pin" class="w-5 h-5 mr-2"></i> Khu Vực</h2>
                    <a href="add_khuvuc.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center text-sm font-medium">
                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Thêm mới
                    </a>
                </div>
                <div class="overflow-x-auto max-h-[300px]">
                    <table class="w-full text-sm">
                        <thead class="bg-green-200 text-green-800 uppercase text-xs">
                            <tr>
                                <th class="py-2 px-4 text-left">ID</th>
                                <th class="py-2 px-4 text-left">Tên khu</th>
                                <th class="py-2 px-4 text-left">Mô tả</th>
                                <th class="py-2 px-4 text-left">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("SELECT idkv, tenkv, mota FROM khuvuc");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr class='border-b hover:bg-green-50'>
                                        <td class='py-2 px-4'>{$row['idkv']}</td>
                                        <td class='py-2 px-4'>{$row['tenkv']}</td>
                                        <td class='py-2 px-4'>{$row['mota']}</td>
                                        <td class='py-2 px-4 space-x-2'>
                                            <a href='update_khuvuc.php?id={$row['idkv']}' class='inline-flex items-center text-green-600 hover:text-green-800'>
                                                <i data-lucide='edit-3' class='w-4 h-4 mr-1'></i> Cập nhật
                                            </a>
                                            <a href='delete_khuvuc.php?id={$row['idkv']}' class='inline-flex items-center text-green-700 hover:text-green-900'>
                                                <i data-lucide='trash-2' class='w-4 h-4 mr-1'></i> Xóa
                                            </a>
                                        </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</body>
</html>