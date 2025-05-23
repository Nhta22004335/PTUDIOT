<?php
include "config.php";
try {
    $conn = connectDatabase();
    $sql = "SELECT idcb, id, tencb, trangthai, thoigian, anh FROM cambien";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage();
} finally {
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo dõi cảm biến</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="theodoi.css?v=<?= time(); ?>">
</head>
<body>
    <!-- Notification Bell -->
    <div id="nutchuong" class="notification-bell" onclick="toggleThongBao()">
        <i class="fas fa-bell"></i>
        <span id="notification-count" class="notification-count">0</span>
    </div>

    <!-- Notification Panel -->
    <div id="thongbao-container" class="notification-panel">
        <h4 class="p-2 border-bottom">🔔 Thông báo từ cảm biến</h4>
        <div id="thongbao-noidung" class="p-2">
            <p>Không có cảnh báo nào.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h2 class="text-center">Dữ liệu cảm biến thời gian thực</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 sensor-container">
            <?php foreach ($results as $row) { ?>
                <div class="col">
                    <div class="sensor-card card h-100 text-center">
                        <img src="picture/<?= $row['anh'] ?>" class="card-img-top sensor-icon mx-auto mt-2" alt="sensor icon">
                        <div class="card-body p-2">
                            <h5 class="card-title sensor-label"><?= $row['tencb'] ?></h5>
                            <input type="text" name="<?= $row['idcb'] ?>" disabled placeholder="Giá trị" id="<?= $row['id'] ?>" class="form-control text-center">
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <!-- Back Button -->
        <div class="text-center mt-3 mb-3">
            <button onclick="troVe()" class="btn btn-gradient"><i class="fas fa-arrow-left me-1"></i>Trở về</button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
    <script>
        // Trở về
        function troVe() {
            window.location.href = "trangchu.php";
        }

        // MQTT Client
        const client = new Paho.MQTT.Client("broker.emqx.io", 8083, "clientId_22004335");
        client.onConnectionLost = onConnectionLost;
        client.onMessageArrived = onMessageArrived;

        client.connect({
            onSuccess: onConnect,
            onFailure: function (error) {
                console.error("Kết nối MQTT thất bại: " + error.errorMessage);
            }
        });

        function onConnect() {
            console.log("Kết nối thành công!");
            client.subscribe("Nta_22004335_gui", {
                onSuccess: function () {
                    console.log("Đã subscribe thành công!");
                },
                onFailure: function (error) {
                    console.error("Lỗi khi subscribe: " + error.errorMessage);
                }
            });
        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.error("Mất kết nối: " + responseObject.errorMessage);
            }
        }

        function onMessageArrived(message) {
            try {
                const dht = JSON.parse(message.payloadString);
                updateValue("nd", dht.temperature);
                updateValue("da", dht.humidity);
                updateValue("as", dht.anhsang);
                updateValue("ndkhi", dht.nongdokhi);
                updateValue("dad", dht.doamdat);
                updateValue("sieuam", dht.sieuam);
            } catch (error) {
                console.error("Lỗi khi xử lý dữ liệu MQTT: " + error.message);
            }
        }

        function updateValue(elementId, value) {
            const element = document.getElementById(elementId);
            if (element) {
                element.value = value;
                element.classList.add("highlight");
                setTimeout(() => element.classList.remove("highlight"), 800);
            } else {
                console.warn(`Phần tử #${elementId} không tồn tại!`);
            }
        }

        let notificationCount = 0;
        let notifications = []; // Array to store notifications

        function toggleThongBao() {
            const container = document.getElementById("thongbao-container");
            const bell = document.getElementById("nutchuong");
            if (container.classList.contains("active")) {
                container.classList.remove("active");
                bell.classList.remove("d-none");
            } else {
                container.classList.add("active");
                bell.classList.add("d-none");
                notificationCount = 0;
                document.getElementById("notification-count").textContent = notificationCount;
                bell.classList.remove("ring");
                container.scrollTop = container.scrollHeight;
            }
        }

        document.addEventListener("click", function (event) {
            const thongBao = document.getElementById("thongbao-container");
            const nutChuong = document.getElementById("nutchuong");
            if (!thongBao.contains(event.target) && !nutChuong.contains(event.target)) {
                thongBao.classList.remove("active");
                nutChuong.classList.remove("d-none");
            }
        });

        function themThongBao(noidung) {
            const container = document.getElementById("thongbao-noidung");
            if (container.children.length === 1 && container.children[0].innerText === "Không có cảnh báo nào.") {
                container.innerHTML = "";
            }
            const p = document.createElement("p");
            p.textContent = noidung;
            p.classList.add("notification-item");
            container.insertBefore(p, container.firstChild);
            notificationCount++;
            document.getElementById("notification-count").textContent = notificationCount;
            const bell = document.getElementById("nutchuong");
            bell.classList.add("ring");
            notifications.push(noidung); // Store notification
        }

        function kiemTraVaThongBao() {
            notifications = []; // Reset notifications array
            const sensorValues = {
                temp: parseFloat(document.getElementById("nd")?.value || 0),
                humi: parseFloat(document.getElementById("da")?.value || 0),
                anhsang: parseFloat(document.getElementById("as")?.value || 0),
                nongdokhi: parseFloat(document.getElementById("ndkhi")?.value || 0),
                doamdat: parseFloat(document.getElementById("dad")?.value || 0),
                sieuam: parseFloat(document.getElementById("sieuam")?.value || 0)
            };

            // Nhiệt độ và độ ẩm
            if (sensorValues.temp < 18 && sensorValues.humi < 50) {
                themThongBao("❄️⚠️ Nhiệt độ thấp và độ ẩm thấp - cây có thể phát triển chậm.");
            } else if (sensorValues.temp < 18 && sensorValues.humi > 75) {
                themThongBao("❄️💧 Nhiệt độ thấp và độ ẩm cao - dễ sinh nấm bệnh.");
            } else if (sensorValues.temp > 30 && sensorValues.humi < 50) {
                themThongBao("🔥💦 Nhiệt độ cao và độ ẩm thấp - nguy cơ héo cây.");
            } else if (sensorValues.temp > 30 && sensorValues.humi > 75) {
                themThongBao("🔥💧 Nhiệt độ cao và độ ẩm cao - dễ thối rễ.");
            } else if (sensorValues.temp >= 18 && sensorValues.temp <= 30 && sensorValues.humi >= 50 && sensorValues.humi <= 75) {
                themThongBao("✅ Điều kiện lý tưởng - cây có sinh trưởng tốt.");
            } else {
                if (sensorValues.temp < 18) themThongBao("❄️ Cảnh báo: Nhiệt độ thấp.");
                if (sensorValues.temp > 30) themThongBao("🌡️ Cảnh báo: Nhiệt độ cao.");
                if (sensorValues.humi < 50) themThongBao("💧 Cảnh báo: Độ ẩm thấp.");
                if (sensorValues.humi > 75) themThongBao("💧 Cảnh báo: Độ ẩm cao.");
            }

            // Ánh sáng
            if (sensorValues.anhsang < 400) {
                themThongBao("⚠️ Thiếu sáng, cần chiếu sáng thêm!");
            } else if (sensorValues.anhsang < 700) {
                themThongBao("Ánh sáng ở mức vừa phải.");
            } else if (sensorValues.anhsang < 900) {
                themThongBao("Mức độ sáng cao.");
            } else {
                themThongBao("⚠️ Ánh sáng quá mạnh, cần giảm cường độ chiếu sáng!");
            }


            // Nồng độ khí
            if (sensorValues.nongdokhi < 200) {
                themThongBao("Không khí sạch!");
            } else if (sensorValues.nongdokhi < 400) {
                themThongBao("Có một chút khí gas, CO, khói nhẹ!");
            } else if (sensorValues.nongdokhi < 600) {
                themThongBao("Khí gas, CO, khói ở mức trung bình!");
            } else {
                themThongBao("⚠️ Có thể rò rỉ gas, CO nặng hoặc cháy!");
            }

            // Độ ẩm đất
            if (sensorValues.doamdat < 300) {
                themThongBao("⚠️ Đất quá ướt!");
            } else if (sensorValues.doamdat < 500) {
                themThongBao("Độ ẩm đất tốt!");
            } else if (sensorValues.doamdat < 700) {
                themThongBao("Đất hơi khô!");
            } else {
                themThongBao("⚠️ Đất khô!");
            }

            // Siêu âm
            if (sensorValues.sieuam < 30) {
                themThongBao("🚨 Báo khẩn! Cạn nước!");
            } else if (sensorValues.sieuam < 100) {
                themThongBao("⚠️ Sắp cạn nước!");
            } else if (sensorValues.sieuam < 450) {
                themThongBao("Mực nước đã đầy!");
            }

            // Send notifications to server for emailing
            if (notifications.length > 0) {
                $.ajax({
                    url: "guiemail.php",
                    method: "POST",
                    data: { notifications: JSON.stringify(notifications) },
                    success: function (response) {
                        console.log("Email gửi thành công:", response);
                    },
                    error: function (xhr, status, error) {
                        console.error("Lỗi khi gửi email:", error);
                    }
                });
            }
        }

        setInterval(kiemTraVaThongBao, 60000);

        function updateSensorValues() {
            const inputs = document.querySelectorAll("input");
            let data = [];
            inputs.forEach(input => {
                data.push({
                    idcb: input.name,
                    value: input.value
                });
            });
            $.ajax({
                url: "capnhatnkcambien.php",
                method: "POST",
                data: { sensors: JSON.stringify(data) },
                success: function (response) {
                    console.log("Cập nhật thành công:", response);
                },
                error: function (xhr, status, error) {
                    console.error("Lỗi khi cập nhật:", error);
                }
            });
        }
        setInterval(updateSensorValues, 120000);
    </script>
</body>
</html>