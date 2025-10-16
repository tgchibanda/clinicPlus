-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 01:42 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clinicplus`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `doctor_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `starts_at` time NOT NULL,
  `ends_at` time NOT NULL,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_payments`
--

CREATE TABLE `booking_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `consultation_id` bigint(20) UNSIGNED NOT NULL,
  `insurance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_ref` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_payload` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'processed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_payments`
--

INSERT INTO `booking_payments` (`id`, `consultation_id`, `insurance_id`, `amount`, `payment_method`, `transaction_ref`, `raw_payload`, `status`, `created_at`, `updated_at`) VALUES
(1, 35, NULL, '15.00', 'cash', NULL, NULL, 'processed', '2025-10-15 03:28:58', '2025-10-15 03:28:58'),
(2, 35, 2, '5.00', 'policy_claim', 'policy_claim#10', NULL, 'processed', '2025-10-15 03:28:58', '2025-10-15 03:28:58'),
(3, 36, NULL, '20.00', 'mixed', 'Cash and transfer', NULL, 'processed', '2025-10-15 03:35:56', '2025-10-15 03:35:56');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `doctor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instruction` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `doctor_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `examination` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diagnosis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `investigation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `management` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_forms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`id`, `patient_id`, `user_id`, `location_id`, `start_at`, `end_at`, `doctor_id`, `reason`, `instruction`, `consultation_fee`, `doctor_notes`, `examination`, `diagnosis`, `investigation`, `management`, `status`, `payment_method`, `request_forms`, `created_at`, `updated_at`) VALUES
(20, 4, 2, 3, '2025-09-21 22:00:00', '2025-09-21 12:30:00', 3, 'His leg is not healing. He has a lot of pain', 'He needs a translator as he cannot speak english', '20.00', NULL, 'Tomusota', 'Gumbo', 'We did all assessments', 'Tora mushonga wedu', '4', NULL, NULL, '2025-09-21 01:20:01', '2025-09-21 11:28:50'),
(21, 4, 2, 3, '2025-09-21 22:30:00', '2025-09-21 13:00:00', 3, 'Same issues', 'Not very happy with his life', '24.00', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, '2025-09-21 11:54:13', '2025-09-21 11:54:13'),
(22, 6, 2, 3, '2025-09-23 22:00:00', '2025-09-23 12:30:00', 3, 'Test reason', 'Test special instructions', '20.00', NULL, NULL, NULL, NULL, NULL, '0', 'Cash', NULL, '2025-09-23 02:53:26', '2025-09-23 02:53:26'),
(23, 5, 2, 3, '2025-09-23 22:30:00', '2025-09-23 13:00:00', 1, 'Pain in the gut', 'May need a hoist', '45.00', NULL, 'He just needed todrink more water', 'Pain was just minor', 'There are not investigations to do', 'He should drink aloe', '4', 'Cash', NULL, '2025-09-23 02:55:43', '2025-09-23 02:57:50'),
(24, 5, 2, 3, '2025-09-26 01:30:00', '2025-09-25 16:00:00', 3, 'asdfa', 'sdfas', '20.00', NULL, 'asdf', 'asdf', 'asdf', 'asdf', '4', 'Cash', NULL, '2025-09-23 03:15:06', '2025-09-23 03:16:07'),
(31, 3, 2, 3, '2025-10-15 03:30:00', '2025-10-14 18:00:00', 1, 'asdAa A', NULL, '20.00', NULL, NULL, NULL, NULL, NULL, 'booked', 'policy_claim', NULL, '2025-10-15 01:40:44', '2025-10-15 01:40:44'),
(32, 3, 2, 1, '2025-10-16 03:30:00', '2025-10-15 18:00:00', 3, 'fg sdfg sdf', 'g sdfg sdfg sdfg s', '20.00', NULL, NULL, NULL, NULL, NULL, '0', 'policy_claim', NULL, '2025-10-15 02:11:41', '2025-10-15 02:11:41'),
(33, 3, 2, 1, '2025-10-22 22:30:00', '2025-10-22 13:00:00', 3, 'jhg', 'jhg', '20.00', NULL, NULL, NULL, NULL, NULL, '0', 'policy_claim', NULL, '2025-10-15 02:27:30', '2025-10-15 02:27:30'),
(34, 3, 2, 1, '2025-10-23 01:30:00', '2025-10-22 16:00:00', 3, 'jhvmv', 'jhvmj', '20.00', NULL, NULL, NULL, NULL, NULL, '0', 'mixed', NULL, '2025-10-15 03:15:01', '2025-10-15 03:15:01'),
(35, 3, 2, 3, '2025-10-15 05:00:00', '2025-10-14 19:30:00', 1, 'kljklj', 'kjhkj', '20.00', NULL, NULL, NULL, NULL, NULL, '0', 'cash', NULL, '2025-10-15 03:28:58', '2025-10-15 03:28:58'),
(36, 2, 2, 3, '2025-10-31 01:30:00', '2025-10-30 16:00:00', 3, 'asdf', NULL, '20.00', NULL, NULL, NULL, NULL, NULL, '0', 'cash', NULL, '2025-10-15 03:35:56', '2025-10-15 03:35:56');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `unit_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suburb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gps` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `user_id`, `unit_number`, `street_name`, `suburb`, `city`, `gps`, `mobile_no`, `created_at`, `updated_at`) VALUES
(1, 2, '66', 'Mudlo street Yarrabilba', '35 Pelican Road Townview Mount Isa QLD 4825', 'Harare', '1111', '0459825176', '2025-09-11 19:51:42', '2025-09-11 19:51:42'),
(2, 3, '788', 'Mudlo street Yarrabilba', '35 Pelican Road Townview Mount Isa QLD 4825', 'Gweru', '1111', '0459825176', '2025-09-11 19:59:57', '2025-09-11 19:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_details`
--

CREATE TABLE `doctor_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drugs`
--

CREATE TABLE `drugs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `minimum_stock_level` int(11) NOT NULL DEFAULT 10,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pieces',
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `drugs`
--

INSERT INTO `drugs` (`id`, `name`, `batch_number`, `description`, `category`, `selling_price`, `stock_quantity`, `minimum_stock_level`, `unit`, `expiry_date`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol', 'B1', 'Used to relieve mild to moderate pain (e.g., headaches, muscle aches, arthritis) and reduce fever.', 'Analgesic', '15.00', 6, 10, 'bags', '2025-09-23', NULL, '2025-09-22 04:40:37'),
(2, 'Metformin', 'B1', 'First-line medication for type 2 diabetes; helps control blood sugar levels by improving insulin sensitivity.', 'Antidiabetic', '20.00', 0, 10, 'pieces', '2027-12-08', NULL, '2025-09-19 19:03:20'),
(3, 'Amoxicillin', 'B1', 'Treats bacterial infections such as ear, throat, chest, and urinary tract infections. Not effective against viral illnesses.', 'Antibiotic', '23.00', 7, 6, 'bottle', '2025-09-30', '2025-09-22 03:07:39', '2025-09-23 03:13:51'),
(4, 'Amoxicillin', 'B2', 'Treats bacterial infections such as ear, throat, chest, and urinary tract infections. Not effective against viral illnesses.', 'Antibiotic', '24.00', 10, 20, 'bottle', '2028-09-07', '2025-09-22 03:42:18', '2025-09-22 03:42:18'),
(5, 'Amlodipine', 'B1', 'Used to manage high blood pressure and angina; relaxes blood vessels to improve blood flow.', 'Antihypertensive', '30.00', 49, 30, 'bottle', '2027-09-09', '2025-09-22 03:57:44', '2025-10-16 00:26:18'),
(6, 'Salbutamol', 'B1', 'Provides quick relief for asthma and chronic obstructive pulmonary disease (COPD) by relaxing airway muscles.', 'Bronchodilator', '20.00', 98, 20, 'tea bags', '2029-09-14', '2025-09-22 04:00:50', '2025-09-23 03:13:51');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feedback` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `insurance_dependents`
--

CREATE TABLE `insurance_dependents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `relationship` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `insurance_dependents`
--

INSERT INTO `insurance_dependents` (`id`, `subscription_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `plan_id`, `relationship`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tinashe', 'Tembo', '1991-11-11', 'male', 3, NULL, '2025-09-30 11:42:36', '2025-09-30 11:42:36'),
(2, 2, 'Fadzayi', 'Chikara', '1991-11-02', 'female', 2, NULL, '2025-09-30 11:48:43', '2025-09-30 11:48:43'),
(3, 3, 'Chido', 'Mutetwa', '1998-02-10', 'female', 2, NULL, '2025-10-01 03:40:54', '2025-10-01 03:40:54');

-- --------------------------------------------------------

--
-- Table structure for table `insurance_events`
--

CREATE TABLE `insurance_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `insurance_events`
--

INSERT INTO `insurance_events` (`id`, `subscription_id`, `type`, `payload`, `created_at`) VALUES
(1, 1, 'subscription_created', '{\"patient_id\":2,\"dependents_count\":1}', '2025-09-30 11:42:36'),
(2, 2, 'subscription_created', '{\"patient_id\":3,\"dependents_count\":1}', '2025-09-30 11:48:43'),
(3, 2, 'payment_recorded', '{\"amount\":\"20.00\",\"payment_method\":\"cash\",\"recorded_by\":null}', '2025-10-01 02:44:52'),
(4, 2, 'payment_recorded', '{\"amount\":\"13.00\",\"payment_method\":\"bank_transfer\",\"recorded_by\":null}', '2025-10-01 02:46:11'),
(5, 1, 'payment_recorded', '{\"amount\":\"10.00\",\"payment_method\":\"cash\",\"recorded_by\":null}', '2025-10-01 03:28:30'),
(6, 3, 'subscription_created', '{\"patient_id\":6,\"dependents_count\":1}', '2025-10-01 03:40:54'),
(7, 3, 'payment_recorded', '{\"amount\":\"45.00\",\"payment_method\":\"cash\",\"recorded_by\":null}', '2025-10-01 03:44:18'),
(8, 3, 'payment_recorded', '{\"amount\":\"18.00\",\"payment_method\":\"mobile\",\"recorded_by\":null}', '2025-10-01 03:45:14'),
(9, 3, 'payment_recorded', '{\"amount\":\"63.00\",\"payment_method\":\"mobile\",\"recorded_by\":null}', '2025-10-01 03:46:54'),
(10, 4, 'subscription_created', '{\"patient_id\":5,\"dependents_count\":0}', '2025-10-12 22:41:05'),
(11, 4, 'payment_recorded', '{\"amount\":\"80.00\",\"payment_method\":\"cash\",\"recorded_by\":null}', '2025-10-12 23:38:46'),
(12, 2, 'payment_recorded', '{\"amount\":\"33.00\",\"payment_method\":\"bank_transfer\",\"recorded_by\":null}', '2025-10-13 01:45:09'),
(13, 4, 'payment_recorded', '{\"amount\":\"80.00\",\"payment_method\":\"cash\",\"recorded_by\":null}', '2025-10-13 01:46:30'),
(14, 1, 'second_reminder_sent', '{\"due_count\":2}', '2025-10-13 02:10:06'),
(15, 1, 'second_reminder_sent', '{\"due_count\":2}', '2025-10-13 02:41:30'),
(16, 2, 'payment_recorded', '{\"amount\":\"33.00\",\"payment_method\":\"cash\",\"recorded_by\":null}', '2025-10-15 19:03:17');

-- --------------------------------------------------------

--
-- Table structure for table `insurance_payments`
--

CREATE TABLE `insurance_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` datetime NOT NULL,
  `status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `insurance_payments`
--

INSERT INTO `insurance_payments` (`id`, `subscription_id`, `amount`, `payment_method`, `transaction_ref`, `paid_at`, `status`, `created_at`, `updated_at`, `note`) VALUES
(1, 2, '20.00', 'cash', NULL, '2025-10-01 12:44:51', 'completed', '2025-10-01 02:44:51', '2025-10-01 02:44:51', 'First cash payment'),
(2, 2, '13.00', 'bank_transfer', NULL, '2025-10-01 12:46:11', 'completed', '2025-10-01 02:46:11', '2025-10-01 02:46:11', 'Balance payment'),
(3, 1, '10.00', 'cash', 'Bond Cash', '2025-10-01 13:28:30', 'completed', '2025-10-01 03:28:30', '2025-10-01 03:28:30', NULL),
(4, 3, '45.00', 'cash', 'Cash in USD', '2025-10-01 13:44:18', 'completed', '2025-10-01 03:44:18', '2025-10-01 03:44:18', NULL),
(5, 3, '18.00', 'mobile', 'Ecocash', '2025-10-01 13:45:14', 'completed', '2025-10-01 03:45:14', '2025-10-01 03:45:14', NULL),
(6, 3, '63.00', 'mobile', NULL, '2025-10-01 13:46:54', 'completed', '2025-10-01 03:46:54', '2025-10-01 03:46:54', NULL),
(7, 4, '80.00', 'cash', 'Ecocash', '2025-10-13 09:38:46', 'completed', '2025-10-12 23:38:46', '2025-10-12 23:38:46', NULL),
(8, 2, '33.00', 'bank_transfer', NULL, '2025-10-13 11:45:08', 'completed', '2025-10-13 01:45:08', '2025-10-13 01:45:08', NULL),
(9, 4, '80.00', 'cash', 'Trans 2', '2025-10-13 11:46:30', 'completed', '2025-10-13 01:46:30', '2025-10-13 01:46:30', NULL),
(10, 2, '33.00', 'cash', 'Ecocash', '2025-10-16 05:03:15', 'completed', '2025-10-15 19:03:16', '2025-10-15 19:03:16', 'He came in');

-- --------------------------------------------------------

--
-- Table structure for table `insurance_plans`
--

CREATE TABLE `insurance_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_adult` decimal(10,2) NOT NULL,
  `price_child` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `insurance_plans`
--

INSERT INTO `insurance_plans` (`id`, `slug`, `name`, `description`, `price_adult`, `price_child`, `active`, `created_at`, `updated_at`) VALUES
(1, 'economy', 'Economy', 'Basic health insurance coverage for individuals and families. Includes essential medical services and preventive care.', '7.00', '5.00', 1, '2025-09-30 02:46:57', '2025-09-30 02:46:57'),
(2, 'economy-plus', 'Economy Plus', 'Enhanced basic coverage with additional benefits including dental check-ups and vision care.', '18.00', '15.00', 1, '2025-09-30 02:46:57', '2025-09-30 02:46:57'),
(3, 'medium', 'Medium', 'Comprehensive coverage for medium healthcare needs. Includes specialist consultations and diagnostic tests.', '48.00', '45.00', 1, '2025-09-30 02:46:57', '2025-09-30 02:46:57'),
(4, 'executive', 'Executive', 'Premium coverage with extensive healthcare benefits including hospitalization and emergency services.', '85.00', '80.00', 1, '2025-09-30 02:46:57', '2025-09-30 02:46:57'),
(5, 'executive-plus', 'Executive Plus', 'Top-tier comprehensive healthcare coverage with premium services, international coverage, and priority care.', '200.00', '150.00', 1, '2025-09-30 02:46:57', '2025-09-30 02:46:57');

-- --------------------------------------------------------

--
-- Table structure for table `insurance_subscriptions`
--

CREATE TABLE `insurance_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `policy_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','active','lapsed','closed','covered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `started_at` datetime NOT NULL,
  `coverage_starts_at` datetime DEFAULT NULL,
  `first_payment_at` datetime DEFAULT NULL,
  `last_payment_at` datetime DEFAULT NULL,
  `total_paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_count` int(11) NOT NULL DEFAULT 0,
  `last_notification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `next_due_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `insurance_subscriptions`
--

INSERT INTO `insurance_subscriptions` (`id`, `policy_number`, `patient_id`, `plan_id`, `status`, `started_at`, `coverage_starts_at`, `first_payment_at`, `last_payment_at`, `total_paid_amount`, `due_count`, `last_notification`, `next_due_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'N1', 2, 5, 'lapsed', '2025-09-30 21:42:36', NULL, '2025-06-01 13:28:30', '2025-07-01 13:28:30', '10.00', 2, 'second', '2025-08-30', NULL, '2025-09-30 11:42:36', '2025-10-13 02:41:27'),
(2, 'N2', 3, 2, 'covered', '2025-09-30 21:48:43', '2025-12-30 21:48:43', '2025-10-01 12:44:51', '2025-10-16 05:03:15', '99.00', 0, '', '2026-03-02', NULL, '2025-09-30 11:48:43', '2025-10-15 19:03:16'),
(3, 'N3', 6, 3, 'covered', '2025-10-01 13:40:54', '2026-01-01 13:40:54', '2025-10-01 13:44:18', '2025-10-01 13:46:54', '126.00', 0, '', '2026-02-01', NULL, '2025-10-01 03:40:54', '2025-10-01 03:46:54'),
(4, 'N4', 5, 4, 'active', '2025-10-13 08:41:05', NULL, '2025-10-13 09:38:46', '2025-10-13 11:46:30', '160.00', 0, '', '2026-01-13', NULL, '2025-10-12 22:41:05', '2025-10-13 01:46:30');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"05c98e13-1f9d-4e09-a0f8-1a2c3b514250\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"admin@clinicplus.com\\\";s:7:\\\"subject\\\";s:16:\\\"iCare OTP 5mGhez\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:10:\\\"iCare User\\\";s:5:\\\"email\\\";s:20:\\\"admin@clinicplus.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is 5mGhez\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 1, 1759227170, 1757599257, 1757599257),
(2, 'default', '{\"uuid\":\"c0a73996-90b3-45a9-951d-1b240318584b\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:20:\\\"iCare OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:10:\\\"iCare User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"admin@clinicplus.com generated OTP is 5mGhez\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757599257, 1757599257),
(3, 'default', '{\"uuid\":\"f4186b3a-bc2b-425c-96b0-3113d92376a5\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:16:\\\"iCare OTP Vaq3Ks\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:10:\\\"iCare User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is Vaq3Ks\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757599342, 1757599342),
(4, 'default', '{\"uuid\":\"c905008c-a89f-4f00-a947-21465e4be109\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:20:\\\"iCare OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:10:\\\"iCare User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is Vaq3Ks\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757599342, 1757599342),
(5, 'default', '{\"uuid\":\"6310d445-c0a3-4bfa-a055-34b1d70bc630\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"iCare Account Role Updated\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:10:\\\"iCare User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:59:\\\"Congratulations, You have updated your account type to user\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757600085, 1757600085),
(6, 'default', '{\"uuid\":\"f38add05-cb0c-470f-a4e9-619dfbf44d17\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"iCare Account Role Updated\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:10:\\\"iCare User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:33:\\\"iCare Account Role Updated ID = 2\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757600085, 1757600085),
(7, 'default', '{\"uuid\":\"dcb4a984-cc6f-4973-8957-0d4a8c104627\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP cglkfa\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is cglkfa\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656147, 1757656147),
(8, 'default', '{\"uuid\":\"ecf72a60-aff2-48d1-9c52-4eb556aa8958\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is cglkfa\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656147, 1757656147),
(9, 'default', '{\"uuid\":\"982fa393-749f-4f1d-b69b-78858a84841c\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP FOQ4Yd\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is FOQ4Yd\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656148, 1757656148),
(10, 'default', '{\"uuid\":\"47a8ffe9-c4ea-44ca-9516-a9b0ba391ba0\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is FOQ4Yd\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656148, 1757656148),
(11, 'default', '{\"uuid\":\"c8a94a4d-daa7-4761-aa27-4e8483d90c4c\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP dUJv3l\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is dUJv3l\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656148, 1757656148),
(12, 'default', '{\"uuid\":\"94d39d43-f190-408a-84f7-0734ce699378\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is dUJv3l\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656148, 1757656148),
(13, 'default', '{\"uuid\":\"3829c9fc-311b-4168-a58c-08ea3a8fc1ae\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP d3taaK\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is d3taaK\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656430, 1757656430),
(14, 'default', '{\"uuid\":\"5d6dc5d8-061f-4bbf-96eb-68a8221beec7\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is d3taaK\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656430, 1757656430),
(15, 'default', '{\"uuid\":\"b6d7ff0e-bee2-4d46-a495-6539298cad89\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Account Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:68:\\\"Welcome Franklin Msiza, You have created a new account on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656737, 1757656737),
(16, 'default', '{\"uuid\":\"3ceb8ee4-5c7a-40af-9397-d59a6dcb7d3c\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Account Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:26:\\\"New Account Created ID = 3\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656737, 1757656737),
(17, 'default', '{\"uuid\":\"e29f90cb-f911-4893-8661-174be77f3c36\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP 3ukPZA\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is 3ukPZA\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656750, 1757656750),
(18, 'default', '{\"uuid\":\"532bba35-0c35-4490-be73-e1ab1b903fc4\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:39:\\\"frank@gmail.com generated OTP is 3ukPZA\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656750, 1757656750),
(19, 'default', '{\"uuid\":\"eab8c819-228d-43c0-a434-a999f72820f7\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:31:\\\"clinicPlus Account Role Updated\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:61:\\\"Congratulations, You have updated your account type to doctor\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656762, 1757656762),
(20, 'default', '{\"uuid\":\"1b8b1be4-40f6-4f80-868e-1cf873ca3905\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:31:\\\"clinicPlus Account Role Updated\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:38:\\\"clinicPlus Account Role Updated ID = 3\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656762, 1757656762),
(21, 'default', '{\"uuid\":\"a9e56375-62a2-40f5-8aaa-39d4a1334322\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:22:\\\"Pending Doctor Account\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:29:\\\"Pending Doctor Account ID = 3\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656762, 1757656762),
(22, 'default', '{\"uuid\":\"a9ac0b30-ec9a-44f2-98bb-783dc9ada67f\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:32:\\\"clinicPlus Doctor Account Review\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:82:\\\"Your account is under review, you will receieve an email once this process is done\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656762, 1757656762),
(23, 'default', '{\"uuid\":\"00dee1bf-f449-45d9-ac0c-92d27eb86ddf\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP IhNRbc\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is IhNRbc\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656890, 1757656890),
(24, 'default', '{\"uuid\":\"ec2258d3-55fb-497b-b1b0-29fb1c1e97da\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is IhNRbc\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656890, 1757656890),
(25, 'default', '{\"uuid\":\"e5e57f9e-8320-43ed-b340-ad810eaddc48\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:34:\\\"clinicPlus Doctor Account Reviewed\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:81:\\\"Congratulations, Your doctor account has been approved, kindly login to start use\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656911, 1757656911),
(26, 'default', '{\"uuid\":\"5954fafe-b918-44b0-b0ce-6d12359048fc\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:34:\\\"clinicPlus Doctor Account Reviewed\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:41:\\\"clinicPlus Doctor Account Reviewed ID = 3\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757656911, 1757656911),
(27, 'default', '{\"uuid\":\"2a5ddb9d-34e6-4142-8be2-dc14add22798\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP 0jtQ63\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is 0jtQ63\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657011, 1757657011),
(28, 'default', '{\"uuid\":\"65b68419-8843-46fe-9f5e-7bfa21285079\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:39:\\\"frank@gmail.com generated OTP is 0jtQ63\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657011, 1757657011),
(29, 'default', '{\"uuid\":\"e5191570-7979-4d73-9944-012873526194\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Account Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:68:\\\"Welcome Marven Murondi, You have created a new account on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657079, 1757657079),
(30, 'default', '{\"uuid\":\"df469235-fcd3-4e9d-8726-385b539eda73\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Account Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:26:\\\"New Account Created ID = 4\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657079, 1757657079),
(31, 'default', '{\"uuid\":\"83ad355b-b05b-4f11-acdc-76434b615bd7\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP IJUegL\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is IJUegL\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657090, 1757657090),
(32, 'default', '{\"uuid\":\"0bdeb04e-85d6-4acc-bea1-6057ed0ea373\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:41:\\\"murondi@gmail.com generated OTP is IJUegL\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657090, 1757657090),
(33, 'default', '{\"uuid\":\"d847d079-0fab-4218-b599-e09e95c79936\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:31:\\\"clinicPlus Account Role Updated\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:59:\\\"Congratulations, You have updated your account type to user\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657095, 1757657095),
(34, 'default', '{\"uuid\":\"9d8bcbc8-f037-4e6c-befa-7c18f8dd92cb\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:31:\\\"clinicPlus Account Role Updated\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:38:\\\"clinicPlus Account Role Updated ID = 4\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657095, 1757657095),
(35, 'default', '{\"uuid\":\"461f4e59-acc7-48de-b5d5-c58de170910a\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP Af5oXF\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is Af5oXF\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657150, 1757657150),
(36, 'default', '{\"uuid\":\"7bcc7046-5357-4603-adef-52f746d047a0\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is Af5oXF\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657150, 1757657150),
(37, 'default', '{\"uuid\":\"026b4e9f-ac1c-4122-b985-3b2dd0a67ac5\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP WuNc7k\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is WuNc7k\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657177, 1757657177),
(38, 'default', '{\"uuid\":\"3ea1e8f4-aa84-43b3-b7f7-2c549a187868\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:41:\\\"murondi@gmail.com generated OTP is WuNc7k\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657177, 1757657177),
(39, 'default', '{\"uuid\":\"2547bbcb-9299-4de1-ab82-046e45bf833f\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:78:\\\"Congratulations, You have created Charlse Mutombeni as a patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657421, 1757657421),
(40, 'default', '{\"uuid\":\"d869709b-f712-4ec0-8a6d-0276b84d4abc\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"charlse@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"charlse@gmail.com\\\";s:4:\\\"data\\\";s:83:\\\"Congratulations Charlse Mutombeni, You have been created as a patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657421, 1757657421),
(41, 'default', '{\"uuid\":\"7e0d9158-1ef7-4216-9b80-b1780323d683\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:24:\\\"New Patient Created id 1\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757657421, 1757657421),
(42, 'default', '{\"uuid\":\"d058fa53-e3ad-4639-af9a-7f284b0e1d76\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP 8SLB0d\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is 8SLB0d\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757658046, 1757658046),
(43, 'default', '{\"uuid\":\"47bd38be-ef17-409f-8c26-eb1ae4a2eb63\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:39:\\\"frank@gmail.com generated OTP is 8SLB0d\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757658046, 1757658046),
(44, 'default', '{\"uuid\":\"31e96ae3-ea09-4f36-b229-a7c2f12618f2\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP 0XbWWD\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is 0XbWWD\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757658076, 1757658076),
(45, 'default', '{\"uuid\":\"504318eb-5d3f-4e03-8657-77321e4fcddc\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:41:\\\"murondi@gmail.com generated OTP is 0XbWWD\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757658076, 1757658076),
(46, 'default', '{\"uuid\":\"43344e35-3741-4c54-8378-fa7bd57a9ad3\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP d7zvYv\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is d7zvYv\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757658979, 1757658979),
(47, 'default', '{\"uuid\":\"27a84555-d1ac-432e-99ef-f4cc320f943b\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:39:\\\"frank@gmail.com generated OTP is d7zvYv\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757658979, 1757658979),
(48, 'default', '{\"uuid\":\"e59325de-a096-447c-ac6f-e900bd9236cd\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP yEGjuS\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is yEGjuS\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659082, 1757659082),
(49, 'default', '{\"uuid\":\"2c96df1c-305a-49dd-9da7-6a109c7ecb92\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:41:\\\"murondi@gmail.com generated OTP is yEGjuS\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659082, 1757659082),
(50, 'default', '{\"uuid\":\"c40fcff0-4606-49e5-87f6-21eb261da58e\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP XHG5kO\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is XHG5kO\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659553, 1757659553);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(51, 'default', '{\"uuid\":\"985b422a-a55a-4e31-bbd4-b84c8356bfad\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:39:\\\"frank@gmail.com generated OTP is XHG5kO\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659553, 1757659553),
(52, 'default', '{\"uuid\":\"6fe24a12-0f15-4156-ade1-1246de5e69b0\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:33:\\\"clinicPlus Consultation Confirmed\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:115:\\\"Your Consultation has been booked by DR Franklin Msiza, The patient will be visited with\\n                 in 24hrs.\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659752, 1757659752),
(53, 'default', '{\"uuid\":\"6a1fc87b-194b-461d-b59d-fc5b39165cb1\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"charlse@gmail.com\\\";s:7:\\\"subject\\\";s:33:\\\"clinicPlus Consultation Confirmed\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"charlse@gmail.com\\\";s:4:\\\"data\\\";s:114:\\\"Your Consultation has been booked by DR Franklin Msiza, The doctor will be visited with\\n                 in 24hrs.\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659752, 1757659752),
(54, 'default', '{\"uuid\":\"f385d8cd-7231-4aed-bbce-661d7c8c108c\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:33:\\\"clinicPlus Consultation Confirmed\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:106:\\\"You have booked a consultation to see Charlse Mutombeni, you have to visit with\\n                 in 24hrs.\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659752, 1757659752),
(55, 'default', '{\"uuid\":\"48874893-189b-44fc-a2ad-168c3f875d04\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:33:\\\"clinicPlus Consultation Confirmed\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:53:\\\"New Consultation has been booked by DR Franklin Msiza\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659752, 1757659752),
(56, 'default', '{\"uuid\":\"0243252a-7d9a-45d1-bc48-2f8982e60630\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP 1d1O5I\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is 1d1O5I\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659948, 1757659948),
(57, 'default', '{\"uuid\":\"1366900f-ef70-4dad-a55b-d797e7b8f162\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:41:\\\"murondi@gmail.com generated OTP is 1d1O5I\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757659948, 1757659948),
(58, 'default', '{\"uuid\":\"6dafaf21-7abd-4732-8102-cfd990d24e76\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP Nv0KAk\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is Nv0KAk\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757682683, 1757682683),
(59, 'default', '{\"uuid\":\"c692bd39-4f39-40fd-89fe-ef1d636491e3\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is Nv0KAk\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757682683, 1757682683),
(60, 'default', '{\"uuid\":\"23e3578c-8b58-4d38-9f32-7ce4c35c7f22\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP Ch8ofp\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:15:\\\"frank@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is Ch8ofp\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757684523, 1757684523),
(61, 'default', '{\"uuid\":\"26e6437f-62de-4558-8300-d2abee2dafe5\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:39:\\\"frank@gmail.com generated OTP is Ch8ofp\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757684523, 1757684523),
(62, 'default', '{\"uuid\":\"5cd9bd09-7c55-4d18-9d6b-2ab037884fe4\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP MDIB8x\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:17:\\\"murondi@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is MDIB8x\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757684807, 1757684807),
(63, 'default', '{\"uuid\":\"fe0fa9ae-8b04-4a21-8820-366ef1b72fe9\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:41:\\\"murondi@gmail.com generated OTP is MDIB8x\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1757684807, 1757684807),
(64, 'default', '{\"uuid\":\"5062b287-29fb-44a7-9832-0bdefe46e173\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP Ly3Pd6\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is Ly3Pd6\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758078743, 1758078743),
(65, 'default', '{\"uuid\":\"cccf2537-a906-4310-bef1-387ce71b747d\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is Ly3Pd6\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758078743, 1758078743),
(66, 'default', '{\"uuid\":\"1c219b28-7af0-491c-a213-8ee14fb9c4eb\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:34:\\\"clinicPlus Walk In Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:66:\\\"Congratulations, You have created Munya as a patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758114808, 1758114808),
(67, 'default', '{\"uuid\":\"b74eb828-7dea-4e13-ace6-2bfdc509ac93\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:14:\\\"muny@gmail.com\\\";s:7:\\\"subject\\\";s:34:\\\"clinicPlus Walk In Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:14:\\\"muny@gmail.com\\\";s:4:\\\"data\\\";s:79:\\\"Congratulations Munya, You have been created as a walk in patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758114808, 1758114808),
(68, 'default', '{\"uuid\":\"e5ccea08-382d-4d4d-b676-a29dca8a80b5\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:24:\\\"New Patient Created id 3\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758114808, 1758114808),
(69, 'default', '{\"uuid\":\"9f4aee0f-8126-47c9-9086-4532c41366cf\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:34:\\\"clinicPlus Walk In Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:65:\\\"Congratulations, You have created Ziso as a patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758115036, 1758115036),
(70, 'default', '{\"uuid\":\"7fc7722e-ef9e-4223-b3d6-02fbbc545f66\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:12:\\\"xi@gmail.com\\\";s:7:\\\"subject\\\";s:34:\\\"clinicPlus Walk In Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:12:\\\"xi@gmail.com\\\";s:4:\\\"data\\\";s:78:\\\"Congratulations Ziso, You have been created as a walk in patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758115036, 1758115036),
(71, 'default', '{\"uuid\":\"615150f2-cf09-4009-99b9-835aa6696e86\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:24:\\\"New Patient Created id 4\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758115036, 1758115036),
(72, 'default', '{\"uuid\":\"5b752f01-c81a-48d7-b829-95ca7e8c1dcf\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:67:\\\"Congratulations, You have created Raviro as a patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758540335, 1758540335),
(73, 'default', '{\"uuid\":\"f66c46b7-8a05-43c5-bb7c-8aa4c58088f4\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:14:\\\"ravi@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:14:\\\"ravi@gmail.com\\\";s:4:\\\"data\\\";s:72:\\\"Congratulations Raviro, You have been created as a patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758540335, 1758540335),
(74, 'default', '{\"uuid\":\"860b89fa-b441-478f-b3cf-3c26efdf66a9\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:24:\\\"New Patient Created id 5\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758540335, 1758540335),
(75, 'default', '{\"uuid\":\"372d1ec3-191f-44ec-860e-49420384b7fd\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:70:\\\"Congratulations, You have created Melisango as a patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758540636, 1758540636),
(76, 'default', '{\"uuid\":\"09e030b4-3bb8-49be-a6b1-3dad31b4502e\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:13:\\\"mel@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:13:\\\"mel@gmail.com\\\";s:4:\\\"data\\\";s:75:\\\"Congratulations Melisango, You have been created as a patient on clinicPlus\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758540636, 1758540636),
(77, 'default', '{\"uuid\":\"802ab2c8-c43a-4ccc-b0a3-0cfc257796da\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:26:\\\"clinicPlus Patient Created\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:24:\\\"New Patient Created id 6\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1758540636, 1758540636),
(78, 'default', '{\"uuid\":\"41cc1702-cc13-45fc-aa93-21d5b44b497a\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP F0RgJv\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is F0RgJv\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269030, 1759269030),
(79, 'default', '{\"uuid\":\"136230a5-7320-4b8e-bd6f-edf4964ca287\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is F0RgJv\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269030, 1759269030),
(80, 'default', '{\"uuid\":\"d18169e6-427e-403c-8697-267266573921\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP jvBmQu\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is jvBmQu\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269032, 1759269032),
(81, 'default', '{\"uuid\":\"e41bee93-aa5c-44dd-9dc7-7eb6f08788e6\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is jvBmQu\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269032, 1759269032),
(82, 'default', '{\"uuid\":\"82385911-60b8-44c2-9537-9882adb5bb87\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP yX8Nzq\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is yX8Nzq\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269032, 1759269032),
(83, 'default', '{\"uuid\":\"a53f4133-1eaa-42d3-b16a-2bd1cfcc9dbe\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is yX8Nzq\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269032, 1759269032),
(84, 'default', '{\"uuid\":\"47bb5c77-029a-4bf9-8cfa-b3fd47fe0cb2\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP 5LTpC8\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is 5LTpC8\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269033, 1759269033),
(85, 'default', '{\"uuid\":\"8352873e-9ce1-48d0-98d5-c954e9972176\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is 5LTpC8\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269033, 1759269033),
(86, 'default', '{\"uuid\":\"c6274cd4-6f60-4a3a-bf55-1c85b53e42e0\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP bHVZ5t\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is bHVZ5t\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269033, 1759269033),
(87, 'default', '{\"uuid\":\"d431e767-1349-483c-9814-571aacba080e\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is bHVZ5t\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269033, 1759269033),
(88, 'default', '{\"uuid\":\"930aeefa-d469-434b-92a4-f46c80417e92\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:7:\\\"subject\\\";s:21:\\\"clinicPlus OTP 5pauAb\\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:20:\\\"tgchibanda@gmail.com\\\";s:4:\\\"data\\\";s:18:\\\"Your OTP is 5pauAb\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269034, 1759269034),
(89, 'default', '{\"uuid\":\"6de68f87-d333-48f7-a690-d13a5e09f213\",\"displayName\":\"App\\\\Jobs\\\\SendEmail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmail\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\SendEmail\\\":10:{s:10:\\\"\\u0000*\\u0000details\\\";a:3:{s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:7:\\\"subject\\\";s:25:\\\"clinicPlus OTP generated \\\";s:4:\\\"data\\\";a:3:{s:4:\\\"name\\\";s:15:\\\"clinicPlus User\\\";s:5:\\\"email\\\";s:18:\\\"icarezim@gmail.com\\\";s:4:\\\"data\\\";s:44:\\\"tgchibanda@gmail.com generated OTP is 5pauAb\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1759269034, 1759269034),
(90, 'default', '{\"uuid\":\"7a9940e9-583f-481b-b366-6ee6236ddb06\",\"displayName\":\"App\\\\Notifications\\\\OverduePaymentNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":\"\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":14:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Patient\\\";s:2:\\\"id\\\";a:1:{i:0;i:2;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:12:\\\"notification\\\";O:44:\\\"App\\\\Notifications\\\\OverduePaymentNotification\\\":12:{s:15:\\\"\\u0000*\\u0000subscription\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:32:\\\"App\\\\Models\\\\InsuranceSubscription\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"patient\\\";i:1;s:4:\\\"plan\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:15:\\\"\\u0000*\\u0000reminderType\\\";s:6:\\\"second\\\";s:2:\\\"id\\\";s:36:\\\"008b2e99-bd31-4e48-9368-f0f0a95b3c7a\\\";s:6:\\\"locale\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1760359290, 1760359290),
(91, 'default', '{\"uuid\":\"f5f88fdf-61a0-490c-b84b-2b2367cb14eb\",\"displayName\":\"App\\\\Notifications\\\\OverduePaymentNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"backoff\":\"\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":14:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Patient\\\";s:2:\\\"id\\\";a:1:{i:0;i:2;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:12:\\\"notification\\\";O:44:\\\"App\\\\Notifications\\\\OverduePaymentNotification\\\":12:{s:15:\\\"\\u0000*\\u0000subscription\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:32:\\\"App\\\\Models\\\\InsuranceSubscription\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:2:{i:0;s:7:\\\"patient\\\";i:1;s:4:\\\"plan\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:15:\\\"\\u0000*\\u0000reminderType\\\";s:6:\\\"second\\\";s:2:\\\"id\\\";s:36:\\\"008b2e99-bd31-4e48-9368-f0f0a95b3c7a\\\";s:6:\\\"locale\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}s:8:\\\"channels\\\";a:1:{i:0;s:5:\\\"nexmo\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1760359290, 1760359290);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `address`, `city`, `state`, `postal_code`, `country`, `created_at`, `updated_at`) VALUES
(1, 'Chadcombe', 'Chadcombe - Harare', 'Harare', 'Harare', '04', 'Zimbabwe', NULL, NULL),
(2, 'Zengeza', 'Zengeza - Chitungwiza', 'Chitungwiza', 'Harare', '04', 'Zimbabwe', NULL, NULL),
(3, 'Norton', 'Norton - Harare', 'Harare', 'Harare', '04', 'Zimbabwe', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_histories`
--

CREATE TABLE `medical_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `consultation_id` bigint(20) UNSIGNED NOT NULL,
  `history` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medical_histories`
--

INSERT INTO `medical_histories` (`id`, `consultation_id`, `history`, `created_at`, `updated_at`) VALUES
(10, 20, 'This person has had a lot of issues with his health. The hospitals have not been helpful.', '2025-09-21 01:20:01', '2025-09-21 01:20:01'),
(11, 21, 'This is the second time', '2025-09-21 11:54:13', '2025-09-21 11:54:13'),
(12, 22, 'Test medical history', '2025-09-23 02:53:26', '2025-09-23 02:53:26'),
(13, 23, 'This person has never been to the hospital', '2025-09-23 02:55:43', '2025-09-23 02:55:43'),
(14, 24, 'saf', '2025-09-23 03:15:06', '2025-09-23 03:15:06'),
(15, 32, 's we efg', '2025-10-15 02:11:41', '2025-10-15 02:11:41'),
(16, 33, 'jhgkjhgkjh', '2025-10-15 02:27:30', '2025-10-15 02:27:30'),
(17, 34, 'sdfasdf', '2025-10-15 03:15:01', '2025-10-15 03:15:01'),
(18, 35, 'jhk', '2025-10-15 03:28:58', '2025-10-15 03:28:58');

-- --------------------------------------------------------

--
-- Table structure for table `medication_payments`
--

CREATE TABLE `medication_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `consultation_id` bigint(20) UNSIGNED NOT NULL,
  `insurance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_ref` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_payload` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'processed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medication_payments`
--

INSERT INTO `medication_payments` (`id`, `consultation_id`, `insurance_id`, `amount`, `payment_method`, `transaction_ref`, `raw_payload`, `status`, `created_at`, `updated_at`) VALUES
(6, 24, 2, '13.00', 'policy_claim', 'policy_claim#15', NULL, 'processed', '2025-10-16 00:26:18', '2025-10-16 00:26:18'),
(7, 24, NULL, '17.00', 'mixed', 'bond and usd', NULL, 'processed', '2025-10-16 00:26:18', '2025-10-16 00:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(4, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(5, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(6, '2016_06_01_000004_create_oauth_clients_table', 1),
(7, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(8, '2019_08_19_000000_create_failed_jobs_table', 1),
(9, '2020_10_21_124600_create_products_table', 1),
(10, '2020_10_23_100949_create_purchases_table', 1),
(11, '2020_12_09_172242_create_contacts_table', 1),
(12, '2020_12_09_172648_create_qualifications_table', 1),
(13, '2020_12_09_173523_create_uploads_table', 1),
(14, '2020_12_09_174029_create_special_areas_table', 1),
(15, '2020_12_17_055512_create_patient_details_table', 1),
(16, '2020_12_19_094325_create_consultations_table', 1),
(17, '2020_12_19_094424_create_medical_histories_table', 1),
(18, '2020_12_19_164315_create_statuses_table', 1),
(19, '2020_12_26_043851_create_payments_table', 1),
(20, '2020_12_26_045315_create_options_table', 1),
(21, '2020_12_27_061156_create_user_roles_table', 1),
(22, '2020_12_28_172051_create_jobs_table', 1),
(23, '2020_12_30_110526_create_zim_services_table', 1),
(24, '2021_01_09_142835_create_feedback_table', 1),
(25, '2021_01_10_091854_create_monthly_conditions_table', 1),
(26, '2021_01_16_101523_create_doctor_details_table', 1),
(27, '2021_01_23_064023_create_payouts_table', 1),
(33, '2025_09_17_024031_create_drugs_table', 2),
(34, '2025_09_17_024606_create_patients_table', 2),
(35, '2025_09_17_024820_create_prescriptions_table', 2),
(36, '2025_09_17_024902_create_prescription_items_table', 2),
(37, '2025_09_17_024954_create_sales_table', 2),
(38, '2025_09_17_025028_create_sale_items_table', 2),
(39, '2025_09_21_012647_create_locations_table', 3),
(40, '2025_09_21_012756_add_location_id_and_super_flag_to_users_table', 4),
(44, '2025_09_21_012903_create_bookings_table', 5),
(45, '2025_09_21_020302_add_location_id_to_consultations_table', 0),
(47, '2025_09_21_020302_add_location_id_to_consultations_table', 1),
(48, '2025_09_21_021900_add_user_id_to_consultations_table', 6),
(49, '2025_09_21_034304_alter_consultations_patient_fk_to_patients', 7),
(50, '2025_09_21_102428_add_consultation_id_to_prescriptions_table', 8),
(51, '2025_09_21_111409_add_consultation_fee_to_consultations_table', 9),
(52, '2025_09_22_112058_remove_payment_fields_from_patients_table', 10),
(53, '2025_09_22_112151_add_payment_method_to_consultations_table', 10),
(54, '2025_09_22_131934_add_batch_number_to_drugs_table', 11),
(55, '2025_09_30_092207_create_insurance_plans_table', 12),
(56, '2025_10_01_125326_add_note_to_insurance_payments_table', 13),
(57, '2025_10_13_132420_create_policy_claims_table', 14),
(59, '2025_10_15_124738_create_booking_payments_table', 15);

-- --------------------------------------------------------

--
-- Table structure for table `monthly_conditions`
--

CREATE TABLE `monthly_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `monthly_conditions`
--

INSERT INTO `monthly_conditions` (`id`, `title`, `description`, `month`, `created_at`, `updated_at`) VALUES
(1, 'Testinf Condition Of the month', 'This is the description', NULL, '2025-09-11 19:55:02', '2025-09-11 19:55:02');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_access_tokens`
--

INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('0ba0fcbe95878fcace112d4ccdceb4fc313d5220d680e68b25f402db40731ad68dfd5370f66c449c', 2, 1, 'Personal Access Token', '[]', 2, '2025-09-11 04:02:22', '2025-09-11 04:02:22', '2026-09-11 14:02:22'),
('0c76860e4bdf87648b83a8d4198d0a3d1eabe2c79c7bdcca5f23c5f5fb96af01cb30073c9ffd17b5', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-30 11:50:32', '2025-09-30 11:50:32', '2026-09-30 21:50:32'),
('0c853476fe150a780a5d8f18d548e4d7e86d308ade1c52b960ee17093d1a2121cea8b52ab4011307', 3, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:03:31', '2025-09-11 20:03:31', '2026-09-12 06:03:31'),
('10293c75cb44c7ec9b23628a60922ece1cf01d0d9cefba901d107c83e952f991bd814752cd1171d3', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-30 11:50:34', '2025-09-30 11:50:34', '2026-09-30 21:50:34'),
('12635791e8569d0c0b935708e056748281378778c95815fd9a4a0f13c76bf8145accde209f522ffd', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-30 11:50:32', '2025-09-30 11:50:32', '2026-09-30 21:50:32'),
('1275cf98e522d5fdad4968063e2eb7dee729c844f84098a0ed22989df9c71282852e68fda56335bc', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-11 19:49:08', '2025-09-11 19:49:08', '2026-09-12 05:49:08'),
('13b8bb1ffda4221135222e3b5c80150a2523b964a8168891257e59c71f1419965f283634dd22d25d', 3, 1, 'Personal Access Token', '[]', 0, '2025-09-12 03:42:03', '2025-09-12 03:42:03', '2026-09-12 13:42:03'),
('155608284d796e6a1cd6b8c2989ead195757275766461128f77235b48f2efe31576a5176cd9458d4', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-30 11:50:33', '2025-09-30 11:50:33', '2026-09-30 21:50:33'),
('21aa8a24a0354d8eae60b1003eae2d2604e77961c259dbf0093ff4403bfb0f463c3949461d0b83ef', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:05:50', '2025-09-11 20:05:50', '2026-09-12 06:05:50'),
('2ff228ce8b7c3caca3bd9d52b9d734eb534c0d6b441dd13abf0febc051d38498f5c97831c8297663', 3, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:36:19', '2025-09-11 20:36:19', '2026-09-12 06:36:19'),
('3101f1b279f8a74723e305aa0451127d6303c7fe33bb7ccdb1ead9fcff0667d986fb70548d33eda0', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-30 11:50:28', '2025-09-30 11:50:28', '2026-09-30 21:50:28'),
('3e5a3aa7053410b80e7a53a84c5a41fbc368e65c68b6fba5a7f819cfbebf7d6ce299672e26cb8ff3', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-11 19:53:50', '2025-09-11 19:53:50', '2026-09-12 05:53:50'),
('498313d55f0682acadec2a0e0f211e090262263ef6cac26a356287f79d5c42b8a4fd0c520134006c', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-12 03:11:23', '2025-09-12 03:11:23', '2026-09-12 13:11:23'),
('56bb169050b67c01c78d5eec3af7c8012a8ccdfd0ae9d91622127ed5e9d4f3b06a5b41a048bf51d3', 4, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:04:50', '2025-09-11 20:04:50', '2026-09-12 06:04:50'),
('58dad2b43bbb4c2c883e84dc9473b3fd87107c8c7951e449a8b88a24074c28148561d1005149e8e5', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-16 17:12:23', '2025-09-16 17:12:23', '2026-09-17 03:12:23'),
('59e169e6d689eb8d675d357e2c4f5e4b35a56659c60fe9bbba39cb4e068049623cffd77ababe5eb0', 3, 1, 'Personal Access Token', '[]', 0, '2025-09-11 19:59:10', '2025-09-11 19:59:10', '2026-09-12 05:59:10'),
('5b5ed4d920b424fb81d65163a37db9a7f8bdabb30738e1e5900b14528ad716e9a5431efd185b26ae', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:01:30', '2025-09-11 20:01:30', '2026-09-12 06:01:30'),
('61f5202f2069486bd54995e755a15a84b51d11658f080e9209bc5c69ce4394f1cf4f9b01ff9c9011', 3, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:20:46', '2025-09-11 20:20:46', '2026-09-12 06:20:46'),
('628c1e08ce27b2f006a17e7d9f4f9bfc751febb6fb818d06c40f6e93723713f03edba6d4337122d9', 4, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:06:17', '2025-09-11 20:06:17', '2026-09-12 06:06:17'),
('69a4aa8081dc6035e6330b806236f6a37ecf883812b7aa7189b2b10c2c6e3ce120185ccd96e614c1', 4, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:52:28', '2025-09-11 20:52:28', '2026-09-12 06:52:28'),
('7a86615cd3edf1c3e049992d437c774d573ab2d478ac72a5b864ba5c99cf6dfba8bbf2ec81422ac0', 3, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:45:53', '2025-09-11 20:45:53', '2026-09-12 06:45:53'),
('7a8e1c31e253a39311991350b240ab9d38dce42192d66bda1825954daba65ac0b4188556f7be0b4f', 4, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:21:16', '2025-09-11 20:21:16', '2026-09-12 06:21:16'),
('972903c69d9ed96dd64be8b8fa2a8466bed4870ee921db823cacb9186e273aa9814f960dbf6559be', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-30 11:50:33', '2025-09-30 11:50:33', '2026-09-30 21:50:33'),
('b4f76b1f582af1037beef60e5e5e9471fb054d34e6f1329382701067218be7c37e47e1e0f99574b0', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-11 04:00:57', '2025-09-11 04:00:57', '2026-09-11 14:00:57'),
('b7dcdf8e2c3f8d264d8d99eea34bd1f041b73cf73c8b7201f809c400912849ac24fd35b6c65b079c', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-11 19:49:08', '2025-09-11 19:49:08', '2026-09-12 05:49:08'),
('c07f94dd9da96c617aea66d5bc4caefcd12de6a9c4531778acad5dce8947205ca4e37d0406dab3f1', 2, 1, 'Personal Access Token', '[]', 0, '2025-09-11 19:49:05', '2025-09-11 19:49:05', '2026-09-12 05:49:05'),
('e001deff242b8afeb438e079200d92cf5d882f0a83ead5aa5933b944eb55fef12027d4681dcd9cd0', 4, 1, 'Personal Access Token', '[]', 0, '2025-09-11 20:38:02', '2025-09-11 20:38:02', '2026-09-12 06:38:02'),
('fc35f3542ddad45352a642c640fd7ce31cf30b4e0c4002e68399647694f8ebe9dd11b67f9f52c947', 4, 1, 'Personal Access Token', '[]', 0, '2025-09-12 03:46:47', '2025-09-12 03:46:47', '2026-09-12 13:46:47');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'clinicPlus Personal Access Client', '2KGB9oEml3eIqE0zID9A9gOukjOUBHLUGNvAAB3F', NULL, 'http://localhost', 1, 0, 0, '2025-09-11 03:36:43', '2025-09-11 03:36:43'),
(2, NULL, 'clinicPlus Password Grant Client', 'PERNcL6DlyOkbgDHqtd2OuTEx9KluYJaLRwBunvT', 'users', 'http://localhost', 0, 1, 0, '2025-09-11 03:36:43', '2025-09-11 03:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-09-11 03:36:43', '2025-09-11 03:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `category`, `type`, `name`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'payment', 'decimal', 'consultation_fee', '45.00', 'Consultation fee', '2025-09-10 02:52:57', '2025-09-10 02:52:57'),
(2, 'payment', 'decimal', 'consultation_fee', '45.00', 'Consultation fee', '2025-09-30 02:42:09', '2025-09-30 02:42:09');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_doctor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('waiting','booked','consulting','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `user_id` int(255) NOT NULL,
  `visit_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `first_name`, `last_name`, `phone`, `email`, `date_of_birth`, `gender`, `address`, `emergency_contact`, `assigned_doctor_id`, `status`, `user_id`, `visit_date`, `created_at`, `updated_at`) VALUES
(1, 'Ari', 'Ella', '6956785678', 'ari@gmail.com', '2025-09-01', 'female', 'Pelican road', '098656', 1, 'waiting', 2, '2025-09-17 12:14:37', NULL, '2025-09-20 18:57:17'),
(2, 'Takunda', 'Chibanda', '0459825176', 'tgchibanda@gmail.com', '2025-09-02', 'male', '35 PELICAN ROAD TOWNVIEW  MOUNT ISA QLD 4825 Australia', NULL, 3, 'booked', 2, '2025-09-17 13:11:38', '2025-09-17 03:11:38', '2025-10-15 03:35:56'),
(3, 'Munya', 'Chisvo', '0459000000', 'muny@gmail.com', '2025-08-31', 'male', '35 PELICAN ROAD TOWNVIEW  MOUNT ISA QLD 4825 Australia', NULL, 1, 'booked', 2, '2025-09-17 13:13:26', '2025-09-17 03:13:26', '2025-10-15 03:28:58'),
(4, 'Ziso', 'Mbune', '0776665556', 'xi@gmail.com', '2024-09-09', 'female', '35 PELICAN ROAD TOWNVIEW  MOUNT ISA QLD 4825 Australia', NULL, 3, 'booked', 2, '2025-09-17 13:17:16', '2025-09-17 03:17:16', '2025-09-21 11:54:13'),
(5, 'Raviro', 'Mika', '0779999999', 'ravi@gmail.com', '2008-09-02', 'male', '4004 indsor park Harare', NULL, 3, 'completed', 2, '2025-09-22 11:25:34', '2025-09-22 01:25:34', '2025-09-23 03:16:41'),
(6, 'Melisa', 'Dube', '0777000000', 'mel@gmail.com', '2025-08-04', 'male', '34 Hoolands Norton', NULL, 3, 'booked', 2, '2025-09-22 11:30:36', '2025-09-22 01:30:36', '2025-09-23 02:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `patient_details`
--

CREATE TABLE `patient_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gps` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_no_contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_details`
--

INSERT INTO `patient_details` (`id`, `user_id`, `fullname`, `email`, `mobile_no`, `address_line1`, `address_line2`, `address_line3`, `city`, `gps`, `contact_person`, `mobile_no_contact_person`, `dob`, `gender`, `created_at`, `updated_at`) VALUES
(1, 4, 'Charlse Mutombeni', 'charlse@gmail.com', '0776665556', '55 Mkoba 12', '54 Harare Mandara', NULL, 'Harare', '111', NULL, '0766983244', '2025-09-02', 'Male', '2025-09-11 20:10:21', '2025-09-11 20:10:21');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'order_created',
  `payout_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `patient_id`, `consultation_id`, `amount`, `order_number`, `status`, `payout_id`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 1, '45.00', 'PAYID-NDB3TFY5L244320PE6809537', 'payout', 2, '2025-09-11 20:11:35', '2025-09-11 20:11:35'),
(3, 4, 1, 2, '45.00', 'PAYID-NDB4GYI82P60417EA611251W', 'paid', NULL, '2025-09-11 20:53:21', '2025-09-11 20:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `payout_batch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_net` decimal(8,2) DEFAULT NULL,
  `amount_gross` decimal(8,2) DEFAULT NULL,
  `fees` decimal(8,2) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payouts`
--

INSERT INTO `payouts` (`id`, `user_id`, `payout_batch`, `amount_net`, `amount_gross`, `fees`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '', '0.00', '0.00', '0.00', 'paid', '2025-09-11 19:57:55', '2025-09-11 19:57:55'),
(2, 1, '1', '29.25', '45.00', '15.75', 'paid', '2025-09-12 03:12:16', '2025-09-12 03:12:16');

-- --------------------------------------------------------

--
-- Table structure for table `policy_claims`
--

CREATE TABLE `policy_claims` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `consultation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `claim_holder_first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `claim_holder_last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `claim_holder_dob` date DEFAULT NULL,
  `claim_holder_relationship` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `claim_category` text COLLATE utf8mb4_unicode_ci DEFAULT 'Consultation fee',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `policy_claims`
--

INSERT INTO `policy_claims` (`id`, `subscription_id`, `consultation_id`, `claim_holder_first_name`, `claim_holder_last_name`, `claim_holder_dob`, `claim_holder_relationship`, `amount`, `claim_category`, `status`, `created_at`, `updated_at`) VALUES
(6, 2, 31, 'Munya', 'Chisvo', '2025-08-31', 'self', '55.00', 'Consultation fee', 'processed', '2025-10-15 01:40:44', '2025-10-15 01:40:44'),
(7, 2, 32, 'Munya', 'Chisvo', '2025-08-31', 'self', '6.00', 'Consultation fee', 'processed', '2025-10-15 02:11:41', '2025-10-15 02:11:41'),
(8, 2, 33, 'Munya', 'Chisvo', '2025-08-31', 'self', '10.00', 'Consultation fee', 'processed', '2025-10-15 02:27:30', '2025-10-15 02:27:30'),
(9, 2, 34, 'Munya', 'Chisvo', '2025-08-31', 'self', '10.00', 'Consultation fee', 'processed', '2025-10-15 03:15:01', '2025-10-15 03:15:01'),
(10, 2, 35, 'Munya', 'Chisvo', '2025-08-31', 'self', '5.00', 'Consultation fee', 'processed', '2025-10-15 03:28:58', '2025-10-15 03:28:58'),
(15, 2, 24, 'Raviro', 'Mika', '2008-09-02', 'self', '13.00', 'Medication', 'processed', '2025-10-16 00:26:18', '2025-10-16 00:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `consultation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `doctor_id` bigint(20) UNSIGNED NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','partial','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `patient_id`, `consultation_id`, `doctor_id`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(12, 4, 20, 2, 'This prescription is to be webster packed', 'completed', '2025-09-21 02:01:26', '2025-09-22 04:40:37'),
(14, 5, 23, 2, 'Notes to the phamarcy', 'partial', '2025-09-23 03:10:51', '2025-09-23 03:13:51'),
(15, 5, 24, 2, 'asdfasdf', 'completed', '2025-09-23 03:16:41', '2025-10-16 00:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prescription_id` bigint(20) UNSIGNED NOT NULL,
  `drug_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_prescribed` int(11) NOT NULL,
  `quantity_dispensed` int(11) NOT NULL DEFAULT 0,
  `dosage_instructions` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescription_items`
--

INSERT INTO `prescription_items` (`id`, `prescription_id`, `drug_id`, `quantity_prescribed`, `quantity_dispensed`, `dosage_instructions`, `unit_price`, `created_at`, `updated_at`) VALUES
(15, 12, 1, 1, 1, 'Take one per day any time', '15.00', '2025-09-21 02:01:26', '2025-09-22 04:40:37'),
(17, 14, 6, 3, 2, 'take on an empty', '20.00', '2025-09-23 03:10:51', '2025-09-23 03:13:51'),
(18, 14, 3, 3, 3, 'Take with food', '23.00', '2025-09-23 03:10:51', '2025-09-23 03:13:51'),
(19, 15, 5, 1, 1, 'asdfasdf', '30.00', '2025-09-23 03:16:41', '2025-10-16 00:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qualifications`
--

CREATE TABLE `qualifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `institution_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qualification_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year_completed` date NOT NULL,
  `upload` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `prescription_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pharmacist_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `voucher_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `patient_id`, `prescription_id`, `pharmacist_id`, `total_amount`, `payment_method`, `voucher_code`, `created_at`, `updated_at`) VALUES
(29, 4, 12, 2, '15.00', 'cash', NULL, '2025-09-22 04:40:37', '2025-09-22 04:40:37'),
(30, 5, 14, 2, '109.00', 'cash', NULL, '2025-09-23 03:13:51', '2025-09-23 03:13:51'),
(31, 5, 15, 2, '30.00', 'mixed', NULL, '2025-10-16 00:26:18', '2025-10-16 00:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `drug_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `drug_id`, `quantity`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES
(12, 29, 1, 1, '15.00', '15.00', '2025-09-22 04:40:37', '2025-09-22 04:40:37'),
(13, 30, 6, 2, '20.00', '40.00', '2025-09-23 03:13:51', '2025-09-23 03:13:51'),
(14, 30, 3, 3, '23.00', '69.00', '2025-09-23 03:13:51', '2025-09-23 03:13:51'),
(15, 31, 5, 1, '30.00', '30.00', '2025-10-16 00:26:18', '2025-10-16 00:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `special_areas`
--

CREATE TABLE `special_areas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `area_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `status_level` int(11) NOT NULL,
  `status_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `status_level`, `status_text`, `status_description`, `created_at`, `updated_at`) VALUES
(1, 0, 'Pending Payment', 'Pending Consultation Payment', '2025-09-10 02:52:57', '2025-09-10 02:52:57'),
(2, 1, 'Pending Doctor Visit', 'Pending Doctor Visit', '2025-09-10 02:52:57', '2025-09-10 02:52:57'),
(3, 2, 'Ready for consultation', 'Ready for consultation', '2025-09-10 02:52:57', '2025-09-10 02:52:57'),
(4, 3, 'In Consultation', 'In Consultation', '2025-09-10 02:52:57', '2025-09-10 02:52:57'),
(5, 4, 'Seen by doctor', 'Seen by doctor', '2025-09-10 02:52:57', '2025-09-10 02:52:57'),
(6, 0, 'Pending Payment', 'Pending Consultation Payment', '2025-09-30 02:42:09', '2025-09-30 02:42:09'),
(7, 1, 'Pending Doctor Visit', 'Pending Doctor Visit', '2025-09-30 02:42:09', '2025-09-30 02:42:09'),
(8, 2, 'Ready for consultation', 'Ready for consultation', '2025-09-30 02:42:09', '2025-09-30 02:42:09'),
(9, 3, 'In Consultation', 'In Consultation', '2025-09-30 02:42:09', '2025-09-30 02:42:09'),
(10, 4, 'Seen by doctor', 'Seen by doctor', '2025-09-30 02:42:09', '2025-09-30 02:42:09');

-- --------------------------------------------------------

--
-- Table structure for table `uploads`
--

CREATE TABLE `uploads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `upload_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `upload` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `uploads`
--

INSERT INTO `uploads` (`id`, `user_id`, `upload_name`, `description`, `upload`, `created_at`, `updated_at`) VALUES
(1, 2, '1757656302_TMC_0438.jpg', 'profile', '/storage/uploads/1757656302_TMC_0438.jpg', '2025-09-11 19:51:42', '2025-09-11 19:51:42'),
(2, 3, '1757656797_TMC_0428.jpg', 'profile', '/storage/uploads/1757656797_TMC_0428.jpg', '2025-09-11 19:59:57', '2025-09-11 19:59:57'),
(3, 3, '1757656848_position-outline-business-system-administrator.pdf', 'National ID', '/storage/uploads/1757656848_position-outline-business-system-administrator.pdf', '2025-09-11 20:00:49', '2025-09-11 20:00:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_super_doctor` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `location_id`, `is_super_doctor`, `name`, `email`, `email_verified_at`, `password`, `avatar`, `provider`, `provider_id`, `access_token`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 3, 0, 'Franklin Msiza', 'frank@clinicplus.com', NULL, '$2y$10$YsyQGQya4gBOfWDxP/5aF.ugHiXCqzvL2JfcG0PCX62tj8UlPlNXi', NULL, NULL, NULL, NULL, NULL, '2025-09-10 02:52:57', '2025-09-10 02:52:57'),
(2, 3, 1, 'Takunda Chibanda', 'tgchibanda@gmail.com', NULL, '$2y$10$YsyQGQya4gBOfWDxP/5aF.ugHiXCqzvL2JfcG0PCX62tj8UlPlNXi', NULL, NULL, NULL, NULL, NULL, '2025-09-11 03:51:30', '2025-09-11 03:51:30'),
(3, 3, 1, 'T Nyamande', 'tnyamande@gmail.com', NULL, '$2y$10$aU8TYvloMbWF7f0Df2.yR.aR6HKhPudJo/Q7qiEF8.48Jh8GWaGIG', NULL, NULL, NULL, NULL, NULL, '2025-09-11 19:58:57', '2025-09-11 19:58:57'),
(4, 3, 0, 'Marven Murondi', 'murondi@gmail.com', NULL, '$2y$10$T6z/auTPXPVhe9hxiQQHiOMcN4.YVQp5qJM16cusaBMC0nuWx2Rgu', NULL, NULL, NULL, NULL, NULL, '2025-09-11 20:04:39', '2025-09-11 20:04:39'),
(5, NULL, 0, 'Payfast Admin', 'admin@clinicPlus.co.zw', NULL, '$2y$10$olNTuHYZe4XRkwKcB.4EuOTw8T3p9H20Go1Dni2H.Rim1Wmf3CmTO', NULL, NULL, NULL, NULL, NULL, '2025-09-30 02:42:09', '2025-09-30 02:42:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'doctor', 'active', '2025-09-10 02:52:57', '2025-09-10 02:52:57'),
(2, 2, 'admin', 'active', '2025-09-11 04:14:45', '2025-09-11 04:14:45'),
(3, 3, 'doctor', 'active', '2025-09-11 19:59:22', '2025-09-11 20:01:50'),
(4, 4, 'user', 'active', '2025-09-11 20:04:55', '2025-09-11 20:04:55'),
(5, 5, 'admin', 'active', '2025-09-30 02:42:09', '2025-09-30 02:42:09');

-- --------------------------------------------------------

--
-- Table structure for table `zim_services`
--

CREATE TABLE `zim_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `landline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_contacts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zim_services`
--

INSERT INTO `zim_services` (`id`, `name`, `address`, `mobile_no`, `landline`, `additional_contacts`, `facebook`, `twitter`, `instagram`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Claybank', '12 Roundabout Lundi Park', '0774474777', '054663546', '0677667', 'www.facebook.com/claybank', NULL, 'www.instagram.com/claybank', 'Radiology', '2025-09-11 19:56:45', '2025-09-11 19:56:45'),
(2, 'QV', '12 Roundabout Lundi Park', '0774474777', '054663546', '0677667', 'www.facebook.com/qv', NULL, 'www.instagram.com/qv', 'Pharmacies', '2025-09-11 19:57:20', '2025-09-11 19:57:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `doctor_slot_unique` (`doctor_id`,`date`,`starts_at`),
  ADD KEY `bookings_patient_id_foreign` (`patient_id`),
  ADD KEY `bookings_location_id_foreign` (`location_id`),
  ADD KEY `bookings_date_doctor_id_index` (`date`,`doctor_id`),
  ADD KEY `bookings_date_location_id_index` (`date`,`location_id`);

--
-- Indexes for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_payments_consultation_id_index` (`consultation_id`),
  ADD KEY `booking_payments_insurance_id_index` (`insurance_id`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consultations_location_id_foreign` (`location_id`),
  ADD KEY `consultations_user_id_foreign` (`user_id`),
  ADD KEY `consultations_patient_id_foreign` (`patient_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_user_id_foreign` (`user_id`);

--
-- Indexes for table `doctor_details`
--
ALTER TABLE `doctor_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drugs`
--
ALTER TABLE `drugs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `insurance_dependents`
--
ALTER TABLE `insurance_dependents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `insurance_dependents_plan_id_foreign` (`plan_id`),
  ADD KEY `insurance_dependents_subscription_id_index` (`subscription_id`);

--
-- Indexes for table `insurance_events`
--
ALTER TABLE `insurance_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `insurance_events_subscription_id_index` (`subscription_id`),
  ADD KEY `insurance_events_type_index` (`type`);

--
-- Indexes for table `insurance_payments`
--
ALTER TABLE `insurance_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `insurance_payments_subscription_id_index` (`subscription_id`),
  ADD KEY `insurance_payments_paid_at_index` (`paid_at`),
  ADD KEY `insurance_payments_subscription_id_paid_at_index` (`subscription_id`,`paid_at`);

--
-- Indexes for table `insurance_plans`
--
ALTER TABLE `insurance_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `insurance_plans_slug_unique` (`slug`),
  ADD KEY `insurance_plans_slug_index` (`slug`),
  ADD KEY `insurance_plans_active_index` (`active`);

--
-- Indexes for table `insurance_subscriptions`
--
ALTER TABLE `insurance_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `policy_number` (`policy_number`),
  ADD KEY `insurance_subscriptions_plan_id_foreign` (`plan_id`),
  ADD KEY `insurance_subscriptions_patient_id_index` (`patient_id`),
  ADD KEY `insurance_subscriptions_status_index` (`status`),
  ADD KEY `insurance_subscriptions_next_due_date_index` (`next_due_date`),
  ADD KEY `insurance_subscriptions_status_next_due_date_index` (`status`,`next_due_date`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical_histories`
--
ALTER TABLE `medical_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medication_payments`
--
ALTER TABLE `medication_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monthly_conditions`
--
ALTER TABLE `monthly_conditions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patients_assigned_doctor_id_foreign` (`assigned_doctor_id`);

--
-- Indexes for table `patient_details`
--
ALTER TABLE `patient_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_details_user_id_foreign` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `policy_claims`
--
ALTER TABLE `policy_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `policy_claims_subscription_id_index` (`subscription_id`),
  ADD KEY `policy_claims_consultation_id_index` (`consultation_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescriptions_patient_id_foreign` (`patient_id`),
  ADD KEY `prescriptions_doctor_id_foreign` (`doctor_id`),
  ADD KEY `prescriptions_consultation_id_foreign` (`consultation_id`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_items_prescription_id_foreign` (`prescription_id`),
  ADD KEY `prescription_items_drug_id_foreign` (`drug_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchases_user_id_foreign` (`user_id`);

--
-- Indexes for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qualifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_patient_id_foreign` (`patient_id`),
  ADD KEY `sales_prescription_id_foreign` (`prescription_id`),
  ADD KEY `sales_pharmacist_id_foreign` (`pharmacist_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_items_sale_id_foreign` (`sale_id`),
  ADD KEY `sale_items_drug_id_foreign` (`drug_id`);

--
-- Indexes for table `special_areas`
--
ALTER TABLE `special_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `special_areas_user_id_foreign` (`user_id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploads_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_location_id_foreign` (`location_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_roles_user_id_foreign` (`user_id`);

--
-- Indexes for table `zim_services`
--
ALTER TABLE `zim_services`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_payments`
--
ALTER TABLE `booking_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `doctor_details`
--
ALTER TABLE `doctor_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drugs`
--
ALTER TABLE `drugs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `insurance_dependents`
--
ALTER TABLE `insurance_dependents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `insurance_events`
--
ALTER TABLE `insurance_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `insurance_payments`
--
ALTER TABLE `insurance_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `insurance_plans`
--
ALTER TABLE `insurance_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `insurance_subscriptions`
--
ALTER TABLE `insurance_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `medical_histories`
--
ALTER TABLE `medical_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `medication_payments`
--
ALTER TABLE `medication_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `monthly_conditions`
--
ALTER TABLE `monthly_conditions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient_details`
--
ALTER TABLE `patient_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `policy_claims`
--
ALTER TABLE `policy_claims`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qualifications`
--
ALTER TABLE `qualifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `special_areas`
--
ALTER TABLE `special_areas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zim_services`
--
ALTER TABLE `zim_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `bookings_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD CONSTRAINT `booking_payments_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `consultations_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `insurance_dependents`
--
ALTER TABLE `insurance_dependents`
  ADD CONSTRAINT `insurance_dependents_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `insurance_plans` (`id`),
  ADD CONSTRAINT `insurance_dependents_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `insurance_subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `insurance_events`
--
ALTER TABLE `insurance_events`
  ADD CONSTRAINT `insurance_events_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `insurance_subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `insurance_payments`
--
ALTER TABLE `insurance_payments`
  ADD CONSTRAINT `insurance_payments_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `insurance_subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `insurance_subscriptions`
--
ALTER TABLE `insurance_subscriptions`
  ADD CONSTRAINT `insurance_subscriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `insurance_subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `insurance_plans` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_assigned_doctor_id_foreign` FOREIGN KEY (`assigned_doctor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `patient_details`
--
ALTER TABLE `patient_details`
  ADD CONSTRAINT `patient_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `policy_claims`
--
ALTER TABLE `policy_claims`
  ADD CONSTRAINT `policy_claims_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `policy_claims_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `insurance_subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_drug_id_foreign` FOREIGN KEY (`drug_id`) REFERENCES `drugs` (`id`),
  ADD CONSTRAINT `prescription_items_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD CONSTRAINT `qualifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `sales_pharmacist_id_foreign` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_drug_id_foreign` FOREIGN KEY (`drug_id`) REFERENCES `drugs` (`id`),
  ADD CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`);

--
-- Constraints for table `special_areas`
--
ALTER TABLE `special_areas`
  ADD CONSTRAINT `special_areas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `uploads`
--
ALTER TABLE `uploads`
  ADD CONSTRAINT `uploads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
