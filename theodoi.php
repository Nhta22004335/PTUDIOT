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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link rel="stylesheet" href="theodoi.css?v=<?= time(); ?>">
    <title>Theo dõi</title>
</head>
<body>
    <!-- Nút chuông thông báo -->
    <div id="nutchuong" onclick="toggleThongBao()">
        <i class="fas fa-bell"></i>
        <span id="notification-count" class="notification-count">0</span>
    </div>
    <!-- Hộp thông báo -->
    <div id="thongbao-container">
        <h4>🔔 Thông báo từ các cảm biến</h4>
        <div id="thongbao-noidung">
            <p>Không có cảnh báo nào.</p>
        </div>
    </div>
    <!-- Nội dung hiển thị -->
    <h2>Dữ liệu cảm biến thời gian thực</h2>
    <div id="main-content">
        <div class="sensor-container">
            <?php foreach ($results as $row) { ?>
                <div class="sensor-card">
                    <img src="<?= $row['anh'] ?>" class="icon" alt="sensor icon">
                    <div class="sensor-label"><?= $row['tencb'] ?></div>
                    <input type="text" name="<?= $row['idcb'] ?>" disabled placeholder="Value" id="<?=$row['id']?>">
                </div>
            <?php } ?>
        </div>
        <!-- Nút Trở về -->
        <div id="back-button-container">
            <button onclick="troVe()" id="btn-trove"><i class="fas fa-arrow-left"></i>Trở về</button>
        </div>
    </div>
</body>
</html>
<script>
    // Trở về
    function troVe() {
        window.location.href = "trangchunguoidung.php";
    }

    // Khởi tạo client MQTT
    const client = new Paho.MQTT.Client("broker.emqx.io", 8083, "clientId_22004335");

    // Gán sự kiện
    client.onConnectionLost = onConnectionLost;
    client.onMessageArrived = onMessageArrived;

    // Kết nối đến MQTT broker với xử lý lỗi
    client.connect({
        onSuccess: onConnect,
        onFailure: function (error) {
            console.error("Kết nối MQTT thất bại: " + error.errorMessage);
        }
    });

    // Khi kết nối thành công
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

    // Khi mất kết nối
    function onConnectionLost(responseObject) {
        if (responseObject.errorCode !== 0) {
            console.error("Mất kết nối: " + responseObject.errorMessage);
        }
    }

    // Khi nhận được tin nhắn từ MQTT
    function onMessageArrived(message) {
        console.log("Dữ liệu nhận được: " + message.payloadString);
        try {
            const dht = JSON.parse(message.payloadString);
            // Kiểm tra và cập nhật dữ liệu vào input nếu tồn tại
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

    // Hàm kiểm tra phần tử DOM và cập nhật giá trị
    function updateValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.value = value;
        } else {
            console.warn(`Phần tử #${elementId} không tồn tại!`);
        }
    }

    let notificationCount = 0; // Biến lưu số lượng thông báo

    // Bấm vào nút chuông sẽ hiển thị hộp thoại tông báo
    function toggleThongBao() {
        const container = document.getElementById("thongbao-container");
        const battat = document.getElementById("nutchuong");
        const mainContent = document.getElementById("main-content");
        const trove = document.getElementById("back-button-container");
        if (container.style.display === "none" || container.style.display === "") {
            container.style.display = "block";
            battat.style.display = "none";
            mainContent.style.marginRight = "27vw"; 
            trove.style.marginLeft = "27vw";
            battat.classList.remove("ring"); 
            notificationCount = 0;
            document.getElementById("notification-count").textContent = notificationCount;
             // 👉 Cuộn đến cuối nội dung thông báo
            container.scrollTop = container.scrollHeight;
        } else {
            container.style.display = "none";
            battat.style.display = "block";
            mainContent.style.marginRight = "0";

        }
    }

    // Khi click ra phía ngoài khỏi hộp thoại thì thông báo sẽ ẩn jk
    document.addEventListener("click", function(event) {
        const thongBao = document.getElementById("thongbao-container");
        const nutChuong = document.getElementById("nutchuong");
        const trove = document.getElementById("back-button-container");
        if (!thongBao.contains(event.target) && !nutChuong.contains(event.target)) {
            thongBao.style.display = "none";
            nutChuong.style.display = "flex";
            document.getElementById("main-content").style.marginRight = "0";
            trove.style.marginLeft = "0";
            trove.style.width = "100%";
            trove.style.display = "flex";
            trove.style.justifyContent = "center"; /* Căn giữa nút */
        }
    });

    // Cập nhật thông báo vào phần tử thông báo
    function themThongBao(noidung) {
        const container = document.getElementById("thongbao-noidung");

        // Xóa thông báo mặc định nếu có
        if (container.children.length === 1 && container.children[0].innerText === "Không có cảnh báo nào.") {
            container.innerHTML = "";
        }
        // Tạo phần tử mới
        const p = document.createElement("p");
        p.textContent = noidung;

        // Thêm vào đầu danh sách
        container.insertBefore(p, container.firstChild);
        notificationCount++;
        document.getElementById("notification-count").textContent = notificationCount;
        if (notificationCount!=0) {
            const battat = document.getElementById("nutchuong");
            battat.classList.add("ring"); 
        } else {
            const battat = document.getElementById("nutchuong");
            battat.classList.remove("ring"); 
        }
    }


