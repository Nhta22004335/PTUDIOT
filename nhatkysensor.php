<?php
require_once "config.php";

try {
    $conn = connectDatabase();
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$params = [];
$where = "WHERE 1";

if ($from && $to) {
    $where .= " AND nk.thoigian BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
}

$stmt = $conn->prepare("SELECT nk.idnk, nk.idcb, cb.tencb, nk.giatricb, nk.thoigian
                        FROM nhatkycb nk
                        LEFT JOIN cambien cb ON nk.idcb = cb.idcb
                        $where
                        ORDER BY nk.thoigian DESC");
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cambienData = [];
foreach ($logs as $log) {
    $cambienData[$log['tencb']][] = [
        'x' => $log['thoigian'],
        'y' => (float)$log['giatricb']
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Cảm Biến</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-image: url('picture/bg.jpg'); /* Thay bằng đường dẫn ảnh của bạn */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(187, 247, 208, 0.5); /* Lớp phủ xanh lá nhạt (green-200/50) */
            z-index: -1;
        }
    </style>
</head>
<body class="bg-green-50 py-10">
    <div class="max-w-6xl mx-auto bg-green-100 p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-4 text-green-900">Lịch Sử Đo Đạt Cảm Biến</h1>
        <a href="trangchu.php" class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-6">Trở về Trang Chủ</a>

        <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end" id="filterForm">
            <div>
                <label class="block text-sm font-medium text-green-800">Từ ngày</label>
                <input type="datetime-local" name="from" value="<?= htmlspecialchars($from) ?>" class="px-3 py-2 border rounded w-full" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-green-800">Đến ngày</label>
                <input type="datetime-local" name="to" value="<?= htmlspecialchars($to) ?>" class="px-3 py-2 border rounded w-full" required>
            </div>
            <div>
                <button id="filterBtn" type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mt-1" disabled>Lọc</button>
            </div>
        </form>

        <div class="overflow-x-auto max-h-[500px] mb-10">
            <table class="w-full text-sm">
                <thead class="bg-green-200 text-green-800 uppercase text-xs">
                    <tr>
                        <th class="py-2 px-4 text-left">ID Nhật Ký</th>
                        <th class="py-2 px-4 text-left">ID Cảm Biến</th>
                        <th class="py-2 px-4 text-left">Tên Cảm Biến</th>
                        <th class="py-2 px-4 text-left">Giá Trị</th>
                        <th class="py-2 px-4 text-left">Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr class="border-b hover:bg-green-50">
                            <td class="py-2 px-4"><?= $log['idnk'] ?></td>
                            <td class="py-2 px-4"><?= $log['idcb'] ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($log['tencb']) ?></td>
                            <td class="py-2 px-4"><?= $log['giatricb'] ?></td>
                            <td class="py-2 px-4"><?= $log['thoigian'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-green-600">Không có dữ liệu phù hợp</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php foreach ($cambienData as $tencb => $data): ?>
            <div class="mb-10">
                <h2 class="text-lg font-semibold mb-2 text-green-900">Biểu đồ: <?= htmlspecialchars($tencb) ?></h2>
                <canvas id="chart-<?= md5($tencb) ?>" height="100"></canvas>
            </div>
            <script>
                const ctx_<?= md5($tencb) ?> = document.getElementById("chart-<?= md5($tencb) ?>").getContext('2d');
                new Chart(ctx_<?= md5($tencb) ?>, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode(array_column($data, 'x')) ?>,
                        datasets: [{
                            label: "Giá trị",
                            data: <?= json_encode(array_column($data, 'y')) ?>,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.2)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { display: true, title: { display: true, text: 'Thời gian' } },
                            y: { display: true, title: { display: true, text: 'Giá trị' } }
                        }
                    }
                });
            </script>
        <?php endforeach; ?>
    </div>
</body>
</html>
<script>
    const fromInput = document.querySelector('input[name="from"]');
    const toInput = document.querySelector('input[name="to"]');
    const filterBtn = document.getElementById('filterBtn');
    const form = document.getElementById('filterForm');

    // Hàm kích hoạt nút nếu đủ dữ liệu
    function toggleButton() {
        filterBtn.disabled = !(fromInput.value && toInput.value);
    }

    // Gắn sự kiện nhập liệu
    fromInput.addEventListener('input', toggleButton);
    toInput.addEventListener('input', toggleButton);

    // Kiểm tra logic khi submit
    form.addEventListener('submit', function (e) {
        const from = new Date(fromInput.value);
        const to = new Date(toInput.value);

        if (from > to) {
            alert('"Ngày bắt đầu" không được lớn hơn "Ngày kết thúc".');
            e.preventDefault();
        }
    });
</script>