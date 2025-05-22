<?php
require_once "config.php"; // Kết nối CSDL
try {
    $conn = connectDatabase();
    $stmt = $conn->query("SELECT `idtb`, `id`, icon, `tentb`, `trangthai` FROM `thietbi`");
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy trạng thái mới nhất từ nhatkytb
    $stmt = $conn->query("
        SELECT idnk, idtb, trangthai, thoigian
        FROM nhatkytb
        WHERE (idtb, thoigian) IN (
            SELECT idtb, MAX(thoigian)
            FROM nhatkytb
            GROUP BY idtb
        )
    ");
    $deviceStates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Tạo mảng trạng thái để truyền sang JavaScript
    $stateMap = [];
    foreach ($deviceStates as $state) {
        $stateMap[$state['idtb']] = $state['trangthai'];
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="dieukhien.css?v=<?= time() ?>">
</head>
<body>
    <div class="main-container">
        <!-- Telegram Commands -->
        <div class="telegram-container">
            <h2>Lệnh từ Telegram</h2>
            <div id="botReply"></div>
            <p id="sendStatus" class="text-center"></p>
        </div>
        <!-- Device Controls -->
        <div class="control-container">
            <h2>Điều Khiển Thiết Bị</h2>
            <div class="grid">
                <?php foreach ($devices as $device): ?>
                    <?php if ($device["trangthai"]=="on"): ?>
                        <div class="card">
                            <i class="<?= $device["icon"] ?>"></i>
                            <span><?= $device["tentb"] ?></span>
                            <label class="switch">
                                <input type="checkbox" id="<?= $device["id"] ?>" data-idtb="<?= $device["idtb"] ?>">
                                <span class="slider"></span>
                            </label>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <button onclick="troVe()" id="btn-trove"><i class="fas fa-arrow-left"></i> Trở về</button>
        </div>
    </div>

    <script>
        // Dữ liệu trạng thái từ PHP
        const deviceStates = <?php echo json_encode($stateMap); ?>;

        // Cập nhật trạng thái slider từ nhatkytb
        function updateSwitchStatesFromDB() {
            const switches = document.querySelectorAll('.switch input');
            switches.forEach(sw => {
                const idtb = sw.getAttribute('data-idtb');
                if (idtb && deviceStates[idtb] !== undefined) {
                    sw.checked = deviceStates[idtb] === 'on';
                    console.log(`Cập nhật slider idtb=${idtb}, trạng thái=${deviceStates[idtb]}`);
                }
            });
        }

        // Gọi khi trang load
        document.addEventListener('DOMContentLoaded', () => {
            updateSwitchStatesFromDB();
        });
        
        // Telegram logic
        let lastUpdateId = localStorage.getItem('lastUpdateId') ? parseInt(localStorage.getItem('lastUpdateId')) : -1;
        let lastMessageId = null;

        // Kiểm tra và vô hiệu hóa webhook
        function checkAndDisableWebhook() {
            const botToken = '7786877837:AAGvwHUTou7QoHEjwdV4c9Mi5dQhzR1HcQU';
            const getWebhookUrl = `https://api.telegram.org/bot${botToken}/getWebhookInfo`;
            fetch(getWebhookUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.ok && data.result.url) {
                        console.log("Webhook hiện tại:", data.result.url);
                        const setWebhookUrl = `https://api.telegram.org/bot${botToken}/setWebhook?url=`;
                        return fetch(setWebhookUrl);
                    } else {
                        console.log("Không có webhook hoặc đã vô hiệu hóa.");
                    }
                })
                .then(res => {
                    if (res) return res.json();
                })
                .then(data => {
                    if (data && data.ok) {
                        console.log("Webhook đã được vô hiệu hóa!");
                    }
                })
                .catch(err => console.error("Lỗi kiểm tra/vô hiệu hóa webhook:", err));
        }
        checkAndDisableWebhook();

        // MQTT logic
        const client = new Paho.MQTT.Client("broker.emqx.io", 8083, "clientId_22004335");
        client.onConnectionLost = onConnectionLost;
        client.onMessageArrived = onMessageArrived;

        client.connect({ onSuccess: onConnect });

        function onConnect() {
            console.log("Kết nối MQTT thành công!");
            client.subscribe("Nta_22004335_gui");
            client.subscribe("Nta_22004335_nhan");
            sendDeviceStatesfirst(); 
        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.error("Mất kết nối MQTT: " + responseObject.errorMessage);
            }
        }

        function onMessageArrived(message) {
            console.log("Dữ liệu MQTT nhận được: " + message.payloadString);
            try {
                const data = JSON.parse(message.payloadString);
                updateSwitchStates(data);
            } catch (e) {
                console.error("Lỗi phân tích JSON MQTT: ", e);
            }
        }

        function updateSwitchStates(data) {
            const switches = document.querySelectorAll('.switch input');
            switches.forEach(sw => {
                if (data[sw.id] !== undefined) {
                    sw.checked = data[sw.id] === 1;
                }
            });
        }
        function saveSwitchState(idtb, state) {
            $.ajax({
                url: 'luuttdieukhien.php',
                type: 'POST',
                data: {
                    idtb: idtb,
                    trangthai: state
                },
                success: function(response) {
                    console.log('Lưu trạng thái thành công:', response);
                },
                error: function(xhr, status, error) {
                    console.error('Lỗi lưu trạng thái:', error);
                }
            });
        }
        function getDeviceStates() {
            const stateObj = {};
            const switches = document.querySelectorAll('.switch input');
            switches.forEach(sw => {
                stateObj[sw.id] = sw.checked ? 1 : 0;
            });
            return stateObj;
        }

        function sendDeviceStatesfirst() {
            const jsonStates = JSON.stringify(getDeviceStates());
            const message = new Paho.MQTT.Message(jsonStates);
            message.destinationName = "Nta_22004335_nhan";
            client.send(message);
            console.log("Gửi trạng thái thiết bị: ", jsonStates);
        }

        function sendDeviceStates() {
            const jsonStates = JSON.stringify(getDeviceStates());
            const message = new Paho.MQTT.Message(jsonStates);
            message.destinationName = "Nta_22004335_nhan";
            client.send(message);
            console.log("Gửi trạng thái thiết bị: ", jsonStates);

            // Lưu trạng thái vào CSDL
            const switches = document.querySelectorAll('.switch input');
            switches.forEach(sw => {
                const idtb = sw.getAttribute('data-idtb');
                const state = sw.checked ? "on" : "off";
                saveSwitchState(idtb, state);
            });
        }

        const switches = document.querySelectorAll('.switch input');
        switches.forEach(switchInput => {
            switchInput.addEventListener('change', sendDeviceStates);
        });

        function fetchBotReply() {
            const botToken = '7786877837:AAGvwHUTou7QoHEjwdV4c9Mi5dQhzR1HcQU';
            const chatId = '7674548260';
            const url = `https://api.telegram.org/bot${botToken}/getUpdates?offset=${lastUpdateId + 1}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    const status = document.getElementById('sendStatus');
                    const botReply = document.getElementById('botReply');

                    if (!data.ok) {
                        throw new Error(`Lỗi Telegram: ${data.description} (Mã: ${data.error_code})`);
                    }

                    if (data.result.length > 0) {
                        const relevantMessages = data.result.filter(update =>
                            update.message && update.message.chat.id.toString() === chatId
                        );
                        if (relevantMessages.length > 0) {
                            lastUpdateId = Math.max(...relevantMessages.map(update => update.update_id));
                            localStorage.setItem('lastUpdateId', lastUpdateId);
                            const latestMessage = relevantMessages[relevantMessages.length - 1];
                            const messageId = latestMessage.message.message_id;
                            const messageText = latestMessage.message.text || 'Không có nội dung phản hồi';

                            if (messageId !== lastMessageId) {
                                lastMessageId = messageId;
                                const notifications = [];
                                const deviceStateUpdates = {};

                                // Expanded keyword arrays with refined lightKeywords to avoid overlap
                                const turnOnKeywords = [
                                    "Bật", "Mở", "Kích hoạt", "Khởi động", "Chạy", 
                                    "Bật lên", "Mở lên", "Tăng", "Hoạt động", "Vận hành", 
                                    "Bật thiết bị", "Mở thiết bị", "Khởi chạy", "Kích hoạt hệ thống"
                                ];
                                const turnOffKeywords = [
                                    "Tắt", "Ngừng", "Dừng", "Hủy", "Đóng", "Ngưng", 
                                    "Tắt đi", "Ngắt", "Hủy bỏ", "Khóa", "Tạm dừng", 
                                    "Tắt thiết bị", "Đóng hệ thống", "Ngưng hoạt động"
                                ];
                                const lightKeywords = [
                                    "đèn chiếu sáng", "ánh sáng", "chiếu sáng", 
                                    "đèn sáng", "hệ thống đèn chiếu sáng", "đèn điện chiếu sáng", 
                                    "đèn led chiếu sáng", "ánh đèn chiếu sáng", "đèn ngoài chiếu sáng", 
                                    "đèn trong chiếu sáng", "hệ thống chiếu sáng"
                                ];
                                const motorKeywords = [
                                    "motor", "động cơ", "máy", "bơm nước", "máy bơm", 
                                    "động cơ nước", "hệ thống bơm", "máy nước", "motor nước", 
                                    "bơm", "động cơ điện"
                                ];
                                const mistKeywords = [
                                    "phun sương", "sương mù", "hệ thống phun", "máy phun sương", 
                                    "phun nước", "tạo sương", "hệ thống sương", "máy sương", 
                                    "phun hơi nước", "sương"
                                ];
                                const fanKeywords = [
                                    "quạt thông gió", "quạt", "hệ thống thông gió", "thông gió", 
                                    "quạt gió", "máy quạt", "quạt điện", "hệ thống quạt", 
                                    "quạt làm mát", "quạt không khí"
                                ];
                                const alarmKeywords = [
                                    "còi báo động", "chuông báo động", "báo động", "còi", 
                                    "chuông cảnh báo", "hệ thống báo động", "còi kêu", 
                                    "báo hiệu", "cảnh báo âm thanh", "chuông kêu"
                                ];
                                const curtainKeywords = [
                                    "hệ thống màng che", "màng che", "rèm che", "màn che", 
                                    "hệ thống rèm", "che phủ", "màng phủ", "rèm", 
                                    "hệ thống che", "màn phủ"
                                ];
                                const ledWarningKeywords = [
                                    "đèn cảnh báo", "đèn led cảnh báo", "led cảnh báo", 
                                    "đèn báo động", "đèn tín hiệu", "đèn báo", "đèn led báo", 
                                    "hệ thống cảnh báo ánh sáng", "đèn hiệu", "đèn báo nguy hiểm"
                                ];

                                const includesAny = (text, keywords) => keywords.some(keyword => text.toLowerCase().includes(keyword.toLowerCase()));

                                // Light system (đèn chiếu sáng)
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, lightKeywords)) {
                                    notifications.push("Có yêu cầu bật đèn chiếu sáng!");
                                    deviceStateUpdates["htdenchieusang"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, lightKeywords)) {
                                    notifications.push("Có yêu cầu tắt đèn chiếu sáng!");
                                    deviceStateUpdates["htdenchieusang"] = 0;
                                }

                                // Motor system (motor bơm nước)
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, motorKeywords)) {
                                    notifications.push("Có yêu cầu bật motor bơm nước!");
                                    deviceStateUpdates["motorbechuanuoc"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, motorKeywords)) {
                                    notifications.push("Có yêu cầu ngưng motor bơm nước!");
                                    deviceStateUpdates["motorbechuanuoc"] = 0;
                                }

                                // Mist system (hệ thống phun sương)
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, mistKeywords)) {
                                    notifications.push("Có yêu cầu kích hoạt hệ thống phun sương!");
                                    deviceStateUpdates["htphunsuong"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, mistKeywords)) {
                                    notifications.push("Có yêu cầu tắt hệ thống phun sương!");
                                    deviceStateUpdates["htphunsuong"] = 0;
                                }

                                // Fan system (quạt thông gió)
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, fanKeywords)) {
                                    notifications.push("Có yêu cầu mở quạt thông gió!");
                                    deviceStateUpdates["quatthongio"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, fanKeywords)) {
                                    notifications.push("Có yêu cầu tắt quạt thông gió!");
                                    deviceStateUpdates["quatthongio"] = 0;
                                }

                                // Alarm system (còi báo động)
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, alarmKeywords)) {
                                    notifications.push("Có yêu cầu bật còi báo động!");
                                    deviceStateUpdates["coibao"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, alarmKeywords)) {
                                    notifications.push("Có yêu cầu tắt còi báo động!");
                                    deviceStateUpdates["coibao"] = 0;
                                }

                                // Curtain system (hệ thống màng che)
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, curtainKeywords)) {
                                    notifications.push("Có yêu cầu kích hoạt hệ thống màng che!");
                                    deviceStateUpdates["htmangche"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, curtainKeywords)) {
                                    notifications.push("Có yêu cầu tắt hệ thống màng che!");
                                    deviceStateUpdates["htmangche"] = 0;
                                }

                                // LED warning system (đèn LED cảnh báo)
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, ledWarningKeywords)) {
                                    notifications.push("Có yêu cầu bật đèn LED cảnh báo!");
                                    deviceStateUpdates["htledcanhbao"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, ledWarningKeywords)) {
                                    notifications.push("Có yêu cầu tắt đèn LED cảnh báo!");
                                    deviceStateUpdates["htledcanhbao"] = 0;
                                }

                                if (notifications.length > 0) {
                                    notifications.forEach(notification => {
                                        console.log(notification);
                                        const p = document.createElement('p');
                                        p.textContent = notification;
                                        botReply.appendChild(p);
                                    });
                                    botReply.scrollTop = botReply.scrollHeight;
                                    status.textContent = "✅ Đã nhận lệnh mới!";

                                    updateSwitchStates(deviceStateUpdates);
                                    sendDeviceStates();
                                } else {
                                    status.textContent = "⚠️ Không nhận diện được lệnh.";
                                }
                            }
                        } else {
                            status.textContent = "⚠️ Đang chờ lệnh mới...";
                        }
                    } else {
                        status.textContent = "⚠️ Đang chờ lệnh mới...";
                    }
                })
                .catch(err => {
                    console.error("Lỗi lấy phản hồi:", err.message);
                    document.getElementById('sendStatus').textContent = `❌ Lỗi: ${err.message}`;
                });
        }

        let isPolling = false;
        function startPolling() {
            if (!isPolling) {
                isPolling = true;
                setInterval(fetchBotReply, 2000); 
            }
        }
        startPolling();

        window.addEventListener('unload', () => {
            clearInterval(pollingInterval);
        });

        function troVe() {
            window.location.href = "trangchu.php";
        }
    </script>
</body>
</html>