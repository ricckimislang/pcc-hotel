-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 27, 2025 at 01:47 PM
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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `additional_items`
--

INSERT INTO `additional_items` (`item_id`, `transaction_id`, `item_name`, `item_price`, `quantity`, `subtotal`, `created_at`) VALUES
(2, 5, 'Extra bed', 300.00, 1, 300.00, '2025-01-06 07:20:42'),
(3, 8, 'Late checkout (2 hrs)', 400.00, 1, 400.00, '2025-01-09 04:45:18'),
(4, 12, 'Room service - Dinner', 450.00, 1, 450.00, '2025-01-15 11:30:25'),
(5, 16, 'Mini bar items', 250.00, 1, 250.00, '2025-01-20 13:15:43'),
(6, 20, 'Laundry service', 180.00, 1, 180.00, '2025-01-25 08:40:12'),
(7, 24, 'Breakfast for extra guests', 350.00, 2, 700.00, '2025-01-29 00:25:36'),
(8, 28, 'WiFi upgrade package', 150.00, 1, 150.00, '2025-02-03 03:10:54'),
(9, 32, 'Extra towels', 100.00, 2, 200.00, '2025-02-08 01:45:22'),
(10, 36, 'Valentines special dinner', 850.00, 1, 850.00, '2025-02-14 11:20:33'),
(11, 40, 'Business center services', 200.00, 1, 200.00, '2025-02-18 06:55:41'),
(12, 41, 'AdditionalGuest', 200.00, 1, 200.00, '2025-04-27 07:37:33'),
(13, 41, 'Extra Bed', 200.00, 1, 200.00, '2025-04-27 07:37:34');

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
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `booking_date`, `guests_count`, `total_price`, `special_requests`, `booking_status`, `payment_status`, `booking_source`) VALUES
(2, 7, 5, '2025-01-03', '2025-01-05', '2024-12-20 02:15:42', 2, 1900.00, 'Non-smoking room please', 'checked_out', 'paid', 'website'),
(3, 8, 9, '2025-01-04', '2025-01-07', '2024-12-22 07:30:22', 4, 3900.00, 'Extra pillows', 'checked_out', 'paid', 'website'),
(4, 9, 12, '2025-01-05', '2025-01-08', '2024-12-26 01:45:12', 5, 5400.00, 'Late check-in around 9pm', 'checked_out', 'paid', 'website'),
(5, 10, 14, '2025-01-06', '2025-01-09', '2024-12-27 06:20:31', 4, 4500.00, 'Room away from elevator', 'checked_out', 'paid', 'website'),
(6, 11, 6, '2025-01-07', '2025-01-10', '2024-12-28 03:35:18', 3, 2850.00, NULL, 'checked_out', 'paid', 'website'),
(7, 12, 10, '2025-01-08', '2025-01-10', '2025-01-02 08:45:27', 4, 2600.00, 'Extra towels', 'checked_out', 'paid', 'website'),
(8, 13, 13, '2025-01-09', '2025-01-12', '2025-01-03 02:25:42', 6, 5400.00, 'Birthday celebration', 'checked_out', 'paid', 'website'),
(9, 14, 15, '2025-01-10', '2025-01-14', '2025-01-04 01:15:22', 3, 5200.00, NULL, 'checked_out', 'paid', 'website'),
(10, 15, 7, '2025-01-12', '2025-01-15', '2025-01-05 05:40:19', 4, 2850.00, 'Need baby crib', 'checked_out', 'paid', 'website'),
(11, 16, 11, '2025-01-14', '2025-01-17', '2025-01-06 03:30:42', 5, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(12, 17, 12, '2025-01-15', '2025-01-18', '2025-01-07 06:25:33', 7, 5400.00, 'Early check-in if possible', 'checked_out', 'paid', 'website'),
(13, 18, 16, '2025-01-16', '2025-01-19', '2025-01-08 07:35:12', 4, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(14, 19, 8, '2025-01-18', '2025-01-21', '2025-01-10 02:20:45', 2, 2850.00, 'Need parking space', 'checked_out', 'paid', 'website'),
(15, 20, 9, '2025-01-19', '2025-01-20', '2025-01-12 01:30:17', 4, 1300.00, NULL, 'checked_out', 'paid', 'website'),
(16, 21, 13, '2025-01-20', '2025-01-24', '2025-01-13 03:45:23', 7, 7200.00, 'Business trip', 'checked_out', 'paid', 'website'),
(17, 22, 14, '2025-01-21', '2025-01-23', '2025-01-14 08:25:32', 4, 3000.00, NULL, 'checked_out', 'paid', 'website'),
(18, 23, 5, '2025-01-22', '2025-01-25', '2025-01-15 05:15:42', 3, 2850.00, 'First floor preferred', 'checked_out', 'paid', 'website'),
(19, 24, 10, '2025-01-23', '2025-01-26', '2025-01-16 06:40:51', 4, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(20, 25, 12, '2025-01-25', '2025-01-28', '2025-01-17 02:35:22', 6, 5400.00, 'Anniversary stay', 'checked_out', 'paid', 'website'),
(21, 26, 15, '2025-01-26', '2025-01-30', '2025-01-18 01:25:16', 3, 5200.00, NULL, 'checked_out', 'paid', 'website'),
(22, 27, 6, '2025-01-27', '2025-01-29', '2025-01-19 03:20:31', 2, 1900.00, 'Quiet room', 'checked_out', 'paid', 'website'),
(23, 28, 11, '2025-01-28', '2025-01-31', '2025-01-20 07:15:42', 5, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(24, 29, 13, '2025-01-29', '2025-02-02', '2025-01-21 04:30:19', 6, 7200.00, 'Family vacation', 'checked_out', 'paid', 'website'),
(25, 30, 16, '2025-01-30', '2025-02-02', '2025-01-22 02:45:23', 4, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(26, 3, 7, '2025-02-01', '2025-02-04', '2025-01-20 03:25:42', 3, 2850.00, 'Near elevator', 'checked_out', 'paid', 'website'),
(27, 4, 9, '2025-02-02', '2025-02-05', '2025-01-21 06:35:19', 4, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(28, 5, 12, '2025-02-03', '2025-02-07', '2025-01-23 08:20:31', 7, 7200.00, 'Group booking', 'checked_out', 'paid', 'website'),
(29, 6, 14, '2025-02-04', '2025-02-07', '2025-01-24 02:15:42', 3, 4500.00, NULL, 'checked_out', 'paid', 'website'),
(30, 7, 8, '2025-02-06', '2025-02-09', '2025-01-25 01:30:16', 4, 2850.00, 'Early check-in', 'checked_out', 'paid', 'website'),
(31, 8, 10, '2025-02-07', '2025-02-10', '2025-01-26 05:45:22', 5, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(32, 9, 13, '2025-02-08', '2025-02-11', '2025-01-27 07:20:31', 6, 5400.00, 'Wedding guests', 'checked_out', 'paid', 'website'),
(33, 10, 15, '2025-02-09', '2025-02-13', '2025-01-28 03:35:42', 4, 5200.00, NULL, 'checked_out', 'paid', 'website'),
(34, 11, 5, '2025-02-10', '2025-02-12', '2025-01-29 02:25:17', 2, 1900.00, 'Late check-out', 'checked_out', 'paid', 'website'),
(35, 12, 11, '2025-02-12', '2025-02-15', '2025-01-30 06:40:23', 5, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(36, 13, 12, '2025-02-14', '2025-02-17', '2025-02-01 01:15:31', 7, 5400.00, 'Valentine day stay', 'checked_out', 'paid', 'website'),
(37, 14, 16, '2025-02-15', '2025-02-19', '2025-02-02 03:30:19', 4, 5200.00, NULL, 'checked_out', 'paid', 'website'),
(38, 15, 6, '2025-02-16', '2025-02-18', '2025-02-03 05:25:42', 3, 1900.00, 'Non-smoking', 'checked_out', 'paid', 'website'),
(39, 16, 9, '2025-02-17', '2025-02-20', '2025-02-04 07:35:17', 4, 3900.00, NULL, 'checked_out', 'paid', 'website'),
(40, 17, 13, '2025-02-18', '2025-02-22', '2025-02-05 02:20:23', 6, 7200.00, 'Business conference', 'checked_out', 'paid', 'website'),
(41, 4, 1, '2025-04-29', '2025-05-03', '2025-04-08 06:20:19', 3, 3800.00, 'Early check-in', 'pending', 'pending', 'website'),
(42, 5, 5, '2025-04-30', '2025-05-04', '2025-04-09 03:35:42', 5, 5200.00, NULL, 'pending', 'pending', 'website'),
(43, 6, 5, '2025-05-01', '2025-05-05', '2025-04-10 05:50:17', 6, 7200.00, 'Family reunion', 'pending', 'pending', 'website'),
(44, 7, 4, '2025-05-02', '2025-05-06', '2025-04-11 02:15:23', 4, 5200.00, NULL, 'pending', 'pending', 'website'),
(45, 8, 3, '2025-05-03', '2025-05-07', '2025-04-12 01:40:31', 3, 3800.00, 'Late check-out', 'pending', 'pending', 'website'),
(46, 9, 2, '2025-05-04', '2025-05-08', '2025-04-13 06:25:19', 5, 5200.00, NULL, 'pending', 'pending', 'website'),
(47, 10, 9, '2025-05-05', '2025-05-09', '2025-04-14 08:10:42', 7, 7200.00, 'Birthday celebration', 'pending', 'pending', 'website'),
(48, 5, 8, '2025-05-06', '2025-05-10', '2025-04-15 03:55:17', 4, 5200.00, NULL, 'pending', 'pending', 'website'),
(49, 3, 5, '2025-04-27', '2025-04-30', '2025-04-27 07:37:34', 4, 2850.00, 'no smoking room', 'checked_out', 'paid', 'website');

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
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customer_profiles`
--

INSERT INTO `customer_profiles` (`customer_id`, `user_id`, `frequent_guest`, `loyal_points`) VALUES
(2, 7, 0, 5),
(3, 8, 0, 0),
(4, 9, 1, 15),
(5, 10, 0, 10),
(6, 11, 0, 5),
(7, 12, 1, 25),
(8, 13, 0, 0),
(9, 14, 0, 5),
(10, 15, 1, 30),
(11, 16, 0, 10),
(12, 17, 0, 5),
(13, 18, 0, 0),
(14, 19, 0, 5),
(15, 20, 1, 20),
(16, 21, 0, 5),
(17, 22, 0, 0),
(18, 23, 1, 15),
(19, 24, 0, 10),
(20, 25, 0, 5),
(21, 26, 1, 25),
(22, 27, 0, 0),
(23, 28, 0, 5),
(24, 29, 1, 20),
(25, 30, 0, 10),
(26, 3, 1, 20);

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
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `booking_id`, `room_id`, `user_id`, `reference_no`, `payment_screenshot`, `amount`, `extra_pay`, `receipt_no`, `created_at`) VALUES
(2, 2, 5, 7, 'REF25010301', 'uploads/payment_screenshots/pay_jan_2025_01.jpg', 1900, 0.00, '002', '2025-01-03 01:30:12'),
(3, 3, 9, 8, 'REF25010402', 'uploads/payment_screenshots/pay_jan_2025_02.jpg', 3900, 0.00, '003', '2025-01-04 03:25:34'),
(4, 4, 12, 9, 'REF25010503', 'uploads/payment_screenshots/pay_jan_2025_03.jpg', 5400, 0.00, '004', '2025-01-05 02:15:45'),
(5, 5, 14, 10, 'REF25010604', 'uploads/payment_screenshots/pay_jan_2025_04.jpg', 4500, 0.00, '005', '2025-01-06 06:20:23'),
(6, 6, 6, 11, 'REF25010705', 'uploads/payment_screenshots/pay_jan_2025_05.jpg', 2850, 0.00, '006', '2025-01-07 01:45:18'),
(7, 7, 10, 12, 'REF25010806', 'uploads/payment_screenshots/pay_jan_2025_06.jpg', 2600, 0.00, '007', '2025-01-08 05:30:09'),
(8, 8, 13, 13, 'REF25010907', 'uploads/payment_screenshots/pay_jan_2025_07.jpg', 5400, 0.00, '008', '2025-01-09 03:15:42'),
(9, 9, 15, 14, 'REF25011008', 'uploads/payment_screenshots/pay_jan_2025_08.jpg', 5200, 0.00, '009', '2025-01-10 07:40:23'),
(10, 10, 7, 15, 'REF25011209', 'uploads/payment_screenshots/pay_jan_2025_09.jpg', 2850, 0.00, '010', '2025-01-12 02:25:31'),
(11, 11, 11, 16, 'REF25011410', 'uploads/payment_screenshots/pay_jan_2025_10.jpg', 3900, 0.00, '011', '2025-01-14 01:50:17'),
(12, 12, 12, 17, 'REF25011511', 'uploads/payment_screenshots/pay_jan_2025_11.jpg', 5400, 0.00, '012', '2025-01-15 06:15:28'),
(13, 13, 16, 18, 'REF25011612', 'uploads/payment_screenshots/pay_jan_2025_12.jpg', 3900, 0.00, '013', '2025-01-16 03:30:39'),
(14, 14, 8, 19, 'REF25011813', 'uploads/payment_screenshots/pay_jan_2025_13.jpg', 2850, 0.00, '014', '2025-01-18 07:45:52'),
(15, 15, 9, 20, 'REF25011914', 'uploads/payment_screenshots/pay_jan_2025_14.jpg', 1300, 0.00, '015', '2025-01-19 02:20:15'),
(16, 16, 13, 21, 'REF25012015', 'uploads/payment_screenshots/pay_jan_2025_15.jpg', 7200, 0.00, '016', '2025-01-20 05:35:27'),
(17, 17, 14, 22, 'REF25012116', 'uploads/payment_screenshots/pay_jan_2025_16.jpg', 3000, 0.00, '017', '2025-01-21 01:10:38'),
(18, 18, 5, 23, 'REF25012217', 'uploads/payment_screenshots/pay_jan_2025_17.jpg', 2850, 0.00, '018', '2025-01-22 06:25:49'),
(19, 19, 10, 24, 'REF25012318', 'uploads/payment_screenshots/pay_jan_2025_18.jpg', 3900, 0.00, '019', '2025-01-23 03:40:13'),
(20, 20, 12, 25, 'REF25012519', 'uploads/payment_screenshots/pay_jan_2025_19.jpg', 5400, 0.00, '020', '2025-01-25 07:55:24'),
(21, 21, 15, 26, 'REF25012620', 'uploads/payment_screenshots/pay_jan_2025_20.jpg', 5200, 0.00, '021', '2025-01-26 02:30:35'),
(22, 22, 6, 27, 'REF25012721', 'uploads/payment_screenshots/pay_jan_2025_21.jpg', 1900, 0.00, '022', '2025-01-27 05:45:46'),
(23, 23, 11, 28, 'REF25012822', 'uploads/payment_screenshots/pay_jan_2025_22.jpg', 3900, 0.00, '023', '2025-01-28 01:20:57'),
(24, 24, 13, 29, 'REF25012923', 'uploads/payment_screenshots/pay_jan_2025_23.jpg', 7200, 0.00, '024', '2025-01-29 06:35:08'),
(25, 25, 16, 30, 'REF25013024', 'uploads/payment_screenshots/pay_jan_2025_24.jpg', 3900, 0.00, '025', '2025-01-30 03:50:19'),
(26, 26, 7, 3, 'REF25020125', 'uploads/payment_screenshots/pay_feb_2025_01.jpg', 2850, 0.00, '026', '2025-02-01 07:05:31'),
(27, 27, 9, 4, 'REF25020226', 'uploads/payment_screenshots/pay_feb_2025_02.jpg', 3900, 0.00, '027', '2025-02-02 02:40:42'),
(28, 28, 12, 5, 'REF25020327', 'uploads/payment_screenshots/pay_feb_2025_03.jpg', 7200, 0.00, '028', '2025-02-03 05:55:53'),
(29, 29, 14, 6, 'REF25020428', 'uploads/payment_screenshots/pay_feb_2025_04.jpg', 4500, 0.00, '029', '2025-02-04 01:30:14'),
(30, 30, 8, 7, 'REF25020629', 'uploads/payment_screenshots/pay_feb_2025_05.jpg', 2850, 0.00, '030', '2025-02-06 06:45:25'),
(31, 31, 10, 8, 'REF25020730', 'uploads/payment_screenshots/pay_feb_2025_06.jpg', 3900, 0.00, '031', '2025-02-07 03:10:36'),
(32, 32, 13, 9, 'REF25020831', 'uploads/payment_screenshots/pay_feb_2025_07.jpg', 5400, 0.00, '032', '2025-02-08 07:25:47'),
(33, 33, 15, 10, 'REF25020932', 'uploads/payment_screenshots/pay_feb_2025_08.jpg', 5200, 0.00, '033', '2025-02-09 02:40:58'),
(34, 34, 5, 11, 'REF25021033', 'uploads/payment_screenshots/pay_feb_2025_09.jpg', 1900, 0.00, '034', '2025-02-10 05:55:09'),
(35, 35, 11, 12, 'REF25021234', 'uploads/payment_screenshots/pay_feb_2025_10.jpg', 3900, 0.00, '035', '2025-02-12 01:20:22'),
(36, 36, 12, 13, 'REF25021435', 'uploads/payment_screenshots/pay_feb_2025_11.jpg', 5400, 0.00, '036', '2025-02-14 06:35:33'),
(37, 37, 16, 14, 'REF25021536', 'uploads/payment_screenshots/pay_feb_2025_12.jpg', 5200, 0.00, '037', '2025-02-15 03:50:44'),
(38, 38, 6, 15, 'REF25021637', 'uploads/payment_screenshots/pay_feb_2025_13.jpg', 1900, 0.00, '038', '2025-02-16 07:05:55'),
(39, 39, 9, 16, 'REF25021738', 'uploads/payment_screenshots/pay_feb_2025_14.jpg', 3900, 0.00, '039', '2025-02-17 02:21:16'),
(40, 40, 13, 17, 'REF25021839', 'uploads/payment_screenshots/pay_feb_2025_15.jpg', 7200, 0.00, '040', '2025-02-18 05:35:27'),
(41, 49, 5, 3, '13123123', 'uploads/payment_screenshots/payment_49_20250427_073249_4e45834b.png', 2850, 400.00, '20250427041', '2025-04-27 07:32:49');

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
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone_number`, `user_type`, `profile_image`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', 'admin@hotel.com', 'password', 'Hotel', 'Admin', '123-456-7890', 'admin', 'admin_profile.jpg', '2025-04-22 18:16:42', '2025-04-22 18:16:42', NULL),
(2, 'manager', 'manager@hotel.com', 'password', 'John', 'Manager', '234-567-8901', 'admin', 'manager_profile.jpg', '2025-04-22 18:16:42', '2025-04-22 18:16:42', NULL),
(3, 'john_doe', 'john@doe.com', 'password', 'John', 'Doe', '345-678-9012', 'customer', 'john_doe.jpg', '2025-04-22 18:16:42', '2025-04-22 18:16:42', NULL),
(4, 'jane_smith', 'jane@smith.com', 'password', 'Jane', 'Smith', '456-789-0123', 'customer', 'jane_smith.jpg', '2025-04-22 18:16:42', '2025-04-22 18:16:42', NULL),
(5, 'bob_johnson', 'bob@johnson.com', 'password', 'Bob', 'Johnson', '567-890-1234', 'customer', 'bob_johnson.jpg', '2025-04-22 18:16:42', '2025-04-22 18:16:42', NULL),
(6, 'alice_brown', 'alice@brown.com', 'password', 'Alice', 'Brown', '678-901-2345', 'customer', 'alice_brown.jpg', '2025-04-22 18:16:42', '2025-04-22 18:16:42', NULL),
(7, 'maria_garcia', 'maria@garcia.com', 'password', 'Maria', 'Garcia', '123-111-2222', 'customer', 'maria_garcia.jpg', '2025-01-02 00:30:42', '2025-01-02 00:30:42', NULL),
(8, 'james_wilson', 'james@wilson.com', 'password', 'James', 'Wilson', '123-222-3333', 'customer', 'james_wilson.jpg', '2025-01-03 02:15:42', '2025-01-03 02:15:42', NULL),
(9, 'linda_martin', 'linda@martin.com', 'password', 'Linda', 'Martin', '123-333-4444', 'customer', 'linda_martin.jpg', '2025-01-04 01:20:42', '2025-01-04 01:20:42', NULL),
(10, 'david_wang', 'david@wang.com', 'password', 'David', 'Wang', '123-444-5555', 'customer', 'david_wang.jpg', '2025-01-05 06:45:42', '2025-01-05 06:45:42', NULL),
(11, 'sarah_jones', 'sarah@jones.com', 'password', 'Sarah', 'Jones', '123-555-6666', 'customer', 'sarah_jones.jpg', '2025-01-06 03:30:42', '2025-01-06 03:30:42', NULL),
(12, 'michael_lee', 'michael@lee.com', 'password', 'Michael', 'Lee', '123-666-7777', 'customer', 'michael_lee.jpg', '2025-01-07 08:20:42', '2025-01-07 08:20:42', NULL),
(13, 'jennifer_kim', 'jennifer@kim.com', 'password', 'Jennifer', 'Kim', '123-777-8888', 'customer', 'jennifer_kim.jpg', '2025-01-08 05:10:42', '2025-01-08 05:10:42', NULL),
(14, 'robert_chen', 'robert@chen.com', 'password', 'Robert', 'Chen', '123-888-9999', 'customer', 'robert_chen.jpg', '2025-01-09 04:25:42', '2025-01-09 04:25:42', NULL),
(15, 'lisa_rodriguez', 'lisa@rodriguez.com', 'password', 'Lisa', 'Rodriguez', '123-999-0000', 'customer', 'lisa_rodriguez.jpg', '2025-01-10 02:40:42', '2025-01-10 02:40:42', NULL),
(16, 'william_brown', 'william@brown.com', 'password', 'William', 'Brown', '123-101-2020', 'customer', 'william_brown.jpg', '2025-01-11 01:15:42', '2025-01-11 01:15:42', NULL),
(17, 'patricia_davis', 'patricia@davis.com', 'password', 'Patricia', 'Davis', '123-202-3030', 'customer', 'patricia_davis.jpg', '2025-01-12 07:30:42', '2025-01-12 07:30:42', NULL),
(18, 'thomas_miller', 'thomas@miller.com', 'password', 'Thomas', 'Miller', '123-303-4040', 'customer', 'thomas_miller.jpg', '2025-01-13 06:20:42', '2025-01-13 06:20:42', NULL),
(19, 'elizabeth_wilson', 'elizabeth@wilson.com', 'password', 'Elizabeth', 'Wilson', '123-404-5050', 'customer', 'elizabeth_wilson.jpg', '2025-01-14 03:45:42', '2025-01-14 03:45:42', NULL),
(20, 'joseph_taylor', 'joseph@taylor.com', 'password', 'Joseph', 'Taylor', '123-505-6060', 'customer', 'joseph_taylor.jpg', '2025-01-15 02:35:42', '2025-01-15 02:35:42', NULL),
(21, 'mary_anderson', 'mary@anderson.com', 'password', 'Mary', 'Anderson', '123-606-7070', 'customer', 'mary_anderson.jpg', '2025-01-16 01:55:42', '2025-01-16 01:55:42', NULL),
(22, 'charles_thomas', 'charles@thomas.com', 'password', 'Charles', 'Thomas', '123-707-8080', 'customer', 'charles_thomas.jpg', '2025-01-17 08:15:42', '2025-01-17 08:15:42', NULL),
(23, 'karen_jackson', 'karen@jackson.com', 'password', 'Karen', 'Jackson', '123-808-9090', 'customer', 'karen_jackson.jpg', '2025-01-18 05:25:42', '2025-01-18 05:25:42', NULL),
(24, 'steven_white', 'steven@white.com', 'password', 'Steven', 'White', '123-909-0101', 'customer', 'steven_white.jpg', '2025-01-19 04:40:42', '2025-01-19 04:40:42', NULL),
(25, 'nancy_harris', 'nancy@harris.com', 'password', 'Nancy', 'Harris', '123-010-2121', 'customer', 'nancy_harris.jpg', '2025-01-20 03:30:42', '2025-01-20 03:30:42', NULL),
(26, 'edward_martin', 'edward@martin.com', 'password', 'Edward', 'Martin', '123-121-3232', 'customer', 'edward_martin.jpg', '2025-01-21 02:20:42', '2025-01-21 02:20:42', NULL),
(27, 'sandra_thompson', 'sandra@thompson.com', 'password', 'Sandra', 'Thompson', '123-232-4343', 'customer', 'sandra_thompson.jpg', '2025-01-22 01:15:42', '2025-01-22 01:15:42', NULL),
(28, 'christopher_garcia', 'christopher@garcia.com', 'password', 'Christopher', 'Garcia', '123-343-5454', 'customer', 'christopher_garcia.jpg', '2025-01-23 07:45:42', '2025-01-23 07:45:42', NULL),
(29, 'margaret_martinez', 'margaret@martinez.com', 'password', 'Margaret', 'Martinez', '123-454-6565', 'customer', 'margaret_martinez.jpg', '2025-01-24 06:35:42', '2025-01-24 06:35:42', NULL),
(30, 'andrew_robinson', 'andrew@robinson.com', 'password', 'Andrew', 'Robinson', '123-565-7676', 'customer', 'andrew_robinson.jpg', '2025-01-25 05:25:42', '2025-01-25 05:25:42', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
