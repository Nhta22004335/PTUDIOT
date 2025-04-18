<?php
session_start();

// Kiểm tra nếu người dùng đã đăng nhập (có thông tin trong session)
if (isset($_SESSION['tendn'])) {
    $tendn = $_SESSION['tendn']; // Lấy tên đăng nhập
    $hoten = $_SESSION['hoten']; // Lấy họ tên
    $email = $_SESSION['email']; // Lấy email
} else {
    // Nếu chưa đăng nhập, chuyển hướng về trang login hoặc trang chủ
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WoodMart Cannabis Slider</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="script.js"></script>
  <link rel="stylesheet" href="style.css">
  <style>
    .swiper { 
      height: 100vh; 
    }
    .swiper-slide { 
      background-size: cover; 
      background-position: center; 
    }
    .swiper-button-next,
    .swiper-button-prev {
      color: #22c55e; 
    }
    .swiper-button-next:hover,
    .swiper-button-prev:hover {
      color: #16a34a;
    }
    .swiper-pagination-bullet {
      background-color: #a3e635;
      opacity: 0.7;
    }
    .swiper-pagination-bullet-active {
      background-color: #22c55e; 
      opacity: 1;
    }
  </style>
</head>
<body class="bg-black text-white font-sans">
  <!-- Navbar -->
  <header class="flex justify-between items-center px-6 py-4 bg-green bg-opacity-70 fixed w-full z-50">
    <div class="text-2xl font-bold">IOT - GRADEN</div>
    <nav class="space-x-6 hidden md:flex items-center">
      <a href="#" onclick="theodoi()" class="hover:text-green-600 flex items-center gap-x-1 transition">
        <i data-lucide="shopping-bag" class="w-4 h-4"></i>Theo dõi
      </a>
      <a href="#" class="hover:text-green-600 flex items-center gap-x-1 transition">
        <i data-lucide="file-text" class="w-4 h-4"></i>Điều khiển
      </a>
      <a href="#" class="hover:text-green-600 flex items-center gap-x-1 transition">
        <i data-lucide="grid" class="w-4 h-4"></i>Lịch sử
      </a>
    </nav>
    <div class="relative" x-data="{ openUser: false }" @click.outside="openUser = false">
      <button @click="openUser = !openUser" class="flex items-center space-x-2 focus:outline-none">
          <img src="picture/avttree.png" alt="Avatar" class="w-8 h-8 rounded-full" />
          <span class="text-sm"><?php echo htmlspecialchars($hoten); ?></span>
      </button>
      <div x-show="openUser" class="absolute right-0 mt-2 w-40 bg-white/20 backdrop-blur-sm text-white rounded shadow-lg z-50 text-sm" x-transition>
          <p class="px-4 py-2">Chào bạn!</p> <!-- Hiển thị tên người dùng -->
          <p class="px-4 py-2">Tên ĐN: <?php echo htmlspecialchars($tendn); ?></p> <!-- Hiển thị email -->
          <p class="px-4 py-2">Email: <?php echo htmlspecialchars($email); ?></p> <!-- Hiển thị email -->
          <a href="logout.php" class="block px-4 py-2 transition duration-200 ease-in-out hover:bg-green-200/50 backdrop-blur-sm hover:text-white hover:rounded">
              Logout
          </a>
      </div>
  </div>
</header>
<!-- Slider -->
<div class="swiper">
  <div class="swiper-wrapper">
    <div class="swiper-slide" style="background-image: url('./picture/vuonkinhtm.png')">
      <div class="h-full w-full bg-black bg-opacity-50 flex items-center px-10">
        <div class="max-w-xl">
          <h4 class="text-green-300 text-sm mb-2">Bạn cần biết gì về hệ thống?</h4>
          <h1 class="text-4xl md:text-6xl font-bold mb-4">Giải pháp<span class="text-green-500">Vườn kính</span> Thông minh</h1>
          <p class="text-gray-300 mb-6">Hệ thống IoT cho vườn kính là một giải pháp nông nghiệp thông minh...</p>
        </div>
      </div>
    </div>
    <div class="swiper-slide" style="background-image: url('./picture/vuonkinhtm01.png')">
      <div class="h-full w-full bg-black bg-opacity-50 flex items-center px-10">
        <div class="max-w-xl">
          <h4 class="text-green-300 text-sm mb-2">Ứng dụng...!</h4>
          <h1 class="text-4xl md:text-6xl font-bold mb-4">Lợi ích của<span class="text-green-500">Mô hình</span> Trong đời sống</h1>
          <p class="text-gray-300 mb-6">Tự động hóa việc theo dõi và điều khiển môi trường trồng trọt trong nhà kính để tối ưu hóa sự phát triển của cây trồng...</p>
          <div class="flex space-x-4">
            <a href="#" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded text-black font-semibold">Thông Tin Thêm</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
  <div class="swiper-pagination"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  const swiper = new Swiper('.swiper', {
    loop: true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    autoplay: { delay: 10000 },
  });
</script>
<script>
    lucide.createIcons();
  </script>
</body>
</html>
