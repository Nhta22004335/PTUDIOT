<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <style>
        #supportPopup {
            margin: auto;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div id="supportPopup" class="block w-80 bg-white shadow-xl rounded-lg p-4 z-50">
        <form id="supportForm" class="text-sm">
            <input type="text" id="contactInfo" placeholder="Nhập email hoặc số điện thoại..." required class="w-full p-2 border rounded mb-2">
            <textarea id="supportMessage" placeholder="Nhập câu hỏi hoặc yêu cầu của bạn..." required class="w-full p-2 border rounded mb-2"></textarea>
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 w-full">Gửi</button>
            <p id="sendStatus" class="mt-2 text-sm font-semibold"></p>
            <div id="botReply"></div>
        </form>
    </div>

    <script>
        // Initialize global variables
        let lastUpdateId = -1; // Start with -1 to fetch all updates initially
        let lastMessageId = null; // Track the last processed message ID
        let pollingInterval = null; // Store the polling interval
        const maxPollingAttempts = 30; // Stop polling after ~150 seconds (30 * 5s)
        let pollingCount = 0;

        // Validate email or phone number
        function isValidContactInfo(contactInfo) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^\+?\d{10,15}$/;
            return emailRegex.test(contactInfo) || phoneRegex.test(contactInfo);
        }

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
                            const contactInfo = document.getElementById('contactInfo').value.trim();

                            let repliedText = '';
                            if (latestMessage.message.reply_to_message && latestMessage.message.reply_to_message.text) {
                                repliedText = latestMessage.message.reply_to_message.text;
                                console.log("Bot đang trả lời nội dung:", repliedText);
                            }

                            
                            if (messageId !== lastMessageId) {
                                lastMessageId = messageId;
                                botReply.textContent = `Phản hồi từ bot: ${messageText}`;
                                botReply.scrollTop = botReply.scrollHeight;
                                status.textContent = "✅ Đã nhận phản hồi từ bot!";
                                // Stop polling after receiving a reply
                                clearInterval(pollingInterval);
                                pollingInterval = null;

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

                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, lightKeywords)) 
                                    console.log("Có yêu cầu bật đèn!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, lightKeywords)) 
                                    console.log("Có yêu cầu tắt đèn!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, motorKeywords)) 
                                    console.log("Có yêu cầu bật motor!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, motorKeywords)) 
                                    console.log("Có yêu cầu ngưng motor!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, mistKeywords)) 
                                    console.log("Có yêu cầu kích hoạt hệ thống phun sương!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, mistKeywords)) 
                                    console.log("Có yêu cầu tắt hệ thống phun sương!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, fanKeywords)) 
                                    console.log("Có yêu cầu mở quạt thông gió!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, fanKeywords)) 
                                    console.log("Có yêu cầu tắt quạt thông gió!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, alarmKeywords)) 
                                    console.log("Có yêu cầu bật còi báo động!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, alarmKeywords)) 
                                    console.log("Có yêu cầu tắt còi báo động!");
                                if (includesAny(messageText, turnOnKeywords) && includesAny(messageText, curtainKeywords)) 
                                    console.log("Có yêu cầu kích hoạt hệ thống màng che!");
                                if (includesAny(messageText, turnOffKeywords) && includesAny(messageText, curtainKeywords)) 
                                    console.log("Có yêu cầu tắt hệ thống màng che!");
                                                            }
                            
                        } else {
                            status.textContent = "⚠️ Đang chờ phản hồi từ bot...";
                        }
                    } else {
                        status.textContent = "⚠️ Đang chờ phản hồi từ bot...";
                    }

                    // Stop polling after max attempts
                    pollingCount++;
                    if (pollingCount >= maxPollingAttempts) {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                        status.textContent = "⏳ Hết thời gian chờ. Vui lòng thử lại sau.";
                    }
                })
                .catch(err => {
                    console.error("Lỗi lấy phản hồi:", err);
                    document.getElementById('sendStatus').textContent = "❌ Lỗi khi lấy phản hồi từ bot.";
                });
        }

        // Handle form submission
        document.getElementById('supportForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const contactInfo = document.getElementById('contactInfo').value.trim();
            const message = document.getElementById('supportMessage').value.trim();
            const status = document.getElementById('sendStatus');

            // Validate contact info
            if (!isValidContactInfo(contactInfo)) {
                status.textContent = "❌ Vui lòng nhập email hoặc số điện thoại hợp lệ.";
                return;
            }

            const botToken = '7786877837:AAGvwHUTou7QoHEjwdV4c9Mi5dQhzR1HcQU'; // Replace with secure backend
            const chatId = '7674548260'; // Replace with secure backend
            const text = encodeURIComponent(`📩 Yêu cầu hỗ trợ mới:\n📞 Liên hệ: ${contactInfo}\n💬 Nội dung: ${message}`);
            const url = `https://api.telegram.org/bot${botToken}/sendMessage?chat_id=${chatId}&text=${text}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        status.textContent = "✅ Tin nhắn đã được gửi! Đang chờ phản hồi...";
                        document.getElementById('botReply').textContent = '';
                        lastMessageId = null;
                        pollingCount = 0;
                        if (!pollingInterval) {
                            pollingInterval = setInterval(fetchBotReply, 5000); // Poll every 5 seconds
                        }
                    } else {
                        status.textContent = `❌ Lỗi: ${data.description}`;
                    }
                })
                .catch(err => {
                    console.error("Lỗi gửi:", err);
                    status.textContent = "❌ Lỗi hệ thống. Không thể gửi.";
                });
        });

        // Stop polling when the page is unloaded
        window.addEventListener('unload', () => {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        });
        setInterval(fetchBotReply, 2000);
    </script>
</body>
</html>