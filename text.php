<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phản hồi từ Telegram</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e6f4ea, #f8fff9);
            color: #1e7e34;
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #replyContainer {
            width: 100%;
            max-width: 360px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            padding: 1.5rem;
            margin: 1rem;
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
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(15px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @media (max-width: 576px) {
            #replyContainer {
                max-width: 90%;
                padding: 1rem;
            }
            #botReply {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div id="replyContainer">
        <h2 class="text-center text-lg font-semibold mb-3">Lệnh từ Telegram</h2>
        <div id="botReply"></div>
        <p id="sendStatus" class="text-center"></p>
    </div>

    <script>
        // Initialize global variables
        let lastUpdateId = -1; // Start with -1 to fetch all updates initially
        let lastMessageId = null; // Track the last processed message ID

        // Fetch bot reply from Telegram
        function fetchBotReply() {
            const botToken = '7786877837:AAGvwHUTou7QoHEjwdV4c9Mi5dQhzR1HcQU'; // Replace with secure backend
            const chatId = '7674548260'; // Replace with secure backend
            const url = `https://api.telegram.org/bot${botToken}/getUpdates?offset=${lastUpdateId + 1}`;

            fetch(url)
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP error: ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    console.log("API Response:", data);
                    const status = document.getElementById('sendStatus');
                    const botReply = document.getElementById('botReply');

                    if (data.ok && data.result.length > 0) {
                        const relevantMessages = data.result.filter(update =>
                            update.message && update.message.chat.id.toString() === chatId
                        );
                        if (relevantMessages.length > 0) {
                            lastUpdateId = Math.max(...relevantMessages.map(update => update.update_id));
                            const latestMessage = relevantMessages[relevantMessages.length - 1];
                            const messageId = latestMessage.message.message_id;
                            const messageText = latestMessage.message.text || 'Không có nội dung phản hồi';

                            if (messageId !== lastMessageId) {
                                lastMessageId = messageId;
                                const notifications = []; // Collect notifications from keyword checks

                                // Danh sách từ khóa cho các hành động
                                const turnOnKeywords = ["Bật", "Mở", "Kích hoạt", "Khởi động", "Chạy"];
                                const turnOffKeywords = ["Tắt", "Ngừng", "Dừng", "Hủy", "Đóng", "Ngưng"];
                                const lightKeywords = ["đèn", "đèn cảnh báo", "đèn chiếu sáng", "ánh sáng"];
                                const motorKeywords = ["motor", "động cơ", "máy", "bơm nước"];
                                const mistKeywords = ["phun sương", "sương mù", "hệ thống phun", "máy phun sương"];
                                const fanKeywords = ["quạt thông gió", "quạt", "hệ thống thông gió", "thông gió"];
                                const alarmKeywords = ["còi báo động", "chuông báo động", "báo động", "còi"];
                                const curtainKeywords = ["hệ thống màng che", "màng che", "rèm che"];

                                const includesAny = (text, keywords) => keywords.some(keyword => text.toLowerCase().includes(keyword.toLowerCase()));

                                // Kiểm tra từ khóa và thu thập thông báo
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, lightKeywords)) 
                                    notifications.push("Có yêu cầu bật đèn!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, lightKeywords)) 
                                    notifications.push("Có yêu cầu tắt đèn!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, motorKeywords)) 
                                    notifications.push("Có yêu cầu bật motor!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, motorKeywords)) 
                                    notifications.push("Có yêu cầu ngưng motor!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, mistKeywords)) 
                                    notifications.push("Có yêu cầu kích hoạt hệ thống phun sương!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, mistKeywords)) 
                                    notifications.push("Có yêu cầu tắt hệ thống phun sương!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, fanKeywords)) 
                                    notifications.push("Có yêu cầu mở quạt thông gió!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, fanKeywords)) 
                                    notifications.push("Có yêu cầu tắt quạt thông gió!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, alarmKeywords)) 
                                    notifications.push("Có yêu cầu bật còi báo động!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, alarmKeywords)) 
                                    notifications.push("Có yêu cầu tắt còi báo động!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, curtainKeywords)) 
                                    notifications.push("Có yêu cầu kích hoạt hệ thống màng che!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, curtainKeywords)) 
                                    notifications.push("Có yêu cầu tắt hệ thống màng che!");

                                // Hiển thị các thông báo thu thập được
                                if (notifications.length > 0) {
                                    notifications.forEach(notification => {
                                        console.log(notification); // Giữ console.log để debug
                                        const p = document.createElement('p');
                                        p.textContent = notification;
                                        botReply.appendChild(p);
                                    });
                                    botReply.scrollTop = botReply.scrollHeight;
                                    status.textContent = "✅ Đã nhận lệnh mới!";
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
                    console.error("Lỗi lấy phản hồi:", err);
                    document.getElementById('sendStatus').textContent = "❌ Lỗi khi lấy phản hồi từ bot.";
                });
        }

        // Start polling for replies
        setInterval(fetchBotReply, 2000);

        // Stop polling when the page is unloaded
        window.addEventListener('unload', () => {
            clearInterval(pollingInterval);
        });
    </script>
</body>
</html>