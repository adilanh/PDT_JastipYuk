-- JastipYuk Database Backup
-- Generated on: 2025-06-14 05:49:38

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `jastip_posts`;
CREATE TABLE `jastip_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jastiper_id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `contact` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `jastiper_id` (`jastiper_id`),
  CONSTRAINT `jastip_posts_ibfk_1` FOREIGN KEY (`jastiper_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `jastip_posts` VALUES 
('1','1','sdkabk','jdlkajsdlka','jshdlaksakdn','1','2025-06-10 14:15:16');

DROP TABLE IF EXISTS `log_pesanan`;
CREATE TABLE `log_pesanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status_change` varchar(200) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `log_pesanan_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `log_pesanan` VALUES 
('1','3','Pesanan #3 diubah menjadi \"Disetujui\" pada 2025-06-10 14:56:22','2025-06-10 14:56:22'),
('3','3','Status diubah menjadi \"Disetujui\"','2025-06-10 15:24:27'),
('5','1','Status diubah menjadi \"Disetujui\"','2025-06-10 15:38:18'),
('7','3','Status diubah menjadi \"Dikirim\"','2025-06-11 20:55:11'),
('9','3','Status diubah menjadi \"Ditolak\"','2025-06-11 20:55:18'),
('11','5','Status diubah menjadi \"Disetujui\"','2025-06-11 21:02:34'),
('13','7','Status diubah menjadi \"Dikirim\"','2025-06-14 12:08:37');

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `notifications` VALUES 
('1','3','Status pesanan #3 diubah menjadi: Disetujui','0','2025-06-10 14:56:22'),
('3','3','Status pesanan #3 diubah menjadi: Disetujui','0','2025-06-10 15:24:27'),
('5','3','Status pesanan #1 diubah menjadi: Disetujui','0','2025-06-10 15:38:18'),
('7','3','Status pesanan #3 diubah menjadi: Dikirim','0','2025-06-11 20:55:11'),
('9','3','Status pesanan #3 diubah menjadi: Ditolak','0','2025-06-11 20:55:18'),
('11','9','Status pesanan #5 diubah menjadi: Disetujui','0','2025-06-11 21:02:34'),
('13','11','Status pesanan #7 diubah menjadi: Dikirim','0','2025-06-14 12:08:37');

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `notes` text,
  `status` enum('Menunggu Persetujuan','Disetujui','Barang Dibeli','Dikirim','Selesai','Ditolak') DEFAULT 'Menunggu Persetujuan',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `jastip_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `orders` VALUES 
('1','1','3','asdasda','1','sembilan belas','Disetujui','2025-06-10 14:16:25','2025-06-10 15:38:18'),
('3','1','3','asdasda','1','sembilan belas','Ditolak','2025-06-10 14:16:29','2025-06-11 20:55:18'),
('5','1','9','tytdhtdth','1','','Disetujui','2025-06-11 21:01:28','2025-06-11 21:02:34'),
('7','1','11','tiket konser enhypen','1','tiket konser VIP','Dikirim','2025-06-14 12:06:02','2025-06-14 12:08:37');

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `user_profiles` VALUES 
('1','1','',NULL,NULL,'2025-06-10 14:14:20'),
('3','3','',NULL,NULL,'2025-06-10 14:15:47'),
('5','5','',NULL,NULL,'2025-06-10 15:39:40'),
('7','7','',NULL,NULL,'2025-06-10 15:42:57'),
('9','9','User 2','','','2025-06-11 20:57:48'),
('11','11','Adila Nurul','0842647577313','Lampung','2025-06-14 12:03:50');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('JASTIPER','CUSTOMER') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` VALUES 
('1','jastip','jastip@example.com','$2y$10$Xqrj7/jFY1NZ7wQBDcUteObeoC9Tgj05C90JiP64F5gHgl3kQU.EK','JASTIPER','2025-06-10 14:14:20'),
('3','user','user@example123','$2y$10$pl5vK6cX3CvWdS8ZZSb4LOl/viCCJ/1ovpeE.WMW6V7P6OwVVLgnu','CUSTOMER','2025-06-10 14:15:47'),
('5','nitip','nitip@asb.com','$2y$10$aAs7shEydhFqJljv78.9KO0UHVfTea7oYrMCvWPkaqE0/kx2pNa4m','CUSTOMER','2025-06-10 15:39:40'),
('7','admin','admin@admin.com','$2y$10$UZEQt9xEVgNiJP8m5WpsCOSogxoBizJOfZbcLWy3YMo4dHZxMjDxq','JASTIPER','2025-06-10 15:42:57'),
('9','user2','user2@abc.com','$2y$10$gA9v8NO8jLWFwB0dw9YVau6zXFfwKffgYsYtkK24wVE4rxupJUBwa','CUSTOMER','2025-06-11 20:57:48'),
('11','adila','adilanurul30@gmail.com','$2y$10$8M5MeEctTMhH5AZsnzRxY.FHDZGfuYR0xUgPW8ARTYVK0OwqXhxfa','CUSTOMER','2025-06-14 12:03:50');

SET FOREIGN_KEY_CHECKS = 1;
