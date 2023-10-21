-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2021 at 02:51 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unanipharma`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL,
  `status` tinyint(5) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `username`, `email`, `password`, `image`, `status`, `timestamp`) VALUES
(2, 'Unani Pharma', 'unanipharma', 'info@unanipharma.com', '$2y$10$mj40jM/kQRLg2Y5scscBouzna/e5I2RxyJSeRhbWjdAMm5vhg.h86', '', 1, '2021-02-11 10:08:30'),
(194, 'Umair Farooqui', 'mufazmi', 'info.umairfarooqui@gmail.com', '$2y$10$mj40jM/kQRLg2Y5scscBouzna/e5I2RxyJSeRhbWjdAMm5vhg.h86', '', 1, '2021-02-11 10:09:51'),
(204, 'Social Codia', 'SocialCodia', 'socialcodia@gmail.com', '$2y$10$mj40jM/kQRLg2Y5scscBouzna/e5I2RxyJSeRhbWjdAMm5vhg.h86', '', 1, '2021-01-06 16:50:53');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `created_at`) VALUES
(36, 'SC', '2021-02-11 13:45:10');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
(29, 'SOCIAL', '2021-02-11 13:45:14');

-- --------------------------------------------------------

--
-- Table structure for table `creditors`
--

CREATE TABLE `creditors` (
  `creditorId` int(11) NOT NULL,
  `creditorName` varchar(50) NOT NULL,
  `creditorMobile` varchar(15) NOT NULL,
  `creditorAddress` varchar(700) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `creditors`
--

INSERT INTO `creditors` (`creditorId`, `creditorName`, `creditorMobile`, `creditorAddress`, `timestamp`) VALUES
(14, 'Umair Farooqui', '9867503256', 'Mumbai', '2021-02-11 13:48:02');

-- --------------------------------------------------------

--
-- Table structure for table `creditpayments`
--

CREATE TABLE `creditpayments` (
  `paymentId` int(11) NOT NULL,
  `paymentMode` varchar(100) NOT NULL,
  `paymentDate` datetime NOT NULL,
  `paymentAmount` int(100) NOT NULL,
  `paymentReciever` int(200) NOT NULL,
  `creditId` int(100) NOT NULL,
  `creditorId` int(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `creditpayments`
--

INSERT INTO `creditpayments` (`paymentId`, `paymentMode`, `paymentDate`, `paymentAmount`, `paymentReciever`, `creditId`, `creditorId`, `created_at`) VALUES
(39, 'CASH', '2021-02-11 19:18:02', 500, 194, 57, 14, '2021-02-11 13:48:02'),
(40, 'CASH', '2021-02-11 19:21:06', 50, 194, 57, 14, '2021-02-11 13:51:06');

-- --------------------------------------------------------

--
-- Table structure for table `credits`
--

CREATE TABLE `credits` (
  `creditId` int(11) NOT NULL,
  `creditorId` int(44) NOT NULL,
  `salesId` varchar(500) NOT NULL,
  `creditDescription` varchar(1000) NOT NULL,
  `creditTime` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `credits`
--

INSERT INTO `credits` (`creditId`, `creditorId`, `salesId`, `creditDescription`, `creditTime`, `timestamp`) VALUES
(57, 14, '[\"1142\"]', 'Umair will pay the remaining amount on upcoming friday.', '2021-02-11 19:18:02', '2021-02-11 13:48:02');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `seller_id` int(100) NOT NULL,
  `invoice_date` date NOT NULL,
  `invoice_url` varchar(600) NOT NULL,
  `total_amount` int(100) NOT NULL DEFAULT 0,
  `paid_amount` int(100) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `seller_id`, `invoice_date`, `invoice_url`, `total_amount`, `paid_amount`, `created_at`) VALUES
(90, 'FHC10001', 12, '2021-02-11', 'uploads/invoices/FHC1000126220021180.pdf', 0, 0, '2021-02-11 13:49:20');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `location_name`, `created_at`) VALUES
(23, 'A1', '2020-12-21 12:31:10'),
(24, 'A2', '2020-12-21 12:31:14'),
(25, 'A3', '2020-12-21 12:31:18'),
(26, 'A4', '2020-12-21 12:31:21'),
(27, 'D1', '2021-01-01 15:39:11'),
(28, 'D2', '2021-01-01 15:39:13'),
(29, 'D3', '2021-01-01 15:39:16'),
(30, 'B1', '2021-01-02 14:33:37'),
(31, 'B2', '2021-01-02 14:33:41'),
(32, 'C1', '2021-01-04 08:12:41'),
(33, 'D3', '2021-01-04 08:12:51'),
(34, 'D4', '2021-01-04 08:12:53'),
(35, 'E1', '2021-01-04 08:12:58'),
(36, 'E2', '2021-01-04 08:13:00'),
(37, 'F1', '2021-01-04 08:13:02'),
(38, 'F2', '2021-01-04 08:13:06'),
(39, 'F3', '2021-01-04 08:13:09'),
(40, 'C2', '2021-01-07 14:24:33'),
(42, 'UNDEFINED', '2021-01-11 15:48:04'),
(43, 'D5', '2021-01-17 11:26:32'),
(44, 'D6', '2021-01-17 11:26:34'),
(50, 'A0', '2021-01-18 16:17:14'),
(51, 'B0', '2021-01-18 16:17:17'),
(52, 'C0', '2021-01-18 16:17:20'),
(53, 'D0', '2021-01-18 16:17:22'),
(54, 'E0', '2021-01-18 16:17:24'),
(55, 'F0', '2021-01-18 16:17:26'),
(56, 'G0', '2021-01-19 15:55:03'),
(57, 'G1', '2021-01-19 15:55:06'),
(58, 'G2', '2021-01-19 15:55:08'),
(59, 'G3', '2021-01-19 15:55:11'),
(60, 'G4', '2021-01-19 15:55:13'),
(61, 'G5', '2021-01-19 15:55:15'),
(62, 'G6', '2021-01-19 15:55:22'),
(63, 'ALM0', '2021-01-23 15:40:57'),
(64, 'ALM1', '2021-01-23 15:41:03'),
(65, 'ALM2', '2021-01-23 17:03:20'),
(66, 'ALM3', '2021-01-23 17:03:29'),
(67, 'ALM4', '2021-01-23 17:03:32'),
(68, 'ALM5', '2021-01-23 17:03:34'),
(69, 'ALM6', '2021-01-23 17:03:35'),
(71, 'TBL1', '2021-01-25 12:18:15'),
(72, 'TBL2', '2021-01-25 12:18:17'),
(73, 'TBL3', '2021-01-25 12:18:20'),
(74, 'TBL4', '2021-01-25 12:18:23'),
(75, 'TBL5', '2021-01-26 10:07:12'),
(76, 'TBL6', '2021-01-26 10:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `payment_mode` varchar(100) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_amount` int(100) NOT NULL,
  `payment_receiver` int(200) NOT NULL,
  `invoice_number` varchar(200) NOT NULL,
  `seller_id` int(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(200) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `size_id` int(200) NOT NULL,
  `brand_id` int(200) NOT NULL,
  `product_price` int(200) NOT NULL,
  `product_quantity` int(200) NOT NULL,
  `location_id` int(200) NOT NULL,
  `product_manufacture` date NOT NULL,
  `product_expire` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `size_id`, `brand_id`, `product_price`, `product_quantity`, `location_id`, `product_manufacture`, `product_expire`, `created_at`) VALUES
