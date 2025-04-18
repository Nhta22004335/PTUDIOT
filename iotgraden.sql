-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 18, 2025 lúc 10:59 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `iotgraden`
--
CREATE DATABASE IF NOT EXISTS `iotgraden` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `iotgraden`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cambien`
--

CREATE TABLE `cambien` (
  `idcb` int(11) NOT NULL,
  `id` varchar(30) NOT NULL,
  `tencb` varchar(30) NOT NULL,
  `trangthai` varchar(10) NOT NULL,
  `thoigian` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `anh` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cambien`
--

INSERT INTO `cambien` (`idcb`, `id`, `tencb`, `trangthai`, `thoigian`, `anh`) VALUES
(1, 'nd', 'Nhiệt độ (°C)', 'on', '2025-04-15 13:00:14', 'picture/nhietdo.png'),
(2, 'da', 'Độ ẩm (%)', 'on', '2025-04-15 13:00:19', 'picture/doam.png'),
(3, 'as', 'Ánh sáng (lux)', 'on', '2025-04-15 13:00:30', 'picture/cole.png'),
(4, 'ndco2', 'Nồng độ khí gas (ppm)', 'on', '2025-04-15 13:32:57', 'picture/co2.png'),
(6, 'dad', 'Khoảng cách (cm)', 'on', '2025-04-15 13:00:52', 'picture/doamdat.png'),
(7, 'sieuam', 'Độ ẩm đất (%)', 'on', '2025-04-15 13:01:04', 'picture/sieuam.png');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `idnd` int(11) NOT NULL,
  `hoten` varchar(30) NOT NULL,
  `tendn` varchar(50) NOT NULL,
  `matkhau` varchar(100) NOT NULL,
  `sdt` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `anh` varchar(50) NOT NULL,
  `thoigian` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoidung`
--

INSERT INTO `nguoidung` (`idnd`, `hoten`, `tendn`, `matkhau`, `sdt`, `email`, `anh`, `thoigian`) VALUES
(1, 'Nguyễn Tuấn Anh', 'anh', '$2y$10$YbGRLgwr4RAAHleeMpc4NeBRFQzOBT7vMN89sIAPaeRsAirBSMmlu', '0702804594', '22004335@st.vlute.edu.vn', 'user.png', '2025-04-15 11:17:20'),
(6, 'Trần Phương Thế', 'the', '$2y$10$ujlbUwIPNTDgWtZEVmbFVu3TtL6zafT2T9M5Tz4Gya3LjAZ2KWaBG', '0702804594', '23004194@st.vlute.edu.vn', 'picture/avttree.png', '2025-04-15 12:17:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhatkycb`
--

CREATE TABLE `nhatkycb` (
  `idnk` int(11) NOT NULL,
  `idcb` int(11) NOT NULL,
  `giatricb` float NOT NULL,
  `thoigian` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhatkycb`
--

INSERT INTO `nhatkycb` (`idnk`, `idcb`, `giatricb`, `thoigian`) VALUES
(1718, 1, 0, '2025-04-18 08:13:26'),
(1719, 2, 0, '2025-04-18 08:13:26'),
(1720, 3, 0, '2025-04-18 08:13:26'),
(1721, 4, 0, '2025-04-18 08:13:26'),
(1722, 6, 0, '2025-04-18 08:13:26'),
(1723, 7, 0, '2025-04-18 08:13:26');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cambien`
--
ALTER TABLE `cambien`
  ADD PRIMARY KEY (`idcb`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`idnd`);

--
-- Chỉ mục cho bảng `nhatkycb`
--
ALTER TABLE `nhatkycb`
  ADD PRIMARY KEY (`idnk`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cambien`
--
ALTER TABLE `cambien`
  MODIFY `idcb` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `idnd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `nhatkycb`
--
ALTER TABLE `nhatkycb`
  MODIFY `idnk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1724;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
