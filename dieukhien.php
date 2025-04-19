<?php
    include "config.php";

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <title>Điều Khiển</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
        font-family: 'Segoe UI', Arial, sans-serif; 
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        background-image: url('picture/bgdieukhien.jpg');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
    }

    .container {
        width: 100%;
        max-width: 900px;
        text-align: center;
    }

    h1 {
        margin-bottom: 30px;
        color: #ffffff;
        font-size: 2.2em;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 20px;
    }

    .card {
        background: #fefefe;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        padding: 20px 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: 0.3s ease;
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(5px);
    }

    .card i {
      font-size: 32px;
      color: #007bff;
      animation: bounce 2s infinite;
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }

    .card span {
      margin: 10px 0;
      font-size: 15px;
      font-weight: 500;
      color: #333;
      min-height: 25px;
    }

    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 26px;
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
      transition: .4s;
      border-radius: 26px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 20px;
      width: 20px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background-color: #007bff;
    }

    input:checked + .slider:before {
      transform: translateX(24px);
    }
    #back-button-container {
        width: 100%;
        display: flex;
        justify-content: center; /* Căn giữa nút */
    }
    #btn-trove {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.25));
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    transition: all 0.3s ease;
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    gap: 8px;
}

#btn-trove:hover {
    background: linear-gradient(135deg, rgba(97, 255, 92, 0.4), rgba(243, 255, 132, 0.5));
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 18px rgba(97, 255, 92, 0.5);
    transform: scale(1.05);
}

#btn-trove:active {
    transform: scale(0.95);
    box-shadow: 0 0 10px rgba(97, 255, 92, 0.3);
}
  </style>
</head>
<body>
  <div class="container">
    <h1>Điều Khiển Thiết Bị</h1>
    <div class="grid">
      <div class="card">
        <i class="fas fa-cogs"></i>
        <span style="min-height: 40px;">Motor bể chứa nước</span>
        <label class="switch">
          <input type="checkbox" id="motorbechuanuoc" />
          <span class="slider"></span>
        </label>
      </div>
      <div class="card">
        <i class="fas fa-snowflake"></i>
        <span style="min-height: 40px;">Hệ thống phun sương</span>
        <label class="switch">
          <input type="checkbox" id="htphunsuong" />
          <span class="slider"></span>
        </label>
      </div>
      <div class="card">
        <i class="fas fa-fan"></i>
        <span style="min-height: 40px;">Quạt thông gió</span>
        <label class="switch">
          <input type="checkbox" id="quatthongio" />
          <span class="slider"></span>
        </label>
      </div>
      <div class="card">
        <i class="fas fa-sun"></i>
        <span style="min-height: 40px;">HT đèn chiếu sáng</span>
        <label class="switch">
          <input type="checkbox" id="htdenchieusang" />
          <span class="slider"></span>
        </label>
      </div>
      <div class="card">
        <i class="fas fa-lightbulb"></i>
        <span style="min-height: 40px;">HT led cảnh báo</span>
        <label class="switch">
          <input type="checkbox" id="htledcanhbao" />
          <span class="slider"></span>
        </label>
      </div>
      <div class="card">
        <i class="fas fa-bullhorn"></i>
        <span style="min-height: 40px;">Còi báo</span>
        <label class="switch">
          <input type="checkbox" id="coibao" />
          <span class="slider"></span>
        </label>
      </div>
      <div class="card">
        <i class="fas fa-cog"></i>
        <span style="min-height: 40px;">HT màng che</span>
        <label class="switch">
          <input type="checkbox" id="htmangche" />
          <span class="slider"></span>
        </label>
      </div>
    </div>
    <!-- Nút Trở về -->
    <div id="back-button-container">
            <button onclick="troVe()" id="btn-trove"><i class="fas fa-arrow-left"></i>Trở về</button>
    </div>
  </div>
</body>
</html>

<script>
            function troVe() {
        window.location.href = "trangchunguoidung.php";
    }
    // Khởi tạo client MQTT
    const client = new Paho.MQTT.Client("broker.emqx.io", 8083, "clientId1_22004335");

    // Gán sự kiện
    client.onConnectionLost = onConnectionLost;
    client.onMessageArrived = onMessageArrived;

    client.connect({onSuccess:onConnect});

    // Khi kết nối thành công
    function onConnect() {
        console.log("Kết nối thành công!");
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
    }
    const switches = document.querySelectorAll('.switch input');

function getDeviceStates() {
    const stateObj = {};
    switches.forEach(sw => {
        const device = sw.id;
        stateObj[device] = sw.checked ? 1 : 0;
    });
    return JSON.stringify(stateObj);
}

switches.forEach(switchInput => {
    switchInput.addEventListener('change', function () {
        const jsonStates = getDeviceStates();
        var message = new Paho.MQTT.Message(jsonStates);
        message.destinationName = "Nta_22004335_nhan";
        client.send(message);
        console.log(jsonStates);
    });
});
</script>
