-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 10, 2025 at 06:22 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pcc-hotel`
--

-- --------------------------------------------------------

--
-- Table structure for table `additional_items`
--

DROP TABLE IF EXISTS `additional_items`;
CREATE TABLE IF NOT EXISTS `additional_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `additional_items`
--

INSERT INTO `additional_items` (`item_id`, `transaction_id`, `item_name`, `item_price`, `quantity`, `subtotal`, `created_at`) VALUES
(1, 1, 'Late Checkout', 300.00, 1, 300.00, '2025-05-08 15:37:57'),
(2, 1, 'Toothbrush', 20.00, 1, 20.00, '2025-05-08 15:37:57'),
(3, 3, 'Laundry', 150.00, 1, 150.00, '2025-05-09 13:15:31');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `room_id` int NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `booking_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `guests_count` int NOT NULL,
  `is_discount` tinyint(1) NOT NULL DEFAULT '0',
  `total_price` decimal(10,2) NOT NULL,
  `special_requests` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `booking_status` enum('pending','confirmed','checked_in','checked_out','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `payment_status` set('pending','paid','partial') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `is_feedback` tinyint(1) DEFAULT '0',
  `booking_source` varchar(50) DEFAULT 'website',
  PRIMARY KEY (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `booking_date`, `guests_count`, `is_discount`, `total_price`, `special_requests`, `booking_status`, `payment_status`, `is_feedback`, `booking_source`) VALUES
(1, 2, 5, '2025-05-08', '2025-05-10', '2025-05-08 15:37:57', 3, 0, 1900.00, 'test', 'checked_out', 'paid', 1, 'website'),
(3, 2, 5, '2025-05-11', '2025-05-12', '2025-05-09 13:10:50', 4, 0, 950.00, 'second test', 'checked_out', 'paid', 0, 'website'),
(4, 2, 5, '2025-05-13', '2025-05-14', '2025-05-09 13:15:31', 1, 0, 950.00, 'test', 'checked_out', 'paid', 0, 'website'),
(7, 2, 5, '2025-05-09', '2025-05-10', '2025-05-09 13:24:32', 1, 0, 950.00, 'test', 'cancelled', 'pending', 0, 'website'),
(8, 2, 5, '2025-05-11', '2025-05-12', '2025-05-09 13:38:17', 4, 0, 950.00, 'sample', 'cancelled', 'pending', 0, 'website'),
(9, 2, 5, '2025-05-09', '2025-05-11', '2025-05-09 14:11:18', 3, 1, 1805.00, 'sample discount test', 'checked_out', 'paid', 1, 'website'),
(10, 2, 9, '2025-05-09', '2025-05-14', '2025-05-09 14:14:28', 4, 0, 6500.00, 'test', 'pending', 'paid', 0, 'website');

-- --------------------------------------------------------

--
-- Table structure for table `customer_profiles`
--

DROP TABLE IF EXISTS `customer_profiles`;
CREATE TABLE IF NOT EXISTS `customer_profiles` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `frequent_guest` int NOT NULL,
  `loyal_points` int NOT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customer_profiles`
--

