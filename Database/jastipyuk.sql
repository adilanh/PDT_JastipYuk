-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 14, 2025 at 05:39 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jastipyuk`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `UbahStatusPesanan` (IN `id_pesanan_input` INT, IN `status_baru_input` VARCHAR(50))   BEGIN
    -- 1. Update status pesanan
    UPDATE orders 
    SET status = status_baru_input, updated_at = CURRENT_TIMESTAMP 
    WHERE id = id_pesanan_input;
    
    -- 2. Catat perubahan ke dalam log
    INSERT INTO log_pesanan (order_id, status_change) 
    VALUES (id_pesanan_input, CONCAT('Status diubah menjadi "', status_baru_input, '"'));
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `HitungTotalPesananAktif` (`id_jastiper_input` INT) RETURNS INT DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total INT DEFAULT 0;
    
    SELECT COUNT(*) INTO total
    FROM orders o
    JOIN jastip_posts jp ON o.post_id = jp.id
    WHERE jp.jastiper_id = id_jastiper_input 
    AND o.status NOT IN ('Selesai', 'Ditolak');
    
    RETURN total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `jastip_posts`
--

CREATE TABLE `jastip_posts` (
  `id` int NOT NULL,
  `jastiper_id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `contact` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jastip_posts`
--

INSERT INTO `jastip_posts` (`id`, `jastiper_id`, `title`, `description`, `contact`, `is_active`, `created_at`) VALUES
(1, 1, 'sdkabk', 'jdlkajsdlka', 'jshdlaksakdn', 1, '2025-06-10 07:15:16');

-- --------------------------------------------------------

--
-- Table structure for table `log_pesanan`
--

CREATE TABLE `log_pesanan` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `status_change` varchar(200) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `log_pesanan`
--

INSERT INTO `log_pesanan` (`id`, `order_id`, `status_change`, `created_at`) VALUES
(1, 3, 'Pesanan #3 diubah menjadi \"Disetujui\" pada 2025-06-10 14:56:22', '2025-06-10 07:56:22'),
(3, 3, 'Status diubah menjadi \"Disetujui\"', '2025-06-10 08:24:27'),
(5, 1, 'Status diubah menjadi \"Disetujui\"', '2025-06-10 08:38:18'),
(7, 3, 'Status diubah menjadi \"Dikirim\"', '2025-06-11 13:55:11'),
(9, 3, 'Status diubah menjadi \"Ditolak\"', '2025-06-11 13:55:18'),
(11, 5, 'Status diubah menjadi \"Disetujui\"', '2025-06-11 14:02:34'),
(13, 7, 'Status diubah menjadi \"Dikirim\"', '2025-06-14 05:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 'Status pesanan #3 diubah menjadi: Disetujui', 0, '2025-06-10 07:56:22'),
(3, 3, 'Status pesanan #3 diubah menjadi: Disetujui', 0, '2025-06-10 08:24:27'),
(5, 3, 'Status pesanan #1 diubah menjadi: Disetujui', 0, '2025-06-10 08:38:18'),
(7, 3, 'Status pesanan #3 diubah menjadi: Dikirim', 0, '2025-06-11 13:55:11'),
(9, 3, 'Status pesanan #3 diubah menjadi: Ditolak', 0, '2025-06-11 13:55:18'),
(11, 9, 'Status pesanan #5 diubah menjadi: Disetujui', 0, '2025-06-11 14:02:34'),
(13, 11, 'Status pesanan #7 diubah menjadi: Dikirim', 0, '2025-06-14 05:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `notes` text,
  `status` enum('Menunggu Persetujuan','Disetujui','Barang Dibeli','Dikirim','Selesai','Ditolak') DEFAULT 'Menunggu Persetujuan',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `post_id`, `customer_id`, `item_name`, `quantity`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'asdasda', 1, 'sembilan belas', 'Disetujui', '2025-06-10 07:16:25', '2025-06-10 08:38:18'),
(3, 1, 3, 'asdasda', 1, 'sembilan belas', 'Ditolak', '2025-06-10 07:16:29', '2025-06-11 13:55:18'),
(5, 1, 9, 'tytdhtdth', 1, '', 'Disetujui', '2025-06-11 14:01:28', '2025-06-11 14:02:34'),
(7, 1, 11, 'tiket konser enhypen', 1, 'tiket konser VIP', 'Dikirim', '2025-06-14 05:06:02', '2025-06-14 05:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('JASTIPER','CUSTOMER') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'jastip', 'jastip@example.com', '$2y$10$Xqrj7/jFY1NZ7wQBDcUteObeoC9Tgj05C90JiP64F5gHgl3kQU.EK', 'JASTIPER', '2025-06-10 07:14:20'),
(3, 'user', 'user@example123', '$2y$10$pl5vK6cX3CvWdS8ZZSb4LOl/viCCJ/1ovpeE.WMW6V7P6OwVVLgnu', 'CUSTOMER', '2025-06-10 07:15:47'),
(5, 'nitip', 'nitip@asb.com', '$2y$10$aAs7shEydhFqJljv78.9KO0UHVfTea7oYrMCvWPkaqE0/kx2pNa4m', 'CUSTOMER', '2025-06-10 08:39:40'),
(7, 'admin', 'admin@admin.com', '$2y$10$UZEQt9xEVgNiJP8m5WpsCOSogxoBizJOfZbcLWy3YMo4dHZxMjDxq', 'JASTIPER', '2025-06-10 08:42:57'),
(9, 'user2', 'user2@abc.com', '$2y$10$gA9v8NO8jLWFwB0dw9YVau6zXFfwKffgYsYtkK24wVE4rxupJUBwa', 'CUSTOMER', '2025-06-11 13:57:48'),
(11, 'adila', 'adilanurul30@gmail.com', '$2y$10$8M5MeEctTMhH5AZsnzRxY.FHDZGfuYR0xUgPW8ARTYVK0OwqXhxfa', 'CUSTOMER', '2025-06-14 05:03:50');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `SaatUserBaruDaftar` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_profiles (user_id, full_name) 
    VALUES (NEW.id, 'Nama Lengkap');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `full_name`, `phone`, `address`, `created_at`) VALUES
(1, 1, '', NULL, NULL, '2025-06-10 07:14:20'),
(3, 3, '', NULL, NULL, '2025-06-10 07:15:47'),
(5, 5, '', NULL, NULL, '2025-06-10 08:39:40'),
(7, 7, '', NULL, NULL, '2025-06-10 08:42:57'),
(9, 9, 'User 2', '', '', '2025-06-11 13:57:48'),
(11, 11, 'Adila Nurul', '0842647577313', 'Lampung', '2025-06-14 05:03:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jastip_posts`
--
ALTER TABLE `jastip_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jastiper_id` (`jastiper_id`);

--
-- Indexes for table `log_pesanan`
--
ALTER TABLE `log_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jastip_posts`
--
ALTER TABLE `jastip_posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `log_pesanan`
--
ALTER TABLE `log_pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jastip_posts`
--
ALTER TABLE `jastip_posts`
  ADD CONSTRAINT `jastip_posts_ibfk_1` FOREIGN KEY (`jastiper_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_pesanan`
--
ALTER TABLE `log_pesanan`
  ADD CONSTRAINT `log_pesanan_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `jastip_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
