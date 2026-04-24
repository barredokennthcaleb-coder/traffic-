-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2026 at 05:20 PM
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
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'admin12345', 'admin12345@gmail.com', '$2y$10$zS87ze3e4lYflDrqmeGHluC.F63bO6lfhuseoPd6t.U6j1Ju8woay', '2026-04-24 06:21:39', '2026-04-24 06:21:39');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '20240424000001', 'App\\Database\\Migrations\\CreateAdminsTable', 'default', 'App', 1777040581, 1),
(2, '20240424000002', 'App\\Database\\Migrations\\CreateViolationsTable', 'default', 'App', 1777040581, 1),
(3, '20240424000003', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1777040581, 1),
(4, '20240424000004', 'App\\Database\\Migrations\\UpdateViolationsTable', 'default', 'App', 1777040603, 2),
(5, '20240424000005', 'App\\Database\\Migrations\\CreateViolationTypesTable', 'default', 'App', 1777040603, 2),
(6, '2026-04-24-142535', 'App\\Database\\Migrations\\AddMissingColumnsToViolationsTable', 'default', 'App', 1777040762, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user','traffic_officer') DEFAULT 'user',
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ken', 'ken@gmail.com', '$2y$10$4UK8zNJBHoM.mH5lXkHzMOSs7WVZISIoQsUAbS1qxKvX.DMRwKA0K', 'user', 'active', '2026-04-24 06:24:39', '2026-04-24 06:24:39'),
(2, 'leb', 'leb@gmail.com', '$2y$10$LufdV2oxGUqxjvALE799EuRkjgFzCSKAUA3/AVbsECXHkBjSnT.ai', 'user', 'active', '2026-04-24 06:34:46', '2026-04-24 06:34:46'),
(3, 'kenneth', 'kenneth@gmail.com', '$2y$10$IavKG8WMg194LZSYNCgiY..gE/3lXZQ/niCDaiNxoVrJUT/v5AFdC', 'admin', 'active', '2026-04-24 06:37:44', '2026-04-24 06:37:44'),
(7, 'janrey', 'janrey@gmail.com', '$2y$10$4FaDo5NXqqN3AgICU3t0AegATrP145oUSMfzeEHyT2O30cn/0HGgW', 'traffic_officer', 'active', '2026-04-24 14:19:59', '2026-04-24 14:19:59'),
(8, 'mark', 'mark@gmail.com', '$2y$10$l3PpGRLyGuI5vu5uG1CiauUX3p.45N1TWDpIzouhqLXvhVlaWdQVG', 'user', 'active', '2026-04-24 14:32:40', '2026-04-24 14:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `violations`
--

CREATE TABLE `violations` (
  `id` int(11) UNSIGNED NOT NULL,
  `ticket_id` varchar(50) DEFAULT NULL,
  `driver_name` varchar(255) NOT NULL,
  `license_plate` varchar(20) NOT NULL,
  `violation_type_id` int(11) UNSIGNED DEFAULT NULL,
  `officer_id` int(11) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `violation_type` varchar(255) NOT NULL,
  `penalty_amount` decimal(10,2) NOT NULL,
  `points` int(11) DEFAULT 0,
  `status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending',
  `violation_date` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paid_date` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `violations`
--

INSERT INTO `violations` (`id`, `ticket_id`, `driver_name`, `license_plate`, `violation_type_id`, `officer_id`, `created_by`, `violation_type`, `penalty_amount`, `points`, `status`, `violation_date`, `created_at`, `updated_at`, `paid_date`, `payment_method`, `receipt_number`, `remarks`, `location`, `notes`) VALUES
(1, 'TKT-16ABCBF8', 'mark', '12345', 2, 7, 7, 'Running Red Light', 200.00, 4, 'Paid', '2026-04-24 14:28:33', '2026-04-24 14:28:33', '2026-04-24 14:36:40', '2026-04-24 14:36:40', 'Cash', 'RCP-EBF5CD346B', NULL, 'kabankalan', ''),
(2, 'TKT-C48A473F', 'ken', '789999', 3, 7, 7, 'Illegal Parking', 75.00, 2, 'Pending', '2026-04-24 15:09:00', '2026-04-24 15:09:00', '2026-04-24 15:09:00', NULL, NULL, NULL, NULL, 'kabankalan', '');

-- --------------------------------------------------------

--
-- Table structure for table `violation_types`
--

CREATE TABLE `violation_types` (
  `id` int(11) UNSIGNED NOT NULL,
  `violation_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `fine_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `points` int(3) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `violation_types`
--

INSERT INTO `violation_types` (`id`, `violation_name`, `description`, `fine_amount`, `points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Speeding', 'Exceeding the speed limit', 150.00, 3, 'active', NULL, NULL),
(2, 'Running Red Light', 'Failing to stop at a red light', 200.00, 4, 'active', NULL, NULL),
(3, 'Illegal Parking', 'Parking in prohibited areas', 75.00, 2, 'active', NULL, NULL),
(4, 'Driving Without License', 'Operating a vehicle without valid license', 500.00, 6, 'active', NULL, NULL),
(5, 'Using Mobile While Driving', 'Holding a mobile phone while driving', 100.00, 2, 'active', NULL, NULL),
(6, 'Not Wearing Seatbelt', 'Driver or passenger not wearing seatbelt', 50.00, 1, 'active', NULL, NULL),
(7, 'Overloading', 'Vehicle exceeding passenger/load limit', 300.00, 4, 'active', NULL, NULL),
(8, 'Running Stop Sign', 'Failing to stop at a stop sign', 175.00, 3, 'active', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `violations`
--
ALTER TABLE `violations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `violation_types`
--
ALTER TABLE `violation_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `violation_name` (`violation_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `violations`
--
ALTER TABLE `violations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `violation_types`
--
ALTER TABLE `violation_types`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