function kiemTraVaThongBao() {
    const sensorValues = {
        temp: parseFloat(document.getElementById("nd")?.value || 0),
        humi: parseFloat(document.getElementById("da")?.value || 0),
        anhsang: parseFloat(document.getElementById("as")?.value || 0),
        nongdokhi: parseFloat(document.getElementById("ndkhi")?.value || 0),
        doamdat: parseFloat(document.getElementById("dad")?.value || 0),
        sieuam: parseFloat(document.getElementById("sieuam")?.value || 0)
    };
    // NHIỆT ĐỘ VÀ ĐỘ ẨM
    if (sensorValues.temp < 18 && sensorValues.humi < 50) {
        themThongBao("❄️⚠️ Nhiệt độ thấp và độ ẩm thấp - cây có thể phát triển chậm, cần giữ ấm và tăng độ ẩm.");
    } else if (sensorValues.temp < 18 && sensorValues.humi > 75) {
        themThongBao("❄️💧 Nhiệt độ thấp và độ ẩm cao - dễ sinh nấm bệnh, cần thông gió.");
    } else if (sensorValues.temp > 30 && sensorValues.humi < 50) {
        themThongBao("🔥💦 Nhiệt độ cao và độ ẩm thấp - nguy cơ héo cây, cần tưới và làm mát.");
    } else if (sensorValues.temp > 30 && sensorValues.humi > 75) {
        themThongBao("🔥💧 Nhiệt độ cao và độ ẩm cao - dễ thối rễ, nên giảm độ ẩm hoặc thông gió.");
    } else if (sensorValues.temp >= 20 && sensorValues.temp <= 30 && sensorValues.humi >= 50 && sensorValues.humi <= 75) {
        themThongBao("✅ Điều kiện lý tưởng - cây cói sinh trưởng tốt.");
    } else {
        if (sensorValues.temp < 18) {
            themThongBao("❄️ Cảnh báo: Nhiệt độ thấp.");
        } else if (sensorValues.temp > 30) {
            themThongBao("🌡️ Cảnh báo: Nhiệt độ cao.");
        }
        if (sensorValues.humi < 50) {
            themThongBao("💧 Cảnh báo: Độ ẩm thấp.");
        } else if (sensorValues.humi > 75) {
            themThongBao("💧 Cảnh báo: Độ ẩm cao.");
        }
    }

    // ÁNH SÁNG 
    if (sensorValues.anhsang > 900) {
        themThongBao("⚠️Cần chiếu sáng gấp!")
    } else
        if (sensorValues.anhsang > 700 && sensorValues.anhsang < 900) {
            themThongBao("⚠️Cần chiếu sáng thêm!")
        }
        else
        if (sensorValues.anhsang > 400 && sensorValues.anhsang < 700) {
            themThongBao("Mức độ sáng cao!")
        }

    // NỒNG ĐỘ KHÍ CO, CH4, KHÓI
    if (sensorValues.nongdoco2 < 200) {
        themThongBao("Không khí sạch!");
    } else
        if (sensorValues.nongdoco2 > 200 && sensorValues.nongdoco2 < 400) 
            themThongBao("Có một chút khí gas, CO, khói nhẹ!");
        else
            if (sensorValues.nongdoco2 > 400 && sensorValues.nongdoco2 < 600) 
                themThongBao("Khí gas, CO, khói đang ở mức trung bình!");
            else
                themThongBao("Có thể rò rỉ gas, CO nặng hoặc cháy");

    //Đư DỘ ẨM ĐẤT
    if (sensorValues.doamdat < 300) {
        themThongBao("⚠️ Đất quá ướt!");
    } else
        if (sensorValues.doamdat > 300 && sensorValues.doamdat < 500) 
            themThongBao("Độ ẩm tốt!");
        else
            if (sensorValues.doamdat > 500 && sensorValues.doamdat < 700)
            themThongBao("Đất hơi khô!");
        else
            themThongBao("Đất khô!");

    // SIÊU ÂM - ĐO MUCEJ NƯỚC TRONG BỂ
    if (sensorValues.sieuam < 450) {
        themThongBao("Mực nước đã đầy!");
    } else
        if (sensorValues.sieuam < 100)
            themThongBao("Sắp cạn nước!");
        else
            if (sensorValues.sieuam < 30) themThongBao("Báo khẩn! Cạn nước!");
}
setInterval(kiemTraVaThongBao, 120000);

// THÊM VÀO CSDL 
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
            success: function(response) {
                console.log("Cập nhật thành công:", response);
            },
            error: function(xhr, status, error) {
                console.error("Lỗi khi cập nhật:", error);
            }
        });
    }
    //setInterval(updateSensorValues, 120000);
</script>
