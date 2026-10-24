-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 24, 2026 at 11:08 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nexus`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` char(36) NOT NULL,
  `account_number` varchar(24) NOT NULL COMMENT 'Account Number',
  `iban` varchar(34) DEFAULT NULL COMMENT 'IBAN -- form par "P K" prefilled boxes',
  `title_of_account` varchar(200) NOT NULL COMMENT 'Title of Account',
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `profit_center` varchar(20) DEFAULT NULL COMMENT 'Profit Center',
  `opening_date` date NOT NULL COMMENT 'Bank Use Only ka "Date" box',
  `account_product_id` char(36) NOT NULL,
  `product_other` varchar(150) DEFAULT NULL COMMENT '"Other ____ (Please Specify)"',
  `currency_id` char(36) NOT NULL,
  `currency_other` varchar(60) DEFAULT NULL COMMENT 'FCY "Other (Please Specify)"',
  `account_class` enum('current','saving','term') NOT NULL COMMENT 'FCY box par Current / Saving / Term ka alag tick',
  `is_foreign_currency` tinyint(1) NOT NULL DEFAULT 0,
  `operating_instruction_id` char(36) NOT NULL,
  `operating_instruction_other` varchar(150) DEFAULT NULL COMMENT 'Other => "(Please Specify)"',
  `special_instructions` text DEFAULT NULL,
  `sms_alerts_subscribed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"Would you like to subscribe for paid SMS Services..." Yes/No',
  `digital_channels_opted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"Would you like to avail BAJK Digital Channels/App financial transaction facility?"',
  `digital_channels_biometric_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"on the basis of provided account biometric verification"',
  `statement_delivery_mode_id` char(36) DEFAULT NULL,
  `statement_frequency_id` char(36) DEFAULT NULL,
  `mailing_address_id` char(36) DEFAULT NULL,
  `initial_deposit` decimal(18,2) DEFAULT NULL COMMENT 'Bank Use Only: "Initial Deposit"',
  `zakat_applicable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"Zakat is applicable in PKR-Saving Account Only" + customer ka exemption status',
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `aof_version` varchar(60) DEFAULT NULL COMMENT 'Kaunsa form version sign hua: BAJK-AOF-Individual-19-Jan-2026 / BAJK-AOF-Govt-Public-Private-28-Jan-2026',
  `status` enum('pending','active','closed') NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `opened_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_cheque_books`
--

CREATE TABLE `account_cheque_books` (
  `id` char(36) NOT NULL,
  `account_id` char(36) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Cheque Book Required: Yes / No',
  `quantity_required` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'Quantity Required',
  `leaves_count` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'No. of Leaves: 10 / 25 / 50 / 100',
  `leaves_other` varchar(60) DEFAULT NULL COMMENT 'Others => "(Please Specify)"',
  `requested_on` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_debit_cards`
--

CREATE TABLE `account_debit_cards` (
  `id` char(36) NOT NULL,
  `account_id` char(36) NOT NULL,
  `account_holder_id` char(36) DEFAULT NULL,
  `card_type_id` char(36) NOT NULL,
  `name_on_card` varchar(19) NOT NULL COMMENT 'Name on Card -- max 19 characters with spaces',
  `requested_on` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_holders`
--

CREATE TABLE `account_holders` (
  `id` char(36) NOT NULL,
  `account_id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `holder_role` enum('primary','joint','sole_proprietor','minor','guardian','authorized_signatory','mandate_holder','agent','executor','trustee','office_bearer') NOT NULL DEFAULT 'primary',
  `applicant_number` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Form par "Applicant''s (1)".."(12)" ka number',
  `is_signatory` tinyint(1) NOT NULL DEFAULT 1,
  `signing_order` tinyint(3) UNSIGNED DEFAULT NULL,
  `relationship_id` char(36) DEFAULT NULL,
  `designation` varchar(120) DEFAULT NULL COMMENT 'Entity: Director / Secretary / DDO / Trustee etc. (governing body list se)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_opening_histories`
--

CREATE TABLE `account_opening_histories` (
  `id` char(36) NOT NULL,
  `account_opening_request_id` char(36) NOT NULL,
  `event` varchar(30) NOT NULL COMMENT 'created, step_saved, submitted, approved, rejected, unlocked',
  `step` varchar(30) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{"Field label": {"old": "...", "new": "..."}}' CHECK (json_valid(`changes`)),
  `note` text DEFAULT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_opening_histories`
--

INSERT INTO `account_opening_histories` (`id`, `account_opening_request_id`, `event`, `step`, `user_id`, `changes`, `note`, `created_at`) VALUES
('01a13aff-4c5f-70d8-a4b8-38b7c93fdf02', '01a13aff-4c4e-733d-b067-1f4e013c8a69', 'created', NULL, 88, NULL, 'Request started for 0011 Arja Branch.', '2026-10-14 15:19:32.192034'),
('01a13b00-a546-73fd-a62d-6fc121b3acd4', '01a13b00-a51e-702c-a2ae-f00257a771a6', 'created', NULL, 88, NULL, 'Old account opening form started for 0011 Arja Branch.', '2026-10-14 15:21:00.486437');

-- --------------------------------------------------------

--
-- Table structure for table `account_opening_paper_forms`
--

CREATE TABLE `account_opening_paper_forms` (
  `id` char(36) NOT NULL,
  `account_opening_request_id` char(36) NOT NULL,
  `display_name` varchar(200) DEFAULT NULL COMMENT 'Title of account / company name, for the request list and search',
  `key_word` text DEFAULT NULL COMMENT 'Key/Secret Word, encrypted',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Everything else the paper form holds' CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_opening_paper_forms`
--

INSERT INTO `account_opening_paper_forms` (`id`, `account_opening_request_id`, `display_name`, `key_word`, `data`, `created_at`, `updated_at`) VALUES
('01a13b00-a53c-709a-b805-67d504063339', '01a13b00-a51e-702c-a2ae-f00257a771a6', NULL, NULL, '[]', '2026-10-14 15:21:00', '2026-10-14 15:21:00');

-- --------------------------------------------------------

--
-- Table structure for table `account_opening_requests`
--

CREATE TABLE `account_opening_requests` (
  `id` char(36) NOT NULL,
  `request_number` varchar(30) NOT NULL,
  `aof_form_type` enum('individual_joint_sole','entity') NOT NULL COMMENT '15-page Individual/Joint/Sole Proprietor form ya 17-page Govt/Partnership/Company/NGO form',
  `form_edition` varchar(10) NOT NULL DEFAULT 'new' COMMENT 'new = current AOF, old = old BAJK paper AOF',
  `aof_version` varchar(60) DEFAULT NULL COMMENT 'BAJK-AOF-Individual-19-Jan-2026 / BAJK-AOF-Govt-Public-Private-28-Jan-2026',
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `account_id` char(36) DEFAULT NULL,
  `request_date` date NOT NULL,
  `terms_and_conditions_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `indemnity_undertaking_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `aof_copy_received_by_customer` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"I/We confirm having received the copy of the Account Opening Form along with Terms & Conditions"',
  `shariah_compliant_investment_authorized` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Individual form: "authorize the Bank to invest the deposit in only Shariah Compliant manner"',
  `vernacular_form_attached` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Important Note (g): customer English/Urdu ke ilawa sign kare to 100 rupee bond paper par vernacular form',
  `qa22_form_attached` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Important Note (f): QA-22 form for foreigner residing in Pakistan',
  `sales_staff_name_1` varchar(150) DEFAULT NULL COMMENT 'Name of Sale Staff/relationship Mgr (1)',
  `sales_staff_employee_no_1` varchar(30) DEFAULT NULL COMMENT 'Employee No. (1)',
  `sales_staff_name_2` varchar(150) DEFAULT NULL COMMENT 'Name of Sale Staff/relationship Mgr (2)',
  `sales_staff_employee_no_2` varchar(30) DEFAULT NULL COMMENT 'Employee No. (2)',
  `authorized_by_name` varchar(150) DEFAULT NULL COMMENT '*Authorized by (Name)',
  `authorized_by_employee_no` varchar(30) DEFAULT NULL COMMENT 'Employee Number/PP.No',
  `authorized_on` date DEFAULT NULL,
  `checker_name` varchar(150) DEFAULT NULL COMMENT '*Branch Supervisor/Data Entry Checker (Ind) ya *Branch Manager/Data Entry Checker (Ent)',
  `checker_employee_no` varchar(30) DEFAULT NULL COMMENT 'Employee Number/PP.No',
  `checked_on` date DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','rejected') NOT NULL DEFAULT 'draft',
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL COMMENT 'Set when approved: nobody can edit until a super admin unlocks it',
  `unlocked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `unlocked_at` timestamp NULL DEFAULT NULL,
  `unlock_reason` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_opening_requests`
--

INSERT INTO `account_opening_requests` (`id`, `request_number`, `aof_form_type`, `form_edition`, `aof_version`, `branch_id`, `customer_id`, `account_id`, `request_date`, `terms_and_conditions_accepted`, `indemnity_undertaking_accepted`, `aof_copy_received_by_customer`, `shariah_compliant_investment_authorized`, `vernacular_form_attached`, `qa22_form_attached`, `sales_staff_name_1`, `sales_staff_employee_no_1`, `sales_staff_name_2`, `sales_staff_employee_no_2`, `authorized_by_name`, `authorized_by_employee_no`, `authorized_on`, `checker_name`, `checker_employee_no`, `checked_on`, `status`, `submitted_by`, `submitted_at`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `locked_at`, `unlocked_by`, `unlocked_at`, `unlock_reason`, `rejection_reason`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01a13aff-4c4e-733d-b067-1f4e013c8a69', '0011-20261014-0001', 'individual_joint_sole', 'new', 'BAJK-AOF-Individual-19-Jan-2026', 11, '01a13aff-4c2a-73b1-9926-4dc2e59ff46f', NULL, '2026-10-14', 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 88, 88, '2026-10-14 15:19:32', '2026-10-14 15:19:32', NULL),
('01a13b00-a51e-702c-a2ae-f00257a771a6', '0011-20261014-0002', 'individual_joint_sole', 'old', 'BAJK-AOF-Old-Paper-Form', 11, NULL, NULL, '2026-10-14', 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 88, 88, '2026-10-14 15:21:00', '2026-10-14 15:21:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `account_products`
--

CREATE TABLE `account_products` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL COMMENT 'BAJK Current Account, PLS Saving Account, Special Deposit Account (SDA), Bemisal Mahana Bachat Account (BMBA), Premium Plus Remittance Saving Account (PPRSA), BAJK Current Account-FCY, BAJK Saving Account-FCY',
  `name_ur` varchar(150) DEFAULT NULL,
  `product_class` enum('current','saving','term') NOT NULL COMMENT 'Form headings: "Current Products" / "Saving Products" / "Term Products"',
  `currency_type` enum('local','foreign') NOT NULL DEFAULT 'local' COMMENT 'Rupee box vs Foreign Currency box',
  `available_for` enum('individual','entity','both') NOT NULL DEFAULT 'both' COMMENT 'PPRSA sirf Individual AOF par chhapa hai',
  `is_zakat_applicable` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Form: "Zakat is applicable in PKR-Saving Account Only"',
  `allows_debit_card` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Form: debit card "for Individual & sole proprietor accounts only"',
  `allowed_cheque_leaves` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '[10,25,50] Individual | [25,50,100] Entity' CHECK (json_valid(`allowed_cheque_leaves`)),
  `is_other` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"Other ____ (Please Specify)" box',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_products`
--

INSERT INTO `account_products` (`id`, `code`, `name`, `name_ur`, `product_class`, `currency_type`, `available_for`, `is_zakat_applicable`, `allows_debit_card`, `allowed_cheque_leaves`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18a4-7213-9707-d22ee28734a5', 'BCA', 'BAJK Current Account', 'بی اے جے کے کرنٹ اکاؤنٹ', 'current', 'local', 'both', 0, 1, '\"[10,25,50]\"', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a5-70d8-bd43-6a965957a285', 'PLS', 'PLS Saving Account (PLS)', 'پی ایل ایس سیونگ اکاؤنٹ', 'saving', 'local', 'both', 1, 1, '\"[10,25,50]\"', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a5-70d8-bd43-6a9659be2bd5', 'SDA', 'Special Deposit Account (SDA)', 'سپیشل ڈپازٹ اکاؤنٹ', 'saving', 'local', 'both', 1, 1, '\"[10,25,50]\"', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a6-70ef-83a8-efa23e503e5e', 'BMBA', 'Bemisal Mahana Bachat Account (BMBA)', 'بے مثال ماہانہ بچت اکاؤنٹ', 'saving', 'local', 'both', 1, 1, '\"[10,25,50]\"', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a6-70ef-83a8-efa23e7968f3', 'PPRSA', 'Premium Plus Remittance Saving Account (PPRSA)', 'پریمیم پلس ریمیٹنس سیونگ اکاؤنٹ', 'saving', 'local', 'individual', 1, 1, '\"[10,25,50]\"', 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a7-71b3-811e-afa60e211769', 'BCA_FCY', 'BAJK Current Account - FCY', 'بی اے جے کے کرنٹ اکاؤنٹ ایف سی وائی', 'current', 'foreign', 'both', 0, 0, '\"[25,50,100]\"', 0, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a7-71b3-811e-afa60e44bef3', 'BSA_FCY', 'BAJK Saving Account - FCY', 'بی اے جے کے سیونگ اکاؤنٹ ایف سی وائی', 'saving', 'foreign', 'both', 0, 0, '\"[25,50,100]\"', 0, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a8-7285-933d-bbe075069865', 'BMBA_FCY', 'Bemisal Mahana Bachat Account (BMBA) - FCY', 'بے مثال ماہانہ بچت اکاؤنٹ ایف سی وائی', 'saving', 'foreign', 'both', 0, 0, '\"[25,50,100]\"', 0, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a8-7285-933d-bbe0759d1a14', 'TERM_FCY', 'BAJK Term Account - FCY', 'بی اے جے کے ٹرم اکاؤنٹ ایف سی وائی', 'term', 'foreign', 'both', 0, 0, '\"[25,50,100]\"', 0, 9, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `account_purposes`
--

CREATE TABLE `account_purposes` (
  `id` char(36) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'Saving, Business, Transactional, Credit Facility, Others',
  `name_ur` varchar(80) DEFAULT NULL,
  `applies_to` enum('individual','entity','both') NOT NULL DEFAULT 'both' COMMENT 'Transactional sirf Individual AOF par, Credit Facility sirf Business CDD par',
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_purposes`
--

INSERT INTO `account_purposes` (`id`, `code`, `name`, `name_ur`, `applies_to`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18cb-7195-a5f9-70d1f7ed9373', 'SAVING', 'Saving', 'بچت', 'both', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18cc-7241-8b78-7a80b22cb0b4', 'BUSINESS', 'Business', 'کاروبار', 'both', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18cc-7241-8b78-7a80b234e168', 'TRANSACTIONAL', 'Transactional', 'لین دین', 'individual', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18cd-717d-94ed-aae91b0245f6', 'CREDIT_FACILITY', 'Credit Facility', 'قرض کی سہولت', 'entity', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18cd-717d-94ed-aae91b410ac9', 'PURPOSE_OTHERS', 'Others', 'دیگر', 'both', 1, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` char(36) DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` char(36) DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` varchar(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'default', 'Region has been created', 'App\\Models\\Region', 'created', '1', NULL, NULL, '{\"attributes\":{\"id\":1,\"name\":\"Muzaffarabad\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:03.000000Z\",\"updated_at\":\"2025-12-12T11:40:03.000000Z\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(2, 'default', 'Region has been created', 'App\\Models\\Region', 'created', '2', NULL, NULL, '{\"attributes\":{\"id\":2,\"name\":\"Rawalakot\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:03.000000Z\",\"updated_at\":\"2025-12-12T11:40:03.000000Z\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(3, 'default', 'Region has been created', 'App\\Models\\Region', 'created', '3', NULL, NULL, '{\"attributes\":{\"id\":3,\"name\":\"Kotli\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:03.000000Z\",\"updated_at\":\"2025-12-12T11:40:03.000000Z\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(4, 'default', 'Region has been created', 'App\\Models\\Region', 'created', '4', NULL, NULL, '{\"attributes\":{\"id\":4,\"name\":\"Mirpur\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:03.000000Z\",\"updated_at\":\"2025-12-12T11:40:03.000000Z\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(5, 'default', 'District has been created', 'App\\Models\\District', 'created', '1', NULL, NULL, '{\"attributes\":{\"region_id\":1,\"name\":\"Muzaffarabad\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(6, 'default', 'District has been created', 'App\\Models\\District', 'created', '2', NULL, NULL, '{\"attributes\":{\"region_id\":1,\"name\":\"Neelum\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(7, 'default', 'District has been created', 'App\\Models\\District', 'created', '3', NULL, NULL, '{\"attributes\":{\"region_id\":1,\"name\":\"Jhelum Valley\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(8, 'default', 'District has been created', 'App\\Models\\District', 'created', '4', NULL, NULL, '{\"attributes\":{\"region_id\":2,\"name\":\"Bagh\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(9, 'default', 'District has been created', 'App\\Models\\District', 'created', '5', NULL, NULL, '{\"attributes\":{\"region_id\":2,\"name\":\"Poonch\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(10, 'default', 'District has been created', 'App\\Models\\District', 'created', '6', NULL, NULL, '{\"attributes\":{\"region_id\":2,\"name\":\"Haveli\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(11, 'default', 'District has been created', 'App\\Models\\District', 'created', '7', NULL, NULL, '{\"attributes\":{\"region_id\":2,\"name\":\"Sudhanoti\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(12, 'default', 'District has been created', 'App\\Models\\District', 'created', '8', NULL, NULL, '{\"attributes\":{\"region_id\":3,\"name\":\"Kotli\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(13, 'default', 'District has been created', 'App\\Models\\District', 'created', '9', NULL, NULL, '{\"attributes\":{\"region_id\":4,\"name\":\"Mirpur\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(14, 'default', 'District has been created', 'App\\Models\\District', 'created', '10', NULL, NULL, '{\"attributes\":{\"region_id\":4,\"name\":\"Bhimber\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(15, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '1', NULL, NULL, '{\"attributes\":{\"name\":\"Main Branch Muzaffarabad\",\"code\":\"0001\",\"address\":\"Fatima Plaza, Chatter Domel Muzaffarabad\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(16, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '2', NULL, NULL, '{\"attributes\":{\"name\":\"Main Branch Mirpur\",\"code\":\"0002\",\"address\":\"Nishat Centre, Allama Iqbal Road, Mirpur\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(17, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '3', NULL, NULL, '{\"attributes\":{\"name\":\"Dadyal Branch\",\"code\":\"0003\",\"address\":\"Maqbool Butt Shaheed Chowk, Dadyal\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(18, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '4', NULL, NULL, '{\"attributes\":{\"name\":\"Chaksawari Branch\",\"code\":\"0004\",\"address\":\"Opposite Al-Shahzad Hotel Chaksawari\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(19, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '5', NULL, NULL, '{\"attributes\":{\"name\":\"Kotli Branch\",\"code\":\"0005\",\"address\":\"Poonch \\/ Rawalpindi Road Kotli\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(20, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '6', NULL, NULL, '{\"attributes\":{\"name\":\"Rawalakot Branch\",\"code\":\"0006\",\"address\":\"Gulf Shopping Complex Katchari Road Rawalakot\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(21, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '7', NULL, NULL, '{\"attributes\":{\"name\":\"Sehnsa Branch\",\"code\":\"0007\",\"address\":\"Main Bazar Sehnsa AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(22, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '8', NULL, NULL, '{\"attributes\":{\"name\":\"Athmuqam Branch\",\"code\":\"0008\",\"address\":\"Main Bazar Athmuqam District Neelum AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(23, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '9', NULL, NULL, '{\"attributes\":{\"name\":\"Islamgarh Branch\",\"code\":\"0009\",\"address\":\"Main Bazar Islamgarh District Mirpur AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(24, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '10', NULL, NULL, '{\"attributes\":{\"name\":\"Bagh Branch\",\"code\":\"0010\",\"address\":\"Main Bazar Bagh AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(25, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '11', NULL, NULL, '{\"attributes\":{\"name\":\"Arja Branch\",\"code\":\"0011\",\"address\":\"Bagh\\/Rawalakot Chowk Arja District Bagh AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(26, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '12', NULL, NULL, '{\"attributes\":{\"name\":\"Bhimber Branch\",\"code\":\"0012\",\"address\":\"Main Bazar Bhimber AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(27, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '13', NULL, NULL, '{\"attributes\":{\"name\":\"Pallandari Branch\",\"code\":\"0013\",\"address\":\"Main Bazar Pallandari AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(28, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '14', NULL, NULL, '{\"attributes\":{\"name\":\"Nar Branch\",\"code\":\"0014\",\"address\":\"Main Bazar Nar District Kotli AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(29, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '15', NULL, NULL, '{\"attributes\":{\"name\":\"F-3 Branch, Mirpur\",\"code\":\"0015\",\"address\":\"Sector F-3 Mirpur AJK\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(30, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '16', NULL, NULL, '{\"attributes\":{\"name\":\"Hajeera Branch\",\"code\":\"0016\",\"address\":\"Main Bazar Hajeera District Poonch Azad Kashmir\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(31, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '17', NULL, NULL, '{\"attributes\":{\"name\":\"Garhi-Dupata Branch\",\"code\":\"0017\",\"address\":\"Pull Bazar Garhi Dupatta Muzaffarabad A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(32, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '18', NULL, NULL, '{\"attributes\":{\"name\":\"Lower Plate Branch\",\"code\":\"0018\",\"address\":\"Near City Hospital Lower Plate Muzaffarabad A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(33, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '19', NULL, NULL, '{\"attributes\":{\"name\":\"Chatter Klass Branch\",\"code\":\"0019\",\"address\":\"Kullian Bazar, Kohala-Muzaffarabad Road\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(34, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '20', NULL, NULL, '{\"attributes\":{\"name\":\"Brahtla Branch\",\"code\":\"0020\",\"address\":\"Main Bazar Brahtla District Kotli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(35, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '21', NULL, NULL, '{\"attributes\":{\"name\":\"Khui Ratta Branch\",\"code\":\"0021\",\"address\":\"Main Bazar Khui Ratta District Kotli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(36, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '22', NULL, NULL, '{\"attributes\":{\"name\":\"Chakaar Branch\",\"code\":\"0022\",\"address\":\"Main Bazar Chakaar District Muzaffarabad A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(37, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '23', NULL, NULL, '{\"attributes\":{\"name\":\"Dheerkot Branch\",\"code\":\"0023\",\"address\":\"Main Bazar Dheerkot District Bagh A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(38, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '24', NULL, NULL, '{\"attributes\":{\"name\":\"Pathika Branch\",\"code\":\"0024\",\"address\":\"Main Bazar Pathika District Muzaffarabad A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(39, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '25', NULL, NULL, '{\"attributes\":{\"name\":\"Bandi Abbaspur Branch\",\"code\":\"0025\",\"address\":\"Pul Bazar Bandi Abbaspur District Poonch A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(40, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '26', NULL, NULL, '{\"attributes\":{\"name\":\"Sarsawa Branch\",\"code\":\"0026\",\"address\":\"Main Bazar Sarsawa District Kotli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(41, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '27', NULL, NULL, '{\"attributes\":{\"name\":\"Pulmanda Branch\",\"code\":\"0027\",\"address\":\"Main Bazar Pulmanda District Mirpur A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(42, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '28', NULL, NULL, '{\"attributes\":{\"name\":\"Madina Market Branch\",\"code\":\"0028\",\"address\":\"Madina Market District Muzaffarabad A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(43, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '29', NULL, NULL, '{\"attributes\":{\"name\":\"Barnala Branch\",\"code\":\"0029\",\"address\":\"Main Bazar Barnala District Bhimber A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(44, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '30', NULL, NULL, '{\"attributes\":{\"name\":\"Khai-gala Branch\",\"code\":\"0030\",\"address\":\"Main Bazar Khai-gala District Poonch A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(45, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '31', NULL, NULL, '{\"attributes\":{\"name\":\"Khad-Gujran Branch\",\"code\":\"0031\",\"address\":\"Main Bazar Khad-Gujran District Kotli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(46, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '32', NULL, NULL, '{\"attributes\":{\"name\":\"Trarkhal Branch\",\"code\":\"0032\",\"address\":\"Main Bazar Tararkhal District Sudhanoti\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(47, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '33', NULL, NULL, '{\"attributes\":{\"name\":\"Chowki Branch\",\"code\":\"0033\",\"address\":\"Main Bazar Chowki District Bhimber A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(48, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '34', NULL, NULL, '{\"attributes\":{\"name\":\"Hattian Branch\",\"code\":\"0034\",\"address\":\"Sakhi Plaza, Chowk Bazar, Hattian Bala\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(49, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '35', NULL, NULL, '{\"attributes\":{\"name\":\"Poona Branch\",\"code\":\"0035\",\"address\":\"Main Bazar Poona District Bhimber A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(50, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '36', NULL, NULL, '{\"attributes\":{\"name\":\"Charhoi Branch\",\"code\":\"0036\",\"address\":\"Main Bazar Charhoi District Kotli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(51, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '37', NULL, NULL, '{\"attributes\":{\"name\":\"Chakothi Branch\",\"code\":\"0037\",\"address\":\"Main Bazar Chakhoti District Hattian A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(52, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '38', NULL, NULL, '{\"attributes\":{\"name\":\"Kahotta Branch\",\"code\":\"0038\",\"address\":\"Main Bazar Kahotta District Haveli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(53, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '39', NULL, NULL, '{\"attributes\":{\"name\":\"Paniola Branch\",\"code\":\"0039\",\"address\":\"Main Bazar Paniola District Poonch A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(54, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '40', NULL, NULL, '{\"attributes\":{\"name\":\"District Complex Muzaffarabad Branch\",\"code\":\"0040\",\"address\":\"Old Secretariat District Complex District Muzaffarabd\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(55, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '41', NULL, NULL, '{\"attributes\":{\"name\":\"Bloch Branch\",\"code\":\"0041\",\"address\":\"Main Bazar Bloch District Sudhanoti\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(56, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '42', NULL, NULL, '{\"attributes\":{\"name\":\"Tatta-Pani Branch\",\"code\":\"0042\",\"address\":\"Main Bazar Tatta-Pani District Kotli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(57, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '43', NULL, NULL, '{\"attributes\":{\"name\":\"Sharda Branch\",\"code\":\"0043\",\"address\":\"Main Sharda Bazar District Neelum A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(58, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '44', NULL, NULL, '{\"attributes\":{\"name\":\"Mi-Toti Branch\",\"code\":\"0044\",\"address\":\"Main Bazar Mi-Toti District Kotli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(59, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '45', NULL, NULL, '{\"attributes\":{\"name\":\"Jatlan Branch\",\"code\":\"0045\",\"address\":\"Main Bazar Jatlan District Mirpur\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(60, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '46', NULL, NULL, '{\"attributes\":{\"name\":\"Mian Muhammad Town Branch\",\"code\":\"0046\",\"address\":\"Mian Muhammad Town District Mirpur A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(61, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '47', NULL, NULL, '{\"attributes\":{\"name\":\"Jhatti Dheri Branch\",\"code\":\"0047\",\"address\":\"Jhatti Dheri Cross District Mirpur A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(62, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '48', NULL, NULL, '{\"attributes\":{\"name\":\"Dhanna Branch\",\"code\":\"0048\",\"address\":\"Dhanna Bazar District Kotli A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(63, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '49', NULL, NULL, '{\"attributes\":{\"name\":\"Talian Branch\",\"code\":\"0049\",\"address\":\"Main Bazar Talian District Sadhnutti A.K\"}}', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(64, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '50', NULL, NULL, '{\"attributes\":{\"name\":\"Thorar Branch\",\"code\":\"0050\",\"address\":\"Main Bazar Thorar District Poonch A.K\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(65, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '51', NULL, NULL, '{\"attributes\":{\"name\":\"Juna Branch\",\"code\":\"0051\",\"address\":\"Main Bazar Juna District Kotli A.K\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(66, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '52', NULL, NULL, '{\"attributes\":{\"name\":\"Joora Branch\",\"code\":\"0052\",\"address\":\"Main Bazar Joora District Neelum A.K\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(67, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '53', NULL, NULL, '{\"attributes\":{\"name\":\"Ladies Branch, Muzaffarabad\",\"code\":\"0053\",\"address\":\"Hashim Plaza, Poultry Farm Road, Muzaffarabad, AK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(68, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '54', NULL, NULL, '{\"attributes\":{\"name\":\"Ladies Branch, Mirpur\",\"code\":\"0054\",\"address\":\"Haris Plaza Chowk Shaheedan Sector C-2 Mirpur AK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(69, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '55', NULL, NULL, '{\"attributes\":{\"name\":\"Seharmandi Branch\",\"code\":\"0055\",\"address\":\"Main Bazar Seharmandi, Tehsil Sehensa District Kotli AK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(70, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '56', NULL, NULL, '{\"attributes\":{\"name\":\"CMH Road Rawalakot Branch\",\"code\":\"0056\",\"address\":\"CMH Road Rawalakot AK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(71, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '57', NULL, NULL, '{\"attributes\":{\"name\":\"Main Bazar Chakswari Branch\",\"code\":\"0057\",\"address\":\"Main Bazar Chakswari Tehsil & District Mirpur AK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(72, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '58', NULL, NULL, '{\"attributes\":{\"name\":\"Ratta Branch Mirpur\",\"code\":\"0058\",\"address\":\"Allah Abad Market Main Road Ratta Tehsil Dadyal Distt. Mirpur\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(73, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '59', NULL, NULL, '{\"attributes\":{\"name\":\"Moil Branch\",\"code\":\"0059\",\"address\":\"Tehsil & District Bhimber AK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(74, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '60', NULL, NULL, '{\"attributes\":{\"name\":\"Banbehk Branch\",\"code\":\"0060\",\"address\":\"Tehsil & District Poonch\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(75, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '61', NULL, NULL, '{\"attributes\":{\"name\":\"Upper Ada Branch\",\"code\":\"0061\",\"address\":\"Near Shah Anayat Darbar Upper Ada Muzaffarabad\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(76, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '62', NULL, NULL, '{\"attributes\":{\"name\":\"Chathian Branch\",\"code\":\"0062\",\"address\":\"Srinagar Road, Chathian Bazar, Dist. Hattian Bala\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(77, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '63', NULL, NULL, '{\"attributes\":{\"name\":\"Kutton Branch\",\"code\":\"0063\",\"address\":\"Kutton, Tehsil Authmuqam, District Neelum\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(78, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '64', NULL, NULL, '{\"attributes\":{\"name\":\"Jarahi Branch\",\"code\":\"0064\",\"address\":\"Tehsil Dolya Jattan, District Kotli\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(79, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '65', NULL, NULL, '{\"attributes\":{\"name\":\"Goi Branch\",\"code\":\"0065\",\"address\":\"Village & P.O Goi Tehsil & District Kotli\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(80, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '66', NULL, NULL, '{\"attributes\":{\"name\":\"Lorry Adda Branch\",\"code\":\"0066\",\"address\":\"Lorry Adda, Tehsil & District Kotli, AJK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(81, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '67', NULL, NULL, '{\"attributes\":{\"name\":\"Gojra By-Pass Branch\",\"code\":\"0067\",\"address\":\"Gojra By-Pass Road, Near WAPDA Colony, Muzaffarabad AJK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(82, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '68', NULL, NULL, '{\"attributes\":{\"name\":\"Mang Branch\",\"code\":\"0068\",\"address\":\"Mang Bazar, Near AC Office, District Sudhnuti AJK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(83, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '69', NULL, NULL, '{\"attributes\":{\"name\":\"Kundal Shahi Branch\",\"code\":\"0069\",\"address\":\"Main Bazar, Kundal Shahi, Tehsil Athmuqam, District Neelum\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(84, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '70', NULL, NULL, '{\"attributes\":{\"name\":\"Brarkot Branch\",\"code\":\"0070\",\"address\":\"Abbottabad Road Brarkot Tehsil & District Muzaffarabad A.K\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(85, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '71', NULL, NULL, '{\"attributes\":{\"name\":\"Muslimabad Branch\",\"code\":\"0071\",\"address\":\"Pattan Sher Khan, Tehsil Mang, District Sudhunoti, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(86, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '72', NULL, NULL, '{\"attributes\":{\"name\":\"Islam Nagar Branch\",\"code\":\"0072\",\"address\":\"Pattan Sher Khan, Tehsil Mang, District Sudhunoti, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(87, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '73', NULL, NULL, '{\"attributes\":{\"name\":\"Samahni Branch\",\"code\":\"0073\",\"address\":\"Shahrah-e-Jammu, Samahni, District Bhimber, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(88, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '74', NULL, NULL, '{\"attributes\":{\"name\":\"Panthal Branch\",\"code\":\"0074\",\"address\":\"Panthal Branch, Tehsil and District Sudhanoti, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(89, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '75', NULL, NULL, '{\"attributes\":{\"name\":\"Nakyal Branch\",\"code\":\"0075\",\"address\":\"Nakyal, Fatehpur Thakyala, District Kotli, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(90, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '76', NULL, NULL, '{\"attributes\":{\"name\":\"Chinari Branch\",\"code\":\"0076\",\"address\":\"Main Bazar Chinari, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(91, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '77', NULL, NULL, '{\"attributes\":{\"name\":\"PM House Road Branch\",\"code\":\"0077\",\"address\":\"Near PM House Muzaffarabad, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(92, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '78', NULL, NULL, '{\"attributes\":{\"name\":\"Kot Jamel Branch\",\"code\":\"0078\",\"address\":\"Kot Jamel Tehsil Barnala Distt. Bhimber, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(93, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '79', NULL, NULL, '{\"attributes\":{\"name\":\"Kotli Road Branch Mirpur\",\"code\":\"0079\",\"address\":\"Kotli Road branch opposite PWD Office Mirpur, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(94, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '80', NULL, NULL, '{\"attributes\":{\"name\":\"Mangla Hamlet\",\"code\":\"0080\",\"address\":\"Main Bazar, Mangla Hamlet, Tehsil and District, Mirpur, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(95, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '81', NULL, NULL, '{\"attributes\":{\"name\":\"Khanabad\",\"code\":\"0081\",\"address\":\"Khanabad Tehsil and District Mirpur, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(96, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '82', NULL, NULL, '{\"attributes\":{\"name\":\"Bypass Road Branch\",\"code\":\"0082\",\"address\":\"Gulhar Shareef Chowk, Kotli, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(97, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '83', NULL, NULL, '{\"attributes\":{\"name\":\"Public Housing Scheme Branch\",\"code\":\"0083\",\"address\":\"Bank Road Rawalakot (AK)\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(98, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '84', NULL, NULL, '{\"attributes\":{\"name\":\"Main Bazar Leepa\",\"code\":\"0084\",\"address\":\"Jhelum Valley (AJ&K)\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(99, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '85', NULL, NULL, '{\"attributes\":{\"name\":\"Chella Bandi Branch\",\"code\":\"0085\",\"address\":\"Neelum Road, Chella Bandi, Muzaffarabad AJK\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(100, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '86', NULL, NULL, '{\"attributes\":{\"name\":\"Hari Ghel Branch\",\"code\":\"0086\",\"address\":\"Opposite To Go Petrol Pump Tehsil Hari Ghel District Bagh, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(101, 'default', 'Branch has been created', 'App\\Models\\Branch', 'created', '87', NULL, NULL, '{\"attributes\":{\"name\":\"Chechian Branch\",\"code\":\"0087\",\"address\":\"Main Bazar Chechian District Mirpur, Azad Kashmir\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(102, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '1', NULL, NULL, '{\"attributes\":{\"id\":1,\"branch_id\":1,\"contact\":\"05288-921254\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(103, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '2', NULL, NULL, '{\"attributes\":{\"id\":2,\"branch_id\":1,\"contact\":\"05822-921181\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(104, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '3', NULL, NULL, '{\"attributes\":{\"id\":3,\"branch_id\":1,\"contact\":\"05822-921365\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(105, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '4', NULL, NULL, '{\"attributes\":{\"id\":4,\"branch_id\":2,\"contact\":\"05827-921448\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(106, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '5', NULL, NULL, '{\"attributes\":{\"id\":5,\"branch_id\":2,\"contact\":\"05827-921442\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(107, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '6', NULL, NULL, '{\"attributes\":{\"id\":6,\"branch_id\":3,\"contact\":\"05827-923070\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(108, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '7', NULL, NULL, '{\"attributes\":{\"id\":7,\"branch_id\":3,\"contact\":\"05827-923069\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(109, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '8', NULL, NULL, '{\"attributes\":{\"id\":8,\"branch_id\":4,\"contact\":\"05827-922664\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(110, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '9', NULL, NULL, '{\"attributes\":{\"id\":9,\"branch_id\":4,\"contact\":\"05822-922665\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(111, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '10', NULL, NULL, '{\"attributes\":{\"id\":10,\"branch_id\":5,\"contact\":\"05826-920241\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(112, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '11', NULL, NULL, '{\"attributes\":{\"id\":11,\"branch_id\":5,\"contact\":\"05826-920242\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(113, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '12', NULL, NULL, '{\"attributes\":{\"id\":12,\"branch_id\":6,\"contact\":\"05824-920069\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(114, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '13', NULL, NULL, '{\"attributes\":{\"id\":13,\"branch_id\":6,\"contact\":\"05824-920068\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(115, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '14', NULL, NULL, '{\"attributes\":{\"id\":14,\"branch_id\":7,\"contact\":\"05826-923062\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(116, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '15', NULL, NULL, '{\"attributes\":{\"id\":15,\"branch_id\":7,\"contact\":\"05826-923063\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(117, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '16', NULL, NULL, '{\"attributes\":{\"id\":16,\"branch_id\":8,\"contact\":\"05821-920033\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(118, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '17', NULL, NULL, '{\"attributes\":{\"id\":17,\"branch_id\":8,\"contact\":\"05821-920033\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(119, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '18', NULL, NULL, '{\"attributes\":{\"id\":18,\"branch_id\":9,\"contact\":\"05827-922562\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(120, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '19', NULL, NULL, '{\"attributes\":{\"id\":19,\"branch_id\":9,\"contact\":\"05827-922563\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(121, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '20', NULL, NULL, '{\"attributes\":{\"id\":20,\"branch_id\":10,\"contact\":\"05823-920103\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(122, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '21', NULL, NULL, '{\"attributes\":{\"id\":21,\"branch_id\":10,\"contact\":\"05823-920104\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(123, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '22', NULL, NULL, '{\"attributes\":{\"id\":22,\"branch_id\":11,\"contact\":\"05823-921005\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(124, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '23', NULL, NULL, '{\"attributes\":{\"id\":23,\"branch_id\":11,\"contact\":\"05823-921006\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(125, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '24', NULL, NULL, '{\"attributes\":{\"id\":24,\"branch_id\":12,\"contact\":\"05828-920505\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(126, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '25', NULL, NULL, '{\"attributes\":{\"id\":25,\"branch_id\":12,\"contact\":\"05828-920506\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(127, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '26', NULL, NULL, '{\"attributes\":{\"id\":26,\"branch_id\":13,\"contact\":\"05825-920010\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(128, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '27', NULL, NULL, '{\"attributes\":{\"id\":27,\"branch_id\":13,\"contact\":\"05825-920009\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(129, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '28', NULL, NULL, '{\"attributes\":{\"id\":28,\"branch_id\":14,\"contact\":\"05826-921060\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(130, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '29', NULL, NULL, '{\"attributes\":{\"id\":29,\"branch_id\":14,\"contact\":\"05826-921061\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(131, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '30', NULL, NULL, '{\"attributes\":{\"id\":30,\"branch_id\":15,\"contact\":\"05827-920439\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(132, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '31', NULL, NULL, '{\"attributes\":{\"id\":31,\"branch_id\":15,\"contact\":\"05827-920440\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(133, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '32', NULL, NULL, '{\"attributes\":{\"id\":32,\"branch_id\":16,\"contact\":\"05824-920233\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(134, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '33', NULL, NULL, '{\"attributes\":{\"id\":33,\"branch_id\":16,\"contact\":\"05824-920234\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(135, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '34', NULL, NULL, '{\"attributes\":{\"id\":34,\"branch_id\":17,\"contact\":\"05822-922502\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(136, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '35', NULL, NULL, '{\"attributes\":{\"id\":35,\"branch_id\":17,\"contact\":\"05822-922503\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(137, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '36', NULL, NULL, '{\"attributes\":{\"id\":36,\"branch_id\":18,\"contact\":\"05822-920466\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(138, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '37', NULL, NULL, '{\"attributes\":{\"id\":37,\"branch_id\":18,\"contact\":\"05822-442664\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(139, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '38', NULL, NULL, '{\"attributes\":{\"id\":38,\"branch_id\":19,\"contact\":\"05822-922302\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(140, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '39', NULL, NULL, '{\"attributes\":{\"id\":39,\"branch_id\":19,\"contact\":\"05822-922303\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(141, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '40', NULL, NULL, '{\"attributes\":{\"id\":40,\"branch_id\":20,\"contact\":\"05826-475094\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(142, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '41', NULL, NULL, '{\"attributes\":{\"id\":41,\"branch_id\":20,\"contact\":\"05826-475094\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(143, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '42', NULL, NULL, '{\"attributes\":{\"id\":42,\"branch_id\":21,\"contact\":\"05826-921863\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(144, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '43', NULL, NULL, '{\"attributes\":{\"id\":43,\"branch_id\":21,\"contact\":\"05826-921862\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(145, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '44', NULL, NULL, '{\"attributes\":{\"id\":44,\"branch_id\":22,\"contact\":\"05822-922003\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(146, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '45', NULL, NULL, '{\"attributes\":{\"id\":45,\"branch_id\":22,\"contact\":\"05822-922002\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(147, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '46', NULL, NULL, '{\"attributes\":{\"id\":46,\"branch_id\":23,\"contact\":\"05823-921213\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(148, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '47', NULL, NULL, '{\"attributes\":{\"id\":47,\"branch_id\":23,\"contact\":\"05823-921214\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(149, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '48', NULL, NULL, '{\"attributes\":{\"id\":48,\"branch_id\":24,\"contact\":\"05822-922108\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(150, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '49', NULL, NULL, '{\"attributes\":{\"id\":49,\"branch_id\":24,\"contact\":\"05822-922109\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(151, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '50', NULL, NULL, '{\"attributes\":{\"id\":50,\"branch_id\":25,\"contact\":\"05824-921027\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(152, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '51', NULL, NULL, '{\"attributes\":{\"id\":51,\"branch_id\":25,\"contact\":\"05824-921028\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(153, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '52', NULL, NULL, '{\"attributes\":{\"id\":52,\"branch_id\":26,\"contact\":\"05826-921163\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(154, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '53', NULL, NULL, '{\"attributes\":{\"id\":53,\"branch_id\":26,\"contact\":\"05826-921164\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(155, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '54', NULL, NULL, '{\"attributes\":{\"id\":54,\"branch_id\":27,\"contact\":\"05827-922260\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(156, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '55', NULL, NULL, '{\"attributes\":{\"id\":55,\"branch_id\":27,\"contact\":\"05827-922261\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(157, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '56', NULL, NULL, '{\"attributes\":{\"id\":56,\"branch_id\":28,\"contact\":\"05822-923126\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(158, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '57', NULL, NULL, '{\"attributes\":{\"id\":57,\"branch_id\":28,\"contact\":\"05822-923127\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(159, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '58', NULL, NULL, '{\"attributes\":{\"id\":58,\"branch_id\":29,\"contact\":\"05828-922064\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(160, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '59', NULL, NULL, '{\"attributes\":{\"id\":59,\"branch_id\":29,\"contact\":\"05828-922063\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(161, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '60', NULL, NULL, '{\"attributes\":{\"id\":60,\"branch_id\":30,\"contact\":\"05824-920911\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(162, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '61', NULL, NULL, '{\"attributes\":{\"id\":61,\"branch_id\":30,\"contact\":\"05824-920912\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(163, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '62', NULL, NULL, '{\"attributes\":{\"id\":62,\"branch_id\":31,\"contact\":\"05826-922361\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(164, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '63', NULL, NULL, '{\"attributes\":{\"id\":63,\"branch_id\":31,\"contact\":\"05826-922362\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(165, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '64', NULL, NULL, '{\"attributes\":{\"id\":64,\"branch_id\":32,\"contact\":\"05825-920224\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(166, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '65', NULL, NULL, '{\"attributes\":{\"id\":65,\"branch_id\":32,\"contact\":\"05825-920223\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(167, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '66', NULL, NULL, '{\"attributes\":{\"id\":66,\"branch_id\":33,\"contact\":\"05828-922162\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(168, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '67', NULL, NULL, '{\"attributes\":{\"id\":67,\"branch_id\":33,\"contact\":\"05828-922261\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(169, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '68', NULL, NULL, '{\"attributes\":{\"id\":68,\"branch_id\":34,\"contact\":\"05822-922631\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(170, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '69', NULL, NULL, '{\"attributes\":{\"id\":69,\"branch_id\":34,\"contact\":\"05822-922632\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(171, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '70', NULL, NULL, '{\"attributes\":{\"id\":70,\"branch_id\":35,\"contact\":\"05827-485533\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(172, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '71', NULL, NULL, '{\"attributes\":{\"id\":71,\"branch_id\":35,\"contact\":\"05827-485533\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(173, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '72', NULL, NULL, '{\"attributes\":{\"id\":72,\"branch_id\":36,\"contact\":\"05826-922165\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(174, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '73', NULL, NULL, '{\"attributes\":{\"id\":73,\"branch_id\":36,\"contact\":\"05826-922165\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(175, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '74', NULL, NULL, '{\"attributes\":{\"id\":74,\"branch_id\":37,\"contact\":\"05822-921826\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(176, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '75', NULL, NULL, '{\"attributes\":{\"id\":75,\"branch_id\":37,\"contact\":\"05822-921826\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(177, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '76', NULL, NULL, '{\"attributes\":{\"id\":76,\"branch_id\":38,\"contact\":\"05823-921772\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(178, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '77', NULL, NULL, '{\"attributes\":{\"id\":77,\"branch_id\":38,\"contact\":\"05823-921772\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(179, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '78', NULL, NULL, '{\"attributes\":{\"id\":78,\"branch_id\":39,\"contact\":\"05824-921608\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(180, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '79', NULL, NULL, '{\"attributes\":{\"id\":79,\"branch_id\":39,\"contact\":\"05824-921608\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(181, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '80', NULL, NULL, '{\"attributes\":{\"id\":80,\"branch_id\":40,\"contact\":\"05822-920043\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(182, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '81', NULL, NULL, '{\"attributes\":{\"id\":81,\"branch_id\":40,\"contact\":\"05822-920043\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(183, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '82', NULL, NULL, '{\"attributes\":{\"id\":82,\"branch_id\":41,\"contact\":\"05825-920316\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(184, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '83', NULL, NULL, '{\"attributes\":{\"id\":83,\"branch_id\":41,\"contact\":\"05825-9203167\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(185, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '84', NULL, NULL, '{\"attributes\":{\"id\":84,\"branch_id\":42,\"contact\":\"05826-921461\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(186, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '85', NULL, NULL, '{\"attributes\":{\"id\":85,\"branch_id\":42,\"contact\":\"05826-921461\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(187, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '86', NULL, NULL, '{\"attributes\":{\"id\":86,\"branch_id\":43,\"contact\":\"05821-920802\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(188, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '87', NULL, NULL, '{\"attributes\":{\"id\":87,\"branch_id\":43,\"contact\":\"05821-920802\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(189, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '88', NULL, NULL, '{\"attributes\":{\"id\":88,\"branch_id\":44,\"contact\":\"05826-921865\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(190, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '89', NULL, NULL, '{\"attributes\":{\"id\":89,\"branch_id\":44,\"contact\":\"05826-921865\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(191, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '90', NULL, NULL, '{\"attributes\":{\"id\":90,\"branch_id\":45,\"contact\":\"05827-922764\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(192, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '91', NULL, NULL, '{\"attributes\":{\"id\":91,\"branch_id\":45,\"contact\":\"05827-922764\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(193, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '92', NULL, NULL, '{\"attributes\":{\"id\":92,\"branch_id\":46,\"contact\":\"05827-920442\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(194, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '93', NULL, NULL, '{\"attributes\":{\"id\":93,\"branch_id\":46,\"contact\":\"05827-920442\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(195, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '94', NULL, NULL, '{\"attributes\":{\"id\":94,\"branch_id\":47,\"contact\":\"05827-922564\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(196, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '95', NULL, NULL, '{\"attributes\":{\"id\":95,\"branch_id\":47,\"contact\":\"05827-922564\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(197, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '96', NULL, NULL, '{\"attributes\":{\"id\":96,\"branch_id\":48,\"contact\":\"05826-921761\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(198, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '97', NULL, NULL, '{\"attributes\":{\"id\":97,\"branch_id\":48,\"contact\":\"05826-921761\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(199, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '98', NULL, NULL, '{\"attributes\":{\"id\":98,\"branch_id\":49,\"contact\":\"05826-471319\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(200, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '99', NULL, NULL, '{\"attributes\":{\"id\":99,\"branch_id\":49,\"contact\":\"05826-471319\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(201, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '100', NULL, NULL, '{\"attributes\":{\"id\":100,\"branch_id\":50,\"contact\":\"05824-921313\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(202, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '101', NULL, NULL, '{\"attributes\":{\"id\":101,\"branch_id\":50,\"contact\":\"05824-921313\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(203, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '102', NULL, NULL, '{\"attributes\":{\"id\":102,\"branch_id\":51,\"contact\":\"05826-474441\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(204, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '103', NULL, NULL, '{\"attributes\":{\"id\":103,\"branch_id\":51,\"contact\":\"05826-474441\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(205, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '104', NULL, NULL, '{\"attributes\":{\"id\":104,\"branch_id\":52,\"contact\":\"05821-920502\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(206, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '105', NULL, NULL, '{\"attributes\":{\"id\":105,\"branch_id\":52,\"contact\":\"05821-920502\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(207, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '106', NULL, NULL, '{\"attributes\":{\"id\":106,\"branch_id\":53,\"contact\":\"05822-923007\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(208, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '107', NULL, NULL, '{\"attributes\":{\"id\":107,\"branch_id\":53,\"contact\":\"05822-923008\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(209, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '108', NULL, NULL, '{\"attributes\":{\"id\":108,\"branch_id\":54,\"contact\":\"05827-920405\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(210, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '109', NULL, NULL, '{\"attributes\":{\"id\":109,\"branch_id\":54,\"contact\":\"05827-920406\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(211, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '110', NULL, NULL, '{\"attributes\":{\"id\":110,\"branch_id\":55,\"contact\":\"05826-923150\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(212, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '111', NULL, NULL, '{\"attributes\":{\"id\":111,\"branch_id\":55,\"contact\":\"05826-923150\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(213, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '112', NULL, NULL, '{\"attributes\":{\"id\":112,\"branch_id\":56,\"contact\":\"05824-920545\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(214, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '113', NULL, NULL, '{\"attributes\":{\"id\":113,\"branch_id\":56,\"contact\":\"05824-920544\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(215, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '114', NULL, NULL, '{\"attributes\":{\"id\":114,\"branch_id\":57,\"contact\":\"05827-922666\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(216, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '115', NULL, NULL, '{\"attributes\":{\"id\":115,\"branch_id\":57,\"contact\":\"05827-922666\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(217, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '116', NULL, NULL, '{\"attributes\":{\"id\":116,\"branch_id\":58,\"contact\":\"05827-923011\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(218, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '117', NULL, NULL, '{\"attributes\":{\"id\":117,\"branch_id\":58,\"contact\":\"05827-923011\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(219, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '118', NULL, NULL, '{\"attributes\":{\"id\":118,\"branch_id\":59,\"contact\":\"05330-29731\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(220, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '119', NULL, NULL, '{\"attributes\":{\"id\":119,\"branch_id\":59,\"contact\":\"05330-29731\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(221, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '120', NULL, NULL, '{\"attributes\":{\"id\":120,\"branch_id\":60,\"contact\":\"05824-921106\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(222, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '121', NULL, NULL, '{\"attributes\":{\"id\":121,\"branch_id\":60,\"contact\":\"05824-921106\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(223, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '122', NULL, NULL, '{\"attributes\":{\"id\":122,\"branch_id\":61,\"contact\":\"05822-920747\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(224, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '123', NULL, NULL, '{\"attributes\":{\"id\":123,\"branch_id\":61,\"contact\":\"05822-920746\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(225, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '124', NULL, NULL, '{\"attributes\":{\"id\":124,\"branch_id\":62,\"contact\":\"05822-922514\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(226, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '125', NULL, NULL, '{\"attributes\":{\"id\":125,\"branch_id\":62,\"contact\":\"05822-922514\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(227, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '126', NULL, NULL, '{\"attributes\":{\"id\":126,\"branch_id\":63,\"contact\":\"05821-920302\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(228, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '127', NULL, NULL, '{\"attributes\":{\"id\":127,\"branch_id\":63,\"contact\":\"05821-920303\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(229, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '128', NULL, NULL, '{\"attributes\":{\"id\":128,\"branch_id\":64,\"contact\":\"05826-923350\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:04.000000Z\",\"updated_at\":\"2025-12-12T11:40:04.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(230, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '129', NULL, NULL, '{\"attributes\":{\"id\":129,\"branch_id\":64,\"contact\":\"05826-923350\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(231, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '130', NULL, NULL, '{\"attributes\":{\"id\":130,\"branch_id\":65,\"contact\":\"05826-480649\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(232, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '131', NULL, NULL, '{\"attributes\":{\"id\":131,\"branch_id\":65,\"contact\":\"05826-480649\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(233, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '132', NULL, NULL, '{\"attributes\":{\"id\":132,\"branch_id\":66,\"contact\":\"05826-920247\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(234, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '133', NULL, NULL, '{\"attributes\":{\"id\":133,\"branch_id\":66,\"contact\":\"05826-920248\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(235, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '134', NULL, NULL, '{\"attributes\":{\"id\":134,\"branch_id\":67,\"contact\":\"05822-923020\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(236, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '135', NULL, NULL, '{\"attributes\":{\"id\":135,\"branch_id\":67,\"contact\":\"05822-923019\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(237, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '136', NULL, NULL, '{\"attributes\":{\"id\":136,\"branch_id\":68,\"contact\":\"05825-920715\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(238, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '137', NULL, NULL, '{\"attributes\":{\"id\":137,\"branch_id\":68,\"contact\":\"05825-920716\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(239, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '138', NULL, NULL, '{\"attributes\":{\"id\":138,\"branch_id\":69,\"contact\":\"05821-920342\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(240, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '139', NULL, NULL, '{\"attributes\":{\"id\":139,\"branch_id\":69,\"contact\":\"05821-920343\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(241, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '140', NULL, NULL, '{\"attributes\":{\"id\":140,\"branch_id\":70,\"contact\":\"0301-3117420\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(242, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '141', NULL, NULL, '{\"attributes\":{\"id\":141,\"branch_id\":71,\"contact\":\"0315-3191313\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(243, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '142', NULL, NULL, '{\"attributes\":{\"id\":142,\"branch_id\":72,\"contact\":\"0345-5939806\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(244, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '143', NULL, NULL, '{\"attributes\":{\"id\":143,\"branch_id\":73,\"contact\":\"05828-922165-66\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(245, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '144', NULL, NULL, '{\"attributes\":{\"id\":144,\"branch_id\":74,\"contact\":\"0346-5463091\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(246, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '145', NULL, NULL, '{\"attributes\":{\"id\":145,\"branch_id\":75,\"contact\":\"05826-922045-46\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(247, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '146', NULL, NULL, '{\"attributes\":{\"id\":146,\"branch_id\":76,\"contact\":\"05826-922045-46\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(248, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '147', NULL, NULL, '{\"attributes\":{\"id\":147,\"branch_id\":77,\"contact\":\"05822-920862\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(249, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '148', NULL, NULL, '{\"attributes\":{\"id\":148,\"branch_id\":78,\"contact\":\"05828-922068\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(250, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '149', NULL, NULL, '{\"attributes\":{\"id\":149,\"branch_id\":79,\"contact\":\"05827-920079\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(251, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '150', NULL, NULL, '{\"attributes\":{\"id\":150,\"branch_id\":79,\"contact\":\"05827-940751\",\"status\":\"fax\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(252, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '151', NULL, NULL, '{\"attributes\":{\"id\":151,\"branch_id\":80,\"contact\":\"05827-922448\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(253, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '152', NULL, NULL, '{\"attributes\":{\"id\":152,\"branch_id\":80,\"contact\":\"05827-922449\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(254, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '153', NULL, NULL, '{\"attributes\":{\"id\":153,\"branch_id\":81,\"contact\":\"05827-922654\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(255, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '154', NULL, NULL, '{\"attributes\":{\"id\":154,\"branch_id\":81,\"contact\":\"05827-922656\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(256, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '155', NULL, NULL, '{\"attributes\":{\"id\":155,\"branch_id\":82,\"contact\":\"05826-940820\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(257, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '156', NULL, NULL, '{\"attributes\":{\"id\":156,\"branch_id\":83,\"contact\":\"05824-920040\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(258, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '157', NULL, NULL, '{\"attributes\":{\"id\":157,\"branch_id\":83,\"contact\":\"05824-920049\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(259, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '158', NULL, NULL, '{\"attributes\":{\"id\":158,\"branch_id\":84,\"contact\":\"05822-922867\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(260, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '159', NULL, NULL, '{\"attributes\":{\"id\":159,\"branch_id\":84,\"contact\":\"05822-922865\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(261, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '160', NULL, NULL, '{\"attributes\":{\"id\":160,\"branch_id\":85,\"contact\":\"05822-923021\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(262, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '161', NULL, NULL, '{\"attributes\":{\"id\":161,\"branch_id\":86,\"contact\":\"05823-920834\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(263, 'default', 'Contact has been created', 'App\\Models\\Contact', 'created', '162', NULL, NULL, '{\"attributes\":{\"id\":162,\"branch_id\":87,\"contact\":\"05827-922264\",\"status\":\"telephone_no\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(264, 'default', 'User has been created', 'App\\Models\\User', 'created', '1', NULL, NULL, '{\"attributes\":{\"id\":1,\"branch_id\":1,\"name\":\"Branch 0001-1-1\",\"email\":\"manager0001@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:05.000000Z\",\"password\":\"$2y$12$OubvB8I\\/k0yR8Z6pNGQcL.EHUnAgvkrWKvEVSmkZo2Bm7oZJw6m3S\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:05.000000Z\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(265, 'default', 'User has been created', 'App\\Models\\User', 'created', '2', NULL, NULL, '{\"attributes\":{\"id\":2,\"branch_id\":2,\"name\":\"Branch 0002-9-4\",\"email\":\"manager0002@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:06.000000Z\",\"password\":\"$2y$12$6mGMmCBEc1E22Axn7rp0ve2plaKkKh7cOS\\/Qj4JrUmBcIogqrVReS\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:06.000000Z\",\"updated_at\":\"2025-12-12T11:40:06.000000Z\"}}', NULL, '2025-12-12 11:40:06', '2025-12-12 11:40:06'),
(266, 'default', 'User has been created', 'App\\Models\\User', 'created', '3', NULL, NULL, '{\"attributes\":{\"id\":3,\"branch_id\":3,\"name\":\"Branch 0003-9-4\",\"email\":\"manager0003@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:06.000000Z\",\"password\":\"$2y$12$6pWDMoPk6YgeVd59ZQx7L.TGwrUsz.bLU\\/zpZmiRveD4PlhPP3nMe\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:06.000000Z\",\"updated_at\":\"2025-12-12T11:40:06.000000Z\"}}', NULL, '2025-12-12 11:40:06', '2025-12-12 11:40:06'),
(267, 'default', 'User has been created', 'App\\Models\\User', 'created', '4', NULL, NULL, '{\"attributes\":{\"id\":4,\"branch_id\":4,\"name\":\"Branch 0004-9-4\",\"email\":\"manager0004@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:06.000000Z\",\"password\":\"$2y$12$3Mab9VRnyXn6WFmiJWSmyuKPksptQ4xmSeIWq95GCnraF8.AWqvkm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:06.000000Z\",\"updated_at\":\"2025-12-12T11:40:06.000000Z\"}}', NULL, '2025-12-12 11:40:06', '2025-12-12 11:40:06'),
(268, 'default', 'User has been created', 'App\\Models\\User', 'created', '5', NULL, NULL, '{\"attributes\":{\"id\":5,\"branch_id\":5,\"name\":\"Branch 0005-8-3\",\"email\":\"manager0005@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:06.000000Z\",\"password\":\"$2y$12$15GkdnYdImh8np1wwqEzm.CXuECV8fEgoHpnKJkrz\\/UgAxDvJD.MG\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:06.000000Z\",\"updated_at\":\"2025-12-12T11:40:06.000000Z\"}}', NULL, '2025-12-12 11:40:06', '2025-12-12 11:40:06'),
(269, 'default', 'User has been created', 'App\\Models\\User', 'created', '6', NULL, NULL, '{\"attributes\":{\"id\":6,\"branch_id\":6,\"name\":\"Branch 0006-5-2\",\"email\":\"manager0006@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:07.000000Z\",\"password\":\"$2y$12$t9pZqsJNMpUWUKwUNnTVMeS6Wn359UfHY44oLvJMWNVekOsnLx0rm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:07.000000Z\",\"updated_at\":\"2025-12-12T11:40:07.000000Z\"}}', NULL, '2025-12-12 11:40:07', '2025-12-12 11:40:07'),
(270, 'default', 'User has been created', 'App\\Models\\User', 'created', '7', NULL, NULL, '{\"attributes\":{\"id\":7,\"branch_id\":7,\"name\":\"Branch 0007-8-3\",\"email\":\"manager0007@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:07.000000Z\",\"password\":\"$2y$12$3t7.SJic87tlRhYVBktFIuXS1cLSJqXNLs5WLjjeyxF0eImpzjKpa\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:07.000000Z\",\"updated_at\":\"2025-12-12T11:40:07.000000Z\"}}', NULL, '2025-12-12 11:40:07', '2025-12-12 11:40:07'),
(271, 'default', 'User has been created', 'App\\Models\\User', 'created', '8', NULL, NULL, '{\"attributes\":{\"id\":8,\"branch_id\":8,\"name\":\"Branch 0008-2-1\",\"email\":\"manager0008@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:07.000000Z\",\"password\":\"$2y$12$5sUOmI1LwlmsQGcbj\\/6DhOK55z.jB99q4dBMbAK030POBIpmH7ROK\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:07.000000Z\",\"updated_at\":\"2025-12-12T11:40:07.000000Z\"}}', NULL, '2025-12-12 11:40:07', '2025-12-12 11:40:07'),
(272, 'default', 'User has been created', 'App\\Models\\User', 'created', '9', NULL, NULL, '{\"attributes\":{\"id\":9,\"branch_id\":9,\"name\":\"Branch 0009-9-4\",\"email\":\"manager0009@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:07.000000Z\",\"password\":\"$2y$12$bcDPpg\\/0FOFmfbvBWTPQze4ryrRhBrade7QNFRTkMAZ6ZYNFo.SuC\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:07.000000Z\",\"updated_at\":\"2025-12-12T11:40:07.000000Z\"}}', NULL, '2025-12-12 11:40:07', '2025-12-12 11:40:07'),
(273, 'default', 'User has been created', 'App\\Models\\User', 'created', '10', NULL, NULL, '{\"attributes\":{\"id\":10,\"branch_id\":10,\"name\":\"Branch 0010-4-2\",\"email\":\"manager0010@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:08.000000Z\",\"password\":\"$2y$12$qz2fA4yHUY9dwHkVYHkbf.gX6sQMcE\\/dxlY.YgBl4hNwqd3N40GLC\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:08.000000Z\",\"updated_at\":\"2025-12-12T11:40:08.000000Z\"}}', NULL, '2025-12-12 11:40:08', '2025-12-12 11:40:08'),
(274, 'default', 'User has been created', 'App\\Models\\User', 'created', '11', NULL, NULL, '{\"attributes\":{\"id\":11,\"branch_id\":11,\"name\":\"Branch 0011-4-2\",\"email\":\"manager0011@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:08.000000Z\",\"password\":\"$2y$12$JeZrpT2pNxM1PdyEmtcGXuYAg5Pj7Aqm\\/uXODiP0VJaRfIdCKyeR.\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:08.000000Z\",\"updated_at\":\"2025-12-12T11:40:08.000000Z\"}}', NULL, '2025-12-12 11:40:08', '2025-12-12 11:40:08'),
(275, 'default', 'User has been created', 'App\\Models\\User', 'created', '12', NULL, NULL, '{\"attributes\":{\"id\":12,\"branch_id\":12,\"name\":\"Branch 0012-10-4\",\"email\":\"manager0012@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:08.000000Z\",\"password\":\"$2y$12$HcWoBQCKFqkJHFgz.ZCTnOIC2DSH45VnNY1dhWq0UUdsuuBXVDAym\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:08.000000Z\",\"updated_at\":\"2025-12-12T11:40:08.000000Z\"}}', NULL, '2025-12-12 11:40:08', '2025-12-12 11:40:08'),
(276, 'default', 'User has been created', 'App\\Models\\User', 'created', '13', NULL, NULL, '{\"attributes\":{\"id\":13,\"branch_id\":13,\"name\":\"Branch 0013-5-2\",\"email\":\"manager0013@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:08.000000Z\",\"password\":\"$2y$12$UW93vt.9ByigtcgVnaExK.0O66zNsKaI9R1lWWzuYfvXtMOlBaTYu\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:08.000000Z\",\"updated_at\":\"2025-12-12T11:40:08.000000Z\"}}', NULL, '2025-12-12 11:40:08', '2025-12-12 11:40:08'),
(277, 'default', 'User has been created', 'App\\Models\\User', 'created', '14', NULL, NULL, '{\"attributes\":{\"id\":14,\"branch_id\":14,\"name\":\"Branch 0014-8-3\",\"email\":\"manager0014@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:09.000000Z\",\"password\":\"$2y$12$t3xhSVn0afhRdzeNLcsC2uT9ejKB6l6VutyCbu5GE5V.qKDbhx4gO\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:09.000000Z\",\"updated_at\":\"2025-12-12T11:40:09.000000Z\"}}', NULL, '2025-12-12 11:40:09', '2025-12-12 11:40:09'),
(278, 'default', 'User has been created', 'App\\Models\\User', 'created', '15', NULL, NULL, '{\"attributes\":{\"id\":15,\"branch_id\":15,\"name\":\"Branch 0015-9-4\",\"email\":\"manager0015@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:09.000000Z\",\"password\":\"$2y$12$yrCyQ2udN7YDzz8PR4Tode0xDupZpz865EoCtqTViQTj8R3tE4IoG\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:09.000000Z\",\"updated_at\":\"2025-12-12T11:40:09.000000Z\"}}', NULL, '2025-12-12 11:40:09', '2025-12-12 11:40:09'),
(279, 'default', 'User has been created', 'App\\Models\\User', 'created', '16', NULL, NULL, '{\"attributes\":{\"id\":16,\"branch_id\":16,\"name\":\"Branch 0016-5-2\",\"email\":\"manager0016@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:09.000000Z\",\"password\":\"$2y$12$znO4DrwcCDo8FwzkwstRxeKi90Pn6JLGd5qKJx9giDjVmPvTtyaAa\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:09.000000Z\",\"updated_at\":\"2025-12-12T11:40:09.000000Z\"}}', NULL, '2025-12-12 11:40:09', '2025-12-12 11:40:09'),
(280, 'default', 'User has been created', 'App\\Models\\User', 'created', '17', NULL, NULL, '{\"attributes\":{\"id\":17,\"branch_id\":17,\"name\":\"Branch 0017-1-1\",\"email\":\"manager0017@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:10.000000Z\",\"password\":\"$2y$12$o3CfmbzIrz6uesEPuwh0NeloEjj4wXQ6jgupBKQ99K6PWhwXIhN3O\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:10.000000Z\",\"updated_at\":\"2025-12-12T11:40:10.000000Z\"}}', NULL, '2025-12-12 11:40:10', '2025-12-12 11:40:10'),
(281, 'default', 'User has been created', 'App\\Models\\User', 'created', '18', NULL, NULL, '{\"attributes\":{\"id\":18,\"branch_id\":18,\"name\":\"Branch 0018-1-1\",\"email\":\"manager0018@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:10.000000Z\",\"password\":\"$2y$12$omQWW.mWbrK3qmIu9vycmuQY7w87pmRgrphiwifWrUfmZxN21UZRW\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:10.000000Z\",\"updated_at\":\"2025-12-12T11:40:10.000000Z\"}}', NULL, '2025-12-12 11:40:10', '2025-12-12 11:40:10');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(282, 'default', 'User has been created', 'App\\Models\\User', 'created', '19', NULL, NULL, '{\"attributes\":{\"id\":19,\"branch_id\":19,\"name\":\"Branch 0019-1-1\",\"email\":\"manager0019@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:10.000000Z\",\"password\":\"$2y$12$D7JK.27Eb9F\\/e.xTyM9sYO3UIpgGpjjBf.FjUJrJQ\\/76WKQTZvr0O\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:10.000000Z\",\"updated_at\":\"2025-12-12T11:40:10.000000Z\"}}', NULL, '2025-12-12 11:40:10', '2025-12-12 11:40:10'),
(283, 'default', 'User has been created', 'App\\Models\\User', 'created', '20', NULL, NULL, '{\"attributes\":{\"id\":20,\"branch_id\":20,\"name\":\"Branch 0020-8-3\",\"email\":\"manager0020@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:10.000000Z\",\"password\":\"$2y$12$gTg0s3z5xTsQEpz3FLVwB.i1nbeqenMxcu0RcMYMx0EyEljvZ\\/RMW\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:10.000000Z\",\"updated_at\":\"2025-12-12T11:40:10.000000Z\"}}', NULL, '2025-12-12 11:40:10', '2025-12-12 11:40:10'),
(284, 'default', 'User has been created', 'App\\Models\\User', 'created', '21', NULL, NULL, '{\"attributes\":{\"id\":21,\"branch_id\":21,\"name\":\"Branch 0021-8-3\",\"email\":\"manager0021@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:11.000000Z\",\"password\":\"$2y$12$lSgMu.SEkB1po2iSg0osyuSmNJqOM8xBgPIQZOpORS2TQCmTltNhi\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:11.000000Z\",\"updated_at\":\"2025-12-12T11:40:11.000000Z\"}}', NULL, '2025-12-12 11:40:11', '2025-12-12 11:40:11'),
(285, 'default', 'User has been created', 'App\\Models\\User', 'created', '22', NULL, NULL, '{\"attributes\":{\"id\":22,\"branch_id\":22,\"name\":\"Branch 0022-1-1\",\"email\":\"manager0022@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:11.000000Z\",\"password\":\"$2y$12$ukgsrjp.1NY7nqXmGTeocuLt2PwtNzNBDHj9jIIVH1pVQBbC\\/chW6\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:11.000000Z\",\"updated_at\":\"2025-12-12T11:40:11.000000Z\"}}', NULL, '2025-12-12 11:40:11', '2025-12-12 11:40:11'),
(286, 'default', 'User has been created', 'App\\Models\\User', 'created', '23', NULL, NULL, '{\"attributes\":{\"id\":23,\"branch_id\":23,\"name\":\"Branch 0023-4-2\",\"email\":\"manager0023@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:11.000000Z\",\"password\":\"$2y$12$73zfX6T92\\/6HiFFR6ehlxuZg.TO.XIh5XnCXP0QoGveP4HoU\\/v8di\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:11.000000Z\",\"updated_at\":\"2025-12-12T11:40:11.000000Z\"}}', NULL, '2025-12-12 11:40:11', '2025-12-12 11:40:11'),
(287, 'default', 'User has been created', 'App\\Models\\User', 'created', '24', NULL, NULL, '{\"attributes\":{\"id\":24,\"branch_id\":24,\"name\":\"Branch 0024-1-1\",\"email\":\"manager0024@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:11.000000Z\",\"password\":\"$2y$12$gyfGPID\\/UWJs.1mGfITAxO1qipyKavYndW91Ph3X6X5EYbDhCMflK\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:11.000000Z\",\"updated_at\":\"2025-12-12T11:40:11.000000Z\"}}', NULL, '2025-12-12 11:40:11', '2025-12-12 11:40:11'),
(288, 'default', 'User has been created', 'App\\Models\\User', 'created', '25', NULL, NULL, '{\"attributes\":{\"id\":25,\"branch_id\":25,\"name\":\"Branch 0025-5-2\",\"email\":\"manager0025@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:12.000000Z\",\"password\":\"$2y$12$X0gJkdIP9F6jauenfbJ9A.ZdKF9DZvxMEs8K8MVCwvL3XWfBjsRMK\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:12.000000Z\",\"updated_at\":\"2025-12-12T11:40:12.000000Z\"}}', NULL, '2025-12-12 11:40:12', '2025-12-12 11:40:12'),
(289, 'default', 'User has been created', 'App\\Models\\User', 'created', '26', NULL, NULL, '{\"attributes\":{\"id\":26,\"branch_id\":26,\"name\":\"Branch 0026-8-3\",\"email\":\"manager0026@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:12.000000Z\",\"password\":\"$2y$12$hOwRPPaY5Ep2DAiWo1wAFuprLPmb344Yz8QYEFjPezwiHXvKRNXM6\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:12.000000Z\",\"updated_at\":\"2025-12-12T11:40:12.000000Z\"}}', NULL, '2025-12-12 11:40:12', '2025-12-12 11:40:12'),
(290, 'default', 'User has been created', 'App\\Models\\User', 'created', '27', NULL, NULL, '{\"attributes\":{\"id\":27,\"branch_id\":27,\"name\":\"Branch 0027-9-4\",\"email\":\"manager0027@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:12.000000Z\",\"password\":\"$2y$12$jZKy746FDs9oHNhYllHs0.tZoZCM7GoxzGAXAzS1m5begTIiMMP9G\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:12.000000Z\",\"updated_at\":\"2025-12-12T11:40:12.000000Z\"}}', NULL, '2025-12-12 11:40:12', '2025-12-12 11:40:12'),
(291, 'default', 'User has been created', 'App\\Models\\User', 'created', '28', NULL, NULL, '{\"attributes\":{\"id\":28,\"branch_id\":28,\"name\":\"Branch 0028-1-1\",\"email\":\"manager0028@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:12.000000Z\",\"password\":\"$2y$12$7i1y3FPAGWQXl465Ymj2meuJxDqnFaYD5rgbjGf3tI\\/7XLrKqaDi6\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:12.000000Z\",\"updated_at\":\"2025-12-12T11:40:12.000000Z\"}}', NULL, '2025-12-12 11:40:12', '2025-12-12 11:40:12'),
(292, 'default', 'User has been created', 'App\\Models\\User', 'created', '29', NULL, NULL, '{\"attributes\":{\"id\":29,\"branch_id\":29,\"name\":\"Branch 0029-10-4\",\"email\":\"manager0029@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:13.000000Z\",\"password\":\"$2y$12$vXNmRdcsrKoiI3fZ8idib.EuQk2j5kL078bGi5T7BGEG\\/lmoNJ0EG\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:13.000000Z\",\"updated_at\":\"2025-12-12T11:40:13.000000Z\"}}', NULL, '2025-12-12 11:40:13', '2025-12-12 11:40:13'),
(293, 'default', 'User has been created', 'App\\Models\\User', 'created', '30', NULL, NULL, '{\"attributes\":{\"id\":30,\"branch_id\":30,\"name\":\"Branch 0030-5-2\",\"email\":\"manager0030@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:13.000000Z\",\"password\":\"$2y$12$ND4UVseXqCcVPMhtqrN.HO2IXaOKkb5AFRgXw9sq9DRq.hmLkVARq\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:13.000000Z\",\"updated_at\":\"2025-12-12T11:40:13.000000Z\"}}', NULL, '2025-12-12 11:40:13', '2025-12-12 11:40:13'),
(294, 'default', 'User has been created', 'App\\Models\\User', 'created', '31', NULL, NULL, '{\"attributes\":{\"id\":31,\"branch_id\":31,\"name\":\"Branch 0031-8-3\",\"email\":\"manager0031@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:13.000000Z\",\"password\":\"$2y$12$AxMljGg2PFZ68Qfct.\\/KJet6N2QUhqv5e8Oc6UBhHvSHHDH1NcOZK\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:13.000000Z\",\"updated_at\":\"2025-12-12T11:40:13.000000Z\"}}', NULL, '2025-12-12 11:40:13', '2025-12-12 11:40:13'),
(295, 'default', 'User has been created', 'App\\Models\\User', 'created', '32', NULL, NULL, '{\"attributes\":{\"id\":32,\"branch_id\":32,\"name\":\"Branch 0032-7-2\",\"email\":\"manager0032@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:14.000000Z\",\"password\":\"$2y$12$jXtgPqHvBFz1cVVzKxU1Y.G\\/5AiezaKvA6GPEvyyabpK2g\\/SNgrg.\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:14.000000Z\",\"updated_at\":\"2025-12-12T11:40:14.000000Z\"}}', NULL, '2025-12-12 11:40:14', '2025-12-12 11:40:14'),
(296, 'default', 'User has been created', 'App\\Models\\User', 'created', '33', NULL, NULL, '{\"attributes\":{\"id\":33,\"branch_id\":33,\"name\":\"Branch 0033-10-4\",\"email\":\"manager0033@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:14.000000Z\",\"password\":\"$2y$12$Isjb4PF08zWsVoW7xItWj.hcuD\\/1nVm6cCwWo91.HE5f0ds5DbmZu\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:14.000000Z\",\"updated_at\":\"2025-12-12T11:40:14.000000Z\"}}', NULL, '2025-12-12 11:40:14', '2025-12-12 11:40:14'),
(297, 'default', 'User has been created', 'App\\Models\\User', 'created', '34', NULL, NULL, '{\"attributes\":{\"id\":34,\"branch_id\":34,\"name\":\"Branch 0034-3-1\",\"email\":\"manager0034@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:14.000000Z\",\"password\":\"$2y$12$eUWPOUr416HIUuQ4BJDpa.YOuw1URS9G1iQ6PhhNqGIeEk8ddDuxu\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:14.000000Z\",\"updated_at\":\"2025-12-12T11:40:14.000000Z\"}}', NULL, '2025-12-12 11:40:14', '2025-12-12 11:40:14'),
(298, 'default', 'User has been created', 'App\\Models\\User', 'created', '35', NULL, NULL, '{\"attributes\":{\"id\":35,\"branch_id\":35,\"name\":\"Branch 0035-10-4\",\"email\":\"manager0035@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:15.000000Z\",\"password\":\"$2y$12$qnWXuX7uPUWAyqLZCW\\/nIeeqeeB57MzS7ivW6eti0Lyyigd\\/P0tIu\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:15.000000Z\",\"updated_at\":\"2025-12-12T11:40:15.000000Z\"}}', NULL, '2025-12-12 11:40:15', '2025-12-12 11:40:15'),
(299, 'default', 'User has been created', 'App\\Models\\User', 'created', '36', NULL, NULL, '{\"attributes\":{\"id\":36,\"branch_id\":36,\"name\":\"Branch 0036-8-3\",\"email\":\"manager0036@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:15.000000Z\",\"password\":\"$2y$12$UCZ0hNCcP6OJY9aionB4uedVoXrc0WqJk1sSEDP3SAaIPS0KX3s6W\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:15.000000Z\",\"updated_at\":\"2025-12-12T11:40:15.000000Z\"}}', NULL, '2025-12-12 11:40:15', '2025-12-12 11:40:15'),
(300, 'default', 'User has been created', 'App\\Models\\User', 'created', '37', NULL, NULL, '{\"attributes\":{\"id\":37,\"branch_id\":37,\"name\":\"Branch 0037-3-1\",\"email\":\"manager0037@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:15.000000Z\",\"password\":\"$2y$12$j2mF3fN80tf7.\\/AcrEyx\\/euYfjWtQBF6HxpyiHSnBPrNFOp9GTZ5.\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:15.000000Z\",\"updated_at\":\"2025-12-12T11:40:15.000000Z\"}}', NULL, '2025-12-12 11:40:15', '2025-12-12 11:40:15'),
(301, 'default', 'User has been created', 'App\\Models\\User', 'created', '38', NULL, NULL, '{\"attributes\":{\"id\":38,\"branch_id\":38,\"name\":\"Branch 0038-6-2\",\"email\":\"manager0038@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:15.000000Z\",\"password\":\"$2y$12$nTx3H6p9ntEGRnz2SWTMdufdfhHeS2BJCAhtP08DwTZYdPa\\/wIdzG\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:15.000000Z\",\"updated_at\":\"2025-12-12T11:40:15.000000Z\"}}', NULL, '2025-12-12 11:40:15', '2025-12-12 11:40:15'),
(302, 'default', 'User has been created', 'App\\Models\\User', 'created', '39', NULL, NULL, '{\"attributes\":{\"id\":39,\"branch_id\":39,\"name\":\"Branch 0039-5-2\",\"email\":\"manager0039@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:16.000000Z\",\"password\":\"$2y$12$LJ6VFEHP2K.Xhds4o7UW0efNtQgacvSTh2dmWpFtBOEO4q\\/WyXkwy\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:16.000000Z\",\"updated_at\":\"2025-12-12T11:40:16.000000Z\"}}', NULL, '2025-12-12 11:40:16', '2025-12-12 11:40:16'),
(303, 'default', 'User has been created', 'App\\Models\\User', 'created', '40', NULL, NULL, '{\"attributes\":{\"id\":40,\"branch_id\":40,\"name\":\"Branch 0040-1-1\",\"email\":\"manager0040@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:16.000000Z\",\"password\":\"$2y$12$QcBUt4lnOhAGigIopLvUc.C39v\\/xngoWcA0CowCU\\/JX5IM0k81L2.\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:16.000000Z\",\"updated_at\":\"2025-12-12T11:40:16.000000Z\"}}', NULL, '2025-12-12 11:40:16', '2025-12-12 11:40:16'),
(304, 'default', 'User has been created', 'App\\Models\\User', 'created', '41', NULL, NULL, '{\"attributes\":{\"id\":41,\"branch_id\":41,\"name\":\"Branch 0041-7-2\",\"email\":\"manager0041@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:16.000000Z\",\"password\":\"$2y$12$E6qUK9lFHSs51UNBDpWQuenB5BKqlomW6CdbNZmqlLnW3ynf8mSsW\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:16.000000Z\",\"updated_at\":\"2025-12-12T11:40:16.000000Z\"}}', NULL, '2025-12-12 11:40:16', '2025-12-12 11:40:16'),
(305, 'default', 'User has been created', 'App\\Models\\User', 'created', '42', NULL, NULL, '{\"attributes\":{\"id\":42,\"branch_id\":42,\"name\":\"Branch 0042-8-3\",\"email\":\"manager0042@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:16.000000Z\",\"password\":\"$2y$12$6\\/iVxgRkusAxYxlUoYwJk.6vdk.sGGA\\/UKrUCFpmbmam\\/7ylZtKc.\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:16.000000Z\",\"updated_at\":\"2025-12-12T11:40:16.000000Z\"}}', NULL, '2025-12-12 11:40:16', '2025-12-12 11:40:16'),
(306, 'default', 'User has been created', 'App\\Models\\User', 'created', '43', NULL, NULL, '{\"attributes\":{\"id\":43,\"branch_id\":43,\"name\":\"Branch 0043-2-1\",\"email\":\"manager0043@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:17.000000Z\",\"password\":\"$2y$12$\\/Wt8xUsxGJpDKg1GhBceo.II7IGGWsPLt.7nWIAIJ4Vn0wYqE6xPm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:17.000000Z\",\"updated_at\":\"2025-12-12T11:40:17.000000Z\"}}', NULL, '2025-12-12 11:40:17', '2025-12-12 11:40:17'),
(307, 'default', 'User has been created', 'App\\Models\\User', 'created', '44', NULL, NULL, '{\"attributes\":{\"id\":44,\"branch_id\":44,\"name\":\"Branch 0044-8-3\",\"email\":\"manager0044@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:17.000000Z\",\"password\":\"$2y$12$egSZsXAq7F0RkreaiVQv5O2NVtByOiKRd8SrUmBqPQTigca8ubsgi\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:17.000000Z\",\"updated_at\":\"2025-12-12T11:40:17.000000Z\"}}', NULL, '2025-12-12 11:40:17', '2025-12-12 11:40:17'),
(308, 'default', 'User has been created', 'App\\Models\\User', 'created', '45', NULL, NULL, '{\"attributes\":{\"id\":45,\"branch_id\":45,\"name\":\"Branch 0045-9-4\",\"email\":\"manager0045@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:17.000000Z\",\"password\":\"$2y$12$akcXWOgXGPbeQgDHTf2Rn.fyYqcM4CZNQWgtyjUN.9m\\/VN4FX05Oy\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:17.000000Z\",\"updated_at\":\"2025-12-12T11:40:17.000000Z\"}}', NULL, '2025-12-12 11:40:17', '2025-12-12 11:40:17'),
(309, 'default', 'User has been created', 'App\\Models\\User', 'created', '46', NULL, NULL, '{\"attributes\":{\"id\":46,\"branch_id\":46,\"name\":\"Branch 0046-9-4\",\"email\":\"manager0046@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:18.000000Z\",\"password\":\"$2y$12$XRvuiZ7gApX3oESWgPZ69.gwoqBUPcHlk7sh8IhrL6No1iGhK\\/XNy\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:18.000000Z\",\"updated_at\":\"2025-12-12T11:40:18.000000Z\"}}', NULL, '2025-12-12 11:40:18', '2025-12-12 11:40:18'),
(310, 'default', 'User has been created', 'App\\Models\\User', 'created', '47', NULL, NULL, '{\"attributes\":{\"id\":47,\"branch_id\":47,\"name\":\"Branch 0047-9-4\",\"email\":\"manager0047@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:18.000000Z\",\"password\":\"$2y$12$ZGtnlGdnEACAau9rjQC0IeQPUS\\/U9oT0ouI2AT157vK0H1Pva4vb2\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:18.000000Z\",\"updated_at\":\"2025-12-12T11:40:18.000000Z\"}}', NULL, '2025-12-12 11:40:18', '2025-12-12 11:40:18'),
(311, 'default', 'User has been created', 'App\\Models\\User', 'created', '48', NULL, NULL, '{\"attributes\":{\"id\":48,\"branch_id\":48,\"name\":\"Branch 0048-8-3\",\"email\":\"manager0048@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:18.000000Z\",\"password\":\"$2y$12$Sx\\/cujTlPf8ToB0V9CxXWOLSJJniKderyblVE\\/OMM\\/HId5wULUVkm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:18.000000Z\",\"updated_at\":\"2025-12-12T11:40:18.000000Z\"}}', NULL, '2025-12-12 11:40:18', '2025-12-12 11:40:18'),
(312, 'default', 'User has been created', 'App\\Models\\User', 'created', '49', NULL, NULL, '{\"attributes\":{\"id\":49,\"branch_id\":49,\"name\":\"Branch 0049-7-2\",\"email\":\"manager0049@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:18.000000Z\",\"password\":\"$2y$12$GkJBR7GbGajpYN9bFTFBruRgBrw2Fka2tPPIf6PtaLr.AZK8znphe\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:18.000000Z\",\"updated_at\":\"2025-12-12T11:40:18.000000Z\"}}', NULL, '2025-12-12 11:40:18', '2025-12-12 11:40:18'),
(313, 'default', 'User has been created', 'App\\Models\\User', 'created', '50', NULL, NULL, '{\"attributes\":{\"id\":50,\"branch_id\":50,\"name\":\"Branch 0050-5-2\",\"email\":\"manager0050@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:19.000000Z\",\"password\":\"$2y$12$TYWYhJ5ptklWKvtwITvsPuuE2lRDYWNrEbDnlXAvIq2GRN6bQ1mmy\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:19.000000Z\",\"updated_at\":\"2025-12-12T11:40:19.000000Z\"}}', NULL, '2025-12-12 11:40:19', '2025-12-12 11:40:19'),
(314, 'default', 'User has been created', 'App\\Models\\User', 'created', '51', NULL, NULL, '{\"attributes\":{\"id\":51,\"branch_id\":51,\"name\":\"Branch 0051-8-3\",\"email\":\"manager0051@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:19.000000Z\",\"password\":\"$2y$12$EVKblRbVPZiqYLd\\/qNhC7eWA2IWL1Yhyr3kn4EmAtjsMTI9VXgtam\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:19.000000Z\",\"updated_at\":\"2025-12-12T11:40:19.000000Z\"}}', NULL, '2025-12-12 11:40:19', '2025-12-12 11:40:19'),
(315, 'default', 'User has been created', 'App\\Models\\User', 'created', '52', NULL, NULL, '{\"attributes\":{\"id\":52,\"branch_id\":52,\"name\":\"Branch 0052-2-1\",\"email\":\"manager0052@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:19.000000Z\",\"password\":\"$2y$12$xC3YdvMAo4Zn6HekY3x72uL4CnOWNo58U8qg6InqQyELjsw.Oz2Jy\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:19.000000Z\",\"updated_at\":\"2025-12-12T11:40:19.000000Z\"}}', NULL, '2025-12-12 11:40:19', '2025-12-12 11:40:19'),
(316, 'default', 'User has been created', 'App\\Models\\User', 'created', '53', NULL, NULL, '{\"attributes\":{\"id\":53,\"branch_id\":53,\"name\":\"Branch 0053-1-1\",\"email\":\"manager0053@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:19.000000Z\",\"password\":\"$2y$12$Seu0Kla\\/L9CxV7als5j\\/geXirE4aGkDzRE5wIGFxzC0S2\\/gB.vGDy\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:19.000000Z\",\"updated_at\":\"2025-12-12T11:40:19.000000Z\"}}', NULL, '2025-12-12 11:40:19', '2025-12-12 11:40:19'),
(317, 'default', 'User has been created', 'App\\Models\\User', 'created', '54', NULL, NULL, '{\"attributes\":{\"id\":54,\"branch_id\":54,\"name\":\"Branch 0054-9-4\",\"email\":\"manager0054@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:20.000000Z\",\"password\":\"$2y$12$bHICuFrg3oj5HuZ8tBRA3u23EnxN3ovxN0IXwtQqpDIA3o8rPMoZK\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:20.000000Z\",\"updated_at\":\"2025-12-12T11:40:20.000000Z\"}}', NULL, '2025-12-12 11:40:20', '2025-12-12 11:40:20'),
(318, 'default', 'User has been created', 'App\\Models\\User', 'created', '55', NULL, NULL, '{\"attributes\":{\"id\":55,\"branch_id\":55,\"name\":\"Branch 0055-8-3\",\"email\":\"manager0055@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:20.000000Z\",\"password\":\"$2y$12$CnRs5XCmfDuiBvMjPAKKBOOhj8421taS5J4dhdFJPamvMyRERj3Kq\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:20.000000Z\",\"updated_at\":\"2025-12-12T11:40:20.000000Z\"}}', NULL, '2025-12-12 11:40:20', '2025-12-12 11:40:20'),
(319, 'default', 'User has been created', 'App\\Models\\User', 'created', '56', NULL, NULL, '{\"attributes\":{\"id\":56,\"branch_id\":56,\"name\":\"Branch 0056-5-2\",\"email\":\"manager0056@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:20.000000Z\",\"password\":\"$2y$12$azo5Q\\/6biTAzpF5jrkBYI.vD7m78R04QSFI2lNn50UDrmW7VPnz76\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:20.000000Z\",\"updated_at\":\"2025-12-12T11:40:20.000000Z\"}}', NULL, '2025-12-12 11:40:20', '2025-12-12 11:40:20'),
(320, 'default', 'User has been created', 'App\\Models\\User', 'created', '57', NULL, NULL, '{\"attributes\":{\"id\":57,\"branch_id\":57,\"name\":\"Branch 0057-9-4\",\"email\":\"manager0057@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:21.000000Z\",\"password\":\"$2y$12$vTmkgcUUetKjRVsIl\\/vIqed08V5iHBfxuxpfUPBgXBccI.15fjwme\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:21.000000Z\",\"updated_at\":\"2025-12-12T11:40:21.000000Z\"}}', NULL, '2025-12-12 11:40:21', '2025-12-12 11:40:21'),
(321, 'default', 'User has been created', 'App\\Models\\User', 'created', '58', NULL, NULL, '{\"attributes\":{\"id\":58,\"branch_id\":58,\"name\":\"Branch 0058-9-4\",\"email\":\"manager0058@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:21.000000Z\",\"password\":\"$2y$12$peDJKfScz3umPDmcTzasvOlk6XVbFoFho7T0pz7k4zX1CVgReBHxa\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:21.000000Z\",\"updated_at\":\"2025-12-12T11:40:21.000000Z\"}}', NULL, '2025-12-12 11:40:21', '2025-12-12 11:40:21'),
(322, 'default', 'User has been created', 'App\\Models\\User', 'created', '59', NULL, NULL, '{\"attributes\":{\"id\":59,\"branch_id\":59,\"name\":\"Branch 0059-10-4\",\"email\":\"manager0059@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:21.000000Z\",\"password\":\"$2y$12$b.iOs3UJpSnBkuc5HCWD0evyBBd8XYCkxXR0\\/yUernFUR81\\/EQFki\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:21.000000Z\",\"updated_at\":\"2025-12-12T11:40:21.000000Z\"}}', NULL, '2025-12-12 11:40:21', '2025-12-12 11:40:21'),
(323, 'default', 'User has been created', 'App\\Models\\User', 'created', '60', NULL, NULL, '{\"attributes\":{\"id\":60,\"branch_id\":60,\"name\":\"Branch 0060-5-2\",\"email\":\"manager0060@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:21.000000Z\",\"password\":\"$2y$12$cEzDXkHu0p53PZCQJ0dlyusL8bY9d4perow4lyDTEoQX37PKbqbXm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:21.000000Z\",\"updated_at\":\"2025-12-12T11:40:21.000000Z\"}}', NULL, '2025-12-12 11:40:21', '2025-12-12 11:40:21'),
(324, 'default', 'User has been created', 'App\\Models\\User', 'created', '61', NULL, NULL, '{\"attributes\":{\"id\":61,\"branch_id\":61,\"name\":\"Branch 0061-1-1\",\"email\":\"manager0061@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:22.000000Z\",\"password\":\"$2y$12$doQcU5knxc4mhJ7OM97FiuQAifn0CiEhZefOFgpMsYVAx0iK5RJq2\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:22.000000Z\",\"updated_at\":\"2025-12-12T11:40:22.000000Z\"}}', NULL, '2025-12-12 11:40:22', '2025-12-12 11:40:22'),
(325, 'default', 'User has been created', 'App\\Models\\User', 'created', '62', NULL, NULL, '{\"attributes\":{\"id\":62,\"branch_id\":62,\"name\":\"Branch 0062-3-1\",\"email\":\"manager0062@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:22.000000Z\",\"password\":\"$2y$12$ewe3GgB4lICAq3d6xd4gO.Le\\/HlrEBZrD11E2IyTLj3\\/RNM9XlaWm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:22.000000Z\",\"updated_at\":\"2025-12-12T11:40:22.000000Z\"}}', NULL, '2025-12-12 11:40:22', '2025-12-12 11:40:22'),
(326, 'default', 'User has been created', 'App\\Models\\User', 'created', '63', NULL, NULL, '{\"attributes\":{\"id\":63,\"branch_id\":63,\"name\":\"Branch 0063-2-1\",\"email\":\"manager0063@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:22.000000Z\",\"password\":\"$2y$12$Vk9Kg7ktciihr3qrk9KLoeROz76IcJkfyPL6CeMv5.1TwqzvQsIqa\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:22.000000Z\",\"updated_at\":\"2025-12-12T11:40:22.000000Z\"}}', NULL, '2025-12-12 11:40:22', '2025-12-12 11:40:22'),
(327, 'default', 'User has been created', 'App\\Models\\User', 'created', '64', NULL, NULL, '{\"attributes\":{\"id\":64,\"branch_id\":64,\"name\":\"Branch 0064-8-3\",\"email\":\"manager0064@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:22.000000Z\",\"password\":\"$2y$12$omTK7tsJyrOmTK4DvKC8.OylpACr68vtDhfzDTg8zFxg\\/8w\\/.I0vq\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:22.000000Z\",\"updated_at\":\"2025-12-12T11:40:22.000000Z\"}}', NULL, '2025-12-12 11:40:22', '2025-12-12 11:40:22'),
(328, 'default', 'User has been created', 'App\\Models\\User', 'created', '65', NULL, NULL, '{\"attributes\":{\"id\":65,\"branch_id\":65,\"name\":\"Branch 0065-8-3\",\"email\":\"manager0065@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:23.000000Z\",\"password\":\"$2y$12$ehKPEaa85e73IHE1UboNie.7onbXVMroB5.6fSebM.l5Qyu\\/PEetm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:23.000000Z\",\"updated_at\":\"2025-12-12T11:40:23.000000Z\"}}', NULL, '2025-12-12 11:40:23', '2025-12-12 11:40:23'),
(329, 'default', 'User has been created', 'App\\Models\\User', 'created', '66', NULL, NULL, '{\"attributes\":{\"id\":66,\"branch_id\":66,\"name\":\"Branch 0066-8-3\",\"email\":\"manager0066@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:23.000000Z\",\"password\":\"$2y$12$AHRgmSQ2Ew3wB5pcLtTgLuPdxbtJpE.aIIoMCcIYRSt29l7Lv.ORO\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:23.000000Z\",\"updated_at\":\"2025-12-12T11:40:23.000000Z\"}}', NULL, '2025-12-12 11:40:23', '2025-12-12 11:40:23'),
(330, 'default', 'User has been created', 'App\\Models\\User', 'created', '67', NULL, NULL, '{\"attributes\":{\"id\":67,\"branch_id\":67,\"name\":\"Branch 0067-1-1\",\"email\":\"manager0067@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:23.000000Z\",\"password\":\"$2y$12$D5iEcai75ZIoBOaSBuqyPuiIww.VVmGaBjWrzAOhcCZvOpyQRjR2K\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:23.000000Z\",\"updated_at\":\"2025-12-12T11:40:23.000000Z\"}}', NULL, '2025-12-12 11:40:23', '2025-12-12 11:40:23'),
(331, 'default', 'User has been created', 'App\\Models\\User', 'created', '68', NULL, NULL, '{\"attributes\":{\"id\":68,\"branch_id\":68,\"name\":\"Branch 0068-7-2\",\"email\":\"manager0068@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:24.000000Z\",\"password\":\"$2y$12$HIk3mY7Cm84p.wxF8YznhObRznlRL8Ggwa4kp4HSOQMGZankPy33e\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:24.000000Z\",\"updated_at\":\"2025-12-12T11:40:24.000000Z\"}}', NULL, '2025-12-12 11:40:24', '2025-12-12 11:40:24'),
(332, 'default', 'User has been created', 'App\\Models\\User', 'created', '69', NULL, NULL, '{\"attributes\":{\"id\":69,\"branch_id\":69,\"name\":\"Branch 0069-2-1\",\"email\":\"manager0069@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:24.000000Z\",\"password\":\"$2y$12$ReY588veN4Jx6cpnQDV05.wRdL.\\/hmn8WhanMoY1\\/O0h0thNRzD6W\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:24.000000Z\",\"updated_at\":\"2025-12-12T11:40:24.000000Z\"}}', NULL, '2025-12-12 11:40:24', '2025-12-12 11:40:24'),
(333, 'default', 'User has been created', 'App\\Models\\User', 'created', '70', NULL, NULL, '{\"attributes\":{\"id\":70,\"branch_id\":70,\"name\":\"Branch 0070-1-1\",\"email\":\"manager0070@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:24.000000Z\",\"password\":\"$2y$12$HqkkLOueEHN.f6doyD07neDQDkFUDGSUHdva4xgTyuL.mfu8ttd2i\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:24.000000Z\",\"updated_at\":\"2025-12-12T11:40:24.000000Z\"}}', NULL, '2025-12-12 11:40:24', '2025-12-12 11:40:24'),
(334, 'default', 'User has been created', 'App\\Models\\User', 'created', '71', NULL, NULL, '{\"attributes\":{\"id\":71,\"branch_id\":71,\"name\":\"Branch 0071-7-2\",\"email\":\"manager0071@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:24.000000Z\",\"password\":\"$2y$12$ME92VLJoxOmV6sRb0ys8Ue6J1bwgU9ruKtACSyjDLxkHhn2URtsra\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:24.000000Z\",\"updated_at\":\"2025-12-12T11:40:24.000000Z\"}}', NULL, '2025-12-12 11:40:24', '2025-12-12 11:40:24'),
(335, 'default', 'User has been created', 'App\\Models\\User', 'created', '72', NULL, NULL, '{\"attributes\":{\"id\":72,\"branch_id\":72,\"name\":\"Branch 0072-7-2\",\"email\":\"manager0072@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:25.000000Z\",\"password\":\"$2y$12$LZSPnGizQmXjJLCc\\/vxBMeiAN9jQv11b0XKGanKS3HM1rGNmA5oEG\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:25.000000Z\",\"updated_at\":\"2025-12-12T11:40:25.000000Z\"}}', NULL, '2025-12-12 11:40:25', '2025-12-12 11:40:25'),
(336, 'default', 'User has been created', 'App\\Models\\User', 'created', '73', NULL, NULL, '{\"attributes\":{\"id\":73,\"branch_id\":73,\"name\":\"Branch 0073-10-4\",\"email\":\"manager0073@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:25.000000Z\",\"password\":\"$2y$12$1WEWZKYbDxRUuSeC8S4WjuRlOk1RwDPOdby1tcICqq7nlSEVuujva\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:25.000000Z\",\"updated_at\":\"2025-12-12T11:40:25.000000Z\"}}', NULL, '2025-12-12 11:40:25', '2025-12-12 11:40:25'),
(337, 'default', 'User has been created', 'App\\Models\\User', 'created', '74', NULL, NULL, '{\"attributes\":{\"id\":74,\"branch_id\":74,\"name\":\"Branch 0074-7-2\",\"email\":\"manager0074@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:25.000000Z\",\"password\":\"$2y$12$1yDFZwlzKHP9JJyrQODly.kOkUSB6pnIH0gTADSo8b8YawDlqGErm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:25.000000Z\",\"updated_at\":\"2025-12-12T11:40:25.000000Z\"}}', NULL, '2025-12-12 11:40:25', '2025-12-12 11:40:25'),
(338, 'default', 'User has been created', 'App\\Models\\User', 'created', '75', NULL, NULL, '{\"attributes\":{\"id\":75,\"branch_id\":75,\"name\":\"Branch 0075-8-3\",\"email\":\"manager0075@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:25.000000Z\",\"password\":\"$2y$12$WC19gYO.sUoSPHJUmM6V9OneWnItH4GWWdgbgryrRJ1QvLnroy7iK\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:25.000000Z\",\"updated_at\":\"2025-12-12T11:40:25.000000Z\"}}', NULL, '2025-12-12 11:40:25', '2025-12-12 11:40:25'),
(339, 'default', 'User has been created', 'App\\Models\\User', 'created', '76', NULL, NULL, '{\"attributes\":{\"id\":76,\"branch_id\":76,\"name\":\"Branch 0076-3-1\",\"email\":\"manager0076@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:26.000000Z\",\"password\":\"$2y$12$EXuNpuJe3wjK2In28BgNqu9Askka8.EF81ZhzqpotSPKeLpeRmuTm\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:26.000000Z\",\"updated_at\":\"2025-12-12T11:40:26.000000Z\"}}', NULL, '2025-12-12 11:40:26', '2025-12-12 11:40:26'),
(340, 'default', 'User has been created', 'App\\Models\\User', 'created', '77', NULL, NULL, '{\"attributes\":{\"id\":77,\"branch_id\":77,\"name\":\"Branch 0077-1-1\",\"email\":\"manager0077@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:26.000000Z\",\"password\":\"$2y$12$CN\\/Dy.lnLpuEv3k\\/JqGM8OjmIQY.tTDOFY0SNUw\\/.kdJmvgotIGOi\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:26.000000Z\",\"updated_at\":\"2025-12-12T11:40:26.000000Z\"}}', NULL, '2025-12-12 11:40:26', '2025-12-12 11:40:26'),
(341, 'default', 'User has been created', 'App\\Models\\User', 'created', '78', NULL, NULL, '{\"attributes\":{\"id\":78,\"branch_id\":78,\"name\":\"Branch 0078-10-4\",\"email\":\"manager0078@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:26.000000Z\",\"password\":\"$2y$12$zNKQEWOnxSDoXtIvi2D5CefYbch18ds4fOc.u3MV5j2\\/ZZKqJCrIe\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:26.000000Z\",\"updated_at\":\"2025-12-12T11:40:26.000000Z\"}}', NULL, '2025-12-12 11:40:26', '2025-12-12 11:40:26'),
(342, 'default', 'User has been created', 'App\\Models\\User', 'created', '79', NULL, NULL, '{\"attributes\":{\"id\":79,\"branch_id\":79,\"name\":\"Branch 0079-9-4\",\"email\":\"manager0079@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:27.000000Z\",\"password\":\"$2y$12$2MISQRntNL4aFQaZO.wX\\/OYlPkKdBVk1AtZEAWDbi\\/wgtO2FRdtiC\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:27.000000Z\",\"updated_at\":\"2025-12-12T11:40:27.000000Z\"}}', NULL, '2025-12-12 11:40:27', '2025-12-12 11:40:27'),
(343, 'default', 'User has been created', 'App\\Models\\User', 'created', '80', NULL, NULL, '{\"attributes\":{\"id\":80,\"branch_id\":80,\"name\":\"Branch 0080-9-4\",\"email\":\"manager0080@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:27.000000Z\",\"password\":\"$2y$12$zt2pMOfbe8u6Zfty73rm\\/eTf07mDr2fIE106dWlsZzNjoF\\/ttL81S\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:27.000000Z\",\"updated_at\":\"2025-12-12T11:40:27.000000Z\"}}', NULL, '2025-12-12 11:40:27', '2025-12-12 11:40:27'),
(344, 'default', 'User has been created', 'App\\Models\\User', 'created', '81', NULL, NULL, '{\"attributes\":{\"id\":81,\"branch_id\":81,\"name\":\"Branch 0081-9-4\",\"email\":\"manager0081@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:27.000000Z\",\"password\":\"$2y$12$oH3P3lZOT.2EY.1vqqsxZulVmuDSHO\\/BY9ErwCWUFl.u4.z82Xnlu\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:27.000000Z\",\"updated_at\":\"2025-12-12T11:40:27.000000Z\"}}', NULL, '2025-12-12 11:40:27', '2025-12-12 11:40:27'),
(345, 'default', 'User has been created', 'App\\Models\\User', 'created', '82', NULL, NULL, '{\"attributes\":{\"id\":82,\"branch_id\":82,\"name\":\"Branch 0082-8-3\",\"email\":\"manager0082@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:27.000000Z\",\"password\":\"$2y$12$zGF5B.OSihNRPleFAHZH\\/edNLF1DkTgWbaFGmR6nf8nG.IIs2wF\\/q\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:27.000000Z\",\"updated_at\":\"2025-12-12T11:40:27.000000Z\"}}', NULL, '2025-12-12 11:40:27', '2025-12-12 11:40:27'),
(346, 'default', 'User has been created', 'App\\Models\\User', 'created', '83', NULL, NULL, '{\"attributes\":{\"id\":83,\"branch_id\":83,\"name\":\"Branch 0083-5-2\",\"email\":\"manager0083@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:28.000000Z\",\"password\":\"$2y$12$HJq3GTD5ILbT61zVbfXgC.BZzXtC7.keO5j3T\\/RpINgrqwXdbV2ea\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:28.000000Z\",\"updated_at\":\"2025-12-12T11:40:28.000000Z\"}}', NULL, '2025-12-12 11:40:28', '2025-12-12 11:40:28'),
(347, 'default', 'User has been created', 'App\\Models\\User', 'created', '84', NULL, NULL, '{\"attributes\":{\"id\":84,\"branch_id\":84,\"name\":\"Branch 0084-3-1\",\"email\":\"manager0084@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:28.000000Z\",\"password\":\"$2y$12$PWPJuNxKUTQam7yD5PRiPOWZ3Vd9vxY\\/\\/pFGYQWhrWWbIucbuCb4S\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:28.000000Z\",\"updated_at\":\"2025-12-12T11:40:28.000000Z\"}}', NULL, '2025-12-12 11:40:28', '2025-12-12 11:40:28'),
(348, 'default', 'User has been created', 'App\\Models\\User', 'created', '85', NULL, NULL, '{\"attributes\":{\"id\":85,\"branch_id\":85,\"name\":\"Branch 0085-1-1\",\"email\":\"manager0085@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:28.000000Z\",\"password\":\"$2y$12$NyS5QwGByShDILcCgro2F.E76DWuCFcQLtZbTUDjqlyf3UOhXJU0O\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:28.000000Z\",\"updated_at\":\"2025-12-12T11:40:28.000000Z\"}}', NULL, '2025-12-12 11:40:28', '2025-12-12 11:40:28'),
(349, 'default', 'User has been created', 'App\\Models\\User', 'created', '86', NULL, NULL, '{\"attributes\":{\"id\":86,\"branch_id\":86,\"name\":\"Branch 0086-4-2\",\"email\":\"manager0086@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:28.000000Z\",\"password\":\"$2y$12$QwF\\/GRBWGbLLgBevnAZpZODOmXUO1oxm6zKPA.lb6NnkoxPdpEsyW\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:28.000000Z\",\"updated_at\":\"2025-12-12T11:40:28.000000Z\"}}', NULL, '2025-12-12 11:40:28', '2025-12-12 11:40:28');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(350, 'default', 'User has been created', 'App\\Models\\User', 'created', '87', NULL, NULL, '{\"attributes\":{\"id\":87,\"branch_id\":87,\"name\":\"Branch 0087-9-4\",\"email\":\"manager0087@bankajk.com\",\"email_verified_at\":\"2025-12-12T11:40:29.000000Z\",\"password\":\"$2y$12$R1Qi373Vh5NuUeMSO8jM2OY6Mw2MeuKUrSjJ4ugm1u.QLJYwH\\/m76\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:29.000000Z\",\"updated_at\":\"2025-12-12T11:40:29.000000Z\"}}', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(351, 'default', 'User has been created', 'App\\Models\\User', 'created', '88', NULL, NULL, '{\"attributes\":{\"id\":88,\"branch_id\":null,\"name\":\"Ali Raza Marchal\",\"email\":\"superadmin@example.com\",\"email_verified_at\":\"2025-12-12T11:40:29.000000Z\",\"password\":\"$2y$12$a33h7u8P6tgrJaxkw5zer.4kelspC8wY1uorT1CgTTPTJrRwsErnK\",\"two_factor_secret\":null,\"two_factor_recovery_codes\":null,\"two_factor_confirmed_at\":null,\"remember_token\":null,\"current_team_id\":null,\"profile_photo_path\":null,\"is_super_admin\":\"No\",\"is_active\":\"Yes\",\"deleted_at\":null,\"created_at\":\"2025-12-12T11:40:29.000000Z\",\"updated_at\":\"2025-12-12T11:40:29.000000Z\"}}', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(352, 'default', 'User has been updated', 'App\\Models\\User', 'updated', '1', 'App\\Models\\User', '88', '{\"attributes\":{\"password\":\"$2y$12$nI9HHr9.Xnvy7g1hM1ALf.Us7BzW72sGvB3ifLKG\\/JQTpeIdWUcsu\",\"updated_at\":\"2025-12-26T11:50:41.000000Z\"},\"old\":{\"password\":\"$2y$12$OubvB8I\\/k0yR8Z6pNGQcL.EHUnAgvkrWKvEVSmkZo2Bm7oZJw6m3S\",\"updated_at\":\"2025-12-12T11:40:05.000000Z\"}}', NULL, '2025-12-26 11:50:41', '2025-12-26 11:50:41'),
(353, 'default', 'User has been updated', 'App\\Models\\User', 'updated', '29', 'App\\Models\\User', '88', '{\"attributes\":{\"password\":\"$2y$12$CPSPWAsH5u22Bw34TD5cwOUADFLSm6t\\/jbMPQsyVqkKnjl.3D7TZa\",\"updated_at\":\"2026-02-20T05:12:47.000000Z\"},\"old\":{\"password\":\"$2y$12$vXNmRdcsrKoiI3fZ8idib.EuQk2j5kL078bGi5T7BGEG\\/lmoNJ0EG\",\"updated_at\":\"2025-12-12T11:40:13.000000Z\"}}', NULL, '2026-02-20 05:12:47', '2026-02-20 05:12:47'),
(354, 'default', 'Customer (CIF) has been created', 'App\\Models\\Customer', 'created', '01a13aff-4c2a-73b1-9926-4dc2e59ff46f', 'App\\Models\\User', '88', '{\"attributes\":{\"id\":\"01a13aff-4c2a-73b1-9926-4dc2e59ff46f\",\"cif_number\":\"0011-20261014-0001\",\"branch_id\":11,\"cif_date\":\"2026-10-13T19:00:00.000000Z\",\"customer_type\":\"individual\",\"aof_form_type\":\"individual_joint_sole\",\"customer_category_id\":\"01a13aef-1865-7302-a4ec-e31d1fc8ff9e\",\"customer_category_other\":null,\"category_code_description\":null,\"economic_sector_id\":null,\"economic_sector_code\":null,\"is_zakat_exempt\":false,\"zakat_exemption_reason_id\":null,\"zakat_exemption_other\":null,\"cz50_submitted_on\":null,\"is_pep\":false,\"pep_form_attached\":false,\"status\":\"draft\",\"created_by\":88,\"updated_by\":88,\"verified_by\":null,\"approved_by\":null,\"approved_at\":null,\"created_at\":\"2026-10-14T15:19:32.000000Z\",\"updated_at\":\"2026-10-14T15:19:32.000000Z\",\"deleted_at\":null}}', NULL, '2026-10-14 15:19:32', '2026-10-14 15:19:32'),
(355, 'default', 'Account opening request has been created', 'App\\Models\\AccountOpeningRequest', 'created', '01a13aff-4c4e-733d-b067-1f4e013c8a69', 'App\\Models\\User', '88', '{\"attributes\":{\"id\":\"01a13aff-4c4e-733d-b067-1f4e013c8a69\",\"request_number\":\"0011-20261014-0001\",\"aof_form_type\":\"individual_joint_sole\",\"form_edition\":\"new\",\"aof_version\":\"BAJK-AOF-Individual-19-Jan-2026\",\"branch_id\":11,\"customer_id\":\"01a13aff-4c2a-73b1-9926-4dc2e59ff46f\",\"account_id\":null,\"request_date\":\"2026-10-13T19:00:00.000000Z\",\"terms_and_conditions_accepted\":false,\"indemnity_undertaking_accepted\":false,\"aof_copy_received_by_customer\":false,\"shariah_compliant_investment_authorized\":false,\"vernacular_form_attached\":false,\"qa22_form_attached\":false,\"sales_staff_name_1\":null,\"sales_staff_employee_no_1\":null,\"sales_staff_name_2\":null,\"sales_staff_employee_no_2\":null,\"authorized_by_name\":null,\"authorized_by_employee_no\":null,\"authorized_on\":null,\"checker_name\":null,\"checker_employee_no\":null,\"checked_on\":null,\"status\":\"draft\",\"submitted_by\":null,\"submitted_at\":null,\"approved_by\":null,\"approved_at\":null,\"rejected_by\":null,\"rejected_at\":null,\"locked_at\":null,\"unlocked_by\":null,\"unlocked_at\":null,\"unlock_reason\":null,\"rejection_reason\":null,\"created_by\":88,\"updated_by\":88,\"created_at\":\"2026-10-14T15:19:32.000000Z\",\"updated_at\":\"2026-10-14T15:19:32.000000Z\",\"deleted_at\":null}}', NULL, '2026-10-14 15:19:32', '2026-10-14 15:19:32'),
(356, 'default', 'Account opening request has been created', 'App\\Models\\AccountOpeningRequest', 'created', '01a13b00-a51e-702c-a2ae-f00257a771a6', 'App\\Models\\User', '88', '{\"attributes\":{\"id\":\"01a13b00-a51e-702c-a2ae-f00257a771a6\",\"request_number\":\"0011-20261014-0002\",\"aof_form_type\":\"individual_joint_sole\",\"form_edition\":\"old\",\"aof_version\":\"BAJK-AOF-Old-Paper-Form\",\"branch_id\":11,\"customer_id\":null,\"account_id\":null,\"request_date\":\"2026-10-13T19:00:00.000000Z\",\"terms_and_conditions_accepted\":false,\"indemnity_undertaking_accepted\":false,\"aof_copy_received_by_customer\":false,\"shariah_compliant_investment_authorized\":false,\"vernacular_form_attached\":false,\"qa22_form_attached\":false,\"sales_staff_name_1\":null,\"sales_staff_employee_no_1\":null,\"sales_staff_name_2\":null,\"sales_staff_employee_no_2\":null,\"authorized_by_name\":null,\"authorized_by_employee_no\":null,\"authorized_on\":null,\"checker_name\":null,\"checker_employee_no\":null,\"checked_on\":null,\"status\":\"draft\",\"submitted_by\":null,\"submitted_at\":null,\"approved_by\":null,\"approved_at\":null,\"rejected_by\":null,\"rejected_at\":null,\"locked_at\":null,\"unlocked_by\":null,\"unlocked_at\":null,\"unlock_reason\":null,\"rejection_reason\":null,\"created_by\":88,\"updated_by\":88,\"created_at\":\"2026-10-14T15:21:00.000000Z\",\"updated_at\":\"2026-10-14T15:21:00.000000Z\",\"deleted_at\":null}}', NULL, '2026-10-14 15:21:00', '2026-10-14 15:21:00');

-- --------------------------------------------------------

--
-- Table structure for table `aksics`
--

CREATE TABLE `aksics` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `father_name` varchar(255) NOT NULL,
  `cnic` varchar(255) NOT NULL,
  `application_no` varchar(255) NOT NULL,
  `account_no` varchar(50) DEFAULT NULL COMMENT 'Change #11: bank account number the loan is booked against',
  `cnic_issue_date` date DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `business_type` varchar(255) DEFAULT NULL,
  `is_startup_business` tinyint(1) NOT NULL DEFAULT 0,
  `quota` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `business_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `business_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `business_sub_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tier` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `district_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tehsil_id` bigint(20) UNSIGNED DEFAULT NULL,
  `aksic_rule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `applicant_choosed_branch_id` varchar(255) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `challan_branch_id` varchar(255) DEFAULT NULL,
  `applicant_choosed_branch_code` varchar(255) DEFAULT NULL,
  `challan_branch_code` varchar(255) DEFAULT NULL,
  `challan_fee` decimal(10,2) DEFAULT NULL,
  `challan_image` varchar(255) DEFAULT NULL,
  `cnic_front` varchar(255) DEFAULT NULL,
  `cnic_back` varchar(255) DEFAULT NULL,
  `challan_image_url` varchar(255) DEFAULT NULL,
  `cnic_front_url` varchar(255) DEFAULT NULL,
  `cnic_back_url` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved') NOT NULL DEFAULT 'Pending',
  `bank_status` varchar(255) DEFAULT NULL,
  `fee_branch_code` varchar(255) DEFAULT NULL,
  `district_name` varchar(255) DEFAULT NULL,
  `tehsil_name` varchar(255) DEFAULT NULL,
  `principal_amount` decimal(15,2) DEFAULT NULL,
  `tenure` int(10) UNSIGNED DEFAULT NULL,
  `disbursement_date` date DEFAULT NULL,
  `site_visit_completed` tinyint(1) NOT NULL DEFAULT 0,
  `site_visit_date` date DEFAULT NULL,
  `consent_entry` enum('Yes','No') DEFAULT NULL,
  `consent_date` date DEFAULT NULL,
  `liquid_security` text DEFAULT NULL,
  `personal_guarantees` text DEFAULT NULL,
  `mortgage` text DEFAULT NULL COMMENT 'Change #9: mortgage / collateral details',
  `kibor_rate` decimal(5,2) DEFAULT NULL,
  `spread_rate` decimal(5,2) DEFAULT NULL,
  `total_rate` decimal(5,2) DEFAULT NULL,
  `total_interest` decimal(20,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aksics`
--

INSERT INTO `aksics` (`id`, `name`, `father_name`, `cnic`, `application_no`, `account_no`, `cnic_issue_date`, `dob`, `phone`, `business_name`, `business_type`, `is_startup_business`, `quota`, `gender`, `business_address`, `permanent_address`, `business_category_id`, `business_sub_category_id`, `tier`, `amount`, `district_id`, `tehsil_id`, `aksic_rule_id`, `applicant_choosed_branch_id`, `branch_id`, `challan_branch_id`, `applicant_choosed_branch_code`, `challan_branch_code`, `challan_fee`, `challan_image`, `cnic_front`, `cnic_back`, `challan_image_url`, `cnic_front_url`, `cnic_back_url`, `status`, `bank_status`, `fee_branch_code`, `district_name`, `tehsil_name`, `principal_amount`, `tenure`, `disbursement_date`, `site_visit_completed`, `site_visit_date`, `consent_entry`, `consent_date`, `liquid_security`, `personal_guarantees`, `mortgage`, `kibor_rate`, `spread_rate`, `total_rate`, `total_interest`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
('019e5de4-4a45-7359-af5e-a90a51b55a03', 'ALI RAZA MARCHAL', 'RAFIQUE AHMAD MARCHAL', '82203-9442865-3', '01', NULL, NULL, NULL, '0300-8169924', 'ABC', 'Existing', 0, 'Male', 'Male', NULL, NULL, 1, 2, NULL, NULL, 1, NULL, 1, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, NULL, 500000.00, 60, '2026-05-25', 1, NULL, 'Yes', '2026-05-25', NULL, NULL, NULL, 12.00, 2.04, 14.04, 173991.864764, '2026-05-25 06:48:22', '2026-05-25 11:23:57', 88, 88, NULL),
('01a0c827-a06f-71af-a2ab-d29a98235ffb', 'Sample Applicant', 'Sample Father', '12345-1234567-2', 'AKSIC-SAMPLE-002', NULL, NULL, NULL, '03001234567', 'Sample Business', 'Existing', 0, 'Male', 'Male', NULL, NULL, 16, 19, NULL, NULL, 4, NULL, 4, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, NULL, 1000000.00, 60, '2026-09-22', 0, NULL, 'No', NULL, NULL, NULL, NULL, 12.00, 2.04, 14.04, 348624.826409, '2026-09-22 08:07:15', '2026-09-22 08:08:11', 88, 88, '2026-09-22 08:08:11'),
('01a0c82a-4325-709b-8d0b-3368c2a50517', 'Sample Applicant', 'Sample Father', '12345-1234567-3', 'AKSIC-SAMPLE-01', NULL, NULL, NULL, '03001234567', 'Sample Business', 'Existing', 0, 'Male', 'Male', NULL, NULL, 16, 17, NULL, NULL, 4, NULL, 4, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, NULL, 1000000.00, 60, '2026-09-22', 0, NULL, 'No', NULL, NULL, NULL, NULL, 12.00, 2.04, 14.04, 348624.826409, '2026-09-22 08:10:08', '2026-09-22 08:17:28', 88, 88, NULL),
('01a0c8ac-e71d-7307-8d93-6a52f6c20183', 'Sample Applicant', 'Sample Father', '12345-1234567-1', 'AKSIC-SAMPLE-001', NULL, NULL, NULL, '03001234567', 'Sample Business', 'Existing', 0, 'Male', 'Male', NULL, NULL, 16, 17, NULL, NULL, 4, NULL, 4, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, NULL, 1000000.00, 60, '2026-09-22', 0, NULL, 'No', NULL, NULL, NULL, NULL, 12.00, 2.04, 14.04, 348624.826409, '2026-09-22 10:32:49', '2026-09-22 10:33:27', 88, 88, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aksic_amortizations`
--

CREATE TABLE `aksic_amortizations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `aksic_id` char(36) NOT NULL,
  `installment_no` int(10) UNSIGNED NOT NULL,
  `period_start_date` date NOT NULL,
  `due_date` date NOT NULL,
  `days` int(10) UNSIGNED NOT NULL,
  `principal_amount_os` decimal(20,6) NOT NULL,
  `installment_per_month` decimal(20,6) NOT NULL,
  `product` decimal(20,6) NOT NULL,
  `interest_rate_per_month` decimal(20,6) NOT NULL,
  `total_interest` decimal(20,6) NOT NULL,
  `total_rate` decimal(8,6) NOT NULL,
  `total_installment` decimal(20,6) NOT NULL,
  `principal_balance_after_installment` decimal(20,6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aksic_applications`
--

CREATE TABLE `aksic_applications` (
  `id` char(36) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `fatherName` varchar(255) NOT NULL,
  `cnic` varchar(255) NOT NULL,
  `application_no` varchar(255) NOT NULL,
  `cnic_issue_date` date DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `businessName` varchar(255) DEFAULT NULL,
  `businessType` varchar(255) DEFAULT NULL,
  `quota` varchar(255) DEFAULT NULL,
  `businessAddress` text DEFAULT NULL,
  `permanentAddress` text DEFAULT NULL,
  `business_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `business_sub_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tier` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `district_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tehsil_id` bigint(20) UNSIGNED DEFAULT NULL,
  `applicant_choosed_branch_id` varchar(255) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `challan_branch_id` varchar(255) DEFAULT NULL,
  `applicant_choosed_branch_code` varchar(255) DEFAULT NULL,
  `challan_branch_code` varchar(255) DEFAULT NULL,
  `challan_fee` decimal(10,2) DEFAULT NULL,
  `challan_image` varchar(255) DEFAULT NULL,
  `cnic_front` varchar(255) DEFAULT NULL,
  `cnic_back` varchar(255) DEFAULT NULL,
  `challan_image_url` varchar(255) DEFAULT NULL,
  `cnic_front_url` varchar(255) DEFAULT NULL,
  `cnic_back_url` varchar(255) DEFAULT NULL,
  `fee_status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `status` enum('NotCompleted','Pending','Forwarded','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `bank_status` varchar(255) DEFAULT NULL,
  `fee_branch_code` varchar(255) DEFAULT NULL,
  `district_name` varchar(255) DEFAULT NULL,
  `tehsil_name` varchar(255) DEFAULT NULL,
  `api_call_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`api_call_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aksic_application_education`
--

CREATE TABLE `aksic_application_education` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `aksic_application_id` char(36) DEFAULT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `education_level` varchar(255) NOT NULL,
  `degree_title` varchar(255) DEFAULT NULL,
  `institute` varchar(255) DEFAULT NULL,
  `passing_year` year(4) DEFAULT NULL,
  `grade_or_percentage` varchar(255) DEFAULT NULL,
  `educations_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`educations_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aksic_application_status_logs`
--

CREATE TABLE `aksic_application_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `aksic_application_id` char(36) DEFAULT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `old_status` varchar(255) NOT NULL,
  `new_status` varchar(255) NOT NULL,
  `changed_by_type` varchar(255) DEFAULT NULL,
  `changed_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`status_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aksic_budgets`
--

CREATE TABLE `aksic_budgets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL COMMENT 'Sanction / letter number',
  `reference_date` date DEFAULT NULL,
  `total_amount` decimal(20,2) NOT NULL COMMENT 'Total markup budget in Rs',
  `existing_business_percentage` decimal(5,2) NOT NULL DEFAULT 25.00,
  `new_business_percentage` decimal(5,2) NOT NULL DEFAULT 75.00,
  `enforcement` enum('report','warn','block') NOT NULL DEFAULT 'block' COMMENT 'report = show only, warn = approve with warning, block = stop approval when a limit is exceeded',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aksic_budgets`
--

INSERT INTO `aksic_budgets` (`id`, `title`, `reference_no`, `reference_date`, `total_amount`, `existing_business_percentage`, `new_business_percentage`, `enforcement`, `is_active`, `notes`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
(1, 'PM Youth Loan Scheme -- Markup Allocation', 'AKSIC Markup Allocation / Budget sheet', NULL, 994225000.00, 25.00, 75.00, 'block', 1, 'Seeded from the AKSIC PMYL markup allocation sheet (Rs 994.225 million; district provision = total x population %).', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aksic_budget_allocations`
--

CREATE TABLE `aksic_budget_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `aksic_budget_id` bigint(20) UNSIGNED NOT NULL,
  `district_id` bigint(20) UNSIGNED NOT NULL,
  `aksic_rule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `allocated_amount` decimal(20,2) NOT NULL COMMENT 'Current markup allocation in Rs',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aksic_budget_allocations`
--

INSERT INTO `aksic_budget_allocations` (`id`, `aksic_budget_id`, `district_id`, `aksic_rule_id`, `allocated_amount`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 8, 8, 183435000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(2, 1, 1, 1, 161462000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(3, 1, 5, 6, 125272000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(4, 1, 9, 9, 99621000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(5, 1, 10, 10, 100317000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(6, 1, 4, 4, 101610000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(7, 1, 7, 7, 71684000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(8, 1, 3, 3, 59455000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(9, 1, 2, 2, 51501000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(10, 1, 6, 5, 39670000.00, '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aksic_budget_revisions`
--

CREATE TABLE `aksic_budget_revisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `aksic_budget_id` bigint(20) UNSIGNED NOT NULL,
  `district_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` enum('initial','enhancement','reduction','redistribution','total_change','settings_change','activation') NOT NULL,
  `previous_amount` decimal(20,2) DEFAULT NULL,
  `new_amount` decimal(20,2) DEFAULT NULL,
  `change_amount` decimal(20,2) DEFAULT NULL COMMENT 'new - previous',
  `reference_no` varchar(100) DEFAULT NULL,
  `reference_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aksic_budget_revisions`
--

INSERT INTO `aksic_budget_revisions` (`id`, `aksic_budget_id`, `district_id`, `action`, `previous_amount`, `new_amount`, `change_amount`, `reference_no`, `reference_date`, `reason`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 8, 'initial', 0.00, 183435000.00, 183435000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(2, 1, 1, 'initial', 0.00, 161462000.00, 161462000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(3, 1, 5, 'initial', 0.00, 125272000.00, 125272000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(4, 1, 9, 'initial', 0.00, 99621000.00, 99621000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(5, 1, 10, 'initial', 0.00, 100317000.00, 100317000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(6, 1, 4, 'initial', 0.00, 101610000.00, 101610000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(7, 1, 7, 'initial', 0.00, 71684000.00, 71684000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(8, 1, 3, 'initial', 0.00, 59455000.00, 59455000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(9, 1, 2, 'initial', 0.00, 51501000.00, 51501000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL),
(10, 1, 6, 'initial', 0.00, 39670000.00, 39670000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-14 15:01:49', '2026-10-14 15:01:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aksic_business_categories`
--

CREATE TABLE `aksic_business_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aksic_business_categories`
--

INSERT INTO `aksic_business_categories` (`id`, `parent_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 0, 'IT & Digital Sector', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(2, 1, 'Information Technology (by professionals)', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(3, 1, 'E-Commerce', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(4, 1, 'Digital Marketing', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(5, 1, 'Software Houses', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(6, 1, 'Call Centres', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(7, 1, 'TEVT Sector', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(8, 0, 'Handicrafts & Cottage Industries', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(9, 8, 'Hand knotted carpet weaving', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(10, 8, 'Walnut wood carving', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(11, 8, 'Kashmiri Shawls & Embroidery', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(12, 8, 'Papier Machie', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(13, 8, 'Chain-stitch, Numda & Gabba', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(14, 8, 'Kangi Making', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(15, 8, 'Phiren & Embroidered Dresses', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(16, 0, 'Agriculture & Livestock', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(17, 16, 'Agri-Businesses', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(18, 16, 'Livestock, Dairy & Poultry Farming', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(19, 16, 'Horticulture & Rose Farming', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(20, 16, 'Olive Plantation', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(21, 16, 'Off-season Vegetable Farming', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(22, 0, 'Tourism & Hospitality', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(23, 22, 'Traditional Food Courts', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(24, 22, 'Paying Guest Units', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(25, 22, 'Hotels & Restaurants', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(26, 0, 'SMEs & Local Industry', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(27, 26, 'Silk Rearing & Processing', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(28, 26, 'Gem Stone Cutting', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(29, 26, 'Stone Carving & Crushing', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(30, 26, 'Sports Goods Manufacturing', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(31, 26, 'Light Engineering Sector', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(32, 26, 'Auto Repair & Workshops', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(33, 0, 'Women Entrepreneurs & Misc.', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(34, 33, 'Boutiques & Tailoring', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(35, 33, 'Home Baking Units', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(36, 33, 'Pickle, Jam & Jelly Making', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(37, 33, 'Garments / Stitching Units', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(38, 33, 'Mineral Water Plants', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(39, 33, 'Soap & Detergent Production', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(40, 33, 'New Innovations', '2025-12-12 11:40:29', '2025-12-12 11:40:29');

-- --------------------------------------------------------

--
-- Table structure for table `aksic_claims`
--

CREATE TABLE `aksic_claims` (
  `id` char(36) NOT NULL,
  `claim_no` varchar(30) NOT NULL,
  `claim_date` date NOT NULL,
  `period_from` date NOT NULL,
  `period_to` date NOT NULL,
  `district_id` bigint(20) UNSIGNED DEFAULT NULL,
  `region_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `total_loans` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_principal_outstanding` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_markup` decimal(20,2) NOT NULL DEFAULT 0.00,
  `status` enum('Lodged','Settled','Rejected') NOT NULL DEFAULT 'Lodged',
  `status_date` date DEFAULT NULL COMMENT 'Date settled / rejected',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aksic_claim_items`
--

CREATE TABLE `aksic_claim_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `aksic_claim_id` char(36) NOT NULL,
  `aksic_id` char(36) NOT NULL,
  `district_id` bigint(20) UNSIGNED DEFAULT NULL,
  `region_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `installments_count` smallint(5) UNSIGNED NOT NULL,
  `principal_outstanding` decimal(20,2) NOT NULL COMMENT 'Opening principal of the first instalment in the period',
  `markup_amount` decimal(20,2) NOT NULL COMMENT 'Markup of instalments due within the claim period',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aksic_rules`
--

CREATE TABLE `aksic_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `district_id` bigint(20) UNSIGNED NOT NULL,
  `district_name` varchar(255) NOT NULL,
  `population_percentage` decimal(5,2) NOT NULL,
  `proposed_beneficiaries` int(10) UNSIGNED NOT NULL,
  `male_percentage` decimal(5,2) NOT NULL DEFAULT 48.00,
  `female_percentage` decimal(5,2) NOT NULL DEFAULT 48.00,
  `special_person_percentage` decimal(5,2) NOT NULL DEFAULT 2.00,
  `transgender_percentage` decimal(5,2) NOT NULL DEFAULT 2.00,
  `requires_site_visit` tinyint(1) NOT NULL DEFAULT 1,
  `requires_business_nature` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aksic_rules`
--

INSERT INTO `aksic_rules` (`id`, `district_id`, `district_name`, `population_percentage`, `proposed_beneficiaries`, `male_percentage`, `female_percentage`, `special_person_percentage`, `transgender_percentage`, `requires_site_visit`, `requires_business_nature`, `is_active`, `notes`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Muzaffarabad', 16.24, 456, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(2, 2, 'Neelum', 5.18, 145, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(3, 3, 'Jhelum Valley', 5.98, 168, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(4, 4, 'Bagh', 10.22, 287, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(5, 6, 'Haveli', 3.99, 112, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(6, 5, 'Poonch', 12.60, 353, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(7, 7, 'Sudhnoti', 7.21, 202, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(8, 8, 'Kotli', 18.45, 518, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:37', '2026-05-25 06:41:37'),
(9, 9, 'Mirpur', 10.02, 281, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:37', '2026-05-25 06:41:37'),
(10, 10, 'Bhimber', 10.09, 283, 48.00, 48.00, 2.00, 2.00, 1, 1, 1, 'AKSIC district-wise disbursement rule from 16-03-2026 letter.', NULL, NULL, NULL, '2026-05-25 06:41:37', '2026-05-25 06:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `boxes`
--

CREATE TABLE `boxes` (
  `id` char(36) NOT NULL,
  `box_number` varchar(255) NOT NULL,
  `boxable_type` varchar(255) NOT NULL,
  `boxable_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('open','closed','full') NOT NULL DEFAULT 'open',
  `location` varchar(255) DEFAULT NULL,
  `file_count` int(11) NOT NULL DEFAULT 0,
  `capacity` int(11) NOT NULL DEFAULT 100,
  `archived_date` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `region_id` bigint(20) UNSIGNED NOT NULL,
  `district_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `region_id`, `district_id`, `code`, `name`, `address`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '0001', 'Main Branch Muzaffarabad', 'Fatima Plaza, Chatter Domel Muzaffarabad', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(2, 4, 9, '0002', 'Main Branch Mirpur', 'Nishat Centre, Allama Iqbal Road, Mirpur', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(3, 4, 9, '0003', 'Dadyal Branch', 'Maqbool Butt Shaheed Chowk, Dadyal', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(4, 4, 9, '0004', 'Chaksawari Branch', 'Opposite Al-Shahzad Hotel Chaksawari', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(5, 3, 8, '0005', 'Kotli Branch', 'Poonch / Rawalpindi Road Kotli', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(6, 2, 5, '0006', 'Rawalakot Branch', 'Gulf Shopping Complex Katchari Road Rawalakot', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(7, 3, 8, '0007', 'Sehnsa Branch', 'Main Bazar Sehnsa AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(8, 1, 2, '0008', 'Athmuqam Branch', 'Main Bazar Athmuqam District Neelum AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(9, 4, 9, '0009', 'Islamgarh Branch', 'Main Bazar Islamgarh District Mirpur AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(10, 2, 4, '0010', 'Bagh Branch', 'Main Bazar Bagh AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(11, 2, 4, '0011', 'Arja Branch', 'Bagh/Rawalakot Chowk Arja District Bagh AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(12, 4, 10, '0012', 'Bhimber Branch', 'Main Bazar Bhimber AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(13, 2, 5, '0013', 'Pallandari Branch', 'Main Bazar Pallandari AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(14, 3, 8, '0014', 'Nar Branch', 'Main Bazar Nar District Kotli AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(15, 4, 9, '0015', 'F-3 Branch, Mirpur', 'Sector F-3 Mirpur AJK', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(16, 2, 5, '0016', 'Hajeera Branch', 'Main Bazar Hajeera District Poonch Azad Kashmir', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(17, 1, 1, '0017', 'Garhi-Dupata Branch', 'Pull Bazar Garhi Dupatta Muzaffarabad A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(18, 1, 1, '0018', 'Lower Plate Branch', 'Near City Hospital Lower Plate Muzaffarabad A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(19, 1, 1, '0019', 'Chatter Klass Branch', 'Kullian Bazar, Kohala-Muzaffarabad Road', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(20, 3, 8, '0020', 'Brahtla Branch', 'Main Bazar Brahtla District Kotli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(21, 3, 8, '0021', 'Khui Ratta Branch', 'Main Bazar Khui Ratta District Kotli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(22, 1, 1, '0022', 'Chakaar Branch', 'Main Bazar Chakaar District Muzaffarabad A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(23, 2, 4, '0023', 'Dheerkot Branch', 'Main Bazar Dheerkot District Bagh A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(24, 1, 1, '0024', 'Pathika Branch', 'Main Bazar Pathika District Muzaffarabad A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(25, 2, 5, '0025', 'Bandi Abbaspur Branch', 'Pul Bazar Bandi Abbaspur District Poonch A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(26, 3, 8, '0026', 'Sarsawa Branch', 'Main Bazar Sarsawa District Kotli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(27, 4, 9, '0027', 'Pulmanda Branch', 'Main Bazar Pulmanda District Mirpur A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(28, 1, 1, '0028', 'Madina Market Branch', 'Madina Market District Muzaffarabad A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(29, 4, 10, '0029', 'Barnala Branch', 'Main Bazar Barnala District Bhimber A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(30, 2, 5, '0030', 'Khai-gala Branch', 'Main Bazar Khai-gala District Poonch A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(31, 3, 8, '0031', 'Khad-Gujran Branch', 'Main Bazar Khad-Gujran District Kotli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(32, 2, 7, '0032', 'Trarkhal Branch', 'Main Bazar Tararkhal District Sudhanoti', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(33, 4, 10, '0033', 'Chowki Branch', 'Main Bazar Chowki District Bhimber A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(34, 1, 3, '0034', 'Hattian Branch', 'Sakhi Plaza, Chowk Bazar, Hattian Bala', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(35, 4, 10, '0035', 'Poona Branch', 'Main Bazar Poona District Bhimber A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(36, 3, 8, '0036', 'Charhoi Branch', 'Main Bazar Charhoi District Kotli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(37, 1, 3, '0037', 'Chakothi Branch', 'Main Bazar Chakhoti District Hattian A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(38, 2, 6, '0038', 'Kahotta Branch', 'Main Bazar Kahotta District Haveli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(39, 2, 5, '0039', 'Paniola Branch', 'Main Bazar Paniola District Poonch A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(40, 1, 1, '0040', 'District Complex Muzaffarabad Branch', 'Old Secretariat District Complex District Muzaffarabd', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(41, 2, 7, '0041', 'Bloch Branch', 'Main Bazar Bloch District Sudhanoti', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(42, 3, 8, '0042', 'Tatta-Pani Branch', 'Main Bazar Tatta-Pani District Kotli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(43, 1, 2, '0043', 'Sharda Branch', 'Main Sharda Bazar District Neelum A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(44, 3, 8, '0044', 'Mi-Toti Branch', 'Main Bazar Mi-Toti District Kotli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(45, 4, 9, '0045', 'Jatlan Branch', 'Main Bazar Jatlan District Mirpur', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(46, 4, 9, '0046', 'Mian Muhammad Town Branch', 'Mian Muhammad Town District Mirpur A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(47, 4, 9, '0047', 'Jhatti Dheri Branch', 'Jhatti Dheri Cross District Mirpur A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(48, 3, 8, '0048', 'Dhanna Branch', 'Dhanna Bazar District Kotli A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(49, 2, 7, '0049', 'Talian Branch', 'Main Bazar Talian District Sadhnutti A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(50, 2, 5, '0050', 'Thorar Branch', 'Main Bazar Thorar District Poonch A.K', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(51, 3, 8, '0051', 'Juna Branch', 'Main Bazar Juna District Kotli A.K', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(52, 1, 2, '0052', 'Joora Branch', 'Main Bazar Joora District Neelum A.K', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(53, 1, 1, '0053', 'Ladies Branch, Muzaffarabad', 'Hashim Plaza, Poultry Farm Road, Muzaffarabad, AK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(54, 4, 9, '0054', 'Ladies Branch, Mirpur', 'Haris Plaza Chowk Shaheedan Sector C-2 Mirpur AK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(55, 3, 8, '0055', 'Seharmandi Branch', 'Main Bazar Seharmandi, Tehsil Sehensa District Kotli AK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(56, 2, 5, '0056', 'CMH Road Rawalakot Branch', 'CMH Road Rawalakot AK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(57, 4, 9, '0057', 'Main Bazar Chakswari Branch', 'Main Bazar Chakswari Tehsil & District Mirpur AK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(58, 4, 9, '0058', 'Ratta Branch Mirpur', 'Allah Abad Market Main Road Ratta Tehsil Dadyal Distt. Mirpur', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(59, 4, 10, '0059', 'Moil Branch', 'Tehsil & District Bhimber AK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(60, 2, 5, '0060', 'Banbehk Branch', 'Tehsil & District Poonch', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(61, 1, 1, '0061', 'Upper Ada Branch', 'Near Shah Anayat Darbar Upper Ada Muzaffarabad', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(62, 1, 3, '0062', 'Chathian Branch', 'Srinagar Road, Chathian Bazar, Dist. Hattian Bala', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(63, 1, 2, '0063', 'Kutton Branch', 'Kutton, Tehsil Authmuqam, District Neelum', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(64, 3, 8, '0064', 'Jarahi Branch', 'Tehsil Dolya Jattan, District Kotli', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(65, 3, 8, '0065', 'Goi Branch', 'Village & P.O Goi Tehsil & District Kotli', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(66, 3, 8, '0066', 'Lorry Adda Branch', 'Lorry Adda, Tehsil & District Kotli, AJK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(67, 1, 1, '0067', 'Gojra By-Pass Branch', 'Gojra By-Pass Road, Near WAPDA Colony, Muzaffarabad AJK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(68, 2, 7, '0068', 'Mang Branch', 'Mang Bazar, Near AC Office, District Sudhnuti AJK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(69, 1, 2, '0069', 'Kundal Shahi Branch', 'Main Bazar, Kundal Shahi, Tehsil Athmuqam, District Neelum', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(70, 1, 1, '0070', 'Brarkot Branch', 'Abbottabad Road Brarkot Tehsil & District Muzaffarabad A.K', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(71, 2, 7, '0071', 'Muslimabad Branch', 'Pattan Sher Khan, Tehsil Mang, District Sudhunoti, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(72, 2, 7, '0072', 'Islam Nagar Branch', 'Pattan Sher Khan, Tehsil Mang, District Sudhunoti, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(73, 4, 10, '0073', 'Samahni Branch', 'Shahrah-e-Jammu, Samahni, District Bhimber, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(74, 2, 7, '0074', 'Panthal Branch', 'Panthal Branch, Tehsil and District Sudhanoti, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(75, 3, 8, '0075', 'Nakyal Branch', 'Nakyal, Fatehpur Thakyala, District Kotli, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(76, 1, 3, '0076', 'Chinari Branch', 'Main Bazar Chinari, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(77, 1, 1, '0077', 'PM House Road Branch', 'Near PM House Muzaffarabad, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(78, 4, 10, '0078', 'Kot Jamel Branch', 'Kot Jamel Tehsil Barnala Distt. Bhimber, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(79, 4, 9, '0079', 'Kotli Road Branch Mirpur', 'Kotli Road branch opposite PWD Office Mirpur, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(80, 4, 9, '0080', 'Mangla Hamlet', 'Main Bazar, Mangla Hamlet, Tehsil and District, Mirpur, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(81, 4, 9, '0081', 'Khanabad', 'Khanabad Tehsil and District Mirpur, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(82, 3, 8, '0082', 'Bypass Road Branch', 'Gulhar Shareef Chowk, Kotli, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(83, 2, 5, '0083', 'Public Housing Scheme Branch', 'Bank Road Rawalakot (AK)', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(84, 1, 3, '0084', 'Main Bazar Leepa', 'Jhelum Valley (AJ&K)', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(85, 1, 1, '0085', 'Chella Bandi Branch', 'Neelum Road, Chella Bandi, Muzaffarabad AJK', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(86, 2, 4, '0086', 'Hari Ghel Branch', 'Opposite To Go Petrol Pump Tehsil Hari Ghel District Bagh, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(87, 4, 9, '0087', 'Chechian Branch', 'Main Bazar Chechian District Mirpur, Azad Kashmir', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `branch_targets`
--

CREATE TABLE `branch_targets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `updated_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `annual_target_amount` decimal(15,3) NOT NULL,
  `target_start_date` date DEFAULT NULL,
  `fiscal_year` year(4) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business_natures`
--

CREATE TABLE `business_natures` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL COMMENT 'Import/Export, Agriculture, Manufacturing, Exchange Company, NBFI, Scheduled Bank, Retail Business, Others',
  `name_ur` varchar(120) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_natures`
--

INSERT INTO `business_natures` (`id`, `code`, `name`, `name_ur`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1890-7250-b00c-043ebd2fb511', 'IMPORT_EXPORT', 'Import/Export', 'درآمد/برآمد', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1890-7250-b00c-043ebdf8809c', 'AGRICULTURE', 'Agriculture', 'زراعت', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1891-70c3-84b7-3f1fe2d06f77', 'MANUFACTURING', 'Manufacturing', 'مینوفیکچرنگ', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1892-7323-a666-881c6ff50413', 'EXCHANGE_COMPANY', 'Exchange Company', 'ایکسچینج کمپنی', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1892-7323-a666-881c6ff94c19', 'NBFI', 'NBFI', 'این بی ایف آئی', 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1893-732d-a87b-7f89f4442fa5', 'SCHEDULED_BANK', 'Scheduled Bank', 'شیڈول بینک', 0, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1894-736d-bf08-791736717486', 'RETAIL_BUSINESS', 'Retail Business', 'ریٹیل کاروبار', 0, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1894-736d-bf08-791736b3c758', 'BN_OTHERS', 'Others', 'دیگر', 1, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('580071baad7b227a20e85017933acecb', 'i:1;', 1791990159),
('580071baad7b227a20e85017933acecb:timer', 'i:1791990159;', 1791990159),
('aksic-index-stats:v2:0:942d3775d9ad7883607d91dcaad517dd', 'a:9:{s:5:\"cases\";i:3;s:9:\"principal\";d:2500000;s:6:\"markup\";d:871241.517582;s:9:\"generated\";i:3;s:7:\"pending\";i:0;s:4:\"male\";i:3;s:6:\"female\";i:0;s:5:\"other\";i:0;s:7:\"average\";d:833333.333333;}', 1791990209),
('boost:mcp:database-schema:mariadb:sales_settlement:1:0:0:0', 'a:2:{s:6:\"engine\";s:7:\"mariadb\";s:6:\"tables\";a:0:{}}', 1792346952),
('dashboard:aksic:v1:0:58bd6a0d3ade707c9bcb57bdbb07cc86', 'a:8:{s:6:\"office\";a:3:{s:5:\"level\";s:3:\"all\";s:10:\"branch_ids\";N;s:5:\"label\";s:12:\"All branches\";}s:6:\"totals\";a:6:{s:5:\"cases\";i:3;s:7:\"pending\";i:0;s:8:\"approved\";i:3;s:9:\"principal\";d:2500000;s:6:\"markup\";d:871241.52;s:12:\"female_share\";d:0;}s:7:\"monthly\";a:3:{s:6:\"labels\";a:12:{i:0;s:6:\"Nov 25\";i:1;s:6:\"Dec 25\";i:2;s:6:\"Jan 26\";i:3;s:6:\"Feb 26\";i:4;s:6:\"Mar 26\";i:5;s:6:\"Apr 26\";i:6;s:6:\"May 26\";i:7;s:6:\"Jun 26\";i:8;s:6:\"Jul 26\";i:9;s:6:\"Aug 26\";i:10;s:6:\"Sep 26\";i:11;s:6:\"Oct 26\";}s:5:\"total\";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:1;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:2;i:11;i:0;}s:8:\"approved\";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:1;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:2;i:11;i:0;}}s:5:\"quota\";a:4:{s:4:\"Male\";i:3;s:6:\"Female\";i:0;s:8:\"Disabled\";i:0;s:11:\"Transgender\";i:0;}s:12:\"breakdown_by\";s:8:\"district\";s:9:\"breakdown\";a:2:{i:0;a:3:{s:5:\"label\";s:4:\"Bagh\";s:8:\"approved\";i:2;s:7:\"pending\";i:0;}i:1;a:3:{s:5:\"label\";s:12:\"Muzaffarabad\";s:8:\"approved\";i:1;s:7:\"pending\";i:0;}}s:14:\"recent_pending\";a:0:{}s:6:\"budget\";a:6:{s:5:\"title\";s:41:\"PM Youth Loan Scheme -- Markup Allocation\";s:6:\"budget\";d:994027000;s:4:\"used\";d:871241.51;s:9:\"remaining\";d:993155758.49;s:7:\"percent\";d:0.1;s:3:\"url\";s:42:\"https://nexus.test/product/aksic-budgets/1\";}}', 1791990174),
('dashboard:aof:v1:bea7eed10f4b9ee051d250032a5080a7', 'a:8:{s:5:\"label\";s:12:\"All branches\";s:6:\"totals\";a:8:{s:8:\"requests\";i:0;s:11:\"in_progress\";i:0;s:8:\"awaiting\";i:0;s:8:\"approved\";i:0;s:8:\"rejected\";i:0;s:10:\"this_month\";i:0;s:10:\"individual\";i:0;s:6:\"entity\";i:0;}s:6:\"status\";a:5:{s:5:\"draft\";i:0;s:9:\"submitted\";i:0;s:12:\"under_review\";i:0;s:8:\"approved\";i:0;s:8:\"rejected\";i:0;}s:7:\"monthly\";a:3:{s:6:\"labels\";a:12:{i:0;s:6:\"Nov 25\";i:1;s:6:\"Dec 25\";i:2;s:6:\"Jan 26\";i:3;s:6:\"Feb 26\";i:4;s:6:\"Mar 26\";i:5;s:6:\"Apr 26\";i:6;s:6:\"May 26\";i:7;s:6:\"Jun 26\";i:8;s:6:\"Jul 26\";i:9;s:6:\"Aug 26\";i:10;s:6:\"Sep 26\";i:11;s:6:\"Oct 26\";}s:5:\"total\";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;}s:8:\"approved\";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;}}s:12:\"breakdown_by\";s:6:\"branch\";s:9:\"breakdown\";a:0:{}s:8:\"products\";a:0:{}s:8:\"awaiting\";a:0:{}}', 1791990174),
('dashboard:files:v1:cc42010ec8e7f4ed274d8ebeb2f24eb2', 'a:5:{s:10:\"unit_label\";s:11:\"All offices\";s:6:\"totals\";a:7:{s:5:\"files\";i:0;s:8:\"archived\";i:0;s:6:\"active\";i:0;s:10:\"this_month\";i:0;s:5:\"pages\";i:0;s:8:\"incoming\";i:0;s:8:\"outgoing\";i:0;}s:7:\"monthly\";a:2:{s:6:\"labels\";a:12:{i:0;s:6:\"Nov 25\";i:1;s:6:\"Dec 25\";i:2;s:6:\"Jan 26\";i:3;s:6:\"Feb 26\";i:4;s:6:\"Mar 26\";i:5;s:6:\"Apr 26\";i:6;s:6:\"May 26\";i:7;s:6:\"Jun 26\";i:8;s:6:\"Jul 26\";i:9;s:6:\"Aug 26\";i:10;s:6:\"Sep 26\";i:11;s:6:\"Oct 26\";}s:5:\"total\";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;}}s:10:\"categories\";a:0:{}s:6:\"recent\";a:0:{}}', 1791990174),
('pending-migrations:ac9f28598fd2ec84b494bc45c8f7f2d9', 'a:0:{}', 1791990414);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('roster:project:v3:9f32eff09f2c499c2c77ca1fce3d7fcc', 'O:26:\"Laravel\\Roster\\ProjectScan\":8:{s:8:\"basePath\";s:33:\"/Users/alirazamarchal/Herd/nexus/\";s:3:\"php\";O:35:\"Laravel\\Roster\\Ecosystems\\Ecosystem\":2:{s:9:\"\0*\0byName\";a:168:{s:23:\"alimarchal/id-generator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"alimarchal/id-generator\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";E:43:\"Laravel\\Roster\\Enums\\PackageSource:Composer\";s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.1\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/alimarchal/id-generator\";}s:19:\"bacon/bacon-qr-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"bacon/bacon-qr-code\";s:10:\"\0*\0version\";s:5:\"3.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/bacon/bacon-qr-code\";}s:23:\"barryvdh/laravel-dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"barryvdh/laravel-dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/barryvdh/laravel-dompdf\";}s:10:\"brick/math\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"brick/math\";s:10:\"\0*\0version\";s:6:\"0.19.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/vendor/brick/math\";}s:31:\"carbonphp/carbon-doctrine-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"carbonphp/carbon-doctrine-types\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/vendor/carbonphp/carbon-doctrine-types\";}s:13:\"composer/pcre\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"composer/pcre\";s:10:\"\0*\0version\";s:5:\"3.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/pcre\";}s:15:\"composer/semver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"composer/semver\";s:10:\"\0*\0version\";s:5:\"3.4.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/semver\";}s:12:\"dasprid/enum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dasprid/enum\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/dasprid/enum\";}s:23:\"dflydev/dot-access-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"dflydev/dot-access-data\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/dflydev/dot-access-data\";}s:21:\"doctrine/deprecations\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"doctrine/deprecations\";s:10:\"\0*\0version\";s:5:\"1.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/deprecations\";}s:18:\"doctrine/inflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"doctrine/inflector\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/inflector\";}s:14:\"doctrine/lexer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"doctrine/lexer\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/lexer\";}s:13:\"dompdf/dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"dompdf/dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/dompdf\";}s:19:\"dompdf/php-font-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"dompdf/php-font-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/php-font-lib\";}s:18:\"dompdf/php-svg-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"dompdf/php-svg-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/php-svg-lib\";}s:29:\"dragonmantank/cron-expression\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"dragonmantank/cron-expression\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/dragonmantank/cron-expression\";}s:23:\"egulias/email-validator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"egulias/email-validator\";s:10:\"\0*\0version\";s:5:\"4.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/egulias/email-validator\";}s:18:\"fruitcake/php-cors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"fruitcake/php-cors\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/fruitcake/php-cors\";}s:27:\"graham-campbell/result-type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"graham-campbell/result-type\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/graham-campbell/result-type\";}s:17:\"guzzlehttp/guzzle\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"guzzlehttp/guzzle\";s:10:\"\0*\0version\";s:5:\"8.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/guzzle\";}s:19:\"guzzlehttp/promises\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"guzzlehttp/promises\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/promises\";}s:15:\"guzzlehttp/psr7\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"guzzlehttp/psr7\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/psr7\";}s:23:\"guzzlehttp/uri-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"guzzlehttp/uri-template\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/uri-template\";}s:15:\"laravel/fortify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/fortify\";s:10:\"\0*\0version\";s:6:\"1.39.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/fortify\";}s:17:\"laravel/framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"13.32.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^13.0\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/framework\";}s:17:\"laravel/jetstream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/jetstream\";s:10:\"\0*\0version\";s:5:\"5.5.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^5.3\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/jetstream\";}s:16:\"laravel/passkeys\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"laravel/passkeys\";s:10:\"\0*\0version\";s:5:\"0.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/passkeys\";}s:15:\"laravel/prompts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:6:\"0.3.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/prompts\";}s:15:\"laravel/sanctum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/sanctum\";s:10:\"\0*\0version\";s:5:\"4.3.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/sanctum\";}s:28:\"laravel/serializable-closure\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"laravel/serializable-closure\";s:10:\"\0*\0version\";s:6:\"2.0.16\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/serializable-closure\";}s:14:\"laravel/tinker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/tinker\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/tinker\";}s:17:\"league/commonmark\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"league/commonmark\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/league/commonmark\";}s:13:\"league/config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"league/config\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/league/config\";}s:16:\"league/flysystem\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"league/flysystem\";s:10:\"\0*\0version\";s:6:\"3.36.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/league/flysystem\";}s:22:\"league/flysystem-local\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"league/flysystem-local\";s:10:\"\0*\0version\";s:6:\"3.35.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/league/flysystem-local\";}s:26:\"league/mime-type-detection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"league/mime-type-detection\";s:10:\"\0*\0version\";s:6:\"1.17.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/league/mime-type-detection\";}s:10:\"league/uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"league/uri\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/vendor/league/uri\";}s:21:\"league/uri-interfaces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"league/uri-interfaces\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/league/uri-interfaces\";}s:17:\"livewire/livewire\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"livewire/livewire\";s:10:\"\0*\0version\";s:5:\"3.8.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/livewire/livewire\";}s:23:\"maennchen/zipstream-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"maennchen/zipstream-php\";s:10:\"\0*\0version\";s:5:\"3.2.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/maennchen/zipstream-php\";}s:17:\"markbaker/complex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"markbaker/complex\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/markbaker/complex\";}s:16:\"markbaker/matrix\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"markbaker/matrix\";s:10:\"\0*\0version\";s:5:\"3.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/markbaker/matrix\";}s:17:\"masterminds/html5\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"masterminds/html5\";s:10:\"\0*\0version\";s:6:\"2.11.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/masterminds/html5\";}s:28:\"mobiledetect/mobiledetectlib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"mobiledetect/mobiledetectlib\";s:10:\"\0*\0version\";s:6:\"4.11.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/mobiledetect/mobiledetectlib\";}s:15:\"monolog/monolog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"monolog/monolog\";s:10:\"\0*\0version\";s:6:\"3.12.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/monolog/monolog\";}s:13:\"nesbot/carbon\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"nesbot/carbon\";s:10:\"\0*\0version\";s:6:\"3.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/nesbot/carbon\";}s:12:\"nette/schema\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"nette/schema\";s:10:\"\0*\0version\";s:5:\"1.3.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/nette/schema\";}s:11:\"nette/utils\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"nette/utils\";s:10:\"\0*\0version\";s:5:\"4.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/nette/utils\";}s:16:\"nikic/php-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"nikic/php-parser\";s:10:\"\0*\0version\";s:5:\"5.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/nikic/php-parser\";}s:19:\"nunomaduro/termwind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"nunomaduro/termwind\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/nunomaduro/termwind\";}s:32:\"paragonie/constant_time_encoding\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"paragonie/constant_time_encoding\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/vendor/paragonie/constant_time_encoding\";}s:31:\"phpdocumentor/reflection-common\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"phpdocumentor/reflection-common\";s:10:\"\0*\0version\";s:5:\"2.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/reflection-common\";}s:33:\"phpdocumentor/reflection-docblock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"phpdocumentor/reflection-docblock\";s:10:\"\0*\0version\";s:5:\"6.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/reflection-docblock\";}s:27:\"phpdocumentor/type-resolver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"phpdocumentor/type-resolver\";s:10:\"\0*\0version\";s:5:\"2.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/type-resolver\";}s:24:\"phpoffice/phpspreadsheet\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"phpoffice/phpspreadsheet\";s:10:\"\0*\0version\";s:6:\"5.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^5.7\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/phpoffice/phpspreadsheet\";}s:19:\"phpoption/phpoption\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpoption/phpoption\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/phpoption/phpoption\";}s:21:\"phpstan/phpdoc-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"phpstan/phpdoc-parser\";s:10:\"\0*\0version\";s:5:\"2.3.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/phpstan/phpdoc-parser\";}s:18:\"pragmarx/google2fa\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"pragmarx/google2fa\";s:10:\"\0*\0version\";s:5:\"9.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/pragmarx/google2fa\";}s:9:\"psr/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psr/clock\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/clock\";}s:13:\"psr/container\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"psr/container\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/container\";}s:20:\"psr/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"psr/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/event-dispatcher\";}s:15:\"psr/http-client\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"psr/http-client\";s:10:\"\0*\0version\";s:5:\"1.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-client\";}s:16:\"psr/http-factory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-factory\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-factory\";}s:16:\"psr/http-message\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-message\";s:10:\"\0*\0version\";s:3:\"2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-message\";}s:7:\"psr/log\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"psr/log\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/log\";}s:16:\"psr/simple-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/simple-cache\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/simple-cache\";}s:9:\"psy/psysh\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psy/psysh\";s:10:\"\0*\0version\";s:7:\"0.12.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/vendor/psy/psysh\";}s:17:\"ramsey/collection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"ramsey/collection\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/ramsey/collection\";}s:11:\"ramsey/uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ramsey/uuid\";s:10:\"\0*\0version\";s:5:\"4.9.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/ramsey/uuid\";}s:25:\"sabberworm/php-css-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"sabberworm/php-css-parser\";s:10:\"\0*\0version\";s:5:\"9.5.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/sabberworm/php-css-parser\";}s:12:\"spatie/image\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"spatie/image\";s:10:\"\0*\0version\";s:5:\"3.9.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/image\";}s:22:\"spatie/image-optimizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"spatie/image-optimizer\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/image-optimizer\";}s:26:\"spatie/laravel-activitylog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"spatie/laravel-activitylog\";s:10:\"\0*\0version\";s:6:\"4.12.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.9\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-activitylog\";}s:27:\"spatie/laravel-medialibrary\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"spatie/laravel-medialibrary\";s:10:\"\0*\0version\";s:7:\"11.23.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^11.23\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-medialibrary\";}s:28:\"spatie/laravel-package-tools\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-package-tools\";s:10:\"\0*\0version\";s:6:\"1.93.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-package-tools\";}s:25:\"spatie/laravel-permission\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spatie/laravel-permission\";s:10:\"\0*\0version\";s:6:\"6.25.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^6.10\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-permission\";}s:28:\"spatie/laravel-query-builder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-query-builder\";s:10:\"\0*\0version\";s:5:\"6.4.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^6.3\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-query-builder\";}s:26:\"spatie/temporary-directory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"spatie/temporary-directory\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/temporary-directory\";}s:20:\"spomky-labs/cbor-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"spomky-labs/cbor-php\";s:10:\"\0*\0version\";s:5:\"3.4.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/spomky-labs/cbor-php\";}s:25:\"spomky-labs/pki-framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spomky-labs/pki-framework\";s:10:\"\0*\0version\";s:5:\"1.6.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/spomky-labs/pki-framework\";}s:13:\"symfony/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"symfony/clock\";s:10:\"\0*\0version\";s:5:\"8.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/clock\";}s:15:\"symfony/console\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/console\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/console\";}s:20:\"symfony/css-selector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"symfony/css-selector\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/css-selector\";}s:29:\"symfony/deprecation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/deprecation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/deprecation-contracts\";}s:21:\"symfony/error-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/error-handler\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/error-handler\";}s:24:\"symfony/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"symfony/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/event-dispatcher\";}s:34:\"symfony/event-dispatcher-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"symfony/event-dispatcher-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/event-dispatcher-contracts\";}s:14:\"symfony/finder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/finder\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/finder\";}s:23:\"symfony/http-foundation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/http-foundation\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/http-foundation\";}s:19:\"symfony/http-kernel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/http-kernel\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/http-kernel\";}s:14:\"symfony/mailer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/mailer\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/mailer\";}s:12:\"symfony/mime\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/mime\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/mime\";}s:22:\"symfony/polyfill-ctype\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-ctype\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-ctype\";}s:30:\"symfony/polyfill-intl-grapheme\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"symfony/polyfill-intl-grapheme\";s:10:\"\0*\0version\";s:6:\"1.41.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-grapheme\";}s:25:\"symfony/polyfill-intl-idn\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-intl-idn\";s:10:\"\0*\0version\";s:6:\"1.42.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-idn\";}s:32:\"symfony/polyfill-intl-normalizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"symfony/polyfill-intl-normalizer\";s:10:\"\0*\0version\";s:6:\"1.42.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-normalizer\";}s:25:\"symfony/polyfill-mbstring\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-mbstring\";s:10:\"\0*\0version\";s:6:\"1.38.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-mbstring\";}s:22:\"symfony/polyfill-php80\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php80\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php80\";}s:22:\"symfony/polyfill-php81\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php81\";s:10:\"\0*\0version\";s:6:\"1.38.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php81\";}s:22:\"symfony/polyfill-php82\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php82\";s:10:\"\0*\0version\";s:6:\"1.38.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php82\";}s:22:\"symfony/polyfill-php84\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php84\";s:10:\"\0*\0version\";s:6:\"1.38.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php84\";}s:22:\"symfony/polyfill-php85\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php85\";s:10:\"\0*\0version\";s:6:\"1.41.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php85\";}s:22:\"symfony/polyfill-php86\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php86\";s:10:\"\0*\0version\";s:6:\"1.41.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php86\";}s:21:\"symfony/polyfill-uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-uuid\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-uuid\";}s:15:\"symfony/process\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/process\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/process\";}s:23:\"symfony/property-access\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/property-access\";s:10:\"\0*\0version\";s:5:\"8.1.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/property-access\";}s:21:\"symfony/property-info\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/property-info\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/property-info\";}s:15:\"symfony/routing\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/routing\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/routing\";}s:18:\"symfony/serializer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/serializer\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/serializer\";}s:25:\"symfony/service-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/service-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/service-contracts\";}s:14:\"symfony/string\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/string\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/string\";}s:19:\"symfony/translation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/translation\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/translation\";}s:29:\"symfony/translation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/translation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/translation-contracts\";}s:17:\"symfony/type-info\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"symfony/type-info\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/type-info\";}s:11:\"symfony/uid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"symfony/uid\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/uid\";}s:18:\"symfony/var-dumper\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/var-dumper\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/var-dumper\";}s:33:\"tijsverkoyen/css-to-inline-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"tijsverkoyen/css-to-inline-styles\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/vendor/tijsverkoyen/css-to-inline-styles\";}s:16:\"vlucas/phpdotenv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"vlucas/phpdotenv\";s:10:\"\0*\0version\";s:5:\"5.7.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/vlucas/phpdotenv\";}s:19:\"voku/portable-ascii\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"voku/portable-ascii\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/voku/portable-ascii\";}s:17:\"web-auth/cose-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"web-auth/cose-lib\";s:10:\"\0*\0version\";s:5:\"4.8.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/web-auth/cose-lib\";}s:21:\"web-auth/webauthn-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"web-auth/webauthn-lib\";s:10:\"\0*\0version\";s:5:\"5.3.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/web-auth/webauthn-lib\";}s:16:\"webmozart/assert\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"webmozart/assert\";s:10:\"\0*\0version\";s:5:\"2.4.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/webmozart/assert\";}s:17:\"brianium/paratest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"brianium/paratest\";s:10:\"\0*\0version\";s:6:\"7.20.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/brianium/paratest\";}s:23:\"composer/xdebug-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"composer/xdebug-handler\";s:10:\"\0*\0version\";s:5:\"3.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/xdebug-handler\";}s:14:\"fakerphp/faker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"fakerphp/faker\";s:10:\"\0*\0version\";s:6:\"1.24.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.23\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/fakerphp/faker\";}s:22:\"fidry/cpu-core-counter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"fidry/cpu-core-counter\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/fidry/cpu-core-counter\";}s:11:\"filp/whoops\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"filp/whoops\";s:10:\"\0*\0version\";s:6:\"2.18.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/filp/whoops\";}s:21:\"hamcrest/hamcrest-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"hamcrest/hamcrest-php\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/hamcrest/hamcrest-php\";}s:30:\"jean85/pretty-package-versions\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"jean85/pretty-package-versions\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/vendor/jean85/pretty-package-versions\";}s:13:\"laravel/boost\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"laravel/boost\";s:10:\"\0*\0version\";s:5:\"2.9.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^2.0\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/boost\";}s:11:\"laravel/mcp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/mcp\";}s:12:\"laravel/pail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pail\";s:10:\"\0*\0version\";s:5:\"1.2.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.1\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/pail\";}s:12:\"laravel/pint\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.32.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.13\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/pint\";}s:14:\"laravel/roster\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/roster\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/roster\";}s:12:\"laravel/sail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/sail\";s:10:\"\0*\0version\";s:6:\"1.67.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.26\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/sail\";}s:15:\"mockery/mockery\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"mockery/mockery\";s:10:\"\0*\0version\";s:6:\"1.6.15\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.6\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/mockery/mockery\";}s:17:\"myclabs/deep-copy\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"myclabs/deep-copy\";s:10:\"\0*\0version\";s:6:\"1.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/myclabs/deep-copy\";}s:20:\"nunomaduro/collision\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"nunomaduro/collision\";s:10:\"\0*\0version\";s:5:\"8.9.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.1\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/nunomaduro/collision\";}s:12:\"pestphp/pest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"pestphp/pest\";s:10:\"\0*\0version\";s:5:\"4.7.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest\";}s:19:\"pestphp/pest-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"pestphp/pest-plugin\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin\";}s:24:\"pestphp/pest-plugin-arch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"pestphp/pest-plugin-arch\";s:10:\"\0*\0version\";s:5:\"4.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-arch\";}s:26:\"pestphp/pest-plugin-mutate\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"pestphp/pest-plugin-mutate\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-mutate\";}s:29:\"pestphp/pest-plugin-profanity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"pestphp/pest-plugin-profanity\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-profanity\";}s:16:\"phar-io/manifest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"phar-io/manifest\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/phar-io/manifest\";}s:15:\"phar-io/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phar-io/version\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/phar-io/version\";}s:25:\"phpunit/php-code-coverage\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-code-coverage\";s:10:\"\0*\0version\";s:6:\"12.5.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-code-coverage\";}s:25:\"phpunit/php-file-iterator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-file-iterator\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-file-iterator\";}s:19:\"phpunit/php-invoker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpunit/php-invoker\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-invoker\";}s:25:\"phpunit/php-text-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-text-template\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-text-template\";}s:17:\"phpunit/php-timer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"phpunit/php-timer\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-timer\";}s:15:\"phpunit/phpunit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:7:\"12.5.33\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^12.0\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/phpunit\";}s:20:\"sebastian/cli-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/cli-parser\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/cli-parser\";}s:20:\"sebastian/comparator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/comparator\";s:10:\"\0*\0version\";s:5:\"7.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/comparator\";}s:20:\"sebastian/complexity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/complexity\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/complexity\";}s:14:\"sebastian/diff\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/diff\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/diff\";}s:21:\"sebastian/environment\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"sebastian/environment\";s:10:\"\0*\0version\";s:5:\"8.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/environment\";}s:18:\"sebastian/exporter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"sebastian/exporter\";s:10:\"\0*\0version\";s:5:\"7.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/exporter\";}s:22:\"sebastian/global-state\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"sebastian/global-state\";s:10:\"\0*\0version\";s:5:\"8.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/global-state\";}s:23:\"sebastian/lines-of-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"sebastian/lines-of-code\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/lines-of-code\";}s:27:\"sebastian/object-enumerator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/object-enumerator\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/object-enumerator\";}s:26:\"sebastian/object-reflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"sebastian/object-reflector\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/object-reflector\";}s:27:\"sebastian/recursion-context\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/recursion-context\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/recursion-context\";}s:14:\"sebastian/type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/type\";s:10:\"\0*\0version\";s:5:\"6.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/type\";}s:17:\"sebastian/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"sebastian/version\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/version\";}s:28:\"staabm/side-effects-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"staabm/side-effects-detector\";s:10:\"\0*\0version\";s:5:\"1.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/staabm/side-effects-detector\";}s:12:\"symfony/yaml\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/yaml\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/yaml\";}s:35:\"ta-tikoma/phpunit-architecture-test\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:35:\"ta-tikoma/phpunit-architecture-test\";s:10:\"\0*\0version\";s:5:\"0.8.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/vendor/ta-tikoma/phpunit-architecture-test\";}s:17:\"theseer/tokenizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"theseer/tokenizer\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/theseer/tokenizer\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:168:{i:0;r:5;i:1;r:13;i:2;r:21;i:3;r:29;i:4;r:37;i:5;r:45;i:6;r:53;i:7;r:61;i:8;r:69;i:9;r:77;i:10;r:85;i:11;r:93;i:12;r:101;i:13;r:109;i:14;r:117;i:15;r:125;i:16;r:133;i:17;r:141;i:18;r:149;i:19;r:157;i:20;r:165;i:21;r:173;i:22;r:181;i:23;r:189;i:24;r:197;i:25;r:205;i:26;r:213;i:27;r:221;i:28;r:229;i:29;r:237;i:30;r:245;i:31;r:253;i:32;r:261;i:33;r:269;i:34;r:277;i:35;r:285;i:36;r:293;i:37;r:301;i:38;r:309;i:39;r:317;i:40;r:325;i:41;r:333;i:42;r:341;i:43;r:349;i:44;r:357;i:45;r:365;i:46;r:373;i:47;r:381;i:48;r:389;i:49;r:397;i:50;r:405;i:51;r:413;i:52;r:421;i:53;r:429;i:54;r:437;i:55;r:445;i:56;r:453;i:57;r:461;i:58;r:469;i:59;r:477;i:60;r:485;i:61;r:493;i:62;r:501;i:63;r:509;i:64;r:517;i:65;r:525;i:66;r:533;i:67;r:541;i:68;r:549;i:69;r:557;i:70;r:565;i:71;r:573;i:72;r:581;i:73;r:589;i:74;r:597;i:75;r:605;i:76;r:613;i:77;r:621;i:78;r:629;i:79;r:637;i:80;r:645;i:81;r:653;i:82;r:661;i:83;r:669;i:84;r:677;i:85;r:685;i:86;r:693;i:87;r:701;i:88;r:709;i:89;r:717;i:90;r:725;i:91;r:733;i:92;r:741;i:93;r:749;i:94;r:757;i:95;r:765;i:96;r:773;i:97;r:781;i:98;r:789;i:99;r:797;i:100;r:805;i:101;r:813;i:102;r:821;i:103;r:829;i:104;r:837;i:105;r:845;i:106;r:853;i:107;r:861;i:108;r:869;i:109;r:877;i:110;r:885;i:111;r:893;i:112;r:901;i:113;r:909;i:114;r:917;i:115;r:925;i:116;r:933;i:117;r:941;i:118;r:949;i:119;r:957;i:120;r:965;i:121;r:973;i:122;r:981;i:123;r:989;i:124;r:997;i:125;r:1005;i:126;r:1013;i:127;r:1021;i:128;r:1029;i:129;r:1037;i:130;r:1045;i:131;r:1053;i:132;r:1061;i:133;r:1069;i:134;r:1077;i:135;r:1085;i:136;r:1093;i:137;r:1101;i:138;r:1109;i:139;r:1117;i:140;r:1125;i:141;r:1133;i:142;r:1141;i:143;r:1149;i:144;r:1157;i:145;r:1165;i:146;r:1173;i:147;r:1181;i:148;r:1189;i:149;r:1197;i:150;r:1205;i:151;r:1213;i:152;r:1221;i:153;r:1229;i:154;r:1237;i:155;r:1245;i:156;r:1253;i:157;r:1261;i:158;r:1269;i:159;r:1277;i:160;r:1285;i:161;r:1293;i:162;r:1301;i:163;r:1309;i:164;r:1317;i:165;r:1325;i:166;r:1333;i:167;r:1341;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:2:\"js\";O:37:\"Laravel\\Roster\\Ecosystems\\JsEcosystem\":3:{s:9:\"\0*\0byName\";a:181:{s:16:\"@alloc/quick-lru\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@alloc/quick-lru\";s:10:\"\0*\0version\";s:5:\"5.2.0\";s:9:\"\0*\0source\";E:38:\"Laravel\\Roster\\Enums\\PackageSource:Npm\";s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@alloc/quick-lru\";}s:18:\"@esbuild/aix-ppc64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/aix-ppc64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/aix-ppc64\";}s:20:\"@esbuild/android-arm\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/android-arm\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-arm\";}s:22:\"@esbuild/android-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/android-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-arm64\";}s:20:\"@esbuild/android-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/android-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-x64\";}s:21:\"@esbuild/darwin-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"@esbuild/darwin-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/darwin-arm64\";}s:19:\"@esbuild/darwin-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/darwin-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/darwin-x64\";}s:22:\"@esbuild/freebsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/freebsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/freebsd-arm64\";}s:20:\"@esbuild/freebsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/freebsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/freebsd-x64\";}s:18:\"@esbuild/linux-arm\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/linux-arm\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-arm\";}s:20:\"@esbuild/linux-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-arm64\";}s:19:\"@esbuild/linux-ia32\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/linux-ia32\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-ia32\";}s:22:\"@esbuild/linux-loong64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/linux-loong64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-loong64\";}s:23:\"@esbuild/linux-mips64el\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@esbuild/linux-mips64el\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-mips64el\";}s:20:\"@esbuild/linux-ppc64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-ppc64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-ppc64\";}s:22:\"@esbuild/linux-riscv64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/linux-riscv64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-riscv64\";}s:20:\"@esbuild/linux-s390x\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-s390x\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-s390x\";}s:18:\"@esbuild/linux-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/linux-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-x64\";}s:21:\"@esbuild/netbsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"@esbuild/netbsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/netbsd-arm64\";}s:19:\"@esbuild/netbsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/netbsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/netbsd-x64\";}s:22:\"@esbuild/openbsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/openbsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openbsd-arm64\";}s:20:\"@esbuild/openbsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/openbsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openbsd-x64\";}s:26:\"@esbuild/openharmony-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"@esbuild/openharmony-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openharmony-arm64\";}s:18:\"@esbuild/sunos-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/sunos-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/sunos-x64\";}s:20:\"@esbuild/win32-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/win32-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-arm64\";}s:19:\"@esbuild/win32-ia32\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/win32-ia32\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-ia32\";}s:18:\"@esbuild/win32-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/win32-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-x64\";}s:23:\"@jridgewell/gen-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@jridgewell/gen-mapping\";s:10:\"\0*\0version\";s:6:\"0.3.13\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/gen-mapping\";}s:23:\"@jridgewell/resolve-uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@jridgewell/resolve-uri\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/resolve-uri\";}s:27:\"@jridgewell/sourcemap-codec\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"@jridgewell/sourcemap-codec\";s:10:\"\0*\0version\";s:5:\"1.5.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/sourcemap-codec\";}s:25:\"@jridgewell/trace-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"@jridgewell/trace-mapping\";s:10:\"\0*\0version\";s:6:\"0.3.31\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/trace-mapping\";}s:19:\"@nodelib/fs.scandir\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@nodelib/fs.scandir\";s:10:\"\0*\0version\";s:5:\"2.1.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.scandir\";}s:16:\"@nodelib/fs.stat\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@nodelib/fs.stat\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.stat\";}s:16:\"@nodelib/fs.walk\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@nodelib/fs.walk\";s:10:\"\0*\0version\";s:5:\"1.2.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.walk\";}s:31:\"@rollup/rollup-android-arm-eabi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-android-arm-eabi\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-android-arm-eabi\";}s:28:\"@rollup/rollup-android-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-android-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-android-arm64\";}s:27:\"@rollup/rollup-darwin-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"@rollup/rollup-darwin-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-darwin-arm64\";}s:25:\"@rollup/rollup-darwin-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"@rollup/rollup-darwin-x64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-darwin-x64\";}s:28:\"@rollup/rollup-freebsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-freebsd-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-freebsd-arm64\";}s:26:\"@rollup/rollup-freebsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"@rollup/rollup-freebsd-x64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-freebsd-x64\";}s:34:\"@rollup/rollup-linux-arm-gnueabihf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"@rollup/rollup-linux-arm-gnueabihf\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:80:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm-gnueabihf\";}s:35:\"@rollup/rollup-linux-arm-musleabihf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:35:\"@rollup/rollup-linux-arm-musleabihf\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:81:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm-musleabihf\";}s:30:\"@rollup/rollup-linux-arm64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-arm64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm64-gnu\";}s:31:\"@rollup/rollup-linux-arm64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-linux-arm64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm64-musl\";}s:32:\"@rollup/rollup-linux-loong64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-linux-loong64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-loong64-gnu\";}s:30:\"@rollup/rollup-linux-ppc64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-ppc64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-ppc64-gnu\";}s:32:\"@rollup/rollup-linux-riscv64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-linux-riscv64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-riscv64-gnu\";}s:33:\"@rollup/rollup-linux-riscv64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"@rollup/rollup-linux-riscv64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:79:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-riscv64-musl\";}s:30:\"@rollup/rollup-linux-s390x-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-s390x-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-s390x-gnu\";}s:28:\"@rollup/rollup-linux-x64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-linux-x64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-x64-gnu\";}s:29:\"@rollup/rollup-linux-x64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"@rollup/rollup-linux-x64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-x64-musl\";}s:32:\"@rollup/rollup-openharmony-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-openharmony-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-openharmony-arm64\";}s:31:\"@rollup/rollup-win32-arm64-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-win32-arm64-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-arm64-msvc\";}s:30:\"@rollup/rollup-win32-ia32-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-win32-ia32-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-ia32-msvc\";}s:28:\"@rollup/rollup-win32-x64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-win32-x64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-x64-gnu\";}s:29:\"@rollup/rollup-win32-x64-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"@rollup/rollup-win32-x64-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-x64-msvc\";}s:18:\"@tailwindcss/forms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@tailwindcss/forms\";s:10:\"\0*\0version\";s:6:\"0.5.10\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^0.5.11\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@tailwindcss/forms\";}s:23:\"@tailwindcss/typography\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@tailwindcss/typography\";s:10:\"\0*\0version\";s:6:\"0.5.19\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^0.5.20\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@tailwindcss/typography\";}s:13:\"@types/estree\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"@types/estree\";s:10:\"\0*\0version\";s:5:\"1.0.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/@types/estree\";}s:10:\"ansi-regex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"ansi-regex\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/ansi-regex\";}s:11:\"ansi-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ansi-styles\";s:10:\"\0*\0version\";s:5:\"4.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/ansi-styles\";}s:11:\"any-promise\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"any-promise\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/any-promise\";}s:8:\"anymatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"anymatch\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/anymatch\";}s:3:\"arg\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:3:\"arg\";s:10:\"\0*\0version\";s:5:\"5.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/node_modules/arg\";}s:8:\"asynckit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"asynckit\";s:10:\"\0*\0version\";s:5:\"0.4.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/asynckit\";}s:12:\"autoprefixer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"autoprefixer\";s:10:\"\0*\0version\";s:7:\"10.4.22\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^10.6.1\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/autoprefixer\";}s:5:\"axios\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"axios\";s:10:\"\0*\0version\";s:6:\"1.13.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^1.20.0\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/axios\";}s:24:\"baseline-browser-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"baseline-browser-mapping\";s:10:\"\0*\0version\";s:5:\"2.9.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/node_modules/baseline-browser-mapping\";}s:17:\"binary-extensions\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"binary-extensions\";s:10:\"\0*\0version\";s:5:\"2.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/binary-extensions\";}s:6:\"braces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"braces\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/braces\";}s:12:\"browserslist\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"browserslist\";s:10:\"\0*\0version\";s:6:\"4.28.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/browserslist\";}s:23:\"call-bind-apply-helpers\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"call-bind-apply-helpers\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/call-bind-apply-helpers\";}s:13:\"camelcase-css\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"camelcase-css\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/camelcase-css\";}s:12:\"caniuse-lite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"caniuse-lite\";s:10:\"\0*\0version\";s:12:\"1.0.30001760\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/caniuse-lite\";}s:5:\"chalk\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"chalk\";s:10:\"\0*\0version\";s:5:\"4.1.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/chalk\";}s:8:\"chokidar\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"chokidar\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/chokidar\";}s:5:\"cliui\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"cliui\";s:10:\"\0*\0version\";s:5:\"8.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/cliui\";}s:13:\"color-convert\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"color-convert\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/color-convert\";}s:10:\"color-name\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"color-name\";s:10:\"\0*\0version\";s:5:\"1.1.4\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/color-name\";}s:15:\"combined-stream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"combined-stream\";s:10:\"\0*\0version\";s:5:\"1.0.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/combined-stream\";}s:9:\"commander\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"commander\";s:10:\"\0*\0version\";s:5:\"4.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/commander\";}s:12:\"concurrently\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"concurrently\";s:10:\"\0*\0version\";s:5:\"9.2.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^9.2.4\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/concurrently\";}s:6:\"cssesc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"cssesc\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/cssesc\";}s:14:\"delayed-stream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"delayed-stream\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/delayed-stream\";}s:10:\"didyoumean\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"didyoumean\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/didyoumean\";}s:3:\"dlv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:3:\"dlv\";s:10:\"\0*\0version\";s:5:\"1.1.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/node_modules/dlv\";}s:12:\"dunder-proto\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dunder-proto\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/dunder-proto\";}s:20:\"electron-to-chromium\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"electron-to-chromium\";s:10:\"\0*\0version\";s:7:\"1.5.267\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/electron-to-chromium\";}s:11:\"emoji-regex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"emoji-regex\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/emoji-regex\";}s:18:\"es-define-property\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"es-define-property\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-define-property\";}s:9:\"es-errors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"es-errors\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-errors\";}s:15:\"es-object-atoms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"es-object-atoms\";s:10:\"\0*\0version\";s:5:\"1.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-object-atoms\";}s:18:\"es-set-tostringtag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"es-set-tostringtag\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-set-tostringtag\";}s:7:\"esbuild\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"esbuild\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/esbuild\";}s:8:\"escalade\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"escalade\";s:10:\"\0*\0version\";s:5:\"3.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/escalade\";}s:9:\"fast-glob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"fast-glob\";s:10:\"\0*\0version\";s:5:\"3.3.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/fast-glob\";}s:5:\"fastq\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"fastq\";s:10:\"\0*\0version\";s:6:\"1.19.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/fastq\";}s:10:\"fill-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"fill-range\";s:10:\"\0*\0version\";s:5:\"7.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/fill-range\";}s:16:\"follow-redirects\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"follow-redirects\";s:10:\"\0*\0version\";s:7:\"1.15.11\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/follow-redirects\";}s:9:\"form-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"form-data\";s:10:\"\0*\0version\";s:5:\"4.0.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/form-data\";}s:11:\"fraction.js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"fraction.js\";s:10:\"\0*\0version\";s:5:\"5.3.4\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/fraction.js\";}s:8:\"fsevents\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"fsevents\";s:10:\"\0*\0version\";s:5:\"2.3.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/fsevents\";}s:13:\"function-bind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"function-bind\";s:10:\"\0*\0version\";s:5:\"1.1.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/function-bind\";}s:15:\"get-caller-file\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"get-caller-file\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-caller-file\";}s:13:\"get-intrinsic\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"get-intrinsic\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-intrinsic\";}s:9:\"get-proto\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"get-proto\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-proto\";}s:11:\"glob-parent\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"glob-parent\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/glob-parent\";}s:4:\"gopd\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"gopd\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/gopd\";}s:8:\"has-flag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"has-flag\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-flag\";}s:11:\"has-symbols\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"has-symbols\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-symbols\";}s:15:\"has-tostringtag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"has-tostringtag\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-tostringtag\";}s:6:\"hasown\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"hasown\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/hasown\";}s:14:\"is-binary-path\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"is-binary-path\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-binary-path\";}s:14:\"is-core-module\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"is-core-module\";s:10:\"\0*\0version\";s:6:\"2.16.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-core-module\";}s:10:\"is-extglob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"is-extglob\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-extglob\";}s:23:\"is-fullwidth-code-point\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"is-fullwidth-code-point\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-fullwidth-code-point\";}s:7:\"is-glob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"is-glob\";s:10:\"\0*\0version\";s:5:\"4.0.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-glob\";}s:9:\"is-number\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"is-number\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-number\";}s:4:\"jiti\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"jiti\";s:10:\"\0*\0version\";s:6:\"1.21.7\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/jiti\";}s:19:\"laravel-vite-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"laravel-vite-plugin\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.3.0\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/laravel-vite-plugin\";}s:9:\"lilconfig\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"lilconfig\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/lilconfig\";}s:17:\"lines-and-columns\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"lines-and-columns\";s:10:\"\0*\0version\";s:5:\"1.2.4\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/lines-and-columns\";}s:15:\"math-intrinsics\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"math-intrinsics\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/math-intrinsics\";}s:6:\"merge2\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"merge2\";s:10:\"\0*\0version\";s:5:\"1.4.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/merge2\";}s:10:\"micromatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"micromatch\";s:10:\"\0*\0version\";s:5:\"4.0.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/micromatch\";}s:7:\"mime-db\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"mime-db\";s:10:\"\0*\0version\";s:6:\"1.52.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/mime-db\";}s:10:\"mime-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"mime-types\";s:10:\"\0*\0version\";s:6:\"2.1.35\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/mime-types\";}s:17:\"mini-svg-data-uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"mini-svg-data-uri\";s:10:\"\0*\0version\";s:5:\"1.4.4\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/mini-svg-data-uri\";}s:2:\"mz\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:2:\"mz\";s:10:\"\0*\0version\";s:5:\"2.7.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"/Users/alirazamarchal/Herd/nexus/node_modules/mz\";}s:6:\"nanoid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"nanoid\";s:10:\"\0*\0version\";s:6:\"3.3.11\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/nanoid\";}s:13:\"node-releases\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"node-releases\";s:10:\"\0*\0version\";s:6:\"2.0.27\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/node-releases\";}s:14:\"normalize-path\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"normalize-path\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/normalize-path\";}s:15:\"normalize-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"normalize-range\";s:10:\"\0*\0version\";s:5:\"0.1.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/normalize-range\";}s:13:\"object-assign\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"object-assign\";s:10:\"\0*\0version\";s:5:\"4.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/object-assign\";}s:11:\"object-hash\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"object-hash\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/object-hash\";}s:10:\"path-parse\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"path-parse\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/path-parse\";}s:10:\"picocolors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"picocolors\";s:10:\"\0*\0version\";s:5:\"1.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/picocolors\";}s:9:\"picomatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"picomatch\";s:10:\"\0*\0version\";s:5:\"2.3.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/picomatch\";}s:4:\"pify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"pify\";s:10:\"\0*\0version\";s:5:\"2.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/pify\";}s:7:\"pirates\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"pirates\";s:10:\"\0*\0version\";s:5:\"4.0.7\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/pirates\";}s:7:\"postcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"postcss\";s:10:\"\0*\0version\";s:5:\"8.5.6\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^8.5.28\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss\";}s:14:\"postcss-import\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"postcss-import\";s:10:\"\0*\0version\";s:6:\"15.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-import\";}s:10:\"postcss-js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"postcss-js\";s:10:\"\0*\0version\";s:5:\"4.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-js\";}s:19:\"postcss-load-config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"postcss-load-config\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-load-config\";}s:14:\"postcss-nested\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"postcss-nested\";s:10:\"\0*\0version\";s:5:\"6.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-nested\";}s:23:\"postcss-selector-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"postcss-selector-parser\";s:10:\"\0*\0version\";s:6:\"6.0.10\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-selector-parser\";}s:20:\"postcss-value-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"postcss-value-parser\";s:10:\"\0*\0version\";s:5:\"4.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-value-parser\";}s:14:\"proxy-from-env\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"proxy-from-env\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/proxy-from-env\";}s:15:\"queue-microtask\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"queue-microtask\";s:10:\"\0*\0version\";s:5:\"1.2.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/queue-microtask\";}s:10:\"read-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"read-cache\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/read-cache\";}s:8:\"readdirp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"readdirp\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/readdirp\";}s:17:\"require-directory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"require-directory\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/require-directory\";}s:7:\"resolve\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"resolve\";s:10:\"\0*\0version\";s:7:\"1.22.11\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/resolve\";}s:7:\"reusify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"reusify\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/reusify\";}s:6:\"rollup\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"rollup\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/rollup\";}s:12:\"run-parallel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"run-parallel\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/run-parallel\";}s:4:\"rxjs\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"rxjs\";s:10:\"\0*\0version\";s:5:\"7.8.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/rxjs\";}s:11:\"shell-quote\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"shell-quote\";s:10:\"\0*\0version\";s:5:\"1.8.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/shell-quote\";}s:13:\"source-map-js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"source-map-js\";s:10:\"\0*\0version\";s:5:\"1.2.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/source-map-js\";}s:12:\"string-width\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"string-width\";s:10:\"\0*\0version\";s:5:\"4.2.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/string-width\";}s:10:\"strip-ansi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"strip-ansi\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/strip-ansi\";}s:7:\"sucrase\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"sucrase\";s:10:\"\0*\0version\";s:6:\"3.35.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/sucrase\";}s:14:\"supports-color\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"supports-color\";s:10:\"\0*\0version\";s:5:\"8.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/supports-color\";}s:31:\"supports-preserve-symlinks-flag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"supports-preserve-symlinks-flag\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/supports-preserve-symlinks-flag\";}s:11:\"tailwindcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:6:\"3.4.18\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^3.4.19\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/tailwindcss\";}s:7:\"thenify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"thenify\";s:10:\"\0*\0version\";s:5:\"3.3.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/thenify\";}s:11:\"thenify-all\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"thenify-all\";s:10:\"\0*\0version\";s:5:\"1.6.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/thenify-all\";}s:10:\"tinyglobby\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"tinyglobby\";s:10:\"\0*\0version\";s:6:\"0.2.15\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/tinyglobby\";}s:14:\"to-regex-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"to-regex-range\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/to-regex-range\";}s:9:\"tree-kill\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"tree-kill\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/tree-kill\";}s:20:\"ts-interface-checker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"ts-interface-checker\";s:10:\"\0*\0version\";s:6:\"0.1.13\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/ts-interface-checker\";}s:5:\"tslib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"tslib\";s:10:\"\0*\0version\";s:5:\"2.8.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/tslib\";}s:22:\"update-browserslist-db\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"update-browserslist-db\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/update-browserslist-db\";}s:14:\"util-deprecate\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"util-deprecate\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/util-deprecate\";}s:4:\"vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"vite\";s:10:\"\0*\0version\";s:5:\"6.4.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^6.4.3\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/vite\";}s:23:\"vite-plugin-full-reload\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"vite-plugin-full-reload\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/vite-plugin-full-reload\";}s:9:\"wrap-ansi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"wrap-ansi\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/wrap-ansi\";}s:4:\"y18n\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"y18n\";s:10:\"\0*\0version\";s:5:\"5.0.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/y18n\";}s:5:\"yargs\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"yargs\";s:10:\"\0*\0version\";s:6:\"17.7.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/yargs\";}s:12:\"yargs-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"yargs-parser\";s:10:\"\0*\0version\";s:6:\"21.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/yargs-parser\";}s:4:\"fdir\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"fdir\";s:10:\"\0*\0version\";s:5:\"6.5.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/fdir\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:181:{i:0;r:1522;i:1;r:1530;i:2;r:1538;i:3;r:1546;i:4;r:1554;i:5;r:1562;i:6;r:1570;i:7;r:1578;i:8;r:1586;i:9;r:1594;i:10;r:1602;i:11;r:1610;i:12;r:1618;i:13;r:1626;i:14;r:1634;i:15;r:1642;i:16;r:1650;i:17;r:1658;i:18;r:1666;i:19;r:1674;i:20;r:1682;i:21;r:1690;i:22;r:1698;i:23;r:1706;i:24;r:1714;i:25;r:1722;i:26;r:1730;i:27;r:1738;i:28;r:1746;i:29;r:1754;i:30;r:1762;i:31;r:1770;i:32;r:1778;i:33;r:1786;i:34;r:1794;i:35;r:1802;i:36;r:1810;i:37;r:1818;i:38;r:1826;i:39;r:1834;i:40;r:1842;i:41;r:1850;i:42;r:1858;i:43;r:1866;i:44;r:1874;i:45;r:1882;i:46;r:1890;i:47;r:1898;i:48;r:1906;i:49;r:1914;i:50;r:1922;i:51;r:1930;i:52;r:1938;i:53;r:1946;i:54;r:1954;i:55;r:1962;i:56;r:1970;i:57;r:1978;i:58;r:1986;i:59;r:1994;i:60;r:2002;i:61;r:2010;i:62;r:2018;i:63;r:2026;i:64;r:2034;i:65;r:2042;i:66;r:2050;i:67;r:2058;i:68;r:2066;i:69;r:2074;i:70;r:2082;i:71;r:2090;i:72;r:2098;i:73;r:2106;i:74;r:2114;i:75;r:2122;i:76;r:2130;i:77;r:2138;i:78;r:2146;i:79;r:2154;i:80;r:2162;i:81;r:2170;i:82;r:2178;i:83;r:2186;i:84;r:2194;i:85;r:2202;i:86;r:2210;i:87;r:2218;i:88;r:2226;i:89;r:2234;i:90;r:2242;i:91;r:2250;i:92;r:2258;i:93;r:2266;i:94;r:2274;i:95;r:2282;i:96;r:2290;i:97;r:2298;i:98;r:2306;i:99;r:2314;i:100;r:2322;i:101;r:2330;i:102;r:2338;i:103;r:2346;i:104;r:2354;i:105;r:2362;i:106;r:2370;i:107;r:2378;i:108;r:2386;i:109;r:2394;i:110;r:2402;i:111;r:2410;i:112;r:2418;i:113;r:2426;i:114;r:2434;i:115;r:2442;i:116;r:2450;i:117;r:2458;i:118;r:2466;i:119;r:2474;i:120;r:2482;i:121;r:2490;i:122;r:2498;i:123;r:2506;i:124;r:2514;i:125;r:2522;i:126;r:2530;i:127;r:2538;i:128;r:2546;i:129;r:2554;i:130;r:2562;i:131;r:2570;i:132;r:2578;i:133;r:2586;i:134;r:2594;i:135;r:2602;i:136;r:2610;i:137;r:2618;i:138;r:2626;i:139;r:2634;i:140;r:2642;i:141;r:2650;i:142;r:2658;i:143;r:2666;i:144;r:2674;i:145;r:2682;i:146;r:2690;i:147;r:2698;i:148;r:2706;i:149;r:2714;i:150;r:2722;i:151;r:2730;i:152;r:2738;i:153;r:2746;i:154;r:2754;i:155;r:2762;i:156;r:2770;i:157;r:2778;i:158;r:2786;i:159;r:2794;i:160;r:2802;i:161;r:2810;i:162;r:2818;i:163;r:2826;i:164;r:2834;i:165;r:2842;i:166;r:2850;i:167;r:2858;i:168;r:2866;i:169;r:2874;i:170;r:2882;i:171;r:2890;i:172;r:2898;i:173;r:2906;i:174;r:2914;i:175;r:2922;i:176;r:2930;i:177;r:2938;i:178;r:2946;i:179;r:2954;i:180;r:2962;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:17:\"\0*\0packageManager\";E:41:\"Laravel\\Roster\\Enums\\JsPackageManager:Npm\";}s:6:\"stacks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:35:\"Laravel\\Roster\\Enums\\Stack:Livewire\";}}s:21:\"browserTestFrameworks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:9:\"frontends\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:6:\"agents\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:4:{i:0;E:37:\"Laravel\\Roster\\Enums\\Agent:ClaudeCode\";i:1;E:32:\"Laravel\\Roster\\Enums\\Agent:Codex\";i:2;E:34:\"Laravel\\Roster\\Enums\\Agent:Copilot\";i:3;E:33:\"Laravel\\Roster\\Enums\\Agent:Gemini\";}}s:7:\"editors\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:34:\"Laravel\\Roster\\Enums\\Editor:VsCode\";}}}', 1792762221);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('roster:project:v3:dc68cb51baa628baf69fe42fcb392f58', 'O:26:\"Laravel\\Roster\\ProjectScan\":8:{s:8:\"basePath\";s:33:\"/Users/alirazamarchal/Herd/nexus/\";s:3:\"php\";O:35:\"Laravel\\Roster\\Ecosystems\\Ecosystem\":2:{s:9:\"\0*\0byName\";a:168:{s:23:\"alimarchal/id-generator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"alimarchal/id-generator\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";E:43:\"Laravel\\Roster\\Enums\\PackageSource:Composer\";s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.1\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/alimarchal/id-generator\";}s:19:\"bacon/bacon-qr-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"bacon/bacon-qr-code\";s:10:\"\0*\0version\";s:5:\"3.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/bacon/bacon-qr-code\";}s:23:\"barryvdh/laravel-dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"barryvdh/laravel-dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/barryvdh/laravel-dompdf\";}s:10:\"brick/math\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"brick/math\";s:10:\"\0*\0version\";s:6:\"0.19.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/vendor/brick/math\";}s:31:\"carbonphp/carbon-doctrine-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"carbonphp/carbon-doctrine-types\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/vendor/carbonphp/carbon-doctrine-types\";}s:13:\"composer/pcre\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"composer/pcre\";s:10:\"\0*\0version\";s:5:\"3.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/pcre\";}s:15:\"composer/semver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"composer/semver\";s:10:\"\0*\0version\";s:5:\"3.4.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/semver\";}s:12:\"dasprid/enum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dasprid/enum\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/dasprid/enum\";}s:23:\"dflydev/dot-access-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"dflydev/dot-access-data\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/dflydev/dot-access-data\";}s:21:\"doctrine/deprecations\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"doctrine/deprecations\";s:10:\"\0*\0version\";s:5:\"1.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/deprecations\";}s:18:\"doctrine/inflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"doctrine/inflector\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/inflector\";}s:14:\"doctrine/lexer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"doctrine/lexer\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/lexer\";}s:13:\"dompdf/dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"dompdf/dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/dompdf\";}s:19:\"dompdf/php-font-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"dompdf/php-font-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/php-font-lib\";}s:18:\"dompdf/php-svg-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"dompdf/php-svg-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/php-svg-lib\";}s:29:\"dragonmantank/cron-expression\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"dragonmantank/cron-expression\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/dragonmantank/cron-expression\";}s:23:\"egulias/email-validator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"egulias/email-validator\";s:10:\"\0*\0version\";s:5:\"4.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/egulias/email-validator\";}s:18:\"fruitcake/php-cors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"fruitcake/php-cors\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/fruitcake/php-cors\";}s:27:\"graham-campbell/result-type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"graham-campbell/result-type\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/graham-campbell/result-type\";}s:17:\"guzzlehttp/guzzle\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"guzzlehttp/guzzle\";s:10:\"\0*\0version\";s:5:\"8.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/guzzle\";}s:19:\"guzzlehttp/promises\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"guzzlehttp/promises\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/promises\";}s:15:\"guzzlehttp/psr7\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"guzzlehttp/psr7\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/psr7\";}s:23:\"guzzlehttp/uri-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"guzzlehttp/uri-template\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/uri-template\";}s:15:\"laravel/fortify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/fortify\";s:10:\"\0*\0version\";s:6:\"1.39.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/fortify\";}s:17:\"laravel/framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"13.32.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^13.0\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/framework\";}s:17:\"laravel/jetstream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/jetstream\";s:10:\"\0*\0version\";s:5:\"5.5.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^5.3\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/jetstream\";}s:16:\"laravel/passkeys\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"laravel/passkeys\";s:10:\"\0*\0version\";s:5:\"0.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/passkeys\";}s:15:\"laravel/prompts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:6:\"0.3.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/prompts\";}s:15:\"laravel/sanctum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/sanctum\";s:10:\"\0*\0version\";s:5:\"4.3.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/sanctum\";}s:28:\"laravel/serializable-closure\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"laravel/serializable-closure\";s:10:\"\0*\0version\";s:6:\"2.0.16\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/serializable-closure\";}s:14:\"laravel/tinker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/tinker\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/tinker\";}s:17:\"league/commonmark\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"league/commonmark\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/league/commonmark\";}s:13:\"league/config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"league/config\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/league/config\";}s:16:\"league/flysystem\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"league/flysystem\";s:10:\"\0*\0version\";s:6:\"3.36.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/league/flysystem\";}s:22:\"league/flysystem-local\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"league/flysystem-local\";s:10:\"\0*\0version\";s:6:\"3.35.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/league/flysystem-local\";}s:26:\"league/mime-type-detection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"league/mime-type-detection\";s:10:\"\0*\0version\";s:6:\"1.17.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/league/mime-type-detection\";}s:10:\"league/uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"league/uri\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/vendor/league/uri\";}s:21:\"league/uri-interfaces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"league/uri-interfaces\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/league/uri-interfaces\";}s:17:\"livewire/livewire\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"livewire/livewire\";s:10:\"\0*\0version\";s:5:\"3.8.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/livewire/livewire\";}s:23:\"maennchen/zipstream-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"maennchen/zipstream-php\";s:10:\"\0*\0version\";s:5:\"3.2.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/maennchen/zipstream-php\";}s:17:\"markbaker/complex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"markbaker/complex\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/markbaker/complex\";}s:16:\"markbaker/matrix\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"markbaker/matrix\";s:10:\"\0*\0version\";s:5:\"3.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/markbaker/matrix\";}s:17:\"masterminds/html5\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"masterminds/html5\";s:10:\"\0*\0version\";s:6:\"2.11.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/masterminds/html5\";}s:28:\"mobiledetect/mobiledetectlib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"mobiledetect/mobiledetectlib\";s:10:\"\0*\0version\";s:6:\"4.11.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/mobiledetect/mobiledetectlib\";}s:15:\"monolog/monolog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"monolog/monolog\";s:10:\"\0*\0version\";s:6:\"3.12.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/monolog/monolog\";}s:13:\"nesbot/carbon\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"nesbot/carbon\";s:10:\"\0*\0version\";s:6:\"3.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/nesbot/carbon\";}s:12:\"nette/schema\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"nette/schema\";s:10:\"\0*\0version\";s:5:\"1.3.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/nette/schema\";}s:11:\"nette/utils\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"nette/utils\";s:10:\"\0*\0version\";s:5:\"4.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/nette/utils\";}s:16:\"nikic/php-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"nikic/php-parser\";s:10:\"\0*\0version\";s:5:\"5.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/nikic/php-parser\";}s:19:\"nunomaduro/termwind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"nunomaduro/termwind\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/nunomaduro/termwind\";}s:32:\"paragonie/constant_time_encoding\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"paragonie/constant_time_encoding\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/vendor/paragonie/constant_time_encoding\";}s:31:\"phpdocumentor/reflection-common\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"phpdocumentor/reflection-common\";s:10:\"\0*\0version\";s:5:\"2.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/reflection-common\";}s:33:\"phpdocumentor/reflection-docblock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"phpdocumentor/reflection-docblock\";s:10:\"\0*\0version\";s:5:\"6.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/reflection-docblock\";}s:27:\"phpdocumentor/type-resolver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"phpdocumentor/type-resolver\";s:10:\"\0*\0version\";s:5:\"2.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/type-resolver\";}s:24:\"phpoffice/phpspreadsheet\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"phpoffice/phpspreadsheet\";s:10:\"\0*\0version\";s:6:\"5.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^5.7\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/phpoffice/phpspreadsheet\";}s:19:\"phpoption/phpoption\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpoption/phpoption\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/phpoption/phpoption\";}s:21:\"phpstan/phpdoc-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"phpstan/phpdoc-parser\";s:10:\"\0*\0version\";s:5:\"2.3.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/phpstan/phpdoc-parser\";}s:18:\"pragmarx/google2fa\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"pragmarx/google2fa\";s:10:\"\0*\0version\";s:5:\"9.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/pragmarx/google2fa\";}s:9:\"psr/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psr/clock\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/clock\";}s:13:\"psr/container\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"psr/container\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/container\";}s:20:\"psr/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"psr/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/event-dispatcher\";}s:15:\"psr/http-client\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"psr/http-client\";s:10:\"\0*\0version\";s:5:\"1.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-client\";}s:16:\"psr/http-factory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-factory\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-factory\";}s:16:\"psr/http-message\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-message\";s:10:\"\0*\0version\";s:3:\"2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-message\";}s:7:\"psr/log\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"psr/log\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/log\";}s:16:\"psr/simple-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/simple-cache\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/simple-cache\";}s:9:\"psy/psysh\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psy/psysh\";s:10:\"\0*\0version\";s:7:\"0.12.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/vendor/psy/psysh\";}s:17:\"ramsey/collection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"ramsey/collection\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/ramsey/collection\";}s:11:\"ramsey/uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ramsey/uuid\";s:10:\"\0*\0version\";s:5:\"4.9.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/ramsey/uuid\";}s:25:\"sabberworm/php-css-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"sabberworm/php-css-parser\";s:10:\"\0*\0version\";s:5:\"9.5.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/sabberworm/php-css-parser\";}s:12:\"spatie/image\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"spatie/image\";s:10:\"\0*\0version\";s:5:\"3.9.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/image\";}s:22:\"spatie/image-optimizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"spatie/image-optimizer\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/image-optimizer\";}s:26:\"spatie/laravel-activitylog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"spatie/laravel-activitylog\";s:10:\"\0*\0version\";s:6:\"4.12.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.9\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-activitylog\";}s:27:\"spatie/laravel-medialibrary\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"spatie/laravel-medialibrary\";s:10:\"\0*\0version\";s:7:\"11.23.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^11.23\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-medialibrary\";}s:28:\"spatie/laravel-package-tools\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-package-tools\";s:10:\"\0*\0version\";s:6:\"1.93.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-package-tools\";}s:25:\"spatie/laravel-permission\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spatie/laravel-permission\";s:10:\"\0*\0version\";s:6:\"6.25.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^6.10\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-permission\";}s:28:\"spatie/laravel-query-builder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-query-builder\";s:10:\"\0*\0version\";s:5:\"6.4.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^6.3\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-query-builder\";}s:26:\"spatie/temporary-directory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"spatie/temporary-directory\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/temporary-directory\";}s:20:\"spomky-labs/cbor-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"spomky-labs/cbor-php\";s:10:\"\0*\0version\";s:5:\"3.4.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/spomky-labs/cbor-php\";}s:25:\"spomky-labs/pki-framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spomky-labs/pki-framework\";s:10:\"\0*\0version\";s:5:\"1.6.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/spomky-labs/pki-framework\";}s:13:\"symfony/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"symfony/clock\";s:10:\"\0*\0version\";s:5:\"8.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/clock\";}s:15:\"symfony/console\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/console\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/console\";}s:20:\"symfony/css-selector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"symfony/css-selector\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/css-selector\";}s:29:\"symfony/deprecation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/deprecation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/deprecation-contracts\";}s:21:\"symfony/error-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/error-handler\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/error-handler\";}s:24:\"symfony/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"symfony/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/event-dispatcher\";}s:34:\"symfony/event-dispatcher-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"symfony/event-dispatcher-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/event-dispatcher-contracts\";}s:14:\"symfony/finder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/finder\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/finder\";}s:23:\"symfony/http-foundation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/http-foundation\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/http-foundation\";}s:19:\"symfony/http-kernel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/http-kernel\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/http-kernel\";}s:14:\"symfony/mailer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/mailer\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/mailer\";}s:12:\"symfony/mime\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/mime\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/mime\";}s:22:\"symfony/polyfill-ctype\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-ctype\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-ctype\";}s:30:\"symfony/polyfill-intl-grapheme\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"symfony/polyfill-intl-grapheme\";s:10:\"\0*\0version\";s:6:\"1.41.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-grapheme\";}s:25:\"symfony/polyfill-intl-idn\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-intl-idn\";s:10:\"\0*\0version\";s:6:\"1.42.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-idn\";}s:32:\"symfony/polyfill-intl-normalizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"symfony/polyfill-intl-normalizer\";s:10:\"\0*\0version\";s:6:\"1.42.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-normalizer\";}s:25:\"symfony/polyfill-mbstring\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-mbstring\";s:10:\"\0*\0version\";s:6:\"1.38.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-mbstring\";}s:22:\"symfony/polyfill-php80\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php80\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php80\";}s:22:\"symfony/polyfill-php81\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php81\";s:10:\"\0*\0version\";s:6:\"1.38.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php81\";}s:22:\"symfony/polyfill-php82\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php82\";s:10:\"\0*\0version\";s:6:\"1.38.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php82\";}s:22:\"symfony/polyfill-php84\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php84\";s:10:\"\0*\0version\";s:6:\"1.38.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php84\";}s:22:\"symfony/polyfill-php85\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php85\";s:10:\"\0*\0version\";s:6:\"1.41.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php85\";}s:22:\"symfony/polyfill-php86\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php86\";s:10:\"\0*\0version\";s:6:\"1.41.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php86\";}s:21:\"symfony/polyfill-uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-uuid\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-uuid\";}s:15:\"symfony/process\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/process\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/process\";}s:23:\"symfony/property-access\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/property-access\";s:10:\"\0*\0version\";s:5:\"8.1.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/property-access\";}s:21:\"symfony/property-info\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/property-info\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/property-info\";}s:15:\"symfony/routing\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/routing\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/routing\";}s:18:\"symfony/serializer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/serializer\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/serializer\";}s:25:\"symfony/service-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/service-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/service-contracts\";}s:14:\"symfony/string\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/string\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/string\";}s:19:\"symfony/translation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/translation\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/translation\";}s:29:\"symfony/translation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/translation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/translation-contracts\";}s:17:\"symfony/type-info\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"symfony/type-info\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/type-info\";}s:11:\"symfony/uid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"symfony/uid\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/uid\";}s:18:\"symfony/var-dumper\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/var-dumper\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/var-dumper\";}s:33:\"tijsverkoyen/css-to-inline-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"tijsverkoyen/css-to-inline-styles\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/vendor/tijsverkoyen/css-to-inline-styles\";}s:16:\"vlucas/phpdotenv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"vlucas/phpdotenv\";s:10:\"\0*\0version\";s:5:\"5.7.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/vlucas/phpdotenv\";}s:19:\"voku/portable-ascii\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"voku/portable-ascii\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/voku/portable-ascii\";}s:17:\"web-auth/cose-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"web-auth/cose-lib\";s:10:\"\0*\0version\";s:5:\"4.8.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/web-auth/cose-lib\";}s:21:\"web-auth/webauthn-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"web-auth/webauthn-lib\";s:10:\"\0*\0version\";s:5:\"5.3.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/web-auth/webauthn-lib\";}s:16:\"webmozart/assert\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"webmozart/assert\";s:10:\"\0*\0version\";s:5:\"2.4.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/webmozart/assert\";}s:17:\"brianium/paratest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"brianium/paratest\";s:10:\"\0*\0version\";s:6:\"7.20.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/brianium/paratest\";}s:23:\"composer/xdebug-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"composer/xdebug-handler\";s:10:\"\0*\0version\";s:5:\"3.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/xdebug-handler\";}s:14:\"fakerphp/faker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"fakerphp/faker\";s:10:\"\0*\0version\";s:6:\"1.24.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.23\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/fakerphp/faker\";}s:22:\"fidry/cpu-core-counter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"fidry/cpu-core-counter\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/fidry/cpu-core-counter\";}s:11:\"filp/whoops\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"filp/whoops\";s:10:\"\0*\0version\";s:6:\"2.18.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/filp/whoops\";}s:21:\"hamcrest/hamcrest-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"hamcrest/hamcrest-php\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/hamcrest/hamcrest-php\";}s:30:\"jean85/pretty-package-versions\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"jean85/pretty-package-versions\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/vendor/jean85/pretty-package-versions\";}s:13:\"laravel/boost\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"laravel/boost\";s:10:\"\0*\0version\";s:5:\"2.9.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^2.0\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/boost\";}s:11:\"laravel/mcp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/mcp\";}s:12:\"laravel/pail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pail\";s:10:\"\0*\0version\";s:5:\"1.2.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.1\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/pail\";}s:12:\"laravel/pint\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.32.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.13\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/pint\";}s:14:\"laravel/roster\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/roster\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/roster\";}s:12:\"laravel/sail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/sail\";s:10:\"\0*\0version\";s:6:\"1.67.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.26\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/sail\";}s:15:\"mockery/mockery\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"mockery/mockery\";s:10:\"\0*\0version\";s:6:\"1.6.15\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.6\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/mockery/mockery\";}s:17:\"myclabs/deep-copy\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"myclabs/deep-copy\";s:10:\"\0*\0version\";s:6:\"1.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/myclabs/deep-copy\";}s:20:\"nunomaduro/collision\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"nunomaduro/collision\";s:10:\"\0*\0version\";s:5:\"8.9.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.1\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/nunomaduro/collision\";}s:12:\"pestphp/pest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"pestphp/pest\";s:10:\"\0*\0version\";s:5:\"4.7.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest\";}s:19:\"pestphp/pest-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"pestphp/pest-plugin\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin\";}s:24:\"pestphp/pest-plugin-arch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"pestphp/pest-plugin-arch\";s:10:\"\0*\0version\";s:5:\"4.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-arch\";}s:26:\"pestphp/pest-plugin-mutate\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"pestphp/pest-plugin-mutate\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-mutate\";}s:29:\"pestphp/pest-plugin-profanity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"pestphp/pest-plugin-profanity\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-profanity\";}s:16:\"phar-io/manifest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"phar-io/manifest\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/phar-io/manifest\";}s:15:\"phar-io/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phar-io/version\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/phar-io/version\";}s:25:\"phpunit/php-code-coverage\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-code-coverage\";s:10:\"\0*\0version\";s:6:\"12.5.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-code-coverage\";}s:25:\"phpunit/php-file-iterator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-file-iterator\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-file-iterator\";}s:19:\"phpunit/php-invoker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpunit/php-invoker\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-invoker\";}s:25:\"phpunit/php-text-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-text-template\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-text-template\";}s:17:\"phpunit/php-timer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"phpunit/php-timer\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-timer\";}s:15:\"phpunit/phpunit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:7:\"12.5.33\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^12.0\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/phpunit\";}s:20:\"sebastian/cli-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/cli-parser\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/cli-parser\";}s:20:\"sebastian/comparator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/comparator\";s:10:\"\0*\0version\";s:5:\"7.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/comparator\";}s:20:\"sebastian/complexity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/complexity\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/complexity\";}s:14:\"sebastian/diff\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/diff\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/diff\";}s:21:\"sebastian/environment\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"sebastian/environment\";s:10:\"\0*\0version\";s:5:\"8.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/environment\";}s:18:\"sebastian/exporter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"sebastian/exporter\";s:10:\"\0*\0version\";s:5:\"7.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/exporter\";}s:22:\"sebastian/global-state\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"sebastian/global-state\";s:10:\"\0*\0version\";s:5:\"8.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/global-state\";}s:23:\"sebastian/lines-of-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"sebastian/lines-of-code\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/lines-of-code\";}s:27:\"sebastian/object-enumerator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/object-enumerator\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/object-enumerator\";}s:26:\"sebastian/object-reflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"sebastian/object-reflector\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/object-reflector\";}s:27:\"sebastian/recursion-context\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/recursion-context\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/recursion-context\";}s:14:\"sebastian/type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/type\";s:10:\"\0*\0version\";s:5:\"6.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/type\";}s:17:\"sebastian/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"sebastian/version\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/version\";}s:28:\"staabm/side-effects-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"staabm/side-effects-detector\";s:10:\"\0*\0version\";s:5:\"1.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/staabm/side-effects-detector\";}s:12:\"symfony/yaml\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/yaml\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/yaml\";}s:35:\"ta-tikoma/phpunit-architecture-test\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:35:\"ta-tikoma/phpunit-architecture-test\";s:10:\"\0*\0version\";s:5:\"0.8.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/vendor/ta-tikoma/phpunit-architecture-test\";}s:17:\"theseer/tokenizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"theseer/tokenizer\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/theseer/tokenizer\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:168:{i:0;r:5;i:1;r:13;i:2;r:21;i:3;r:29;i:4;r:37;i:5;r:45;i:6;r:53;i:7;r:61;i:8;r:69;i:9;r:77;i:10;r:85;i:11;r:93;i:12;r:101;i:13;r:109;i:14;r:117;i:15;r:125;i:16;r:133;i:17;r:141;i:18;r:149;i:19;r:157;i:20;r:165;i:21;r:173;i:22;r:181;i:23;r:189;i:24;r:197;i:25;r:205;i:26;r:213;i:27;r:221;i:28;r:229;i:29;r:237;i:30;r:245;i:31;r:253;i:32;r:261;i:33;r:269;i:34;r:277;i:35;r:285;i:36;r:293;i:37;r:301;i:38;r:309;i:39;r:317;i:40;r:325;i:41;r:333;i:42;r:341;i:43;r:349;i:44;r:357;i:45;r:365;i:46;r:373;i:47;r:381;i:48;r:389;i:49;r:397;i:50;r:405;i:51;r:413;i:52;r:421;i:53;r:429;i:54;r:437;i:55;r:445;i:56;r:453;i:57;r:461;i:58;r:469;i:59;r:477;i:60;r:485;i:61;r:493;i:62;r:501;i:63;r:509;i:64;r:517;i:65;r:525;i:66;r:533;i:67;r:541;i:68;r:549;i:69;r:557;i:70;r:565;i:71;r:573;i:72;r:581;i:73;r:589;i:74;r:597;i:75;r:605;i:76;r:613;i:77;r:621;i:78;r:629;i:79;r:637;i:80;r:645;i:81;r:653;i:82;r:661;i:83;r:669;i:84;r:677;i:85;r:685;i:86;r:693;i:87;r:701;i:88;r:709;i:89;r:717;i:90;r:725;i:91;r:733;i:92;r:741;i:93;r:749;i:94;r:757;i:95;r:765;i:96;r:773;i:97;r:781;i:98;r:789;i:99;r:797;i:100;r:805;i:101;r:813;i:102;r:821;i:103;r:829;i:104;r:837;i:105;r:845;i:106;r:853;i:107;r:861;i:108;r:869;i:109;r:877;i:110;r:885;i:111;r:893;i:112;r:901;i:113;r:909;i:114;r:917;i:115;r:925;i:116;r:933;i:117;r:941;i:118;r:949;i:119;r:957;i:120;r:965;i:121;r:973;i:122;r:981;i:123;r:989;i:124;r:997;i:125;r:1005;i:126;r:1013;i:127;r:1021;i:128;r:1029;i:129;r:1037;i:130;r:1045;i:131;r:1053;i:132;r:1061;i:133;r:1069;i:134;r:1077;i:135;r:1085;i:136;r:1093;i:137;r:1101;i:138;r:1109;i:139;r:1117;i:140;r:1125;i:141;r:1133;i:142;r:1141;i:143;r:1149;i:144;r:1157;i:145;r:1165;i:146;r:1173;i:147;r:1181;i:148;r:1189;i:149;r:1197;i:150;r:1205;i:151;r:1213;i:152;r:1221;i:153;r:1229;i:154;r:1237;i:155;r:1245;i:156;r:1253;i:157;r:1261;i:158;r:1269;i:159;r:1277;i:160;r:1285;i:161;r:1293;i:162;r:1301;i:163;r:1309;i:164;r:1317;i:165;r:1325;i:166;r:1333;i:167;r:1341;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:2:\"js\";O:37:\"Laravel\\Roster\\Ecosystems\\JsEcosystem\":3:{s:9:\"\0*\0byName\";a:181:{s:16:\"@alloc/quick-lru\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@alloc/quick-lru\";s:10:\"\0*\0version\";s:5:\"5.2.0\";s:9:\"\0*\0source\";E:38:\"Laravel\\Roster\\Enums\\PackageSource:Npm\";s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@alloc/quick-lru\";}s:18:\"@esbuild/aix-ppc64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/aix-ppc64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/aix-ppc64\";}s:20:\"@esbuild/android-arm\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/android-arm\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-arm\";}s:22:\"@esbuild/android-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/android-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-arm64\";}s:20:\"@esbuild/android-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/android-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-x64\";}s:21:\"@esbuild/darwin-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"@esbuild/darwin-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/darwin-arm64\";}s:19:\"@esbuild/darwin-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/darwin-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/darwin-x64\";}s:22:\"@esbuild/freebsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/freebsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/freebsd-arm64\";}s:20:\"@esbuild/freebsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/freebsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/freebsd-x64\";}s:18:\"@esbuild/linux-arm\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/linux-arm\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-arm\";}s:20:\"@esbuild/linux-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-arm64\";}s:19:\"@esbuild/linux-ia32\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/linux-ia32\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-ia32\";}s:22:\"@esbuild/linux-loong64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/linux-loong64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-loong64\";}s:23:\"@esbuild/linux-mips64el\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@esbuild/linux-mips64el\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-mips64el\";}s:20:\"@esbuild/linux-ppc64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-ppc64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-ppc64\";}s:22:\"@esbuild/linux-riscv64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/linux-riscv64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-riscv64\";}s:20:\"@esbuild/linux-s390x\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-s390x\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-s390x\";}s:18:\"@esbuild/linux-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/linux-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-x64\";}s:21:\"@esbuild/netbsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"@esbuild/netbsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/netbsd-arm64\";}s:19:\"@esbuild/netbsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/netbsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/netbsd-x64\";}s:22:\"@esbuild/openbsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/openbsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openbsd-arm64\";}s:20:\"@esbuild/openbsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/openbsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openbsd-x64\";}s:26:\"@esbuild/openharmony-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"@esbuild/openharmony-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openharmony-arm64\";}s:18:\"@esbuild/sunos-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/sunos-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/sunos-x64\";}s:20:\"@esbuild/win32-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/win32-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-arm64\";}s:19:\"@esbuild/win32-ia32\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/win32-ia32\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-ia32\";}s:18:\"@esbuild/win32-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/win32-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-x64\";}s:23:\"@jridgewell/gen-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@jridgewell/gen-mapping\";s:10:\"\0*\0version\";s:6:\"0.3.13\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/gen-mapping\";}s:23:\"@jridgewell/resolve-uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@jridgewell/resolve-uri\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/resolve-uri\";}s:27:\"@jridgewell/sourcemap-codec\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"@jridgewell/sourcemap-codec\";s:10:\"\0*\0version\";s:5:\"1.5.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/sourcemap-codec\";}s:25:\"@jridgewell/trace-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"@jridgewell/trace-mapping\";s:10:\"\0*\0version\";s:6:\"0.3.31\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/trace-mapping\";}s:19:\"@nodelib/fs.scandir\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@nodelib/fs.scandir\";s:10:\"\0*\0version\";s:5:\"2.1.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.scandir\";}s:16:\"@nodelib/fs.stat\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@nodelib/fs.stat\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.stat\";}s:16:\"@nodelib/fs.walk\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@nodelib/fs.walk\";s:10:\"\0*\0version\";s:5:\"1.2.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.walk\";}s:31:\"@rollup/rollup-android-arm-eabi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-android-arm-eabi\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-android-arm-eabi\";}s:28:\"@rollup/rollup-android-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-android-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-android-arm64\";}s:27:\"@rollup/rollup-darwin-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"@rollup/rollup-darwin-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-darwin-arm64\";}s:25:\"@rollup/rollup-darwin-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"@rollup/rollup-darwin-x64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-darwin-x64\";}s:28:\"@rollup/rollup-freebsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-freebsd-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-freebsd-arm64\";}s:26:\"@rollup/rollup-freebsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"@rollup/rollup-freebsd-x64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-freebsd-x64\";}s:34:\"@rollup/rollup-linux-arm-gnueabihf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"@rollup/rollup-linux-arm-gnueabihf\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:80:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm-gnueabihf\";}s:35:\"@rollup/rollup-linux-arm-musleabihf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:35:\"@rollup/rollup-linux-arm-musleabihf\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:81:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm-musleabihf\";}s:30:\"@rollup/rollup-linux-arm64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-arm64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm64-gnu\";}s:31:\"@rollup/rollup-linux-arm64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-linux-arm64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm64-musl\";}s:32:\"@rollup/rollup-linux-loong64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-linux-loong64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-loong64-gnu\";}s:30:\"@rollup/rollup-linux-ppc64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-ppc64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-ppc64-gnu\";}s:32:\"@rollup/rollup-linux-riscv64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-linux-riscv64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-riscv64-gnu\";}s:33:\"@rollup/rollup-linux-riscv64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"@rollup/rollup-linux-riscv64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:79:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-riscv64-musl\";}s:30:\"@rollup/rollup-linux-s390x-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-s390x-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-s390x-gnu\";}s:28:\"@rollup/rollup-linux-x64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-linux-x64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-x64-gnu\";}s:29:\"@rollup/rollup-linux-x64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"@rollup/rollup-linux-x64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-x64-musl\";}s:32:\"@rollup/rollup-openharmony-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-openharmony-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-openharmony-arm64\";}s:31:\"@rollup/rollup-win32-arm64-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-win32-arm64-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-arm64-msvc\";}s:30:\"@rollup/rollup-win32-ia32-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-win32-ia32-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-ia32-msvc\";}s:28:\"@rollup/rollup-win32-x64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-win32-x64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-x64-gnu\";}s:29:\"@rollup/rollup-win32-x64-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"@rollup/rollup-win32-x64-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-x64-msvc\";}s:18:\"@tailwindcss/forms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@tailwindcss/forms\";s:10:\"\0*\0version\";s:6:\"0.5.10\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^0.5.11\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@tailwindcss/forms\";}s:23:\"@tailwindcss/typography\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@tailwindcss/typography\";s:10:\"\0*\0version\";s:6:\"0.5.19\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^0.5.20\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@tailwindcss/typography\";}s:13:\"@types/estree\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"@types/estree\";s:10:\"\0*\0version\";s:5:\"1.0.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/@types/estree\";}s:10:\"ansi-regex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"ansi-regex\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/ansi-regex\";}s:11:\"ansi-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ansi-styles\";s:10:\"\0*\0version\";s:5:\"4.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/ansi-styles\";}s:11:\"any-promise\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"any-promise\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/any-promise\";}s:8:\"anymatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"anymatch\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/anymatch\";}s:3:\"arg\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:3:\"arg\";s:10:\"\0*\0version\";s:5:\"5.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/node_modules/arg\";}s:8:\"asynckit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"asynckit\";s:10:\"\0*\0version\";s:5:\"0.4.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/asynckit\";}s:12:\"autoprefixer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"autoprefixer\";s:10:\"\0*\0version\";s:7:\"10.4.22\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^10.6.1\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/autoprefixer\";}s:5:\"axios\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"axios\";s:10:\"\0*\0version\";s:6:\"1.13.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^1.20.0\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/axios\";}s:24:\"baseline-browser-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"baseline-browser-mapping\";s:10:\"\0*\0version\";s:5:\"2.9.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/node_modules/baseline-browser-mapping\";}s:17:\"binary-extensions\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"binary-extensions\";s:10:\"\0*\0version\";s:5:\"2.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/binary-extensions\";}s:6:\"braces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"braces\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/braces\";}s:12:\"browserslist\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"browserslist\";s:10:\"\0*\0version\";s:6:\"4.28.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/browserslist\";}s:23:\"call-bind-apply-helpers\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"call-bind-apply-helpers\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/call-bind-apply-helpers\";}s:13:\"camelcase-css\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"camelcase-css\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/camelcase-css\";}s:12:\"caniuse-lite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"caniuse-lite\";s:10:\"\0*\0version\";s:12:\"1.0.30001760\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/caniuse-lite\";}s:5:\"chalk\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"chalk\";s:10:\"\0*\0version\";s:5:\"4.1.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/chalk\";}s:8:\"chokidar\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"chokidar\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/chokidar\";}s:5:\"cliui\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"cliui\";s:10:\"\0*\0version\";s:5:\"8.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/cliui\";}s:13:\"color-convert\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"color-convert\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/color-convert\";}s:10:\"color-name\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"color-name\";s:10:\"\0*\0version\";s:5:\"1.1.4\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/color-name\";}s:15:\"combined-stream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"combined-stream\";s:10:\"\0*\0version\";s:5:\"1.0.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/combined-stream\";}s:9:\"commander\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"commander\";s:10:\"\0*\0version\";s:5:\"4.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/commander\";}s:12:\"concurrently\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"concurrently\";s:10:\"\0*\0version\";s:5:\"9.2.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^9.2.4\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/concurrently\";}s:6:\"cssesc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"cssesc\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/cssesc\";}s:14:\"delayed-stream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"delayed-stream\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/delayed-stream\";}s:10:\"didyoumean\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"didyoumean\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/didyoumean\";}s:3:\"dlv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:3:\"dlv\";s:10:\"\0*\0version\";s:5:\"1.1.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/node_modules/dlv\";}s:12:\"dunder-proto\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dunder-proto\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/dunder-proto\";}s:20:\"electron-to-chromium\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"electron-to-chromium\";s:10:\"\0*\0version\";s:7:\"1.5.267\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/electron-to-chromium\";}s:11:\"emoji-regex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"emoji-regex\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/emoji-regex\";}s:18:\"es-define-property\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"es-define-property\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-define-property\";}s:9:\"es-errors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"es-errors\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-errors\";}s:15:\"es-object-atoms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"es-object-atoms\";s:10:\"\0*\0version\";s:5:\"1.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-object-atoms\";}s:18:\"es-set-tostringtag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"es-set-tostringtag\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-set-tostringtag\";}s:7:\"esbuild\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"esbuild\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/esbuild\";}s:8:\"escalade\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"escalade\";s:10:\"\0*\0version\";s:5:\"3.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/escalade\";}s:9:\"fast-glob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"fast-glob\";s:10:\"\0*\0version\";s:5:\"3.3.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/fast-glob\";}s:5:\"fastq\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"fastq\";s:10:\"\0*\0version\";s:6:\"1.19.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/fastq\";}s:10:\"fill-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"fill-range\";s:10:\"\0*\0version\";s:5:\"7.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/fill-range\";}s:16:\"follow-redirects\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"follow-redirects\";s:10:\"\0*\0version\";s:7:\"1.15.11\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/follow-redirects\";}s:9:\"form-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"form-data\";s:10:\"\0*\0version\";s:5:\"4.0.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/form-data\";}s:11:\"fraction.js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"fraction.js\";s:10:\"\0*\0version\";s:5:\"5.3.4\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/fraction.js\";}s:8:\"fsevents\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"fsevents\";s:10:\"\0*\0version\";s:5:\"2.3.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/fsevents\";}s:13:\"function-bind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"function-bind\";s:10:\"\0*\0version\";s:5:\"1.1.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/function-bind\";}s:15:\"get-caller-file\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"get-caller-file\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-caller-file\";}s:13:\"get-intrinsic\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"get-intrinsic\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-intrinsic\";}s:9:\"get-proto\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"get-proto\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-proto\";}s:11:\"glob-parent\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"glob-parent\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/glob-parent\";}s:4:\"gopd\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"gopd\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/gopd\";}s:8:\"has-flag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"has-flag\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-flag\";}s:11:\"has-symbols\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"has-symbols\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-symbols\";}s:15:\"has-tostringtag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"has-tostringtag\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-tostringtag\";}s:6:\"hasown\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"hasown\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/hasown\";}s:14:\"is-binary-path\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"is-binary-path\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-binary-path\";}s:14:\"is-core-module\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"is-core-module\";s:10:\"\0*\0version\";s:6:\"2.16.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-core-module\";}s:10:\"is-extglob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"is-extglob\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-extglob\";}s:23:\"is-fullwidth-code-point\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"is-fullwidth-code-point\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-fullwidth-code-point\";}s:7:\"is-glob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"is-glob\";s:10:\"\0*\0version\";s:5:\"4.0.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-glob\";}s:9:\"is-number\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"is-number\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-number\";}s:4:\"jiti\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"jiti\";s:10:\"\0*\0version\";s:6:\"1.21.7\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/jiti\";}s:19:\"laravel-vite-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"laravel-vite-plugin\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.3.0\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/laravel-vite-plugin\";}s:9:\"lilconfig\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"lilconfig\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/lilconfig\";}s:17:\"lines-and-columns\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"lines-and-columns\";s:10:\"\0*\0version\";s:5:\"1.2.4\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/lines-and-columns\";}s:15:\"math-intrinsics\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"math-intrinsics\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/math-intrinsics\";}s:6:\"merge2\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"merge2\";s:10:\"\0*\0version\";s:5:\"1.4.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/merge2\";}s:10:\"micromatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"micromatch\";s:10:\"\0*\0version\";s:5:\"4.0.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/micromatch\";}s:7:\"mime-db\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"mime-db\";s:10:\"\0*\0version\";s:6:\"1.52.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/mime-db\";}s:10:\"mime-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"mime-types\";s:10:\"\0*\0version\";s:6:\"2.1.35\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/mime-types\";}s:17:\"mini-svg-data-uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"mini-svg-data-uri\";s:10:\"\0*\0version\";s:5:\"1.4.4\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/mini-svg-data-uri\";}s:2:\"mz\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:2:\"mz\";s:10:\"\0*\0version\";s:5:\"2.7.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"/Users/alirazamarchal/Herd/nexus/node_modules/mz\";}s:6:\"nanoid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"nanoid\";s:10:\"\0*\0version\";s:6:\"3.3.11\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/nanoid\";}s:13:\"node-releases\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"node-releases\";s:10:\"\0*\0version\";s:6:\"2.0.27\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/node-releases\";}s:14:\"normalize-path\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"normalize-path\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/normalize-path\";}s:15:\"normalize-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"normalize-range\";s:10:\"\0*\0version\";s:5:\"0.1.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/normalize-range\";}s:13:\"object-assign\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"object-assign\";s:10:\"\0*\0version\";s:5:\"4.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/object-assign\";}s:11:\"object-hash\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"object-hash\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/object-hash\";}s:10:\"path-parse\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"path-parse\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/path-parse\";}s:10:\"picocolors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"picocolors\";s:10:\"\0*\0version\";s:5:\"1.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/picocolors\";}s:9:\"picomatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"picomatch\";s:10:\"\0*\0version\";s:5:\"2.3.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/picomatch\";}s:4:\"pify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"pify\";s:10:\"\0*\0version\";s:5:\"2.3.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/pify\";}s:7:\"pirates\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"pirates\";s:10:\"\0*\0version\";s:5:\"4.0.7\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/pirates\";}s:7:\"postcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"postcss\";s:10:\"\0*\0version\";s:5:\"8.5.6\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^8.5.28\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss\";}s:14:\"postcss-import\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"postcss-import\";s:10:\"\0*\0version\";s:6:\"15.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-import\";}s:10:\"postcss-js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"postcss-js\";s:10:\"\0*\0version\";s:5:\"4.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-js\";}s:19:\"postcss-load-config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"postcss-load-config\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-load-config\";}s:14:\"postcss-nested\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"postcss-nested\";s:10:\"\0*\0version\";s:5:\"6.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-nested\";}s:23:\"postcss-selector-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"postcss-selector-parser\";s:10:\"\0*\0version\";s:6:\"6.0.10\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-selector-parser\";}s:20:\"postcss-value-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"postcss-value-parser\";s:10:\"\0*\0version\";s:5:\"4.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-value-parser\";}s:14:\"proxy-from-env\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"proxy-from-env\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/proxy-from-env\";}s:15:\"queue-microtask\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"queue-microtask\";s:10:\"\0*\0version\";s:5:\"1.2.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/queue-microtask\";}s:10:\"read-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"read-cache\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/read-cache\";}s:8:\"readdirp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"readdirp\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/readdirp\";}s:17:\"require-directory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"require-directory\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/require-directory\";}s:7:\"resolve\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"resolve\";s:10:\"\0*\0version\";s:7:\"1.22.11\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/resolve\";}s:7:\"reusify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"reusify\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/reusify\";}s:6:\"rollup\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"rollup\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/rollup\";}s:12:\"run-parallel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"run-parallel\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/run-parallel\";}s:4:\"rxjs\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"rxjs\";s:10:\"\0*\0version\";s:5:\"7.8.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/rxjs\";}s:11:\"shell-quote\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"shell-quote\";s:10:\"\0*\0version\";s:5:\"1.8.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/shell-quote\";}s:13:\"source-map-js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"source-map-js\";s:10:\"\0*\0version\";s:5:\"1.2.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/source-map-js\";}s:12:\"string-width\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"string-width\";s:10:\"\0*\0version\";s:5:\"4.2.3\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/string-width\";}s:10:\"strip-ansi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"strip-ansi\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/strip-ansi\";}s:7:\"sucrase\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"sucrase\";s:10:\"\0*\0version\";s:6:\"3.35.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/sucrase\";}s:14:\"supports-color\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"supports-color\";s:10:\"\0*\0version\";s:5:\"8.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/supports-color\";}s:31:\"supports-preserve-symlinks-flag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"supports-preserve-symlinks-flag\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/supports-preserve-symlinks-flag\";}s:11:\"tailwindcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:6:\"3.4.18\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^3.4.19\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/tailwindcss\";}s:7:\"thenify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"thenify\";s:10:\"\0*\0version\";s:5:\"3.3.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/thenify\";}s:11:\"thenify-all\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"thenify-all\";s:10:\"\0*\0version\";s:5:\"1.6.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/thenify-all\";}s:10:\"tinyglobby\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"tinyglobby\";s:10:\"\0*\0version\";s:6:\"0.2.15\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/tinyglobby\";}s:14:\"to-regex-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"to-regex-range\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/to-regex-range\";}s:9:\"tree-kill\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"tree-kill\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/tree-kill\";}s:20:\"ts-interface-checker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"ts-interface-checker\";s:10:\"\0*\0version\";s:6:\"0.1.13\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/ts-interface-checker\";}s:5:\"tslib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"tslib\";s:10:\"\0*\0version\";s:5:\"2.8.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/tslib\";}s:22:\"update-browserslist-db\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"update-browserslist-db\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/update-browserslist-db\";}s:14:\"util-deprecate\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"util-deprecate\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/util-deprecate\";}s:4:\"vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"vite\";s:10:\"\0*\0version\";s:5:\"6.4.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^6.4.3\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/vite\";}s:23:\"vite-plugin-full-reload\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"vite-plugin-full-reload\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/vite-plugin-full-reload\";}s:9:\"wrap-ansi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"wrap-ansi\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/wrap-ansi\";}s:4:\"y18n\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"y18n\";s:10:\"\0*\0version\";s:5:\"5.0.8\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/y18n\";}s:5:\"yargs\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"yargs\";s:10:\"\0*\0version\";s:6:\"17.7.2\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/yargs\";}s:12:\"yargs-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"yargs-parser\";s:10:\"\0*\0version\";s:6:\"21.1.1\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/yargs-parser\";}s:4:\"fdir\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"fdir\";s:10:\"\0*\0version\";s:5:\"6.5.0\";s:9:\"\0*\0source\";r:1525;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/fdir\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:181:{i:0;r:1522;i:1;r:1530;i:2;r:1538;i:3;r:1546;i:4;r:1554;i:5;r:1562;i:6;r:1570;i:7;r:1578;i:8;r:1586;i:9;r:1594;i:10;r:1602;i:11;r:1610;i:12;r:1618;i:13;r:1626;i:14;r:1634;i:15;r:1642;i:16;r:1650;i:17;r:1658;i:18;r:1666;i:19;r:1674;i:20;r:1682;i:21;r:1690;i:22;r:1698;i:23;r:1706;i:24;r:1714;i:25;r:1722;i:26;r:1730;i:27;r:1738;i:28;r:1746;i:29;r:1754;i:30;r:1762;i:31;r:1770;i:32;r:1778;i:33;r:1786;i:34;r:1794;i:35;r:1802;i:36;r:1810;i:37;r:1818;i:38;r:1826;i:39;r:1834;i:40;r:1842;i:41;r:1850;i:42;r:1858;i:43;r:1866;i:44;r:1874;i:45;r:1882;i:46;r:1890;i:47;r:1898;i:48;r:1906;i:49;r:1914;i:50;r:1922;i:51;r:1930;i:52;r:1938;i:53;r:1946;i:54;r:1954;i:55;r:1962;i:56;r:1970;i:57;r:1978;i:58;r:1986;i:59;r:1994;i:60;r:2002;i:61;r:2010;i:62;r:2018;i:63;r:2026;i:64;r:2034;i:65;r:2042;i:66;r:2050;i:67;r:2058;i:68;r:2066;i:69;r:2074;i:70;r:2082;i:71;r:2090;i:72;r:2098;i:73;r:2106;i:74;r:2114;i:75;r:2122;i:76;r:2130;i:77;r:2138;i:78;r:2146;i:79;r:2154;i:80;r:2162;i:81;r:2170;i:82;r:2178;i:83;r:2186;i:84;r:2194;i:85;r:2202;i:86;r:2210;i:87;r:2218;i:88;r:2226;i:89;r:2234;i:90;r:2242;i:91;r:2250;i:92;r:2258;i:93;r:2266;i:94;r:2274;i:95;r:2282;i:96;r:2290;i:97;r:2298;i:98;r:2306;i:99;r:2314;i:100;r:2322;i:101;r:2330;i:102;r:2338;i:103;r:2346;i:104;r:2354;i:105;r:2362;i:106;r:2370;i:107;r:2378;i:108;r:2386;i:109;r:2394;i:110;r:2402;i:111;r:2410;i:112;r:2418;i:113;r:2426;i:114;r:2434;i:115;r:2442;i:116;r:2450;i:117;r:2458;i:118;r:2466;i:119;r:2474;i:120;r:2482;i:121;r:2490;i:122;r:2498;i:123;r:2506;i:124;r:2514;i:125;r:2522;i:126;r:2530;i:127;r:2538;i:128;r:2546;i:129;r:2554;i:130;r:2562;i:131;r:2570;i:132;r:2578;i:133;r:2586;i:134;r:2594;i:135;r:2602;i:136;r:2610;i:137;r:2618;i:138;r:2626;i:139;r:2634;i:140;r:2642;i:141;r:2650;i:142;r:2658;i:143;r:2666;i:144;r:2674;i:145;r:2682;i:146;r:2690;i:147;r:2698;i:148;r:2706;i:149;r:2714;i:150;r:2722;i:151;r:2730;i:152;r:2738;i:153;r:2746;i:154;r:2754;i:155;r:2762;i:156;r:2770;i:157;r:2778;i:158;r:2786;i:159;r:2794;i:160;r:2802;i:161;r:2810;i:162;r:2818;i:163;r:2826;i:164;r:2834;i:165;r:2842;i:166;r:2850;i:167;r:2858;i:168;r:2866;i:169;r:2874;i:170;r:2882;i:171;r:2890;i:172;r:2898;i:173;r:2906;i:174;r:2914;i:175;r:2922;i:176;r:2930;i:177;r:2938;i:178;r:2946;i:179;r:2954;i:180;r:2962;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:17:\"\0*\0packageManager\";E:41:\"Laravel\\Roster\\Enums\\JsPackageManager:Npm\";}s:6:\"stacks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:35:\"Laravel\\Roster\\Enums\\Stack:Livewire\";}}s:21:\"browserTestFrameworks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:9:\"frontends\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:6:\"agents\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:4:{i:0;E:37:\"Laravel\\Roster\\Enums\\Agent:ClaudeCode\";i:1;E:32:\"Laravel\\Roster\\Enums\\Agent:Codex\";i:2;E:34:\"Laravel\\Roster\\Enums\\Agent:Copilot\";i:3;E:33:\"Laravel\\Roster\\Enums\\Agent:Gemini\";}}s:7:\"editors\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}}', 1791994692);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:98:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"view users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"create users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:10:\"edit users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"delete users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:10:\"view roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"create roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:10:\"edit roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:12:\"delete roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:16:\"view permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:18:\"create permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:16:\"edit permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:18:\"delete permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"assign roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"revoke roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:18:\"assign permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:18:\"revoke permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:15:\"view complaints\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:17:\"create complaints\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:15:\"edit complaints\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:17:\"delete complaints\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:17:\"assign complaints\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:19:\"escalate complaints\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:22;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:13:\"view branches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:23;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:15:\"create branches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:24;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:13:\"edit branches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:25;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:15:\"delete branches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:26;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:12:\"view regions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:27;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:14:\"create regions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:28;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:12:\"edit regions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:29;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:14:\"delete regions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:30;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:14:\"view divisions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:31;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:16:\"create divisions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:32;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:14:\"edit divisions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:33;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:16:\"delete divisions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:34;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:15:\"view categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:35;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:17:\"create categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:36;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:15:\"edit categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:37;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:17:\"delete categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:38;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:39;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:16:\"generate reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:40;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:14:\"export reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:41;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:14:\"view dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:42;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:14:\"view analytics\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:43;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:13:\"view settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:44;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:13:\"edit settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:45;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:15:\"system settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:46;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:13:\"view managers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:47;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:15:\"create managers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:48;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:13:\"edit managers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:49;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:15:\"delete managers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:50;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:16:\"view aksic rules\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:51;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:18:\"create aksic rules\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:52;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:16:\"edit aksic rules\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:53;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:18:\"delete aksic rules\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:54;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:14:\"approve aksics\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:55;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:13:\"import aksics\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:56;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:20:\"view file categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:57;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:22:\"create file categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:58;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:20:\"edit file categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:59;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:22:\"delete file categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:60;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:28:\"view file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:61;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:30:\"create file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:62;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:28:\"edit file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:63;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:30:\"delete file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:64;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:32:\"transfer file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:65;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:33:\"approve file management transfers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:66;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:31:\"archive file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:67;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:12:\"create boxes\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:68;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:12:\"manage boxes\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:69;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:21:\"view account openings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:70;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:23:\"create account openings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:71;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:21:\"edit account openings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:72;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:23:\"delete account openings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:73;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:24:\"approve account openings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:74;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:33:\"manage account opening references\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:75;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:17:\"view aksic claims\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:76;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:18:\"lodge aksic claims\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:77;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:19:\"settle aksic claims\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:78;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:19:\"delete aksic claims\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:79;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:17:\"view aksic budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:80;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:19:\"manage aksic budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:81;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:11:\"view aksics\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:82;a:3:{s:1:\"a\";i:89;s:1:\"b\";s:13:\"create aksics\";s:1:\"c\";s:3:\"web\";}i:83;a:3:{s:1:\"a\";i:90;s:1:\"b\";s:11:\"edit aksics\";s:1:\"c\";s:3:\"web\";}i:84;a:3:{s:1:\"a\";i:91;s:1:\"b\";s:13:\"delete aksics\";s:1:\"c\";s:3:\"web\";}i:85;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:20:\"view all aksic cases\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:5;}}i:86;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:20:\"view aksic dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:87;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:30:\"view file management dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:88;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:30:\"view account opening dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:89;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:30:\"export file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:90;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:23:\"submit account openings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:91;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:23:\"unlock account openings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:92;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:30:\"submit file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:93;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:31:\"approve file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:94;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:30:\"unlock file management systems\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:95;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:23:\"view aksic applications\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:96;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:23:\"sync aksic applications\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}i:97;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:31:\"update aksic application status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:5;}}}s:5:\"roles\";a:5:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:6:\"branch\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:6:\"region\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:8:\"division\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"head-office\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:11:\"super-admin\";s:1:\"c\";s:3:\"web\";}}}', 1792076514);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card_types`
--

CREATE TABLE `card_types` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL COMMENT 'BAJK Debit Card (Enhanced ATM & POS limits)',
  `name_ur` varchar(120) DEFAULT NULL,
  `name_on_card_max_length` tinyint(3) UNSIGNED NOT NULL DEFAULT 19 COMMENT 'Form: "Maximum length is 19 characters with spaces"',
  `available_for` enum('individual','entity','both') NOT NULL DEFAULT 'individual' COMMENT 'Form: "for Individual & sole proprietor accounts only"',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `card_types`
--

INSERT INTO `card_types` (`id`, `code`, `name`, `name_ur`, `name_on_card_max_length`, `available_for`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18af-7043-af3e-c5abd16fcc4e', 'BAJK_DEBIT', 'BAJK Debit Card (Enhanced ATM & POS limits)', 'بی اے جے کے ڈیبٹ کارڈ', 19, 'individual', 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b0-72a7-8168-5fe50ff76f08', 'BAJK_SILVER', 'BAJK Silver Debit Card', 'بی اے جے کے سلور ڈیبٹ کارڈ', 19, 'individual', 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b1-700a-bd27-8f3e56d7e690', 'BAJK_GOLD', 'BAJK Gold Debit Card', 'بی اے جے کے گولڈ ڈیبٹ کارڈ', 19, 'individual', 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Forms', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(2, 'Staff Advances', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(3, 'Staff Advances Policy', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(4, 'Code Of Conduct', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(5, 'Rules', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(6, 'Leave Rules', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(7, 'Operation\'s Manual', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(8, 'Basic Credit Guide', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29');

-- --------------------------------------------------------

--
-- Table structure for table `cdd_account_purpose`
--

CREATE TABLE `cdd_account_purpose` (
  `customer_due_diligence_id` char(36) NOT NULL,
  `account_purpose_id` char(36) NOT NULL,
  `other_description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cdd_counter_party_type`
--

CREATE TABLE `cdd_counter_party_type` (
  `customer_due_diligence_id` char(36) NOT NULL,
  `counter_party_type_id` char(36) NOT NULL,
  `other_description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cdd_income_source`
--

CREATE TABLE `cdd_income_source` (
  `customer_due_diligence_id` char(36) NOT NULL,
  `income_source_id` char(36) NOT NULL,
  `other_description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cdd_transaction_mode`
--

CREATE TABLE `cdd_transaction_mode` (
  `customer_due_diligence_id` char(36) NOT NULL,
  `transaction_mode_id` char(36) NOT NULL,
  `direction` enum('credit','debit') NOT NULL COMMENT 'Usual Mode of Credit Transaction / Usual Mode of Debit Transaction',
  `other_description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cdd_wealth_source`
--

CREATE TABLE `cdd_wealth_source` (
  `customer_due_diligence_id` char(36) NOT NULL,
  `wealth_source_id` char(36) NOT NULL,
  `other_description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` char(36) NOT NULL,
  `complaint_number` varchar(20) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `status` enum('Open','In Progress','Pending','Resolved','Closed','Reopened') NOT NULL DEFAULT 'Open',
  `source` enum('Phone','Email','Portal','Walk-in','Other') NOT NULL DEFAULT 'Phone',
  `complainant_name` varchar(100) DEFAULT NULL,
  `complainant_email` varchar(100) DEFAULT NULL,
  `complainant_phone` varchar(20) DEFAULT NULL,
  `complainant_account_number` varchar(50) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `region_id` bigint(20) UNSIGNED DEFAULT NULL,
  `division_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `expected_resolution_date` timestamp NULL DEFAULT NULL,
  `harassment_incident_date` datetime DEFAULT NULL,
  `harassment_location` varchar(150) DEFAULT NULL,
  `harassment_witnesses` varchar(255) DEFAULT NULL,
  `harassment_reported_to` varchar(150) DEFAULT NULL,
  `harassment_details` text DEFAULT NULL,
  `harassment_confidential` tinyint(1) NOT NULL DEFAULT 0,
  `harassment_sub_category` varchar(150) DEFAULT NULL,
  `harassment_employee_number` varchar(50) DEFAULT NULL,
  `harassment_employee_phone` varchar(50) DEFAULT NULL,
  `harassment_abuser_employee_number` varchar(50) DEFAULT NULL,
  `harassment_abuser_name` varchar(150) DEFAULT NULL,
  `harassment_abuser_phone` varchar(50) DEFAULT NULL,
  `harassment_abuser_email` varchar(150) DEFAULT NULL,
  `harassment_abuser_relationship` varchar(100) DEFAULT NULL,
  `grievance_employee_id` varchar(50) DEFAULT NULL,
  `grievance_department_position` varchar(150) DEFAULT NULL,
  `grievance_supervisor_name` varchar(150) DEFAULT NULL,
  `grievance_employment_start_date` date DEFAULT NULL,
  `grievance_type` varchar(100) DEFAULT NULL,
  `grievance_policy_violated` varchar(255) DEFAULT NULL,
  `grievance_previous_attempts` enum('Yes','No') DEFAULT NULL,
  `grievance_previous_attempts_details` text DEFAULT NULL,
  `grievance_desired_outcome` text DEFAULT NULL,
  `grievance_subject_name` varchar(150) DEFAULT NULL,
  `grievance_subject_position` varchar(150) DEFAULT NULL,
  `grievance_subject_relationship` varchar(100) DEFAULT NULL,
  `grievance_union_representation` tinyint(1) NOT NULL DEFAULT 0,
  `grievance_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `grievance_acknowledgment` tinyint(1) NOT NULL DEFAULT 0,
  `grievance_first_occurred_date` date DEFAULT NULL,
  `grievance_most_recent_date` date DEFAULT NULL,
  `grievance_pattern_frequency` varchar(50) DEFAULT NULL,
  `grievance_performance_effect` varchar(50) DEFAULT NULL,
  `sla_breached` tinyint(1) NOT NULL DEFAULT 0,
  `reopen_reason` text DEFAULT NULL,
  `priority_change_reason` varchar(500) DEFAULT NULL,
  `status_change_reason` varchar(500) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_assignments`
--

CREATE TABLE `complaint_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `complaint_id` char(36) NOT NULL,
  `assigned_to` bigint(20) UNSIGNED NOT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `assignment_type` enum('Primary','Secondary','Observer') NOT NULL DEFAULT 'Primary',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unassigned_at` timestamp NULL DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_attachments`
--

CREATE TABLE `complaint_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `complaint_id` char(36) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(150) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_categories`
--

CREATE TABLE `complaint_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `parent_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `default_priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `sla_hours` int(11) NOT NULL DEFAULT 24,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_categories`
--

INSERT INTO `complaint_categories` (`id`, `category_name`, `parent_category_id`, `description`, `default_priority`, `sla_hours`, `is_active`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Service Issue', NULL, 'Issues related to service quality or delivery delays', 'Medium', 48, 1, NULL, NULL, NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(2, 'Product Quality', NULL, 'Complaints concerning product defects or quality concerns', 'High', 24, 1, NULL, NULL, NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(3, 'Billing Error', NULL, 'Incorrect charges, invoices or payment issues', 'High', 12, 1, NULL, NULL, NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(4, 'Technical Problem', NULL, 'System outages, application bugs or login/access problems', 'Critical', 4, 1, NULL, NULL, NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(5, 'Feedback / Suggestion', NULL, 'Non-urgent customer suggestions or general feedback', 'Low', 72, 1, NULL, NULL, NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(6, 'Harassment', NULL, 'Reports related to harassment, abuse, or inappropriate conduct requiring sensitive handling. Escalates to senior management; ensure clear supporting evidence (screenshots, emails, logs) is attached and incident details are documented professionally.', 'High', 12, 1, NULL, NULL, NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(7, 'Grievance', NULL, 'Employee or customer grievance requiring formal review and possible mediation.', 'Medium', 36, 1, NULL, NULL, NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_comments`
--

CREATE TABLE `complaint_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `complaint_id` char(36) NOT NULL,
  `comment_text` text NOT NULL,
  `comment_type` enum('Internal','Customer','System') NOT NULL DEFAULT 'Internal',
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_escalations`
--

CREATE TABLE `complaint_escalations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `complaint_id` char(36) NOT NULL,
  `escalated_from` bigint(20) UNSIGNED NOT NULL,
  `escalated_to` bigint(20) UNSIGNED NOT NULL,
  `escalation_level` int(11) NOT NULL DEFAULT 1,
  `escalated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `escalation_reason` text NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_histories`
--

CREATE TABLE `complaint_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `complaint_id` char(36) NOT NULL,
  `action_type` enum('Created','Assigned','Reassigned','Status Changed','Comment Added','File Attached','Resolved','Reopened','Closed','Priority Changed','Category Changed','Feedback','Escalated','Branch Transfer','Region Transfer','Division Transfer') NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED NOT NULL,
  `performed_by` bigint(20) UNSIGNED NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attachment` varchar(255) DEFAULT NULL,
  `complaint_type` enum('Internal','Customer','System') NOT NULL DEFAULT 'Internal',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_metrics`
--

CREATE TABLE `complaint_metrics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `complaint_id` char(36) NOT NULL,
  `time_to_first_response` int(11) DEFAULT NULL,
  `first_response_at` timestamp NULL DEFAULT NULL,
  `time_to_resolution` int(11) DEFAULT NULL,
  `reopened_count` int(11) NOT NULL DEFAULT 0,
  `escalation_count` int(11) NOT NULL DEFAULT 0,
  `assignment_count` int(11) NOT NULL DEFAULT 0,
  `customer_satisfaction_score` decimal(3,2) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_status_types`
--

CREATE TABLE `complaint_status_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_status_types`
--

INSERT INTO `complaint_status_types` (`id`, `name`, `code`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Open', 'OPEN', 'Complaint is open and awaiting action', 1, NULL, NULL),
(2, 'In Progress', 'IN_PROGRESS', 'Complaint is actively being worked on', 1, NULL, NULL),
(3, 'Pending', 'PENDING', 'Complaint is pending further action or information', 1, NULL, NULL),
(4, 'Resolved', 'RESOLVED', 'Complaint has been resolved', 1, NULL, NULL),
(5, 'Closed', 'CLOSED', 'Complaint case has been closed', 1, NULL, NULL),
(6, 'Reopened', 'REOPENED', 'Previously closed complaint has been reopened', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_templates`
--

CREATE TABLE `complaint_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `template_subject` varchar(255) DEFAULT NULL,
  `template_body` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_watchers`
--

CREATE TABLE `complaint_watchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `complaint_id` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_witnesses`
--

CREATE TABLE `complaint_witnesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `complaint_id` char(36) NOT NULL,
  `employee_number` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `statement` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `status` enum('email','fax','telephone_no','mobile_no','whatsapp') NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `branch_id`, `contact`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, '05288-921254', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(2, 1, '05822-921181', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(3, 1, '05822-921365', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(4, 2, '05827-921448', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(5, 2, '05827-921442', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(6, 3, '05827-923070', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(7, 3, '05827-923069', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(8, 4, '05827-922664', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(9, 4, '05822-922665', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(10, 5, '05826-920241', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(11, 5, '05826-920242', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(12, 6, '05824-920069', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(13, 6, '05824-920068', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(14, 7, '05826-923062', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(15, 7, '05826-923063', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(16, 8, '05821-920033', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(17, 8, '05821-920033', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(18, 9, '05827-922562', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(19, 9, '05827-922563', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(20, 10, '05823-920103', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(21, 10, '05823-920104', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(22, 11, '05823-921005', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(23, 11, '05823-921006', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(24, 12, '05828-920505', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(25, 12, '05828-920506', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(26, 13, '05825-920010', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(27, 13, '05825-920009', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(28, 14, '05826-921060', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(29, 14, '05826-921061', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(30, 15, '05827-920439', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(31, 15, '05827-920440', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(32, 16, '05824-920233', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(33, 16, '05824-920234', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(34, 17, '05822-922502', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(35, 17, '05822-922503', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(36, 18, '05822-920466', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(37, 18, '05822-442664', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(38, 19, '05822-922302', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(39, 19, '05822-922303', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(40, 20, '05826-475094', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(41, 20, '05826-475094', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(42, 21, '05826-921863', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(43, 21, '05826-921862', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(44, 22, '05822-922003', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(45, 22, '05822-922002', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(46, 23, '05823-921213', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(47, 23, '05823-921214', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(48, 24, '05822-922108', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(49, 24, '05822-922109', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(50, 25, '05824-921027', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(51, 25, '05824-921028', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(52, 26, '05826-921163', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(53, 26, '05826-921164', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(54, 27, '05827-922260', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(55, 27, '05827-922261', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(56, 28, '05822-923126', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(57, 28, '05822-923127', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(58, 29, '05828-922064', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(59, 29, '05828-922063', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(60, 30, '05824-920911', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(61, 30, '05824-920912', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(62, 31, '05826-922361', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(63, 31, '05826-922362', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(64, 32, '05825-920224', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(65, 32, '05825-920223', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(66, 33, '05828-922162', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(67, 33, '05828-922261', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(68, 34, '05822-922631', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(69, 34, '05822-922632', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(70, 35, '05827-485533', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(71, 35, '05827-485533', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(72, 36, '05826-922165', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(73, 36, '05826-922165', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(74, 37, '05822-921826', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(75, 37, '05822-921826', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(76, 38, '05823-921772', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(77, 38, '05823-921772', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(78, 39, '05824-921608', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(79, 39, '05824-921608', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(80, 40, '05822-920043', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(81, 40, '05822-920043', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(82, 41, '05825-920316', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(83, 41, '05825-9203167', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(84, 42, '05826-921461', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(85, 42, '05826-921461', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(86, 43, '05821-920802', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(87, 43, '05821-920802', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(88, 44, '05826-921865', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(89, 44, '05826-921865', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(90, 45, '05827-922764', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(91, 45, '05827-922764', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(92, 46, '05827-920442', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(93, 46, '05827-920442', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(94, 47, '05827-922564', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(95, 47, '05827-922564', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(96, 48, '05826-921761', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(97, 48, '05826-921761', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(98, 49, '05826-471319', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(99, 49, '05826-471319', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(100, 50, '05824-921313', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(101, 50, '05824-921313', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(102, 51, '05826-474441', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(103, 51, '05826-474441', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(104, 52, '05821-920502', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(105, 52, '05821-920502', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(106, 53, '05822-923007', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(107, 53, '05822-923008', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(108, 54, '05827-920405', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(109, 54, '05827-920406', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(110, 55, '05826-923150', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(111, 55, '05826-923150', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(112, 56, '05824-920545', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(113, 56, '05824-920544', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(114, 57, '05827-922666', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(115, 57, '05827-922666', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(116, 58, '05827-923011', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(117, 58, '05827-923011', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(118, 59, '05330-29731', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(119, 59, '05330-29731', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(120, 60, '05824-921106', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(121, 60, '05824-921106', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(122, 61, '05822-920747', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(123, 61, '05822-920746', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(124, 62, '05822-922514', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(125, 62, '05822-922514', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(126, 63, '05821-920302', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(127, 63, '05821-920303', 'fax', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(128, 64, '05826-923350', 'telephone_no', NULL, '2025-12-12 11:40:04', '2025-12-12 11:40:04'),
(129, 64, '05826-923350', 'fax', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(130, 65, '05826-480649', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(131, 65, '05826-480649', 'fax', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(132, 66, '05826-920247', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(133, 66, '05826-920248', 'fax', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(134, 67, '05822-923020', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(135, 67, '05822-923019', 'fax', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(136, 68, '05825-920715', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(137, 68, '05825-920716', 'fax', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(138, 69, '05821-920342', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(139, 69, '05821-920343', 'fax', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(140, 70, '0301-3117420', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(141, 71, '0315-3191313', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(142, 72, '0345-5939806', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(143, 73, '05828-922165-66', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(144, 74, '0346-5463091', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(145, 75, '05826-922045-46', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(146, 76, '05826-922045-46', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(147, 77, '05822-920862', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(148, 78, '05828-922068', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(149, 79, '05827-920079', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(150, 79, '05827-940751', 'fax', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(151, 80, '05827-922448', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(152, 80, '05827-922449', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(153, 81, '05827-922654', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(154, 81, '05827-922656', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(155, 82, '05826-940820', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(156, 83, '05824-920040', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(157, 83, '05824-920049', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(158, 84, '05822-922867', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(159, 84, '05822-922865', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(160, 85, '05822-923021', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(161, 86, '05823-920834', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(162, 87, '05827-922264', 'telephone_no', NULL, '2025-12-12 11:40:05', '2025-12-12 11:40:05');

-- --------------------------------------------------------

--
-- Table structure for table `controlling_person_types`
--

CREATE TABLE `controlling_person_types` (
  `id` char(36) NOT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(120) NOT NULL COMMENT 'Owner (direct or indirect), Controlling Person by other means, Senior Management Official, Beneficiary, Settlor, Trustee, Protector, Settlor-equivalent, Trustee-equivalent, Protector-equivalent, Beneficiary-equivalent, Other-equivalent',
  `name_ur` varchar(120) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `controlling_person_types`
--

INSERT INTO `controlling_person_types` (`id`, `code`, `name`, `name_ur`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18d7-732d-a428-2f2694dd7aa4', 'OWNER', 'Owner (direct or indirect)', 'مالک (بلاواسطہ یا بالواسطہ)', 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d8-729e-95c7-6e8c3aa87644', 'CONTROL_OTHER_MEANS', 'Controlling Person by other means', 'کنٹرول کرنے والا دیگر ذرائع سے', 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d8-729e-95c7-6e8c3aa8e55a', 'SENIOR_MGMT', 'Senior Management Official', 'سینئر مینجمنٹ آفیشل', 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d9-7274-800c-7d98aec483ef', 'BENEFICIARY', 'Beneficiary', 'بینیفشری', 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d9-7274-800c-7d98afaf0eb6', 'SETTLOR', 'Settlor', 'سیٹلر', 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18da-72a6-91cd-1fcc5d0a1fe1', 'TRUSTEE', 'Trustee', 'ٹرسٹی', 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18dc-7023-95b2-a8d704d3c73b', 'PROTECTOR', 'Protector', 'پروٹیکٹر', 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18dc-7023-95b2-a8d705ac3a18', 'SETTLOR_EQUIVALENT', 'Settlor-equivalent', 'سیٹلر مساوی', 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18dd-70b5-88e5-111690394917', 'TRUSTEE_EQUIVALENT', 'Trustee-equivalent', 'ٹرسٹی مساوی', 9, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18dd-70b5-88e5-111690818f92', 'PROTECTOR_EQUIVALENT', 'Protector-equivalent', 'پروٹیکٹر مساوی', 10, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18de-719a-8cc8-a29ebea20002', 'BENEFICIARY_EQUIVALENT', 'Beneficiary-equivalent', 'بینیفشری مساوی', 11, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18de-719a-8cc8-a29ebf2563fb', 'OTHER_EQUIVALENT', 'Other-equivalent', 'دیگر مساوی', 12, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `counter_party_types`
--

CREATE TABLE `counter_party_types` (
  `id` char(36) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'Financial Institution, Limited Company, Proprietorship, Govt. Entity, NPO, Others',
  `name_ur` varchar(80) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counter_party_types`
--

INSERT INTO `counter_party_types` (`id`, `code`, `name`, `name_ur`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18ce-7359-8faf-0ddc7c2e543a', 'FINANCIAL_INSTITUTION', 'Financial Institution', 'مالیاتی ادارہ', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18cf-73b6-b34d-4e49bbc0d590', 'LIMITED_COMPANY', 'Limited Company', 'لمیٹڈ کمپنی', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18cf-73b6-b34d-4e49bbeb261d', 'PROPRIETORSHIP', 'Proprietorship', 'پروپرائٹرشپ', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d0-706b-85e6-6e72a33340d2', 'GOVT_ENTITY', 'Govt. Entity', 'سرکاری ادارہ', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d0-706b-85e6-6e72a38cc20c', 'NPO', 'NPO', 'این پی او', 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d1-70a5-942f-4e4ecc1fc13e', 'COUNTER_OTHERS', 'Others', 'دیگر', 1, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` char(36) NOT NULL,
  `iso2` char(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2, e.g. PK',
  `iso3` char(3) NOT NULL COMMENT 'ISO 3166-1 alpha-3, e.g. PAK',
  `name` varchar(100) NOT NULL,
  `name_ur` varchar(100) DEFAULT NULL,
  `dial_code` varchar(8) DEFAULT NULL COMMENT 'Form: country code mandatory with mobile/telephone',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `iso2`, `iso3`, `name`, `name_ur`, `dial_code`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1857-7383-8bd4-8627236be69a', 'PK', 'PAK', 'Pakistan', 'پاکستان', '+92', 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1859-70d9-b644-c22631657552', 'US', 'USA', 'United States of America', 'امریکہ', '+1', 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-185a-72b8-bcb0-f878a0a1adf2', 'GB', 'GBR', 'United Kingdom', 'برطانیہ', '+44', 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-185c-7036-97ca-22b251f2ed56', 'AE', 'ARE', 'United Arab Emirates', 'متحدہ عرب امارات', '+971', 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-185d-72f6-9260-638e10f75cc5', 'SA', 'SAU', 'Saudi Arabia', 'سعودی عرب', '+966', 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `crs_entity_classifications`
--

CREATE TABLE `crs_entity_classifications` (
  `id` char(36) NOT NULL,
  `code` varchar(40) NOT NULL,
  `form_option` char(1) DEFAULT NULL COMMENT 'a, b, c, d, e, f, g, h, i -- printed form ka option letter',
  `name` varchar(220) NOT NULL,
  `name_ur` varchar(220) DEFAULT NULL,
  `requires_giin` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Options (b) & (c): "please provide the GIIN obtained"',
  `requires_controlling_persons` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Options (a) & (i): 20% or more shareholding & voting rights wale log',
  `requires_stock_exchange` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Option (d): "please provide name of stock exchange where company is listed"',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `crs_entity_classifications`
--

INSERT INTO `crs_entity_classifications` (`id`, `code`, `form_option`, `name`, `name_ur`, `requires_giin`, `requires_controlling_persons`, `requires_stock_exchange`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18d2-7390-93f0-d7f1667d4d42', 'INV_ENTITY_NP_COUNTRY', 'a', 'Investment Entity located in a Non-Participating Country and managed by another Financial Institution', NULL, 0, 1, 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d2-7390-93f0-d7f166d9e9e3', 'OTHER_INV_ENTITY', 'b', 'Other Investment Entity', NULL, 1, 0, 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d3-7362-a71f-acaa42880ff7', 'FI_DEPOSITORY', 'c', 'Financial Institution - Depository Institution, Custodial Institution or Specified Insurance Company', NULL, 1, 0, 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d4-716d-9a4e-c0c23502d7e7', 'ACTIVE_NFE_LISTED', 'd', 'An Active NFE, stock of which is regularly traded on an established securities market, or a corporation which is a related entity of such a corporation', NULL, 0, 0, 1, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d4-716d-9a4e-c0c23573d047', 'GOVT_ENTITY_CENTRAL_BANK', 'e', 'Government Entity or Central Bank', NULL, 0, 0, 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d5-7028-8372-dca6840f308e', 'INTERNATIONAL_ORG', 'f', 'International Organization', NULL, 0, 0, 0, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d5-7028-8372-dca684bf8270', 'NGO_NPO_STARTUP_NFE', 'g', 'NGO/NPO or a startup NFE or Non Profit NFE', NULL, 0, 0, 0, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d6-7149-b103-0d95fd3053ef', 'ACTIVE_NFE_INCOME', 'h', 'Active NFE - having 50% and above income derived from core business activity', NULL, 0, 0, 0, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18d6-7149-b103-0d95fe1c04ee', 'PASSIVE_NFE', 'i', 'Passive NFE', NULL, 0, 1, 0, 9, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` char(36) NOT NULL,
  `code` char(3) NOT NULL COMMENT 'PKR, USD, GBP, EUR, AED, SAR',
  `name` varchar(60) NOT NULL,
  `symbol` varchar(10) DEFAULT NULL,
  `is_local` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true sirf PKR ke liye (Rupee products)',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `code`, `name`, `symbol`, `is_local`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-185e-70ae-a401-375e6c69c4a6', 'PKR', 'Pakistani Rupee', 'Rs', 1, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-185f-7335-ba9c-3608904ee88a', 'USD', 'US Dollar', '$', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1860-71e9-8d2f-e363d6c674f6', 'GBP', 'Great Britain Pound', '£', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1860-71e9-8d2f-e363d72c33b6', 'EUR', 'Euro', '€', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1861-706b-9673-e1ed20e2793b', 'AED', 'UAE Dirham', 'د.إ', 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1862-706d-8651-af414f71c2fe', 'SAR', 'Saudi Riyal', '﷼', 0, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1862-706d-8651-af415039812b', 'JPY', 'Japanese Yen', '¥', 0, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` char(36) NOT NULL,
  `cif_number` varchar(20) NOT NULL COMMENT 'Client/Relationship ID -- AOF p.1 aur CIF header',
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `cif_date` date NOT NULL COMMENT 'CIF header ka "Date" box',
  `customer_type` enum('individual','sole_proprietor','entity') NOT NULL COMMENT 'customer_categories.requires_* se derive hota hai',
  `aof_form_type` enum('individual_joint_sole','entity') NOT NULL COMMENT 'Kaunsa printed AOF bhara gaya: 15-page Individual/Joint/Sole Proprietor ya 17-page Govt/Company',
  `customer_category_id` char(36) NOT NULL,
  `customer_category_other` varchar(150) DEFAULT NULL COMMENT 'Others => "(Please Specify)"',
  `category_code_description` varchar(200) DEFAULT NULL COMMENT 'Form box: "Category Code & Desc."',
  `economic_sector_id` char(36) DEFAULT NULL,
  `economic_sector_code` varchar(10) DEFAULT NULL COMMENT 'Entity AOF: Economic Sector Code typed by the user (replaces the dropdown)',
  `is_zakat_exempt` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Zakat Exemption: Yes / No',
  `zakat_exemption_reason_id` char(36) DEFAULT NULL,
  `zakat_exemption_other` varchar(150) DEFAULT NULL,
  `cz50_submitted_on` date DEFAULT NULL COMMENT 'Form: "Subject to submission of affidavit/declaration form CZ-50 on time"',
  `is_pep` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Politically Exposed Person / Political Connections: Yes / No',
  `pep_form_attached` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"if Yes, please complete and attached the PEP declaration form"',
  `status` enum('draft','pending_verification','active','closed','rejected') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'CIF hard-delete nahi hota -- record retention requirement'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `cif_number`, `branch_id`, `cif_date`, `customer_type`, `aof_form_type`, `customer_category_id`, `customer_category_other`, `category_code_description`, `economic_sector_id`, `economic_sector_code`, `is_zakat_exempt`, `zakat_exemption_reason_id`, `zakat_exemption_other`, `cz50_submitted_on`, `is_pep`, `pep_form_attached`, `status`, `created_by`, `updated_by`, `verified_by`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01a13aff-4c2a-73b1-9926-4dc2e59ff46f', '0011-20261014-0001', 11, '2026-10-14', 'individual', 'individual_joint_sole', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 'draft', 88, 88, NULL, NULL, NULL, '2026-10-14 15:19:32', '2026-10-14 15:19:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `address_type` enum('permanent_residential','registered_business','current_residential','current_business','mailing') NOT NULL,
  `house_office_no` varchar(100) DEFAULT NULL COMMENT 'House/Office No.',
  `street_area` varchar(150) DEFAULT NULL COMMENT 'Street/Area',
  `tehsil_district` varchar(120) DEFAULT NULL COMMENT 'Tehsil/District',
  `nearest_landmark` varchar(150) DEFAULT NULL COMMENT 'Nearest Landmark',
  `city` varchar(100) DEFAULT NULL COMMENT 'City',
  `country_id` char(36) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL COMMENT 'Postal Code',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_categories`
--

CREATE TABLE `customer_categories` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL COMMENT 'Form box: "Category Code & Desc."',
  `name` varchar(120) NOT NULL,
  `name_ur` varchar(120) DEFAULT NULL,
  `applies_to` enum('individual','entity','both') NOT NULL COMMENT 'individual = 15-page AOF, entity = 17-page AOF',
  `requires_individual_profile` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true => customer_individuals row lazmi ("Customer Details For Individuals")',
  `requires_organization_profile` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true => customer_organizations row lazmi ("Customer Details For Business")',
  `is_other` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Others => "(Please Specify)" free text',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_categories`
--

INSERT INTO `customer_categories` (`id`, `code`, `name`, `name_ur`, `applies_to`, `requires_individual_profile`, `requires_organization_profile`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 'INDIVIDUAL', 'Individual', 'انفرادی', 'individual', 1, 0, 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1865-7302-a4ec-e31d207c72f5', 'SOLE_PROP', 'Sole Proprietorship', 'سول پروپرائٹر شپ', 'individual', 1, 1, 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1866-709e-8cd8-5c8f8d966f63', 'JOINT', 'Joint', 'مشترکہ', 'individual', 1, 0, 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1867-72e8-a0fa-882fa7e6086c', 'IND_OTHERS', 'Others', 'دیگر', 'individual', 1, 0, 1, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1867-72e8-a0fa-882fa8239621', 'PARTNERSHIP', 'Partnership (Registered/Unregistered)', 'شراکت (رجسٹرڈ/غیر رجسٹرڈ)', 'entity', 0, 1, 0, 10, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1868-7102-84a1-7429b83d733f', 'LIMITED_CO', 'Limited Companies', 'لمیٹڈ کمپنیاں', 'entity', 0, 1, 0, 11, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1869-736b-ba60-e4daf501ce30', 'PUBLIC_LISTED', 'Public Listed', 'پبلک لسٹڈ', 'entity', 0, 1, 0, 12, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1869-736b-ba60-e4daf5d3d52f', 'PUBLIC_UNLISTED', 'Public Unlisted', 'پبلک ان لسٹڈ', 'entity', 0, 1, 0, 13, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186a-70c0-8278-a2cb4220fa62', 'PRIVATE', 'Private', 'پرائیویٹ', 'entity', 0, 1, 0, 14, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186a-70c0-8278-a2cb42b21690', 'FOREIGN_MISSION', 'Foreign Missions/Diplomates', 'غیر ملکی سفارت خانے/سفارتکار', 'entity', 0, 1, 0, 15, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186b-735b-8877-ee080b537b24', 'GOVT_INSTITUTION', 'Government Institution (Federal/Provisional/Local)', 'سرکاری ادارہ (وفاقی/صوبائی/مقامی)', 'entity', 0, 1, 0, 16, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186c-73e8-8dbc-b6d9c4b048da', 'SOCIETY', 'Societies', 'سوسائٹیز', 'entity', 0, 1, 0, 17, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186d-7155-b85a-e464d35dda68', 'CLUB', 'Club', 'کلب', 'entity', 0, 1, 0, 18, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186d-7155-b85a-e464d3603e03', 'TRUST', 'Trust', 'ٹرسٹ', 'entity', 0, 1, 0, 19, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186e-72ef-a165-2d7e5755b8c8', 'ASSOCIATION', 'Associations', 'ایسوسی ایشن', 'entity', 0, 1, 0, 20, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186f-70f0-b4d6-1deab31ce6a8', 'LOCAL_ZAKAT', 'Local Zakat Committee', 'لوکل زکوٰۃ کمیٹی', 'entity', 0, 1, 0, 21, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-186f-70f0-b4d6-1deab3f40aa4', 'MONEY_EXCHANGE', 'Business - Money Exchange Company', 'منی ایکسچینج کمپنی', 'entity', 0, 1, 0, 22, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1870-71a7-bd30-60dd17c691c5', 'FOREIGN_BRANCH', 'Branch Office or Liaison Office of Foreign Companies', 'غیر ملکی کمپنیوں کا برانچ/رابطہ دفتر', 'entity', 0, 1, 0, 23, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1870-71a7-bd30-60dd17cddd3f', 'NGO_NPO', 'NGO/NPO\'s Charities', 'این جی او/این پی او/خیراتی ادارے', 'entity', 0, 1, 0, 24, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1871-737e-9460-3205b262f7d0', 'AGENT', 'Agents Accounts', 'ایجنٹ اکاؤنٹ', 'entity', 0, 1, 0, 25, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1872-71ab-9c8e-95594fc6d081', 'EXECUTOR', 'Executors & Administrators', 'مختار کنندہ اور منتظم', 'entity', 0, 1, 0, 26, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1872-71ab-9c8e-95594fe2bc1c', 'ENT_OTHERS', 'Others', 'دیگر', 'entity', 0, 1, 1, 27, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `customer_contacts`
--

CREATE TABLE `customer_contacts` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `contact_type` enum('mobile','telephone_residence_office','fax_personal_office','email_personal_office') NOT NULL,
  `country_code` varchar(8) DEFAULT NULL COMMENT 'Form: "(Country Code is mandatory with Mobile & Telephone Number)"',
  `value` varchar(150) NOT NULL COMMENT 'Number ya email address',
  `sequence` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Telephone (1) / (2)',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_registered_for_mobile_banking` tinyint(1) NOT NULL DEFAULT 0 COMMENT '*** mandatory for Mobile Banking',
  `is_registered_for_estatement` tinyint(1) NOT NULL DEFAULT 0 COMMENT '** mandatory for Internet Banking & BAJK E-Statement',
  `is_contact_center_registered` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'T&C clause 67',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_controlling_persons`
--

CREATE TABLE `customer_controlling_persons` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `person_customer_id` char(36) DEFAULT NULL,
  `declaration_basis` enum('fatca_10_percent','crs_20_percent','both') NOT NULL DEFAULT 'fatca_10_percent',
  `sequence` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Applicant 1..10 (form ka number)',
  `person_name` varchar(150) NOT NULL COMMENT 'Applicant ka naam',
  `designation` varchar(120) DEFAULT NULL COMMENT 'Designation',
  `shareholding_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Share holding / Voting rights %',
  `controlling_person_type_id` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_documents`
--

CREATE TABLE `customer_documents` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `account_id` char(36) DEFAULT NULL,
  `account_opening_request_id` char(36) DEFAULT NULL,
  `document_type_id` char(36) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(200) DEFAULT NULL,
  `document_number` varchar(80) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_attested` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Important Note (b)',
  `attested_by` varchar(150) DEFAULT NULL COMMENT 'Gazetted officer / Nazim / Administrator / Bank officer',
  `status` enum('pending','received','verified','waived','rejected') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_on` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_due_diligences`
--

CREATE TABLE `customer_due_diligences` (
  `id` char(36) NOT NULL,
  `account_id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `cdd_type` enum('individual','sole_proprietor','business') NOT NULL COMMENT 'Kaunsa printed CDD box bhara gaya',
  `customer_source` enum('walk_in','marketed','referred') DEFAULT NULL COMMENT 'Type of Customer: Walk In / Marketed / Referred By',
  `referred_by` varchar(150) DEFAULT NULL COMMENT '"Referred By ____"',
  `physical_verification_conducted` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Physical Verification Conducted',
  `proscribed_list_cleared` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Name Cleared From Proscribed List',
  `employer_name` varchar(200) DEFAULT NULL COMMENT 'Employer Name',
  `employer_designation` varchar(120) DEFAULT NULL COMMENT 'Designation',
  `employer_address` text DEFAULT NULL COMMENT 'Address of Employer',
  `fund_provider_employer` varchar(200) DEFAULT NULL COMMENT 'Employer of Fund Provider',
  `fund_provider_id_number` varchar(50) DEFAULT NULL COMMENT 'ID Document No. of Fund Provider',
  `fund_provider_relationship_id` char(36) DEFAULT NULL,
  `is_dnfbp` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"IS A DNFBP?"',
  `business_name` varchar(200) DEFAULT NULL COMMENT 'Business Name',
  `business_nature_text` varchar(200) DEFAULT NULL COMMENT 'Business Nature',
  `business_address` text DEFAULT NULL COMMENT 'Business Address',
  `type_of_channels` varchar(200) DEFAULT NULL COMMENT 'Type of Channels',
  `type_of_counterparties` varchar(200) DEFAULT NULL COMMENT 'Type of Counterparties',
  `geographies_involved` varchar(200) DEFAULT NULL COMMENT 'Geographies Involved',
  `nature_of_work` varchar(200) DEFAULT NULL COMMENT 'Nature of Work',
  `home_remittance_country_id` char(36) DEFAULT NULL,
  `home_remittance_relationship` varchar(120) DEFAULT NULL COMMENT 'Relationship ____',
  `monthly_income` decimal(18,2) DEFAULT NULL COMMENT 'Monthly Income (Individual CDD)',
  `monthly_net_income` decimal(18,2) DEFAULT NULL COMMENT 'Monthly Net Income (Business CDD)',
  `number_of_employees` int(10) UNSIGNED DEFAULT NULL COMMENT 'Number of employees (Business CDD)',
  `source_of_income_other` varchar(200) DEFAULT NULL COMMENT 'Others => "(Please Specify)"',
  `source_of_wealth_other` varchar(200) DEFAULT NULL COMMENT 'Others => "(Please Specify)"',
  `purpose_of_account_other` varchar(200) DEFAULT NULL COMMENT 'Others => "(Please Specify)"',
  `credit_mode_other` varchar(120) DEFAULT NULL COMMENT 'Usual Mode of Credit Transaction: Others',
  `debit_mode_other` varchar(120) DEFAULT NULL COMMENT 'Usual Mode of Debit Transaction: Others',
  `counter_party_other` varchar(120) DEFAULT NULL COMMENT 'Expected Type of Counter Parties: Others',
  `initial_deposit` decimal(18,2) DEFAULT NULL COMMENT 'Initial Deposit',
  `expected_monthly_credit_amount` decimal(18,2) DEFAULT NULL COMMENT 'Expected Aggregate Credit (per month): Amount',
  `expected_monthly_credit_count` int(10) UNSIGNED DEFAULT NULL COMMENT 'No. of Transaction',
  `expected_monthly_debit_amount` decimal(18,2) DEFAULT NULL COMMENT 'Expected Aggregate Debit (per month): Amount',
  `expected_monthly_debit_count` int(10) UNSIGNED DEFAULT NULL COMMENT 'No. of Transaction',
  `expected_iftt_amount` decimal(18,2) DEFAULT NULL COMMENT 'Expected IFTT Amount (per month) -- Intra-Fund Transfer Transaction',
  `expected_oftt_amount` decimal(18,2) DEFAULT NULL COMMENT 'Expected OFTT Amount (per month) -- Online/Outward Fund Transfer',
  `currency_symbol` varchar(10) DEFAULT NULL COMMENT 'Form note: "In case of Foreign Currency Account, please mention currency symbol"',
  `joint_account_note` text DEFAULT NULL COMMENT 'Form note: joint accounts mein expected credit/debit aggregate aur source of income collectively',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_fatca_details`
--

CREATE TABLE `customer_fatca_details` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `q1_is_us_person` tinyint(1) DEFAULT NULL COMMENT 'Q1: "Are you a US National/Citizen/Green Card Holder or U.S. Resident"',
  `q2_country_of_birth_us` tinyint(1) DEFAULT NULL COMMENT 'Q2: "Is your country of birth US?"',
  `q3_has_us_address_or_phone` tinyint(1) DEFAULT NULL COMMENT 'Q3: "Do you have a U.S. Address or Telephone No?"',
  `q4_has_us_mandate_or_links` tinyint(1) DEFAULT NULL COMMENT 'Q4: mandate to a person having an address in the US / any US links',
  `form_w9_signed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"If Yes please sign Form W9"',
  `form_w8_ben_signed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Non-US Person claim => W8 BEN',
  `us_nationality_revoked` tinyint(1) NOT NULL DEFAULT 0 COMMENT '"If applicant claims revocation of U.S. Nationality"',
  `loss_of_nationality_cert_path` varchar(255) DEFAULT NULL COMMENT 'Certificate of loss of residency / written explanation of revocation',
  `entity_incorporated_in_us` tinyint(1) DEFAULT NULL COMMENT '(a) Country of Incorporation and/or Parent Company Country of Incorporation is the U.S.?',
  `incorporation_country_id` char(36) DEFAULT NULL,
  `nfe_type` enum('active','passive') DEFAULT NULL COMMENT '(b) Active Non-Financial Entity ya Passive Non-Financial Entity',
  `form_w8_ben_e_signed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Passive NFE => W8-BEN-E',
  `ssn_ein_document_path` varchar(255) DEFAULT NULL COMMENT '"copy of Social Security Number Card (SSN)/Employer Identification Number (EIN)"',
  `crs_entity_classification_id` char(36) DEFAULT NULL,
  `giin` varchar(30) DEFAULT NULL COMMENT 'Global Intermediary Identification Number',
  `listed_stock_exchange` varchar(150) DEFAULT NULL COMMENT 'Option (d): name of stock exchange where company is listed',
  `is_tax_resident_other_country` tinyint(1) DEFAULT NULL COMMENT '"Are you a Tax Resident of country other than Pakistan & USA?"',
  `self_certification_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_identifications`
--

CREATE TABLE `customer_identifications` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `identification_document_type_id` char(36) NOT NULL,
  `document_number` varchar(50) NOT NULL,
  `issue_date` date DEFAULT NULL COMMENT 'Issued Date',
  `expiry_date` date DEFAULT NULL COMMENT 'Expiry Date of Identification Document',
  `place_of_issuance` varchar(120) DEFAULT NULL COMMENT 'Place of Issuance (City)',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_expired_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `nadra_token_number` varchar(60) DEFAULT NULL COMMENT 'NADRA receipt/token number',
  `renewal_due_date` date DEFAULT NULL COMMENT '3 maheene ke andar renewed CNIC lazmi',
  `verisys_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verisys_date` date DEFAULT NULL,
  `verisys_report_path` varchar(255) DEFAULT NULL,
  `is_attested` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Important Note (b): Gazetted officer/Nazim/Administrator/Bank officer',
  `scan_path` varchar(255) DEFAULT NULL COMMENT 'Photocopy of identity document',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_individuals`
--

CREATE TABLE `customer_individuals` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `title` varchar(10) DEFAULT NULL COMMENT 'Mr / Mrs / Ms',
  `full_name` varchar(150) NOT NULL COMMENT '*Name',
  `full_name_ur` varchar(150) DEFAULT NULL,
  `parentage_relationship_id` char(36) DEFAULT NULL,
  `parent_or_spouse_name` varchar(150) DEFAULT NULL,
  `mother_maiden_name` varchar(150) DEFAULT NULL COMMENT '*Mother''s Maiden Name',
  `gender_id` char(36) DEFAULT NULL,
  `marital_status_id` char(36) DEFAULT NULL,
  `marital_status_other` varchar(80) DEFAULT NULL COMMENT 'Others => "(Please Specify)"',
  `education_level_id` char(36) DEFAULT NULL,
  `education_other` varchar(80) DEFAULT NULL,
  `date_of_birth` date NOT NULL COMMENT '*Date of Birth',
  `birth_country_id` char(36) DEFAULT NULL,
  `birth_city` varchar(100) DEFAULT NULL COMMENT 'City of Birth',
  `residence_country_id` char(36) DEFAULT NULL,
  `profession_id` char(36) DEFAULT NULL,
  `profession_other` varchar(120) DEFAULT NULL,
  `designation` varchar(120) DEFAULT NULL COMMENT 'Designation',
  `ntn` varchar(30) DEFAULT NULL COMMENT 'N.T.N Number',
  `is_minor` tinyint(1) NOT NULL DEFAULT 0,
  `guardian_name` varchar(150) DEFAULT NULL COMMENT '*Name of Guardian',
  `guardian_relationship_id` char(36) DEFAULT NULL,
  `guardian_customer_id` char(36) DEFAULT NULL,
  `employer_institution_name` varchar(200) DEFAULT NULL COMMENT 'Employer''s/Educational Institution Name',
  `employer_institution_address` text DEFAULT NULL COMMENT 'Employer''s/Educational Institution Address',
  `has_availed_mnp` tinyint(1) DEFAULT NULL COMMENT '"Have you availed Mobile Number Portability (MNP) services?" Yes/No',
  `mnp_new_provider` varchar(100) DEFAULT NULL COMMENT '"please mention name of your new Mobile Service provider"',
  `requires_witness` tinyint(1) NOT NULL DEFAULT 0 COMMENT '*Witness is mandatory for illiterate/visually impaired customers',
  `witness_name` varchar(150) DEFAULT NULL COMMENT 'Name of Witness (Accompanied by Customer)',
  `witness_relation` varchar(100) DEFAULT NULL COMMENT 'Relation with Customer',
  `witness_cnic` varchar(25) DEFAULT NULL COMMENT 'CNIC',
  `witness_signature_path` varchar(255) DEFAULT NULL COMMENT 'Signature',
  `photograph_path` varchar(255) DEFAULT NULL COMMENT 'Photo Account / shaky-immature signature case: passport sized photograph',
  `thumb_impression_taken` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Left & right thumb impressions on SS Card',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_nationalities`
--

CREATE TABLE `customer_nationalities` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `country_id` char(36) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Nationality (1) = primary',
  `sequence` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Form ke box ka number: 1, 2, 3',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_next_of_kin`
--

CREATE TABLE `customer_next_of_kin` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL COMMENT 'Name',
  `relationship_id` char(36) DEFAULT NULL,
  `relationship_other` varchar(80) DEFAULT NULL,
  `identification_document_type_id` char(36) DEFAULT NULL,
  `identification_number` varchar(50) DEFAULT NULL COMMENT 'CNIC/CRC/NICOP/POC/Alient Registration Card No.',
  `address` varchar(255) DEFAULT NULL COMMENT 'Address',
  `tehsil_district` varchar(120) DEFAULT NULL COMMENT 'Tehsil/District',
  `nearest_landmark` varchar(150) DEFAULT NULL COMMENT 'Nearest Landmark',
  `city` varchar(100) DEFAULT NULL COMMENT 'City',
  `country_id` char(36) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL COMMENT 'Post Code',
  `telephone` varchar(30) DEFAULT NULL COMMENT 'Personal/Office Telephone No.',
  `email` varchar(150) DEFAULT NULL COMMENT 'Personal/Office Email ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_organizations`
--

CREATE TABLE `customer_organizations` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `business_name` varchar(200) NOT NULL COMMENT '*Company/Business Name',
  `business_name_ur` varchar(200) DEFAULT NULL,
  `other_business_of_proprietor` varchar(200) DEFAULT NULL COMMENT 'AOF-Ind p.15: "Other Business of the Proprietor"',
  `business_nature_id` char(36) DEFAULT NULL,
  `business_nature_other` varchar(150) DEFAULT NULL COMMENT 'Others => "(Please specify)"',
  `business_registration_number` varchar(60) DEFAULT NULL COMMENT 'Business Registration No. (SECP BRN etc.)',
  `issued_date` date DEFAULT NULL COMMENT 'Issued Date',
  `expiry_date` date DEFAULT NULL COMMENT 'Expiry Date',
  `business_commencement_date` date DEFAULT NULL COMMENT 'Business Commencement Date',
  `business_incorporation_date` date DEFAULT NULL COMMENT 'Business Incorporation Date',
  `years_in_business` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'Years in Business',
  `incorporation_country_id` char(36) DEFAULT NULL,
  `ntn` varchar(30) DEFAULT NULL COMMENT 'NTN Number (if available)',
  `sales_tax_registration_number` varchar(40) DEFAULT NULL COMMENT 'Sales Tax Registration No.',
  `chamber_membership_number` varchar(60) DEFAULT NULL COMMENT 'Membership Number of Chamber of Commerce/Trade Body',
  `tax_exempt_on_cash_withdrawal` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Tax Exemption: On Cash Withdrawal',
  `tax_exempt_on_profit` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Tax Exemption: On Profit (Tax Exemption Certificate from FBR/CBR)',
  `parent_company_name` varchar(200) DEFAULT NULL COMMENT 'Parent Company/Group Name',
  `group_companies` text DEFAULT NULL COMMENT 'Other Companies of the Group',
  `main_geographic_area` varchar(200) DEFAULT NULL COMMENT 'Main Geographic Area of Activity',
  `outside_pakistan_country` varchar(100) DEFAULT NULL COMMENT 'If Outside Pakistan -> Country',
  `outside_pakistan_province` varchar(100) DEFAULT NULL COMMENT 'If Outside Pakistan -> Province/State',
  `number_of_employees` int(10) UNSIGNED DEFAULT NULL COMMENT 'Number of employees',
  `is_dnfbp` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'AOF-Ind p.15: "IS A DNFBP?" (Designated Non-Financial Business and Professions)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_special_category`
--

CREATE TABLE `customer_special_category` (
  `customer_id` char(36) NOT NULL,
  `special_category_id` char(36) NOT NULL,
  `extra_value` varchar(100) DEFAULT NULL COMMENT 'Non-Resident => "(in years) Time since residing out side Pakistan"',
  `other_description` varchar(150) DEFAULT NULL COMMENT 'Others => "(Please Specify)"',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_tax_residencies`
--

CREATE TABLE `customer_tax_residencies` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `country_id` char(36) NOT NULL,
  `tin` varchar(50) DEFAULT NULL COMMENT 'TIN/NTN',
  `no_tin_reason` enum('A','B','C') DEFAULT NULL COMMENT 'If no TIN available enter Reason A, B or C',
  `reason_b_explanation` text DEFAULT NULL COMMENT '"Please explain why you are unable to obtain a TIN if you selected Reason B"',
  `tin_document_path` varchar(255) DEFAULT NULL COMMENT 'Entity AOF: "Please provide copy of TIN Card OR NTN (National Tax Number)/Tax document"',
  `sequence` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Grid ki row number',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `region_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `region_id`, `name`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Muzaffarabad', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(2, 1, 'Neelum', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(3, 1, 'Jhelum Valley', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(4, 2, 'Bagh', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(5, 2, 'Poonch', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(6, 2, 'Haveli', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(7, 2, 'Sudhanoti', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(8, 3, 'Kotli', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(9, 4, 'Mirpur', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(10, 4, 'Bhimber', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`id`, `name`, `short_name`, `created_at`, `updated_at`) VALUES
(1, 'Islamic Banking Division', 'IBD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(2, 'Operations Division', 'OD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(3, 'Credit Management Division', 'CMD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(4, 'Human Resource Management Division', 'HRMD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(5, 'Special Asset Management Division', 'SAM', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(6, 'Compliance Division', 'CD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(7, 'Treasury Management Division', 'TMD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(8, 'Financial Control Division', 'FCD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(9, 'Information Technology Division', 'ITD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(10, 'Commercial & Retail Banking Division', 'CRBD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(11, 'Credit Administration Division', 'CAD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(12, 'Risk Management Division', 'RMD', '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(13, 'Audit & Inspection Division', 'AID', '2025-12-12 11:40:29', '2025-12-12 11:40:29');

-- --------------------------------------------------------

--
-- Table structure for table `document_requirements`
--

CREATE TABLE `document_requirements` (
  `id` char(36) NOT NULL,
  `document_type_id` char(36) NOT NULL,
  `customer_category_id` char(36) NOT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `applies_when` varchar(255) DEFAULT NULL COMMENT 'Conditional bullet, e.g. "profession = HOUSEWIFE", "special_category = MINOR", "is_pep = true"',
  `notes` text DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_requirements`
--

INSERT INTO `document_requirements` (`id`, `document_type_id`, `customer_category_id`, `is_mandatory`, `applies_when`, `notes`, `sort_order`, `created_at`, `updated_at`) VALUES
('01a13aef-1901-7011-9eaf-148a249cb6dc', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1902-727d-b409-b73e2ad743c2', '01a13aef-18e4-73bc-860d-73e3096c616f', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'profession = HOUSEWIFE', NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1903-7069-af82-edc1c2f2823d', '01a13aef-18e5-7243-aa64-62cab44c6071', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'profession = AGRICULTURE', NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1903-7069-af82-edc1c31bf49e', '01a13aef-18e4-73bc-860d-73e309ce21ed', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'profession IN (HOUSEWIFE, PROF_STUDENT, UNEMPLOYED)', NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1904-70b7-8423-19251fb3850c', '01a13aef-18e5-7243-aa64-62cab3d167ef', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'profession IN (GOVT_SERVICE, PRIVATE_SERVICE)', NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1905-7151-a2b3-85c9d1410174', '01a13aef-18e6-7306-9f75-b959cf13115a', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'income_source = STOCK_INVESTMENT', NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1906-7127-8297-d55988b82a33', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'profession = SELF_EMPLOYED', NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1906-7127-8297-d5598992a213', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1907-7327-9f5e-2ce65c5b19fe', '01a13aef-18f6-7108-89da-d98fa99af237', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'signature shaky/immature or photo account', NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1907-7327-9f5e-2ce65c82c845', '01a13aef-18f5-7320-af84-41996810a6f6', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'foreigner residing in Pakistan', NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1908-71d0-8429-a59a434cfbc4', '01a13aef-18f6-7108-89da-d98fa98f6e40', '01a13aef-1865-7302-a4ec-e31d1fc8ff9e', 0, 'signature other than English/Urdu', NULL, 11, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1909-7317-84b4-b128744e75f2', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1866-709e-8cd8-5c8f8d966f63', 1, 'CDD on all joint account holders as if each were an individual customer', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1909-7317-84b4-b12875175fa5', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1866-709e-8cd8-5c8f8d966f63', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190a-7031-ab4a-50691a782635', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1865-7302-a4ec-e31d207c72f5', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190b-7032-a695-34dee638a4c2', '01a13aef-18e8-7344-8f47-47c093da69f2', '01a13aef-1865-7302-a4ec-e31d207c72f5', 1, 'registered concerns', NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190b-7032-a695-34dee731dfcd', '01a13aef-18e8-7344-8f47-47c09448a748', '01a13aef-1865-7302-a4ec-e31d207c72f5', 0, 'wherever applicable', NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190c-70ce-b136-39d2686953cd', '01a13aef-18e9-7024-8fbd-d2ff97e0a34d', '01a13aef-1865-7302-a4ec-e31d207c72f5', 0, 'wherever applicable', NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190c-70ce-b136-39d268adfa5a', '01a13aef-18e9-7024-8fbd-d2ff98347aa5', '01a13aef-1865-7302-a4ec-e31d207c72f5', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190d-72a1-88dc-94188ba7c842', '01a13aef-18ea-705b-bf66-6f4f67146039', '01a13aef-1865-7302-a4ec-e31d207c72f5', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190e-735d-96d5-0618f241e8f5', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1865-7302-a4ec-e31d207c72f5', 1, NULL, NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190e-735d-96d5-0618f3363914', '01a13aef-18eb-7095-a307-3d33e828645f', '01a13aef-1865-7302-a4ec-e31d207c72f5', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190f-7295-ad3d-df39dc96da4d', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1865-7302-a4ec-e31d207c72f5', 1, NULL, NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-190f-7295-ad3d-df39dd1483a6', '01a13aef-18eb-7095-a307-3d33e92112b1', '01a13aef-1865-7302-a4ec-e31d207c72f5', 0, 'where applicable', NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1910-739d-aa1e-d3692979a5c8', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, 'All partners and authorized signatories', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1911-72c5-baa5-7d4c6d0a7f1b', '01a13aef-18f7-72a9-9582-8e814a12183e', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1911-72c5-baa5-7d4c6dbf1411', '01a13aef-18f7-72a9-9582-8e814aa9da47', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, 'Unregistered partnership must be stated on the form', NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1912-7165-a3c5-67492cb8632c', '01a13aef-18f8-7251-a61f-75ef10fdabb3', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1912-7165-a3c5-67492cff6398', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1913-72c3-9d26-51c117f2285a', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1913-72c3-9d26-51c118cbad2e', '01a13aef-18f1-70ff-8c98-2ad30f9e75bd', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, 'Source of fund / proof of income of the UBO', NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1914-73d8-919e-8ed0659a4f1b', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1914-73d8-919e-8ed065a0b71e', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-1867-72e8-a0fa-882fa8239621', 1, 'Company rubber stamp affixed with partner sign', NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1914-73d8-919e-8ed065b4e044', '01a13aef-18f8-7251-a61f-75ef1116b3a6', '01a13aef-1867-72e8-a0fa-882fa8239621', 0, 'Where applicable', NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1915-7304-afe4-fdedce44ec18', '01a13aef-18ec-7280-a8ba-ecbfb8de8939', '01a13aef-1868-7102-84a1-7429b83d733f', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1916-70a2-bb7b-74648eddfd5e', '01a13aef-18ec-7280-a8ba-ecbfb80cf995', '01a13aef-1868-7102-84a1-7429b83d733f', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1916-70a2-bb7b-74648fca1b1c', '01a13aef-18fa-71ae-a615-e9841b70fe87', '01a13aef-1868-7102-84a1-7429b83d733f', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1917-73aa-abd4-42f414c26e9c', '01a13aef-18fa-71ae-a615-e9841c0bdf75', '01a13aef-1868-7102-84a1-7429b83d733f', 1, 'Wherever applicable', NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1917-73aa-abd4-42f415ab20d5', '01a13aef-18fb-71d9-ab5c-931ae89ab2f0', '01a13aef-1868-7102-84a1-7429b83d733f', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1918-72c1-b1e1-c7861f2e1829', '01a13aef-18fb-71d9-ab5c-931ae8c9cc3b', '01a13aef-1868-7102-84a1-7429b83d733f', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1918-72c1-b1e1-c7861fd027ff', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1868-7102-84a1-7429b83d733f', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1918-72c1-b1e1-c7861ff1e4f0', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1868-7102-84a1-7429b83d733f', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1919-7127-b6b8-0177417fb05b', '01a13aef-18fc-72f2-9ed2-a7a3d28157e9', '01a13aef-1868-7102-84a1-7429b83d733f', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1919-7127-b6b8-01774212b2bf', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-1868-7102-84a1-7429b83d733f', 1, 'With company secretary signature', NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191a-70e8-913f-a6c9d9ffd180', '01a13aef-18f1-70ff-8c98-2ad30f9e75bd', '01a13aef-1868-7102-84a1-7429b83d733f', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191a-70e8-913f-a6c9dab78b25', '01a13aef-18fc-72f2-9ed2-a7a3d30ef723', '01a13aef-1868-7102-84a1-7429b83d733f', 0, NULL, NULL, 12, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191b-713d-b042-d95b3f59f1be', '01a13aef-18fd-71dc-acf4-57117fe85d51', '01a13aef-1868-7102-84a1-7429b83d733f', 0, 'Where applicable', NULL, 13, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191b-713d-b042-d95b400aab16', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1868-7102-84a1-7429b83d733f', 1, NULL, NULL, 14, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191c-7382-bd2c-3dd375397d55', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1868-7102-84a1-7429b83d733f', 1, NULL, NULL, 15, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191c-7382-bd2c-3dd3762aaef3', '01a13aef-18f8-7251-a61f-75ef1116b3a6', '01a13aef-1868-7102-84a1-7429b83d733f', 0, 'Where applicable', NULL, 16, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191d-7216-9b9d-b3d2f32d6b84', '01a13aef-18fd-71dc-acf4-57118052ab0c', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, 'Newly registered branch or liaison office', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191d-7216-9b9d-b3d2f3a344e4', '01a13aef-18fe-73ad-887d-42edddd7b8f3', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, 'Already registered foreign company', NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191e-7104-80b2-d63e1e6c12eb', '01a13aef-18fe-73ad-887d-42edddda9a33', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191e-7104-80b2-d63e1f3bdf64', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, 'All signatories of the account', NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191f-7208-8daa-359e4ed48dab', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-191f-7208-8daa-359e4f3a6e21', '01a13aef-18fb-71d9-ab5c-931ae89ab2f0', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1920-739e-9125-04c139f05c8c', '01a13aef-18ff-72f1-806f-b69aac8c35a0', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, NULL, NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1920-739e-9125-04c13a54ee2a', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, 'With company secretary signature', NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1921-700b-b75b-f422d979d262', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, NULL, NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1921-700b-b75b-f422da520fb9', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1870-71a7-bd30-60dd17c691c5', 1, NULL, NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1922-72c1-b556-3009f52d297c', '01a13aef-1900-711c-ac92-f04fe8dad9a6', '01a13aef-186a-70c0-8278-a2cb42b21690', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1922-72c1-b556-3009f58bdace', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186a-70c0-8278-a2cb42b21690', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1923-70a4-8cd2-686d49038664', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186a-70c0-8278-a2cb42b21690', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1924-7014-b2cc-5324cee155b5', '01a13aef-18ff-72f1-806f-b69aacc41db7', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1924-7014-b2cc-5324cf86eb09', '01a13aef-18ec-7280-a8ba-ecbfb80cf995', '01a13aef-186d-7155-b85a-e464d3603e03', 1, 'By-laws / Rules and Regulations', NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1925-731c-9c5e-081a755b7c55', '01a13aef-18ec-7280-a8ba-ecbfb8de8939', '01a13aef-186d-7155-b85a-e464d3603e03', 1, 'Resolution of the Governing body / Board of Trustees / Executive Committee', NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1925-731c-9c5e-081a75e44c00', '01a13aef-18ed-712b-a8c2-72ed33430e51', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1926-7145-a358-4d4ffd189f15', '01a13aef-18ed-712b-a8c2-72ed34281a2e', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1926-7145-a358-4d4ffdb5633e', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1926-7145-a358-4d4ffdb5d72a', '01a13aef-18ee-71bb-80d8-ebe95570477f', '01a13aef-186d-7155-b85a-e464d3603e03', 0, NULL, NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1927-70d3-835b-ef0280653312', '01a13aef-18ef-7324-aee2-83ff9afb272d', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1927-70d3-835b-ef028129e7ec', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1928-7351-ba5d-9458a8229cd6', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1928-7351-ba5d-9458a84a9335', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 11, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1929-72c4-9b98-05d12ae39ddf', '01a13aef-18f1-70ff-8c98-2ad30f9e75bd', '01a13aef-186d-7155-b85a-e464d3603e03', 1, NULL, NULL, 12, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192a-714a-9324-a9d2dc555cba', '01a13aef-18f1-70ff-8c98-2ad30fb99300', '01a13aef-186b-735b-8877-ee080b537b24', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192a-714a-9324-a9d2dc71ca77', '01a13aef-18f2-70e0-ba61-f14db921602a', '01a13aef-186b-735b-8877-ee080b537b24', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192a-714a-9324-a9d2dccf4c69', '01a13aef-18f2-70e0-ba61-f14db93dadcf', '01a13aef-186b-735b-8877-ee080b537b24', 0, 'Armed Forces / allied accounts', NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192b-7093-8f43-da3e0b228355', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186b-735b-8877-ee080b537b24', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192b-7093-8f43-da3e0bef382d', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186b-735b-8877-ee080b537b24', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192c-7030-910d-133f0ed6980e', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-186b-735b-8877-ee080b537b24', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192c-7030-910d-133f0f444f9b', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-186b-735b-8877-ee080b537b24', 1, NULL, NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192d-73c9-bc5b-d6beba171ef4', '01a13aef-18e8-7344-8f47-47c093da69f2', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192e-7095-a6a5-c9873ec8d43d', '01a13aef-18ec-7280-a8ba-ecbfb80cf995', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192e-7095-a6a5-c9873f676fd4', '01a13aef-18ec-7280-a8ba-ecbfb8de8939', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192f-72a3-9a6a-d55cf22c371c', '01a13aef-18ed-712b-a8c2-72ed34281a2e', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-192f-72a3-9a6a-d55cf23b2d9b', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1930-73a7-bb5c-49dde4ecbb35', '01a13aef-18ee-71bb-80d8-ebe95570477f', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 0, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1930-73a7-bb5c-49dde586638f', '01a13aef-18ee-71bb-80d8-ebe9558d1074', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 0, 'International NGO receiving funds from abroad', NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1930-73a7-bb5c-49dde6109af6', '01a13aef-18ef-7324-aee2-83ff9afb272d', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1931-72b5-9683-9d94c9f69abe', '01a13aef-18ed-712b-a8c2-72ed33430e51', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1931-72b5-9683-9d94ca9b90af', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1932-722b-b1f0-0492e44e1047', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 11, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1932-722b-b1f0-0492e4e9fd54', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 12, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1933-7335-91a2-6a9ce1ab0494', '01a13aef-18f4-72c8-9d04-0d1e4def05ae', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 0, 'wherever applicable', NULL, 13, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1933-7335-91a2-6a9ce2232f50', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 14, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1934-722b-bc78-f6250d710655', '01a13aef-18f1-70ff-8c98-2ad30f9e75bd', '01a13aef-1870-71a7-bd30-60dd17cddd3f', 1, NULL, NULL, 15, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1934-722b-bc78-f6250e06fe1c', '01a13aef-18f3-7057-8db6-5b76dee812ad', '01a13aef-1871-737e-9460-3205b262f7d0', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1935-72be-a456-c69e325a9cd2', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1871-737e-9460-3205b262f7d0', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1936-717f-b18c-ceecbb4b24f7', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1871-737e-9460-3205b262f7d0', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1936-717f-b18c-ceecbb97f8a5', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1871-737e-9460-3205b262f7d0', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1937-7227-b61f-13722319fb15', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1871-737e-9460-3205b262f7d0', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1938-717d-9174-b4b44eeb513e', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1872-71ab-9c8e-95594fc6d081', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1938-717d-9174-b4b44fb3bee6', '01a13aef-18f3-7057-8db6-5b76df7f03e2', '01a13aef-1872-71ab-9c8e-95594fc6d081', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1939-707a-b3f8-68a30ca8371c', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1872-71ab-9c8e-95594fc6d081', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1939-707a-b3f8-68a30d6f5c18', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1872-71ab-9c8e-95594fc6d081', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193a-7022-8d02-6bad1097198e', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1872-71ab-9c8e-95594fc6d081', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193a-7022-8d02-6bad10d48661', '01a13aef-18ed-712b-a8c2-72ed33430e51', '01a13aef-186d-7155-b85a-e464d35dda68', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193b-7059-88e2-d084e859ad18', '01a13aef-18ed-712b-a8c2-72ed34281a2e', '01a13aef-186d-7155-b85a-e464d35dda68', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193b-7059-88e2-d084e92c3b7f', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186d-7155-b85a-e464d35dda68', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193c-7049-a16f-ea1c77ec80d8', '01a13aef-18ee-71bb-80d8-ebe95570477f', '01a13aef-186d-7155-b85a-e464d35dda68', 0, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193c-7049-a16f-ea1c783a9165', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-186d-7155-b85a-e464d35dda68', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193d-7250-9623-dec2bc54d512', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186d-7155-b85a-e464d35dda68', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193d-7250-9623-dec2bd435f0b', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-186d-7155-b85a-e464d35dda68', 1, NULL, NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193e-72fa-bd03-90bd8138df0a', '01a13aef-18ed-712b-a8c2-72ed33430e51', '01a13aef-186c-73e8-8dbc-b6d9c4b048da', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193e-72fa-bd03-90bd81fc8188', '01a13aef-18ed-712b-a8c2-72ed34281a2e', '01a13aef-186c-73e8-8dbc-b6d9c4b048da', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193f-72a9-bb04-7520f6f08eb4', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186c-73e8-8dbc-b6d9c4b048da', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-193f-72a9-bb04-7520f7930d88', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-186c-73e8-8dbc-b6d9c4b048da', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1940-709b-a957-b9569ba4090f', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186c-73e8-8dbc-b6d9c4b048da', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1940-709b-a957-b9569bc36fd2', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-186c-73e8-8dbc-b6d9c4b048da', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1941-7127-be13-318abc47167f', '01a13aef-18ed-712b-a8c2-72ed33430e51', '01a13aef-186e-72ef-a165-2d7e5755b8c8', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1941-7127-be13-318abd1bdf20', '01a13aef-18ed-712b-a8c2-72ed34281a2e', '01a13aef-186e-72ef-a165-2d7e5755b8c8', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1942-73c6-8dca-c1892271d3e3', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186e-72ef-a165-2d7e5755b8c8', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1942-73c6-8dca-c18922fbb6c2', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-186e-72ef-a165-2d7e5755b8c8', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1943-7182-b6f9-6bb3dc5f56c9', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186e-72ef-a165-2d7e5755b8c8', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1943-7182-b6f9-6bb3dd59c00b', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-186e-72ef-a165-2d7e5755b8c8', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1944-7015-848b-9ceceb8e40a8', '01a13aef-18ec-7280-a8ba-ecbfb8de8939', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1944-7015-848b-9cecec1e8f02', '01a13aef-18ec-7280-a8ba-ecbfb80cf995', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1945-70bc-b95e-83884ba27c2f', '01a13aef-18fa-71ae-a615-e9841b70fe87', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1945-70bc-b95e-83884c87327e', '01a13aef-18fa-71ae-a615-e9841c0bdf75', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, 'Wherever applicable', NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1946-72ff-a01e-8aef351f722b', '01a13aef-18fb-71d9-ab5c-931ae89ab2f0', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1946-72ff-a01e-8aef3525c9f6', '01a13aef-18fb-71d9-ab5c-931ae8c9cc3b', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1947-7255-b9af-ab93d243ed73', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1947-7255-b9af-ab93d2b2fe3b', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1948-7233-ab68-c7da215c2a72', '01a13aef-18fc-72f2-9ed2-a7a3d28157e9', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1948-7233-ab68-c7da220482b3', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, 'With company secretary signature', NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1949-72a0-9875-e9f279d2992f', '01a13aef-18f1-70ff-8c98-2ad30f9e75bd', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1949-72a0-9875-e9f27a7b62c6', '01a13aef-18fc-72f2-9ed2-a7a3d30ef723', '01a13aef-1869-736b-ba60-e4daf501ce30', 0, NULL, NULL, 12, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194a-70c1-86e7-c8b3041d5ade', '01a13aef-18fd-71dc-acf4-57117fe85d51', '01a13aef-1869-736b-ba60-e4daf501ce30', 0, 'Where applicable', NULL, 13, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194a-70c1-86e7-c8b304f4a22d', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, NULL, NULL, 14, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194a-70c1-86e7-c8b30595da20', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1869-736b-ba60-e4daf501ce30', 1, NULL, NULL, 15, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194b-7074-ad37-c55f3dcf24d5', '01a13aef-18f8-7251-a61f-75ef1116b3a6', '01a13aef-1869-736b-ba60-e4daf501ce30', 0, 'Where applicable', NULL, 16, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194c-7120-8a2c-456997338928', '01a13aef-18ec-7280-a8ba-ecbfb8de8939', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194d-737d-86f1-ad6d3fd76163', '01a13aef-18ec-7280-a8ba-ecbfb80cf995', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194d-737d-86f1-ad6d3ff1b6af', '01a13aef-18fa-71ae-a615-e9841b70fe87', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194d-737d-86f1-ad6d40c4222a', '01a13aef-18fa-71ae-a615-e9841c0bdf75', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, 'Wherever applicable', NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194e-73d2-bb17-ca5f2ae327b5', '01a13aef-18fb-71d9-ab5c-931ae89ab2f0', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194e-73d2-bb17-ca5f2aff409c', '01a13aef-18fb-71d9-ab5c-931ae8c9cc3b', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194f-71e0-abb1-2766f26d5f31', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-194f-71e0-abb1-2766f2fcc71d', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1950-71ba-9bd6-eba95455574d', '01a13aef-18fc-72f2-9ed2-a7a3d28157e9', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1950-71ba-9bd6-eba954ba34dc', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, 'With company secretary signature', NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1951-7207-89fb-5db8c70a88b9', '01a13aef-18f1-70ff-8c98-2ad30f9e75bd', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1951-7207-89fb-5db8c7ceef54', '01a13aef-18fc-72f2-9ed2-a7a3d30ef723', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 0, NULL, NULL, 12, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1952-711c-9508-71db11be762a', '01a13aef-18fd-71dc-acf4-57117fe85d51', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 0, 'Where applicable', NULL, 13, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1952-711c-9508-71db11dda85f', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, NULL, NULL, 14, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1953-724c-aa7b-132ebaf41929', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 1, NULL, NULL, 15, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1953-724c-aa7b-132ebb9bcfd4', '01a13aef-18f8-7251-a61f-75ef1116b3a6', '01a13aef-1869-736b-ba60-e4daf5d3d52f', 0, 'Where applicable', NULL, 16, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1955-7261-bc05-de451410e6bb', '01a13aef-18ec-7280-a8ba-ecbfb8de8939', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1957-72c5-9778-3a0e3dd567d0', '01a13aef-18ec-7280-a8ba-ecbfb80cf995', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1957-72c5-9778-3a0e3e272532', '01a13aef-18fa-71ae-a615-e9841b70fe87', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1958-717e-a968-8e1f46cbbf3c', '01a13aef-18fa-71ae-a615-e9841c0bdf75', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, 'Wherever applicable', NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1958-717e-a968-8e1f478eb987', '01a13aef-18fb-71d9-ab5c-931ae89ab2f0', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1959-7310-9fd1-60ddf16f7516', '01a13aef-18fb-71d9-ab5c-931ae8c9cc3b', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1959-7310-9fd1-60ddf2526836', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-195b-726e-a670-536e5a04b17f', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-195c-7103-a325-b7732b626872', '01a13aef-18fc-72f2-9ed2-a7a3d28157e9', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-195c-7103-a325-b7732c5e123a', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, 'With company secretary signature', NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-195d-709f-964d-2386a36ad1cb', '01a13aef-18f1-70ff-8c98-2ad30f9e75bd', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-195e-713e-abf0-3ab1890110e2', '01a13aef-18fc-72f2-9ed2-a7a3d30ef723', '01a13aef-186a-70c0-8278-a2cb4220fa62', 0, NULL, NULL, 12, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-195e-713e-abf0-3ab189864884', '01a13aef-18fd-71dc-acf4-57117fe85d51', '01a13aef-186a-70c0-8278-a2cb4220fa62', 0, 'Where applicable', NULL, 13, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-195f-7056-bb06-5533d29da123', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, NULL, NULL, 14, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-195f-7056-bb06-5533d3462766', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-186a-70c0-8278-a2cb4220fa62', 1, NULL, NULL, 15, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1960-709e-8702-365644b2cb71', '01a13aef-18f8-7251-a61f-75ef1116b3a6', '01a13aef-186a-70c0-8278-a2cb4220fa62', 0, 'Where applicable', NULL, 16, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1961-7138-ba9c-1a33686acde9', '01a13aef-18ec-7280-a8ba-ecbfb8de8939', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1961-7138-ba9c-1a3368932f97', '01a13aef-18ec-7280-a8ba-ecbfb80cf995', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1961-7138-ba9c-1a3369642bca', '01a13aef-18fa-71ae-a615-e9841b70fe87', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1962-70a1-9990-9fd047942bba', '01a13aef-18fa-71ae-a615-e9841c0bdf75', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, 'Wherever applicable', NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1962-70a1-9990-9fd047ce3ea6', '01a13aef-18fb-71d9-ab5c-931ae89ab2f0', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1963-704e-a958-cbd9baf206a5', '01a13aef-18fb-71d9-ab5c-931ae8c9cc3b', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1963-704e-a958-cbd9bbb4db7a', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1964-7154-b4ce-92bfb959f8ab', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, NULL, NULL, 8, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1964-7154-b4ce-92bfb9cd7ed6', '01a13aef-18fc-72f2-9ed2-a7a3d28157e9', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1965-72fe-836e-d2552ad2a4ea', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, 'With company secretary signature', NULL, 10, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1965-72fe-836e-d2552ada7fa2', '01a13aef-18f1-70ff-8c98-2ad30f9e75bd', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1966-715f-9bff-23cf4860020d', '01a13aef-18fc-72f2-9ed2-a7a3d30ef723', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 0, NULL, NULL, 12, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1966-715f-9bff-23cf493d891b', '01a13aef-18fd-71dc-acf4-57117fe85d51', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 0, 'Where applicable', NULL, 13, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1967-7151-9161-66625908fd2c', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, NULL, NULL, 14, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1967-7151-9161-6662593e8f88', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 1, NULL, NULL, 15, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1968-7357-999c-4e846a547a03', '01a13aef-18f8-7251-a61f-75ef1116b3a6', '01a13aef-186f-70f0-b4d6-1deab3f40aa4', 0, 'Where applicable', NULL, 16, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1968-7357-999c-4e846abb2418', '01a13aef-18ed-712b-a8c2-72ed33430e51', '01a13aef-186f-70f0-b4d6-1deab31ce6a8', 1, NULL, NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1969-7161-9439-a25bacfb43a3', '01a13aef-18ed-712b-a8c2-72ed34281a2e', '01a13aef-186f-70f0-b4d6-1deab31ce6a8', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1969-7161-9439-a25baddc9375', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-186f-70f0-b4d6-1deab31ce6a8', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196a-7154-a763-9a311b02d940', '01a13aef-18ef-7324-aee2-83ff9b35d9da', '01a13aef-186f-70f0-b4d6-1deab31ce6a8', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196a-7154-a763-9a311b29506a', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-186f-70f0-b4d6-1deab31ce6a8', 1, NULL, NULL, 5, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196b-7002-9024-5150d7a6e5ec', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-186f-70f0-b4d6-1deab31ce6a8', 1, NULL, NULL, 6, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196b-7002-9024-5150d812f601', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1867-72e8-a0fa-882fa7e6086c', 1, 'Important Notes (b): attested after original seen', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196c-7361-8eae-0c745e4b5386', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1867-72e8-a0fa-882fa7e6086c', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196c-7361-8eae-0c745f2ab904', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1867-72e8-a0fa-882fa7e6086c', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196d-717a-b99b-440d5d5eb74b', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1867-72e8-a0fa-882fa7e6086c', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196d-717a-b99b-440d5e24dec6', '01a13aef-18e1-73e7-aa89-825bddb6b58b', '01a13aef-1872-71ab-9c8e-95594fe2bc1c', 1, 'Important Notes (b): attested after original seen', NULL, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196e-70bc-982a-25e7a637b4a0', '01a13aef-18f0-7225-81bc-99769bce0d08', '01a13aef-1872-71ab-9c8e-95594fe2bc1c', 1, NULL, NULL, 2, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196e-70bc-982a-25e7a69e14c1', '01a13aef-18e6-7306-9f75-b959cf42d1f1', '01a13aef-1872-71ab-9c8e-95594fe2bc1c', 1, NULL, NULL, 3, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-196f-7284-9a90-0ff6b285c1e8', '01a13aef-18e7-7025-bbbf-afa269bc7a3c', '01a13aef-1872-71ab-9c8e-95594fe2bc1c', 1, NULL, NULL, 4, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `id` char(36) NOT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(220) NOT NULL,
  `name_ur` varchar(220) DEFAULT NULL,
  `requires_attestation` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Important Note (b): Gazetted officer/Nazim/Administrator/Bank officer se attested "after original seen"',
  `description` text DEFAULT NULL COMMENT 'Form ka bullet text reference ke liye',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`id`, `code`, `name`, `name_ur`, `requires_attestation`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18e1-73e7-aa89-825bddb6b58b', 'ID_DOC_COPY', 'Photocopy of valid identity document (CNIC/SNIC/NICOP/POC/ARC/PoR/Passport)', NULL, 1, NULL, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e2-708b-b6fe-808bd6b2c503', 'FORM_B_CRC', 'Form-B / Birth Certificate / Student ID card of minor', NULL, 1, NULL, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e3-732a-aeb5-e4482fd0712b', 'GUARDIAN_ID', 'Photocopy of identity document of the guardian of the minor', NULL, 1, NULL, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e4-73bc-860d-73e3096c616f', 'SOURCE_OF_FUNDS_DECL', 'Declaration for source of funds', NULL, 0, NULL, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e4-73bc-860d-73e309ce21ed', 'PROOF_OF_INCOME', 'Proof of income (salary slip / utility bill / rent agreement / aqama etc.)', NULL, 0, NULL, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e5-7243-aa64-62cab3d167ef', 'SALARY_SLIP_APPOINTMENT', 'Salary slip, copy of appointment letter or letter from employer', NULL, 0, NULL, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e5-7243-aa64-62cab44c6071', 'LANDHOLDING_EVIDENCE', 'Landholding record / Passbook / Form 7 / Form 2 / Dhal receipt / Khasra-Khatooni / Land Agreement', NULL, 0, NULL, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e6-7306-9f75-b959cf13115a', 'STOCK_TRADING_SHEET', 'Trading sheet of stock investor', NULL, 0, NULL, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e6-7306-9f75-b959cf42d1f1', 'PHYSICAL_VERIFICATION', 'Physical verification report', NULL, 0, NULL, 9, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e7-7025-bbbf-afa269bc7a3c', 'SS_UNDERTAKING', 'Undertaking for Acceptance of Different Specimen Signatures for Account Operation', NULL, 0, NULL, 10, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e8-7344-8f47-47c093da69f2', 'REG_CERTIFICATE', 'Registration certificate for registered concerns / Certificate of Incorporation', NULL, 1, NULL, 11, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e8-7344-8f47-47c09448a748', 'SALES_TAX_NTN', 'Sales tax registration or NTN', NULL, 0, NULL, 12, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e9-7024-8fbd-d2ff97e0a34d', 'TRADE_BODY_MEMBERSHIP', 'Certificate or proof of membership of trade bodies', NULL, 0, NULL, 13, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18e9-7024-8fbd-d2ff98347aa5', 'SOLE_PROP_DECLARATION', 'Declaration of sole proprietorship on business letter head including Source of Fund', NULL, 0, NULL, 14, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ea-705b-bf66-6f4f67146039', 'ACCOUNT_REQUISITION_LETTERHEAD', 'Account opening requisition on business letter head', NULL, 0, NULL, 15, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18eb-7095-a307-3d33e828645f', 'UBO_DECLARATION', 'Ultimate Beneficial Owner (UBO) Declaration Form', NULL, 0, NULL, 16, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18eb-7095-a307-3d33e92112b1', 'RENTAL_OWNERSHIP_DOC', 'Rental/Ownership document such as Rental Agreement or Lease document of business place', NULL, 0, NULL, 17, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ec-7280-a8ba-ecbfb80cf995', 'MEMORANDUM_ARTICLES', 'Memorandum & Article of Association / By-laws / Rules and Regulations', NULL, 1, NULL, 18, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ec-7280-a8ba-ecbfb8de8939', 'BOARD_RESOLUTION', 'Resolution of the Board of Directors/Trustees/Executive Committee for opening of account', NULL, 0, NULL, 19, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ed-712b-a8c2-72ed33430e51', 'LIST_OF_TRUSTEES', 'List of trustees/members/governing body on letterhead', NULL, 0, NULL, 20, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ed-712b-a8c2-72ed34281a2e', 'NPO_DECLARATION_FORM', 'Declaration form for NPOs/NGOs/Charities, Trust, Clubs, Societies and Associations signed by Governing Body', NULL, 0, NULL, 21, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ee-71bb-80d8-ebe95570477f', 'LATEST_FINANCIALS', 'Latest financial statement including audit report', NULL, 0, NULL, 22, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ee-71bb-80d8-ebe9558d1074', 'EAD_APPROVAL', 'Approval of EAD, Government of Pakistan (International NGO receiving funds from abroad)', NULL, 0, NULL, 23, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ef-7324-aee2-83ff9afb272d', 'DECLARATION_QUESTIONNAIRE', 'Declaration statement / questionnaire', NULL, 0, NULL, 24, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ef-7324-aee2-83ff9b35d9da', 'RUBBER_STAMP', 'Rubber stamp along with secretary/Member signature affixed on all documents', NULL, 0, NULL, 25, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f0-7225-81bc-99769bce0d08', 'CIF_ALL_SIGNATORIES', 'CIF of all members and authorized signatories', NULL, 0, NULL, 26, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f1-70ff-8c98-2ad30f9e75bd', 'SOURCE_OF_FUND_GOVERNING_BODY', 'Source of Fund / proof of income of Governing Body/Board of Trustees/Controlling Persons', NULL, 0, NULL, 27, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f1-70ff-8c98-2ad30fb99300', 'GOVT_AUTHORITY_LETTER', 'Resolution/Authority letter from concerned department duly endorsed by Admin/Finance Division', NULL, 0, NULL, 28, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f2-70e0-ba61-f14db921602a', 'FUND_ALLOCATION_LETTER', 'Fund allocation letter issued by concerned authority', NULL, 0, NULL, 29, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f2-70e0-ba61-f14db93dadcf', 'GAZETTE_NOTIFICATION', 'Authenticated list of authorized individuals along with the gazette notification', NULL, 0, NULL, 30, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f3-7057-8db6-5b76dee812ad', 'POWER_OF_ATTORNEY', 'Certified copy of Power of Attorney or Agency Agreement', NULL, 1, NULL, 31, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f3-7057-8db6-5b76df7f03e2', 'LETTER_OF_ADMINISTRATION', 'Certified copy of Letter of Administration or Probate', NULL, 1, NULL, 32, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f4-72c8-9d04-0d1e4def05ae', 'TAX_EXEMPTION_CERTIFICATE', 'Tax exemption certificate', NULL, 0, NULL, 33, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f4-72c8-9d04-0d1e4e36a9ec', 'PEP_FORM', 'PEP Form approved from Head Office', NULL, 0, NULL, 34, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f5-7320-af84-41996810a6f6', 'QA22_FORM', 'QA-22 form (foreigner residing in Pakistan)', NULL, 0, NULL, 35, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f6-7108-89da-d98fa98f6e40', 'VERNACULAR_FORM', 'Vernacular form on 100 rupees bond paper (signature other than English/Urdu)', NULL, 0, NULL, 36, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f6-7108-89da-d98fa99af237', 'PASSPORT_PHOTOS', 'Two passport sized attested photographs (shaky/immature signature case)', NULL, 1, NULL, 37, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f7-72a9-9582-8e814a12183e', 'PARTNERSHIP_DEED', 'Attested copy of \"Partnership Deed\" duly signed by all partners of the firm', NULL, 1, NULL, 38, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f7-72a9-9582-8e814aa9da47', 'REGISTRAR_OF_FIRMS_CERT', 'Attested copy of Registration Certificate with Registrar of Firms (unregistered partnership must be stated on the account opening form)', NULL, 1, NULL, 39, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f8-7251-a61f-75ef10fdabb3', 'PARTNERS_AUTHORITY_LETTER', 'Authority letter from all partners, in original, authorizing the person(s) to operate the firm\'s account', NULL, 0, NULL, 40, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f8-7251-a61f-75ef1116b3a6', 'CHAMBER_MEMBERSHIP_CERT', 'Membership Certificate — Chamber of Commerce \"Where Applicable\"', NULL, 0, NULL, 41, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f8-7251-a61f-75ef11499985', 'LLP_AGREEMENT', 'Limited Liability Partnership Deed / Agreement', NULL, 1, NULL, 42, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f9-72a8-aed3-aad53e406e67', 'LLP_FORM_III', 'LLP-Form-III (initial particulars of partners / designated partner in case of newly incorporated LLP)', NULL, 1, NULL, 43, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18f9-72a8-aed3-aad53e5f0094', 'LLP_FORM_V', 'LLP-Form-V (change in partners / designated partner in case of already incorporated LLP)', NULL, 1, NULL, 44, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fa-71ae-a615-e9841b70fe87', 'CERT_OF_INCORPORATION', 'Certificate of Incorporation', NULL, 1, NULL, 45, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fa-71ae-a615-e9841c0bdf75', 'CERT_COMMENCEMENT_BUSINESS', 'Certificate of commencement of business, wherever applicable', NULL, 1, NULL, 46, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fb-71d9-ab5c-931ae89ab2f0', 'LIST_OF_DIRECTORS', 'List of directors required on company letterhead', NULL, 0, NULL, 47, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fb-71d9-ab5c-931ae8c9cc3b', 'FORM_29', 'Form-29 issued under Companies Ordinance 1984, or Annexure IV/INC form II issued under Companies Act 2017 (\"Form-A/Form C\") where applicable', NULL, 1, NULL, 48, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fc-72f2-9ed2-a7a3d28157e9', 'SHAREHOLDER_VERIFICATION_10', 'Identification and verification of natural person shareholders holding 10% or above stake in the entity', NULL, 0, NULL, 49, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fc-72f2-9ed2-a7a3d30ef723', 'RISK_REGISTER_UBO', 'Copy of Risk Register (Register of Beneficial Owners)', NULL, 0, NULL, 50, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fd-71dc-acf4-57117fe85d51', 'SHAREHOLDING_LETTERHEAD', 'Shareholding of the company on company letterhead \"Where Applicable\"', NULL, 0, NULL, 51, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fd-71dc-acf4-57118052ab0c', 'FORM_II_FOREIGN_BRANCH', 'Form II about particulars of directors, Principal Officer etc. for a newly registered branch or liaison office of a foreign company', NULL, 1, NULL, 52, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fe-73ad-887d-42edddd7b8f3', 'FORM_III_FOREIGN_BRANCH', 'Form III about change in directors, principal officers etc. in an already registered foreign company branch or liaison office', NULL, 1, NULL, 53, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18fe-73ad-887d-42edddda9a33', 'BOI_PERMISSION_LETTER', 'Copy of permission letter from the relevant authority i.e. Board of Investment', NULL, 1, NULL, 54, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ff-72f1-806f-b69aac8c35a0', 'PRINCIPAL_OFFICE_LETTER', 'Letter from the Principal Office of the entity authorizing the person(s) to open and operate the account', NULL, 0, NULL, 55, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ff-72f1-806f-b69aacc41db7', 'TRUST_REGISTRATION_CERT', 'Certificate of Registration / Instrument of Trust', NULL, 1, NULL, 56, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1900-711c-ac92-f04fe8dad9a6', 'FOREIGN_EXCHANGE_MANUAL', 'As per Foreign Exchange manual for details on Resident / Non-Resident accounts', NULL, 0, NULL, 57, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `economic_sectors`
--

CREATE TABLE `economic_sectors` (
  `id` char(36) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(150) NOT NULL,
  `name_ur` varchar(150) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `education_levels`
--

CREATE TABLE `education_levels` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'No Education, Below Matric, Matric/O level, Intermediate/A level, Graduate, Postgraduate, Others',
  `name_ur` varchar(80) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `education_levels`
--

INSERT INTO `education_levels` (`id`, `code`, `name`, `name_ur`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1884-706e-9db1-2445007eb020', 'NO_EDUCATION', 'No Education', 'نا خواندہ', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1885-72b6-89f5-132a3607e226', 'BELOW_MATRIC', 'Below Matric', 'میٹرک سے کم', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1886-70dc-91fc-1f4ab053e44e', 'MATRIC_O_LEVEL', 'Matric/O level', 'میٹرک/او لیول', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1886-70dc-91fc-1f4ab14842f9', 'INTER_A_LEVEL', 'Intermediate/A level', 'انٹرمیڈیٹ/اے لیول', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1887-7305-a197-e892a6ca9b5b', 'GRADUATE', 'Graduate', 'گریجویٹ', 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1887-7305-a197-e892a6f81287', 'POSTGRADUATE', 'Postgraduate', 'پوسٹ گریجویٹ', 0, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1888-7041-8e1d-5afc4e2925b7', 'EDU_OTHERS', 'Others', 'دیگر', 1, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_resources`
--

CREATE TABLE `employee_resources` (
  `id` char(36) NOT NULL,
  `division_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `resource_no` varchar(255) DEFAULT NULL,
  `resource_number` varchar(255) NOT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_categories`
--

CREATE TABLE `file_categories` (
  `id` char(36) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` enum('0','1') NOT NULL DEFAULT '1',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `file_categories`
--

INSERT INTO `file_categories` (`id`, `category_code`, `category_name`, `description`, `is_active`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
('04ae0d4f-c8a3-4ef0-9f42-92914dffcb0b', 'FC-010', 'Collateral and Securities Records', 'Original or controlled security documents, title deeds, charge documents and other collateral records, subject to the Bank\'s secure custody arrangements.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('1b685502-1a28-4907-9100-689b00334669', 'FC-001', 'Account Opening Records', 'Account Opening Forms (AOFs), specimen signature cards, KYC/CDD documents, mandates, authorized signatory records and account closure documents.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('1c76054d-7d74-43c1-8fed-83a77991bcc7', 'FC-015', 'Cash Management Records', 'Cash remittance records, cash transfer documents, cash-in-transit records, cash insurance-related documents and cash-limit approvals.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('2725ef8d-6745-4ae1-a7c3-3645c8ea1cbe', 'FC-016', 'Regulatory and Tax Records', 'SBP returns, FBR notices and replies, tax-related documents, statutory statements and related correspondence.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('582bed7b-9dbd-42ee-822f-50a1bca77282', 'FC-018', 'Customer Complaints and Claims', 'Customer complaints, disputed transactions, claim applications, investigation findings and final settlement correspondence.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('5e16055c-f21b-4850-9092-26a1149b185b', 'FC-004', 'Clearing Records', 'Inward and outward clearing registers, clearing instruments, returned cheques and clearing reconciliation statements.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('6025ab79-377a-46c0-9c4d-dfeb51d1442c', 'FC-003', 'Cheque and Payment Records', 'Paid cheques, debit/credit vouchers, payment instruments, stop-payment instructions and related supporting documents.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('7b521c3e-43a7-4107-8f65-92795399149c', 'FC-019', 'Dormant and Unclaimed Account Records', 'Dormant account documentation, unclaimed deposit records, customer tracing correspondence and related regulatory submissions.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('8aa4c756-d4c5-41c3-b31f-8c41f717c77b', 'FC-008', 'Reconciliation and Suspense Accounts', 'Inter-branch reconciliation statements, suspense account records, outstanding entries and adjustment documentation.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('956b2945-1358-430f-b473-5eaeb96efdd9', 'FC-005', 'Remittance and Funds Transfer Records', 'Domestic and international remittance documents, funds transfer instructions, payment confirmations and supporting vouchers.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('a602c833-87d1-477c-9f8c-aecdf171dc3b', 'FC-011', 'AML/CFT and Compliance Records', 'Customer due diligence records, compliance review documentation and transaction-monitoring records, subject to applicable retention and confidentiality requirements.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('a922a4c8-1ed2-42ae-93b3-402f98ad5e9e', 'FC-002', 'Cash Transaction Records', 'Cash payment and receipt vouchers, cash scrolls, teller sheets, daily cash balancing statements and cash difference reports.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('c2201007-d58e-4775-9fa1-89c7f037c750', 'FC-014', 'Cheque Book and Security Stationery Records', 'Cheque-book issuance registers, stock registers, security stationery records, damaged cheque-book records and destruction certificates.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('c6417c2a-9852-49ff-8699-e4594ce8c923', 'FC-007', 'General Ledger and Accounting Records', 'General ledger extracts, subsidiary ledgers, journal vouchers, day books, trial balances and accounting statements.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('cd02bc9a-cd33-405e-8302-26b39b9fb5db', 'FC-006', 'Deposit Account Records', 'Term deposit applications, deposit receipts, maturity instructions, premature encashment records and related correspondence.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('cec6cfe7-0cc4-4ad0-b92a-ad46b167747f', 'FC-012', 'Audit and Inspection Records', 'Internal and external audit reports, SBP inspection observations, audit replies, compliance reports and rectification evidence.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('db4c8ec1-dd5a-49c1-9ef2-e47d478d84ca', 'FC-017', 'Branch Administrative Records', 'Branch operational correspondence, inspection visit reports, handover/takeover documents, operational approvals and control registers.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('e5109a5c-17ec-41ad-88e5-3624dd73df70', 'FC-020', 'Fixed Assets and Other Registers', 'Fixed asset registers, asset disposal records, branch inventory records and other operational registers required under the Bank\'s policy.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('ec96c356-45e7-46f7-85ef-102a8f818a58', 'FC-009', 'Advances and Credit Records', 'Loan applications, sanction letters, financing agreements, security documents, guarantees, mortgage records, recovery proceedings and settlement documents.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('f48c19b1-b994-4b51-b70c-a1d9b9a2386d', 'FC-013', 'Fraud, Investigation and Legal Records', 'FIRs, investigation reports, fraud-related correspondence, insurance claims, legal notices, court orders and litigation files.', '1', NULL, NULL, NULL, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `file_management_systems`
--

CREATE TABLE `file_management_systems` (
  `id` char(36) NOT NULL,
  `digital_id` varchar(40) NOT NULL,
  `file_no` varchar(60) DEFAULT NULL,
  `file_category_id` char(36) NOT NULL,
  `fileable_type` varchar(255) NOT NULL,
  `fileable_id` bigint(20) UNSIGNED NOT NULL,
  `document_date` date NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'draft, submitted, approved, rejected',
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL COMMENT 'Set when approved: nobody can edit until a super admin unlocks it',
  `unlocked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `unlocked_at` timestamp NULL DEFAULT NULL,
  `unlock_reason` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `current_custodian_id` bigint(20) UNSIGNED DEFAULT NULL,
  `box_id` char(36) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `position_in_box` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_management_transfers`
--

CREATE TABLE `file_management_transfers` (
  `id` char(36) NOT NULL,
  `file_management_system_id` char(36) NOT NULL,
  `source_fileable_type` varchar(255) NOT NULL,
  `source_fileable_id` bigint(20) UNSIGNED NOT NULL,
  `destination_fileable_type` varchar(255) NOT NULL,
  `destination_fileable_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `decided_by` bigint(20) UNSIGNED DEFAULT NULL,
  `decision_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genders`
--

CREATE TABLE `genders` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  `name_ur` varchar(40) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genders`
--

INSERT INTO `genders` (`id`, `code`, `name`, `name_ur`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1880-72b6-af1d-21414c4b4161', 'MALE', 'Male', 'مرد', 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1880-72b6-af1d-21414d4667c6', 'FEMALE', 'Female', 'عورت', 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1881-7139-bb55-b57b50c63737', 'TRANSGENDER', 'Trans Gender', 'خواجہ سرا', 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `head_offices`
--

CREATE TABLE `head_offices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `identification_document_types`
--

CREATE TABLE `identification_document_types` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL COMMENT 'CNIC, SNIC, NICOP, POC, ARC, POR, PASSPORT, CRC_B_FORM, BIRTH_CERT, STUDENT_ID',
  `name` varchar(150) NOT NULL COMMENT 'Form: "CNIC / Birth Certificate(Government Authority) CRC/B-Form/Alien Registration Card No./POC/NICOP/POR" aur "Passport No. (Foreign Individual Only)"',
  `name_ur` varchar(150) DEFAULT NULL,
  `issuing_authority` varchar(120) DEFAULT NULL COMMENT 'NADRA / NARA-Ministry of Interior / Foreign Authority',
  `is_for_minor` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Form-B / CRC / Birth Certificate / Student ID card',
  `is_for_foreigner` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Passport "Foreign Individual Only", ARC, POR, NICOP',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `identification_document_types`
--

INSERT INTO `identification_document_types` (`id`, `code`, `name`, `name_ur`, `issuing_authority`, `is_for_minor`, `is_for_foreigner`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-189c-7181-a998-8d2361811f5b', 'CNIC', 'Computerized National Identity Card (CNIC)', 'کمپیوٹرائزڈ قومی شناختی کارڈ', 'NADRA', 0, 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189d-732a-ab41-53698a72cc1b', 'SNIC', 'Smart National Identity Card (SNIC)', 'سمارٹ قومی شناختی کارڈ', 'NADRA', 0, 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189d-732a-ab41-53698ae7177b', 'NICOP', 'National Identity Card for Overseas Pakistanis (NICOP)', 'نائیکوپ', 'NADRA', 0, 1, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189e-7362-9741-f546990434aa', 'POC', 'Pakistan Origin Card (POC)', 'پاکستان اوریجن کارڈ', 'NADRA', 0, 1, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189f-7148-a67c-03318af0edee', 'ARC', 'Alien Registration Card (AR+D47C)', 'ایلین رجسٹریشن کارڈ', 'NARA, Ministry of Interior', 0, 1, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189f-7148-a67c-03318bb681d6', 'POR', 'Proof of Registration Card (PoR)', 'پی او آر کارڈ', 'NADRA', 0, 1, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189f-7148-a67c-03318c9ea125', 'PASSPORT', 'Passport (Foreign Individual Only)', 'پاسپورٹ', 'Passport Authority', 0, 1, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a0-7315-ac39-58bc876ac1e9', 'CRC_B_FORM', 'CRC / B-Form', 'سی آر سی/بی فارم', 'NADRA', 1, 0, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a1-707e-8b3c-0c5587d0b4c9', 'BIRTH_CERT', 'Birth Certificate (Government Authority)', 'برتھ سرٹیفکیٹ', 'Government Authority', 1, 0, 9, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18a1-707e-8b3c-0c5588137286', 'STUDENT_ID', 'Student ID Card', 'طالب علم کارڈ', 'Educational Institution', 1, 0, 10, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `id_prefixes`
--

CREATE TABLE `id_prefixes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Type name like invoice, complaint',
  `prefix` varchar(10) NOT NULL COMMENT 'Short prefix like INV, CMP',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `id_prefixes`
--

INSERT INTO `id_prefixes` (`id`, `name`, `prefix`, `created_at`, `updated_at`) VALUES
(1, 'invoice', 'INV', '2025-12-12 11:39:59', '2025-12-12 11:39:59'),
(2, 'complaint', 'CMP', '2025-12-12 11:39:59', '2025-12-12 11:39:59'),
(3, 'quotation', 'QTN', '2025-12-12 11:39:59', '2025-12-12 11:39:59'),
(4, 'circular', 'CIR', '2025-12-12 11:39:59', '2025-12-12 11:39:59'),
(5, 'employee_resource', 'EMPRES', '2025-12-12 11:39:59', '2025-12-12 11:39:59'),
(6, 'audit', 'AUD', '2025-12-12 11:39:59', '2025-12-12 11:39:59'),
(7, 'audit_findings', 'AFD', '2025-12-12 11:39:59', '2025-12-12 11:39:59'),
(8, 'audit_actions', 'ACT', '2025-12-12 11:39:59', '2025-12-12 11:39:59');

-- --------------------------------------------------------

--
-- Table structure for table `income_sources`
--

CREATE TABLE `income_sources` (
  `id` char(36) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL COMMENT 'Salaried, Pensioner, Student, House Wife, Unemployed, Self Employed, Labor/Daily Wages, Agriculturist, Stock/Investment, Rented Property, Home Remittance, Export Proceeds, Property/Real Estate, Foreign Direct Investment (FDI), Local Trading, Equity/FX Trading, Charity & Funds Donations, Others',
  `name_ur` varchar(120) DEFAULT NULL,
  `applies_to` enum('individual','entity','both') NOT NULL DEFAULT 'both' COMMENT 'individual = AOF-Ind p.14 | entity = AOF-Ind p.15 (Sole Prop) & AOF-Ent p.17',
  `requires_employer_details` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Salaried/Pensioner => Employer Name, Designation, Address of Employer',
  `requires_fund_provider_details` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Student/House Wife/Unemployed => Employer of Fund Provider, ID Doc No., Relationship',
  `requires_country` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Home Remittance => "(Country ____ Relationship ____)"',
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `income_sources`
--

INSERT INTO `income_sources` (`id`, `code`, `name`, `name_ur`, `applies_to`, `requires_employer_details`, `requires_fund_provider_details`, `requires_country`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18b7-713c-99aa-866b3997e629', 'SALARIED', 'Salaried', 'تنخواہ دار', 'individual', 1, 0, 0, 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b8-7310-a906-c3294b6ea655', 'PENSIONER', 'Pensioner', 'پنشنر', 'individual', 1, 0, 0, 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b8-7310-a906-c3294c530f59', 'STUDENT', 'Student', 'طالب علم', 'individual', 0, 1, 0, 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b9-70c5-9def-9d1ed8c68fd7', 'HOUSE_WIFE', 'House Wife', 'خاتون خانہ', 'individual', 0, 1, 0, 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b9-70c5-9def-9d1ed976e3cd', 'UNEMPLOYED', 'Unemployed', 'بے روزگار', 'individual', 0, 1, 0, 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ba-7217-a4d5-465f1abd63d4', 'SELF_EMPLOYED', 'Self Employed', 'سیلف ایمپلائیڈ', 'both', 0, 0, 0, 0, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18bb-71e9-920c-f8fb1c43afc3', 'LABOR_DAILY_WAGES', 'Labor/Daily Wages', 'مزدور/یومیہ اجرت', 'individual', 0, 0, 0, 0, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18bc-728c-a573-a5a5e16fb695', 'AGRICULTURIST', 'Agriculturist', 'زمیندار', 'individual', 0, 0, 0, 0, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18bc-728c-a573-a5a5e1d6c97c', 'FAMILY_BUSINESS', 'Family Business', 'خاندانی کاروبار', 'individual', 0, 0, 0, 0, 19, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18bd-711c-a7d1-0c81fc59e5de', 'STOCK_INVESTMENT', 'Stock/Investment', 'اسٹاک/سرمایہ کاری', 'individual', 0, 0, 0, 0, 9, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18be-73b6-a1c5-5e0bf359ceaa', 'RENTED_PROPERTY', 'Rented Property', 'کرایہ کی جائیداد', 'both', 0, 0, 0, 0, 10, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18bf-720e-bdcc-cdebc2867cf5', 'HOME_REMITTANCE', 'Home Remittance', 'ترسیلات زر', 'individual', 0, 0, 1, 0, 11, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c0-73ef-a2ad-7cfe819c6ecd', 'EXPORT_PROCEEDS', 'Export Proceeds', 'برآمدی آمدن', 'entity', 0, 0, 0, 0, 12, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c1-7378-b973-7c65f76b5cfc', 'PROPERTY_REAL_ESTATE', 'Property/Real Estate', 'جائیداد/رئیل اسٹیٹ', 'entity', 0, 0, 0, 0, 13, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c1-7378-b973-7c65f8424253', 'FDI', 'Foreign Direct Investment (FDI)', 'براہ راست غیر ملکی سرمایہ کاری', 'entity', 0, 0, 0, 0, 14, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c2-7269-af13-031a73a66ea8', 'LOCAL_TRADING', 'Local Trading', 'مقامی تجارت', 'entity', 0, 0, 0, 0, 15, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c2-7269-af13-031a74533545', 'EQUITY_FX_TRADING', 'Equity/FX Trading', 'ایکویٹی/ایف ایکس ٹریڈنگ', 'entity', 0, 0, 0, 0, 16, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c3-7190-817d-cd1bea3780d5', 'CHARITY_DONATIONS', 'Charity & Funds Donations', 'خیرات و عطیات', 'entity', 0, 0, 0, 0, 17, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c3-7190-817d-cd1bea46c666', 'INCOME_OTHERS', 'Others', 'دیگر', 'both', 0, 0, 0, 1, 18, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `division_id` bigint(20) UNSIGNED NOT NULL,
  `manager_user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT 'designation',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marital_statuses`
--

CREATE TABLE `marital_statuses` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL COMMENT 'Married, Single, Others',
  `name_ur` varchar(40) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marital_statuses`
--

INSERT INTO `marital_statuses` (`id`, `code`, `name`, `name_ur`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1882-7382-ad15-c1034419b948', 'MARRIED', 'Married', 'شادی شدہ', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1882-7382-ad15-c10344a04d0e', 'SINGLE', 'Single', 'غیر شادی شدہ', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1883-71db-9e2c-2550bba8d761', 'MS_OTHERS', 'Others', 'دیگر', 1, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` char(36) NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`generated_conversions`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_regions_table', 1),
(2, '0001_01_01_000002_create_districts_table', 1),
(3, '0001_01_01_000003_create_branches_table', 1),
(4, '0001_01_01_000004_create_contacts_table', 1),
(5, '0001_01_01_000005_create_users_table', 1),
(6, '0001_01_01_000006_create_cache_table', 1),
(7, '0001_01_01_000007_create_jobs_table', 1),
(8, '2024_01_01_000000_create_id_prefixes_table', 1),
(9, '2025_01_17_062513_add_two_factor_columns_to_users_table', 1),
(10, '2025_01_17_062525_create_personal_access_tokens_table', 1),
(11, '2025_01_17_065420_create_branch_targets_table', 1),
(12, '2025_01_19_090432_create_permission_tables', 1),
(13, '2025_01_19_090433_create_divisions_table', 1),
(14, '2025_01_19_094604_create_activity_log_table', 1),
(15, '2025_01_19_094605_add_event_column_to_activity_log_table', 1),
(16, '2025_01_19_094606_add_batch_uuid_column_to_activity_log_table', 1),
(17, '2025_01_21_044811_create_daily_positions_table', 1),
(18, '2025_01_22_072953_create_reports_table', 1),
(19, '2025_02_04_060353_create_circulars_table', 1),
(20, '2025_02_04_060354_create_managers_table', 1),
(21, '2025_02_12_130216_create_complaint_status_types_table', 1),
(22, '2025_02_12_130339_create_complaints_table', 1),
(23, '2025_02_12_130431_create_complaint_histories_table', 1),
(24, '2025_02_12_130432_create_complaint_comments_table', 1),
(25, '2025_02_12_130522_create_complaint_attachments_table', 1),
(26, '2025_03_26_054904_create_complaint_categories_table', 1),
(27, '2025_03_26_054905_create_complaint_assignments_table', 1),
(28, '2025_03_26_054906_create_complaint_escalations_table', 1),
(29, '2025_03_26_054907_create_complaint_watchers_table', 1),
(30, '2025_03_26_054908_create_complaint_templates_table', 1),
(31, '2025_03_26_054909_create_complaint_metrics_table', 1),
(32, '2025_04_29_111040_create_printed_stationeries_table', 1),
(33, '2025_04_29_122515_create_stationery_transactions_table', 1),
(34, '2025_05_09_115245_create_dispatch_registers_table', 1),
(35, '2025_08_08_120539_fix_activity_log_uuid_columns_properly', 1),
(36, '2025_08_20_100000_create_categories_table', 1),
(37, '2025_08_20_110607_create_employee_resources_table', 1),
(38, '2025_08_22_121000_create_complaint_witnesses_table', 1),
(39, '2025_08_23_093200_create_audit_types_table', 1),
(40, '2025_08_23_093217_create_audits_table', 1),
(41, '2025_08_23_093225_create_audit_auditors_table', 1),
(42, '2025_08_23_093231_create_audit_checklist_items_table', 1),
(43, '2025_08_23_093234_create_audit_item_responses_table', 1),
(44, '2025_08_23_093239_create_audit_findings_table', 1),
(45, '2025_08_23_093243_create_audit_actions_table', 1),
(46, '2025_08_23_093322_create_audit_action_updates_table', 1),
(47, '2025_08_23_093324_create_audit_status_histories_table', 1),
(48, '2025_08_23_093326_create_audit_scopes_table', 1),
(49, '2025_08_23_093328_create_audit_documents_table', 1),
(50, '2025_08_23_093341_create_audit_finding_attachments_table', 1),
(51, '2025_08_23_093359_create_audit_risks_table', 1),
(52, '2025_08_23_093402_create_audit_notifications_table', 1),
(53, '2025_08_23_093404_create_audit_tags_table', 1),
(54, '2025_08_23_093406_create_audit_tag_pivots_table', 1),
(55, '2025_08_23_093408_create_audit_schedules_table', 1),
(56, '2025_08_23_093410_create_audit_metrics_caches_table', 1),
(57, '2025_09_24_235605_create_aksic_applications_table', 1),
(58, '2025_09_25_001837_create_aksic_application_education_table', 1),
(59, '2025_09_25_001842_create_aksic_application_status_logs_table', 1),
(60, '2025_09_26_000001_create_aksic_business_categories_table', 1),
(61, '2026_05_18_063038_create_aksics_table', 2),
(62, '2026_05_18_064444_create_aksic_amortizations_table', 2),
(63, '2026_05_18_072120_add_tracking_and_soft_deletes_to_aksics_tables', 2),
(64, '2026_05_18_072448_normalize_aksics_status_and_drop_fee_status', 2),
(65, '2026_05_18_192026_seed_manager_permissions', 2),
(66, '2026_05_19_000001_create_aksic_rules_table', 2),
(67, '2026_05_19_000002_add_rule_fields_to_aksics_table', 2),
(68, '2026_05_25_051254_add_total_interest_and_permissions_to_aksics_table', 2),
(69, '2026_08_28_000001_create_file_management_tables', 3),
(70, '2026_08_28_055122_create_media_table', 3),
(71, '2026_09_22_000001_create_account_opening_reference_tables', 3),
(72, '2026_09_22_000002_create_customer_tables', 3),
(73, '2026_09_22_000003_create_account_tables', 3),
(74, '2026_09_22_000004_create_account_opening_request_tables', 3),
(75, '2026_09_23_000001_update_aksics_table', 3),
(76, '2026_09_23_000002_create_aksic_claims_tables', 3),
(77, '2026_09_23_000003_create_aksic_budget_tables', 3),
(78, '2026_09_23_000004_seed_access_permissions', 3),
(79, '2026_10_12_171736_add_economic_sector_code_to_customers_table', 3),
(80, '2026_10_12_172835_add_maker_checker_to_account_opening_requests', 3),
(81, '2026_10_13_192734_add_description_and_oru_categories_to_file_categories', 3),
(82, '2026_10_13_193047_allow_term_accounts_in_account_products', 3),
(83, '2026_10_13_193242_add_form_edition_to_account_opening_requests', 3),
(84, '2026_10_13_193552_create_account_opening_paper_forms_table', 3),
(85, '2026_10_13_194857_load_account_opening_reference_data', 3),
(86, '2026_10_13_195223_add_maker_checker_to_file_management_systems', 3),
(87, '2026_10_14_072330_add_aksic_application_permissions', 3),
(88, '2026_10_14_132052_drop_audit_circular_stationery_dispatch_and_daily_position_tables', 3);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 2),
(1, 'App\\Models\\User', 3),
(1, 'App\\Models\\User', 4),
(1, 'App\\Models\\User', 5),
(1, 'App\\Models\\User', 6),
(1, 'App\\Models\\User', 7),
(1, 'App\\Models\\User', 8),
(1, 'App\\Models\\User', 9),
(1, 'App\\Models\\User', 10),
(1, 'App\\Models\\User', 11),
(1, 'App\\Models\\User', 12),
(1, 'App\\Models\\User', 13),
(1, 'App\\Models\\User', 14),
(1, 'App\\Models\\User', 15),
(1, 'App\\Models\\User', 16),
(1, 'App\\Models\\User', 17),
(1, 'App\\Models\\User', 18),
(1, 'App\\Models\\User', 19),
(1, 'App\\Models\\User', 20),
(1, 'App\\Models\\User', 21),
(1, 'App\\Models\\User', 22),
(1, 'App\\Models\\User', 23),
(1, 'App\\Models\\User', 24),
(1, 'App\\Models\\User', 25),
(1, 'App\\Models\\User', 26),
(1, 'App\\Models\\User', 27),
(1, 'App\\Models\\User', 28),
(1, 'App\\Models\\User', 29),
(1, 'App\\Models\\User', 30),
(1, 'App\\Models\\User', 31),
(1, 'App\\Models\\User', 32),
(1, 'App\\Models\\User', 33),
(1, 'App\\Models\\User', 34),
(1, 'App\\Models\\User', 35),
(1, 'App\\Models\\User', 36),
(1, 'App\\Models\\User', 37),
(1, 'App\\Models\\User', 38),
(1, 'App\\Models\\User', 39),
(1, 'App\\Models\\User', 40),
(1, 'App\\Models\\User', 41),
(1, 'App\\Models\\User', 42),
(1, 'App\\Models\\User', 43),
(1, 'App\\Models\\User', 44),
(1, 'App\\Models\\User', 45),
(1, 'App\\Models\\User', 46),
(1, 'App\\Models\\User', 47),
(1, 'App\\Models\\User', 48),
(1, 'App\\Models\\User', 49),
(1, 'App\\Models\\User', 50),
(1, 'App\\Models\\User', 51),
(1, 'App\\Models\\User', 52),
(1, 'App\\Models\\User', 53),
(1, 'App\\Models\\User', 54),
(1, 'App\\Models\\User', 55),
(1, 'App\\Models\\User', 56),
(1, 'App\\Models\\User', 57),
(1, 'App\\Models\\User', 58),
(1, 'App\\Models\\User', 59),
(1, 'App\\Models\\User', 60),
(1, 'App\\Models\\User', 61),
(1, 'App\\Models\\User', 62),
(1, 'App\\Models\\User', 63),
(1, 'App\\Models\\User', 64),
(1, 'App\\Models\\User', 65),
(1, 'App\\Models\\User', 66),
(1, 'App\\Models\\User', 67),
(1, 'App\\Models\\User', 68),
(1, 'App\\Models\\User', 69),
(1, 'App\\Models\\User', 70),
(1, 'App\\Models\\User', 71),
(1, 'App\\Models\\User', 72),
(1, 'App\\Models\\User', 73),
(1, 'App\\Models\\User', 74),
(1, 'App\\Models\\User', 75),
(1, 'App\\Models\\User', 76),
(1, 'App\\Models\\User', 77),
(1, 'App\\Models\\User', 78),
(1, 'App\\Models\\User', 79),
(1, 'App\\Models\\User', 80),
(1, 'App\\Models\\User', 81),
(1, 'App\\Models\\User', 82),
(1, 'App\\Models\\User', 83),
(1, 'App\\Models\\User', 84),
(1, 'App\\Models\\User', 85),
(1, 'App\\Models\\User', 86),
(1, 'App\\Models\\User', 87),
(5, 'App\\Models\\User', 88);

-- --------------------------------------------------------

--
-- Table structure for table `operating_instructions`
--

CREATE TABLE `operating_instructions` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'Signing Authority, Singly, Either or Survivor, Jointly, Mandate, Other',
  `name_ur` varchar(80) DEFAULT NULL,
  `requires_multiple_holders` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Either or Survivor / Jointly',
  `requires_mandate_form` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Mandate => "Please fill the prescribed form"',
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `operating_instructions`
--

INSERT INTO `operating_instructions` (`id`, `code`, `name`, `name_ur`, `requires_multiple_holders`, `requires_mandate_form`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18a9-7127-bdb7-ca2757247c27', 'SIGNING_AUTHORITY', 'Signing Authority', 'دستخط کنندہ', 0, 0, 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18aa-7262-9c15-4d123df5c9fa', 'SINGLY', 'Singly', 'واحد مجاز', 0, 0, 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18aa-7262-9c15-4d123ef3adbd', 'EITHER_OR_SURVIVOR', 'Either or Survivor', 'کوئی ایک یا باقی ماندہ', 1, 0, 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ab-708a-9608-f3d939507bc7', 'JOINTLY', 'Jointly', 'مشترکہ', 1, 0, 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ab-708a-9608-f3d93986e15b', 'MANDATE', 'Mandate', 'اختیار', 0, 1, 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ac-7057-91cf-bed2b976580b', 'OI_OTHER', 'Other', 'دیگر', 0, 0, 1, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view users', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(2, 'create users', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(3, 'edit users', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(4, 'delete users', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(5, 'view roles', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(6, 'create roles', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(7, 'edit roles', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(8, 'delete roles', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(9, 'view permissions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(10, 'create permissions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(11, 'edit permissions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(12, 'delete permissions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(13, 'assign roles', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(14, 'revoke roles', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(15, 'assign permissions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(16, 'revoke permissions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(17, 'view complaints', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(18, 'create complaints', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(19, 'edit complaints', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(20, 'delete complaints', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(21, 'assign complaints', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(22, 'escalate complaints', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(29, 'view branches', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(30, 'create branches', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(31, 'edit branches', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(32, 'delete branches', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(33, 'view regions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(34, 'create regions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(35, 'edit regions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(36, 'delete regions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(37, 'view divisions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(38, 'create divisions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(39, 'edit divisions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(40, 'delete divisions', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(41, 'view categories', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(42, 'create categories', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(43, 'edit categories', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(44, 'delete categories', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(45, 'view reports', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(46, 'generate reports', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(47, 'export reports', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(48, 'view dashboard', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(49, 'view analytics', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(50, 'view settings', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(51, 'edit settings', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(52, 'system settings', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(53, 'view managers', 'web', '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(54, 'create managers', 'web', '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(55, 'edit managers', 'web', '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(56, 'delete managers', 'web', '2026-05-25 06:41:36', '2026-05-25 06:41:36'),
(57, 'view aksic rules', 'web', '2026-05-25 06:41:37', '2026-05-25 06:41:37'),
(58, 'create aksic rules', 'web', '2026-05-25 06:41:37', '2026-05-25 06:41:37'),
(59, 'edit aksic rules', 'web', '2026-05-25 06:41:37', '2026-05-25 06:41:37'),
(60, 'delete aksic rules', 'web', '2026-05-25 06:41:37', '2026-05-25 06:41:37'),
(61, 'approve aksics', 'web', '2026-05-25 06:41:37', '2026-05-25 06:41:37'),
(62, 'import aksics', 'web', '2026-05-25 06:41:37', '2026-05-25 06:41:37'),
(63, 'view file categories', 'web', '2026-10-14 15:01:44', '2026-10-14 15:01:44'),
(64, 'create file categories', 'web', '2026-10-14 15:01:44', '2026-10-14 15:01:44'),
(65, 'edit file categories', 'web', '2026-10-14 15:01:44', '2026-10-14 15:01:44'),
(66, 'delete file categories', 'web', '2026-10-14 15:01:44', '2026-10-14 15:01:44'),
(67, 'view file management systems', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(68, 'create file management systems', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(69, 'edit file management systems', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(70, 'delete file management systems', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(71, 'transfer file management systems', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(72, 'approve file management transfers', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(73, 'archive file management systems', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(74, 'create boxes', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(75, 'manage boxes', 'web', '2026-10-14 15:01:45', '2026-10-14 15:01:45'),
(76, 'view account openings', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(77, 'create account openings', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(78, 'edit account openings', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(79, 'delete account openings', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(80, 'approve account openings', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(81, 'manage account opening references', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(82, 'view aksic claims', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(83, 'lodge aksic claims', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(84, 'settle aksic claims', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(85, 'delete aksic claims', 'web', '2026-10-14 15:01:48', '2026-10-14 15:01:48'),
(86, 'view aksic budget', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(87, 'manage aksic budget', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(88, 'view aksics', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(89, 'create aksics', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(90, 'edit aksics', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(91, 'delete aksics', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(92, 'view all aksic cases', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(93, 'view aksic dashboard', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(94, 'view file management dashboard', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(95, 'view account opening dashboard', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(96, 'export file management systems', 'web', '2026-10-14 15:01:49', '2026-10-14 15:01:49'),
(97, 'submit account openings', 'web', '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
(98, 'unlock account openings', 'web', '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
(99, 'submit file management systems', 'web', '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
(100, 'approve file management systems', 'web', '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
(101, 'unlock file management systems', 'web', '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
(102, 'view aksic applications', 'web', '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
(103, 'sync aksic applications', 'web', '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
(104, 'update aksic application status', 'web', '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professions`
--

CREATE TABLE `professions` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL COMMENT 'Government Service, Private Service, Housewife, Self Employed, Unemployed, Agriculture, Student, Others',
  `name_ur` varchar(120) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `professions`
--

INSERT INTO `professions` (`id`, `code`, `name`, `name_ur`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1889-73c4-bf26-e886201c0d8a', 'GOVT_SERVICE', 'Government Service', 'سرکاری ملازمت', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1889-73c4-bf26-e88620bdd53c', 'PRIVATE_SERVICE', 'Private Service', 'پرائیویٹ ملازمت', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-188a-71ba-a645-fe7adb36c0bd', 'HOUSEWIFE', 'Housewife', 'خاتون خانہ', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-188c-7268-a00f-53d5807a96c7', 'SELF_EMPLOYED', 'Self Employed', 'سیلف ایمپلائیڈ', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-188d-71b9-b778-8f5fb00bbc6b', 'UNEMPLOYED', 'Unemployed', 'بے روزگار', 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-188d-71b9-b778-8f5fb0cac386', 'AGRICULTURE', 'Agriculture', 'زراعت', 0, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-188e-7398-b773-bd04d893d05f', 'PROF_STUDENT', 'Student', 'طالب علم', 0, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-188e-7398-b773-bd04d9086603', 'PROF_OTHERS', 'Others', 'دیگر', 1, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `name`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Muzaffarabad', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(2, 'Rawalakot', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(3, 'Kotli', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03'),
(4, 'Mirpur', NULL, '2025-12-12 11:40:03', '2025-12-12 11:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `relationships`
--

CREATE TABLE `relationships` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `name_ur` varchar(60) DEFAULT NULL,
  `context_parentage` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'S/o, D/o, W/o',
  `context_guardian` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Relationship with Guardian/Minor',
  `context_next_of_kin` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Contact Persons block',
  `context_fund_provider` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Relationship with Fund Provider',
  `context_ubo` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Relationship with Customer (UBO)',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `relationships`
--

INSERT INTO `relationships` (`id`, `code`, `name`, `name_ur`, `context_parentage`, `context_guardian`, `context_next_of_kin`, `context_fund_provider`, `context_ubo`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1895-7133-87b0-2b9686ad5c79', 'FATHER', 'Father (S/o)', 'والد', 1, 1, 1, 1, 1, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1896-720e-9b2b-b116e89d8c2e', 'MOTHER', 'Mother', 'والدہ', 0, 1, 1, 1, 1, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1897-73e5-a72d-0d10708dad79', 'HUSBAND', 'Husband (W/o)', 'شوہر', 1, 1, 1, 1, 1, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1897-73e5-a72d-0d10718a6e5f', 'WIFE', 'Wife', 'بیوی', 0, 1, 1, 1, 1, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1898-71fd-af99-2bd9c6242bac', 'SON', 'Son', 'بیٹا', 0, 1, 1, 1, 1, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1898-71fd-af99-2bd9c6eca0c3', 'DAUGHTER', 'Daughter (D/o)', 'بیٹی', 1, 1, 1, 1, 1, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1899-70cb-82a2-46c19aad281e', 'BROTHER', 'Brother', 'بھائی', 0, 1, 1, 1, 1, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189a-73db-b721-2d3139663f93', 'SISTER', 'Sister', 'بہن', 0, 1, 1, 1, 1, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189a-73db-b721-2d313a33e12a', 'GUARDIAN', 'Guardian', 'سرپرست', 0, 1, 1, 1, 0, 9, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189b-7100-b09b-506019f3084c', 'EMPLOYER', 'Employer', 'آجر', 0, 0, 0, 1, 0, 10, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-189b-7100-b09b-50601ac98cb8', 'REL_OTHER', 'Other', 'دیگر', 0, 1, 1, 1, 1, 11, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `branch_code` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'branch', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(2, 'region', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(3, 'division', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(4, 'head-office', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05'),
(5, 'super-admin', 'web', '2025-12-12 11:40:05', '2025-12-12 11:40:05');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(2, 3),
(2, 4),
(2, 5),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(4, 4),
(4, 5),
(5, 4),
(5, 5),
(6, 5),
(7, 4),
(7, 5),
(8, 5),
(9, 5),
(10, 5),
(11, 5),
(12, 5),
(13, 4),
(13, 5),
(14, 4),
(14, 5),
(15, 5),
(16, 5),
(17, 1),
(17, 2),
(17, 3),
(17, 4),
(17, 5),
(18, 1),
(18, 2),
(18, 3),
(18, 4),
(18, 5),
(19, 1),
(19, 2),
(19, 3),
(19, 4),
(19, 5),
(20, 4),
(20, 5),
(21, 2),
(21, 3),
(21, 4),
(21, 5),
(22, 3),
(22, 4),
(22, 5),
(29, 2),
(29, 3),
(29, 4),
(29, 5),
(30, 4),
(30, 5),
(31, 3),
(31, 4),
(31, 5),
(32, 4),
(32, 5),
(33, 5),
(34, 5),
(35, 5),
(36, 5),
(37, 5),
(38, 5),
(39, 5),
(40, 5),
(41, 5),
(42, 5),
(43, 5),
(44, 5),
(45, 1),
(45, 2),
(45, 3),
(45, 4),
(45, 5),
(46, 2),
(46, 3),
(46, 4),
(46, 5),
(47, 3),
(47, 4),
(47, 5),
(48, 1),
(48, 2),
(48, 3),
(48, 4),
(48, 5),
(49, 4),
(49, 5),
(50, 4),
(50, 5),
(51, 4),
(51, 5),
(52, 5),
(53, 1),
(53, 2),
(53, 3),
(53, 4),
(53, 5),
(54, 3),
(54, 4),
(54, 5),
(55, 3),
(55, 4),
(55, 5),
(56, 4),
(56, 5),
(57, 4),
(57, 5),
(58, 4),
(58, 5),
(59, 4),
(59, 5),
(60, 4),
(60, 5),
(61, 4),
(61, 5),
(62, 4),
(62, 5),
(63, 1),
(63, 2),
(63, 3),
(63, 4),
(63, 5),
(64, 3),
(64, 4),
(64, 5),
(65, 3),
(65, 4),
(65, 5),
(66, 4),
(66, 5),
(67, 1),
(67, 2),
(67, 3),
(67, 4),
(67, 5),
(68, 1),
(68, 2),
(68, 3),
(68, 4),
(68, 5),
(69, 1),
(69, 2),
(69, 3),
(69, 4),
(69, 5),
(70, 4),
(70, 5),
(71, 1),
(71, 2),
(71, 3),
(71, 4),
(71, 5),
(72, 4),
(72, 5),
(73, 1),
(73, 2),
(73, 3),
(73, 4),
(73, 5),
(74, 1),
(74, 2),
(74, 3),
(74, 4),
(74, 5),
(75, 1),
(75, 2),
(75, 3),
(75, 4),
(75, 5),
(76, 1),
(76, 2),
(76, 3),
(76, 4),
(76, 5),
(77, 1),
(77, 2),
(77, 3),
(77, 4),
(77, 5),
(78, 1),
(78, 2),
(78, 3),
(78, 4),
(78, 5),
(79, 4),
(79, 5),
(80, 4),
(80, 5),
(81, 4),
(81, 5),
(82, 2),
(82, 3),
(82, 4),
(82, 5),
(83, 4),
(83, 5),
(84, 4),
(84, 5),
(85, 4),
(85, 5),
(86, 2),
(86, 3),
(86, 4),
(86, 5),
(87, 4),
(87, 5),
(88, 1),
(88, 2),
(88, 3),
(88, 4),
(88, 5),
(92, 3),
(92, 4),
(92, 5),
(93, 1),
(93, 2),
(93, 3),
(93, 4),
(93, 5),
(94, 1),
(94, 2),
(94, 3),
(94, 4),
(94, 5),
(95, 1),
(95, 2),
(95, 3),
(95, 4),
(95, 5),
(96, 1),
(96, 2),
(96, 3),
(96, 4),
(96, 5),
(97, 1),
(97, 2),
(97, 3),
(97, 4),
(97, 5),
(98, 5),
(99, 1),
(99, 2),
(99, 3),
(99, 4),
(99, 5),
(100, 1),
(100, 2),
(100, 3),
(100, 4),
(100, 5),
(101, 5),
(102, 1),
(102, 2),
(102, 3),
(102, 4),
(102, 5),
(103, 4),
(103, 5),
(104, 4),
(104, 5);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2IbnnR1IQgFWwIjO4hunzklAQs3ruqvboiwG09ql', NULL, '127.0.0.1', 'HerdApplications', 'ZXlKcGRpSTZJbU5OVnprM1JtSnFTMVpNTjBOYU1ITlNaVUpRWm1jOVBTSXNJblpoYkhWbElqb2lNVk5DVTFnNE0zQkZWRVJyVTFnMFZ6bFFVVTUwY2xSNVJuRmpWbXRhVmpWalpVSkZXbEpGV21OTGVFVmhSelU0YVdKbVNFeFhTRTlzZDJWcGFDOHhVbE5KUjAxdGNFWnJXVTk2TnpJcmQxWjVRWGM1VFdscFRHVmxkamwyUjNoaE5FSTJXVFpXTDNKRWNtSlpkazFrVURaWFVUbHpRM2hqU1RWcU16UTNOMGtyVG5oU056Z3dZelJWWjNWdGNWcHJVM1J6ZERZeFZFMVlSakV3UVZKRU5IQnpRVU5xZEVSbFVEUlpNblZQTUVRNGFFcExOVGxwY2pZNFExSkJTRFJ4VjB0SGFEUkVTSEZoSzBoelptdHFjbWRLWVV3NWJIZG5UM1ZuU2pSNk1tdG9NM0JHYnpVell6UnhkV3gzV1ZabldraDNjUzl5Y0ZvME1rdEZWWGhRVUZSaFJWQlBjMGxLTHpKRk1rbHpkMGM1Y1ZkMFpFbG1jVUZPUld0TFVGb3hNRkpZYzFSR1F6TjFkMGs5SWl3aWJXRmpJam9pWWpVMk1EazFNVGMwTldKaFptWTROREF6Tmpnek4ySTVOelV5TlRZeFltRXdNekJrTXpaaVpUWXlabVJpT1dZeVlUZzNOVFV5TkROak9EQmhNRE0zWmlJc0luUmhaeUk2SWlKOQ==', 1792871257),
('bDEM8v1aBA5aGJADcpjdb1yfu5TbrOYHA5TR36hJ', NULL, '127.0.0.1', 'HerdApplications', 'ZXlKcGRpSTZJbWxMVWpoSVZWTTJWR2hTV1RsS0wybHJXbEoyZEhjOVBTSXNJblpoYkhWbElqb2lVemRxVDBGVFVqVTRlSGxyYmpGb1ltZDFTRUpKVW1jdlFscFBhMmxHU1Uxak9WaHpWM1FyVW5aVWJtZERkbFJOVFdwYWVFaFhRekpHYzI5WVFtcE9aM1YwU1RNd2IySndVMHd6U0dGUmMwcENRVU00Wm5wNFNFMDJRa2hoVjFoR1VHZFdTR1pZTW10M2JGZ3JLMDFaTHpjdlVubzBkV3c0WjI5blFrZzBiRzFDV1ZSa1ZVMXlRMmxrYjJ0emQzRk5laTh6VGpJMWMxVmtTa1ZrYW5ReU0xUlZUVE5YWnk4MGJXVXZOQzlMY3pkcVlrSlJNMHhYWjBWdksyNUtjblJvVkdGQkt6aGFVRzlHVEhOWVpXNDNhbGxHVjBsRmJWQjRZeTlDWjJvdk9UTjZOVEZDVEhaVmMyMDBVVkZRZWxOd01ITjFZbk40U1RoSFMyRm1lRVZhV1d4QllVRTVOMGR5UVZKM2MxTXdVVkU0YjBsdk0zb3ZNa0kwSzJRd1pUbFZSRlZaYUhOYVMyeERWRkU5SWl3aWJXRmpJam9pTUdZNU5HSXlNRFUzTkRsak9XSTVNekl3WWpZeE5qRXpOV1poTVROaU5UQmhNMlJtWmpVNFltSXdaVE5oTnpoaVl6TTVaamMzWXpNM00yVXlZVFF3WXlJc0luUmhaeUk2SWlKOQ==', 1792756654),
('cs15pvB4CR7DoZWB2C7nWYFgqmqU95nK41PwvYyd', 88, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbHB0TlU5TE9IbEZiVXMzVEZaMFFuZFZhV0kzTkhjOVBTSXNJblpoYkhWbElqb2liMkpRVEcxNGRXWnFSVzkwZEdsWlRVNUNVbEpzZGpCa2RXRlRkSGxzT0d4aldrTklkbTB2YzNKaFdtVnhTR0kwV1RsbGRrNVlaVXBNWlVGdlUybEVaM1kxUWpGU1JYUm1kbWRhUzNGdFZtRkRXVlE1V25aNU1IcHJUVU5WVldkdGVHSklMelZRYjNCVmEzUkdWVmhYUWpWV2RWWTFWamRuVWxGWFpWWTJiRzEwY0dkbk1qaExTWE00UWxOeGNqa3pUblJ0YzJoeWNpOUtjMFZ4VldnNFRHaG9abGhaZW5Sb1owdG1aSGQyWWtRM1RWVkNLM0ZCVm5wMFowcFBMMkZzVDNFMFRuSkRRMFphUTJSUloyeDRRV2hTWW1ZcmRFbDFVVFV4WWtadGFsQlRSalo2WmtZNE9XOUZiVzlGY21wNFJXd3lUemhPZWt4bGQyWkJkVGMxTWpBeFNtWkpTazU2Ym5oaWNrZExNVTFyUm1nNWRsWjVUbE0zWldOR1Jsa3pSblZLUVVFNVVtSm5iWE5PV214cGREbHVUQzh2V1cxVlozWnhiME4xZUZWcldUVmpVV3hUTVc5NmQzSmhiRXQ1WTA5TWJuaHdXVk4xUjBsb1pIWkxSR2hLU1RSWmIwVTBSVlV5V0ZkWmVqbEpWUzgxTjJoSGVVNVRaMFprWnlzeE5ETktNRkp5YUN0cmNVRk9jVXhHU21WVVlUbDVUbFkwUmtOa05FOVdiV3hRTmxGdmMxcHRTelpoUldzM1VYbG9TWEZ1UTBZd2JFYzVUM2xrVlZobk1ITnNTMVE0ZW1ORksyRkhXbVp1VTJsTVFrOVllSEF5Y0VsR1EzWmplRmxEVTFGU2VIVkZjbGt6Y3pOaU5FZ3ZWalozTkZBNFVWcEdZbTA0ZWs1dlRYSlBjMkpXU1hSNVltRlpaRWxCUkZvdlFscFJiVVZUYVUwd1lYY3JkbkJMZVhZdlVsZGlWMk15UzJadVVWaFZaREZXZFRnMFdGQnFaMUp6ZVVGbFoyTkROalZaVjBSRk5YUlFkRkZoVUZscmVVSlFWRzByYmtocWJ6Qm1ZVzlwTWtFMmEydE9Ua2h0UmpnM2FIVXpNekJWU25BclkwdzFLekpUYTBST1V6bHJOM0JXU2tkSFZsUlBMMXBoVlhWb2NYUmhlV2hoT0RKbU9WSnZabkZuWkU4NFUwMVVRWFpLVDJaclZETmhjWEJ3WWl0R2RVeFZaRmhLTW1scmJGZFZOVEYwYTBoMk5uRkVVR1JCTDJGaFZEVjZkMHRvV1RGQlBUMGlMQ0p0WVdNaU9pSTBPVEUyWm1Jd1pEbGpaalkyWVdWbU1qUTBaRE15WTJGbFpUTmhNVEZoT1dNeE1qSTBNMkkxTkdFME5UWmtZVFF5WmpjMU1EVm1OVFZtWmpOaVkyUXdJaXdpZEdGbklqb2lJbjA9', 1791991260),
('TXFzQqjSXOHYsZWgL2BDsthzGJcXG7pKKlV6erYZ', NULL, '127.0.0.1', 'HerdApplications', 'ZXlKcGRpSTZJbXBLYUV4Vk5qUnBPRU5ITkVkeFZWZDBkVmxtUVdjOVBTSXNJblpoYkhWbElqb2lNM1VyTlZaUVVXZG5jVWg0UTBwb09XcDJPQ3RWV21OSk5WRlRhbTlCZFVNeGIxUkNURVV6Ukc5V2JuaHlOVkZpY1VVeVRtODFlRnBzZWxwU05YRktlRkZWWlhobFlXdEliRnBCTVdSd1NETkRURzR4V21GdmJFdFZZMUpOZURCdWFERnVWMjlKZW1NMGNEZEtiMHhwTldoRWFIZzFZemhzWVRreFJ6VTNaVEZaZUdKMVpqa3lkR015WkRacFFVb3dhVEJ5Umt4R1UyeDFZMkYxVGtwMlpFWllaamxuVml0RFJDdGtjVTh5UjBOcWJIWmFTVzlsWjJ4TU5YbExWR1ZMYzJKMk5qYzFhRlJ0VDFZNUwyMU1VMlJsT1RFeVlrdGFXalpXV0ROVVUwcGlVa0lyVG1GWWN6aGxLMlpRUnpKM2FrSk1jRW81YTJkcFVqWlRNR3hVYlV4WVVFcHhibmt2YVRGUVNtSjZiRVprVEhRM1VHUjNVbGRxUkdWTldtY3llbFp2V1VoS1pUWkxTbTg5SWl3aWJXRmpJam9pWkdRMFpEZzBNVGxrT1RCbE9ESTNPVFV5T0dKaE56ZGhNell6T1dabE1EYzNNalk1WkdZMVlUa3dabUU0TWpVek56ZzNaVGN4TXpjNE5XVXdaR1E0WXlJc0luUmhaeUk2SWlKOQ==', 1792731377),
('XC22X1M2SVSy0cHrZBWcFjhIIbJFy5tu6uuTv6vT', NULL, '127.0.0.1', 'HerdApplications', 'ZXlKcGRpSTZJaXQzVkVaTVIzazRSVk5vYm5obFZYQlhWbUZuVDNjOVBTSXNJblpoYkhWbElqb2lNRWRpVEV0aWIycFVWamQ0WjNwSE1VMUJabUo0YzB0WU5WUjBTazltZGk5TFNTdHBVelY1YTNVeVlURllTa2N4TVRKamRXZE1iMlJ4WXl0RVNGSXZaMDlUU0V0S1IwTlRlRlJOWlhGNVVuaFdVRGM0U1ZkMFpqZEJjbVZXVHpkd1ZGcFZkVWMxYmpkelRYQkhXSFk0ZG5sVWMzaDFOelZPWlZoTU1FVXlVVGxJU1c1dlVtSXZSalJDUTFGUk5tbHlTa3BFWTBodk5saEJkV3RFY0M5RWMwNTRaRVJRTW5jd2FpOUdNbmRRUjBsdVFURjVNVW80U2xsNVNFd3pNRnBXYUhGV01ISjRWVFpLY25WSWQwRkVXSE52VVVoeFpWSnplVWd5ZGpORVQwUjNOV014U1UxdGRsaFllRWxMZEhWaFFYcEJOV3hJYnpWSWJsVldTM1pTUmxodVIyaGhVa3gzWTFCWGN6RnhPVU13ZG5WQmVXRjVURmRqTVZWSE1scFhhWGs1TVdWYUt6VkJSM2M5SWl3aWJXRmpJam9pTnpFd05tWmtaV1ZsWWpSallURmtOakkyTVdabFpESXpZbUkxWkdWa04yTmhORE00WlRWaE0yRmhaV0UxTkRKaFlUUm1ZbUkxTVdFeVpXSTRNRE5oWVNJc0luUmhaeUk2SWlKOQ==', 1792606231),
('YIdKMP5vnYyVWyr3dNQfzBDlLUTKXSvXf5rzW0Uh', NULL, '127.0.0.1', 'HerdApplications', 'ZXlKcGRpSTZJbmRxWTNaSlRXcFFUMmhwT0RSc2RWSklVVUZsTTNjOVBTSXNJblpoYkhWbElqb2lTRmxtVkdGMlZHRmhkVkYzYzFSbFZVZEJhVEV6Um5ORFR6TlZTblpSVm1kTVpXeHdlV3hoZFRsVFJFNUhSMkkwYVdRdlFVb3hjV2hUUmtseGNtVTVSM1ZYV0ZFeE1UUmlVVEp5VG1GT2FHMHlOM2xaWTBWU00wNWtUbkZCVTFjMk0wWkhWMFo0SzJSb1J6UmlSekIwTXpFemFtdzBUVFZEYTNBNGRVUkphRWRPVEZOeFFUTnlVR2QyTTNveGVXZHRMMXBNYzAxV1Z5dEJRVEV6VERKR1UyUTNRMWhXWjJOS1N6TkJUWGsyU1VWRmMwOUlSRkFyYmpaTGIyVnJNbWhNYXpGRk9Yb3plVFpSZVRkVWNHMHJTa1ppYmtJNE1XcENXRXBqVFVSR1ltUjRkMU41UldwdVUyNXFVMWhFV21wRGJVc3JRalJFTVV4V1dGRk5LM3BXZVVJMWVFeERSM3B1VTJoSVluZ3hRekJUU1haVE5IZ3ZkV1ZUTmtKMFQxQlFSMmRQUlZVMUwyRkhNVms5SWl3aWJXRmpJam9pTnpWbU5UTXdOVEJtWVRnME1qQXpOV1EzTVdFMU9ETXpZbVJrTVRjM1pURm1NMkUyTm1RMll6bG1NR1kzTURVMU1XTm1ZemM1TkRCa00yUmxZVE0zWmlJc0luUmhaeUk2SWlKOQ==', 1792606112);

-- --------------------------------------------------------

--
-- Table structure for table `special_categories`
--

CREATE TABLE `special_categories` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL COMMENT 'BAJK Staff, Minor, Photo Account, Mustahiqeen-e-Zakat, Parda Nasheen, Student, Visually Impaired Person/Blind, Disaster Affectees/IDPs, Registered Alien, Widow, Pensioners, Senior Citizen, Govt Emp/Semi Govt Emp, Pension, Salary, Physically Handicapped, Non-Resident, Others',
  `name_ur` varchar(120) DEFAULT NULL,
  `requires_extra_value` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Non-Resident => "(in years) Time since residing out side Pakistan"',
  `extra_value_label` varchar(80) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Others => "(Please Specify)"',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `special_categories`
--

INSERT INTO `special_categories` (`id`, `code`, `name`, `name_ur`, `requires_extra_value`, `extra_value_label`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-1873-70e5-821e-e55a9e0cad58', 'BAJK_STAFF', 'BAJK Staff', 'بی اے جے کے کا عملہ', 0, NULL, 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1874-73a9-9692-04d7f2282fc6', 'MINOR', 'Minor', 'نابالغ', 0, NULL, 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1875-72c4-8d0c-f63cad651d52', 'PHOTO_ACCOUNT', 'Photo Account', 'فوٹو اکاؤنٹ', 0, NULL, 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1875-72c4-8d0c-f63cae3903d4', 'MUSTAHIQEEN_ZAKAT', 'Mustahiqeen-e-Zakat', 'مستحقین زکوٰۃ', 0, NULL, 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1876-7118-a186-a6d61d70fa79', 'PARDA_NASHEEN', 'Parda Nasheen', 'پردہ نشین', 0, NULL, 0, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1877-7176-8861-b3f35a735a1e', 'STUDENT', 'Student', 'طالب علم', 0, NULL, 0, 6, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1877-7176-8861-b3f35ae27570', 'VISUALLY_IMPAIRED', 'Visually Impaired Person/Blind', 'کمزور بصارت/نابینا افراد', 0, NULL, 0, 7, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1878-7291-a979-23b3eb6c44e0', 'DISASTER_IDP', 'Disaster Affectees/IDPs', 'آفت زدہ/نقل مکانی کرنے والے', 0, NULL, 0, 8, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1878-7291-a979-23b3eb7d4519', 'REGISTERED_ALIEN', 'Registered Alien', 'رجسٹرڈ غیر ملکی', 0, NULL, 0, 9, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-1879-72db-9b04-9941a3776d79', 'WIDOW', 'Widow', 'بیوہ', 0, NULL, 0, 10, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187a-72be-86d7-5fc5687733ee', 'PENSIONERS', 'Pensioners', 'پنشنرز', 0, NULL, 0, 11, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187a-72be-86d7-5fc56897e916', 'SENIOR_CITIZEN', 'Senior Citizen', 'بزرگ شہری', 0, NULL, 0, 12, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187b-7325-a822-2623b910deab', 'GOVT_EMP', 'Govt. Emp/Semi Govt. Emp', 'سرکاری/نیم سرکاری ملازم', 0, NULL, 0, 13, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187b-7325-a822-2623b943c61d', 'PENSION', 'Pension', 'پنشن', 0, NULL, 0, 14, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187c-71cb-b19d-918df6d48e6f', 'SALARY', 'Salary', 'تنخواہ', 0, NULL, 0, 15, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187d-70fb-a941-37a1186f942e', 'PHYSICAL_HANDICAP', 'Physically Handicapped', 'معذور افراد', 0, NULL, 0, 16, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187d-70fb-a941-37a118fd1fbf', 'NON_RESIDENT', 'Non-Resident (in years)', 'بیرون ملک رہائشی', 1, 'Time since residing out side Pakistan (in years)', 0, 17, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187e-70cc-8c69-662869351de5', 'ILLITERATE', 'Illiterate', 'ناخواندہ', 0, NULL, 0, 18, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-187f-7046-beb4-c8fff1ded909', 'SPECIAL_OTHERS', 'Others', 'دیگر', 0, NULL, 1, 19, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `specimen_signatures`
--

CREATE TABLE `specimen_signatures` (
  `id` char(36) NOT NULL,
  `account_holder_id` char(36) NOT NULL,
  `ss_card_number` varchar(40) DEFAULT NULL COMMENT 'SS Card number',
  `specimen_number` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '"Different Specimen Signatures" undertaking ke tehat 1 se zyada',
  `specimen_type` enum('signature','thumb_impression','stamp') NOT NULL DEFAULT 'signature',
  `signature_image_path` varchar(255) DEFAULT NULL,
  `thumb_left_image_path` varchar(255) DEFAULT NULL COMMENT 'Important Note (c)',
  `thumb_right_image_path` varchar(255) DEFAULT NULL COMMENT 'Important Note (c)',
  `organization_stamp_path` varchar(255) DEFAULT NULL COMMENT 'Company''s/Organisation''s Stamp',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statement_delivery_modes`
--

CREATE TABLE `statement_delivery_modes` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'BAJK E-Statement, Mail by Post/Courier, Hold Mail Facility',
  `name_ur` varchar(80) DEFAULT NULL,
  `requires_email` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Form footnote: email "Mandatory Field for Internet Banking & BAJK E-Statement"',
  `available_for` enum('individual','entity','both') NOT NULL DEFAULT 'both' COMMENT 'Hold Mail Facility sirf Entity AOF p.5 par hai',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statement_delivery_modes`
--

INSERT INTO `statement_delivery_modes` (`id`, `code`, `name`, `name_ur`, `requires_email`, `available_for`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18b2-71fb-9c67-6c8f6072d23a', 'E_STATEMENT', 'BAJK E-Statement', 'بی اے جے کے ای اسٹیٹمنٹ', 1, 'both', 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b2-71fb-9c67-6c8f611bb64f', 'POST_COURIER', 'Mail by Post/Courier', 'بذریعہ ڈاک یا کورئیر', 0, 'both', 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b3-71c4-a99b-3fe0061b208f', 'HOLD_MAIL', 'Hold Mail Facility', 'ڈاک روکنے کی سہولت', 0, 'both', 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `statement_frequencies`
--

CREATE TABLE `statement_frequencies` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL COMMENT 'Monthly, Quarterly, Six Monthly/Semi-Annually, Yearly',
  `name_ur` varchar(60) DEFAULT NULL,
  `months_interval` smallint(5) UNSIGNED NOT NULL COMMENT '1, 3, 6, 12',
  `available_for` enum('individual','entity','both') NOT NULL DEFAULT 'both' COMMENT 'Yearly sirf Individual AOF par, Semi-Annually sirf Entity AOF par',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statement_frequencies`
--

INSERT INTO `statement_frequencies` (`id`, `code`, `name`, `name_ur`, `months_interval`, `available_for`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18b4-7069-aac4-cd99526a4b22', 'MONTHLY', 'Monthly', 'ماہانہ', 1, 'both', 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b4-7069-aac4-cd9952c804d8', 'QUARTERLY', 'Quarterly', 'سہ ماہی', 3, 'both', 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b5-703f-a967-21b4278446fd', 'SEMI_ANNUAL', 'Six Monthly / Semi-Annually', 'ششماہی', 6, 'both', 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18b5-703f-a967-21b4286b4340', 'YEARLY', 'Yearly', 'سالانہ', 12, 'individual', 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_modes`
--

CREATE TABLE `transaction_modes` (
  `id` char(36) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'Cash, Clearing, Remittance, Collection, Others',
  `name_ur` varchar(80) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_modes`
--

INSERT INTO `transaction_modes` (`id`, `code`, `name`, `name_ur`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18c7-71a0-8e25-f9e5be80482e', 'CASH', 'Cash', 'نقد', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c8-7315-be05-e625daee41b2', 'CLEARING', 'Clearing', 'کلیئرنگ', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c9-733c-a6dd-4e7cc9046d08', 'REMITTANCE', 'Remittance', 'ترسیل', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c9-733c-a6dd-4e7cc947bfbd', 'COLLECTION', 'Collection', 'کلیکشن', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ca-70e0-a513-2a22ea5a6cd2', 'MODE_OTHERS', 'Others', 'دیگر', 1, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `ultimate_beneficial_owners`
--

CREATE TABLE `ultimate_beneficial_owners` (
  `id` char(36) NOT NULL,
  `account_id` char(36) NOT NULL,
  `customer_due_diligence_id` char(36) DEFAULT NULL,
  `name` varchar(150) NOT NULL COMMENT 'Ultimate Beneficial Owner of the Account (if different from Customer)',
  `relationship_id` char(36) DEFAULT NULL,
  `relationship_other` varchar(120) DEFAULT NULL,
  `identification_document_type_id` char(36) DEFAULT NULL,
  `identification_number` varchar(50) DEFAULT NULL COMMENT 'CNIC/SNIC/NICOP/POC/Passport/Alient Registered Card Number',
  `declaration_form_received` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'UBO Declaration Form (documentation checklist)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `region_id` bigint(20) UNSIGNED DEFAULT NULL,
  `division_id` bigint(20) UNSIGNED DEFAULT NULL,
  `head_office_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_president_office` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `is_super_admin` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `branch_id`, `region_id`, `division_id`, `head_office_id`, `is_president_office`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `is_super_admin`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, NULL, 0, 'Branch 0001-1-1', 'manager0001@bankajk.com', '2025-12-12 11:40:05', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:05', '2025-12-26 11:50:41'),
(2, 2, NULL, NULL, NULL, 0, 'Branch 0002-9-4', 'manager0002@bankajk.com', '2025-12-12 11:40:06', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:06', '2025-12-12 11:40:06'),
(3, 3, NULL, NULL, NULL, 0, 'Branch 0003-9-4', 'manager0003@bankajk.com', '2025-12-12 11:40:06', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:06', '2025-12-12 11:40:06'),
(4, 4, NULL, NULL, NULL, 0, 'Branch 0004-9-4', 'manager0004@bankajk.com', '2025-12-12 11:40:06', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:06', '2025-12-12 11:40:06'),
(5, 5, NULL, NULL, NULL, 0, 'Branch 0005-8-3', 'manager0005@bankajk.com', '2025-12-12 11:40:06', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:06', '2025-12-12 11:40:06'),
(6, 6, NULL, NULL, NULL, 0, 'Branch 0006-5-2', 'manager0006@bankajk.com', '2025-12-12 11:40:07', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:07', '2025-12-12 11:40:07'),
(7, 7, NULL, NULL, NULL, 0, 'Branch 0007-8-3', 'manager0007@bankajk.com', '2025-12-12 11:40:07', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:07', '2025-12-12 11:40:07'),
(8, 8, NULL, NULL, NULL, 0, 'Branch 0008-2-1', 'manager0008@bankajk.com', '2025-12-12 11:40:07', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:07', '2025-12-12 11:40:07'),
(9, 9, NULL, NULL, NULL, 0, 'Branch 0009-9-4', 'manager0009@bankajk.com', '2025-12-12 11:40:07', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:07', '2025-12-12 11:40:07'),
(10, 10, NULL, NULL, NULL, 0, 'Branch 0010-4-2', 'manager0010@bankajk.com', '2025-12-12 11:40:08', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:08', '2025-12-12 11:40:08'),
(11, 11, NULL, NULL, NULL, 0, 'Branch 0011-4-2', 'manager0011@bankajk.com', '2025-12-12 11:40:08', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:08', '2025-12-12 11:40:08'),
(12, 12, NULL, NULL, NULL, 0, 'Branch 0012-10-4', 'manager0012@bankajk.com', '2025-12-12 11:40:08', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:08', '2025-12-12 11:40:08'),
(13, 13, NULL, NULL, NULL, 0, 'Branch 0013-5-2', 'manager0013@bankajk.com', '2025-12-12 11:40:08', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:08', '2025-12-12 11:40:08'),
(14, 14, NULL, NULL, NULL, 0, 'Branch 0014-8-3', 'manager0014@bankajk.com', '2025-12-12 11:40:09', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:09', '2025-12-12 11:40:09'),
(15, 15, NULL, NULL, NULL, 0, 'Branch 0015-9-4', 'manager0015@bankajk.com', '2025-12-12 11:40:09', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:09', '2025-12-12 11:40:09'),
(16, 16, NULL, NULL, NULL, 0, 'Branch 0016-5-2', 'manager0016@bankajk.com', '2025-12-12 11:40:09', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:09', '2025-12-12 11:40:09'),
(17, 17, NULL, NULL, NULL, 0, 'Branch 0017-1-1', 'manager0017@bankajk.com', '2025-12-12 11:40:10', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:10', '2025-12-12 11:40:10'),
(18, 18, NULL, NULL, NULL, 0, 'Branch 0018-1-1', 'manager0018@bankajk.com', '2025-12-12 11:40:10', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:10', '2025-12-12 11:40:10'),
(19, 19, NULL, NULL, NULL, 0, 'Branch 0019-1-1', 'manager0019@bankajk.com', '2025-12-12 11:40:10', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:10', '2025-12-12 11:40:10'),
(20, 20, NULL, NULL, NULL, 0, 'Branch 0020-8-3', 'manager0020@bankajk.com', '2025-12-12 11:40:10', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:10', '2025-12-12 11:40:10'),
(21, 21, NULL, NULL, NULL, 0, 'Branch 0021-8-3', 'manager0021@bankajk.com', '2025-12-12 11:40:11', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:11', '2025-12-12 11:40:11'),
(22, 22, NULL, NULL, NULL, 0, 'Branch 0022-1-1', 'manager0022@bankajk.com', '2025-12-12 11:40:11', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:11', '2025-12-12 11:40:11'),
(23, 23, NULL, NULL, NULL, 0, 'Branch 0023-4-2', 'manager0023@bankajk.com', '2025-12-12 11:40:11', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:11', '2025-12-12 11:40:11'),
(24, 24, NULL, NULL, NULL, 0, 'Branch 0024-1-1', 'manager0024@bankajk.com', '2025-12-12 11:40:11', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:11', '2025-12-12 11:40:11'),
(25, 25, NULL, NULL, NULL, 0, 'Branch 0025-5-2', 'manager0025@bankajk.com', '2025-12-12 11:40:12', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:12', '2025-12-12 11:40:12'),
(26, 26, NULL, NULL, NULL, 0, 'Branch 0026-8-3', 'manager0026@bankajk.com', '2025-12-12 11:40:12', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:12', '2025-12-12 11:40:12'),
(27, 27, NULL, NULL, NULL, 0, 'Branch 0027-9-4', 'manager0027@bankajk.com', '2025-12-12 11:40:12', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:12', '2025-12-12 11:40:12'),
(28, 28, NULL, NULL, NULL, 0, 'Branch 0028-1-1', 'manager0028@bankajk.com', '2025-12-12 11:40:12', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:12', '2025-12-12 11:40:12'),
(29, 29, NULL, NULL, NULL, 0, 'Branch 0029-10-4', 'manager0029@bankajk.com', '2025-12-12 11:40:13', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:13', '2026-02-20 05:12:47'),
(30, 30, NULL, NULL, NULL, 0, 'Branch 0030-5-2', 'manager0030@bankajk.com', '2025-12-12 11:40:13', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:13', '2025-12-12 11:40:13'),
(31, 31, NULL, NULL, NULL, 0, 'Branch 0031-8-3', 'manager0031@bankajk.com', '2025-12-12 11:40:13', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:13', '2025-12-12 11:40:13'),
(32, 32, NULL, NULL, NULL, 0, 'Branch 0032-7-2', 'manager0032@bankajk.com', '2025-12-12 11:40:14', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:14', '2025-12-12 11:40:14'),
(33, 33, NULL, NULL, NULL, 0, 'Branch 0033-10-4', 'manager0033@bankajk.com', '2025-12-12 11:40:14', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:14', '2025-12-12 11:40:14'),
(34, 34, NULL, NULL, NULL, 0, 'Branch 0034-3-1', 'manager0034@bankajk.com', '2025-12-12 11:40:14', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:14', '2025-12-12 11:40:14'),
(35, 35, NULL, NULL, NULL, 0, 'Branch 0035-10-4', 'manager0035@bankajk.com', '2025-12-12 11:40:15', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:15', '2025-12-12 11:40:15'),
(36, 36, NULL, NULL, NULL, 0, 'Branch 0036-8-3', 'manager0036@bankajk.com', '2025-12-12 11:40:15', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:15', '2025-12-12 11:40:15'),
(37, 37, NULL, NULL, NULL, 0, 'Branch 0037-3-1', 'manager0037@bankajk.com', '2025-12-12 11:40:15', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:15', '2025-12-12 11:40:15'),
(38, 38, NULL, NULL, NULL, 0, 'Branch 0038-6-2', 'manager0038@bankajk.com', '2025-12-12 11:40:15', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:15', '2025-12-12 11:40:15'),
(39, 39, NULL, NULL, NULL, 0, 'Branch 0039-5-2', 'manager0039@bankajk.com', '2025-12-12 11:40:16', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:16', '2025-12-12 11:40:16'),
(40, 40, NULL, NULL, NULL, 0, 'Branch 0040-1-1', 'manager0040@bankajk.com', '2025-12-12 11:40:16', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:16', '2025-12-12 11:40:16'),
(41, 41, NULL, NULL, NULL, 0, 'Branch 0041-7-2', 'manager0041@bankajk.com', '2025-12-12 11:40:16', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:16', '2025-12-12 11:40:16'),
(42, 42, NULL, NULL, NULL, 0, 'Branch 0042-8-3', 'manager0042@bankajk.com', '2025-12-12 11:40:16', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:16', '2025-12-12 11:40:16'),
(43, 43, NULL, NULL, NULL, 0, 'Branch 0043-2-1', 'manager0043@bankajk.com', '2025-12-12 11:40:17', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:17', '2025-12-12 11:40:17'),
(44, 44, NULL, NULL, NULL, 0, 'Branch 0044-8-3', 'manager0044@bankajk.com', '2025-12-12 11:40:17', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:17', '2025-12-12 11:40:17'),
(45, 45, NULL, NULL, NULL, 0, 'Branch 0045-9-4', 'manager0045@bankajk.com', '2025-12-12 11:40:17', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:17', '2025-12-12 11:40:17'),
(46, 46, NULL, NULL, NULL, 0, 'Branch 0046-9-4', 'manager0046@bankajk.com', '2025-12-12 11:40:18', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:18', '2025-12-12 11:40:18'),
(47, 47, NULL, NULL, NULL, 0, 'Branch 0047-9-4', 'manager0047@bankajk.com', '2025-12-12 11:40:18', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:18', '2025-12-12 11:40:18'),
(48, 48, NULL, NULL, NULL, 0, 'Branch 0048-8-3', 'manager0048@bankajk.com', '2025-12-12 11:40:18', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:18', '2025-12-12 11:40:18'),
(49, 49, NULL, NULL, NULL, 0, 'Branch 0049-7-2', 'manager0049@bankajk.com', '2025-12-12 11:40:18', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:18', '2025-12-12 11:40:18'),
(50, 50, NULL, NULL, NULL, 0, 'Branch 0050-5-2', 'manager0050@bankajk.com', '2025-12-12 11:40:19', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:19', '2025-12-12 11:40:19'),
(51, 51, NULL, NULL, NULL, 0, 'Branch 0051-8-3', 'manager0051@bankajk.com', '2025-12-12 11:40:19', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:19', '2025-12-12 11:40:19'),
(52, 52, NULL, NULL, NULL, 0, 'Branch 0052-2-1', 'manager0052@bankajk.com', '2025-12-12 11:40:19', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:19', '2025-12-12 11:40:19'),
(53, 53, NULL, NULL, NULL, 0, 'Branch 0053-1-1', 'manager0053@bankajk.com', '2025-12-12 11:40:19', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:19', '2025-12-12 11:40:19'),
(54, 54, NULL, NULL, NULL, 0, 'Branch 0054-9-4', 'manager0054@bankajk.com', '2025-12-12 11:40:20', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:20', '2025-12-12 11:40:20'),
(55, 55, NULL, NULL, NULL, 0, 'Branch 0055-8-3', 'manager0055@bankajk.com', '2025-12-12 11:40:20', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:20', '2025-12-12 11:40:20'),
(56, 56, NULL, NULL, NULL, 0, 'Branch 0056-5-2', 'manager0056@bankajk.com', '2025-12-12 11:40:20', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:20', '2025-12-12 11:40:20'),
(57, 57, NULL, NULL, NULL, 0, 'Branch 0057-9-4', 'manager0057@bankajk.com', '2025-12-12 11:40:21', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:21', '2025-12-12 11:40:21'),
(58, 58, NULL, NULL, NULL, 0, 'Branch 0058-9-4', 'manager0058@bankajk.com', '2025-12-12 11:40:21', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:21', '2025-12-12 11:40:21'),
(59, 59, NULL, NULL, NULL, 0, 'Branch 0059-10-4', 'manager0059@bankajk.com', '2025-12-12 11:40:21', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:21', '2025-12-12 11:40:21'),
(60, 60, NULL, NULL, NULL, 0, 'Branch 0060-5-2', 'manager0060@bankajk.com', '2025-12-12 11:40:21', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:21', '2025-12-12 11:40:21'),
(61, 61, NULL, NULL, NULL, 0, 'Branch 0061-1-1', 'manager0061@bankajk.com', '2025-12-12 11:40:22', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:22', '2025-12-12 11:40:22'),
(62, 62, NULL, NULL, NULL, 0, 'Branch 0062-3-1', 'manager0062@bankajk.com', '2025-12-12 11:40:22', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:22', '2025-12-12 11:40:22'),
(63, 63, NULL, NULL, NULL, 0, 'Branch 0063-2-1', 'manager0063@bankajk.com', '2025-12-12 11:40:22', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:22', '2025-12-12 11:40:22'),
(64, 64, NULL, NULL, NULL, 0, 'Branch 0064-8-3', 'manager0064@bankajk.com', '2025-12-12 11:40:22', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:22', '2025-12-12 11:40:22'),
(65, 65, NULL, NULL, NULL, 0, 'Branch 0065-8-3', 'manager0065@bankajk.com', '2025-12-12 11:40:23', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:23', '2025-12-12 11:40:23'),
(66, 66, NULL, NULL, NULL, 0, 'Branch 0066-8-3', 'manager0066@bankajk.com', '2025-12-12 11:40:23', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:23', '2025-12-12 11:40:23'),
(67, 67, NULL, NULL, NULL, 0, 'Branch 0067-1-1', 'manager0067@bankajk.com', '2025-12-12 11:40:23', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:23', '2025-12-12 11:40:23'),
(68, 68, NULL, NULL, NULL, 0, 'Branch 0068-7-2', 'manager0068@bankajk.com', '2025-12-12 11:40:24', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:24', '2025-12-12 11:40:24'),
(69, 69, NULL, NULL, NULL, 0, 'Branch 0069-2-1', 'manager0069@bankajk.com', '2025-12-12 11:40:24', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:24', '2025-12-12 11:40:24'),
(70, 70, NULL, NULL, NULL, 0, 'Branch 0070-1-1', 'manager0070@bankajk.com', '2025-12-12 11:40:24', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:24', '2025-12-12 11:40:24'),
(71, 71, NULL, NULL, NULL, 0, 'Branch 0071-7-2', 'manager0071@bankajk.com', '2025-12-12 11:40:24', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:24', '2025-12-12 11:40:24'),
(72, 72, NULL, NULL, NULL, 0, 'Branch 0072-7-2', 'manager0072@bankajk.com', '2025-12-12 11:40:25', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:25', '2025-12-12 11:40:25'),
(73, 73, NULL, NULL, NULL, 0, 'Branch 0073-10-4', 'manager0073@bankajk.com', '2025-12-12 11:40:25', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:25', '2025-12-12 11:40:25'),
(74, 74, NULL, NULL, NULL, 0, 'Branch 0074-7-2', 'manager0074@bankajk.com', '2025-12-12 11:40:25', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:25', '2025-12-12 11:40:25'),
(75, 75, NULL, NULL, NULL, 0, 'Branch 0075-8-3', 'manager0075@bankajk.com', '2025-12-12 11:40:25', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:25', '2025-12-12 11:40:25'),
(76, 76, NULL, NULL, NULL, 0, 'Branch 0076-3-1', 'manager0076@bankajk.com', '2025-12-12 11:40:26', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:26', '2025-12-12 11:40:26'),
(77, 77, NULL, NULL, NULL, 0, 'Branch 0077-1-1', 'manager0077@bankajk.com', '2025-12-12 11:40:26', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:26', '2025-12-12 11:40:26'),
(78, 78, NULL, NULL, NULL, 0, 'Branch 0078-10-4', 'manager0078@bankajk.com', '2025-12-12 11:40:26', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:26', '2025-12-12 11:40:26'),
(79, 79, NULL, NULL, NULL, 0, 'Branch 0079-9-4', 'manager0079@bankajk.com', '2025-12-12 11:40:27', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:27', '2025-12-12 11:40:27'),
(80, 80, NULL, NULL, NULL, 0, 'Branch 0080-9-4', 'manager0080@bankajk.com', '2025-12-12 11:40:27', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:27', '2025-12-12 11:40:27'),
(81, 81, NULL, NULL, NULL, 0, 'Branch 0081-9-4', 'manager0081@bankajk.com', '2025-12-12 11:40:27', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:27', '2025-12-12 11:40:27'),
(82, 82, NULL, NULL, NULL, 0, 'Branch 0082-8-3', 'manager0082@bankajk.com', '2025-12-12 11:40:27', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:27', '2025-12-12 11:40:27'),
(83, 83, NULL, NULL, NULL, 0, 'Branch 0083-5-2', 'manager0083@bankajk.com', '2025-12-12 11:40:28', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:28', '2025-12-12 11:40:28'),
(84, 84, NULL, NULL, NULL, 0, 'Branch 0084-3-1', 'manager0084@bankajk.com', '2025-12-12 11:40:28', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:28', '2025-12-12 11:40:28'),
(85, 85, NULL, NULL, NULL, 0, 'Branch 0085-1-1', 'manager0085@bankajk.com', '2025-12-12 11:40:28', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:28', '2025-12-12 11:40:28'),
(86, 86, NULL, NULL, NULL, 0, 'Branch 0086-4-2', 'manager0086@bankajk.com', '2025-12-12 11:40:28', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:28', '2025-12-12 11:40:28'),
(87, 87, NULL, NULL, NULL, 0, 'Branch 0087-9-4', 'manager0087@bankajk.com', '2025-12-12 11:40:29', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29'),
(88, NULL, NULL, NULL, NULL, 0, 'Ali Raza Marchal', 'superadmin@example.com', '2025-12-12 11:40:29', '123', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29');

-- --------------------------------------------------------

--
-- Table structure for table `wealth_sources`
--

CREATE TABLE `wealth_sources` (
  `id` char(36) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL COMMENT 'Gift, Personal Savings, Rented Property/Property Sales, Inheritance, Others',
  `name_ur` varchar(120) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wealth_sources`
--

INSERT INTO `wealth_sources` (`id`, `code`, `name`, `name_ur`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18c4-70dd-af21-1ab5ae06a177', 'GIFT', 'Gift', 'تحفہ', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c5-7238-a956-432e34f01a97', 'PERSONAL_SAVINGS', 'Personal Savings', 'ذاتی بچت', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c6-733e-8501-e3e531070c9f', 'RENTED_PROPERTY_SALES', 'Rented Property/Property Sales', 'کرایہ/جائیداد کی فروخت', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c6-733e-8501-e3e5311b28df', 'INHERITANCE', 'Inheritance', 'وراثت', 0, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18c6-733e-8501-e3e531ed3388', 'WEALTH_OTHERS', 'Others', 'دیگر', 1, 5, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `zakat_exemption_reasons`
--

CREATE TABLE `zakat_exemption_reasons` (
  `id` char(36) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'Non-Muslim, Foreigner, Due to Fiqah, Others',
  `name_ur` varchar(80) DEFAULT NULL,
  `is_other` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zakat_exemption_reasons`
--

INSERT INTO `zakat_exemption_reasons` (`id`, `code`, `name`, `name_ur`, `is_other`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('01a13aef-18ad-7230-9b25-dc8949cea0c2', 'NON_MUSLIM', 'Non-Muslim', 'غیر مسلم', 0, 1, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ad-7230-9b25-dc894acc39cc', 'FOREIGNER', 'Foreigner', 'غیر ملکی', 0, 2, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ae-73ea-8130-620670024874', 'DUE_TO_FIQAH', 'Due to Fiqah', 'فقہ کی وجہ سے', 0, 3, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50'),
('01a13aef-18ae-73ea-8130-620670fa9848', 'ZAKAT_OTHERS', 'Others', 'دیگر', 1, 4, 1, '2026-10-14 15:01:50', '2026-10-14 15:01:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accounts_account_number_unique` (`account_number`),
  ADD UNIQUE KEY `accounts_iban_unique` (`iban`),
  ADD KEY `accounts_currency_id_foreign` (`currency_id`),
  ADD KEY `accounts_operating_instruction_id_foreign` (`operating_instruction_id`),
  ADD KEY `accounts_statement_delivery_mode_id_foreign` (`statement_delivery_mode_id`),
  ADD KEY `accounts_statement_frequency_id_foreign` (`statement_frequency_id`),
  ADD KEY `accounts_mailing_address_id_foreign` (`mailing_address_id`),
  ADD KEY `accounts_created_by_foreign` (`created_by`),
  ADD KEY `accounts_updated_by_foreign` (`updated_by`),
  ADD KEY `accounts_opened_by_foreign` (`opened_by`),
  ADD KEY `accounts_approved_by_foreign` (`approved_by`),
  ADD KEY `accounts_branch_id_status_index` (`branch_id`,`status`),
  ADD KEY `accounts_account_product_id_status_index` (`account_product_id`,`status`);

--
-- Indexes for table `account_cheque_books`
--
ALTER TABLE `account_cheque_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_cheque_books_account_id_index` (`account_id`);

--
-- Indexes for table `account_debit_cards`
--
ALTER TABLE `account_debit_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_debit_cards_account_holder_id_foreign` (`account_holder_id`),
  ADD KEY `account_debit_cards_card_type_id_foreign` (`card_type_id`),
  ADD KEY `account_debit_cards_account_id_index` (`account_id`);

--
-- Indexes for table `account_holders`
--
ALTER TABLE `account_holders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_holder_unique` (`account_id`,`customer_id`,`holder_role`),
  ADD KEY `account_holders_relationship_id_foreign` (`relationship_id`),
  ADD KEY `account_holders_account_id_is_active_index` (`account_id`,`is_active`),
  ADD KEY `account_holders_customer_id_is_active_index` (`customer_id`,`is_active`);

--
-- Indexes for table `account_opening_histories`
--
ALTER TABLE `account_opening_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_opening_histories_user_id_foreign` (`user_id`),
  ADD KEY `aof_history_request_time` (`account_opening_request_id`,`created_at`);

--
-- Indexes for table `account_opening_paper_forms`
--
ALTER TABLE `account_opening_paper_forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_opening_paper_forms_account_opening_request_id_unique` (`account_opening_request_id`);

--
-- Indexes for table `account_opening_requests`
--
ALTER TABLE `account_opening_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_opening_requests_request_number_unique` (`request_number`),
  ADD KEY `account_opening_requests_customer_id_foreign` (`customer_id`),
  ADD KEY `account_opening_requests_account_id_foreign` (`account_id`),
  ADD KEY `account_opening_requests_created_by_foreign` (`created_by`),
  ADD KEY `account_opening_requests_updated_by_foreign` (`updated_by`),
  ADD KEY `account_opening_requests_branch_id_status_index` (`branch_id`,`status`),
  ADD KEY `account_opening_requests_request_date_index` (`request_date`),
  ADD KEY `account_opening_requests_submitted_by_foreign` (`submitted_by`),
  ADD KEY `account_opening_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `account_opening_requests_rejected_by_foreign` (`rejected_by`),
  ADD KEY `account_opening_requests_unlocked_by_foreign` (`unlocked_by`),
  ADD KEY `account_opening_requests_form_edition_index` (`form_edition`);

--
-- Indexes for table `account_products`
--
ALTER TABLE `account_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_products_code_unique` (`code`),
  ADD KEY `account_products_product_class_currency_type_is_active_index` (`product_class`,`currency_type`,`is_active`);

--
-- Indexes for table `account_purposes`
--
ALTER TABLE `account_purposes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_purposes_code_unique` (`code`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `aksics`
--
ALTER TABLE `aksics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aksics_cnic_unique` (`cnic`),
  ADD UNIQUE KEY `aksics_application_no_unique` (`application_no`),
  ADD KEY `aksics_created_by_foreign` (`created_by`),
  ADD KEY `aksics_updated_by_foreign` (`updated_by`),
  ADD KEY `aksics_aksic_rule_id_foreign` (`aksic_rule_id`),
  ADD KEY `aksics_account_no_index` (`account_no`),
  ADD KEY `aksics_branch_id_created_at_index` (`branch_id`,`created_at`),
  ADD KEY `aksics_status_created_at_index` (`status`,`created_at`),
  ADD KEY `aksics_created_at_id_index` (`created_at`,`id`),
  ADD KEY `aksics_district_id_index` (`district_id`),
  ADD KEY `aksics_quota_index` (`quota`),
  ADD KEY `aksics_name_index` (`name`),
  ADD KEY `aksics_disbursement_date_index` (`disbursement_date`);

--
-- Indexes for table `aksic_amortizations`
--
ALTER TABLE `aksic_amortizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aksic_amortizations_aksic_id_installment_no_unique` (`aksic_id`,`installment_no`),
  ADD KEY `aksic_amortizations_aksic_id_due_date_index` (`aksic_id`,`due_date`),
  ADD KEY `aksic_amortizations_created_by_foreign` (`created_by`),
  ADD KEY `aksic_amortizations_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `aksic_applications`
--
ALTER TABLE `aksic_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aksic_applications_applicant_id_unique` (`applicant_id`),
  ADD UNIQUE KEY `aksic_applications_cnic_unique` (`cnic`),
  ADD UNIQUE KEY `aksic_applications_application_no_unique` (`application_no`);

--
-- Indexes for table `aksic_application_education`
--
ALTER TABLE `aksic_application_education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aksic_application_education_aksic_application_id_foreign` (`aksic_application_id`),
  ADD KEY `aksic_application_education_applicant_id_foreign` (`applicant_id`);

--
-- Indexes for table `aksic_application_status_logs`
--
ALTER TABLE `aksic_application_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aksic_application_status_logs_aksic_application_id_foreign` (`aksic_application_id`),
  ADD KEY `aksic_application_status_logs_applicant_id_foreign` (`applicant_id`);

--
-- Indexes for table `aksic_budgets`
--
ALTER TABLE `aksic_budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aksic_budgets_created_by_foreign` (`created_by`),
  ADD KEY `aksic_budgets_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `aksic_budget_allocations`
--
ALTER TABLE `aksic_budget_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aksic_budget_allocations_aksic_budget_id_district_id_unique` (`aksic_budget_id`,`district_id`),
  ADD KEY `aksic_budget_allocations_district_id_foreign` (`district_id`),
  ADD KEY `aksic_budget_allocations_aksic_rule_id_foreign` (`aksic_rule_id`),
  ADD KEY `aksic_budget_allocations_created_by_foreign` (`created_by`),
  ADD KEY `aksic_budget_allocations_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `aksic_budget_revisions`
--
ALTER TABLE `aksic_budget_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aksic_budget_revisions_district_id_foreign` (`district_id`),
  ADD KEY `aksic_budget_revisions_created_by_foreign` (`created_by`),
  ADD KEY `aksic_budget_revisions_updated_by_foreign` (`updated_by`),
  ADD KEY `aksic_budget_revisions_aksic_budget_id_created_at_index` (`aksic_budget_id`,`created_at`);

--
-- Indexes for table `aksic_business_categories`
--
ALTER TABLE `aksic_business_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aksic_business_categories_parent_id_index` (`parent_id`);

--
-- Indexes for table `aksic_claims`
--
ALTER TABLE `aksic_claims`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aksic_claims_claim_no_unique` (`claim_no`),
  ADD KEY `aksic_claims_district_id_foreign` (`district_id`),
  ADD KEY `aksic_claims_region_id_foreign` (`region_id`),
  ADD KEY `aksic_claims_branch_id_foreign` (`branch_id`),
  ADD KEY `aksic_claims_created_by_foreign` (`created_by`),
  ADD KEY `aksic_claims_updated_by_foreign` (`updated_by`),
  ADD KEY `aksic_claims_period_from_period_to_index` (`period_from`,`period_to`),
  ADD KEY `aksic_claims_status_index` (`status`);

--
-- Indexes for table `aksic_claim_items`
--
ALTER TABLE `aksic_claim_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aksic_claim_items_aksic_claim_id_aksic_id_unique` (`aksic_claim_id`,`aksic_id`),
  ADD KEY `aksic_claim_items_aksic_id_foreign` (`aksic_id`),
  ADD KEY `aksic_claim_items_region_id_foreign` (`region_id`),
  ADD KEY `aksic_claim_items_branch_id_foreign` (`branch_id`),
  ADD KEY `aksic_claim_items_mis_index` (`district_id`,`region_id`,`branch_id`,`gender`);

--
-- Indexes for table `aksic_rules`
--
ALTER TABLE `aksic_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aksic_rules_district_id_unique` (`district_id`),
  ADD KEY `aksic_rules_created_by_foreign` (`created_by`),
  ADD KEY `aksic_rules_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `boxes`
--
ALTER TABLE `boxes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `boxes_box_number_unique` (`box_number`),
  ADD KEY `boxes_boxable_type_boxable_id_index` (`boxable_type`,`boxable_id`),
  ADD KEY `boxes_created_by_foreign` (`created_by`),
  ADD KEY `idx_boxable` (`boxable_type`,`boxable_id`),
  ADD KEY `idx_box_status` (`status`),
  ADD KEY `idx_box_number` (`box_number`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branches_code_unique` (`code`),
  ADD KEY `branches_region_id_foreign` (`region_id`),
  ADD KEY `branches_district_id_foreign` (`district_id`);

--
-- Indexes for table `branch_targets`
--
ALTER TABLE `branch_targets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_targets_branch_id_fiscal_year_unique` (`branch_id`,`fiscal_year`),
  ADD KEY `branch_targets_created_by_user_id_foreign` (`created_by_user_id`),
  ADD KEY `branch_targets_updated_by_user_id_foreign` (`updated_by_user_id`);

--
-- Indexes for table `business_natures`
--
ALTER TABLE `business_natures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `business_natures_code_unique` (`code`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `card_types`
--
ALTER TABLE `card_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `card_types_code_unique` (`code`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cdd_account_purpose`
--
ALTER TABLE `cdd_account_purpose`
  ADD UNIQUE KEY `cdd_account_purpose_unique` (`customer_due_diligence_id`,`account_purpose_id`),
  ADD KEY `cdd_account_purpose_account_purpose_id_foreign` (`account_purpose_id`);

--
-- Indexes for table `cdd_counter_party_type`
--
ALTER TABLE `cdd_counter_party_type`
  ADD UNIQUE KEY `cdd_counter_party_unique` (`customer_due_diligence_id`,`counter_party_type_id`),
  ADD KEY `cdd_counter_party_type_counter_party_type_id_foreign` (`counter_party_type_id`);

--
-- Indexes for table `cdd_income_source`
--
ALTER TABLE `cdd_income_source`
  ADD UNIQUE KEY `cdd_income_source_unique` (`customer_due_diligence_id`,`income_source_id`),
  ADD KEY `cdd_income_source_income_source_id_foreign` (`income_source_id`);

--
-- Indexes for table `cdd_transaction_mode`
--
ALTER TABLE `cdd_transaction_mode`
  ADD UNIQUE KEY `cdd_txn_mode_unique` (`customer_due_diligence_id`,`transaction_mode_id`,`direction`),
  ADD KEY `cdd_transaction_mode_transaction_mode_id_foreign` (`transaction_mode_id`);

--
-- Indexes for table `cdd_wealth_source`
--
ALTER TABLE `cdd_wealth_source`
  ADD UNIQUE KEY `cdd_wealth_source_unique` (`customer_due_diligence_id`,`wealth_source_id`),
  ADD KEY `cdd_wealth_source_wealth_source_id_foreign` (`wealth_source_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `complaints_complaint_number_unique` (`complaint_number`),
  ADD KEY `complaints_branch_id_foreign` (`branch_id`),
  ADD KEY `complaints_assigned_by_foreign` (`assigned_by`),
  ADD KEY `complaints_resolved_by_foreign` (`resolved_by`),
  ADD KEY `complaints_grievance_type_index` (`grievance_type`),
  ADD KEY `complaints_status_priority_index` (`status`,`priority`),
  ADD KEY `complaints_complaint_number_index` (`complaint_number`),
  ADD KEY `complaints_assigned_to_index` (`assigned_to`),
  ADD KEY `complaints_region_id_index` (`region_id`),
  ADD KEY `complaints_division_id_index` (`division_id`),
  ADD KEY `complaints_created_at_status_index` (`created_at`,`status`),
  ADD KEY `complaints_sla_breached_index` (`sla_breached`);

--
-- Indexes for table `complaint_assignments`
--
ALTER TABLE `complaint_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_assignments_assigned_to_foreign` (`assigned_to`),
  ADD KEY `complaint_assignments_assigned_by_foreign` (`assigned_by`),
  ADD KEY `complaint_assignments_created_by_foreign` (`created_by`),
  ADD KEY `complaint_assignments_updated_by_foreign` (`updated_by`),
  ADD KEY `idx_assignments` (`complaint_id`,`assigned_to`,`is_active`);

--
-- Indexes for table `complaint_attachments`
--
ALTER TABLE `complaint_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_attachments_created_by_foreign` (`created_by`),
  ADD KEY `complaint_attachments_updated_by_foreign` (`updated_by`),
  ADD KEY `idx_complaint_attachments` (`complaint_id`);

--
-- Indexes for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_categories_created_by_foreign` (`created_by`),
  ADD KEY `complaint_categories_updated_by_foreign` (`updated_by`),
  ADD KEY `complaint_categories_parent_category_id_foreign` (`parent_category_id`);

--
-- Indexes for table `complaint_comments`
--
ALTER TABLE `complaint_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_comments_created_by_foreign` (`created_by`),
  ADD KEY `complaint_comments_updated_by_foreign` (`updated_by`),
  ADD KEY `idx_complaint_comments` (`complaint_id`,`created_at`);

--
-- Indexes for table `complaint_escalations`
--
ALTER TABLE `complaint_escalations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_escalations_escalated_from_foreign` (`escalated_from`),
  ADD KEY `complaint_escalations_escalated_to_foreign` (`escalated_to`),
  ADD KEY `complaint_escalations_created_by_foreign` (`created_by`),
  ADD KEY `complaint_escalations_updated_by_foreign` (`updated_by`),
  ADD KEY `idx_complaint_escalations` (`complaint_id`,`escalation_level`);

--
-- Indexes for table `complaint_histories`
--
ALTER TABLE `complaint_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_histories_complaint_id_foreign` (`complaint_id`),
  ADD KEY `complaint_histories_status_id_foreign` (`status_id`),
  ADD KEY `complaint_histories_performed_by_foreign` (`performed_by`),
  ADD KEY `complaint_histories_created_by_foreign` (`created_by`),
  ADD KEY `complaint_histories_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `complaint_metrics`
--
ALTER TABLE `complaint_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_metrics_created_by_foreign` (`created_by`),
  ADD KEY `complaint_metrics_updated_by_foreign` (`updated_by`),
  ADD KEY `idx_complaint_metrics` (`complaint_id`);

--
-- Indexes for table `complaint_status_types`
--
ALTER TABLE `complaint_status_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `complaint_status_types_code_unique` (`code`);

--
-- Indexes for table `complaint_templates`
--
ALTER TABLE `complaint_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_templates_created_by_foreign` (`created_by`),
  ADD KEY `complaint_templates_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `complaint_watchers`
--
ALTER TABLE `complaint_watchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_watcher` (`complaint_id`,`user_id`),
  ADD KEY `complaint_watchers_user_id_foreign` (`user_id`),
  ADD KEY `complaint_watchers_created_by_foreign` (`created_by`),
  ADD KEY `complaint_watchers_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `complaint_witnesses`
--
ALTER TABLE `complaint_witnesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_witnesses_created_by_foreign` (`created_by`),
  ADD KEY `complaint_witnesses_updated_by_foreign` (`updated_by`),
  ADD KEY `complaint_witnesses_complaint_id_index` (`complaint_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `controlling_person_types`
--
ALTER TABLE `controlling_person_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `controlling_person_types_code_unique` (`code`);

--
-- Indexes for table `counter_party_types`
--
ALTER TABLE `counter_party_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `counter_party_types_code_unique` (`code`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `countries_iso2_unique` (`iso2`),
  ADD UNIQUE KEY `countries_iso3_unique` (`iso3`);

--
-- Indexes for table `crs_entity_classifications`
--
ALTER TABLE `crs_entity_classifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `crs_entity_classifications_code_unique` (`code`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `currencies_code_unique` (`code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_cif_number_unique` (`cif_number`),
  ADD KEY `customers_customer_category_id_foreign` (`customer_category_id`),
  ADD KEY `customers_economic_sector_id_foreign` (`economic_sector_id`),
  ADD KEY `customers_zakat_exemption_reason_id_foreign` (`zakat_exemption_reason_id`),
  ADD KEY `customers_created_by_foreign` (`created_by`),
  ADD KEY `customers_updated_by_foreign` (`updated_by`),
  ADD KEY `customers_verified_by_foreign` (`verified_by`),
  ADD KEY `customers_approved_by_foreign` (`approved_by`),
  ADD KEY `customers_customer_type_status_index` (`customer_type`,`status`),
  ADD KEY `customers_branch_id_status_index` (`branch_id`,`status`),
  ADD KEY `customers_is_pep_index` (`is_pep`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_addresses_country_id_foreign` (`country_id`),
  ADD KEY `customer_addresses_customer_id_address_type_index` (`customer_id`,`address_type`);

--
-- Indexes for table `customer_categories`
--
ALTER TABLE `customer_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_categories_code_unique` (`code`),
  ADD KEY `customer_categories_applies_to_is_active_index` (`applies_to`,`is_active`);

--
-- Indexes for table `customer_contacts`
--
ALTER TABLE `customer_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_contacts_customer_id_contact_type_index` (`customer_id`,`contact_type`),
  ADD KEY `customer_contacts_value_index` (`value`);

--
-- Indexes for table `customer_controlling_persons`
--
ALTER TABLE `customer_controlling_persons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_controlling_persons_person_customer_id_foreign` (`person_customer_id`),
  ADD KEY `customer_controlling_persons_controlling_person_type_id_foreign` (`controlling_person_type_id`),
  ADD KEY `customer_controlling_persons_customer_id_declaration_basis_index` (`customer_id`,`declaration_basis`);

--
-- Indexes for table `customer_documents`
--
ALTER TABLE `customer_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_documents_account_opening_request_id_foreign` (`account_opening_request_id`),
  ADD KEY `customer_documents_document_type_id_foreign` (`document_type_id`),
  ADD KEY `customer_documents_verified_by_foreign` (`verified_by`),
  ADD KEY `customer_documents_customer_id_status_index` (`customer_id`,`status`),
  ADD KEY `customer_documents_account_id_status_index` (`account_id`,`status`),
  ADD KEY `customer_documents_expiry_date_index` (`expiry_date`);

--
-- Indexes for table `customer_due_diligences`
--
ALTER TABLE `customer_due_diligences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cdd_account_customer_unique` (`account_id`,`customer_id`,`cdd_type`),
  ADD KEY `customer_due_diligences_fund_provider_relationship_id_foreign` (`fund_provider_relationship_id`),
  ADD KEY `customer_due_diligences_home_remittance_country_id_foreign` (`home_remittance_country_id`),
  ADD KEY `customer_due_diligences_customer_id_index` (`customer_id`);

--
-- Indexes for table `customer_fatca_details`
--
ALTER TABLE `customer_fatca_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_fatca_details_customer_id_unique` (`customer_id`),
  ADD KEY `customer_fatca_details_incorporation_country_id_foreign` (`incorporation_country_id`),
  ADD KEY `customer_fatca_details_crs_entity_classification_id_foreign` (`crs_entity_classification_id`);

--
-- Indexes for table `customer_identifications`
--
ALTER TABLE `customer_identifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cust_ident_unique` (`customer_id`,`identification_document_type_id`,`document_number`),
  ADD KEY `customer_identifications_identification_document_type_id_foreign` (`identification_document_type_id`),
  ADD KEY `customer_identifications_document_number_index` (`document_number`),
  ADD KEY `customer_identifications_expiry_date_index` (`expiry_date`);

--
-- Indexes for table `customer_individuals`
--
ALTER TABLE `customer_individuals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_individuals_customer_id_unique` (`customer_id`),
  ADD KEY `customer_individuals_parentage_relationship_id_foreign` (`parentage_relationship_id`),
  ADD KEY `customer_individuals_gender_id_foreign` (`gender_id`),
  ADD KEY `customer_individuals_marital_status_id_foreign` (`marital_status_id`),
  ADD KEY `customer_individuals_education_level_id_foreign` (`education_level_id`),
  ADD KEY `customer_individuals_birth_country_id_foreign` (`birth_country_id`),
  ADD KEY `customer_individuals_residence_country_id_foreign` (`residence_country_id`),
  ADD KEY `customer_individuals_profession_id_foreign` (`profession_id`),
  ADD KEY `customer_individuals_guardian_relationship_id_foreign` (`guardian_relationship_id`),
  ADD KEY `customer_individuals_guardian_customer_id_foreign` (`guardian_customer_id`),
  ADD KEY `customer_individuals_full_name_index` (`full_name`),
  ADD KEY `customer_individuals_date_of_birth_index` (`date_of_birth`);

--
-- Indexes for table `customer_nationalities`
--
ALTER TABLE `customer_nationalities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cust_nationality_unique` (`customer_id`,`country_id`),
  ADD KEY `customer_nationalities_country_id_foreign` (`country_id`);

--
-- Indexes for table `customer_next_of_kin`
--
ALTER TABLE `customer_next_of_kin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_next_of_kin_relationship_id_foreign` (`relationship_id`),
  ADD KEY `customer_next_of_kin_identification_document_type_id_foreign` (`identification_document_type_id`),
  ADD KEY `customer_next_of_kin_country_id_foreign` (`country_id`),
  ADD KEY `customer_next_of_kin_customer_id_index` (`customer_id`);

--
-- Indexes for table `customer_organizations`
--
ALTER TABLE `customer_organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_organizations_customer_id_unique` (`customer_id`),
  ADD KEY `customer_organizations_business_nature_id_foreign` (`business_nature_id`),
  ADD KEY `customer_organizations_incorporation_country_id_foreign` (`incorporation_country_id`),
  ADD KEY `customer_organizations_business_name_index` (`business_name`),
  ADD KEY `customer_organizations_ntn_index` (`ntn`);

--
-- Indexes for table `customer_special_category`
--
ALTER TABLE `customer_special_category`
  ADD UNIQUE KEY `cust_special_cat_unique` (`customer_id`,`special_category_id`),
  ADD KEY `customer_special_category_special_category_id_foreign` (`special_category_id`);

--
-- Indexes for table `customer_tax_residencies`
--
ALTER TABLE `customer_tax_residencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cust_tax_residency_unique` (`customer_id`,`country_id`),
  ADD KEY `customer_tax_residencies_country_id_foreign` (`country_id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `districts_name_unique` (`name`),
  ADD KEY `districts_region_id_foreign` (`region_id`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_requirements`
--
ALTER TABLE `document_requirements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `doc_req_type_category_unique` (`document_type_id`,`customer_category_id`),
  ADD KEY `document_requirements_customer_category_id_is_mandatory_index` (`customer_category_id`,`is_mandatory`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_types_code_unique` (`code`);

--
-- Indexes for table `economic_sectors`
--
ALTER TABLE `economic_sectors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `economic_sectors_code_unique` (`code`);

--
-- Indexes for table `education_levels`
--
ALTER TABLE `education_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `education_levels_code_unique` (`code`);

--
-- Indexes for table `employee_resources`
--
ALTER TABLE `employee_resources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_resources_resource_number_unique` (`resource_number`),
  ADD UNIQUE KEY `employee_resources_resource_no_unique` (`resource_no`),
  ADD UNIQUE KEY `employee_resources_reference_no_unique` (`reference_no`),
  ADD KEY `employee_resources_division_id_foreign` (`division_id`),
  ADD KEY `employee_resources_category_id_foreign` (`category_id`),
  ADD KEY `employee_resources_created_by_foreign` (`created_by`),
  ADD KEY `employee_resources_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `file_categories`
--
ALTER TABLE `file_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_categories_category_code_unique` (`category_code`),
  ADD KEY `file_categories_created_by_foreign` (`created_by`),
  ADD KEY `file_categories_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `file_management_systems`
--
ALTER TABLE `file_management_systems`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_management_systems_digital_id_unique` (`digital_id`),
  ADD KEY `file_management_systems_file_category_id_foreign` (`file_category_id`),
  ADD KEY `file_management_systems_fileable_type_fileable_id_index` (`fileable_type`,`fileable_id`),
  ADD KEY `file_management_systems_created_by_foreign` (`created_by`),
  ADD KEY `file_management_systems_updated_by_foreign` (`updated_by`),
  ADD KEY `file_management_systems_current_custodian_id_foreign` (`current_custodian_id`),
  ADD KEY `idx_fileable_date` (`fileable_type`,`fileable_id`,`document_date`),
  ADD KEY `file_management_systems_file_no_index` (`file_no`),
  ADD KEY `file_management_systems_box_id_foreign` (`box_id`),
  ADD KEY `file_management_systems_submitted_by_foreign` (`submitted_by`),
  ADD KEY `file_management_systems_approved_by_foreign` (`approved_by`),
  ADD KEY `file_management_systems_rejected_by_foreign` (`rejected_by`),
  ADD KEY `file_management_systems_unlocked_by_foreign` (`unlocked_by`),
  ADD KEY `file_management_systems_status_index` (`status`);

--
-- Indexes for table `file_management_transfers`
--
ALTER TABLE `file_management_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_management_transfers_recipient_id_foreign` (`recipient_id`),
  ADD KEY `file_management_transfers_requested_by_foreign` (`requested_by`),
  ADD KEY `file_management_transfers_decided_by_foreign` (`decided_by`),
  ADD KEY `file_management_transfers_file_management_system_id_status_index` (`file_management_system_id`,`status`);

--
-- Indexes for table `genders`
--
ALTER TABLE `genders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `genders_code_unique` (`code`);

--
-- Indexes for table `head_offices`
--
ALTER TABLE `head_offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `head_offices_name_unique` (`name`);

--
-- Indexes for table `identification_document_types`
--
ALTER TABLE `identification_document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identification_document_types_code_unique` (`code`);

--
-- Indexes for table `id_prefixes`
--
ALTER TABLE `id_prefixes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_prefixes_name_unique` (`name`),
  ADD UNIQUE KEY `id_prefixes_prefix_unique` (`prefix`);

--
-- Indexes for table `income_sources`
--
ALTER TABLE `income_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `income_sources_code_unique` (`code`),
  ADD KEY `income_sources_applies_to_is_active_index` (`applies_to`,`is_active`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `managers_created_by_user_id_foreign` (`created_by_user_id`),
  ADD KEY `managers_division_id_foreign` (`division_id`),
  ADD KEY `managers_manager_user_id_foreign` (`manager_user_id`),
  ADD KEY `managers_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `marital_statuses`
--
ALTER TABLE `marital_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `marital_statuses_code_unique` (`code`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_uuid_unique` (`uuid`),
  ADD KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `media_order_column_index` (`order_column`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `operating_instructions`
--
ALTER TABLE `operating_instructions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `operating_instructions_code_unique` (`code`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `professions`
--
ALTER TABLE `professions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `professions_code_unique` (`code`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `regions_name_unique` (`name`);

--
-- Indexes for table `relationships`
--
ALTER TABLE `relationships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `relationships_code_unique` (`code`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `special_categories`
--
ALTER TABLE `special_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `special_categories_code_unique` (`code`);

--
-- Indexes for table `specimen_signatures`
--
ALTER TABLE `specimen_signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `specimen_signatures_verified_by_foreign` (`verified_by`),
  ADD KEY `specimen_signatures_account_holder_id_is_active_index` (`account_holder_id`,`is_active`);

--
-- Indexes for table `statement_delivery_modes`
--
ALTER TABLE `statement_delivery_modes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `statement_delivery_modes_code_unique` (`code`);

--
-- Indexes for table `statement_frequencies`
--
ALTER TABLE `statement_frequencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `statement_frequencies_code_unique` (`code`);

--
-- Indexes for table `transaction_modes`
--
ALTER TABLE `transaction_modes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_modes_code_unique` (`code`);

--
-- Indexes for table `ultimate_beneficial_owners`
--
ALTER TABLE `ultimate_beneficial_owners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ultimate_beneficial_owners_customer_due_diligence_id_foreign` (`customer_due_diligence_id`),
  ADD KEY `ultimate_beneficial_owners_relationship_id_foreign` (`relationship_id`),
  ADD KEY `ubo_identification_doc_type_foreign` (`identification_document_type_id`),
  ADD KEY `ultimate_beneficial_owners_account_id_index` (`account_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_branch_id_foreign` (`branch_id`),
  ADD KEY `users_region_id_foreign` (`region_id`),
  ADD KEY `users_division_id_foreign` (`division_id`),
  ADD KEY `users_head_office_id_foreign` (`head_office_id`);

--
-- Indexes for table `wealth_sources`
--
ALTER TABLE `wealth_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wealth_sources_code_unique` (`code`);

--
-- Indexes for table `zakat_exemption_reasons`
--
ALTER TABLE `zakat_exemption_reasons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zakat_exemption_reasons_code_unique` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=357;

--
-- AUTO_INCREMENT for table `aksic_amortizations`
--
ALTER TABLE `aksic_amortizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aksic_application_education`
--
ALTER TABLE `aksic_application_education`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aksic_application_status_logs`
--
ALTER TABLE `aksic_application_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aksic_budgets`
--
ALTER TABLE `aksic_budgets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `aksic_budget_allocations`
--
ALTER TABLE `aksic_budget_allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `aksic_budget_revisions`
--
ALTER TABLE `aksic_budget_revisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `aksic_business_categories`
--
ALTER TABLE `aksic_business_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `aksic_claim_items`
--
ALTER TABLE `aksic_claim_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aksic_rules`
--
ALTER TABLE `aksic_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `branch_targets`
--
ALTER TABLE `branch_targets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `complaint_assignments`
--
ALTER TABLE `complaint_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_attachments`
--
ALTER TABLE `complaint_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `complaint_comments`
--
ALTER TABLE `complaint_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_escalations`
--
ALTER TABLE `complaint_escalations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_histories`
--
ALTER TABLE `complaint_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_metrics`
--
ALTER TABLE `complaint_metrics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_status_types`
--
ALTER TABLE `complaint_status_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `complaint_templates`
--
ALTER TABLE `complaint_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_watchers`
--
ALTER TABLE `complaint_watchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_witnesses`
--
ALTER TABLE `complaint_witnesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `head_offices`
--
ALTER TABLE `head_offices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `id_prefixes`
--
ALTER TABLE `id_prefixes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_account_product_id_foreign` FOREIGN KEY (`account_product_id`) REFERENCES `account_products` (`id`),
  ADD CONSTRAINT `accounts_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `accounts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounts_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `accounts_mailing_address_id_foreign` FOREIGN KEY (`mailing_address_id`) REFERENCES `customer_addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounts_opened_by_foreign` FOREIGN KEY (`opened_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounts_operating_instruction_id_foreign` FOREIGN KEY (`operating_instruction_id`) REFERENCES `operating_instructions` (`id`),
  ADD CONSTRAINT `accounts_statement_delivery_mode_id_foreign` FOREIGN KEY (`statement_delivery_mode_id`) REFERENCES `statement_delivery_modes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounts_statement_frequency_id_foreign` FOREIGN KEY (`statement_frequency_id`) REFERENCES `statement_frequencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `account_cheque_books`
--
ALTER TABLE `account_cheque_books`
  ADD CONSTRAINT `account_cheque_books_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `account_debit_cards`
--
ALTER TABLE `account_debit_cards`
  ADD CONSTRAINT `account_debit_cards_account_holder_id_foreign` FOREIGN KEY (`account_holder_id`) REFERENCES `account_holders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_debit_cards_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_debit_cards_card_type_id_foreign` FOREIGN KEY (`card_type_id`) REFERENCES `card_types` (`id`);

--
-- Constraints for table `account_holders`
--
ALTER TABLE `account_holders`
  ADD CONSTRAINT `account_holders_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_holders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `account_holders_relationship_id_foreign` FOREIGN KEY (`relationship_id`) REFERENCES `relationships` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `account_opening_histories`
--
ALTER TABLE `account_opening_histories`
  ADD CONSTRAINT `account_opening_histories_account_opening_request_id_foreign` FOREIGN KEY (`account_opening_request_id`) REFERENCES `account_opening_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_opening_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `account_opening_paper_forms`
--
ALTER TABLE `account_opening_paper_forms`
  ADD CONSTRAINT `account_opening_paper_forms_account_opening_request_id_foreign` FOREIGN KEY (`account_opening_request_id`) REFERENCES `account_opening_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `account_opening_requests`
--
ALTER TABLE `account_opening_requests`
  ADD CONSTRAINT `account_opening_requests_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_opening_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_opening_requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `account_opening_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_opening_requests_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_opening_requests_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_opening_requests_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_opening_requests_unlocked_by_foreign` FOREIGN KEY (`unlocked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_opening_requests_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `aksics`
--
ALTER TABLE `aksics`
  ADD CONSTRAINT `aksics_aksic_rule_id_foreign` FOREIGN KEY (`aksic_rule_id`) REFERENCES `aksic_rules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `aksics_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksics_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `aksic_amortizations`
--
ALTER TABLE `aksic_amortizations`
  ADD CONSTRAINT `aksic_amortizations_aksic_id_foreign` FOREIGN KEY (`aksic_id`) REFERENCES `aksics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aksic_amortizations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_amortizations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `aksic_application_education`
--
ALTER TABLE `aksic_application_education`
  ADD CONSTRAINT `aksic_application_education_aksic_application_id_foreign` FOREIGN KEY (`aksic_application_id`) REFERENCES `aksic_applications` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `aksic_application_education_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `aksic_applications` (`applicant_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `aksic_application_status_logs`
--
ALTER TABLE `aksic_application_status_logs`
  ADD CONSTRAINT `aksic_application_status_logs_aksic_application_id_foreign` FOREIGN KEY (`aksic_application_id`) REFERENCES `aksic_applications` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `aksic_application_status_logs_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `aksic_applications` (`applicant_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `aksic_budgets`
--
ALTER TABLE `aksic_budgets`
  ADD CONSTRAINT `aksic_budgets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_budgets_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `aksic_budget_allocations`
--
ALTER TABLE `aksic_budget_allocations`
  ADD CONSTRAINT `aksic_budget_allocations_aksic_budget_id_foreign` FOREIGN KEY (`aksic_budget_id`) REFERENCES `aksic_budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aksic_budget_allocations_aksic_rule_id_foreign` FOREIGN KEY (`aksic_rule_id`) REFERENCES `aksic_rules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_budget_allocations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_budget_allocations_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `aksic_budget_allocations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `aksic_budget_revisions`
--
ALTER TABLE `aksic_budget_revisions`
  ADD CONSTRAINT `aksic_budget_revisions_aksic_budget_id_foreign` FOREIGN KEY (`aksic_budget_id`) REFERENCES `aksic_budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aksic_budget_revisions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_budget_revisions_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_budget_revisions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `aksic_claims`
--
ALTER TABLE `aksic_claims`
  ADD CONSTRAINT `aksic_claims_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_claims_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_claims_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_claims_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_claims_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `aksic_claim_items`
--
ALTER TABLE `aksic_claim_items`
  ADD CONSTRAINT `aksic_claim_items_aksic_claim_id_foreign` FOREIGN KEY (`aksic_claim_id`) REFERENCES `aksic_claims` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aksic_claim_items_aksic_id_foreign` FOREIGN KEY (`aksic_id`) REFERENCES `aksics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aksic_claim_items_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_claim_items_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_claim_items_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `aksic_rules`
--
ALTER TABLE `aksic_rules`
  ADD CONSTRAINT `aksic_rules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aksic_rules_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `aksic_rules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `boxes`
--
ALTER TABLE `boxes`
  ADD CONSTRAINT `boxes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branches_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `branch_targets`
--
ALTER TABLE `branch_targets`
  ADD CONSTRAINT `branch_targets_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `branch_targets_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `branch_targets_updated_by_user_id_foreign` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cdd_account_purpose`
--
ALTER TABLE `cdd_account_purpose`
  ADD CONSTRAINT `cdd_account_purpose_account_purpose_id_foreign` FOREIGN KEY (`account_purpose_id`) REFERENCES `account_purposes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cdd_account_purpose_customer_due_diligence_id_foreign` FOREIGN KEY (`customer_due_diligence_id`) REFERENCES `customer_due_diligences` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cdd_counter_party_type`
--
ALTER TABLE `cdd_counter_party_type`
  ADD CONSTRAINT `cdd_counter_party_type_counter_party_type_id_foreign` FOREIGN KEY (`counter_party_type_id`) REFERENCES `counter_party_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cdd_counter_party_type_customer_due_diligence_id_foreign` FOREIGN KEY (`customer_due_diligence_id`) REFERENCES `customer_due_diligences` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cdd_income_source`
--
ALTER TABLE `cdd_income_source`
  ADD CONSTRAINT `cdd_income_source_customer_due_diligence_id_foreign` FOREIGN KEY (`customer_due_diligence_id`) REFERENCES `customer_due_diligences` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cdd_income_source_income_source_id_foreign` FOREIGN KEY (`income_source_id`) REFERENCES `income_sources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cdd_transaction_mode`
--
ALTER TABLE `cdd_transaction_mode`
  ADD CONSTRAINT `cdd_transaction_mode_customer_due_diligence_id_foreign` FOREIGN KEY (`customer_due_diligence_id`) REFERENCES `customer_due_diligences` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cdd_transaction_mode_transaction_mode_id_foreign` FOREIGN KEY (`transaction_mode_id`) REFERENCES `transaction_modes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cdd_wealth_source`
--
ALTER TABLE `cdd_wealth_source`
  ADD CONSTRAINT `cdd_wealth_source_customer_due_diligence_id_foreign` FOREIGN KEY (`customer_due_diligence_id`) REFERENCES `customer_due_diligences` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cdd_wealth_source_wealth_source_id_foreign` FOREIGN KEY (`wealth_source_id`) REFERENCES `wealth_sources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_assignments`
--
ALTER TABLE `complaint_assignments`
  ADD CONSTRAINT `complaint_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaint_assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaint_assignments_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_assignments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_assignments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_attachments`
--
ALTER TABLE `complaint_attachments`
  ADD CONSTRAINT `complaint_attachments_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_attachments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_attachments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  ADD CONSTRAINT `complaint_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_categories_parent_category_id_foreign` FOREIGN KEY (`parent_category_id`) REFERENCES `complaint_categories` (`id`),
  ADD CONSTRAINT `complaint_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_comments`
--
ALTER TABLE `complaint_comments`
  ADD CONSTRAINT `complaint_comments_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_comments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_comments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_escalations`
--
ALTER TABLE `complaint_escalations`
  ADD CONSTRAINT `complaint_escalations_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_escalations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_escalations_escalated_from_foreign` FOREIGN KEY (`escalated_from`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaint_escalations_escalated_to_foreign` FOREIGN KEY (`escalated_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaint_escalations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_histories`
--
ALTER TABLE `complaint_histories`
  ADD CONSTRAINT `complaint_histories_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_histories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_histories_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaint_histories_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `complaint_status_types` (`id`),
  ADD CONSTRAINT `complaint_histories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_metrics`
--
ALTER TABLE `complaint_metrics`
  ADD CONSTRAINT `complaint_metrics_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_metrics_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_metrics_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_templates`
--
ALTER TABLE `complaint_templates`
  ADD CONSTRAINT `complaint_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_watchers`
--
ALTER TABLE `complaint_watchers`
  ADD CONSTRAINT `complaint_watchers_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_watchers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_watchers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_watchers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `complaint_witnesses`
--
ALTER TABLE `complaint_witnesses`
  ADD CONSTRAINT `complaint_witnesses_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_witnesses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaint_witnesses_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_customer_category_id_foreign` FOREIGN KEY (`customer_category_id`) REFERENCES `customer_categories` (`id`),
  ADD CONSTRAINT `customers_economic_sector_id_foreign` FOREIGN KEY (`economic_sector_id`) REFERENCES `economic_sectors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_zakat_exemption_reason_id_foreign` FOREIGN KEY (`zakat_exemption_reason_id`) REFERENCES `zakat_exemption_reasons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_contacts`
--
ALTER TABLE `customer_contacts`
  ADD CONSTRAINT `customer_contacts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_controlling_persons`
--
ALTER TABLE `customer_controlling_persons`
  ADD CONSTRAINT `customer_controlling_persons_controlling_person_type_id_foreign` FOREIGN KEY (`controlling_person_type_id`) REFERENCES `controlling_person_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_controlling_persons_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_controlling_persons_person_customer_id_foreign` FOREIGN KEY (`person_customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_documents`
--
ALTER TABLE `customer_documents`
  ADD CONSTRAINT `customer_documents_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_documents_account_opening_request_id_foreign` FOREIGN KEY (`account_opening_request_id`) REFERENCES `account_opening_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_documents_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_documents_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  ADD CONSTRAINT `customer_documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_due_diligences`
--
ALTER TABLE `customer_due_diligences`
  ADD CONSTRAINT `customer_due_diligences_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_due_diligences_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_due_diligences_fund_provider_relationship_id_foreign` FOREIGN KEY (`fund_provider_relationship_id`) REFERENCES `relationships` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_due_diligences_home_remittance_country_id_foreign` FOREIGN KEY (`home_remittance_country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_fatca_details`
--
ALTER TABLE `customer_fatca_details`
  ADD CONSTRAINT `customer_fatca_details_crs_entity_classification_id_foreign` FOREIGN KEY (`crs_entity_classification_id`) REFERENCES `crs_entity_classifications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_fatca_details_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_fatca_details_incorporation_country_id_foreign` FOREIGN KEY (`incorporation_country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_identifications`
--
ALTER TABLE `customer_identifications`
  ADD CONSTRAINT `customer_identifications_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_identifications_identification_document_type_id_foreign` FOREIGN KEY (`identification_document_type_id`) REFERENCES `identification_document_types` (`id`);

--
-- Constraints for table `customer_individuals`
--
ALTER TABLE `customer_individuals`
  ADD CONSTRAINT `customer_individuals_birth_country_id_foreign` FOREIGN KEY (`birth_country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_individuals_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_individuals_education_level_id_foreign` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_individuals_gender_id_foreign` FOREIGN KEY (`gender_id`) REFERENCES `genders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_individuals_guardian_customer_id_foreign` FOREIGN KEY (`guardian_customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_individuals_guardian_relationship_id_foreign` FOREIGN KEY (`guardian_relationship_id`) REFERENCES `relationships` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_individuals_marital_status_id_foreign` FOREIGN KEY (`marital_status_id`) REFERENCES `marital_statuses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_individuals_parentage_relationship_id_foreign` FOREIGN KEY (`parentage_relationship_id`) REFERENCES `relationships` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_individuals_profession_id_foreign` FOREIGN KEY (`profession_id`) REFERENCES `professions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_individuals_residence_country_id_foreign` FOREIGN KEY (`residence_country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_nationalities`
--
ALTER TABLE `customer_nationalities`
  ADD CONSTRAINT `customer_nationalities_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `customer_nationalities_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_next_of_kin`
--
ALTER TABLE `customer_next_of_kin`
  ADD CONSTRAINT `customer_next_of_kin_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_next_of_kin_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_next_of_kin_identification_document_type_id_foreign` FOREIGN KEY (`identification_document_type_id`) REFERENCES `identification_document_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_next_of_kin_relationship_id_foreign` FOREIGN KEY (`relationship_id`) REFERENCES `relationships` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_organizations`
--
ALTER TABLE `customer_organizations`
  ADD CONSTRAINT `customer_organizations_business_nature_id_foreign` FOREIGN KEY (`business_nature_id`) REFERENCES `business_natures` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_organizations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_organizations_incorporation_country_id_foreign` FOREIGN KEY (`incorporation_country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_special_category`
--
ALTER TABLE `customer_special_category`
  ADD CONSTRAINT `customer_special_category_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_special_category_special_category_id_foreign` FOREIGN KEY (`special_category_id`) REFERENCES `special_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_tax_residencies`
--
ALTER TABLE `customer_tax_residencies`
  ADD CONSTRAINT `customer_tax_residencies_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `customer_tax_residencies_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `districts`
--
ALTER TABLE `districts`
  ADD CONSTRAINT `districts_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `document_requirements`
--
ALTER TABLE `document_requirements`
  ADD CONSTRAINT `document_requirements_customer_category_id_foreign` FOREIGN KEY (`customer_category_id`) REFERENCES `customer_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_requirements_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_resources`
--
ALTER TABLE `employee_resources`
  ADD CONSTRAINT `employee_resources_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_resources_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_resources_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_resources_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `file_categories`
--
ALTER TABLE `file_categories`
  ADD CONSTRAINT `file_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `file_management_systems`
--
ALTER TABLE `file_management_systems`
  ADD CONSTRAINT `file_management_systems_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_management_systems_box_id_foreign` FOREIGN KEY (`box_id`) REFERENCES `boxes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_management_systems_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_management_systems_current_custodian_id_foreign` FOREIGN KEY (`current_custodian_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_management_systems_file_category_id_foreign` FOREIGN KEY (`file_category_id`) REFERENCES `file_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `file_management_systems_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_management_systems_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_management_systems_unlocked_by_foreign` FOREIGN KEY (`unlocked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_management_systems_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `file_management_transfers`
--
ALTER TABLE `file_management_transfers`
  ADD CONSTRAINT `file_management_transfers_decided_by_foreign` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_management_transfers_file_management_system_id_foreign` FOREIGN KEY (`file_management_system_id`) REFERENCES `file_management_systems` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_management_transfers_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `file_management_transfers_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `managers`
--
ALTER TABLE `managers`
  ADD CONSTRAINT `managers_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `managers_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `managers_manager_user_id_foreign` FOREIGN KEY (`manager_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `managers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `specimen_signatures`
--
ALTER TABLE `specimen_signatures`
  ADD CONSTRAINT `specimen_signatures_account_holder_id_foreign` FOREIGN KEY (`account_holder_id`) REFERENCES `account_holders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `specimen_signatures_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ultimate_beneficial_owners`
--
ALTER TABLE `ultimate_beneficial_owners`
  ADD CONSTRAINT `ubo_identification_doc_type_foreign` FOREIGN KEY (`identification_document_type_id`) REFERENCES `identification_document_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ultimate_beneficial_owners_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ultimate_beneficial_owners_customer_due_diligence_id_foreign` FOREIGN KEY (`customer_due_diligence_id`) REFERENCES `customer_due_diligences` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ultimate_beneficial_owners_relationship_id_foreign` FOREIGN KEY (`relationship_id`) REFERENCES `relationships` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `users_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_head_office_id_foreign` FOREIGN KEY (`head_office_id`) REFERENCES `head_offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
