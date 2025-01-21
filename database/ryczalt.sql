-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 21, 2025 at 09:24 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ryczalt`
--

-- --------------------------------------------------------

--
-- Table structure for table `close_month`
--

CREATE TABLE `close_month` (
  `month` int(11) NOT NULL CHECK (`month` between 1 and 12),
  `year` int(11) NOT NULL CHECK (`year` >= 2000),
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `nip` varchar(10) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `postal_code` varchar(6) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `nip`, `address`, `postal_code`, `city`, `phone`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Qumi Soft Jakub Kumięga', '6020084026', 'Daniszewska 29/23', '03-230', 'Warszawa', NULL, NULL, 1, '2025-01-16 19:56:18', '2025-01-16 19:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(2) NOT NULL,
  `companyId` int(11) DEFAULT NULL,
  `isSystem` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `code`, `companyId`, `isSystem`, `created_at`, `updated_at`) VALUES
(1, 'Austria', 'AT', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(2, 'Belgia', 'BE', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(3, 'Bułgaria', 'BG', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(4, 'Chorwacja', 'HR', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(5, 'Cypr', 'CY', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(6, 'Czechy', 'CZ', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(7, 'Dania', 'DK', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(8, 'Estonia', 'EE', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(9, 'Finlandia', 'FI', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(10, 'Francja', 'FR', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(11, 'Grecja', 'GR', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(12, 'Hiszpania', 'ES', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(13, 'Holandia', 'NL', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(14, 'Irlandia', 'IE', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(15, 'Litwa', 'LT', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(16, 'Luksemburg', 'LU', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(17, 'Łotwa', 'LV', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(18, 'Malta', 'MT', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(19, 'Niemcy', 'DE', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(20, 'Polska', 'PL', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(21, 'Portugalia', 'PT', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(22, 'Rumunia', 'RO', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(23, 'Słowacja', 'SK', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(24, 'Słowenia', 'SI', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(25, 'Szwecja', 'SE', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(26, 'Węgry', 'HU', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13'),
(27, 'Włochy', 'IT', NULL, 1, '2025-01-16 19:59:11', '2025-01-20 16:00:13');

-- --------------------------------------------------------

--
-- Table structure for table `customerAddressDetails`
--

CREATE TABLE `customerAddressDetails` (
  `addressId` int(11) NOT NULL,
  `customerId` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postalCode` varchar(10) DEFAULT NULL,
  `country` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customerAddressDetails`
--

INSERT INTO `customerAddressDetails` (`addressId`, `customerId`, `name`, `street`, `city`, `postalCode`, `country`, `createdAt`, `updatedAt`) VALUES
(1, 1, '', '', '', '', NULL, '2025-01-17 18:32:36', '2025-01-17 18:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `customerContactDetails`
--

CREATE TABLE `customerContactDetails` (
  `contactId` int(11) NOT NULL,
  `customerId` int(11) NOT NULL,
  `contactPerson` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customerContactDetails`
--

INSERT INTO `customerContactDetails` (`contactId`, `customerId`, `contactPerson`, `email`, `phone`, `website`, `fax`, `createdAt`, `updatedAt`) VALUES
(1, 1, '', '', '', '', '', '2025-01-17 18:32:36', '2025-01-17 18:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customerId` int(11) NOT NULL,
  `companyId` int(11) NOT NULL,
  `customerName` varchar(255) NOT NULL,
  `customerVat` varchar(20) NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postalCode` varchar(10) NOT NULL,
  `country` int(11) NOT NULL,
  `accountNumber` varchar(50) DEFAULT NULL,
  `isSupplier` tinyint(1) DEFAULT 0,
  `isRecipient` tinyint(1) DEFAULT 0,
  `isOffice` tinyint(1) DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customerId`, `companyId`, `customerName`, `customerVat`, `street`, `city`, `postalCode`, `country`, `accountNumber`, `isSupplier`, `isRecipient`, `isOffice`, `createdAt`, `updatedAt`) VALUES
