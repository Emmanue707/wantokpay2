-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 21, 2025 at 05:22 PM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u787474055_wantokpay2`
--

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `link_token` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `link_token`, `amount`, `is_read`, `created_at`) VALUES
(1, 2, 'payment_request', 'New payment request of K20 from ekokele', 'pi_3QjktnDUpDhJwyLX0EDHisaE_secret_uZu0aqN2gZEaKHjSi7dPHj297', 20.00, 0, '2025-01-21 17:05:59');

-- --------------------------------------------------------

--
-- Table structure for table `payment_links`
--

CREATE TABLE `payment_links` (
  `id` int(11) NOT NULL,
  `merchant_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `recipient_username` varchar(50) DEFAULT NULL,
  `link_token` varchar(255) DEFAULT NULL,
  `status` enum('active','used','expired') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_links`
--

INSERT INTO `payment_links` (`id`, `merchant_id`, `amount`, `description`, `recipient_username`, `link_token`, `status`, `created_at`) VALUES
(1, 1, 20.00, '20 kina', NULL, 'pi_3QjksbDUpDhJwyLX1hQCz6w2_secret_IPOQC4TkUHQvlkM3uo59ARnqK', 'active', '2025-01-21 17:04:46'),
(2, 1, 20.00, '20 kina', 'manu', 'pi_3QjktnDUpDhJwyLX0EDHisaE_secret_uZu0aqN2gZEaKHjSi7dPHj297', 'active', '2025-01-21 17:05:59'),
(3, 1, 20.00, '20 kina', NULL, 'pi_3QjkwMDUpDhJwyLX0OZ3CNJ9_secret_8jNGh5oO8V8Moni3kriYKEEaK', 'active', '2025-01-21 17:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL,
  `merchant_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('active','used','expired') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `fee_amount` decimal(10,2) DEFAULT NULL,
  `type` enum('qr_payment','card_payment') NOT NULL,
  `status` enum('completed','failed') DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `sender_id`, `receiver_id`, `amount`, `fee_amount`, `type`, `status`, `created_at`) VALUES
(1, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:02'),
(2, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:03'),
(3, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:03'),
(4, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:04'),
(5, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:05'),
(6, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:05'),
(7, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:06'),
(8, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:06'),
(9, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:07'),
(10, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:07'),
(11, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:08'),
(12, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:08'),
(13, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:09'),
(14, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:10'),
(15, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:10'),
(16, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:11'),
(17, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:11'),
(18, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:12'),
(19, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:12'),
(20, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:13'),
(21, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:13'),
(22, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:14'),
(23, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:14'),
(24, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:15'),
(25, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:15'),
(26, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:16'),
(27, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:16'),
(28, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:17'),
(29, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:17'),
(30, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:18'),
(31, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:19'),
(32, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:19'),
(33, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:20'),
(34, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:20'),
(35, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:21'),
(36, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:21'),
(37, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:22'),
(38, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:22'),
(39, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:23'),
(40, 2, 1, 20.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:26:23'),
(41, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:28:56'),
(42, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:28:56'),
(43, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:28:57'),
(44, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:28:58'),
(45, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:28:58'),
(46, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:28:59'),
(47, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:28:59'),
(48, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:00'),
(49, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:00'),
(50, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:01'),
(51, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:01'),
(52, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:02'),
(53, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:02'),
(54, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:03'),
(55, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:04'),
(56, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:04'),
(57, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:05'),
(58, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:05'),
(59, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:06'),
(60, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:06'),
(61, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:07'),
(62, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:07'),
(63, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:08'),
(64, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:08'),
(65, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:09'),
(66, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:29:09'),
(67, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:54:58'),
(68, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:54:58'),
(69, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:54:59'),
(70, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:54:59'),
(71, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:00'),
(72, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:00'),
(73, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:01'),
(74, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:01'),
(75, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:02'),
(76, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:02'),
(77, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:03'),
(78, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:04'),
(79, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:04'),
(80, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:05'),
(81, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:05'),
(82, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:06'),
(83, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:06'),
(84, 2, 1, 200.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:55:07'),
(85, 2, 1, 3.00, NULL, 'qr_payment', 'completed', '2025-01-17 13:58:14'),
(86, 2, 1, 5.00, NULL, 'qr_payment', 'completed', '2025-01-17 14:01:53'),
(87, 2, 3, 10.00, NULL, 'qr_payment', 'completed', '2025-01-17 15:35:23'),
(88, 2, 3, 7.00, NULL, 'qr_payment', 'completed', '2025-01-17 15:41:07'),
(89, 2, 3, 3.00, NULL, 'qr_payment', 'completed', '2025-01-17 15:41:44'),
(90, 2, 3, 8.00, NULL, 'qr_payment', 'completed', '2025-01-17 16:01:01'),
(91, 2, 3, 8.00, NULL, 'qr_payment', 'completed', '2025-01-17 16:01:22'),
(92, 2, 3, 8.00, NULL, 'qr_payment', 'completed', '2025-01-17 16:03:19'),
(93, 2, 3, 8.00, NULL, 'qr_payment', 'completed', '2025-01-17 16:05:42'),
(94, 2, 3, 6.00, NULL, 'qr_payment', 'completed', '2025-01-17 16:06:27'),
(95, 2, 3, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 16:07:01'),
(96, 2, 3, 100.00, NULL, 'qr_payment', 'completed', '2025-01-17 16:22:11'),
(97, 2, 1, 100.00, NULL, 'qr_payment', 'completed', '2025-01-19 12:48:36'),
(98, 2, 1, 9.00, NULL, 'qr_payment', 'completed', '2025-01-19 13:48:18'),
(99, 2, 1, 9.00, NULL, 'qr_payment', 'completed', '2025-01-19 13:48:19'),
(100, 2, 1, 9.00, NULL, 'qr_payment', 'completed', '2025-01-19 13:53:52'),
(101, 2, 1, 9.00, NULL, 'qr_payment', 'completed', '2025-01-19 13:59:39'),
(102, 2, 1, 9.00, NULL, 'qr_payment', 'completed', '2025-01-19 13:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `has_payment_method` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `stripe_customer_id`, `created_at`, `has_payment_method`) VALUES
(1, 'ekokele', 'ekokele707@gmail.com', '$2y$10$pltMfhJJRjNIpWoJPjKceOU80CZMHTtdzjgfei/uvGy/tK7iLWELW', 'cus_RbSK7nFYRVjlj4', '2025-01-15 15:54:00', 1),
(2, 'manu', 'awardwan@gmail.com', '$2y$10$irnkqkz1/OZzlc34whSIwu/aB4xaA7F45RDBU5K.YNy.Ww/5k401.', 'cus_RbSMPOsTqzMONL', '2025-01-15 16:40:05', 1),
(3, 'naomi', 'kloudone1@gmail.com', '$2y$10$EBb5EDf9vFwzlI5D9QUZkuV3dyD5AgBvUQA9Ku0lE/yZfPFpVJmZ.', 'cus_RbTTCidAQsZgKT', '2025-01-17 14:27:11', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_links`
--
ALTER TABLE `payment_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_links`
--
ALTER TABLE `payment_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payment_links`
--
ALTER TABLE `payment_links`
  ADD CONSTRAINT `payment_links_ibfk_1` FOREIGN KEY (`merchant_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`merchant_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
