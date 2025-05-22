<?php
// Start session to retrieve $_SESSION['tendn']
session_start();
// Get current time in Asia/Ho_Chi_Minh timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
$currentTime = date('H:i d/m/Y'); // Reflects 01:23 23/05/2025
$reporter = isset($_SESSION['tendn']) ? htmlspecialchars($_SESSION['tendn']) : '[Nhập tên người lập]';

// Include PHPWord (assumes installed via Composer)
require_once 'vendor/autoload.php';
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Table as TableStyle;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistical Report Editor</title>
    <style>
        body {
            margin: 0;
            font-family: 'Times New Roman', Times, serif;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .toolbar {
            background-color: #ffffff;
            border-bottom: 1px solid #d1d1d1;
            padding: 10px;
            display: flex;
            gap: 10px;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .toolbar select, .toolbar button, .editor button {
            padding: 8px 12px;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            background-color: #f9f9f9;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Times New Roman', Times, serif;
        }
        .toolbar select:hover, .toolbar button:hover, .editor button:hover {
            background-color: #e0e0e0;
        }
        .editor {
            flex: 1;
            background-color: #ffffff;
            margin: 20px;
            padding: 20px;
            border: 1px solid #d1d1d1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: auto;
        }
        #editor {
            width: 100%;
            min-height: 100%;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
        }
        #editor h1, #editor h2 {
            margin: 0 0 10px 0;
        }
        #editor p {
            margin: 0 0 10px 0;
        }
        .motto {
            text-align: center;
            font-size: 12pt;
        }
        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        input[type="text"] {
            border: none;
            border-bottom: 1px solid #888;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #888;
            background: transparent;
            outline: none;
            width: 100%;
        }
        input[type="text"]:focus {
            border-bottom: 1px solid #000;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #d1d1d1;
            padding: 8px;
            text-align: left;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
        }
        th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        td input[type="text"] {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <select id="reportType" onchange="changeReportType()">
            <option value="device">Thống kê thiết bị</option>
            <option value="sensor">Thống kê cảm biến</option>
            <option value="sensorHistory">Thống kê lịch sử cảm biến</option>
            <option value="deviceHistory">Thống kê lịch sử thiết bị</option>
        </select>
        <select id="fontSize" onchange="changeFontSize()">
            <option value="10">10</option>
            <option value="12" selected>12</option>
            <option value="14">14</option>
            <option value="16">16</option>
            <option value="18">18</option>
            <option value="24">24</option>
            <option value="36">36</option>
        </select>
        <select id="textAlign" onchange="changeTextAlign()">
            <option value="left">Căn trái</option>
            <option value="center">Căn giữa</option>
            <option value="right">Căn phải</option>
        </select>
        <button onclick="exportToWord()">In báo cáo</button>
        <button onclick="goBack()">Trở về</button>
    </div>
    <div class="editor">
        <div id="editor">
            <p class="motto"><strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong></p>
            <p class="motto">Độc lập - Tự do - Hạnh phúc</p>
            <p class="motto">-----------------</p>
            <p><strong>Tên trường:</strong> Trường Đại học Sư phạm Kỹ thuật Vĩnh Long</p>
            <p><strong>Khoa:</strong> Khoa Công Nghệ Thông Tin</p>
            <h1 style="text-align: center;">THỐNG KÊ THIẾT BỊ</h1>
            <h2>Thông tin chung</h2>
            <p><strong>Thời gian lập báo cáo:</strong> <?php echo $currentTime; ?></p>
            <p><strong>Người lập báo cáo:</strong> <?php echo $reporter; ?></p>
            <h2>Dữ liệu thống kê</h2>
            <button onclick="fetchData()">Xuất</button>
            <table id="dataTable">
                <tr>
                    <th>Mã thiết bị</th>
                    <th>Tên thiết bị</th>
                    <th>Số lượng</th>
                    <th>Trạng thái</th>
                </tr>
                <tr>
                    <td colspan="4"><strong>Ghi chú:</strong> <input type="text" value="" placeholder="Nhập ghi chú hoặc phân tích"></td>
                </tr>
            </table>
            <div class="signature">
                <p><strong>Người lập báo cáo</strong><br>Ký và ghi rõ họ tên</p>
                <p><strong>Người phê duyệt</strong><br>Ký và ghi rõ họ tên</p>
            </div>
        </div>
    </div>
    <script>
        function changeReportType() {
            const reportType = document.getElementById('reportType').value;
            const editor = document.getElementById('editor');
            const title = {
                'device': 'THỐNG KÊ THIẾT BỊ',
                'sensor': 'THỐNG KÊ CẢM BIẾN',
                'sensorHistory': 'THỐNG KÊ LỊCH SỬ CẢM BIẾN',
                'deviceHistory': 'THỐNG KÊ LỊCH SỬ THIẾT BỊ'
            };
            const tableHeaders = {
                'device': `
                    <tr>
                        <th>Mã thiết bị</th>
                        <th>Tên thiết bị</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                    </tr>
                `,
                'sensor': `
                    <tr>
                        <th>Mã cảm biến</th>
                        <th>Loại cảm biến</th>
                        <th>Vị trí</th>
                        <th>Trạng thái</th>
                    </tr>
                `,
                'sensorHistory': `
                    <tr>
                        <th>Mã cảm biến</th>
                        <th>Ngày</th>
                        <th>Giá trị</th>
                        <th>Ghi chú</th>
                    </tr>
                `,
                'deviceHistory': `
                    <tr>
                        <th>Mã thiết bị</th>
                        <th>Ngày</th>
                        <th>Sự kiện bảo trì</th>
                        <th>Ghi chú</th>
                    </tr>
                `
            };
            editor.innerHTML = `
                <p class="motto"><strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong></p>
                <p class="motto">Độc lập - Tự do - Hạnh phúc</p>
                <p class="motto">-----------------</p>
                <p><strong>Tên trường:</strong> Trường Đại học Sư phạm Kỹ thuật Vĩnh Long</p>
                <p><strong>Khoa:</strong> Khoa Công Nghệ Thông Tin</p>
                <h1 style="text-align: center;">${title[reportType]}</h1>
                <h2>Thông tin chung</h2>
                <p><strong>Thời gian lập báo cáo:</strong> <?php echo $currentTime; ?></p>
                <p><strong>Người lập báo cáo:</strong> <?php echo $reporter; ?></p>
                <h2>Dữ liệu thống kê</h2>
                <button onclick="fetchData()">Xuất</button>
                <table id="dataTable">
                    ${tableHeaders[reportType]}
                    <tr>
                        <td colspan="4"><strong>Ghi chú:</strong> <input type="text" value="" placeholder="Nhập ghi chú hoặc phân tích"></td>
                    </tr>
                </table>
                <div class="signature">
                    <p><strong>Người lập báo cáo</strong><br>Ký và ghi rõ họ tên</p>
                    <p><strong>Người phê duyệt</strong><br>Ký và ghi rõ họ tên</p>
                </div>
            `;
        }

        function fetchData() {
            const reportType = document.getElementById('reportType').value;
            fetch('fetch_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'reportType=' + encodeURIComponent(reportType)
            })
            .then(response => response.json())
            .then(data => {
                const table = document.getElementById('dataTable');
                const headers = table.getElementsByTagName('tr')[0]; // Keep headers
                const notesRow = table.getElementsByTagName('tr')[table.rows.length - 1]; // Keep notes row
                // Clear existing data rows
                while (table.rows.length > 2) {
                    table.deleteRow(1);
                }
                // Insert rows for each record
                data.forEach((rowData, index) => {
                    const row = table.insertRow(index + 1);
                    if (reportType === 'device') {
                        row.innerHTML = `
                            <td><input type="text" value="${rowData.idtb || ''}" placeholder="Nhập mã thiết bị"></td>
                            <td><input type="text" value="${rowData.tentb || ''}" placeholder="Nhập tên thiết bị"></td>
                            <td><input type="text" value="" placeholder="Nhập số lượng"></td>
                            <td><input type="text" value="${rowData.trangthai || ''}" placeholder="Nhập trạng thái"></td>
                        `;
                    } else if (reportType === 'sensor') {
                        row.innerHTML = `
                            <td><input type="text" value="${rowData.idcb || ''}" placeholder="Nhập mã cảm biến"></td>
                            <td><input type="text" value="${rowData.tencb || ''}" placeholder="Nhập loại cảm biến"></td>
                            <td><input type="text" value="${rowData.idvt || ''}" placeholder="Nhập vị trí"></td>
                            <td><input type="text" value="${rowData.trangthai || ''}" placeholder="Nhập trạng thái"></td>
                        `;
                    } else if (reportType === 'sensorHistory') {
                        row.innerHTML = `
                            <td><input type="text" value="${rowData.idcb || ''}" placeholder="Nhập mã cảm biến"></td>
                            <td><input type="text" value="${rowData.thoigian || ''}" placeholder="Nhập ngày"></td>
                            <td><input type="text" value="${rowData.giatricb || ''}" placeholder="Nhập giá trị"></td>
                            <td><input type="text" value="" placeholder="Nhập ghi chú"></td>
                        `;
                    } else if (reportType === 'deviceHistory') {
                        row.innerHTML = `
                            <td><input type="text" value="${rowData.idtb || ''}" placeholder="Nhập mã thiết bị"></td>
                            <td><input type="text" value="${rowData.thoigian || ''}" placeholder="Nhập ngày"></td>
                            <td><input type="text" value="${rowData.trangthai || ''}" placeholder="Nhập sự kiện"></td>
                            <td><input type="text" value="" placeholder="Nhập ghi chú"></td>
                        `;
                    }
                });
                // Re-append notes row
                table.appendChild(notesRow);
                notesRow.getElementsByTagName('input')[0].value = '';
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                alert('Lỗi khi tải dữ liệu từ cơ sở dữ liệu.');
            });
        }

        function exportToWord() {
            const reportType = document.getElementById('reportType').value;
            const titleMap = {
                'device': 'THỐNG KÊ THIẾT BỊ',
                'sensor': 'THỐNG KÊ CẢM BIẾN',
                'sensorHistory': 'THỐNG KÊ LỊCH SỬ CẢM BIẾN',
                'deviceHistory': 'THỐNG KÊ LỊCH SỬ THIẾT BỊ'
            };
            const tableHeaders = {
                'device': ['Mã thiết bị', 'Tên thiết bị', 'Số lượng', 'Trạng thái'],
                'sensor': ['Mã cảm biến', 'Loại cảm biến', 'Vị trí', 'Trạng thái'],
                'sensorHistory': ['Mã cảm biến', 'Ngày', 'Giá trị', 'Ghi chú'],
                'deviceHistory': ['Mã thiết bị', 'Ngày', 'Sự kiện bảo trì', 'Ghi chú']
            };

            // Get table data
            const table = document.getElementById('dataTable');
            const rows = table.getElementsByTagName('tr');
            const tableData = [];
            for (let i = 1; i < rows.length - 1; i++) { // Skip header and notes row
                const inputs = rows[i].getElementsByTagName('input');
                const rowData = Array.from(inputs).map(input => input.value);
                tableData.push(rowData);
            }
            const notesInput = rows[rows.length - 1].getElementsByTagName('input')[0].value;

            // Send data to server for Word export
            fetch('', { // Self-reference to current script
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'exportWord=1' +
                    '&reportType=' + encodeURIComponent(reportType) +
                    '&title=' + encodeURIComponent(titleMap[reportType]) +
                    '&tableHeaders=' + encodeURIComponent(JSON.stringify(tableHeaders[reportType])) +
                    '&tableData=' + encodeURIComponent(JSON.stringify(tableData)) +
                    '&notes=' + encodeURIComponent(notesInput) +
                    '&time=<?php echo urlencode($currentTime); ?>' +
                    '&reporter=<?php echo urlencode($reporter); ?>'
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `BaoCao_${reportType}_${Date.now()}.docx`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Error exporting to Word:', error);
                alert('Lỗi khi xuất báo cáo ra file Word.');
            });
        }

        function changeFontSize() {
            const size = document.getElementById('fontSize').value;
            const inputs = document.querySelectorAll('#editor input');
            inputs.forEach(input => {
                input.style.fontSize = size + 'pt';
            });
        }

        function changeTextAlign() {
            const align = document.getElementById('textAlign').value;
            document.getElementById('editor').style.textAlign = align;
        }

        function goBack() {
            window.history.back();
        }

        <?php
        // Handle Word export on server side
        if (isset($_POST['exportWord'])) {
            $reportType = $_POST['reportType'];
            $title = $_POST['title'];
            $tableHeaders = json_decode($_POST['tableHeaders'], true);
            $tableData = json_decode($_POST['tableData'], true);
            $notes = $_POST['notes'];
            $time = $_POST['time'];
            $reporter = $_POST['reporter'];

            try {
                // Create new PHPWord object
                $phpWord = new PhpWord();
                $section = $phpWord->addSection();

                // Define styles
                $fontStyle = ['name' => 'Times New Roman', 'size' => 12];
                $boldFontStyle = ['name' => 'Times New Roman', 'size' => 12, 'bold' => true];
                $centerStyle = ['alignment' => 'center'];
                $tableStyle = [
                    'borderSize' => 6,
                    'borderColor' => '999999',
                    'cellMargin' => 80,
                    'alignment' => 'left'
                ];
                $phpWord->addTableStyle('ReportTable', $tableStyle);

                // Add motto
                $section->addText('CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', $boldFontStyle, $centerStyle);
                $section->addText('Độc lập - Tự do - Hạnh phúc', $fontStyle, $centerStyle);
                $section->addText('-----------------', $fontStyle, $centerStyle);

                // Add school and faculty
                $section->addText('Tên trường: Trường Đại học Sư phạm Kỹ thuật Vĩnh Long', $fontStyle);
                $section->addText('Khoa: Khoa Công Nghệ Thông Tin', $fontStyle);
                $section->addTextBreak(1);

                // Add title
                $section->addText($title, ['name' => 'Times New Roman', 'size' => 16, 'bold' => true], $centerStyle);
                $section->addTextBreak(1);

                // Add general info
                $section->addText('Thông tin chung', ['name' => 'Times New Roman', 'size' => 14, 'bold' => true]);
                $section->addText("Thời gian lập báo cáo: $time", $fontStyle);
                $section->addText("Người lập báo cáo: $reporter", $fontStyle);
                $section->addTextBreak(1);

                // Add data section
                $section->addText('Dữ liệu thống kê', ['name' => 'Times New Roman', 'size' => 14, 'bold' => true]);

                // Add table
                $table = $section->addTable('ReportTable');
                // Add header row
                $table->addRow();
                foreach ($tableHeaders as $header) {
                    $cell = $table->addCell(2500, ['bgColor' => 'F9F9F9']);
                    $cell->addText($header, $boldFontStyle);
                }
                // Add data rows
                foreach ($tableData as $rowData) {
                    $table->addRow();
                    foreach ($rowData as $value) {
                        $table->addCell(2500)->addText($value, $fontStyle);
                    }
                }
                // Add notes row
                if (!empty($notes)) {
                    $table->addRow();
                    $cell = $table->addCell(10000, ['gridSpan' => 4]);
                    $cell->addText("Ghi chú: $notes", $fontStyle);
                }

                // Add signature section
                $section->addTextBreak(2);
                $table = $section->addTable(['unit' => 'pct', 'width' => 100]);
                $table->addRow();
                $cell1 = $table->addCell(5000);
                $cell2 = $table->addCell(5000);
                $cell1->addText('Người lập báo cáo', $boldFontStyle);
                $cell1->addText('Ký và ghi rõ họ tên', $fontStyle);
                $cell2->addText('Người phê duyệt', $boldFontStyle, ['alignment' => 'right']);
                $cell2->addText('Ký và ghi rõ họ tên', $fontStyle, ['alignment' => 'right']);

                // Save to temporary file
                $tempFile = tempnam(sys_get_temp_dir(), 'Report') . '.docx';
                $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                $writer->save($tempFile);

                // Send file to client with proper headers
                if (file_exists($tempFile)) {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                    header('Content-Disposition: attachment; filename="BaoCao_' . $reportType . '_' . time() . '.docx"');
                    header('Content-Length: ' . filesize($tempFile));
                    header('Cache-Control: max-age=0');
                    readfile($tempFile);
                    unlink($tempFile); // Clean up
                } else {
                    throw new Exception('Failed to create temporary file.');
                }
            } catch (Exception $e) {
                // Log error for debugging
                error_log("Word export error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Lỗi khi tạo file Word: ' . $e->getMessage()]);
                exit;
            }
            exit;
        }
        ?>
    </script>
</body>
</html>