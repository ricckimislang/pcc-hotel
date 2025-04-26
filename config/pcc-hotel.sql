-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 26, 2025 at 03:57 AM
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `additional_items`
--

INSERT INTO `additional_items` (`item_id`, `transaction_id`, `item_name`, `item_price`, `quantity`, `subtotal`, `created_at`) VALUES
(1, 1, 'Addtional 1 hr', 500.00, 1, 500.00, '2025-04-25 15:41:52');

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
  `total_price` decimal(10,2) NOT NULL,
  `special_requests` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `booking_status` enum('pending','confirmed','checked_in','checked_out','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `payment_status` set('pending','paid','partial') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `booking_source` varchar(50) DEFAULT 'website',
  PRIMARY KEY (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `booking_date`, `guests_count`, `total_price`, `special_requests`, `booking_status`, `payment_status`, `booking_source`) VALUES
(1, 3, 1, '2025-04-26', '2025-04-30', '2025-04-25 15:41:52', 3, 3800.00, 'may noodles sana naka hain', 'checked_out', 'paid', 'website');

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
(1, 3, 1, 20);

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_number`, `room_type_id`, `description`, `status`, `is_active`) VALUES
(1, '001', 1, '❗Children 12 years old and below are free of charge when sharing room with an adult.\r\n\r\n❗rooms exclusive for breakfast.\r\n\r\n❗Check-in time is at 2:00 PM. Check-out time is at 1:00 PM. Early arrivals (12:00 MN to 5:00 AM) are still charge the whole day rate.', 'available', 1),
(2, '002', 2, '123', 'available', 1),
(3, '003', 3, '123', 'available', 1),
(4, '20', 6, 'FUNCTION', 'available', 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `room_gallery`
--

INSERT INTO `room_gallery` (`gallery_id`, `room_type_id`, `image_path`, `caption`, `display_order`, `created_at`) VALUES
(1, 1, 'room_1_gallery_1745594776_6665.jpg', NULL, 0, '2025-04-25 23:26:16'),
(2, 2, 'room_2_gallery_1745594790_5898.jpg', NULL, 0, '2025-04-25 23:26:30'),
(3, 2, 'room_2_gallery_1745594790_7738.jpg', NULL, 1, '2025-04-25 23:26:30'),
(4, 2, 'room_2_gallery_1745594790_4713.jpg', NULL, 2, '2025-04-25 23:26:30'),
(5, 3, 'room_3_gallery_1745594809_3472.jpg', NULL, 0, '2025-04-25 23:26:49'),
(6, 3, 'room_3_gallery_1745594809_8917.jpg', NULL, 1, '2025-04-25 23:26:49'),
(7, 3, 'room_3_gallery_1745594809_6825.jpg', NULL, 2, '2025-04-25 23:26:49');

-- --------------------------------------------------------

--
-- Table structure for table `room_media`
--

DROP TABLE IF EXISTS `room_media`;
CREATE TABLE IF NOT EXISTS `room_media` (
  `media_id` int NOT NULL AUTO_INCREMENT,
  `room_id` int NOT NULL,
  `panorama_image` varchar(255) DEFAULT NULL,
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`media_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `room_media`
--

INSERT INTO `room_media` (`media_id`, `room_id`, `panorama_image`, `last_updated`) VALUES
(1, 1, 'room_1_panorama_1745594832_1041.jpg', '2025-04-25 23:27:12'),
(2, 2, NULL, '2025-04-25 23:26:30'),
(3, 3, NULL, '2025-04-25 23:26:49');

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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`room_type_id`, `type_name`, `description`, `floor_type`, `base_price`, `capacity`, `amenities`, `image_path`) VALUES
(1, 'STANDARD MATRIMONIAL', 'GOOD FOR 2 Persons.\r\n❗Children 12 years old and below are free of charge when sharing room with an adult.\r\n\r\n❗rooms exclusive for breakfast.\r\n\r\n❗Check-in time is at 2:00 PM. Check-out time is at 1:00 PM. Early arrivals (12:00 MN to 5:00 AM) are still charge the whole day rate.', 1, 950.00, 4, 'BATH AMENITIES', 'public/room_images/room_1745591300_7054.jpg'),
(2, 'TWIN MATRIMONIAL', 'Maximum of 5 pax add on 1 extra foam', 1, 1300.00, 5, 'BATH AMENITIES SET, TV, WIFI', 'public/room_images/room_1745573557_9209.jpg'),
(3, 'FAMILY DELUXE TWIN', 'Maximum of 7pax additional 2extra foam', 1, 1800.00, 7, 'BATH AMENITIES, TV, WIFI', 'public/room_images/room_1745573226_1512.jpg'),
(7, 'TEST', '123', 1, 123.00, 1, '123', 'public/room_images/room_1745573341_2456.jpg'),
(5, 'STANDARD MATRIMONIAL', 'Maximum of 4pax additional 2extra foam', 2, 1300.00, 4, 'BATH AMENITIES SET, SMART-TV, WIFI', 'public/room_images/room_1745573523_5405.jpg'),
(6, 'FUNCTION HALL', '5000 for 50 pax, 3500 for 35 pax, 2500 for 25 Pax.', 3, 100.00, 50, 'NONE', NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `booking_id`, `room_id`, `user_id`, `reference_no`, `payment_screenshot`, `amount`, `extra_pay`, `receipt_no`, `created_at`) VALUES
(1, 1, 1, 3, '23123123', 'uploads/payment_screenshots/1745595361_WIN_20250131_00_42_07_Pro.jpg', 3800, 500.00, '001', '2025-04-25 15:36:01');

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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone_number`, `user_type`, `profile_image`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', 'admin@hotel.com', 'password', 'Hotel', 'Admin', '123-456-7890', 'admin', 'admin_profile.jpg', '2025-04-23 02:16:42', '2025-04-23 02:16:42', NULL),
(2, 'manager', 'manager@hotel.com', 'password', 'John', 'Manager', '234-567-8901', 'admin', 'manager_profile.jpg', '2025-04-23 02:16:42', '2025-04-23 02:16:42', NULL),
(3, 'john_doe', 'john@doe.com', 'password', 'John', 'Doe', '345-678-9012', 'customer', 'john_doe.jpg', '2025-04-23 02:16:42', '2025-04-23 02:16:42', NULL),
(4, 'jane_smith', 'jane@smith.com', 'password', 'Jane', 'Smith', '456-789-0123', 'customer', 'jane_smith.jpg', '2025-04-23 02:16:42', '2025-04-23 02:16:42', NULL),
(5, 'bob_johnson', 'bob@johnson.com', 'password', 'Bob', 'Johnson', '567-890-1234', 'customer', 'bob_johnson.jpg', '2025-04-23 02:16:42', '2025-04-23 02:16:42', NULL),
(6, 'alice_brown', 'alice@brown.com', 'password', 'Alice', 'Brown', '678-901-2345', 'customer', 'alice_brown.jpg', '2025-04-23 02:16:42', '2025-04-23 02:16:42', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