(1, 1, 'ASSECO BUSINESS SOLUTIONS SPÓŁKA AKCYJNA', 'PL5222612717', 'ul. Konrada Wallenroda 4C', 'Lublin', '20-607', 20, '', 1, 1, 0, '2025-01-17 18:32:36', '2025-01-17 18:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `document_type`
--

CREATE TABLE `document_type` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `signature` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_type`
--

INSERT INTO `document_type` (`id`, `name`, `signature`) VALUES
(1, 'Faktura', 'FV');

-- --------------------------------------------------------

--
-- Table structure for table `flate_rate`
--

CREATE TABLE `flate_rate` (
  `ryczalt_id` bigint(20) UNSIGNED NOT NULL,
  `companyId` int(11) NOT NULL,
  `lp` int(11) DEFAULT NULL,
  `dateOfEntry` date NOT NULL,
  `dateOfReceipt` date NOT NULL,
  `documentNumber` varchar(255) NOT NULL,
  `isClose` tinyint(1) DEFAULT 0,
  `rate3` decimal(12,2) DEFAULT 0.00,
  `rate5_5` decimal(12,2) DEFAULT 0.00,
  `rate8_5` decimal(12,2) DEFAULT 0.00,
  `rate10` decimal(12,2) DEFAULT 0.00,
  `rate12` decimal(12,2) DEFAULT 0.00,
  `rate12_5` decimal(12,2) DEFAULT 0.00,
  `rate14` decimal(12,2) DEFAULT 0.00,
  `rate15` decimal(12,2) DEFAULT 0.00,
  `rate17` decimal(12,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `vatRegisterId` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flate_rate`
--

INSERT INTO `flate_rate` (`ryczalt_id`, `companyId`, `lp`, `dateOfEntry`, `dateOfReceipt`, `documentNumber`, `isClose`, `rate3`, `rate5_5`, `rate8_5`, `rate10`, `rate12`, `rate12_5`, `rate14`, `rate15`, `rate17`, `remarks`, `vatRegisterId`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(3, 1, 1, '2025-01-17', '2025-01-17', 'ddd', 0, 0.00, 0.00, 0.00, 0.00, 11000.00, 0.00, 2000.00, 0.00, 0.00, '0', NULL, 1, '2025-01-17 20:16:20', 1, '2025-01-21 06:13:51'),
(11, 1, 2, '2025-01-20', '2025-01-20', 'FV1/2025', 0, 0.00, 0.00, 0.00, 0.00, 2000.00, 0.00, 0.00, 0.00, 0.00, '', 2, 1, '2025-01-20 17:14:03', 1, '2025-01-21 06:13:51'),
(22, 1, 3, '2025-01-20', '2025-01-20', '5/2025', 0, 0.00, 0.00, 0.00, 0.00, 11000.00, 0.00, 0.00, 0.00, 0.00, '', 13, 1, '2025-01-20 19:49:47', 1, '2025-01-21 06:13:51'),
(23, 1, 4, '2025-01-21', '2025-01-21', 'FA234=2025', 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1250.00, '', 15, 1, '2025-01-21 06:13:51', 1, '2025-01-21 06:13:51');

-- --------------------------------------------------------

--
-- Table structure for table `license`
--

CREATE TABLE `license` (
  `id` int(11) NOT NULL,
  `licenseNumber` varchar(50) NOT NULL,
  `companyId` int(11) NOT NULL,
  `dataStart` date NOT NULL,
  `dataEnd` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `license`
--

INSERT INTO `license` (`id`, `licenseNumber`, `companyId`, `dataStart`, `dataEnd`, `created_at`, `updated_at`) VALUES
(1, 'Lic6020084026Enterprise', 1, '2025-01-01', '2026-01-01', '2025-01-16 20:01:44', '2025-01-16 20:01:44');

-- --------------------------------------------------------

--
-- Table structure for table `registerVat`
--

CREATE TABLE `registerVat` (
  `vatRegisterId` int(11) NOT NULL,
  `companyId` int(11) NOT NULL,
  `documentTypeId` int(11) NOT NULL,
  `documentDate` date NOT NULL,
  `taxLiabilityDate` date NOT NULL,
  `dateOfSell` date NOT NULL,
  `documentNumber` varchar(100) NOT NULL,
  `customerId` int(11) NOT NULL,
  `rate23Net` decimal(18,2) DEFAULT 0.00,
  `rate23Vat` decimal(18,2) DEFAULT 0.00,
  `rate23Gross` decimal(18,2) DEFAULT 0.00,
  `sell23Net` decimal(18,2) DEFAULT 0.00,
  `sell23Vat` decimal(18,2) DEFAULT 0.00,
  `sell23Gross` decimal(18,2) DEFAULT 0.00,
  `sell23ZWNet` decimal(18,2) DEFAULT 0.00,
  `sell23ZWVat` decimal(18,2) DEFAULT 0.00,
  `sell23ZWGross` decimal(18,2) DEFAULT 0.00,
  `rate8Net` decimal(18,2) DEFAULT 0.00,
  `rate8Vat` decimal(18,2) DEFAULT 0.00,
  `rate8Gross` decimal(18,2) DEFAULT 0.00,
  `sell8Net` decimal(18,2) DEFAULT 0.00,
  `sell8Vat` decimal(18,2) DEFAULT 0.00,
  `sell8Gross` decimal(18,2) DEFAULT 0.00,
  `sell8ZWNet` decimal(18,2) DEFAULT 0.00,
  `sell8ZWVat` decimal(18,2) DEFAULT 0.00,
  `sell8ZWGross` decimal(18,2) DEFAULT 0.00,
  `rate5Net` decimal(18,2) DEFAULT 0.00,
  `rate5Vat` decimal(18,2) DEFAULT 0.00,
  `rate5Gross` decimal(18,2) DEFAULT 0.00,
  `sell5Net` decimal(18,2) DEFAULT 0.00,
  `sell5Vat` decimal(18,2) DEFAULT 0.00,
  `sell5Gross` decimal(18,2) DEFAULT 0.00,
  `sell5ZWNet` decimal(18,2) DEFAULT 0.00,
  `sell5ZWVat` decimal(18,2) DEFAULT 0.00,
  `sell5ZWGross` decimal(18,2) DEFAULT 0.00,
  `rate0` decimal(18,2) DEFAULT 0.00,
  `export0` decimal(18,2) DEFAULT 0.00,
  `wdt0` decimal(18,2) DEFAULT 0.00,
  `wsu` decimal(18,2) DEFAULT 0.00,
  `exemptSales` decimal(18,2) DEFAULT 0.00,
  `reverseCharge` decimal(18,2) DEFAULT 0.00,
  `isDelivery` tinyint(1) DEFAULT 0,
  `isServices` tinyint(1) DEFAULT 0,
  `isCustomerPayer` tinyint(1) DEFAULT 0,
  `ryczaltId` int(11) DEFAULT NULL,
  `wnt` tinyint(1) DEFAULT 0,
  `importOutsideUe` tinyint(1) DEFAULT 0,
  `importServicesUe` tinyint(1) DEFAULT 0,
  `importServicesOutsideUe` tinyint(1) DEFAULT 0,
  `deduction50` tinyint(1) DEFAULT 0,
  `fixedAssets` tinyint(1) DEFAULT 0,
  `correctFixedAssets` tinyint(1) DEFAULT 0,
  `MPP` tinyint(1) DEFAULT 0,
  `purchaseFixedAssets` tinyint(1) DEFAULT 0,
  `isReverseCharge` tinyint(1) DEFAULT 0,
  `purchaseMarking` varchar(100) DEFAULT NULL,
  `isThreeSided` tinyint(1) DEFAULT 0,
  `isSell` tinyint(1) DEFAULT 0,
  `isClosed` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `modified_by` int(11) DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registerVat`
--

INSERT INTO `registerVat` (`vatRegisterId`, `companyId`, `documentTypeId`, `documentDate`, `taxLiabilityDate`, `dateOfSell`, `documentNumber`, `customerId`, `rate23Net`, `rate23Vat`, `rate23Gross`, `sell23Net`, `sell23Vat`, `sell23Gross`, `sell23ZWNet`, `sell23ZWVat`, `sell23ZWGross`, `rate8Net`, `rate8Vat`, `rate8Gross`, `sell8Net`, `sell8Vat`, `sell8Gross`, `sell8ZWNet`, `sell8ZWVat`, `sell8ZWGross`, `rate5Net`, `rate5Vat`, `rate5Gross`, `sell5Net`, `sell5Vat`, `sell5Gross`, `sell5ZWNet`, `sell5ZWVat`, `sell5ZWGross`, `rate0`, `export0`, `wdt0`, `wsu`, `exemptSales`, `reverseCharge`, `isDelivery`, `isServices`, `isCustomerPayer`, `ryczaltId`, `wnt`, `importOutsideUe`, `importServicesUe`, `importServicesOutsideUe`, `deduction50`, `fixedAssets`, `correctFixedAssets`, `MPP`, `purchaseFixedAssets`, `isReverseCharge`, `purchaseMarking`, `isThreeSided`, `isSell`, `isClosed`, `created_by`, `created_at`, `modified_by`, `modified_at`) VALUES
(3, 1, 1, '2025-01-20', '2025-01-20', '2025-01-20', '1/2025', 1, 2000.00, 460.00, 2460.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1000.00, 80.00, 1080.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1000.00, 50.00, 1050.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1000.00, 1000.00, 1000.00, 1000.00, 1000.00, 1000.00, 0, 1, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, 1, 0, 1, '2025-01-20 18:42:16', 1, '2025-01-20 21:06:09'),
(7, 1, 1, '2025-01-20', '2025-01-20', '2025-01-20', '4/2025', 1, 1000.00, 230.00, 1230.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 17, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, 1, 0, 1, '2025-01-20 19:06:35', NULL, NULL),
(13, 1, 1, '2025-01-20', '2025-01-20', '2025-01-20', '5/2025', 1, 11000.00, 2530.00, 13530.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 22, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, 1, 0, 1, '2025-01-20 20:49:56', NULL, NULL),
(14, 1, 1, '2025-01-21', '2025-01-21', '2025-01-21', 'ui234i', 1, 2000.00, 460.00, 2460.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, 1, 0, 1, '2025-01-21 07:08:22', NULL, NULL),
(15, 1, 1, '2025-01-21', '2025-01-21', '2025-01-21', 'FA234=2025', 1, 1250.00, 287.50, 1537.50, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 23, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, 1, 0, 1, '2025-01-21 07:13:59', NULL, NULL),
(22, 1, 1, '2025-01-21', '2025-01-21', '2025-01-21', 'FZ1/2025', 1, 0.00, 0.00, 0.00, 1000.00, 230.00, 1230.00, 500.00, 115.00, 615.00, 0.00, 0.00, 0.00, 1000.00, 80.00, 1080.00, 500.00, 40.00, 540.00, 0.00, 0.00, 0.00, 1000.00, 50.00, 1050.00, 500.00, 25.00, 525.00, 3000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0, NULL, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, '', 0, 0, 0, 1, '2025-01-21 07:55:55', 1, '2025-01-21 08:28:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `company_id`, `first_name`, `last_name`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 1, 'Jakub', 'Kumięga', '2025-01-21 07:05:28', '2025-01-16 19:52:57', '2025-01-21 06:05:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `close_month`
--
ALTER TABLE `close_month`
  ADD UNIQUE KEY `company_id` (`company_id`,`month`,`year`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `companyId` (`companyId`);

--
-- Indexes for table `customerAddressDetails`
--
ALTER TABLE `customerAddressDetails`
  ADD PRIMARY KEY (`addressId`),
  ADD KEY `customerId` (`customerId`),
  ADD KEY `country` (`country`);

--
-- Indexes for table `customerContactDetails`
--
ALTER TABLE `customerContactDetails`
  ADD PRIMARY KEY (`contactId`),
  ADD KEY `customerId` (`customerId`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customerId`),
  ADD KEY `idx_company_vat` (`companyId`,`customerVat`),
  ADD KEY `idx_company_name` (`companyId`,`customerName`),
  ADD KEY `country` (`country`);

--
-- Indexes for table `document_type`
--
ALTER TABLE `document_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `signature` (`signature`);

--
-- Indexes for table `flate_rate`
--
ALTER TABLE `flate_rate`
  ADD PRIMARY KEY (`ryczalt_id`),
  ADD UNIQUE KEY `flate_rate_company_document_unique` (`companyId`,`documentNumber`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `license`
--
ALTER TABLE `license`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `licenseNumber` (`licenseNumber`),
  ADD KEY `fk_license_company` (`companyId`);

--
-- Indexes for table `registerVat`
--
ALTER TABLE `registerVat`
  ADD PRIMARY KEY (`vatRegisterId`),
  ADD KEY `FK_RegisterVat_Company` (`companyId`),
  ADD KEY `FK_RegisterVat_Customer` (`customerId`),
  ADD KEY `FK_RegisterVat_CreatedBy` (`created_by`),
  ADD KEY `FK_RegisterVat_ModifiedBy` (`modified_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `fk_user_company` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `customerAddressDetails`
--
ALTER TABLE `customerAddressDetails`
  MODIFY `addressId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customerContactDetails`
--
ALTER TABLE `customerContactDetails`
  MODIFY `contactId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customerId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `document_type`
--
ALTER TABLE `document_type`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `flate_rate`
--
ALTER TABLE `flate_rate`
  MODIFY `ryczalt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `license`
--
ALTER TABLE `license`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registerVat`
--
ALTER TABLE `registerVat`
  MODIFY `vatRegisterId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `close_month`
--
ALTER TABLE `close_month`
  ADD CONSTRAINT `close_month_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `countries`
--
ALTER TABLE `countries`
  ADD CONSTRAINT `countries_ibfk_1` FOREIGN KEY (`companyId`) REFERENCES `companies` (`id`);

--
-- Constraints for table `customerAddressDetails`
--
ALTER TABLE `customerAddressDetails`
  ADD CONSTRAINT `customeraddressdetails_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `customers` (`customerId`) ON DELETE CASCADE,
  ADD CONSTRAINT `customeraddressdetails_ibfk_2` FOREIGN KEY (`country`) REFERENCES `countries` (`id`);

--
-- Constraints for table `customerContactDetails`
--
ALTER TABLE `customerContactDetails`
  ADD CONSTRAINT `customercontactdetails_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `customers` (`customerId`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`country`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`companyId`) REFERENCES `companies` (`id`);

--
-- Constraints for table `flate_rate`
--
ALTER TABLE `flate_rate`
  ADD CONSTRAINT `fk_flate_rate_company` FOREIGN KEY (`companyId`) REFERENCES `companies` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `flate_rate_ibfk_1` FOREIGN KEY (`companyId`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `flate_rate_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `flate_rate_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `license`
--
ALTER TABLE `license`
  ADD CONSTRAINT `fk_license_company` FOREIGN KEY (`companyId`) REFERENCES `companies` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `registerVat`
--
ALTER TABLE `registerVat`
  ADD CONSTRAINT `FK_RegisterVat_Company` FOREIGN KEY (`companyId`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `FK_RegisterVat_CreatedBy` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_RegisterVat_Customer` FOREIGN KEY (`customerId`) REFERENCES `customers` (`customerId`),
  ADD CONSTRAINT `FK_RegisterVat_ModifiedBy` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