INSERT INTO `customer_profiles` (`customer_id`, `user_id`, `frequent_guest`, `loyal_points`) VALUES
(1, 2, 6, 20);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `room_id` int NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `booking_id` (`booking_id`),
  KEY `room_id` (`room_id`)
) ;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `customer_id`, `booking_id`, `room_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 5, 5, 'very good experience keep it up!', '2025-05-09 14:49:13', NULL),
(2, 2, 9, 5, 1, 'awful', '2025-05-09 15:10:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `item_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `item_price`, `item_description`, `created_at`, `updated_at`) VALUES
(1, 'Additional Guest', 200.00, 'Fee for extra guest beyond room capacity', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(2, 'Towel', 50.00, 'Extra towel', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(3, 'Soap', 25.00, 'Extra soap', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(4, 'Shampoo', 30.00, 'Extra shampoo', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(5, 'Toothbrush', 20.00, 'Extra toothbrush', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(6, 'Toothpaste', 25.00, 'Extra toothpaste', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(7, 'Slippers', 40.00, 'Extra slippers', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(8, 'Extra Bed', 200.00, 'Additional bed in room', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(9, 'Laundry', 150.00, 'Per load of laundry service', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(10, 'Room Service', 100.00, 'Room service fee', '2025-05-08 15:08:10', '2025-05-08 15:08:10'),
(11, 'Late Checkout', 300.00, 'Extended checkout time fee', '2025-05-08 15:08:10', '2025-05-08 15:08:10');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `room_type_id` int NOT NULL,
  `description` text,
  `status` enum('available','occupied','maintenance','reserved') NOT NULL DEFAULT 'available',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `room_type_id` (`room_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_number`, `room_type_id`, `description`, `status`, `is_active`) VALUES
(5, '101', 1, 'Standard Matrimonial Room - Ground Floor', 'available', 1),
(6, '102', 1, 'Standard Matrimonial Room - Ground Floor', 'available', 1),
(7, '103', 1, 'Standard Matrimonial Room - Ground Floor', 'available', 1),
(8, '104', 1, 'Standard Matrimonial Room - Ground Floor', 'available', 1),
(9, '201', 2, 'Twin Matrimonial Room - Ground Floor', 'available', 1),
(10, '202', 2, 'Twin Matrimonial Room - Ground Floor', 'available', 1),
(11, '203', 2, 'Twin Matrimonial Room - Ground Floor', 'available', 1),
(12, '301', 3, 'Family Deluxe Twin Room - Ground Floor', 'available', 1),
(13, '302', 3, 'Family Deluxe Twin Room - Ground Floor', 'available', 1);

-- --------------------------------------------------------

--
-- Table structure for table `room_gallery`
--

DROP TABLE IF EXISTS `room_gallery`;
CREATE TABLE IF NOT EXISTS `room_gallery` (
  `gallery_id` int NOT NULL AUTO_INCREMENT,
  `room_type_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(100) DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`gallery_id`),
  KEY `room_type_id` (`room_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `room_gallery`
--

INSERT INTO `room_gallery` (`gallery_id`, `room_type_id`, `image_path`, `caption`, `display_order`, `created_at`) VALUES
(1, 1, 'room_1_gallery_1745729099_4227.jpg', NULL, 0, '2025-04-27 12:44:59'),
(2, 1, 'room_1_gallery_1745729099_4325.jpg', NULL, 1, '2025-04-27 12:44:59'),
(3, 1, 'room_1_gallery_1745729099_4559.jpg', NULL, 2, '2025-04-27 12:44:59');

-- --------------------------------------------------------

--
-- Table structure for table `room_media`
--

DROP TABLE IF EXISTS `room_media`;
CREATE TABLE IF NOT EXISTS `room_media` (
  `media_id` int NOT NULL AUTO_INCREMENT,
  `room_type_id` int NOT NULL,
  `panorama_image` varchar(255) DEFAULT NULL,
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`media_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `room_media`
--

INSERT INTO `room_media` (`media_id`, `room_type_id`, `panorama_image`, `last_updated`) VALUES
(1, 1, 'room_1_panorama_1745729290_7593.jpg', '2025-04-27 12:48:10');

-- --------------------------------------------------------

--
-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
CREATE TABLE IF NOT EXISTS `room_types` (
  `room_type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  `description` text,
  `floor_type` int NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `capacity` int NOT NULL,
  `amenities` text,
  `image_path` text,
  PRIMARY KEY (`room_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`room_type_id`, `type_name`, `description`, `floor_type`, `base_price`, `capacity`, `amenities`, `image_path`) VALUES
(1, 'STANDARD MATRIMONIAL', '❗Children 12 years old and below are free of charge when sharing room with an adult.\r\n❗rooms exclusive for breakfast.\r\n❗Check-in time is at 2:00 PM. Check-out time is at 1:00 PM. Early arrivals (12:00 MN to 5:00 AM) are still charge the whole day rate.', 1, 950.00, 4, 'BATH SET, TV, WIFI', 'public/room_images/room_1745649134_9131.jpg'),
(2, 'TWIN MATRIMONIAL', 'Maximum of 5 pax add on 1 extra foam', 1, 1300.00, 5, 'BATH, TV, WIFI', 'public/room_images/room_1745649176_1835.jpg'),
(3, 'FAMILY DELUXE TWIN', 'Maximum of 7pax additional 2extra foam', 1, 1800.00, 7, 'BATH, WIFI, TV', 'public/room_images/room_1745649215_7638.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `room_id` int NOT NULL,
  `user_id` int NOT NULL,
  `reference_no` text,
  `payment_screenshot` text,
  `amount` int NOT NULL,
  `extra_pay` double(10,2) DEFAULT '0.00',
  `receipt_no` text NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `booking_id`, `room_id`, `user_id`, `reference_no`, `payment_screenshot`, `amount`, `extra_pay`, `receipt_no`, `created_at`) VALUES
(1, 1, 5, 2, 'testGCASH0123', 'uploads/payment_screenshots/payment_1_20250508_144731_de88d8b3.png', 1900, 320.00, '20250508001', '2025-05-08 14:47:31'),
(2, 3, 5, 2, 'gacshh213', 'uploads/payment_screenshots/payment_3_20250509_131027_35ca045d.png', 950, 0.00, '20250509002', '2025-05-09 13:10:27'),
(3, 4, 5, 2, '123123', 'uploads/payment_screenshots/payment_4_20250509_131500_c21741ba.png', 950, 150.00, '20250509003', '2025-05-09 13:15:00'),
(4, 5, 9, 2, 'test543', 'uploads/payment_screenshots/payment_5_20250509_132320_d380e8a6.png', 5200, 0.00, '20250509004', '2025-05-09 13:23:20'),
(5, 9, 5, 2, 'test123444', 'uploads/payment_screenshots/payment_9_20250509_141028_571d0dcc.png', 1805, 0.00, '20250509005', '2025-05-09 14:10:28'),
(6, 10, 9, 2, '123123123', 'uploads/payment_screenshots/payment_10_20250509_141845_c5d1efdb.png', 6500, 0.00, '20250509006', '2025-05-09 14:18:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `user_type` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
    `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `lock_expires` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone_number`, `user_type`, `profile_image`, `created_at`, `updated_at`, `last_login`, `is_locked`, `lock_expires`) VALUES
(1, 'admin', 'admin@gmail.com', '202cb962ac59075b964b07152d234b70', 'pcc', 'admin', '+63 9123456789', 'admin', NULL, '2025-05-02 09:13:12', '2025-05-02 09:13:12', NULL, 0, NULL),
(2, 'john', 'john@doe.com', '202cb962ac59075b964b07152d234b70', 'john', 'doe', '09123646786', 'customer', NULL, '2025-05-08 13:32:56', '2025-05-08 13:32:56', NULL, 0, NULL),
(3, 'codingriccki', 'codingriccki@gmail.com', 'f1fbeee4c2e74a41808d57fc6431a2f7', 'riccki', 'rejee', '09123456789', 'customer', NULL, '2025-05-10 06:20:14', '2025-05-10 06:20:14', NULL, 0, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;