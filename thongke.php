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
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-6xl mx-auto bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Lịch Sử Đo Đạt Cảm Biến</h1>

        <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium">Từ ngày</label>
                <input type="datetime-local" name="from" value="<?= htmlspecialchars($from) ?>" class="px-3 py-2 border rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">Đến ngày</label>
                <input type="datetime-local" name="to" value="<?= htmlspecialchars($to) ?>" class="px-3 py-2 border rounded w-full">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-1">Lọc</button>
            </div>
        </form>

        <div class="overflow-x-auto max-h-[500px] mb-10">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
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
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4"><?= $log['idnk'] ?></td>
                            <td class="py-2 px-4"><?= $log['idcb'] ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($log['tencb']) ?></td>
                            <td class="py-2 px-4"><?= $log['giatricb'] ?></td>
                            <td class="py-2 px-4"><?= $log['thoigian'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-gray-500">Không có dữ liệu phù hợp</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php foreach ($cambienData as $tencb => $data): ?>
            <div class="mb-10">
                <h2 class="text-lg font-semibold mb-2">Biểu đồ: <?= htmlspecialchars($tencb) ?></h2>
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
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
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