(731, 29, 'APPLICATOIN DEVELOPMENT', 74, 36, 600, 300, 23, '2021-01-01', '2024-03-01', '2021-02-11 13:46:11');

-- --------------------------------------------------------

--
-- Table structure for table `products_record`
--

CREATE TABLE `products_record` (
  `product_id` int(11) NOT NULL,
  `category_id` int(200) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `size_id` int(200) NOT NULL,
  `brand_id` int(200) NOT NULL,
  `product_price` int(200) NOT NULL,
  `product_quantity` int(200) NOT NULL,
  `location_id` int(200) NOT NULL,
  `product_manufacture` date NOT NULL,
  `product_expire` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products_record`
--

INSERT INTO `products_record` (`product_id`, `category_id`, `product_name`, `size_id`, `brand_id`, `product_price`, `product_quantity`, `location_id`, `product_manufacture`, `product_expire`, `created_at`) VALUES
(731, 29, 'APPLICATOIN DEVELOPMENT', 74, 36, 600, 300, 23, '2021-01-01', '2024-03-01', '2021-02-11 13:46:11');

-- --------------------------------------------------------

--
-- Table structure for table `quantities`
--

CREATE TABLE `quantities` (
  `quantity_id` int(11) NOT NULL,
  `quantity` int(200) NOT NULL,
  `product_id` int(200) NOT NULL,
  `size_id` int(200) NOT NULL,
  `brand_id` int(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `seller_id` int(11) NOT NULL,
  `seller_fname` varchar(50) NOT NULL,
  `seller_lname` varchar(50) NOT NULL,
  `seller_email` varchar(50) NOT NULL,
  `seller_contact` varchar(12) NOT NULL,
  `seller_contact_1` varchar(12) NOT NULL,
  `seller_image` varchar(100) NOT NULL,
  `seller_address` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sellers`
--

INSERT INTO `sellers` (`seller_id`, `seller_fname`, `seller_lname`, `seller_email`, `seller_contact`, `seller_contact_1`, `seller_image`, `seller_address`) VALUES
(12, 'Umair', 'Farooqui', 'info.umairfarooqui@gmail.com', '9867503256', '', 'uploads/60046852a46be.png', '402, B Wing, Madina Park Building, Khardi Village Road, Kausa, Mumbra');

-- --------------------------------------------------------

--
-- Table structure for table `sellers_sells`
--

CREATE TABLE `sellers_sells` (
  `sellers_sell_id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `product_id` int(200) NOT NULL,
  `sell_quantity` int(200) NOT NULL,
  `sell_discount` float NOT NULL,
  `sell_price` int(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sellers_sells`
--

INSERT INTO `sellers_sells` (`sellers_sell_id`, `invoice_number`, `product_id`, `sell_quantity`, `sell_discount`, `sell_price`, `created_at`, `updated_at`) VALUES
(29, 'FHC10001', 731, 10, 40, 3600, '2021-02-11 13:49:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sells`
--

CREATE TABLE `sells` (
  `sell_id` int(11) NOT NULL,
  `product_id` int(200) NOT NULL,
  `sell_quantity` int(200) NOT NULL,
  `sell_discount` int(200) NOT NULL DEFAULT 0,
  `sell_price` int(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sells`
--

INSERT INTO `sells` (`sell_id`, `product_id`, `sell_quantity`, `sell_discount`, `sell_price`, `created_at`, `updated_at`) VALUES
(1141, 731, 1, 0, 600, '2021-02-11 13:47:01', NULL),
(1142, 731, 1, 0, 600, '2021-02-11 13:47:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `size_id` int(11) NOT NULL,
  `size_name` varchar(200) NOT NULL,
  `size_type` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`size_id`, `size_name`, `size_type`, `created_at`) VALUES
(14, '25ML', 0, '2020-12-28 12:39:36'),
(15, '50ML', 0, '2020-12-28 12:39:39'),
(16, '100ML', 0, '2020-12-28 12:39:42'),
(17, '200ML', 0, '2020-12-28 12:39:46'),
(18, '500ML', 0, '2020-12-28 12:39:52'),
(19, '300ML', 0, '2021-01-01 15:35:49'),
(20, '40 TABLET', 0, '2021-01-02 16:03:01'),
(21, '50 TABLET', 0, '2021-01-02 16:06:34'),
(22, '100 TABLET', 0, '2021-01-02 16:07:43'),
(23, '125GM', 0, '2021-01-04 08:03:41'),
(24, '150GM', 0, '2021-01-04 08:34:44'),
(25, '300GM', 0, '2021-01-05 11:23:25'),
(26, '200GM', 0, '2021-01-06 12:10:34'),
(27, '250GM', 0, '2021-01-06 14:50:26'),
(28, '500GM', 0, '2021-01-06 16:20:08'),
(29, '50 PILLS', 0, '2021-01-07 14:23:05'),
(30, '100 pills', 0, '2021-01-09 16:17:23'),
(31, '25 PILLS', 0, '2021-01-10 09:29:47'),
(32, '15 PILLS', 0, '2021-01-10 09:33:32'),
(33, '10 PILLS', 0, '2021-01-10 12:02:42'),
(34, '5 PILLS', 0, '2021-01-10 12:18:18'),
(35, '20 PILLS', 0, '2021-01-10 12:44:47'),
(36, '30 PILLS', 0, '2021-01-10 13:23:13'),
(37, '40 PILLS', 0, '2021-01-10 16:01:21'),
(38, '20ML', 0, '2021-01-11 12:15:25'),
(39, '50 CAPSULE', 0, '2021-01-11 12:21:34'),
(40, '50GM', 0, '2021-01-11 15:49:53'),
(41, '100GM', 0, '2021-01-11 15:49:57'),
(42, '60GM', 0, '2021-01-12 14:58:14'),
(43, '75GM', 0, '2021-01-12 16:19:02'),
(44, '60 PILLS', 0, '2021-01-13 10:53:01'),
(45, '120 PILLS', 0, '2021-01-13 10:54:54'),
(46, '60 TABLET', 0, '2021-01-13 11:05:49'),
(47, '60 CAPSULE', 0, '2021-01-18 13:29:01'),
(48, '80 TABLET', 0, '2021-01-18 13:47:09'),
(49, '33GM', 0, '2021-01-18 14:12:31'),
(50, '280GM', 0, '2021-01-18 16:23:00'),
(51, '6GMS', 0, '2021-01-19 16:13:24'),
(52, '80 PILLS', 0, '2021-01-22 11:56:33'),
(53, '380GM', 0, '2021-01-22 14:35:43'),
(54, '380ML', 0, '2021-01-23 16:25:51'),
(55, '125ML', 0, '2021-01-23 16:32:41'),
(61, '1kgs', 0, '2021-01-24 08:05:10'),
(62, '10ML', 0, '2021-01-24 11:21:18'),
(63, '10GM', 0, '2021-01-24 12:25:17'),
(64, 'TBL1', 0, '2021-01-25 12:18:01'),
(65, 'TBL2', 0, '2021-01-25 12:18:05'),
(66, 'TBL3', 0, '2021-01-25 12:18:08'),
(67, '400 PILLS', 0, '2021-01-25 13:32:46'),
(68, '15ML', 0, '2021-01-25 15:08:12'),
(69, '20GM', 0, '2021-01-25 16:09:55'),
(70, '25GM', 0, '2021-01-25 16:13:46'),
(71, '30GM', 0, '2021-01-29 16:18:09'),
(72, '15gm', 0, '2021-02-05 15:07:01'),
(73, '250ML', 0, '2021-02-06 15:02:01'),
(74, '2PC', 0, '2021-02-11 13:45:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `creditors`
--
ALTER TABLE `creditors`
  ADD PRIMARY KEY (`creditorId`);

--
-- Indexes for table `creditpayments`
--
ALTER TABLE `creditpayments`
  ADD PRIMARY KEY (`paymentId`);

--
-- Indexes for table `credits`
--
ALTER TABLE `credits`
  ADD PRIMARY KEY (`creditId`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `products_record`
--
ALTER TABLE `products_record`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `quantities`
--
ALTER TABLE `quantities`
  ADD PRIMARY KEY (`quantity_id`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`seller_id`);

--
-- Indexes for table `sellers_sells`
--
ALTER TABLE `sellers_sells`
  ADD PRIMARY KEY (`sellers_sell_id`);

--
-- Indexes for table `sells`
--
ALTER TABLE `sells`
  ADD PRIMARY KEY (`sell_id`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`size_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `creditors`
--
ALTER TABLE `creditors`
  MODIFY `creditorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `creditpayments`
--
ALTER TABLE `creditpayments`
  MODIFY `paymentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `credits`
--
ALTER TABLE `credits`
  MODIFY `creditId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=732;

--
-- AUTO_INCREMENT for table `products_record`
--
ALTER TABLE `products_record`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=732;

--
-- AUTO_INCREMENT for table `quantities`
--
ALTER TABLE `quantities`
  MODIFY `quantity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sellers_sells`
--
ALTER TABLE `sellers_sells`
  MODIFY `sellers_sell_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `sells`
--
ALTER TABLE `sells`
  MODIFY `sell_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1143;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
