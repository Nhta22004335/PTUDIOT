<?php
include "config.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điều Khiển Thiết Bị và Lệnh Telegram</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e6f4ea, #f8fff9);
            color: #1e7e34;
            min-height: 100vh;
            padding: 10px;
            display: flex;
            justify-content: center;
            align-items: center;

            background-image: url('picture/bg.jpg');
            background-size: cover;         /* Phủ toàn bộ màn hình */
            background-position: center;    /* Căn giữa */
            background-repeat: no-repeat;   /* Không lặp lại */
        }
        .main-container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .telegram-container, .control-container {
            flex: 1;
            min-width: 300px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            padding: 1.5rem;
        }
        .telegram-container h2, .control-container h2 {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1e7e34;
        }
        #botReply {
            max-height: 300px;
            overflow-y: auto;
            padding: 0.5rem;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            background: #e6f4ea;
            font-size: 0.85rem;
        }
        #botReply p {
            margin: 0.4rem 0;
            padding: 0.6rem;
            background: #d4edda;
            border-radius: 6px;
            animation: slideIn 0.4s ease;
        }
        #sendStatus {
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
            text-align: center;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }
        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            padding: 15px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-3px);
        }
        .card i {
            font-size: 28px;
            color: #28a745;
            animation: bounce 2s infinite;
        }
        .card span {
            margin: 8px 0;
            font-size: 0.9rem;
            font-weight: 500;
            color: #1e7e34;
            min-height: 30px;
            text-align: center;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .3s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #28a745;
        }
        input:checked + .slider:before {
            transform: translateX(24px);
        }
        #btn-trove {
            background: linear-gradient(45deg, #28a745, #34c759);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin: 1rem auto 0;
        }
        #btn-trove:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.2);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(15px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            .telegram-container, .control-container {
                min-width: 100%;
                padding: 1rem;
            }
            .telegram-container h2, .control-container h2 {
                font-size: 1.25rem;
            }
            #botReply {
                font-size: 0.8rem;
            }
            .card {
                padding: 12px 8px;
            }
            .card i {
                font-size: 24px;
            }
            .card span {
                font-size: 0.85rem;
            }
        }
    </style>
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
                <div class="card">
                    <i class="fas fa-cogs"></i>
                    <span>Motor bể chứa nước</span>
                    <label class="switch">
                        <input type="checkbox" id="motorbechuanuoc">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="card">
                    <i class="fas fa-snowflake"></i>
                    <span>Hệ thống p sương</span>
                    <label class="switch">
                        <input type="checkbox" id="htphunsuong">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="card">
                    <i class="fas fa-fan"></i>
                    <span>Quạt thông gió</span>
                    <label class="switch">
                        <input type="checkbox" id="quatthongio">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="card">
                    <i class="fas fa-sun"></i>
                    <span>HT đèn chiếu sáng</span>
                    <label class="switch">
                        <input type="checkbox" id="htdenchieusang">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="card">
                    <i class="fas fa-lightbulb"></i>
                    <span>HT led cảnh báo</span>
                    <label class="switch">
                        <input type="checkbox" id="htledcanhbao">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="card">
                    <i class="fas fa-bullhorn"></i>
                    <span>Còi báo</span>
                    <label class="switch">
                        <input type="checkbox" id="coibao">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="card">
                    <i class="fas fa-cog"></i>
                    <span>HT màng che</span>
                    <label class="switch">
                        <input type="checkbox" id="htmangche">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            <button onclick="troVe()" id="btn-trove"><i class="fas fa-arrow-left"></i> Trở về</button>
        </div>
    </div>

    <script>
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
        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.error("Mất kết nối MQTT: " + responseObject.errorMessage);
            }
        }

        function onMessageArrived(message) {
            // console.log("Dữ liệu MQTT nhận được: " + message.payloadString);
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

        function getDeviceStates() {
            const stateObj = {};
            const switches = document.querySelectorAll('.switch input');
            switches.forEach(sw => {
                stateObj[sw.id] = sw.checked ? 1 : 0;
            });
            return stateObj;
        }

        function sendDeviceStates() {
            const jsonStates = JSON.stringify(getDeviceStates());
            const message = new Paho.MQTT.Message(jsonStates);
            message.destinationName = "Nta_22004335_nhan";
            client.send(message);
            // console.log("Gửi trạng thái thiết bị: ", jsonStates);
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

                                const turnOnKeywords = ["Bật", "Mở", "Kích hoạt", "Khởi động", "Chạy"];
                                const turnOffKeywords = ["Tắt", "Ngừng", "Dừng", "Hủy", "Đóng", "Ngưng"];
                                const lightKeywords = ["đèn", "đèn cảnh báo", "đèn chiếu sáng", "ánh sáng"];
                                const motorKeywords = ["motor", "động cơ", "máy", "bơm nước"];
                                const mistKeywords = ["phun sương", "sương mù", "hệ thống phun", "máy phun sương"];
                                const fanKeywords = ["quạt thông gió", "quạt", "hệ thống thông gió", "thông gió"];
                                const alarmKeywords = ["còi báo động", "chuông báo động", "báo động", "còi"];
                                const curtainKeywords = ["hệ thống màng che", "màng che", "rèm che"];

                                const includesAny = (text, keywords) => keywords.some(keyword => text.toLowerCase().includes(keyword.toLowerCase()));

                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, lightKeywords)) {
                                    notifications.push("Có yêu cầu bật đèn chiếu sáng!");
                                    deviceStateUpdates["htdenchieusang"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, lightKeywords)) {
                                    notifications.push("Có yêu cầu tắt đèn chiếu sáng!");
                                    deviceStateUpdates["htdenchieusang"] = 0;
                                }
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, motorKeywords)) {
                                    notifications.push("Có yêu cầu bật motor!");
                                    deviceStateUpdates["motorbechuanuoc"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, motorKeywords)) {
                                    notifications.push("Có yêu cầu ngưng motor!");
                                    deviceStateUpdates["motorbechuanuoc"] = 0;
                                }
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, mistKeywords)) {
                                    notifications.push("Có yêu cầu kích hoạt hệ thống phun sương!");
                                    deviceStateUpdates["htphunsuong"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, mistKeywords)) {
                                    notifications.push("Có yêu cầu tắt hệ thống phun sương!");
                                    deviceStateUpdates["htphunsuong"] = 0;
                                }
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, fanKeywords)) {
                                    notifications.push("Có yêu cầu mở quạt thông gió!");
                                    deviceStateUpdates["quatthongio"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, fanKeywords)) {
                                    notifications.push("Có yêu cầu tắt quạt thông gió!");
                                    deviceStateUpdates["quatthongio"] = 0;
                                }
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, alarmKeywords)) {
                                    notifications.push("Có yêu cầu bật còi báo động!");
                                    deviceStateUpdates["coibao"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, alarmKeywords)) {
                                    notifications.push("Có yêu cầu tắt còi báo động!");
                                    deviceStateUpdates["coibao"] = 0;
                                }
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, curtainKeywords)) {
                                    notifications.push("Có yêu cầu kích hoạt hệ thống màng che!");
                                    deviceStateUpdates["htmangche"] = 1;
                                }
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, curtainKeywords)) {
                                    notifications.push("Có yêu cầu tắt hệ thống màng che!");
                                    deviceStateUpdates["htmangche"] = 0;
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