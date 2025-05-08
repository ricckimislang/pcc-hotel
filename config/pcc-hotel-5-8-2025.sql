-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 08, 2025 at 01:40 PM
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(13, '302', 3, 'Family Deluxe Twin Room - Ground Floor', 'available', 1),
(14, '401', 4, 'Standard Matrimonial Room - Second Floor', 'available', 1),
(15, '402', 4, 'Standard Matrimonial Room - Second Floor', 'available', 1),
(16, '403', 4, 'Standard Matrimonial Room - Second Floor', 'available', 1);

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
(3, 'FAMILY DELUXE TWIN', 'Maximum of 7pax additional 2extra foam', 1, 1800.00, 7, 'BATH, WIFI, TV', 'public/room_images/room_1745649215_7638.jpg'),
(4, 'TWIN STANDARD MATRIMONIAL 2F', 'Maximum of 6pax additional 2extra foam', 2, 1500.00, 6, 'WIFI, SMART-TV, BATH', 'public/room_images/room_1745649535_3856.jpg');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone_number`, `user_type`, `profile_image`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', 'admin@gmail.com', '202cb962ac59075b964b07152d234b70', 'pcc', 'admin', '+63 9123456789', 'admin', NULL, '2025-05-02 09:13:12', '2025-05-02 09:13:12', NULL),
(2, 'john', 'john@doe.com', '202cb962ac59075b964b07152d234b70', 'john', 'doe', '09123646786', 'customer', NULL, '2025-05-08 13:32:56', '2025-05-08 13:32:56', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
