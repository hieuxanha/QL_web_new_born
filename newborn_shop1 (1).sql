-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 12:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `newborn_shop1`
--

-- --------------------------------------------------------

--
-- Table structure for table `chitiet_hoadon`
--

CREATE TABLE `chitiet_hoadon` (
  `id` int(11) NOT NULL,
  `hoa_don_id` int(11) NOT NULL,
  `san_pham_id` int(11) NOT NULL,
  `soLuong` int(11) NOT NULL,
  `giaTien` decimal(10,2) NOT NULL,
  `thanhTien` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gio_hang`
--

CREATE TABLE `gio_hang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `san_pham_id` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `ngay_them` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sanpham`
--

CREATE TABLE `sanpham` (
  `id` int(11) NOT NULL,
  `ten_san_pham` varchar(255) NOT NULL,
  `gia` decimal(10,2) NOT NULL,
  `loai_san_pham` varchar(100) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `so_luong` int(11) NOT NULL,
  `anh_san_pham` varchar(255) DEFAULT NULL,
  `san_pham_noi_bat` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sanpham`
--

INSERT INTO `sanpham` (`id`, `ten_san_pham`, `gia`, `loai_san_pham`, `mo_ta`, `so_luong`, `anh_san_pham`, `san_pham_noi_bat`) VALUES
(32, 'bé mặc 4', 120000.00, 'Bé mặc', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 22, 'uploads/NB5.webp', 0),
(33, 'bé mặc 6', 210000.00, 'Bé mặc', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 2, 'uploads/NB6.webp', 0),
(34, 'bé mặc 7', 230000.00, 'Bé mặc', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 22, 'uploads/7_be_mac.webp', 0),
(35, 'bé mặc 8', 240000.00, 'Bé mặc', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 4, 'uploads/8_be_mac.webp', 0),
(36, 'bé ngủ 1', 200000.00, 'Bé ngủ', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 2, 'uploads/1_sheeping.webp', 0),
(37, 'bé ngủ 2', 222222.00, 'Bé ngủ', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 3, 'uploads/2_sleeping.webp', 0),
(38, 'bé ngủ 3', 230000.00, 'Bé ngủ', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 2, 'uploads/3_sleeping.webp', 0),
(39, 'bé ngủ 4', 231000.00, 'Bé ngủ', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 2, 'uploads/4_sleeping.webp', 0),
(40, 'bé ngủ 5', 240000.00, 'Bé ngủ', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 23, 'uploads/5_sleeping.webp', 0),
(41, 'bé ngủ 6', 213123.00, 'Bé ngủ', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 22, 'uploads/6_sleeping.webp', 0),
(43, 'bé ngủ 8', 213332.00, 'Bé ngủ', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 2, 'uploads/8_sleeping.png', 0),
(44, 'bé chơi', 200000.00, 'Bé chơi', 'Đội ngũ thiết kế - sáng tạo của Nous liên tục làm mới chính mình và sản phẩm để đem lại những điều tuyệt vời nhất cho khách hàng', 12, 'uploads/xe_đẩy.webp', 0),
(45, 'bé chơi chơi', 222222.00, 'Bé chơi', 'Đội ngũ thiết kế - sáng tạo của Nous liên tục làm mới chính mình và sản phẩm để đem lại những điều tuyệt vời nhất cho khách hàng', 12, 'uploads/xe_đẩy1.webp', 1),
(46, 'be choiii', 332222.00, 'Bé chơi', 'Đội ngũ thiết kế - sáng tạo của Nous liên tục làm mới chính mình và sản phẩm để đem lại những điều tuyệt vời nhất cho khách hàng', 2, 'uploads/xe_đẩy2.webp', 1),
(47, 'béchwoiii', 121222.00, 'Bé chơi', 'Đội ngũ thiết kế - sáng tạo của Nous liên tục làm mới chính mình và sản phẩm để đem lại những điều tuyệt vời nhất cho khách hàng', 2, 'uploads/xe_đẩy4.webp', 0),
(49, 'be123', 123.00, 'Bé ăn uống', 'Mang lại sự thoải mái để lớn khôn luôn là hành trình hạnh phúc. Thế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy. Nó rộng lớn, mới lạ và đầy bất ngờ.', 123, 'uploads/áo_nỉ_cá_heo.jpeg', 1),
(62, 'AAAAAA', 12000000.00, 'Bé chơi', 'Mang lại sự thoải mái để lớn khôn luôn là\r\nhành trình hạnh phúc.\r\nThế giới mà con trẻ nhìn thấy khác với những gì người lớn thấy.\r\nNó rộng lớn, mới lạ và đầy bất ngờ.', 1111, 'uploads/lược.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `thanhtoan`
--

CREATE TABLE `thanhtoan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hoTen` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `soDienThoai` varchar(15) NOT NULL,
  `diaChi` varchar(255) NOT NULL,
  `ngayThanhToan` datetime NOT NULL,
  `tongTien` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','khachhang','nhanvien') NOT NULL DEFAULT 'khachhang',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `address`, `password`, `role`, `created_at`) VALUES
(30, 'hieuzz', '0945465945', 'ant@gmail.com', 'ha noi', '$2y$10$l2hglm6Bos1lKsBnvKXMKugn6//yX9p/.HncgTIDlzLcsleA15.jW', 'khachhang', '2025-04-26 07:41:32'),
(31, 'admin', '1231231232', 'admin123@gmail.com', 'Hà nội', '$2y$10$6X9OStq8VnQE05j.c/AGVOYm3LtKe.TojlE6PXg94nORDPQw097qi', 'admin', '2025-04-26 08:38:02'),
(32, 'nhan vien', '1231231232', 'nv@gmail.com', 'Hà nội', '$2y$10$VRNTGThoBMQNmJ/B7wsTAu49YbN2kdvIwP1n3bnpNa9J7tKu4.Qaa', 'khachhang', '2025-04-26 08:38:24'),
(33, 'khach hang', '1231231232', 'kh@gmail.com', 'Hà nội', '$2y$10$97Sfw0DWTEnZs/0HpoQBj.mIEOSgyp.GUFmwU0ouUsR8fYM6mmXZ6', 'khachhang', '2025-04-26 08:38:38'),
(34, 'nhan vien2', '0977633085', 'nv2@gmail.com', 'THON 4 Xa Canh Nau', '$2y$10$ZIKFRGhu60RPzNKYjm2Mze5d.HCWS9RnOcfnrUgyZ3N96AmZ6po9a', 'nhanvien', '2025-04-26 08:41:25'),
(41, 'Long Khắc', '0977633085', 'nchieu7924@gmail.com', 'THON 4 Xa Canh Nau Huyen Thach That', '$2y$10$YPos/2LTe5XSlwyO/C1In.31xQsjcKAjMBhAIgpvYHO5t7OeUkzWy', 'nhanvien', '2025-04-28 03:36:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chitiet_hoadon`
--
ALTER TABLE `chitiet_hoadon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hoa_don_id` (`hoa_don_id`),
  ADD KEY `chitiet_hoadon_ibfk_2` (`san_pham_id`);

--
-- Indexes for table `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `san_pham_id` (`san_pham_id`);

--
-- Indexes for table `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chitiet_hoadon`
--
ALTER TABLE `chitiet_hoadon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `gio_hang`
--
ALTER TABLE `gio_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitiet_hoadon`
--
ALTER TABLE `chitiet_hoadon`
  ADD CONSTRAINT `chitiet_hoadon_ibfk_1` FOREIGN KEY (`hoa_don_id`) REFERENCES `thanhtoan` (`id`),
  ADD CONSTRAINT `chitiet_hoadon_ibfk_2` FOREIGN KEY (`san_pham_id`) REFERENCES `sanpham` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD CONSTRAINT `gio_hang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gio_hang_ibfk_2` FOREIGN KEY (`san_pham_id`) REFERENCES `sanpham` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `thanhtoan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
