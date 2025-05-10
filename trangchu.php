<?php
session_start();
if (isset($_SESSION['tendn'])) {
    $tendn = $_SESSION['tendn'];
    $hoten = $_SESSION['hoten'];
    $email = $_SESSION['email'];
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
  <link rel="stylesheet" href="style.css">
  <style>
    .swiper { height: 100vh; }
    .swiper-slide { background-size: cover; background-position: center; }
    .swiper-button-next, .swiper-button-prev { color: #22c55e; }
    .swiper-button-next:hover, .swiper-button-prev:hover { color: #16a34a; }
    .swiper-pagination-bullet { background-color: #a3e635; opacity: 0.7; }
    .swiper-pagination-bullet-active { background-color: #22c55e; opacity: 1; }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 100;
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: #1a1a1a;
      color: #e5e7eb;
      max-width: 600px;
      width: 90%;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
      position: relative;
      border: 2px solid #22c55e;
    }
    .modal-content h2 { color: #22c55e; font-size: 1.75rem; margin-bottom: 1rem; }
    .modal-content h3 { color: #a3e635; font-size: 1.25rem; margin-top: 1.5rem; margin-bottom: 0.5rem; }
    .modal-content p, .modal-content ul { margin-bottom: 1rem; line-height: 1.6; }
    .modal-content ul li { list-style-type: disc; margin-left: 1.5rem; }
    .close-modal {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: #22c55e;
      color: black;
      font-weight: bold;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background 0.2s;
    }
    .close-modal:hover { background: #16a34a; }
    @media (max-width: 640px) {
      .modal-content { padding: 1.5rem; width: 95%; }
      .modal-content h2 { font-size: 1.5rem; }
      .modal-content h3 { font-size: 1.1rem; }
    }
    #menu-toggle { display: none; }
    @media (max-width: 768px) {
      #menu-toggle { display: block; }
      #nav-menu {
        display: block;
        position: absolute;
        top: 64px;
        left: 0;
        width: 100%;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 60;
        padding: 1rem;
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: transform 0.8s ease-in-out, opacity 0.8s ease-in-out, visibility 0.8s ease-in-out;
      }
      #nav-menu.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
      }
      #nav-menu a {
        color: white;
        font-size: 1.1rem;
        padding: 0.5rem;
      }
      #nav-menu a:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
      }
      header { flex-wrap: wrap; }
      .text-2xl { font-size: 1.5rem; }
      .relative > button > span { display: none; }
      .relative > button > img { width: 2rem; height: 2rem; }
    }
  </style>
</head>
<body class="bg-black text-white font-sans">
  <header class="flex justify-between items-center px-6 py-4 bg-green bg-opacity-70 fixed w-full z-50">
    <div class="text-2xl font-bold">IOT - GRADEN</div>
    <button id="menu-toggle" class="md:hidden focus:outline-none">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>
    <nav id="nav-menu" class="hidden md:flex md:items-center md:space-x-6 absolute md:static top-16 left-0 w-full md:w-auto bg-green bg-opacity-90 md:bg-transparent p-4 md:p-0 transition-all duration-300">
      <a href="theodoi.php" class="block py-2 md:py-0 hover:text-green-600 flex items-center gap-x-1 transition" <?php echo !isset($_SESSION['tendn']) ? 'style="pointer-events: none;"' : ''; ?>>
        <i data-lucide="shopping-bag" class="w-4 h-4"></i>Theo dõi
      </a>
      <a href="dieukhien.php" class="block py-2 md:py-0 hover:text-green-600 flex items-center gap-x-1 transition" <?php echo !isset($_SESSION['tendn']) ? 'style="pointer-events: none;"' : ''; ?>>
        <i data-lucide="file-text" class="w-4 h-4"></i>Điều khiển & Thao tác Telegram
      </a>
      <a href="#" class="block py-2 md:py-0 hover:text-green-600 flex items-center gap-x-1 transition" <?php echo !isset($_SESSION['tendn']) ? 'style="pointer-events: none;"' : ''; ?>>
        <i data-lucide="grid" class="w-4 h-4"></i>Thống kê
      </a>
    </nav>
    <div class="relative" id="user-menu" x-data="{ openUser: false }" @click.outside="openUser = false">
      <?php if (isset($_SESSION['tendn'])): ?>
        <button @click="openUser = !openUser" class="flex items-center space-x-2 focus:outline-none">
          <img src="picture/avttree.png" alt="Avatar" class="w-8 h-8 rounded-full" />
          <span class="text-sm"><?php echo htmlspecialchars($hoten); ?></span>
        </button>
        <div x-show="openUser" class="absolute right-0 mt-2 w-60 bg-white/20 backdrop-blur-sm text-white rounded shadow-lg z-50 text-sm" x-transition>
          <p class="px-4 py-2">Chào bạn!</p>
          <p class="px-4 py-2">Tên ĐN: <?php echo htmlspecialchars($tendn); ?></p>
          <p class="px-4 py-2">Email: <?php echo htmlspecialchars($email); ?></p>
          <a href="logout.php" class="block px-4 py-2 transition duration-200 ease-in-out hover:bg-green-200/50 backdrop-blur-sm hover:text-white hover:rounded">
            Logout
          </a>
        </div>
      <?php else: ?>
        <button @click="openUser = !openUser" class="flex items-center space-x-2 focus:outline-none">
          <img src="picture/avttree.png" alt="Avatar" class="w-8 h-8 rounded-full" />
          <span class="text-sm">Vui lòng ĐN tài khoản!</span>
        </button>
      <?php endif; ?>
    </div>
  </header>
  <div class="swiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide" style="background-image: url('./picture/vuonkinhtm.png')">
        <div class="h-full w-full bg-black bg-opacity-50 flex items-center px-10">
          <div class="max-w-xl">
            <h4 class="text-green-300 text-sm mb-2">Bạn cần biết gì về hệ thống?</h4>
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Giải pháp<span class="text-green-500">Vườn kính</span> Thông minh</h1>
            <p class="text-gray-300 mb-6">Hệ thống IoT cho vườn kính là một giải pháp nông nghiệp thông minh...</p>
            <?php if (!isset($_SESSION['tendn'])): ?>
              <div class="flex space-x-4">
                <a href="login.html" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded text-black font-semibold">Đăng Nhập</a>
                <a href="register.html" class="border border-white px-4 py-2 rounded text-white hover:bg-white hover:text-black">Đăng Ký</a>
              </div>
            <?php endif; ?>
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
              <a href="#" onclick="openModal()" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded text-black font-semibold">Thông Tin Thêm</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
  </div>
  <div id="infoModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" onclick="closeModal()">×</span>
      <h2>Thông Tin Hệ Thống</h2>
      <h3>Giới Thiệu</h3>
      <p>
        Hệ thống IoT Vườn Kính Thông Minh là một giải pháp nông nghiệp hiện đại, ứng dụng công nghệ Internet vạn vật (IoT) để tự động hóa việc theo dõi và điều khiển môi trường trồng trọt. Hệ thống tích hợp cảm biến, thiết bị điều khiển, và giao tiếp qua Telegram, giúp người dùng tối ưu hóa năng suất cây trồng, tiết kiệm tài nguyên và giảm thiểu công sức.
      </p>
      <h3>Liên Hệ</h3>
      <ul>
        <li><strong>Email</strong>: monkeystore.hotro.4335@gmail.com</li>
        <li><strong>Số điện thoại</strong>: (+84) 702 804 594</li>
        <li><strong>Địa chỉ</strong>: 73 Nguyễn Huệ, phường 2, thành phố Vĩnh Long, tỉnh Vĩnh Long</li>
      </ul>
      <h3>Bản Quyền</h3>
      <p>
        © 2025 IoT Garden. Bản quyền thuộc về Nhóm Phát Triển IoT Garden. Mọi hình thức sao chép hoặc sử dụng trái phép đều bị nghiêm cấm.
      </p>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    const swiper = new Swiper('.swiper', {
      loop: true,
      navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
      pagination: { el: '.swiper-pagination', clickable: true },
      autoplay: { delay: 10000 },
    });
    function openModal() {
      document.getElementById('infoModal').style.display = 'flex';
    }
    function closeModal() {
      document.getElementById('infoModal').style.display = 'none';
    }
    window.onclick = function(event) {
      const modal = document.getElementById('infoModal');
      if (event.target === modal) {
        modal.style.display = 'none';
      }
    };
    document.addEventListener('DOMContentLoaded', function () {
      const menuToggle = document.getElementById('menu-toggle');
      const navMenu = document.getElementById('nav-menu');
      const userMenu = document.getElementById('user-menu');
      if (menuToggle && navMenu && userMenu) {
        menuToggle.addEventListener('click', function () {
          console.log('Hamburger clicked');
          navMenu.classList.toggle('active');
          if (navMenu.classList.contains('active')) {
            userMenu.style.display = 'none';
          } else {
            userMenu.style.display = 'block';
          }
        });
      } else {
        console.error('Menu toggle, nav menu, or user menu not found');
      }
    });
    lucide.createIcons();
  </script>
</body>
</html>