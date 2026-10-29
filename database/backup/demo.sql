-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 29, 2026 at 06:03 AM
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
('01a18188-bd30-7173-be8e-049a5ecab013', '01a18188-bd0f-7096-be8a-4a690d9f4c4b', 'created', NULL, 88, NULL, 'Old account opening form started for 0010 Bagh Branch.', '2026-10-28 08:03:04.624258');

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
('01a18188-bd28-70df-b5d4-7671b6c55b3e', '01a18188-bd0f-7096-be8a-4a690d9f4c4b', NULL, NULL, '[]', '2026-10-28 08:03:04', '2026-10-28 08:03:04');

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
('01a18188-bd0f-7096-be8a-4a690d9f4c4b', '0010-20261028-0001', 'individual_joint_sole', 'old', 'BAJK-AOF-Old-Paper-Form', 10, NULL, NULL, '2026-10-28', 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 88, 88, '2026-10-28 08:03:04', '2026-10-28 08:03:43', '2026-10-28 08:03:43');

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
('01a18188-112c-733b-b3d7-c9d8301b34fe', 'BCA', 'BAJK Current Account', 'بی اے جے کے کرنٹ اکاؤنٹ', 'current', 'local', 'both', 0, 1, '\"[10,25,50]\"', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-112c-733b-b3d7-c9d83026f11f', 'PLS', 'PLS Saving Account (PLS)', 'پی ایل ایس سیونگ اکاؤنٹ', 'saving', 'local', 'both', 1, 1, '\"[10,25,50]\"', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-112d-71e8-a0b2-7e2ae03aac6d', 'SDA', 'Special Deposit Account (SDA)', 'سپیشل ڈپازٹ اکاؤنٹ', 'saving', 'local', 'both', 1, 1, '\"[10,25,50]\"', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-112e-71e9-a2e0-4afe7e3802eb', 'BMBA', 'Bemisal Mahana Bachat Account (BMBA)', 'بے مثال ماہانہ بچت اکاؤنٹ', 'saving', 'local', 'both', 1, 1, '\"[10,25,50]\"', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-112e-71e9-a2e0-4afe7efb1041', 'PPRSA', 'Premium Plus Remittance Saving Account (PPRSA)', 'پریمیم پلس ریمیٹنس سیونگ اکاؤنٹ', 'saving', 'local', 'individual', 1, 1, '\"[10,25,50]\"', 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-112f-733f-baa8-b3567db60d71', 'BCA_FCY', 'BAJK Current Account - FCY', 'بی اے جے کے کرنٹ اکاؤنٹ ایف سی وائی', 'current', 'foreign', 'both', 0, 0, '\"[25,50,100]\"', 0, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1130-70b9-a4d9-ea5ccd0071aa', 'BSA_FCY', 'BAJK Saving Account - FCY', 'بی اے جے کے سیونگ اکاؤنٹ ایف سی وائی', 'saving', 'foreign', 'both', 0, 0, '\"[25,50,100]\"', 0, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1130-70b9-a4d9-ea5ccdb02bcc', 'BMBA_FCY', 'Bemisal Mahana Bachat Account (BMBA) - FCY', 'بے مثال ماہانہ بچت اکاؤنٹ ایف سی وائی', 'saving', 'foreign', 'both', 0, 0, '\"[25,50,100]\"', 0, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1131-72d7-90ae-8893f08d8b7a', 'TERM_FCY', 'BAJK Term Account - FCY', 'بی اے جے کے ٹرم اکاؤنٹ ایف سی وائی', 'term', 'foreign', 'both', 0, 0, '\"[25,50,100]\"', 0, 9, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-1150-7208-9552-0bb883ac9053', 'SAVING', 'Saving', 'بچت', 'both', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1150-7208-9552-0bb884a7c267', 'BUSINESS', 'Business', 'کاروبار', 'both', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1150-7208-9552-0bb884b2c3bb', 'TRANSACTIONAL', 'Transactional', 'لین دین', 'individual', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1151-71a8-9df7-e165dbf58409', 'CREDIT_FACILITY', 'Credit Facility', 'قرض کی سہولت', 'entity', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1151-71a8-9df7-e165dc45d80e', 'PURPOSE_OTHERS', 'Others', 'دیگر', 'both', 1, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
(354, 'default', 'Account opening request has been created', 'App\\Models\\AccountOpeningRequest', 'created', '01a18188-bd0f-7096-be8a-4a690d9f4c4b', 'App\\Models\\User', '88', '{\"attributes\":{\"id\":\"01a18188-bd0f-7096-be8a-4a690d9f4c4b\",\"request_number\":\"0010-20261028-0001\",\"aof_form_type\":\"individual_joint_sole\",\"form_edition\":\"old\",\"aof_version\":\"BAJK-AOF-Old-Paper-Form\",\"branch_id\":10,\"customer_id\":null,\"account_id\":null,\"request_date\":\"2026-10-27T19:00:00.000000Z\",\"terms_and_conditions_accepted\":false,\"indemnity_undertaking_accepted\":false,\"aof_copy_received_by_customer\":false,\"shariah_compliant_investment_authorized\":false,\"vernacular_form_attached\":false,\"qa22_form_attached\":false,\"sales_staff_name_1\":null,\"sales_staff_employee_no_1\":null,\"sales_staff_name_2\":null,\"sales_staff_employee_no_2\":null,\"authorized_by_name\":null,\"authorized_by_employee_no\":null,\"authorized_on\":null,\"checker_name\":null,\"checker_employee_no\":null,\"checked_on\":null,\"status\":\"draft\",\"submitted_by\":null,\"submitted_at\":null,\"approved_by\":null,\"approved_at\":null,\"rejected_by\":null,\"rejected_at\":null,\"locked_at\":null,\"unlocked_by\":null,\"unlocked_at\":null,\"unlock_reason\":null,\"rejection_reason\":null,\"created_by\":88,\"updated_by\":88,\"created_at\":\"2026-10-28T08:03:04.000000Z\",\"updated_at\":\"2026-10-28T08:03:04.000000Z\",\"deleted_at\":null}}', NULL, '2026-10-28 08:03:04', '2026-10-28 08:03:04'),
(355, 'default', 'Account opening request has been deleted', 'App\\Models\\AccountOpeningRequest', 'deleted', '01a18188-bd0f-7096-be8a-4a690d9f4c4b', 'App\\Models\\User', '88', '{\"old\":{\"id\":\"01a18188-bd0f-7096-be8a-4a690d9f4c4b\",\"request_number\":\"0010-20261028-0001\",\"aof_form_type\":\"individual_joint_sole\",\"form_edition\":\"old\",\"aof_version\":\"BAJK-AOF-Old-Paper-Form\",\"branch_id\":10,\"customer_id\":null,\"account_id\":null,\"request_date\":\"2026-10-27T19:00:00.000000Z\",\"terms_and_conditions_accepted\":false,\"indemnity_undertaking_accepted\":false,\"aof_copy_received_by_customer\":false,\"shariah_compliant_investment_authorized\":false,\"vernacular_form_attached\":false,\"qa22_form_attached\":false,\"sales_staff_name_1\":null,\"sales_staff_employee_no_1\":null,\"sales_staff_name_2\":null,\"sales_staff_employee_no_2\":null,\"authorized_by_name\":null,\"authorized_by_employee_no\":null,\"authorized_on\":null,\"checker_name\":null,\"checker_employee_no\":null,\"checked_on\":null,\"status\":\"draft\",\"submitted_by\":null,\"submitted_at\":null,\"approved_by\":null,\"approved_at\":null,\"rejected_by\":null,\"rejected_at\":null,\"locked_at\":null,\"unlocked_by\":null,\"unlocked_at\":null,\"unlock_reason\":null,\"rejection_reason\":null,\"created_by\":88,\"updated_by\":88,\"created_at\":\"2026-10-28T08:03:04.000000Z\",\"updated_at\":\"2026-10-28T08:03:43.000000Z\",\"deleted_at\":\"2026-10-28T08:03:43.000000Z\"}}', NULL, '2026-10-28 08:03:43', '2026-10-28 08:03:43');

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

--
-- Dumping data for table `aksic_amortizations`
--

INSERT INTO `aksic_amortizations` (`id`, `aksic_id`, `installment_no`, `period_start_date`, `due_date`, `days`, `principal_amount_os`, `installment_per_month`, `product`, `interest_rate_per_month`, `total_interest`, `total_rate`, `total_installment`, `principal_balance_after_installment`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
(1, '019e5de4-4a45-7359-af5e-a90a51b55a03', 1, '2026-05-25', '2026-05-31', 7, 500000.000000, 8333.333333, 70200.000000, 192.328767, 1346.301369, 14.040000, 9679.634702, 491666.666667, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(2, '019e5de4-4a45-7359-af5e-a90a51b55a03', 2, '2026-06-01', '2026-06-30', 30, 491666.666667, 8333.333333, 69030.000000, 189.123287, 5673.698610, 14.040000, 14007.031943, 483333.333334, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(3, '019e5de4-4a45-7359-af5e-a90a51b55a03', 3, '2026-07-01', '2026-07-31', 31, 483333.333334, 8333.333333, 67860.000000, 185.917808, 5763.452048, 14.040000, 14096.785381, 475000.000001, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(4, '019e5de4-4a45-7359-af5e-a90a51b55a03', 4, '2026-08-01', '2026-08-31', 31, 475000.000001, 8333.333333, 66690.000000, 182.712328, 5664.082168, 14.040000, 13997.415501, 466666.666668, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(5, '019e5de4-4a45-7359-af5e-a90a51b55a03', 5, '2026-09-01', '2026-09-30', 30, 466666.666668, 8333.333333, 65520.000000, 179.506849, 5385.205470, 14.040000, 13718.538803, 458333.333335, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(6, '019e5de4-4a45-7359-af5e-a90a51b55a03', 6, '2026-10-01', '2026-10-31', 31, 458333.333335, 8333.333333, 64350.000000, 176.301369, 5465.342439, 14.040000, 13798.675772, 450000.000002, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(7, '019e5de4-4a45-7359-af5e-a90a51b55a03', 7, '2026-11-01', '2026-11-30', 30, 450000.000002, 8333.333333, 63180.000000, 173.095890, 5192.876700, 14.040000, 13526.210033, 441666.666669, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(8, '019e5de4-4a45-7359-af5e-a90a51b55a03', 8, '2026-12-01', '2026-12-31', 31, 441666.666669, 8333.333333, 62010.000000, 169.890410, 5266.602710, 14.040000, 13599.936043, 433333.333336, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(9, '019e5de4-4a45-7359-af5e-a90a51b55a03', 9, '2027-01-01', '2027-01-31', 31, 433333.333336, 8333.333333, 60840.000000, 166.684931, 5167.232861, 14.040000, 13500.566194, 425000.000003, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(10, '019e5de4-4a45-7359-af5e-a90a51b55a03', 10, '2027-02-01', '2027-02-28', 28, 425000.000003, 8333.333333, 59670.000000, 163.479452, 4577.424656, 14.040000, 12910.757989, 416666.666670, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(11, '019e5de4-4a45-7359-af5e-a90a51b55a03', 11, '2027-03-01', '2027-03-31', 31, 416666.666670, 8333.333333, 58500.000000, 160.273972, 4968.493132, 14.040000, 13301.826465, 408333.333337, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(12, '019e5de4-4a45-7359-af5e-a90a51b55a03', 12, '2027-04-01', '2027-04-30', 30, 408333.333337, 8333.333333, 57330.000000, 157.068493, 4712.054790, 14.040000, 13045.388123, 400000.000004, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(13, '019e5de4-4a45-7359-af5e-a90a51b55a03', 13, '2027-05-01', '2027-05-31', 31, 400000.000004, 8333.333333, 56160.000000, 153.863013, 4769.753403, 14.040000, 13103.086736, 391666.666671, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(14, '019e5de4-4a45-7359-af5e-a90a51b55a03', 14, '2027-06-01', '2027-06-30', 30, 391666.666671, 8333.333333, 54990.000000, 150.657534, 4519.726020, 14.040000, 12853.059353, 383333.333338, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(15, '019e5de4-4a45-7359-af5e-a90a51b55a03', 15, '2027-07-01', '2027-07-31', 31, 383333.333338, 8333.333333, 53820.000000, 147.452054, 4571.013674, 14.040000, 12904.347007, 375000.000005, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(16, '019e5de4-4a45-7359-af5e-a90a51b55a03', 16, '2027-08-01', '2027-08-31', 31, 375000.000005, 8333.333333, 52650.000000, 144.246575, 4471.643825, 14.040000, 12804.977158, 366666.666672, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(17, '019e5de4-4a45-7359-af5e-a90a51b55a03', 17, '2027-09-01', '2027-09-30', 30, 366666.666672, 8333.333333, 51480.000000, 141.041095, 4231.232850, 14.040000, 12564.566183, 358333.333339, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(18, '019e5de4-4a45-7359-af5e-a90a51b55a03', 18, '2027-10-01', '2027-10-31', 31, 358333.333339, 8333.333333, 50310.000000, 137.835616, 4272.904096, 14.040000, 12606.237429, 350000.000006, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(19, '019e5de4-4a45-7359-af5e-a90a51b55a03', 19, '2027-11-01', '2027-11-30', 30, 350000.000006, 8333.333333, 49140.000000, 134.630136, 4038.904080, 14.040000, 12372.237413, 341666.666673, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(20, '019e5de4-4a45-7359-af5e-a90a51b55a03', 20, '2027-12-01', '2027-12-31', 31, 341666.666673, 8333.333333, 47970.000000, 131.424657, 4074.164367, 14.040000, 12407.497700, 333333.333340, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(21, '019e5de4-4a45-7359-af5e-a90a51b55a03', 21, '2028-01-01', '2028-01-31', 31, 333333.333340, 8333.333333, 46800.000000, 127.868852, 3963.934412, 14.040000, 12297.267745, 325000.000007, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(22, '019e5de4-4a45-7359-af5e-a90a51b55a03', 22, '2028-02-01', '2028-02-29', 29, 325000.000007, 8333.333333, 45630.000000, 124.672131, 3615.491799, 14.040000, 11948.825132, 316666.666674, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(23, '019e5de4-4a45-7359-af5e-a90a51b55a03', 23, '2028-03-01', '2028-03-31', 31, 316666.666674, 8333.333333, 44460.000001, 121.475409, 3765.737679, 14.040000, 12099.071012, 308333.333341, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(24, '019e5de4-4a45-7359-af5e-a90a51b55a03', 24, '2028-04-01', '2028-04-30', 30, 308333.333341, 8333.333333, 43290.000001, 118.278688, 3548.360640, 14.040000, 11881.693973, 300000.000008, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(25, '019e5de4-4a45-7359-af5e-a90a51b55a03', 25, '2028-05-01', '2028-05-31', 31, 300000.000008, 8333.333333, 42120.000001, 115.081967, 3567.540977, 14.040000, 11900.874310, 291666.666675, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(26, '019e5de4-4a45-7359-af5e-a90a51b55a03', 26, '2028-06-01', '2028-06-30', 30, 291666.666675, 8333.333333, 40950.000001, 111.885245, 3356.557350, 14.040000, 11689.890683, 283333.333342, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(27, '019e5de4-4a45-7359-af5e-a90a51b55a03', 27, '2028-07-01', '2028-07-31', 31, 283333.333342, 8333.333333, 39780.000001, 108.688524, 3369.344244, 14.040000, 11702.677577, 275000.000009, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(28, '019e5de4-4a45-7359-af5e-a90a51b55a03', 28, '2028-08-01', '2028-08-31', 31, 275000.000009, 8333.333333, 38610.000001, 105.491803, 3270.245893, 14.040000, 11603.579226, 266666.666676, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(29, '019e5de4-4a45-7359-af5e-a90a51b55a03', 29, '2028-09-01', '2028-09-30', 30, 266666.666676, 8333.333333, 37440.000001, 102.295081, 3068.852430, 14.040000, 11402.185763, 258333.333343, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(30, '019e5de4-4a45-7359-af5e-a90a51b55a03', 30, '2028-10-01', '2028-10-31', 31, 258333.333343, 8333.333333, 36270.000001, 99.098360, 3072.049160, 14.040000, 11405.382493, 250000.000010, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(31, '019e5de4-4a45-7359-af5e-a90a51b55a03', 31, '2028-11-01', '2028-11-30', 30, 250000.000010, 8333.333333, 35100.000001, 95.901639, 2877.049170, 14.040000, 11210.382503, 241666.666677, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(32, '019e5de4-4a45-7359-af5e-a90a51b55a03', 32, '2028-12-01', '2028-12-31', 31, 241666.666677, 8333.333333, 33930.000001, 92.704918, 2873.852458, 14.040000, 11207.185791, 233333.333344, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(33, '019e5de4-4a45-7359-af5e-a90a51b55a03', 33, '2029-01-01', '2029-01-31', 31, 233333.333344, 8333.333333, 32760.000001, 89.753424, 2782.356144, 14.040000, 11115.689477, 225000.000011, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(34, '019e5de4-4a45-7359-af5e-a90a51b55a03', 34, '2029-02-01', '2029-02-28', 28, 225000.000011, 8333.333333, 31590.000001, 86.547945, 2423.342460, 14.040000, 10756.675793, 216666.666678, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(35, '019e5de4-4a45-7359-af5e-a90a51b55a03', 35, '2029-03-01', '2029-03-31', 31, 216666.666678, 8333.333333, 30420.000001, 83.342465, 2583.616415, 14.040000, 10916.949748, 208333.333345, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(36, '019e5de4-4a45-7359-af5e-a90a51b55a03', 36, '2029-04-01', '2029-04-30', 30, 208333.333345, 8333.333333, 29250.000001, 80.136986, 2404.109580, 14.040000, 10737.442913, 200000.000012, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(37, '019e5de4-4a45-7359-af5e-a90a51b55a03', 37, '2029-05-01', '2029-05-31', 31, 200000.000012, 8333.333333, 28080.000001, 76.931506, 2384.876686, 14.040000, 10718.210019, 191666.666679, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(38, '019e5de4-4a45-7359-af5e-a90a51b55a03', 38, '2029-06-01', '2029-06-30', 30, 191666.666679, 8333.333333, 26910.000001, 73.726027, 2211.780810, 14.040000, 10545.114143, 183333.333346, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(39, '019e5de4-4a45-7359-af5e-a90a51b55a03', 39, '2029-07-01', '2029-07-31', 31, 183333.333346, 8333.333333, 25740.000001, 70.520547, 2186.136957, 14.040000, 10519.470290, 175000.000013, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(40, '019e5de4-4a45-7359-af5e-a90a51b55a03', 40, '2029-08-01', '2029-08-31', 31, 175000.000013, 8333.333333, 24570.000001, 67.315068, 2086.767108, 14.040000, 10420.100441, 166666.666680, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(41, '019e5de4-4a45-7359-af5e-a90a51b55a03', 41, '2029-09-01', '2029-09-30', 30, 166666.666680, 8333.333333, 23400.000001, 64.109589, 1923.287670, 14.040000, 10256.621003, 158333.333347, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(42, '019e5de4-4a45-7359-af5e-a90a51b55a03', 42, '2029-10-01', '2029-10-31', 31, 158333.333347, 8333.333333, 22230.000001, 60.904109, 1888.027379, 14.040000, 10221.360712, 150000.000014, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(43, '019e5de4-4a45-7359-af5e-a90a51b55a03', 43, '2029-11-01', '2029-11-30', 30, 150000.000014, 8333.333333, 21060.000001, 57.698630, 1730.958900, 14.040000, 10064.292233, 141666.666681, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(44, '019e5de4-4a45-7359-af5e-a90a51b55a03', 44, '2029-12-01', '2029-12-31', 31, 141666.666681, 8333.333333, 19890.000002, 54.493150, 1689.287650, 14.040000, 10022.620983, 133333.333348, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(45, '019e5de4-4a45-7359-af5e-a90a51b55a03', 45, '2030-01-01', '2030-01-31', 31, 133333.333348, 8333.333333, 18720.000002, 51.287671, 1589.917801, 14.040000, 9923.251134, 125000.000015, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(46, '019e5de4-4a45-7359-af5e-a90a51b55a03', 46, '2030-02-01', '2030-02-28', 28, 125000.000015, 8333.333333, 17550.000002, 48.082191, 1346.301348, 14.040000, 9679.634681, 116666.666682, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(47, '019e5de4-4a45-7359-af5e-a90a51b55a03', 47, '2030-03-01', '2030-03-31', 31, 116666.666682, 8333.333333, 16380.000002, 44.876712, 1391.178072, 14.040000, 9724.511405, 108333.333349, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(48, '019e5de4-4a45-7359-af5e-a90a51b55a03', 48, '2030-04-01', '2030-04-30', 30, 108333.333349, 8333.333333, 15210.000002, 41.671232, 1250.136960, 14.040000, 9583.470293, 100000.000016, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(49, '019e5de4-4a45-7359-af5e-a90a51b55a03', 49, '2030-05-01', '2030-05-31', 31, 100000.000016, 8333.333333, 14040.000002, 38.465753, 1192.438343, 14.040000, 9525.771676, 91666.666683, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(50, '019e5de4-4a45-7359-af5e-a90a51b55a03', 50, '2030-06-01', '2030-06-30', 30, 91666.666683, 8333.333333, 12870.000002, 35.260273, 1057.808190, 14.040000, 9391.141523, 83333.333350, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(51, '019e5de4-4a45-7359-af5e-a90a51b55a03', 51, '2030-07-01', '2030-07-31', 31, 83333.333350, 8333.333333, 11700.000002, 32.054794, 993.698614, 14.040000, 9327.031947, 75000.000017, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(52, '019e5de4-4a45-7359-af5e-a90a51b55a03', 52, '2030-08-01', '2030-08-31', 31, 75000.000017, 8333.333333, 10530.000002, 28.849315, 894.328765, 14.040000, 9227.662098, 66666.666684, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(53, '019e5de4-4a45-7359-af5e-a90a51b55a03', 53, '2030-09-01', '2030-09-30', 30, 66666.666684, 8333.333333, 9360.000002, 25.643835, 769.315050, 14.040000, 9102.648383, 58333.333351, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(54, '019e5de4-4a45-7359-af5e-a90a51b55a03', 54, '2030-10-01', '2030-10-31', 31, 58333.333351, 8333.333333, 8190.000002, 22.438356, 695.589036, 14.040000, 9028.922369, 50000.000018, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(55, '019e5de4-4a45-7359-af5e-a90a51b55a03', 55, '2030-11-01', '2030-11-30', 30, 50000.000018, 8333.333333, 7020.000002, 19.232876, 576.986280, 14.040000, 8910.319613, 41666.666685, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(56, '019e5de4-4a45-7359-af5e-a90a51b55a03', 56, '2030-12-01', '2030-12-31', 31, 41666.666685, 8333.333333, 5850.000002, 16.027397, 496.849307, 14.040000, 8830.182640, 33333.333352, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(57, '019e5de4-4a45-7359-af5e-a90a51b55a03', 57, '2031-01-01', '2031-01-31', 31, 33333.333352, 8333.333333, 4680.000002, 12.821917, 397.479427, 14.040000, 8730.812760, 25000.000019, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(58, '019e5de4-4a45-7359-af5e-a90a51b55a03', 58, '2031-02-01', '2031-02-28', 28, 25000.000019, 8333.333333, 3510.000002, 9.616438, 269.260264, 14.040000, 8602.593597, 16666.666686, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(59, '019e5de4-4a45-7359-af5e-a90a51b55a03', 59, '2031-03-01', '2031-03-31', 31, 16666.666686, 8333.333333, 2340.000002, 6.410958, 198.739698, 14.040000, 8532.073031, 8333.333353, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(60, '019e5de4-4a45-7359-af5e-a90a51b55a03', 60, '2031-04-01', '2031-04-30', 30, 8333.333353, 8333.333353, 1170.000002, 3.205479, 96.164370, 14.040000, 8429.497723, 0.000000, '2026-05-25 11:23:57', '2026-05-25 11:23:57', 88, 88, NULL),
(61, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 1, '2026-09-22', '2026-09-30', 9, 1000000.000000, 16666.666666, 140400.000000, 384.657534, 3461.917806, 14.040000, 20128.584472, 983333.333334, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(62, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 2, '2026-10-01', '2026-10-31', 31, 983333.333334, 16666.666666, 138060.000000, 378.246575, 11725.643825, 14.040000, 28392.310491, 966666.666668, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(63, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 3, '2026-11-01', '2026-11-30', 30, 966666.666668, 16666.666666, 135720.000000, 371.835616, 11155.068480, 14.040000, 27821.735146, 950000.000002, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(64, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 4, '2026-12-01', '2026-12-31', 31, 950000.000002, 16666.666666, 133380.000000, 365.424657, 11328.164367, 14.040000, 27994.831033, 933333.333336, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(65, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 5, '2027-01-01', '2027-01-31', 31, 933333.333336, 16666.666666, 131040.000000, 359.013698, 11129.424638, 14.040000, 27796.091304, 916666.666670, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(66, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 6, '2027-02-01', '2027-02-28', 28, 916666.666670, 16666.666666, 128700.000000, 352.602739, 9872.876692, 14.040000, 26539.543358, 900000.000004, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(67, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 7, '2027-03-01', '2027-03-31', 31, 900000.000004, 16666.666666, 126360.000000, 346.191780, 10731.945180, 14.040000, 27398.611846, 883333.333338, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(68, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 8, '2027-04-01', '2027-04-30', 30, 883333.333338, 16666.666666, 124020.000000, 339.780821, 10193.424630, 14.040000, 26860.091296, 866666.666672, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(69, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 9, '2027-05-01', '2027-05-31', 31, 866666.666672, 16666.666666, 121680.000000, 333.369863, 10334.465753, 14.040000, 27001.132419, 850000.000006, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(70, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 10, '2027-06-01', '2027-06-30', 30, 850000.000006, 16666.666666, 119340.000000, 326.958904, 9808.767120, 14.040000, 26475.433786, 833333.333340, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(71, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 11, '2027-07-01', '2027-07-31', 31, 833333.333340, 16666.666666, 117000.000000, 320.547945, 9936.986295, 14.040000, 26603.652961, 816666.666674, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(72, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 12, '2027-08-01', '2027-08-31', 31, 816666.666674, 16666.666666, 114660.000001, 314.136986, 9738.246566, 14.040000, 26404.913232, 800000.000008, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(73, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 13, '2027-09-01', '2027-09-30', 30, 800000.000008, 16666.666666, 112320.000001, 307.726027, 9231.780810, 14.040000, 25898.447476, 783333.333342, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(74, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 14, '2027-10-01', '2027-10-31', 31, 783333.333342, 16666.666666, 109980.000001, 301.315068, 9340.767108, 14.040000, 26007.433774, 766666.666676, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(75, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 15, '2027-11-01', '2027-11-30', 30, 766666.666676, 16666.666666, 107640.000001, 294.904109, 8847.123270, 14.040000, 25513.789936, 750000.000010, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(76, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 16, '2027-12-01', '2027-12-31', 31, 750000.000010, 16666.666666, 105300.000001, 288.493150, 8943.287650, 14.040000, 25609.954316, 733333.333344, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(77, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 17, '2028-01-01', '2028-01-31', 31, 733333.333344, 16666.666666, 102960.000001, 281.311475, 8720.655725, 14.040000, 25387.322391, 716666.666678, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(78, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 18, '2028-02-01', '2028-02-29', 29, 716666.666678, 16666.666666, 100620.000001, 274.918032, 7972.622928, 14.040000, 24639.289594, 700000.000012, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(79, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 19, '2028-03-01', '2028-03-31', 31, 700000.000012, 16666.666666, 98280.000001, 268.524590, 8324.262290, 14.040000, 24990.928956, 683333.333346, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(80, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 20, '2028-04-01', '2028-04-30', 30, 683333.333346, 16666.666666, 95940.000001, 262.131147, 7863.934410, 14.040000, 24530.601076, 666666.666680, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(81, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 21, '2028-05-01', '2028-05-31', 31, 666666.666680, 16666.666666, 93600.000001, 255.737704, 7927.868824, 14.040000, 24594.535490, 650000.000014, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(82, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 22, '2028-06-01', '2028-06-30', 30, 650000.000014, 16666.666666, 91260.000001, 249.344262, 7480.327860, 14.040000, 24146.994526, 633333.333348, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(83, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 23, '2028-07-01', '2028-07-31', 31, 633333.333348, 16666.666666, 88920.000002, 242.950819, 7531.475389, 14.040000, 24198.142055, 616666.666682, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(84, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 24, '2028-08-01', '2028-08-31', 31, 616666.666682, 16666.666666, 86580.000002, 236.557377, 7333.278687, 14.040000, 23999.945353, 600000.000016, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(85, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 25, '2028-09-01', '2028-09-30', 30, 600000.000016, 16666.666666, 84240.000002, 230.163934, 6904.918020, 14.040000, 23571.584686, 583333.333350, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(86, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 26, '2028-10-01', '2028-10-31', 31, 583333.333350, 16666.666666, 81900.000002, 223.770491, 6936.885221, 14.040000, 23603.551887, 566666.666684, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(87, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 27, '2028-11-01', '2028-11-30', 30, 566666.666684, 16666.666666, 79560.000002, 217.377049, 6521.311470, 14.040000, 23187.978136, 550000.000018, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(88, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 28, '2028-12-01', '2028-12-31', 31, 550000.000018, 16666.666666, 77220.000002, 210.983606, 6540.491786, 14.040000, 23207.158452, 533333.333352, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(89, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 29, '2029-01-01', '2029-01-31', 31, 533333.333352, 16666.666666, 74880.000002, 205.150684, 6359.671204, 14.040000, 23026.337870, 516666.666686, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(90, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 30, '2029-02-01', '2029-02-28', 28, 516666.666686, 16666.666666, 72540.000002, 198.739726, 5564.712328, 14.040000, 22231.378994, 500000.000020, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(91, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 31, '2029-03-01', '2029-03-31', 31, 500000.000020, 16666.666666, 70200.000002, 192.328767, 5962.191777, 14.040000, 22628.858443, 483333.333354, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(92, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 32, '2029-04-01', '2029-04-30', 30, 483333.333354, 16666.666666, 67860.000002, 185.917808, 5577.534240, 14.040000, 22244.200906, 466666.666688, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(93, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 33, '2029-05-01', '2029-05-31', 31, 466666.666688, 16666.666666, 65520.000002, 179.506849, 5564.712319, 14.040000, 22231.378985, 450000.000022, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(94, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 34, '2029-06-01', '2029-06-30', 30, 450000.000022, 16666.666666, 63180.000003, 173.095890, 5192.876700, 14.040000, 21859.543366, 433333.333356, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(95, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 35, '2029-07-01', '2029-07-31', 31, 433333.333356, 16666.666666, 60840.000003, 166.684931, 5167.232861, 14.040000, 21833.899527, 416666.666690, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(96, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 36, '2029-08-01', '2029-08-31', 31, 416666.666690, 16666.666666, 58500.000003, 160.273972, 4968.493132, 14.040000, 21635.159798, 400000.000024, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(97, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 37, '2029-09-01', '2029-09-30', 30, 400000.000024, 16666.666666, 56160.000003, 153.863013, 4615.890390, 14.040000, 21282.557056, 383333.333358, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(98, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 38, '2029-10-01', '2029-10-31', 31, 383333.333358, 16666.666666, 53820.000003, 147.452054, 4571.013674, 14.040000, 21237.680340, 366666.666692, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(99, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 39, '2029-11-01', '2029-11-30', 30, 366666.666692, 16666.666666, 51480.000003, 141.041095, 4231.232850, 14.040000, 20897.899516, 350000.000026, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(100, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 40, '2029-12-01', '2029-12-31', 31, 350000.000026, 16666.666666, 49140.000003, 134.630136, 4173.534216, 14.040000, 20840.200882, 333333.333360, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(101, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 41, '2030-01-01', '2030-01-31', 31, 333333.333360, 16666.666666, 46800.000003, 128.219178, 3974.794518, 14.040000, 20641.461184, 316666.666694, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(102, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 42, '2030-02-01', '2030-02-28', 28, 316666.666694, 16666.666666, 44460.000003, 121.808219, 3410.630132, 14.040000, 20077.296798, 300000.000028, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(103, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 43, '2030-03-01', '2030-03-31', 31, 300000.000028, 16666.666666, 42120.000003, 115.397260, 3577.315060, 14.040000, 20243.981726, 283333.333362, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(104, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 44, '2030-04-01', '2030-04-30', 30, 283333.333362, 16666.666666, 39780.000004, 108.986301, 3269.589030, 14.040000, 19936.255696, 266666.666696, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(105, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 45, '2030-05-01', '2030-05-31', 31, 266666.666696, 16666.666666, 37440.000004, 102.575342, 3179.835602, 14.040000, 19846.502268, 250000.000030, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(106, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 46, '2030-06-01', '2030-06-30', 30, 250000.000030, 16666.666666, 35100.000004, 96.164383, 2884.931490, 14.040000, 19551.598156, 233333.333364, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(107, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 47, '2030-07-01', '2030-07-31', 31, 233333.333364, 16666.666666, 32760.000004, 89.753424, 2782.356144, 14.040000, 19449.022810, 216666.666698, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(108, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 48, '2030-08-01', '2030-08-31', 31, 216666.666698, 16666.666666, 30420.000004, 83.342465, 2583.616415, 14.040000, 19250.283081, 200000.000032, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(109, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 49, '2030-09-01', '2030-09-30', 30, 200000.000032, 16666.666666, 28080.000004, 76.931506, 2307.945180, 14.040000, 18974.611846, 183333.333366, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(110, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 50, '2030-10-01', '2030-10-31', 31, 183333.333366, 16666.666666, 25740.000004, 70.520547, 2186.136957, 14.040000, 18852.803623, 166666.666700, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(111, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 51, '2030-11-01', '2030-11-30', 30, 166666.666700, 16666.666666, 23400.000004, 64.109589, 1923.287670, 14.040000, 18589.954336, 150000.000034, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(112, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 52, '2030-12-01', '2030-12-31', 31, 150000.000034, 16666.666666, 21060.000004, 57.698630, 1788.657530, 14.040000, 18455.324196, 133333.333368, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(113, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 53, '2031-01-01', '2031-01-31', 31, 133333.333368, 16666.666666, 18720.000004, 51.287671, 1589.917801, 14.040000, 18256.584467, 116666.666702, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(114, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 54, '2031-02-01', '2031-02-28', 28, 116666.666702, 16666.666666, 16380.000004, 44.876712, 1256.547936, 14.040000, 17923.214602, 100000.000036, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(115, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 55, '2031-03-01', '2031-03-31', 31, 100000.000036, 16666.666666, 14040.000005, 38.465753, 1192.438343, 14.040000, 17859.105009, 83333.333370, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(116, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 56, '2031-04-01', '2031-04-30', 30, 83333.333370, 16666.666666, 11700.000005, 32.054794, 961.643820, 14.040000, 17628.310486, 66666.666704, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(117, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 57, '2031-05-01', '2031-05-31', 31, 66666.666704, 16666.666666, 9360.000005, 25.643835, 794.958885, 14.040000, 17461.625551, 50000.000038, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(118, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 58, '2031-06-01', '2031-06-30', 30, 50000.000038, 16666.666666, 7020.000005, 19.232876, 576.986280, 14.040000, 17243.652946, 33333.333372, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(119, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 59, '2031-07-01', '2031-07-31', 31, 33333.333372, 16666.666666, 4680.000005, 12.821917, 397.479427, 14.040000, 17064.146093, 16666.666706, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(120, '01a0c827-a06f-71af-a2ab-d29a98235ffb', 60, '2031-08-01', '2031-08-31', 31, 16666.666706, 16666.666706, 2340.000005, 6.410958, 198.739698, 14.040000, 16865.406404, 0.000000, '2026-09-22 08:07:59', '2026-09-22 08:07:59', 88, 88, NULL),
(121, '01a0c82a-4325-709b-8d0b-3368c2a50517', 1, '2026-09-22', '2026-09-30', 9, 1000000.000000, 16666.666666, 140400.000000, 384.657534, 3461.917806, 14.040000, 20128.584472, 983333.333334, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(122, '01a0c82a-4325-709b-8d0b-3368c2a50517', 2, '2026-10-01', '2026-10-31', 31, 983333.333334, 16666.666666, 138060.000000, 378.246575, 11725.643825, 14.040000, 28392.310491, 966666.666668, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(123, '01a0c82a-4325-709b-8d0b-3368c2a50517', 3, '2026-11-01', '2026-11-30', 30, 966666.666668, 16666.666666, 135720.000000, 371.835616, 11155.068480, 14.040000, 27821.735146, 950000.000002, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(124, '01a0c82a-4325-709b-8d0b-3368c2a50517', 4, '2026-12-01', '2026-12-31', 31, 950000.000002, 16666.666666, 133380.000000, 365.424657, 11328.164367, 14.040000, 27994.831033, 933333.333336, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(125, '01a0c82a-4325-709b-8d0b-3368c2a50517', 5, '2027-01-01', '2027-01-31', 31, 933333.333336, 16666.666666, 131040.000000, 359.013698, 11129.424638, 14.040000, 27796.091304, 916666.666670, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(126, '01a0c82a-4325-709b-8d0b-3368c2a50517', 6, '2027-02-01', '2027-02-28', 28, 916666.666670, 16666.666666, 128700.000000, 352.602739, 9872.876692, 14.040000, 26539.543358, 900000.000004, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(127, '01a0c82a-4325-709b-8d0b-3368c2a50517', 7, '2027-03-01', '2027-03-31', 31, 900000.000004, 16666.666666, 126360.000000, 346.191780, 10731.945180, 14.040000, 27398.611846, 883333.333338, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(128, '01a0c82a-4325-709b-8d0b-3368c2a50517', 8, '2027-04-01', '2027-04-30', 30, 883333.333338, 16666.666666, 124020.000000, 339.780821, 10193.424630, 14.040000, 26860.091296, 866666.666672, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(129, '01a0c82a-4325-709b-8d0b-3368c2a50517', 9, '2027-05-01', '2027-05-31', 31, 866666.666672, 16666.666666, 121680.000000, 333.369863, 10334.465753, 14.040000, 27001.132419, 850000.000006, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(130, '01a0c82a-4325-709b-8d0b-3368c2a50517', 10, '2027-06-01', '2027-06-30', 30, 850000.000006, 16666.666666, 119340.000000, 326.958904, 9808.767120, 14.040000, 26475.433786, 833333.333340, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(131, '01a0c82a-4325-709b-8d0b-3368c2a50517', 11, '2027-07-01', '2027-07-31', 31, 833333.333340, 16666.666666, 117000.000000, 320.547945, 9936.986295, 14.040000, 26603.652961, 816666.666674, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(132, '01a0c82a-4325-709b-8d0b-3368c2a50517', 12, '2027-08-01', '2027-08-31', 31, 816666.666674, 16666.666666, 114660.000001, 314.136986, 9738.246566, 14.040000, 26404.913232, 800000.000008, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(133, '01a0c82a-4325-709b-8d0b-3368c2a50517', 13, '2027-09-01', '2027-09-30', 30, 800000.000008, 16666.666666, 112320.000001, 307.726027, 9231.780810, 14.040000, 25898.447476, 783333.333342, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(134, '01a0c82a-4325-709b-8d0b-3368c2a50517', 14, '2027-10-01', '2027-10-31', 31, 783333.333342, 16666.666666, 109980.000001, 301.315068, 9340.767108, 14.040000, 26007.433774, 766666.666676, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(135, '01a0c82a-4325-709b-8d0b-3368c2a50517', 15, '2027-11-01', '2027-11-30', 30, 766666.666676, 16666.666666, 107640.000001, 294.904109, 8847.123270, 14.040000, 25513.789936, 750000.000010, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(136, '01a0c82a-4325-709b-8d0b-3368c2a50517', 16, '2027-12-01', '2027-12-31', 31, 750000.000010, 16666.666666, 105300.000001, 288.493150, 8943.287650, 14.040000, 25609.954316, 733333.333344, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(137, '01a0c82a-4325-709b-8d0b-3368c2a50517', 17, '2028-01-01', '2028-01-31', 31, 733333.333344, 16666.666666, 102960.000001, 281.311475, 8720.655725, 14.040000, 25387.322391, 716666.666678, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(138, '01a0c82a-4325-709b-8d0b-3368c2a50517', 18, '2028-02-01', '2028-02-29', 29, 716666.666678, 16666.666666, 100620.000001, 274.918032, 7972.622928, 14.040000, 24639.289594, 700000.000012, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(139, '01a0c82a-4325-709b-8d0b-3368c2a50517', 19, '2028-03-01', '2028-03-31', 31, 700000.000012, 16666.666666, 98280.000001, 268.524590, 8324.262290, 14.040000, 24990.928956, 683333.333346, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(140, '01a0c82a-4325-709b-8d0b-3368c2a50517', 20, '2028-04-01', '2028-04-30', 30, 683333.333346, 16666.666666, 95940.000001, 262.131147, 7863.934410, 14.040000, 24530.601076, 666666.666680, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(141, '01a0c82a-4325-709b-8d0b-3368c2a50517', 21, '2028-05-01', '2028-05-31', 31, 666666.666680, 16666.666666, 93600.000001, 255.737704, 7927.868824, 14.040000, 24594.535490, 650000.000014, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(142, '01a0c82a-4325-709b-8d0b-3368c2a50517', 22, '2028-06-01', '2028-06-30', 30, 650000.000014, 16666.666666, 91260.000001, 249.344262, 7480.327860, 14.040000, 24146.994526, 633333.333348, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(143, '01a0c82a-4325-709b-8d0b-3368c2a50517', 23, '2028-07-01', '2028-07-31', 31, 633333.333348, 16666.666666, 88920.000002, 242.950819, 7531.475389, 14.040000, 24198.142055, 616666.666682, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(144, '01a0c82a-4325-709b-8d0b-3368c2a50517', 24, '2028-08-01', '2028-08-31', 31, 616666.666682, 16666.666666, 86580.000002, 236.557377, 7333.278687, 14.040000, 23999.945353, 600000.000016, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(145, '01a0c82a-4325-709b-8d0b-3368c2a50517', 25, '2028-09-01', '2028-09-30', 30, 600000.000016, 16666.666666, 84240.000002, 230.163934, 6904.918020, 14.040000, 23571.584686, 583333.333350, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(146, '01a0c82a-4325-709b-8d0b-3368c2a50517', 26, '2028-10-01', '2028-10-31', 31, 583333.333350, 16666.666666, 81900.000002, 223.770491, 6936.885221, 14.040000, 23603.551887, 566666.666684, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(147, '01a0c82a-4325-709b-8d0b-3368c2a50517', 27, '2028-11-01', '2028-11-30', 30, 566666.666684, 16666.666666, 79560.000002, 217.377049, 6521.311470, 14.040000, 23187.978136, 550000.000018, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(148, '01a0c82a-4325-709b-8d0b-3368c2a50517', 28, '2028-12-01', '2028-12-31', 31, 550000.000018, 16666.666666, 77220.000002, 210.983606, 6540.491786, 14.040000, 23207.158452, 533333.333352, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(149, '01a0c82a-4325-709b-8d0b-3368c2a50517', 29, '2029-01-01', '2029-01-31', 31, 533333.333352, 16666.666666, 74880.000002, 205.150684, 6359.671204, 14.040000, 23026.337870, 516666.666686, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(150, '01a0c82a-4325-709b-8d0b-3368c2a50517', 30, '2029-02-01', '2029-02-28', 28, 516666.666686, 16666.666666, 72540.000002, 198.739726, 5564.712328, 14.040000, 22231.378994, 500000.000020, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(151, '01a0c82a-4325-709b-8d0b-3368c2a50517', 31, '2029-03-01', '2029-03-31', 31, 500000.000020, 16666.666666, 70200.000002, 192.328767, 5962.191777, 14.040000, 22628.858443, 483333.333354, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(152, '01a0c82a-4325-709b-8d0b-3368c2a50517', 32, '2029-04-01', '2029-04-30', 30, 483333.333354, 16666.666666, 67860.000002, 185.917808, 5577.534240, 14.040000, 22244.200906, 466666.666688, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(153, '01a0c82a-4325-709b-8d0b-3368c2a50517', 33, '2029-05-01', '2029-05-31', 31, 466666.666688, 16666.666666, 65520.000002, 179.506849, 5564.712319, 14.040000, 22231.378985, 450000.000022, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(154, '01a0c82a-4325-709b-8d0b-3368c2a50517', 34, '2029-06-01', '2029-06-30', 30, 450000.000022, 16666.666666, 63180.000003, 173.095890, 5192.876700, 14.040000, 21859.543366, 433333.333356, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(155, '01a0c82a-4325-709b-8d0b-3368c2a50517', 35, '2029-07-01', '2029-07-31', 31, 433333.333356, 16666.666666, 60840.000003, 166.684931, 5167.232861, 14.040000, 21833.899527, 416666.666690, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(156, '01a0c82a-4325-709b-8d0b-3368c2a50517', 36, '2029-08-01', '2029-08-31', 31, 416666.666690, 16666.666666, 58500.000003, 160.273972, 4968.493132, 14.040000, 21635.159798, 400000.000024, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(157, '01a0c82a-4325-709b-8d0b-3368c2a50517', 37, '2029-09-01', '2029-09-30', 30, 400000.000024, 16666.666666, 56160.000003, 153.863013, 4615.890390, 14.040000, 21282.557056, 383333.333358, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(158, '01a0c82a-4325-709b-8d0b-3368c2a50517', 38, '2029-10-01', '2029-10-31', 31, 383333.333358, 16666.666666, 53820.000003, 147.452054, 4571.013674, 14.040000, 21237.680340, 366666.666692, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(159, '01a0c82a-4325-709b-8d0b-3368c2a50517', 39, '2029-11-01', '2029-11-30', 30, 366666.666692, 16666.666666, 51480.000003, 141.041095, 4231.232850, 14.040000, 20897.899516, 350000.000026, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(160, '01a0c82a-4325-709b-8d0b-3368c2a50517', 40, '2029-12-01', '2029-12-31', 31, 350000.000026, 16666.666666, 49140.000003, 134.630136, 4173.534216, 14.040000, 20840.200882, 333333.333360, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(161, '01a0c82a-4325-709b-8d0b-3368c2a50517', 41, '2030-01-01', '2030-01-31', 31, 333333.333360, 16666.666666, 46800.000003, 128.219178, 3974.794518, 14.040000, 20641.461184, 316666.666694, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(162, '01a0c82a-4325-709b-8d0b-3368c2a50517', 42, '2030-02-01', '2030-02-28', 28, 316666.666694, 16666.666666, 44460.000003, 121.808219, 3410.630132, 14.040000, 20077.296798, 300000.000028, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(163, '01a0c82a-4325-709b-8d0b-3368c2a50517', 43, '2030-03-01', '2030-03-31', 31, 300000.000028, 16666.666666, 42120.000003, 115.397260, 3577.315060, 14.040000, 20243.981726, 283333.333362, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(164, '01a0c82a-4325-709b-8d0b-3368c2a50517', 44, '2030-04-01', '2030-04-30', 30, 283333.333362, 16666.666666, 39780.000004, 108.986301, 3269.589030, 14.040000, 19936.255696, 266666.666696, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(165, '01a0c82a-4325-709b-8d0b-3368c2a50517', 45, '2030-05-01', '2030-05-31', 31, 266666.666696, 16666.666666, 37440.000004, 102.575342, 3179.835602, 14.040000, 19846.502268, 250000.000030, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(166, '01a0c82a-4325-709b-8d0b-3368c2a50517', 46, '2030-06-01', '2030-06-30', 30, 250000.000030, 16666.666666, 35100.000004, 96.164383, 2884.931490, 14.040000, 19551.598156, 233333.333364, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(167, '01a0c82a-4325-709b-8d0b-3368c2a50517', 47, '2030-07-01', '2030-07-31', 31, 233333.333364, 16666.666666, 32760.000004, 89.753424, 2782.356144, 14.040000, 19449.022810, 216666.666698, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(168, '01a0c82a-4325-709b-8d0b-3368c2a50517', 48, '2030-08-01', '2030-08-31', 31, 216666.666698, 16666.666666, 30420.000004, 83.342465, 2583.616415, 14.040000, 19250.283081, 200000.000032, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(169, '01a0c82a-4325-709b-8d0b-3368c2a50517', 49, '2030-09-01', '2030-09-30', 30, 200000.000032, 16666.666666, 28080.000004, 76.931506, 2307.945180, 14.040000, 18974.611846, 183333.333366, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(170, '01a0c82a-4325-709b-8d0b-3368c2a50517', 50, '2030-10-01', '2030-10-31', 31, 183333.333366, 16666.666666, 25740.000004, 70.520547, 2186.136957, 14.040000, 18852.803623, 166666.666700, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(171, '01a0c82a-4325-709b-8d0b-3368c2a50517', 51, '2030-11-01', '2030-11-30', 30, 166666.666700, 16666.666666, 23400.000004, 64.109589, 1923.287670, 14.040000, 18589.954336, 150000.000034, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(172, '01a0c82a-4325-709b-8d0b-3368c2a50517', 52, '2030-12-01', '2030-12-31', 31, 150000.000034, 16666.666666, 21060.000004, 57.698630, 1788.657530, 14.040000, 18455.324196, 133333.333368, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(173, '01a0c82a-4325-709b-8d0b-3368c2a50517', 53, '2031-01-01', '2031-01-31', 31, 133333.333368, 16666.666666, 18720.000004, 51.287671, 1589.917801, 14.040000, 18256.584467, 116666.666702, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(174, '01a0c82a-4325-709b-8d0b-3368c2a50517', 54, '2031-02-01', '2031-02-28', 28, 116666.666702, 16666.666666, 16380.000004, 44.876712, 1256.547936, 14.040000, 17923.214602, 100000.000036, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(175, '01a0c82a-4325-709b-8d0b-3368c2a50517', 55, '2031-03-01', '2031-03-31', 31, 100000.000036, 16666.666666, 14040.000005, 38.465753, 1192.438343, 14.040000, 17859.105009, 83333.333370, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(176, '01a0c82a-4325-709b-8d0b-3368c2a50517', 56, '2031-04-01', '2031-04-30', 30, 83333.333370, 16666.666666, 11700.000005, 32.054794, 961.643820, 14.040000, 17628.310486, 66666.666704, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(177, '01a0c82a-4325-709b-8d0b-3368c2a50517', 57, '2031-05-01', '2031-05-31', 31, 66666.666704, 16666.666666, 9360.000005, 25.643835, 794.958885, 14.040000, 17461.625551, 50000.000038, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(178, '01a0c82a-4325-709b-8d0b-3368c2a50517', 58, '2031-06-01', '2031-06-30', 30, 50000.000038, 16666.666666, 7020.000005, 19.232876, 576.986280, 14.040000, 17243.652946, 33333.333372, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(179, '01a0c82a-4325-709b-8d0b-3368c2a50517', 59, '2031-07-01', '2031-07-31', 31, 33333.333372, 16666.666666, 4680.000005, 12.821917, 397.479427, 14.040000, 17064.146093, 16666.666706, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(180, '01a0c82a-4325-709b-8d0b-3368c2a50517', 60, '2031-08-01', '2031-08-31', 31, 16666.666706, 16666.666706, 2340.000005, 6.410958, 198.739698, 14.040000, 16865.406404, 0.000000, '2026-09-22 08:17:28', '2026-09-22 08:17:28', 88, 88, NULL),
(181, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 1, '2026-09-22', '2026-09-30', 9, 1000000.000000, 16666.666666, 140400.000000, 384.657534, 3461.917806, 14.040000, 20128.584472, 983333.333334, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(182, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 2, '2026-10-01', '2026-10-31', 31, 983333.333334, 16666.666666, 138060.000000, 378.246575, 11725.643825, 14.040000, 28392.310491, 966666.666668, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(183, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 3, '2026-11-01', '2026-11-30', 30, 966666.666668, 16666.666666, 135720.000000, 371.835616, 11155.068480, 14.040000, 27821.735146, 950000.000002, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(184, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 4, '2026-12-01', '2026-12-31', 31, 950000.000002, 16666.666666, 133380.000000, 365.424657, 11328.164367, 14.040000, 27994.831033, 933333.333336, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(185, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 5, '2027-01-01', '2027-01-31', 31, 933333.333336, 16666.666666, 131040.000000, 359.013698, 11129.424638, 14.040000, 27796.091304, 916666.666670, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(186, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 6, '2027-02-01', '2027-02-28', 28, 916666.666670, 16666.666666, 128700.000000, 352.602739, 9872.876692, 14.040000, 26539.543358, 900000.000004, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(187, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 7, '2027-03-01', '2027-03-31', 31, 900000.000004, 16666.666666, 126360.000000, 346.191780, 10731.945180, 14.040000, 27398.611846, 883333.333338, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(188, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 8, '2027-04-01', '2027-04-30', 30, 883333.333338, 16666.666666, 124020.000000, 339.780821, 10193.424630, 14.040000, 26860.091296, 866666.666672, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(189, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 9, '2027-05-01', '2027-05-31', 31, 866666.666672, 16666.666666, 121680.000000, 333.369863, 10334.465753, 14.040000, 27001.132419, 850000.000006, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(190, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 10, '2027-06-01', '2027-06-30', 30, 850000.000006, 16666.666666, 119340.000000, 326.958904, 9808.767120, 14.040000, 26475.433786, 833333.333340, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(191, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 11, '2027-07-01', '2027-07-31', 31, 833333.333340, 16666.666666, 117000.000000, 320.547945, 9936.986295, 14.040000, 26603.652961, 816666.666674, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(192, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 12, '2027-08-01', '2027-08-31', 31, 816666.666674, 16666.666666, 114660.000001, 314.136986, 9738.246566, 14.040000, 26404.913232, 800000.000008, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(193, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 13, '2027-09-01', '2027-09-30', 30, 800000.000008, 16666.666666, 112320.000001, 307.726027, 9231.780810, 14.040000, 25898.447476, 783333.333342, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(194, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 14, '2027-10-01', '2027-10-31', 31, 783333.333342, 16666.666666, 109980.000001, 301.315068, 9340.767108, 14.040000, 26007.433774, 766666.666676, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(195, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 15, '2027-11-01', '2027-11-30', 30, 766666.666676, 16666.666666, 107640.000001, 294.904109, 8847.123270, 14.040000, 25513.789936, 750000.000010, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(196, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 16, '2027-12-01', '2027-12-31', 31, 750000.000010, 16666.666666, 105300.000001, 288.493150, 8943.287650, 14.040000, 25609.954316, 733333.333344, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(197, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 17, '2028-01-01', '2028-01-31', 31, 733333.333344, 16666.666666, 102960.000001, 281.311475, 8720.655725, 14.040000, 25387.322391, 716666.666678, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(198, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 18, '2028-02-01', '2028-02-29', 29, 716666.666678, 16666.666666, 100620.000001, 274.918032, 7972.622928, 14.040000, 24639.289594, 700000.000012, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(199, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 19, '2028-03-01', '2028-03-31', 31, 700000.000012, 16666.666666, 98280.000001, 268.524590, 8324.262290, 14.040000, 24990.928956, 683333.333346, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(200, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 20, '2028-04-01', '2028-04-30', 30, 683333.333346, 16666.666666, 95940.000001, 262.131147, 7863.934410, 14.040000, 24530.601076, 666666.666680, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL);
INSERT INTO `aksic_amortizations` (`id`, `aksic_id`, `installment_no`, `period_start_date`, `due_date`, `days`, `principal_amount_os`, `installment_per_month`, `product`, `interest_rate_per_month`, `total_interest`, `total_rate`, `total_installment`, `principal_balance_after_installment`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`) VALUES
(201, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 21, '2028-05-01', '2028-05-31', 31, 666666.666680, 16666.666666, 93600.000001, 255.737704, 7927.868824, 14.040000, 24594.535490, 650000.000014, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(202, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 22, '2028-06-01', '2028-06-30', 30, 650000.000014, 16666.666666, 91260.000001, 249.344262, 7480.327860, 14.040000, 24146.994526, 633333.333348, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(203, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 23, '2028-07-01', '2028-07-31', 31, 633333.333348, 16666.666666, 88920.000002, 242.950819, 7531.475389, 14.040000, 24198.142055, 616666.666682, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(204, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 24, '2028-08-01', '2028-08-31', 31, 616666.666682, 16666.666666, 86580.000002, 236.557377, 7333.278687, 14.040000, 23999.945353, 600000.000016, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(205, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 25, '2028-09-01', '2028-09-30', 30, 600000.000016, 16666.666666, 84240.000002, 230.163934, 6904.918020, 14.040000, 23571.584686, 583333.333350, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(206, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 26, '2028-10-01', '2028-10-31', 31, 583333.333350, 16666.666666, 81900.000002, 223.770491, 6936.885221, 14.040000, 23603.551887, 566666.666684, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(207, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 27, '2028-11-01', '2028-11-30', 30, 566666.666684, 16666.666666, 79560.000002, 217.377049, 6521.311470, 14.040000, 23187.978136, 550000.000018, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(208, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 28, '2028-12-01', '2028-12-31', 31, 550000.000018, 16666.666666, 77220.000002, 210.983606, 6540.491786, 14.040000, 23207.158452, 533333.333352, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(209, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 29, '2029-01-01', '2029-01-31', 31, 533333.333352, 16666.666666, 74880.000002, 205.150684, 6359.671204, 14.040000, 23026.337870, 516666.666686, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(210, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 30, '2029-02-01', '2029-02-28', 28, 516666.666686, 16666.666666, 72540.000002, 198.739726, 5564.712328, 14.040000, 22231.378994, 500000.000020, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(211, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 31, '2029-03-01', '2029-03-31', 31, 500000.000020, 16666.666666, 70200.000002, 192.328767, 5962.191777, 14.040000, 22628.858443, 483333.333354, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(212, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 32, '2029-04-01', '2029-04-30', 30, 483333.333354, 16666.666666, 67860.000002, 185.917808, 5577.534240, 14.040000, 22244.200906, 466666.666688, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(213, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 33, '2029-05-01', '2029-05-31', 31, 466666.666688, 16666.666666, 65520.000002, 179.506849, 5564.712319, 14.040000, 22231.378985, 450000.000022, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(214, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 34, '2029-06-01', '2029-06-30', 30, 450000.000022, 16666.666666, 63180.000003, 173.095890, 5192.876700, 14.040000, 21859.543366, 433333.333356, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(215, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 35, '2029-07-01', '2029-07-31', 31, 433333.333356, 16666.666666, 60840.000003, 166.684931, 5167.232861, 14.040000, 21833.899527, 416666.666690, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(216, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 36, '2029-08-01', '2029-08-31', 31, 416666.666690, 16666.666666, 58500.000003, 160.273972, 4968.493132, 14.040000, 21635.159798, 400000.000024, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(217, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 37, '2029-09-01', '2029-09-30', 30, 400000.000024, 16666.666666, 56160.000003, 153.863013, 4615.890390, 14.040000, 21282.557056, 383333.333358, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(218, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 38, '2029-10-01', '2029-10-31', 31, 383333.333358, 16666.666666, 53820.000003, 147.452054, 4571.013674, 14.040000, 21237.680340, 366666.666692, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(219, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 39, '2029-11-01', '2029-11-30', 30, 366666.666692, 16666.666666, 51480.000003, 141.041095, 4231.232850, 14.040000, 20897.899516, 350000.000026, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(220, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 40, '2029-12-01', '2029-12-31', 31, 350000.000026, 16666.666666, 49140.000003, 134.630136, 4173.534216, 14.040000, 20840.200882, 333333.333360, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(221, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 41, '2030-01-01', '2030-01-31', 31, 333333.333360, 16666.666666, 46800.000003, 128.219178, 3974.794518, 14.040000, 20641.461184, 316666.666694, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(222, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 42, '2030-02-01', '2030-02-28', 28, 316666.666694, 16666.666666, 44460.000003, 121.808219, 3410.630132, 14.040000, 20077.296798, 300000.000028, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(223, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 43, '2030-03-01', '2030-03-31', 31, 300000.000028, 16666.666666, 42120.000003, 115.397260, 3577.315060, 14.040000, 20243.981726, 283333.333362, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(224, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 44, '2030-04-01', '2030-04-30', 30, 283333.333362, 16666.666666, 39780.000004, 108.986301, 3269.589030, 14.040000, 19936.255696, 266666.666696, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(225, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 45, '2030-05-01', '2030-05-31', 31, 266666.666696, 16666.666666, 37440.000004, 102.575342, 3179.835602, 14.040000, 19846.502268, 250000.000030, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(226, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 46, '2030-06-01', '2030-06-30', 30, 250000.000030, 16666.666666, 35100.000004, 96.164383, 2884.931490, 14.040000, 19551.598156, 233333.333364, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(227, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 47, '2030-07-01', '2030-07-31', 31, 233333.333364, 16666.666666, 32760.000004, 89.753424, 2782.356144, 14.040000, 19449.022810, 216666.666698, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(228, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 48, '2030-08-01', '2030-08-31', 31, 216666.666698, 16666.666666, 30420.000004, 83.342465, 2583.616415, 14.040000, 19250.283081, 200000.000032, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(229, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 49, '2030-09-01', '2030-09-30', 30, 200000.000032, 16666.666666, 28080.000004, 76.931506, 2307.945180, 14.040000, 18974.611846, 183333.333366, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(230, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 50, '2030-10-01', '2030-10-31', 31, 183333.333366, 16666.666666, 25740.000004, 70.520547, 2186.136957, 14.040000, 18852.803623, 166666.666700, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(231, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 51, '2030-11-01', '2030-11-30', 30, 166666.666700, 16666.666666, 23400.000004, 64.109589, 1923.287670, 14.040000, 18589.954336, 150000.000034, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(232, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 52, '2030-12-01', '2030-12-31', 31, 150000.000034, 16666.666666, 21060.000004, 57.698630, 1788.657530, 14.040000, 18455.324196, 133333.333368, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(233, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 53, '2031-01-01', '2031-01-31', 31, 133333.333368, 16666.666666, 18720.000004, 51.287671, 1589.917801, 14.040000, 18256.584467, 116666.666702, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(234, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 54, '2031-02-01', '2031-02-28', 28, 116666.666702, 16666.666666, 16380.000004, 44.876712, 1256.547936, 14.040000, 17923.214602, 100000.000036, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(235, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 55, '2031-03-01', '2031-03-31', 31, 100000.000036, 16666.666666, 14040.000005, 38.465753, 1192.438343, 14.040000, 17859.105009, 83333.333370, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(236, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 56, '2031-04-01', '2031-04-30', 30, 83333.333370, 16666.666666, 11700.000005, 32.054794, 961.643820, 14.040000, 17628.310486, 66666.666704, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(237, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 57, '2031-05-01', '2031-05-31', 31, 66666.666704, 16666.666666, 9360.000005, 25.643835, 794.958885, 14.040000, 17461.625551, 50000.000038, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(238, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 58, '2031-06-01', '2031-06-30', 30, 50000.000038, 16666.666666, 7020.000005, 19.232876, 576.986280, 14.040000, 17243.652946, 33333.333372, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(239, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 59, '2031-07-01', '2031-07-31', 31, 33333.333372, 16666.666666, 4680.000005, 12.821917, 397.479427, 14.040000, 17064.146093, 16666.666706, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL),
(240, '01a0c8ac-e71d-7307-8d93-6a52f6c20183', 60, '2031-08-01', '2031-08-31', 31, 16666.666706, 16666.666706, 2340.000005, 6.410958, 198.739698, 14.040000, 16865.406404, 0.000000, '2026-09-22 10:33:27', '2026-09-22 10:33:27', 88, 88, NULL);

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
  `status` enum('NotCompleted','Pending','In Progress','Forwarded','Approved','Rejected') NOT NULL DEFAULT 'Pending',
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
(1, 'PM Youth Loan Scheme -- Markup Allocation', 'AKSIC Markup Allocation / Budget sheet', NULL, 994225000.00, 25.00, 75.00, 'block', 1, 'Seeded from the AKSIC PMYL markup allocation sheet (Rs 994.225 million; district provision = total x population %).', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL, NULL);

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
(1, 1, 8, 8, 183435000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(2, 1, 1, 1, 161462000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(3, 1, 5, 6, 125272000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(4, 1, 9, 9, 99621000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(5, 1, 10, 10, 100317000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(6, 1, 4, 4, 101610000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(7, 1, 7, 7, 71684000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(8, 1, 3, 3, 59455000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(9, 1, 2, 2, 51501000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(10, 1, 6, 5, 39670000.00, '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL);

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
(1, 1, 8, 'initial', 0.00, 183435000.00, 183435000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(2, 1, 1, 'initial', 0.00, 161462000.00, 161462000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(3, 1, 5, 'initial', 0.00, 125272000.00, 125272000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(4, 1, 9, 'initial', 0.00, 99621000.00, 99621000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(5, 1, 10, 'initial', 0.00, 100317000.00, 100317000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(6, 1, 4, 'initial', 0.00, 101610000.00, 101610000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(7, 1, 7, 'initial', 0.00, 71684000.00, 71684000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(8, 1, 3, 'initial', 0.00, 59455000.00, 59455000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(9, 1, 2, 'initial', 0.00, 51501000.00, 51501000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL),
(10, 1, 6, 'initial', 0.00, 39670000.00, 39670000.00, NULL, NULL, 'Initial allocation from the markup allocation sheet.', '2026-10-28 08:02:19', '2026-10-28 08:02:19', NULL, NULL);

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
('01a18188-1119-7190-b5d1-088bc38c73ff', 'IMPORT_EXPORT', 'Import/Export', 'درآمد/برآمد', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1119-7190-b5d1-088bc3d30077', 'AGRICULTURE', 'Agriculture', 'زراعت', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111a-72f4-b9c3-73fc48ab83ed', 'MANUFACTURING', 'Manufacturing', 'مینوفیکچرنگ', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111a-72f4-b9c3-73fc48d68c23', 'EXCHANGE_COMPANY', 'Exchange Company', 'ایکسچینج کمپنی', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111b-73ba-a93d-126317a75218', 'NBFI', 'NBFI', 'این بی ایف آئی', 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111c-71d4-8b0c-721d716c1b7e', 'SCHEDULED_BANK', 'Scheduled Bank', 'شیڈول بینک', 0, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111c-71d4-8b0c-721d71de7ab9', 'RETAIL_BUSINESS', 'Retail Business', 'ریٹیل کاروبار', 0, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111d-7030-9951-d2f31b7ea8ea', 'BN_OTHERS', 'Others', 'دیگر', 1, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('roster:project:v3:b79314e9acf6fda5284ef53fd79b8e3e', 'O:26:\"Laravel\\Roster\\ProjectScan\":8:{s:8:\"basePath\";s:33:\"/Users/alirazamarchal/Herd/nexus/\";s:3:\"php\";O:35:\"Laravel\\Roster\\Ecosystems\\Ecosystem\":2:{s:9:\"\0*\0byName\";a:169:{s:23:\"alimarchal/id-generator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"alimarchal/id-generator\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";E:43:\"Laravel\\Roster\\Enums\\PackageSource:Composer\";s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.1\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/alimarchal/id-generator\";}s:19:\"bacon/bacon-qr-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"bacon/bacon-qr-code\";s:10:\"\0*\0version\";s:5:\"3.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/bacon/bacon-qr-code\";}s:23:\"barryvdh/laravel-dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"barryvdh/laravel-dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/barryvdh/laravel-dompdf\";}s:10:\"brick/math\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"brick/math\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/vendor/brick/math\";}s:31:\"carbonphp/carbon-doctrine-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"carbonphp/carbon-doctrine-types\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/vendor/carbonphp/carbon-doctrine-types\";}s:13:\"composer/pcre\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"composer/pcre\";s:10:\"\0*\0version\";s:5:\"3.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/pcre\";}s:15:\"composer/semver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"composer/semver\";s:10:\"\0*\0version\";s:5:\"3.5.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/semver\";}s:12:\"dasprid/enum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dasprid/enum\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/dasprid/enum\";}s:23:\"dflydev/dot-access-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"dflydev/dot-access-data\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/dflydev/dot-access-data\";}s:21:\"doctrine/deprecations\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"doctrine/deprecations\";s:10:\"\0*\0version\";s:5:\"1.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/deprecations\";}s:18:\"doctrine/inflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"doctrine/inflector\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/inflector\";}s:14:\"doctrine/lexer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"doctrine/lexer\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/lexer\";}s:13:\"dompdf/dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"dompdf/dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/dompdf\";}s:19:\"dompdf/php-font-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"dompdf/php-font-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/php-font-lib\";}s:18:\"dompdf/php-svg-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"dompdf/php-svg-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/php-svg-lib\";}s:29:\"dragonmantank/cron-expression\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"dragonmantank/cron-expression\";s:10:\"\0*\0version\";s:5:\"3.6.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/dragonmantank/cron-expression\";}s:23:\"egulias/email-validator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"egulias/email-validator\";s:10:\"\0*\0version\";s:5:\"4.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/egulias/email-validator\";}s:18:\"fruitcake/php-cors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"fruitcake/php-cors\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/fruitcake/php-cors\";}s:27:\"graham-campbell/result-type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"graham-campbell/result-type\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/graham-campbell/result-type\";}s:17:\"guzzlehttp/guzzle\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"guzzlehttp/guzzle\";s:10:\"\0*\0version\";s:5:\"8.3.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/guzzle\";}s:19:\"guzzlehttp/promises\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"guzzlehttp/promises\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/promises\";}s:15:\"guzzlehttp/psr7\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"guzzlehttp/psr7\";s:10:\"\0*\0version\";s:5:\"3.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/psr7\";}s:23:\"guzzlehttp/uri-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"guzzlehttp/uri-template\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/uri-template\";}s:15:\"laravel/fortify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/fortify\";s:10:\"\0*\0version\";s:6:\"1.41.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/fortify\";}s:17:\"laravel/framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"13.39.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^13.0\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/framework\";}s:17:\"laravel/jetstream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/jetstream\";s:10:\"\0*\0version\";s:5:\"5.5.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^5.3\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/jetstream\";}s:16:\"laravel/passkeys\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"laravel/passkeys\";s:10:\"\0*\0version\";s:5:\"0.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/passkeys\";}s:15:\"laravel/prompts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:6:\"0.3.26\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/prompts\";}s:15:\"laravel/sanctum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/sanctum\";s:10:\"\0*\0version\";s:5:\"4.3.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/sanctum\";}s:28:\"laravel/serializable-closure\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"laravel/serializable-closure\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/serializable-closure\";}s:14:\"laravel/tinker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/tinker\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/tinker\";}s:17:\"league/commonmark\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"league/commonmark\";s:10:\"\0*\0version\";s:6:\"2.10.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/league/commonmark\";}s:13:\"league/config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"league/config\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/league/config\";}s:16:\"league/flysystem\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"league/flysystem\";s:10:\"\0*\0version\";s:6:\"3.36.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/league/flysystem\";}s:22:\"league/flysystem-local\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"league/flysystem-local\";s:10:\"\0*\0version\";s:6:\"3.35.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/league/flysystem-local\";}s:26:\"league/mime-type-detection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"league/mime-type-detection\";s:10:\"\0*\0version\";s:6:\"1.17.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/league/mime-type-detection\";}s:10:\"league/uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"league/uri\";s:10:\"\0*\0version\";s:5:\"7.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/vendor/league/uri\";}s:21:\"league/uri-interfaces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"league/uri-interfaces\";s:10:\"\0*\0version\";s:5:\"7.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/league/uri-interfaces\";}s:17:\"livewire/livewire\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"livewire/livewire\";s:10:\"\0*\0version\";s:6:\"3.8.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/livewire/livewire\";}s:23:\"maennchen/zipstream-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"maennchen/zipstream-php\";s:10:\"\0*\0version\";s:5:\"3.2.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/maennchen/zipstream-php\";}s:17:\"markbaker/complex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"markbaker/complex\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/markbaker/complex\";}s:16:\"markbaker/matrix\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"markbaker/matrix\";s:10:\"\0*\0version\";s:5:\"3.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/markbaker/matrix\";}s:17:\"masterminds/html5\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"masterminds/html5\";s:10:\"\0*\0version\";s:6:\"2.12.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/masterminds/html5\";}s:28:\"mobiledetect/mobiledetectlib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"mobiledetect/mobiledetectlib\";s:10:\"\0*\0version\";s:6:\"4.11.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/mobiledetect/mobiledetectlib\";}s:15:\"monolog/monolog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"monolog/monolog\";s:10:\"\0*\0version\";s:6:\"3.12.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/monolog/monolog\";}s:13:\"nesbot/carbon\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"nesbot/carbon\";s:10:\"\0*\0version\";s:6:\"3.15.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/nesbot/carbon\";}s:12:\"nette/schema\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"nette/schema\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/nette/schema\";}s:11:\"nette/utils\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"nette/utils\";s:10:\"\0*\0version\";s:5:\"4.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/nette/utils\";}s:16:\"nikic/php-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"nikic/php-parser\";s:10:\"\0*\0version\";s:5:\"5.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/nikic/php-parser\";}s:19:\"nunomaduro/termwind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"nunomaduro/termwind\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/nunomaduro/termwind\";}s:32:\"paragonie/constant_time_encoding\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"paragonie/constant_time_encoding\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/vendor/paragonie/constant_time_encoding\";}s:31:\"phpdocumentor/reflection-common\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"phpdocumentor/reflection-common\";s:10:\"\0*\0version\";s:5:\"2.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/reflection-common\";}s:33:\"phpdocumentor/reflection-docblock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"phpdocumentor/reflection-docblock\";s:10:\"\0*\0version\";s:5:\"6.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/reflection-docblock\";}s:27:\"phpdocumentor/type-resolver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"phpdocumentor/type-resolver\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/type-resolver\";}s:24:\"phpoffice/phpspreadsheet\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"phpoffice/phpspreadsheet\";s:10:\"\0*\0version\";s:6:\"5.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^5.7\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/phpoffice/phpspreadsheet\";}s:19:\"phpoption/phpoption\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpoption/phpoption\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/phpoption/phpoption\";}s:21:\"phpstan/phpdoc-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"phpstan/phpdoc-parser\";s:10:\"\0*\0version\";s:5:\"2.3.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/phpstan/phpdoc-parser\";}s:18:\"pragmarx/google2fa\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"pragmarx/google2fa\";s:10:\"\0*\0version\";s:5:\"9.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/pragmarx/google2fa\";}s:9:\"psr/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psr/clock\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/clock\";}s:13:\"psr/container\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"psr/container\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/container\";}s:20:\"psr/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"psr/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/event-dispatcher\";}s:15:\"psr/http-client\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"psr/http-client\";s:10:\"\0*\0version\";s:5:\"1.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-client\";}s:16:\"psr/http-factory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-factory\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-factory\";}s:16:\"psr/http-message\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-message\";s:10:\"\0*\0version\";s:3:\"2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-message\";}s:7:\"psr/log\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"psr/log\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/log\";}s:16:\"psr/simple-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/simple-cache\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/simple-cache\";}s:9:\"psy/psysh\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psy/psysh\";s:10:\"\0*\0version\";s:7:\"0.12.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/vendor/psy/psysh\";}s:17:\"ramsey/collection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"ramsey/collection\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/ramsey/collection\";}s:11:\"ramsey/uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ramsey/uuid\";s:10:\"\0*\0version\";s:5:\"4.9.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/ramsey/uuid\";}s:25:\"sabberworm/php-css-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"sabberworm/php-css-parser\";s:10:\"\0*\0version\";s:5:\"9.5.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/sabberworm/php-css-parser\";}s:12:\"spatie/image\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"spatie/image\";s:10:\"\0*\0version\";s:5:\"3.9.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/image\";}s:22:\"spatie/image-optimizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"spatie/image-optimizer\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/image-optimizer\";}s:26:\"spatie/laravel-activitylog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"spatie/laravel-activitylog\";s:10:\"\0*\0version\";s:6:\"4.12.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.9\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-activitylog\";}s:27:\"spatie/laravel-medialibrary\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"spatie/laravel-medialibrary\";s:10:\"\0*\0version\";s:8:\"11.23.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^11.23\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-medialibrary\";}s:28:\"spatie/laravel-package-tools\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-package-tools\";s:10:\"\0*\0version\";s:6:\"1.93.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-package-tools\";}s:25:\"spatie/laravel-permission\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spatie/laravel-permission\";s:10:\"\0*\0version\";s:6:\"6.25.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^6.10\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-permission\";}s:28:\"spatie/laravel-query-builder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-query-builder\";s:10:\"\0*\0version\";s:5:\"6.4.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^6.3\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-query-builder\";}s:26:\"spatie/temporary-directory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"spatie/temporary-directory\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/temporary-directory\";}s:20:\"spomky-labs/cbor-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"spomky-labs/cbor-php\";s:10:\"\0*\0version\";s:5:\"3.4.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/spomky-labs/cbor-php\";}s:25:\"spomky-labs/pki-framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spomky-labs/pki-framework\";s:10:\"\0*\0version\";s:5:\"1.6.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/spomky-labs/pki-framework\";}s:13:\"symfony/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"symfony/clock\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/clock\";}s:15:\"symfony/console\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/console\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/console\";}s:20:\"symfony/css-selector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"symfony/css-selector\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/css-selector\";}s:29:\"symfony/deprecation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/deprecation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/deprecation-contracts\";}s:21:\"symfony/error-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/error-handler\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/error-handler\";}s:24:\"symfony/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"symfony/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/event-dispatcher\";}s:34:\"symfony/event-dispatcher-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"symfony/event-dispatcher-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/event-dispatcher-contracts\";}s:14:\"symfony/finder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/finder\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/finder\";}s:23:\"symfony/http-foundation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/http-foundation\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/http-foundation\";}s:19:\"symfony/http-kernel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/http-kernel\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/http-kernel\";}s:14:\"symfony/mailer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/mailer\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/mailer\";}s:12:\"symfony/mime\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/mime\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/mime\";}s:22:\"symfony/polyfill-ctype\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-ctype\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-ctype\";}s:30:\"symfony/polyfill-intl-grapheme\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"symfony/polyfill-intl-grapheme\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-grapheme\";}s:25:\"symfony/polyfill-intl-idn\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-intl-idn\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-idn\";}s:32:\"symfony/polyfill-intl-normalizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"symfony/polyfill-intl-normalizer\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-normalizer\";}s:25:\"symfony/polyfill-mbstring\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-mbstring\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-mbstring\";}s:22:\"symfony/polyfill-php80\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php80\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php80\";}s:22:\"symfony/polyfill-php81\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php81\";s:10:\"\0*\0version\";s:6:\"1.38.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php81\";}s:22:\"symfony/polyfill-php82\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php82\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php82\";}s:22:\"symfony/polyfill-php84\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php84\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php84\";}s:22:\"symfony/polyfill-php85\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php85\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php85\";}s:22:\"symfony/polyfill-php86\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php86\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php86\";}s:21:\"symfony/polyfill-time\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-time\";s:10:\"\0*\0version\";s:6:\"1.42.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-time\";}s:21:\"symfony/polyfill-uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-uuid\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-uuid\";}s:15:\"symfony/process\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/process\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/process\";}s:23:\"symfony/property-access\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/property-access\";s:10:\"\0*\0version\";s:5:\"8.1.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/property-access\";}s:21:\"symfony/property-info\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/property-info\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/property-info\";}s:15:\"symfony/routing\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/routing\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/routing\";}s:18:\"symfony/serializer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/serializer\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/serializer\";}s:25:\"symfony/service-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/service-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/service-contracts\";}s:14:\"symfony/string\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/string\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/string\";}s:19:\"symfony/translation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/translation\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/translation\";}s:29:\"symfony/translation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/translation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/translation-contracts\";}s:17:\"symfony/type-info\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"symfony/type-info\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/type-info\";}s:11:\"symfony/uid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"symfony/uid\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/uid\";}s:18:\"symfony/var-dumper\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/var-dumper\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/var-dumper\";}s:33:\"tijsverkoyen/css-to-inline-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"tijsverkoyen/css-to-inline-styles\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/vendor/tijsverkoyen/css-to-inline-styles\";}s:16:\"vlucas/phpdotenv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"vlucas/phpdotenv\";s:10:\"\0*\0version\";s:5:\"5.8.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/vlucas/phpdotenv\";}s:19:\"voku/portable-ascii\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"voku/portable-ascii\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/voku/portable-ascii\";}s:17:\"web-auth/cose-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"web-auth/cose-lib\";s:10:\"\0*\0version\";s:5:\"4.8.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/web-auth/cose-lib\";}s:21:\"web-auth/webauthn-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"web-auth/webauthn-lib\";s:10:\"\0*\0version\";s:5:\"5.3.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/web-auth/webauthn-lib\";}s:16:\"webmozart/assert\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"webmozart/assert\";s:10:\"\0*\0version\";s:5:\"2.5.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/webmozart/assert\";}s:17:\"brianium/paratest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"brianium/paratest\";s:10:\"\0*\0version\";s:6:\"7.20.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/brianium/paratest\";}s:23:\"composer/xdebug-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"composer/xdebug-handler\";s:10:\"\0*\0version\";s:5:\"3.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/xdebug-handler\";}s:14:\"fakerphp/faker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"fakerphp/faker\";s:10:\"\0*\0version\";s:6:\"1.24.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.23\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/fakerphp/faker\";}s:22:\"fidry/cpu-core-counter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"fidry/cpu-core-counter\";s:10:\"\0*\0version\";s:5:\"1.4.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/fidry/cpu-core-counter\";}s:11:\"filp/whoops\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"filp/whoops\";s:10:\"\0*\0version\";s:6:\"2.18.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/filp/whoops\";}s:21:\"hamcrest/hamcrest-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"hamcrest/hamcrest-php\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/hamcrest/hamcrest-php\";}s:30:\"jean85/pretty-package-versions\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"jean85/pretty-package-versions\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/vendor/jean85/pretty-package-versions\";}s:13:\"laravel/boost\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"laravel/boost\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^2.0\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/boost\";}s:11:\"laravel/mcp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"1.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/mcp\";}s:12:\"laravel/pail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pail\";s:10:\"\0*\0version\";s:5:\"1.2.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.1\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/pail\";}s:12:\"laravel/pint\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.32.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.13\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/pint\";}s:14:\"laravel/roster\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/roster\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/roster\";}s:12:\"laravel/sail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/sail\";s:10:\"\0*\0version\";s:6:\"1.69.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.26\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/sail\";}s:15:\"mockery/mockery\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"mockery/mockery\";s:10:\"\0*\0version\";s:6:\"1.6.15\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.6\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/mockery/mockery\";}s:17:\"myclabs/deep-copy\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"myclabs/deep-copy\";s:10:\"\0*\0version\";s:6:\"1.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/myclabs/deep-copy\";}s:20:\"nunomaduro/collision\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"nunomaduro/collision\";s:10:\"\0*\0version\";s:5:\"8.9.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.1\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/nunomaduro/collision\";}s:12:\"pestphp/pest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"pestphp/pest\";s:10:\"\0*\0version\";s:5:\"4.7.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest\";}s:19:\"pestphp/pest-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"pestphp/pest-plugin\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin\";}s:24:\"pestphp/pest-plugin-arch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"pestphp/pest-plugin-arch\";s:10:\"\0*\0version\";s:5:\"4.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-arch\";}s:26:\"pestphp/pest-plugin-mutate\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"pestphp/pest-plugin-mutate\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-mutate\";}s:29:\"pestphp/pest-plugin-profanity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"pestphp/pest-plugin-profanity\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-profanity\";}s:16:\"phar-io/manifest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"phar-io/manifest\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/phar-io/manifest\";}s:15:\"phar-io/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phar-io/version\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/phar-io/version\";}s:25:\"phpunit/php-code-coverage\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-code-coverage\";s:10:\"\0*\0version\";s:6:\"12.5.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-code-coverage\";}s:25:\"phpunit/php-file-iterator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-file-iterator\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-file-iterator\";}s:19:\"phpunit/php-invoker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpunit/php-invoker\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-invoker\";}s:25:\"phpunit/php-text-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-text-template\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-text-template\";}s:17:\"phpunit/php-timer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"phpunit/php-timer\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-timer\";}s:15:\"phpunit/phpunit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:7:\"12.5.33\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^12.0\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/phpunit\";}s:20:\"sebastian/cli-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/cli-parser\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/cli-parser\";}s:20:\"sebastian/comparator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/comparator\";s:10:\"\0*\0version\";s:5:\"7.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/comparator\";}s:20:\"sebastian/complexity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/complexity\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/complexity\";}s:14:\"sebastian/diff\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/diff\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/diff\";}s:21:\"sebastian/environment\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"sebastian/environment\";s:10:\"\0*\0version\";s:5:\"8.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/environment\";}s:18:\"sebastian/exporter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"sebastian/exporter\";s:10:\"\0*\0version\";s:5:\"7.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/exporter\";}s:22:\"sebastian/global-state\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"sebastian/global-state\";s:10:\"\0*\0version\";s:5:\"8.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/global-state\";}s:23:\"sebastian/lines-of-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"sebastian/lines-of-code\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/lines-of-code\";}s:27:\"sebastian/object-enumerator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/object-enumerator\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/object-enumerator\";}s:26:\"sebastian/object-reflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"sebastian/object-reflector\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/object-reflector\";}s:27:\"sebastian/recursion-context\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/recursion-context\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/recursion-context\";}s:14:\"sebastian/type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/type\";s:10:\"\0*\0version\";s:5:\"6.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/type\";}s:17:\"sebastian/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"sebastian/version\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/version\";}s:28:\"staabm/side-effects-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"staabm/side-effects-detector\";s:10:\"\0*\0version\";s:5:\"1.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/staabm/side-effects-detector\";}s:12:\"symfony/yaml\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/yaml\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/yaml\";}s:35:\"ta-tikoma/phpunit-architecture-test\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:35:\"ta-tikoma/phpunit-architecture-test\";s:10:\"\0*\0version\";s:5:\"0.8.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/vendor/ta-tikoma/phpunit-architecture-test\";}s:17:\"theseer/tokenizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"theseer/tokenizer\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/theseer/tokenizer\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:169:{i:0;r:5;i:1;r:13;i:2;r:21;i:3;r:29;i:4;r:37;i:5;r:45;i:6;r:53;i:7;r:61;i:8;r:69;i:9;r:77;i:10;r:85;i:11;r:93;i:12;r:101;i:13;r:109;i:14;r:117;i:15;r:125;i:16;r:133;i:17;r:141;i:18;r:149;i:19;r:157;i:20;r:165;i:21;r:173;i:22;r:181;i:23;r:189;i:24;r:197;i:25;r:205;i:26;r:213;i:27;r:221;i:28;r:229;i:29;r:237;i:30;r:245;i:31;r:253;i:32;r:261;i:33;r:269;i:34;r:277;i:35;r:285;i:36;r:293;i:37;r:301;i:38;r:309;i:39;r:317;i:40;r:325;i:41;r:333;i:42;r:341;i:43;r:349;i:44;r:357;i:45;r:365;i:46;r:373;i:47;r:381;i:48;r:389;i:49;r:397;i:50;r:405;i:51;r:413;i:52;r:421;i:53;r:429;i:54;r:437;i:55;r:445;i:56;r:453;i:57;r:461;i:58;r:469;i:59;r:477;i:60;r:485;i:61;r:493;i:62;r:501;i:63;r:509;i:64;r:517;i:65;r:525;i:66;r:533;i:67;r:541;i:68;r:549;i:69;r:557;i:70;r:565;i:71;r:573;i:72;r:581;i:73;r:589;i:74;r:597;i:75;r:605;i:76;r:613;i:77;r:621;i:78;r:629;i:79;r:637;i:80;r:645;i:81;r:653;i:82;r:661;i:83;r:669;i:84;r:677;i:85;r:685;i:86;r:693;i:87;r:701;i:88;r:709;i:89;r:717;i:90;r:725;i:91;r:733;i:92;r:741;i:93;r:749;i:94;r:757;i:95;r:765;i:96;r:773;i:97;r:781;i:98;r:789;i:99;r:797;i:100;r:805;i:101;r:813;i:102;r:821;i:103;r:829;i:104;r:837;i:105;r:845;i:106;r:853;i:107;r:861;i:108;r:869;i:109;r:877;i:110;r:885;i:111;r:893;i:112;r:901;i:113;r:909;i:114;r:917;i:115;r:925;i:116;r:933;i:117;r:941;i:118;r:949;i:119;r:957;i:120;r:965;i:121;r:973;i:122;r:981;i:123;r:989;i:124;r:997;i:125;r:1005;i:126;r:1013;i:127;r:1021;i:128;r:1029;i:129;r:1037;i:130;r:1045;i:131;r:1053;i:132;r:1061;i:133;r:1069;i:134;r:1077;i:135;r:1085;i:136;r:1093;i:137;r:1101;i:138;r:1109;i:139;r:1117;i:140;r:1125;i:141;r:1133;i:142;r:1141;i:143;r:1149;i:144;r:1157;i:145;r:1165;i:146;r:1173;i:147;r:1181;i:148;r:1189;i:149;r:1197;i:150;r:1205;i:151;r:1213;i:152;r:1221;i:153;r:1229;i:154;r:1237;i:155;r:1245;i:156;r:1253;i:157;r:1261;i:158;r:1269;i:159;r:1277;i:160;r:1285;i:161;r:1293;i:162;r:1301;i:163;r:1309;i:164;r:1317;i:165;r:1325;i:166;r:1333;i:167;r:1341;i:168;r:1349;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:2:\"js\";O:37:\"Laravel\\Roster\\Ecosystems\\JsEcosystem\":3:{s:9:\"\0*\0byName\";a:184:{s:16:\"@alloc/quick-lru\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@alloc/quick-lru\";s:10:\"\0*\0version\";s:5:\"5.2.0\";s:9:\"\0*\0source\";E:38:\"Laravel\\Roster\\Enums\\PackageSource:Npm\";s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@alloc/quick-lru\";}s:18:\"@esbuild/aix-ppc64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/aix-ppc64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/aix-ppc64\";}s:20:\"@esbuild/android-arm\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/android-arm\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-arm\";}s:22:\"@esbuild/android-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/android-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-arm64\";}s:20:\"@esbuild/android-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/android-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-x64\";}s:21:\"@esbuild/darwin-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"@esbuild/darwin-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/darwin-arm64\";}s:19:\"@esbuild/darwin-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/darwin-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/darwin-x64\";}s:22:\"@esbuild/freebsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/freebsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/freebsd-arm64\";}s:20:\"@esbuild/freebsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/freebsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/freebsd-x64\";}s:18:\"@esbuild/linux-arm\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/linux-arm\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-arm\";}s:20:\"@esbuild/linux-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-arm64\";}s:19:\"@esbuild/linux-ia32\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/linux-ia32\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-ia32\";}s:22:\"@esbuild/linux-loong64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/linux-loong64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-loong64\";}s:23:\"@esbuild/linux-mips64el\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@esbuild/linux-mips64el\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-mips64el\";}s:20:\"@esbuild/linux-ppc64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-ppc64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-ppc64\";}s:22:\"@esbuild/linux-riscv64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/linux-riscv64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-riscv64\";}s:20:\"@esbuild/linux-s390x\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-s390x\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-s390x\";}s:18:\"@esbuild/linux-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/linux-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-x64\";}s:21:\"@esbuild/netbsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"@esbuild/netbsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/netbsd-arm64\";}s:19:\"@esbuild/netbsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/netbsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/netbsd-x64\";}s:22:\"@esbuild/openbsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/openbsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openbsd-arm64\";}s:20:\"@esbuild/openbsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/openbsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openbsd-x64\";}s:26:\"@esbuild/openharmony-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"@esbuild/openharmony-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openharmony-arm64\";}s:18:\"@esbuild/sunos-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/sunos-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/sunos-x64\";}s:20:\"@esbuild/win32-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/win32-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-arm64\";}s:19:\"@esbuild/win32-ia32\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/win32-ia32\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-ia32\";}s:18:\"@esbuild/win32-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/win32-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-x64\";}s:23:\"@jridgewell/gen-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@jridgewell/gen-mapping\";s:10:\"\0*\0version\";s:6:\"0.3.13\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/gen-mapping\";}s:23:\"@jridgewell/resolve-uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@jridgewell/resolve-uri\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/resolve-uri\";}s:27:\"@jridgewell/sourcemap-codec\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"@jridgewell/sourcemap-codec\";s:10:\"\0*\0version\";s:5:\"1.5.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/sourcemap-codec\";}s:25:\"@jridgewell/trace-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"@jridgewell/trace-mapping\";s:10:\"\0*\0version\";s:6:\"0.3.31\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/trace-mapping\";}s:19:\"@nodelib/fs.scandir\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@nodelib/fs.scandir\";s:10:\"\0*\0version\";s:5:\"2.1.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.scandir\";}s:16:\"@nodelib/fs.stat\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@nodelib/fs.stat\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.stat\";}s:16:\"@nodelib/fs.walk\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@nodelib/fs.walk\";s:10:\"\0*\0version\";s:5:\"1.2.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.walk\";}s:31:\"@rollup/rollup-android-arm-eabi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-android-arm-eabi\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-android-arm-eabi\";}s:28:\"@rollup/rollup-android-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-android-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-android-arm64\";}s:27:\"@rollup/rollup-darwin-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"@rollup/rollup-darwin-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-darwin-arm64\";}s:25:\"@rollup/rollup-darwin-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"@rollup/rollup-darwin-x64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-darwin-x64\";}s:28:\"@rollup/rollup-freebsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-freebsd-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-freebsd-arm64\";}s:26:\"@rollup/rollup-freebsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"@rollup/rollup-freebsd-x64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-freebsd-x64\";}s:34:\"@rollup/rollup-linux-arm-gnueabihf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"@rollup/rollup-linux-arm-gnueabihf\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:80:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm-gnueabihf\";}s:35:\"@rollup/rollup-linux-arm-musleabihf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:35:\"@rollup/rollup-linux-arm-musleabihf\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:81:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm-musleabihf\";}s:30:\"@rollup/rollup-linux-arm64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-arm64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm64-gnu\";}s:31:\"@rollup/rollup-linux-arm64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-linux-arm64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm64-musl\";}s:32:\"@rollup/rollup-linux-loong64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-linux-loong64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-loong64-gnu\";}s:30:\"@rollup/rollup-linux-ppc64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-ppc64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-ppc64-gnu\";}s:32:\"@rollup/rollup-linux-riscv64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-linux-riscv64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-riscv64-gnu\";}s:33:\"@rollup/rollup-linux-riscv64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"@rollup/rollup-linux-riscv64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:79:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-riscv64-musl\";}s:30:\"@rollup/rollup-linux-s390x-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-s390x-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-s390x-gnu\";}s:28:\"@rollup/rollup-linux-x64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-linux-x64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-x64-gnu\";}s:29:\"@rollup/rollup-linux-x64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"@rollup/rollup-linux-x64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-x64-musl\";}s:32:\"@rollup/rollup-openharmony-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-openharmony-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-openharmony-arm64\";}s:31:\"@rollup/rollup-win32-arm64-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-win32-arm64-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-arm64-msvc\";}s:30:\"@rollup/rollup-win32-ia32-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-win32-ia32-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-ia32-msvc\";}s:28:\"@rollup/rollup-win32-x64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-win32-x64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-x64-gnu\";}s:29:\"@rollup/rollup-win32-x64-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"@rollup/rollup-win32-x64-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-x64-msvc\";}s:18:\"@tailwindcss/forms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@tailwindcss/forms\";s:10:\"\0*\0version\";s:6:\"0.5.11\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^0.5.11\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@tailwindcss/forms\";}s:23:\"@tailwindcss/typography\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@tailwindcss/typography\";s:10:\"\0*\0version\";s:6:\"0.5.20\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^0.5.20\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@tailwindcss/typography\";}s:13:\"@types/estree\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"@types/estree\";s:10:\"\0*\0version\";s:5:\"1.0.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/@types/estree\";}s:10:\"agent-base\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"agent-base\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/agent-base\";}s:10:\"ansi-regex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"ansi-regex\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/ansi-regex\";}s:11:\"ansi-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ansi-styles\";s:10:\"\0*\0version\";s:5:\"4.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/ansi-styles\";}s:11:\"any-promise\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"any-promise\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/any-promise\";}s:8:\"anymatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"anymatch\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/anymatch\";}s:3:\"arg\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:3:\"arg\";s:10:\"\0*\0version\";s:5:\"5.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/node_modules/arg\";}s:8:\"asynckit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"asynckit\";s:10:\"\0*\0version\";s:5:\"0.4.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/asynckit\";}s:12:\"autoprefixer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"autoprefixer\";s:10:\"\0*\0version\";s:6:\"10.6.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^10.6.2\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/autoprefixer\";}s:5:\"axios\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"axios\";s:10:\"\0*\0version\";s:6:\"1.20.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^1.21.0\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/axios\";}s:24:\"baseline-browser-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"baseline-browser-mapping\";s:10:\"\0*\0version\";s:7:\"2.11.32\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/node_modules/baseline-browser-mapping\";}s:17:\"binary-extensions\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"binary-extensions\";s:10:\"\0*\0version\";s:5:\"2.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/binary-extensions\";}s:6:\"braces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"braces\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/braces\";}s:12:\"browserslist\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"browserslist\";s:10:\"\0*\0version\";s:6:\"4.29.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/browserslist\";}s:23:\"call-bind-apply-helpers\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"call-bind-apply-helpers\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/call-bind-apply-helpers\";}s:13:\"camelcase-css\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"camelcase-css\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/camelcase-css\";}s:12:\"caniuse-lite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"caniuse-lite\";s:10:\"\0*\0version\";s:12:\"1.0.30001819\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/caniuse-lite\";}s:5:\"chalk\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"chalk\";s:10:\"\0*\0version\";s:5:\"4.1.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/chalk\";}s:8:\"chokidar\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"chokidar\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/chokidar\";}s:5:\"cliui\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"cliui\";s:10:\"\0*\0version\";s:5:\"8.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/cliui\";}s:13:\"color-convert\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"color-convert\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/color-convert\";}s:10:\"color-name\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"color-name\";s:10:\"\0*\0version\";s:5:\"1.1.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/color-name\";}s:15:\"combined-stream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"combined-stream\";s:10:\"\0*\0version\";s:5:\"1.0.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/combined-stream\";}s:9:\"commander\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"commander\";s:10:\"\0*\0version\";s:5:\"4.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/commander\";}s:12:\"concurrently\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"concurrently\";s:10:\"\0*\0version\";s:5:\"9.2.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^9.2.5\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/concurrently\";}s:6:\"cssesc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"cssesc\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/cssesc\";}s:5:\"debug\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"debug\";s:10:\"\0*\0version\";s:5:\"4.4.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/debug\";}s:14:\"delayed-stream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"delayed-stream\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/delayed-stream\";}s:10:\"didyoumean\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"didyoumean\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/didyoumean\";}s:3:\"dlv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:3:\"dlv\";s:10:\"\0*\0version\";s:5:\"1.1.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/node_modules/dlv\";}s:12:\"dunder-proto\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dunder-proto\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/dunder-proto\";}s:20:\"electron-to-chromium\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"electron-to-chromium\";s:10:\"\0*\0version\";s:7:\"1.5.464\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/electron-to-chromium\";}s:11:\"emoji-regex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"emoji-regex\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/emoji-regex\";}s:18:\"es-define-property\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"es-define-property\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-define-property\";}s:9:\"es-errors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"es-errors\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-errors\";}s:15:\"es-object-atoms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"es-object-atoms\";s:10:\"\0*\0version\";s:5:\"1.1.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-object-atoms\";}s:18:\"es-set-tostringtag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"es-set-tostringtag\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-set-tostringtag\";}s:7:\"esbuild\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"esbuild\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/esbuild\";}s:8:\"escalade\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"escalade\";s:10:\"\0*\0version\";s:5:\"3.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/escalade\";}s:9:\"fast-glob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"fast-glob\";s:10:\"\0*\0version\";s:5:\"3.3.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/fast-glob\";}s:5:\"fastq\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"fastq\";s:10:\"\0*\0version\";s:6:\"1.19.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/fastq\";}s:10:\"fill-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"fill-range\";s:10:\"\0*\0version\";s:5:\"7.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/fill-range\";}s:16:\"follow-redirects\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"follow-redirects\";s:10:\"\0*\0version\";s:6:\"1.16.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/follow-redirects\";}s:9:\"form-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"form-data\";s:10:\"\0*\0version\";s:5:\"4.0.6\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/form-data\";}s:11:\"fraction.js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"fraction.js\";s:10:\"\0*\0version\";s:5:\"5.3.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/fraction.js\";}s:8:\"fsevents\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"fsevents\";s:10:\"\0*\0version\";s:5:\"2.3.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/fsevents\";}s:13:\"function-bind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"function-bind\";s:10:\"\0*\0version\";s:5:\"1.1.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/function-bind\";}s:15:\"get-caller-file\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"get-caller-file\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-caller-file\";}s:13:\"get-intrinsic\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"get-intrinsic\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-intrinsic\";}s:9:\"get-proto\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"get-proto\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-proto\";}s:11:\"glob-parent\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"glob-parent\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/glob-parent\";}s:4:\"gopd\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"gopd\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/gopd\";}s:8:\"has-flag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"has-flag\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-flag\";}s:11:\"has-symbols\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"has-symbols\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-symbols\";}s:15:\"has-tostringtag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"has-tostringtag\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-tostringtag\";}s:6:\"hasown\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"hasown\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/hasown\";}s:17:\"https-proxy-agent\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"https-proxy-agent\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/https-proxy-agent\";}s:14:\"is-binary-path\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"is-binary-path\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-binary-path\";}s:14:\"is-core-module\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"is-core-module\";s:10:\"\0*\0version\";s:6:\"2.16.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-core-module\";}s:10:\"is-extglob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"is-extglob\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-extglob\";}s:23:\"is-fullwidth-code-point\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"is-fullwidth-code-point\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-fullwidth-code-point\";}s:7:\"is-glob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"is-glob\";s:10:\"\0*\0version\";s:5:\"4.0.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-glob\";}s:9:\"is-number\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"is-number\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-number\";}s:4:\"jiti\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"jiti\";s:10:\"\0*\0version\";s:6:\"1.21.7\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/jiti\";}s:19:\"laravel-vite-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"laravel-vite-plugin\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.3.0\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/laravel-vite-plugin\";}s:9:\"lilconfig\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"lilconfig\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/lilconfig\";}s:17:\"lines-and-columns\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"lines-and-columns\";s:10:\"\0*\0version\";s:5:\"1.2.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/lines-and-columns\";}s:15:\"math-intrinsics\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"math-intrinsics\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/math-intrinsics\";}s:6:\"merge2\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"merge2\";s:10:\"\0*\0version\";s:5:\"1.4.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/merge2\";}s:10:\"micromatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"micromatch\";s:10:\"\0*\0version\";s:5:\"4.0.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/micromatch\";}s:7:\"mime-db\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"mime-db\";s:10:\"\0*\0version\";s:6:\"1.52.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/mime-db\";}s:10:\"mime-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"mime-types\";s:10:\"\0*\0version\";s:6:\"2.1.35\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/mime-types\";}s:17:\"mini-svg-data-uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"mini-svg-data-uri\";s:10:\"\0*\0version\";s:5:\"1.4.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/mini-svg-data-uri\";}s:2:\"ms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:2:\"ms\";s:10:\"\0*\0version\";s:5:\"2.1.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"/Users/alirazamarchal/Herd/nexus/node_modules/ms\";}s:2:\"mz\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:2:\"mz\";s:10:\"\0*\0version\";s:5:\"2.7.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"/Users/alirazamarchal/Herd/nexus/node_modules/mz\";}s:6:\"nanoid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"nanoid\";s:10:\"\0*\0version\";s:6:\"3.3.20\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/nanoid\";}s:13:\"node-releases\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"node-releases\";s:10:\"\0*\0version\";s:6:\"2.0.60\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/node-releases\";}s:14:\"normalize-path\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"normalize-path\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/normalize-path\";}s:13:\"object-assign\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"object-assign\";s:10:\"\0*\0version\";s:5:\"4.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/object-assign\";}s:11:\"object-hash\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"object-hash\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/object-hash\";}s:10:\"path-parse\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"path-parse\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/path-parse\";}s:10:\"picocolors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"picocolors\";s:10:\"\0*\0version\";s:5:\"1.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/picocolors\";}s:9:\"picomatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"picomatch\";s:10:\"\0*\0version\";s:5:\"2.3.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/picomatch\";}s:4:\"pify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"pify\";s:10:\"\0*\0version\";s:5:\"2.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/pify\";}s:7:\"pirates\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"pirates\";s:10:\"\0*\0version\";s:5:\"4.0.7\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/pirates\";}s:7:\"postcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"postcss\";s:10:\"\0*\0version\";s:6:\"8.5.31\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^8.5.32\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss\";}s:14:\"postcss-import\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"postcss-import\";s:10:\"\0*\0version\";s:6:\"15.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-import\";}s:10:\"postcss-js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"postcss-js\";s:10:\"\0*\0version\";s:5:\"4.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-js\";}s:19:\"postcss-load-config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"postcss-load-config\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-load-config\";}s:14:\"postcss-nested\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"postcss-nested\";s:10:\"\0*\0version\";s:5:\"6.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-nested\";}s:23:\"postcss-selector-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"postcss-selector-parser\";s:10:\"\0*\0version\";s:6:\"6.0.10\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-selector-parser\";}s:20:\"postcss-value-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"postcss-value-parser\";s:10:\"\0*\0version\";s:5:\"4.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-value-parser\";}s:14:\"proxy-from-env\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"proxy-from-env\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/proxy-from-env\";}s:15:\"queue-microtask\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"queue-microtask\";s:10:\"\0*\0version\";s:5:\"1.2.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/queue-microtask\";}s:10:\"read-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"read-cache\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/read-cache\";}s:8:\"readdirp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"readdirp\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/readdirp\";}s:17:\"require-directory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"require-directory\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/require-directory\";}s:7:\"resolve\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"resolve\";s:10:\"\0*\0version\";s:7:\"1.22.11\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/resolve\";}s:7:\"reusify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"reusify\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/reusify\";}s:6:\"rollup\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"rollup\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/rollup\";}s:12:\"run-parallel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"run-parallel\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/run-parallel\";}s:4:\"rxjs\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"rxjs\";s:10:\"\0*\0version\";s:5:\"7.8.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/rxjs\";}s:11:\"shell-quote\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"shell-quote\";s:10:\"\0*\0version\";s:6:\"1.12.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/shell-quote\";}s:13:\"source-map-js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"source-map-js\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/source-map-js\";}s:12:\"string-width\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"string-width\";s:10:\"\0*\0version\";s:5:\"4.2.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/string-width\";}s:10:\"strip-ansi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"strip-ansi\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/strip-ansi\";}s:7:\"sucrase\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"sucrase\";s:10:\"\0*\0version\";s:6:\"3.35.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/sucrase\";}s:14:\"supports-color\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"supports-color\";s:10:\"\0*\0version\";s:5:\"8.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/supports-color\";}s:31:\"supports-preserve-symlinks-flag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"supports-preserve-symlinks-flag\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/supports-preserve-symlinks-flag\";}s:11:\"tailwindcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:6:\"3.4.19\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^3.4.19\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/tailwindcss\";}s:7:\"thenify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"thenify\";s:10:\"\0*\0version\";s:5:\"3.3.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/thenify\";}s:11:\"thenify-all\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"thenify-all\";s:10:\"\0*\0version\";s:5:\"1.6.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/thenify-all\";}s:10:\"tinyglobby\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"tinyglobby\";s:10:\"\0*\0version\";s:6:\"0.2.15\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/tinyglobby\";}s:14:\"to-regex-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"to-regex-range\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/to-regex-range\";}s:9:\"tree-kill\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"tree-kill\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/tree-kill\";}s:20:\"ts-interface-checker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"ts-interface-checker\";s:10:\"\0*\0version\";s:6:\"0.1.13\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/ts-interface-checker\";}s:5:\"tslib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"tslib\";s:10:\"\0*\0version\";s:5:\"2.8.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/tslib\";}s:22:\"update-browserslist-db\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"update-browserslist-db\";s:10:\"\0*\0version\";s:5:\"1.3.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/update-browserslist-db\";}s:14:\"util-deprecate\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"util-deprecate\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/util-deprecate\";}s:4:\"vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"vite\";s:10:\"\0*\0version\";s:5:\"6.4.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^6.4.5\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/vite\";}s:23:\"vite-plugin-full-reload\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"vite-plugin-full-reload\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/vite-plugin-full-reload\";}s:9:\"wrap-ansi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"wrap-ansi\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/wrap-ansi\";}s:4:\"y18n\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"y18n\";s:10:\"\0*\0version\";s:5:\"5.0.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/y18n\";}s:5:\"yargs\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"yargs\";s:10:\"\0*\0version\";s:6:\"17.7.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/yargs\";}s:12:\"yargs-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"yargs-parser\";s:10:\"\0*\0version\";s:6:\"21.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/yargs-parser\";}s:4:\"fdir\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"fdir\";s:10:\"\0*\0version\";s:5:\"6.5.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/fdir\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:184:{i:0;r:1531;i:1;r:1539;i:2;r:1547;i:3;r:1555;i:4;r:1563;i:5;r:1571;i:6;r:1579;i:7;r:1587;i:8;r:1595;i:9;r:1603;i:10;r:1611;i:11;r:1619;i:12;r:1627;i:13;r:1635;i:14;r:1643;i:15;r:1651;i:16;r:1659;i:17;r:1667;i:18;r:1675;i:19;r:1683;i:20;r:1691;i:21;r:1699;i:22;r:1707;i:23;r:1715;i:24;r:1723;i:25;r:1731;i:26;r:1739;i:27;r:1747;i:28;r:1755;i:29;r:1763;i:30;r:1771;i:31;r:1779;i:32;r:1787;i:33;r:1795;i:34;r:1803;i:35;r:1811;i:36;r:1819;i:37;r:1827;i:38;r:1835;i:39;r:1843;i:40;r:1851;i:41;r:1859;i:42;r:1867;i:43;r:1875;i:44;r:1883;i:45;r:1891;i:46;r:1899;i:47;r:1907;i:48;r:1915;i:49;r:1923;i:50;r:1931;i:51;r:1939;i:52;r:1947;i:53;r:1955;i:54;r:1963;i:55;r:1971;i:56;r:1979;i:57;r:1987;i:58;r:1995;i:59;r:2003;i:60;r:2011;i:61;r:2019;i:62;r:2027;i:63;r:2035;i:64;r:2043;i:65;r:2051;i:66;r:2059;i:67;r:2067;i:68;r:2075;i:69;r:2083;i:70;r:2091;i:71;r:2099;i:72;r:2107;i:73;r:2115;i:74;r:2123;i:75;r:2131;i:76;r:2139;i:77;r:2147;i:78;r:2155;i:79;r:2163;i:80;r:2171;i:81;r:2179;i:82;r:2187;i:83;r:2195;i:84;r:2203;i:85;r:2211;i:86;r:2219;i:87;r:2227;i:88;r:2235;i:89;r:2243;i:90;r:2251;i:91;r:2259;i:92;r:2267;i:93;r:2275;i:94;r:2283;i:95;r:2291;i:96;r:2299;i:97;r:2307;i:98;r:2315;i:99;r:2323;i:100;r:2331;i:101;r:2339;i:102;r:2347;i:103;r:2355;i:104;r:2363;i:105;r:2371;i:106;r:2379;i:107;r:2387;i:108;r:2395;i:109;r:2403;i:110;r:2411;i:111;r:2419;i:112;r:2427;i:113;r:2435;i:114;r:2443;i:115;r:2451;i:116;r:2459;i:117;r:2467;i:118;r:2475;i:119;r:2483;i:120;r:2491;i:121;r:2499;i:122;r:2507;i:123;r:2515;i:124;r:2523;i:125;r:2531;i:126;r:2539;i:127;r:2547;i:128;r:2555;i:129;r:2563;i:130;r:2571;i:131;r:2579;i:132;r:2587;i:133;r:2595;i:134;r:2603;i:135;r:2611;i:136;r:2619;i:137;r:2627;i:138;r:2635;i:139;r:2643;i:140;r:2651;i:141;r:2659;i:142;r:2667;i:143;r:2675;i:144;r:2683;i:145;r:2691;i:146;r:2699;i:147;r:2707;i:148;r:2715;i:149;r:2723;i:150;r:2731;i:151;r:2739;i:152;r:2747;i:153;r:2755;i:154;r:2763;i:155;r:2771;i:156;r:2779;i:157;r:2787;i:158;r:2795;i:159;r:2803;i:160;r:2811;i:161;r:2819;i:162;r:2827;i:163;r:2835;i:164;r:2843;i:165;r:2851;i:166;r:2859;i:167;r:2867;i:168;r:2875;i:169;r:2883;i:170;r:2891;i:171;r:2899;i:172;r:2907;i:173;r:2915;i:174;r:2923;i:175;r:2931;i:176;r:2939;i:177;r:2947;i:178;r:2955;i:179;r:2963;i:180;r:2971;i:181;r:2979;i:182;r:2987;i:183;r:2995;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:17:\"\0*\0packageManager\";E:41:\"Laravel\\Roster\\Enums\\JsPackageManager:Npm\";}s:6:\"stacks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:35:\"Laravel\\Roster\\Enums\\Stack:Livewire\";}}s:21:\"browserTestFrameworks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:9:\"frontends\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:6:\"agents\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:4:{i:0;E:37:\"Laravel\\Roster\\Enums\\Agent:ClaudeCode\";i:1;E:32:\"Laravel\\Roster\\Enums\\Agent:Codex\";i:2;E:34:\"Laravel\\Roster\\Enums\\Agent:Copilot\";i:3;E:33:\"Laravel\\Roster\\Enums\\Agent:Gemini\";}}s:7:\"editors\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:34:\"Laravel\\Roster\\Enums\\Editor:VsCode\";}}}', 1793180358);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('roster:project:v3:e3f7dc63b91b2866b0963add02a8cad8', 'O:26:\"Laravel\\Roster\\ProjectScan\":8:{s:8:\"basePath\";s:33:\"/Users/alirazamarchal/Herd/nexus/\";s:3:\"php\";O:35:\"Laravel\\Roster\\Ecosystems\\Ecosystem\":2:{s:9:\"\0*\0byName\";a:169:{s:23:\"alimarchal/id-generator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"alimarchal/id-generator\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";E:43:\"Laravel\\Roster\\Enums\\PackageSource:Composer\";s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.1\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/alimarchal/id-generator\";}s:19:\"bacon/bacon-qr-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"bacon/bacon-qr-code\";s:10:\"\0*\0version\";s:5:\"3.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/bacon/bacon-qr-code\";}s:23:\"barryvdh/laravel-dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"barryvdh/laravel-dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/barryvdh/laravel-dompdf\";}s:10:\"brick/math\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"brick/math\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/vendor/brick/math\";}s:31:\"carbonphp/carbon-doctrine-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"carbonphp/carbon-doctrine-types\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/vendor/carbonphp/carbon-doctrine-types\";}s:13:\"composer/pcre\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"composer/pcre\";s:10:\"\0*\0version\";s:5:\"3.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/pcre\";}s:15:\"composer/semver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"composer/semver\";s:10:\"\0*\0version\";s:5:\"3.5.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/semver\";}s:12:\"dasprid/enum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dasprid/enum\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/dasprid/enum\";}s:23:\"dflydev/dot-access-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"dflydev/dot-access-data\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/dflydev/dot-access-data\";}s:21:\"doctrine/deprecations\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"doctrine/deprecations\";s:10:\"\0*\0version\";s:5:\"1.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/deprecations\";}s:18:\"doctrine/inflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"doctrine/inflector\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/inflector\";}s:14:\"doctrine/lexer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"doctrine/lexer\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/doctrine/lexer\";}s:13:\"dompdf/dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"dompdf/dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/dompdf\";}s:19:\"dompdf/php-font-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"dompdf/php-font-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/php-font-lib\";}s:18:\"dompdf/php-svg-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"dompdf/php-svg-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/dompdf/php-svg-lib\";}s:29:\"dragonmantank/cron-expression\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"dragonmantank/cron-expression\";s:10:\"\0*\0version\";s:5:\"3.6.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/dragonmantank/cron-expression\";}s:23:\"egulias/email-validator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"egulias/email-validator\";s:10:\"\0*\0version\";s:5:\"4.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/egulias/email-validator\";}s:18:\"fruitcake/php-cors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"fruitcake/php-cors\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/fruitcake/php-cors\";}s:27:\"graham-campbell/result-type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"graham-campbell/result-type\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/graham-campbell/result-type\";}s:17:\"guzzlehttp/guzzle\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"guzzlehttp/guzzle\";s:10:\"\0*\0version\";s:5:\"8.3.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/guzzle\";}s:19:\"guzzlehttp/promises\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"guzzlehttp/promises\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/promises\";}s:15:\"guzzlehttp/psr7\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"guzzlehttp/psr7\";s:10:\"\0*\0version\";s:5:\"3.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/psr7\";}s:23:\"guzzlehttp/uri-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"guzzlehttp/uri-template\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/guzzlehttp/uri-template\";}s:15:\"laravel/fortify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/fortify\";s:10:\"\0*\0version\";s:6:\"1.41.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/fortify\";}s:17:\"laravel/framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"13.39.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^13.0\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/framework\";}s:17:\"laravel/jetstream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/jetstream\";s:10:\"\0*\0version\";s:5:\"5.5.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^5.3\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/jetstream\";}s:16:\"laravel/passkeys\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"laravel/passkeys\";s:10:\"\0*\0version\";s:5:\"0.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/passkeys\";}s:15:\"laravel/prompts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:6:\"0.3.26\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/prompts\";}s:15:\"laravel/sanctum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/sanctum\";s:10:\"\0*\0version\";s:5:\"4.3.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/sanctum\";}s:28:\"laravel/serializable-closure\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"laravel/serializable-closure\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/serializable-closure\";}s:14:\"laravel/tinker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/tinker\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/tinker\";}s:17:\"league/commonmark\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"league/commonmark\";s:10:\"\0*\0version\";s:6:\"2.10.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/league/commonmark\";}s:13:\"league/config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"league/config\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/league/config\";}s:16:\"league/flysystem\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"league/flysystem\";s:10:\"\0*\0version\";s:6:\"3.36.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/league/flysystem\";}s:22:\"league/flysystem-local\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"league/flysystem-local\";s:10:\"\0*\0version\";s:6:\"3.35.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/league/flysystem-local\";}s:26:\"league/mime-type-detection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"league/mime-type-detection\";s:10:\"\0*\0version\";s:6:\"1.17.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/league/mime-type-detection\";}s:10:\"league/uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"league/uri\";s:10:\"\0*\0version\";s:5:\"7.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/vendor/league/uri\";}s:21:\"league/uri-interfaces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"league/uri-interfaces\";s:10:\"\0*\0version\";s:5:\"7.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/league/uri-interfaces\";}s:17:\"livewire/livewire\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"livewire/livewire\";s:10:\"\0*\0version\";s:6:\"3.8.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/livewire/livewire\";}s:23:\"maennchen/zipstream-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"maennchen/zipstream-php\";s:10:\"\0*\0version\";s:5:\"3.2.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/maennchen/zipstream-php\";}s:17:\"markbaker/complex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"markbaker/complex\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/markbaker/complex\";}s:16:\"markbaker/matrix\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"markbaker/matrix\";s:10:\"\0*\0version\";s:5:\"3.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/markbaker/matrix\";}s:17:\"masterminds/html5\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"masterminds/html5\";s:10:\"\0*\0version\";s:6:\"2.12.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/masterminds/html5\";}s:28:\"mobiledetect/mobiledetectlib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"mobiledetect/mobiledetectlib\";s:10:\"\0*\0version\";s:6:\"4.11.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/mobiledetect/mobiledetectlib\";}s:15:\"monolog/monolog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"monolog/monolog\";s:10:\"\0*\0version\";s:6:\"3.12.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/monolog/monolog\";}s:13:\"nesbot/carbon\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"nesbot/carbon\";s:10:\"\0*\0version\";s:6:\"3.15.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/nesbot/carbon\";}s:12:\"nette/schema\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"nette/schema\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/nette/schema\";}s:11:\"nette/utils\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"nette/utils\";s:10:\"\0*\0version\";s:5:\"4.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/nette/utils\";}s:16:\"nikic/php-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"nikic/php-parser\";s:10:\"\0*\0version\";s:5:\"5.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/nikic/php-parser\";}s:19:\"nunomaduro/termwind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"nunomaduro/termwind\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/nunomaduro/termwind\";}s:32:\"paragonie/constant_time_encoding\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"paragonie/constant_time_encoding\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/vendor/paragonie/constant_time_encoding\";}s:31:\"phpdocumentor/reflection-common\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"phpdocumentor/reflection-common\";s:10:\"\0*\0version\";s:5:\"2.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/reflection-common\";}s:33:\"phpdocumentor/reflection-docblock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"phpdocumentor/reflection-docblock\";s:10:\"\0*\0version\";s:5:\"6.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/reflection-docblock\";}s:27:\"phpdocumentor/type-resolver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"phpdocumentor/type-resolver\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/phpdocumentor/type-resolver\";}s:24:\"phpoffice/phpspreadsheet\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"phpoffice/phpspreadsheet\";s:10:\"\0*\0version\";s:6:\"5.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^5.7\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/phpoffice/phpspreadsheet\";}s:19:\"phpoption/phpoption\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpoption/phpoption\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/phpoption/phpoption\";}s:21:\"phpstan/phpdoc-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"phpstan/phpdoc-parser\";s:10:\"\0*\0version\";s:5:\"2.3.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/phpstan/phpdoc-parser\";}s:18:\"pragmarx/google2fa\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"pragmarx/google2fa\";s:10:\"\0*\0version\";s:5:\"9.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/pragmarx/google2fa\";}s:9:\"psr/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psr/clock\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/clock\";}s:13:\"psr/container\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"psr/container\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/container\";}s:20:\"psr/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"psr/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/event-dispatcher\";}s:15:\"psr/http-client\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"psr/http-client\";s:10:\"\0*\0version\";s:5:\"1.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-client\";}s:16:\"psr/http-factory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-factory\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-factory\";}s:16:\"psr/http-message\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-message\";s:10:\"\0*\0version\";s:3:\"2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/http-message\";}s:7:\"psr/log\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"psr/log\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/log\";}s:16:\"psr/simple-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/simple-cache\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/psr/simple-cache\";}s:9:\"psy/psysh\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psy/psysh\";s:10:\"\0*\0version\";s:7:\"0.12.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/vendor/psy/psysh\";}s:17:\"ramsey/collection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"ramsey/collection\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/ramsey/collection\";}s:11:\"ramsey/uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ramsey/uuid\";s:10:\"\0*\0version\";s:5:\"4.9.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/ramsey/uuid\";}s:25:\"sabberworm/php-css-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"sabberworm/php-css-parser\";s:10:\"\0*\0version\";s:5:\"9.5.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/sabberworm/php-css-parser\";}s:12:\"spatie/image\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"spatie/image\";s:10:\"\0*\0version\";s:5:\"3.9.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/image\";}s:22:\"spatie/image-optimizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"spatie/image-optimizer\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/image-optimizer\";}s:26:\"spatie/laravel-activitylog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"spatie/laravel-activitylog\";s:10:\"\0*\0version\";s:6:\"4.12.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.9\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-activitylog\";}s:27:\"spatie/laravel-medialibrary\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"spatie/laravel-medialibrary\";s:10:\"\0*\0version\";s:8:\"11.23.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^11.23\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-medialibrary\";}s:28:\"spatie/laravel-package-tools\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-package-tools\";s:10:\"\0*\0version\";s:6:\"1.93.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-package-tools\";}s:25:\"spatie/laravel-permission\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spatie/laravel-permission\";s:10:\"\0*\0version\";s:6:\"6.25.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^6.10\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-permission\";}s:28:\"spatie/laravel-query-builder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-query-builder\";s:10:\"\0*\0version\";s:5:\"6.4.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^6.3\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/laravel-query-builder\";}s:26:\"spatie/temporary-directory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"spatie/temporary-directory\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/spatie/temporary-directory\";}s:20:\"spomky-labs/cbor-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"spomky-labs/cbor-php\";s:10:\"\0*\0version\";s:5:\"3.4.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/spomky-labs/cbor-php\";}s:25:\"spomky-labs/pki-framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spomky-labs/pki-framework\";s:10:\"\0*\0version\";s:5:\"1.6.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/spomky-labs/pki-framework\";}s:13:\"symfony/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"symfony/clock\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/clock\";}s:15:\"symfony/console\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/console\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/console\";}s:20:\"symfony/css-selector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"symfony/css-selector\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/css-selector\";}s:29:\"symfony/deprecation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/deprecation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/deprecation-contracts\";}s:21:\"symfony/error-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/error-handler\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/error-handler\";}s:24:\"symfony/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"symfony/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/event-dispatcher\";}s:34:\"symfony/event-dispatcher-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"symfony/event-dispatcher-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/event-dispatcher-contracts\";}s:14:\"symfony/finder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/finder\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/finder\";}s:23:\"symfony/http-foundation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/http-foundation\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/http-foundation\";}s:19:\"symfony/http-kernel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/http-kernel\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/http-kernel\";}s:14:\"symfony/mailer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/mailer\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/mailer\";}s:12:\"symfony/mime\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/mime\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/mime\";}s:22:\"symfony/polyfill-ctype\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-ctype\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-ctype\";}s:30:\"symfony/polyfill-intl-grapheme\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"symfony/polyfill-intl-grapheme\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-grapheme\";}s:25:\"symfony/polyfill-intl-idn\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-intl-idn\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-idn\";}s:32:\"symfony/polyfill-intl-normalizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"symfony/polyfill-intl-normalizer\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-intl-normalizer\";}s:25:\"symfony/polyfill-mbstring\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-mbstring\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-mbstring\";}s:22:\"symfony/polyfill-php80\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php80\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php80\";}s:22:\"symfony/polyfill-php81\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php81\";s:10:\"\0*\0version\";s:6:\"1.38.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php81\";}s:22:\"symfony/polyfill-php82\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php82\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php82\";}s:22:\"symfony/polyfill-php84\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php84\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php84\";}s:22:\"symfony/polyfill-php85\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php85\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php85\";}s:22:\"symfony/polyfill-php86\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php86\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-php86\";}s:21:\"symfony/polyfill-time\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-time\";s:10:\"\0*\0version\";s:6:\"1.42.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-time\";}s:21:\"symfony/polyfill-uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-uuid\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/polyfill-uuid\";}s:15:\"symfony/process\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/process\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/process\";}s:23:\"symfony/property-access\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/property-access\";s:10:\"\0*\0version\";s:5:\"8.1.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/property-access\";}s:21:\"symfony/property-info\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/property-info\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/property-info\";}s:15:\"symfony/routing\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/routing\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/routing\";}s:18:\"symfony/serializer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/serializer\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/serializer\";}s:25:\"symfony/service-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/service-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/service-contracts\";}s:14:\"symfony/string\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/string\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/string\";}s:19:\"symfony/translation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/translation\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/translation\";}s:29:\"symfony/translation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/translation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/translation-contracts\";}s:17:\"symfony/type-info\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"symfony/type-info\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/type-info\";}s:11:\"symfony/uid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"symfony/uid\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/uid\";}s:18:\"symfony/var-dumper\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/var-dumper\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/var-dumper\";}s:33:\"tijsverkoyen/css-to-inline-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"tijsverkoyen/css-to-inline-styles\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/vendor/tijsverkoyen/css-to-inline-styles\";}s:16:\"vlucas/phpdotenv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"vlucas/phpdotenv\";s:10:\"\0*\0version\";s:5:\"5.8.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/vlucas/phpdotenv\";}s:19:\"voku/portable-ascii\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"voku/portable-ascii\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/voku/portable-ascii\";}s:17:\"web-auth/cose-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"web-auth/cose-lib\";s:10:\"\0*\0version\";s:5:\"4.8.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/web-auth/cose-lib\";}s:21:\"web-auth/webauthn-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"web-auth/webauthn-lib\";s:10:\"\0*\0version\";s:5:\"5.3.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/web-auth/webauthn-lib\";}s:16:\"webmozart/assert\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"webmozart/assert\";s:10:\"\0*\0version\";s:5:\"2.5.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/webmozart/assert\";}s:17:\"brianium/paratest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"brianium/paratest\";s:10:\"\0*\0version\";s:6:\"7.20.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/brianium/paratest\";}s:23:\"composer/xdebug-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"composer/xdebug-handler\";s:10:\"\0*\0version\";s:5:\"3.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/composer/xdebug-handler\";}s:14:\"fakerphp/faker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"fakerphp/faker\";s:10:\"\0*\0version\";s:6:\"1.24.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.23\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/fakerphp/faker\";}s:22:\"fidry/cpu-core-counter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"fidry/cpu-core-counter\";s:10:\"\0*\0version\";s:5:\"1.4.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/fidry/cpu-core-counter\";}s:11:\"filp/whoops\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"filp/whoops\";s:10:\"\0*\0version\";s:6:\"2.18.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/filp/whoops\";}s:21:\"hamcrest/hamcrest-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"hamcrest/hamcrest-php\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/hamcrest/hamcrest-php\";}s:30:\"jean85/pretty-package-versions\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"jean85/pretty-package-versions\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/vendor/jean85/pretty-package-versions\";}s:13:\"laravel/boost\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"laravel/boost\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^2.0\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/boost\";}s:11:\"laravel/mcp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"1.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/mcp\";}s:12:\"laravel/pail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pail\";s:10:\"\0*\0version\";s:5:\"1.2.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.1\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/pail\";}s:12:\"laravel/pint\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.32.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.13\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/pint\";}s:14:\"laravel/roster\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/roster\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/roster\";}s:12:\"laravel/sail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/sail\";s:10:\"\0*\0version\";s:6:\"1.69.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.26\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/laravel/sail\";}s:15:\"mockery/mockery\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"mockery/mockery\";s:10:\"\0*\0version\";s:6:\"1.6.15\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.6\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/mockery/mockery\";}s:17:\"myclabs/deep-copy\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"myclabs/deep-copy\";s:10:\"\0*\0version\";s:6:\"1.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/myclabs/deep-copy\";}s:20:\"nunomaduro/collision\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"nunomaduro/collision\";s:10:\"\0*\0version\";s:5:\"8.9.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.1\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/nunomaduro/collision\";}s:12:\"pestphp/pest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"pestphp/pest\";s:10:\"\0*\0version\";s:5:\"4.7.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest\";}s:19:\"pestphp/pest-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"pestphp/pest-plugin\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin\";}s:24:\"pestphp/pest-plugin-arch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"pestphp/pest-plugin-arch\";s:10:\"\0*\0version\";s:5:\"4.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-arch\";}s:26:\"pestphp/pest-plugin-mutate\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"pestphp/pest-plugin-mutate\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-mutate\";}s:29:\"pestphp/pest-plugin-profanity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"pestphp/pest-plugin-profanity\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/vendor/pestphp/pest-plugin-profanity\";}s:16:\"phar-io/manifest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"phar-io/manifest\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/vendor/phar-io/manifest\";}s:15:\"phar-io/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phar-io/version\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/phar-io/version\";}s:25:\"phpunit/php-code-coverage\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-code-coverage\";s:10:\"\0*\0version\";s:6:\"12.5.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-code-coverage\";}s:25:\"phpunit/php-file-iterator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-file-iterator\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-file-iterator\";}s:19:\"phpunit/php-invoker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpunit/php-invoker\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-invoker\";}s:25:\"phpunit/php-text-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-text-template\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-text-template\";}s:17:\"phpunit/php-timer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"phpunit/php-timer\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/php-timer\";}s:15:\"phpunit/phpunit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:7:\"12.5.33\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^12.0\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/vendor/phpunit/phpunit\";}s:20:\"sebastian/cli-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/cli-parser\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/cli-parser\";}s:20:\"sebastian/comparator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/comparator\";s:10:\"\0*\0version\";s:5:\"7.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/comparator\";}s:20:\"sebastian/complexity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/complexity\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/complexity\";}s:14:\"sebastian/diff\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/diff\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/diff\";}s:21:\"sebastian/environment\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"sebastian/environment\";s:10:\"\0*\0version\";s:5:\"8.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/environment\";}s:18:\"sebastian/exporter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"sebastian/exporter\";s:10:\"\0*\0version\";s:5:\"7.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/exporter\";}s:22:\"sebastian/global-state\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"sebastian/global-state\";s:10:\"\0*\0version\";s:5:\"8.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/global-state\";}s:23:\"sebastian/lines-of-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"sebastian/lines-of-code\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/lines-of-code\";}s:27:\"sebastian/object-enumerator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/object-enumerator\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/object-enumerator\";}s:26:\"sebastian/object-reflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"sebastian/object-reflector\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/object-reflector\";}s:27:\"sebastian/recursion-context\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/recursion-context\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/recursion-context\";}s:14:\"sebastian/type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/type\";s:10:\"\0*\0version\";s:5:\"6.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/type\";}s:17:\"sebastian/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"sebastian/version\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/sebastian/version\";}s:28:\"staabm/side-effects-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"staabm/side-effects-detector\";s:10:\"\0*\0version\";s:5:\"1.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/vendor/staabm/side-effects-detector\";}s:12:\"symfony/yaml\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/yaml\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/vendor/symfony/yaml\";}s:35:\"ta-tikoma/phpunit-architecture-test\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:35:\"ta-tikoma/phpunit-architecture-test\";s:10:\"\0*\0version\";s:5:\"0.8.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/vendor/ta-tikoma/phpunit-architecture-test\";}s:17:\"theseer/tokenizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"theseer/tokenizer\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/vendor/theseer/tokenizer\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:169:{i:0;r:5;i:1;r:13;i:2;r:21;i:3;r:29;i:4;r:37;i:5;r:45;i:6;r:53;i:7;r:61;i:8;r:69;i:9;r:77;i:10;r:85;i:11;r:93;i:12;r:101;i:13;r:109;i:14;r:117;i:15;r:125;i:16;r:133;i:17;r:141;i:18;r:149;i:19;r:157;i:20;r:165;i:21;r:173;i:22;r:181;i:23;r:189;i:24;r:197;i:25;r:205;i:26;r:213;i:27;r:221;i:28;r:229;i:29;r:237;i:30;r:245;i:31;r:253;i:32;r:261;i:33;r:269;i:34;r:277;i:35;r:285;i:36;r:293;i:37;r:301;i:38;r:309;i:39;r:317;i:40;r:325;i:41;r:333;i:42;r:341;i:43;r:349;i:44;r:357;i:45;r:365;i:46;r:373;i:47;r:381;i:48;r:389;i:49;r:397;i:50;r:405;i:51;r:413;i:52;r:421;i:53;r:429;i:54;r:437;i:55;r:445;i:56;r:453;i:57;r:461;i:58;r:469;i:59;r:477;i:60;r:485;i:61;r:493;i:62;r:501;i:63;r:509;i:64;r:517;i:65;r:525;i:66;r:533;i:67;r:541;i:68;r:549;i:69;r:557;i:70;r:565;i:71;r:573;i:72;r:581;i:73;r:589;i:74;r:597;i:75;r:605;i:76;r:613;i:77;r:621;i:78;r:629;i:79;r:637;i:80;r:645;i:81;r:653;i:82;r:661;i:83;r:669;i:84;r:677;i:85;r:685;i:86;r:693;i:87;r:701;i:88;r:709;i:89;r:717;i:90;r:725;i:91;r:733;i:92;r:741;i:93;r:749;i:94;r:757;i:95;r:765;i:96;r:773;i:97;r:781;i:98;r:789;i:99;r:797;i:100;r:805;i:101;r:813;i:102;r:821;i:103;r:829;i:104;r:837;i:105;r:845;i:106;r:853;i:107;r:861;i:108;r:869;i:109;r:877;i:110;r:885;i:111;r:893;i:112;r:901;i:113;r:909;i:114;r:917;i:115;r:925;i:116;r:933;i:117;r:941;i:118;r:949;i:119;r:957;i:120;r:965;i:121;r:973;i:122;r:981;i:123;r:989;i:124;r:997;i:125;r:1005;i:126;r:1013;i:127;r:1021;i:128;r:1029;i:129;r:1037;i:130;r:1045;i:131;r:1053;i:132;r:1061;i:133;r:1069;i:134;r:1077;i:135;r:1085;i:136;r:1093;i:137;r:1101;i:138;r:1109;i:139;r:1117;i:140;r:1125;i:141;r:1133;i:142;r:1141;i:143;r:1149;i:144;r:1157;i:145;r:1165;i:146;r:1173;i:147;r:1181;i:148;r:1189;i:149;r:1197;i:150;r:1205;i:151;r:1213;i:152;r:1221;i:153;r:1229;i:154;r:1237;i:155;r:1245;i:156;r:1253;i:157;r:1261;i:158;r:1269;i:159;r:1277;i:160;r:1285;i:161;r:1293;i:162;r:1301;i:163;r:1309;i:164;r:1317;i:165;r:1325;i:166;r:1333;i:167;r:1341;i:168;r:1349;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:2:\"js\";O:37:\"Laravel\\Roster\\Ecosystems\\JsEcosystem\":3:{s:9:\"\0*\0byName\";a:184:{s:16:\"@alloc/quick-lru\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@alloc/quick-lru\";s:10:\"\0*\0version\";s:5:\"5.2.0\";s:9:\"\0*\0source\";E:38:\"Laravel\\Roster\\Enums\\PackageSource:Npm\";s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@alloc/quick-lru\";}s:18:\"@esbuild/aix-ppc64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/aix-ppc64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/aix-ppc64\";}s:20:\"@esbuild/android-arm\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/android-arm\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-arm\";}s:22:\"@esbuild/android-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/android-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-arm64\";}s:20:\"@esbuild/android-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/android-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/android-x64\";}s:21:\"@esbuild/darwin-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"@esbuild/darwin-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/darwin-arm64\";}s:19:\"@esbuild/darwin-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/darwin-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/darwin-x64\";}s:22:\"@esbuild/freebsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/freebsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/freebsd-arm64\";}s:20:\"@esbuild/freebsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/freebsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/freebsd-x64\";}s:18:\"@esbuild/linux-arm\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/linux-arm\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-arm\";}s:20:\"@esbuild/linux-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-arm64\";}s:19:\"@esbuild/linux-ia32\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/linux-ia32\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-ia32\";}s:22:\"@esbuild/linux-loong64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/linux-loong64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-loong64\";}s:23:\"@esbuild/linux-mips64el\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@esbuild/linux-mips64el\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-mips64el\";}s:20:\"@esbuild/linux-ppc64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-ppc64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-ppc64\";}s:22:\"@esbuild/linux-riscv64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/linux-riscv64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-riscv64\";}s:20:\"@esbuild/linux-s390x\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/linux-s390x\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-s390x\";}s:18:\"@esbuild/linux-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/linux-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/linux-x64\";}s:21:\"@esbuild/netbsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"@esbuild/netbsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/netbsd-arm64\";}s:19:\"@esbuild/netbsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/netbsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/netbsd-x64\";}s:22:\"@esbuild/openbsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"@esbuild/openbsd-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openbsd-arm64\";}s:20:\"@esbuild/openbsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/openbsd-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openbsd-x64\";}s:26:\"@esbuild/openharmony-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"@esbuild/openharmony-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/openharmony-arm64\";}s:18:\"@esbuild/sunos-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/sunos-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/sunos-x64\";}s:20:\"@esbuild/win32-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"@esbuild/win32-arm64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-arm64\";}s:19:\"@esbuild/win32-ia32\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@esbuild/win32-ia32\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-ia32\";}s:18:\"@esbuild/win32-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@esbuild/win32-x64\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@esbuild/win32-x64\";}s:23:\"@jridgewell/gen-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@jridgewell/gen-mapping\";s:10:\"\0*\0version\";s:6:\"0.3.13\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/gen-mapping\";}s:23:\"@jridgewell/resolve-uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@jridgewell/resolve-uri\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/resolve-uri\";}s:27:\"@jridgewell/sourcemap-codec\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"@jridgewell/sourcemap-codec\";s:10:\"\0*\0version\";s:5:\"1.5.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/sourcemap-codec\";}s:25:\"@jridgewell/trace-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"@jridgewell/trace-mapping\";s:10:\"\0*\0version\";s:6:\"0.3.31\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/node_modules/@jridgewell/trace-mapping\";}s:19:\"@nodelib/fs.scandir\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"@nodelib/fs.scandir\";s:10:\"\0*\0version\";s:5:\"2.1.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.scandir\";}s:16:\"@nodelib/fs.stat\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@nodelib/fs.stat\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.stat\";}s:16:\"@nodelib/fs.walk\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"@nodelib/fs.walk\";s:10:\"\0*\0version\";s:5:\"1.2.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/@nodelib/fs.walk\";}s:31:\"@rollup/rollup-android-arm-eabi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-android-arm-eabi\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-android-arm-eabi\";}s:28:\"@rollup/rollup-android-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-android-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-android-arm64\";}s:27:\"@rollup/rollup-darwin-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"@rollup/rollup-darwin-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-darwin-arm64\";}s:25:\"@rollup/rollup-darwin-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"@rollup/rollup-darwin-x64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-darwin-x64\";}s:28:\"@rollup/rollup-freebsd-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-freebsd-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-freebsd-arm64\";}s:26:\"@rollup/rollup-freebsd-x64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"@rollup/rollup-freebsd-x64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-freebsd-x64\";}s:34:\"@rollup/rollup-linux-arm-gnueabihf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"@rollup/rollup-linux-arm-gnueabihf\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:80:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm-gnueabihf\";}s:35:\"@rollup/rollup-linux-arm-musleabihf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:35:\"@rollup/rollup-linux-arm-musleabihf\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:81:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm-musleabihf\";}s:30:\"@rollup/rollup-linux-arm64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-arm64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm64-gnu\";}s:31:\"@rollup/rollup-linux-arm64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-linux-arm64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-arm64-musl\";}s:32:\"@rollup/rollup-linux-loong64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-linux-loong64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-loong64-gnu\";}s:30:\"@rollup/rollup-linux-ppc64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-ppc64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-ppc64-gnu\";}s:32:\"@rollup/rollup-linux-riscv64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-linux-riscv64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-riscv64-gnu\";}s:33:\"@rollup/rollup-linux-riscv64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"@rollup/rollup-linux-riscv64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:79:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-riscv64-musl\";}s:30:\"@rollup/rollup-linux-s390x-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-linux-s390x-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-s390x-gnu\";}s:28:\"@rollup/rollup-linux-x64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-linux-x64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-x64-gnu\";}s:29:\"@rollup/rollup-linux-x64-musl\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"@rollup/rollup-linux-x64-musl\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-linux-x64-musl\";}s:32:\"@rollup/rollup-openharmony-arm64\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"@rollup/rollup-openharmony-arm64\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-openharmony-arm64\";}s:31:\"@rollup/rollup-win32-arm64-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"@rollup/rollup-win32-arm64-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-arm64-msvc\";}s:30:\"@rollup/rollup-win32-ia32-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"@rollup/rollup-win32-ia32-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-ia32-msvc\";}s:28:\"@rollup/rollup-win32-x64-gnu\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"@rollup/rollup-win32-x64-gnu\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-x64-gnu\";}s:29:\"@rollup/rollup-win32-x64-msvc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"@rollup/rollup-win32-x64-msvc\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"/Users/alirazamarchal/Herd/nexus/node_modules/@rollup/rollup-win32-x64-msvc\";}s:18:\"@tailwindcss/forms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@tailwindcss/forms\";s:10:\"\0*\0version\";s:6:\"0.5.11\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^0.5.11\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/@tailwindcss/forms\";}s:23:\"@tailwindcss/typography\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"@tailwindcss/typography\";s:10:\"\0*\0version\";s:6:\"0.5.20\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^0.5.20\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/@tailwindcss/typography\";}s:13:\"@types/estree\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"@types/estree\";s:10:\"\0*\0version\";s:5:\"1.0.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/@types/estree\";}s:10:\"agent-base\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"agent-base\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/agent-base\";}s:10:\"ansi-regex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"ansi-regex\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/ansi-regex\";}s:11:\"ansi-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ansi-styles\";s:10:\"\0*\0version\";s:5:\"4.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/ansi-styles\";}s:11:\"any-promise\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"any-promise\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/any-promise\";}s:8:\"anymatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"anymatch\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/anymatch\";}s:3:\"arg\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:3:\"arg\";s:10:\"\0*\0version\";s:5:\"5.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/node_modules/arg\";}s:8:\"asynckit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"asynckit\";s:10:\"\0*\0version\";s:5:\"0.4.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/asynckit\";}s:12:\"autoprefixer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"autoprefixer\";s:10:\"\0*\0version\";s:6:\"10.6.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^10.6.2\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/autoprefixer\";}s:5:\"axios\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"axios\";s:10:\"\0*\0version\";s:6:\"1.20.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^1.21.0\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/axios\";}s:24:\"baseline-browser-mapping\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"baseline-browser-mapping\";s:10:\"\0*\0version\";s:7:\"2.11.32\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"/Users/alirazamarchal/Herd/nexus/node_modules/baseline-browser-mapping\";}s:17:\"binary-extensions\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"binary-extensions\";s:10:\"\0*\0version\";s:5:\"2.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/binary-extensions\";}s:6:\"braces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"braces\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/braces\";}s:12:\"browserslist\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"browserslist\";s:10:\"\0*\0version\";s:6:\"4.29.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/browserslist\";}s:23:\"call-bind-apply-helpers\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"call-bind-apply-helpers\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/call-bind-apply-helpers\";}s:13:\"camelcase-css\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"camelcase-css\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/camelcase-css\";}s:12:\"caniuse-lite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"caniuse-lite\";s:10:\"\0*\0version\";s:12:\"1.0.30001819\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/caniuse-lite\";}s:5:\"chalk\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"chalk\";s:10:\"\0*\0version\";s:5:\"4.1.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/chalk\";}s:8:\"chokidar\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"chokidar\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/chokidar\";}s:5:\"cliui\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"cliui\";s:10:\"\0*\0version\";s:5:\"8.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/cliui\";}s:13:\"color-convert\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"color-convert\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/color-convert\";}s:10:\"color-name\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"color-name\";s:10:\"\0*\0version\";s:5:\"1.1.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/color-name\";}s:15:\"combined-stream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"combined-stream\";s:10:\"\0*\0version\";s:5:\"1.0.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/combined-stream\";}s:9:\"commander\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"commander\";s:10:\"\0*\0version\";s:5:\"4.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/commander\";}s:12:\"concurrently\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"concurrently\";s:10:\"\0*\0version\";s:5:\"9.2.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^9.2.5\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/concurrently\";}s:6:\"cssesc\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"cssesc\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/cssesc\";}s:5:\"debug\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"debug\";s:10:\"\0*\0version\";s:5:\"4.4.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/debug\";}s:14:\"delayed-stream\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"delayed-stream\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/delayed-stream\";}s:10:\"didyoumean\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"didyoumean\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/didyoumean\";}s:3:\"dlv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:3:\"dlv\";s:10:\"\0*\0version\";s:5:\"1.1.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"/Users/alirazamarchal/Herd/nexus/node_modules/dlv\";}s:12:\"dunder-proto\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dunder-proto\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/dunder-proto\";}s:20:\"electron-to-chromium\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"electron-to-chromium\";s:10:\"\0*\0version\";s:7:\"1.5.464\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/electron-to-chromium\";}s:11:\"emoji-regex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"emoji-regex\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/emoji-regex\";}s:18:\"es-define-property\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"es-define-property\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-define-property\";}s:9:\"es-errors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"es-errors\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-errors\";}s:15:\"es-object-atoms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"es-object-atoms\";s:10:\"\0*\0version\";s:5:\"1.1.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-object-atoms\";}s:18:\"es-set-tostringtag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"es-set-tostringtag\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"/Users/alirazamarchal/Herd/nexus/node_modules/es-set-tostringtag\";}s:7:\"esbuild\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"esbuild\";s:10:\"\0*\0version\";s:7:\"0.25.12\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/esbuild\";}s:8:\"escalade\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"escalade\";s:10:\"\0*\0version\";s:5:\"3.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/escalade\";}s:9:\"fast-glob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"fast-glob\";s:10:\"\0*\0version\";s:5:\"3.3.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/fast-glob\";}s:5:\"fastq\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"fastq\";s:10:\"\0*\0version\";s:6:\"1.19.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/fastq\";}s:10:\"fill-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"fill-range\";s:10:\"\0*\0version\";s:5:\"7.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/fill-range\";}s:16:\"follow-redirects\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"follow-redirects\";s:10:\"\0*\0version\";s:6:\"1.16.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"/Users/alirazamarchal/Herd/nexus/node_modules/follow-redirects\";}s:9:\"form-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"form-data\";s:10:\"\0*\0version\";s:5:\"4.0.6\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/form-data\";}s:11:\"fraction.js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"fraction.js\";s:10:\"\0*\0version\";s:5:\"5.3.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/fraction.js\";}s:8:\"fsevents\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"fsevents\";s:10:\"\0*\0version\";s:5:\"2.3.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/fsevents\";}s:13:\"function-bind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"function-bind\";s:10:\"\0*\0version\";s:5:\"1.1.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/function-bind\";}s:15:\"get-caller-file\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"get-caller-file\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-caller-file\";}s:13:\"get-intrinsic\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"get-intrinsic\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-intrinsic\";}s:9:\"get-proto\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"get-proto\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/get-proto\";}s:11:\"glob-parent\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"glob-parent\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/glob-parent\";}s:4:\"gopd\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"gopd\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/gopd\";}s:8:\"has-flag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"has-flag\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-flag\";}s:11:\"has-symbols\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"has-symbols\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-symbols\";}s:15:\"has-tostringtag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"has-tostringtag\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/has-tostringtag\";}s:6:\"hasown\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"hasown\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/hasown\";}s:17:\"https-proxy-agent\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"https-proxy-agent\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/https-proxy-agent\";}s:14:\"is-binary-path\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"is-binary-path\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-binary-path\";}s:14:\"is-core-module\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"is-core-module\";s:10:\"\0*\0version\";s:6:\"2.16.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-core-module\";}s:10:\"is-extglob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"is-extglob\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-extglob\";}s:23:\"is-fullwidth-code-point\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"is-fullwidth-code-point\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-fullwidth-code-point\";}s:7:\"is-glob\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"is-glob\";s:10:\"\0*\0version\";s:5:\"4.0.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-glob\";}s:9:\"is-number\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"is-number\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/is-number\";}s:4:\"jiti\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"jiti\";s:10:\"\0*\0version\";s:6:\"1.21.7\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/jiti\";}s:19:\"laravel-vite-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"laravel-vite-plugin\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.3.0\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/laravel-vite-plugin\";}s:9:\"lilconfig\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"lilconfig\";s:10:\"\0*\0version\";s:5:\"3.1.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/lilconfig\";}s:17:\"lines-and-columns\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"lines-and-columns\";s:10:\"\0*\0version\";s:5:\"1.2.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/lines-and-columns\";}s:15:\"math-intrinsics\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"math-intrinsics\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/math-intrinsics\";}s:6:\"merge2\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"merge2\";s:10:\"\0*\0version\";s:5:\"1.4.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/merge2\";}s:10:\"micromatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"micromatch\";s:10:\"\0*\0version\";s:5:\"4.0.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/micromatch\";}s:7:\"mime-db\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"mime-db\";s:10:\"\0*\0version\";s:6:\"1.52.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/mime-db\";}s:10:\"mime-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"mime-types\";s:10:\"\0*\0version\";s:6:\"2.1.35\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/mime-types\";}s:17:\"mini-svg-data-uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"mini-svg-data-uri\";s:10:\"\0*\0version\";s:5:\"1.4.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/mini-svg-data-uri\";}s:2:\"ms\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:2:\"ms\";s:10:\"\0*\0version\";s:5:\"2.1.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"/Users/alirazamarchal/Herd/nexus/node_modules/ms\";}s:2:\"mz\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:2:\"mz\";s:10:\"\0*\0version\";s:5:\"2.7.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"/Users/alirazamarchal/Herd/nexus/node_modules/mz\";}s:6:\"nanoid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"nanoid\";s:10:\"\0*\0version\";s:6:\"3.3.20\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/nanoid\";}s:13:\"node-releases\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"node-releases\";s:10:\"\0*\0version\";s:6:\"2.0.60\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/node-releases\";}s:14:\"normalize-path\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"normalize-path\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/normalize-path\";}s:13:\"object-assign\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"object-assign\";s:10:\"\0*\0version\";s:5:\"4.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/object-assign\";}s:11:\"object-hash\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"object-hash\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/object-hash\";}s:10:\"path-parse\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"path-parse\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/path-parse\";}s:10:\"picocolors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"picocolors\";s:10:\"\0*\0version\";s:5:\"1.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/picocolors\";}s:9:\"picomatch\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"picomatch\";s:10:\"\0*\0version\";s:5:\"2.3.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/picomatch\";}s:4:\"pify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"pify\";s:10:\"\0*\0version\";s:5:\"2.3.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/pify\";}s:7:\"pirates\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"pirates\";s:10:\"\0*\0version\";s:5:\"4.0.7\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/pirates\";}s:7:\"postcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"postcss\";s:10:\"\0*\0version\";s:6:\"8.5.31\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^8.5.32\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss\";}s:14:\"postcss-import\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"postcss-import\";s:10:\"\0*\0version\";s:6:\"15.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-import\";}s:10:\"postcss-js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"postcss-js\";s:10:\"\0*\0version\";s:5:\"4.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-js\";}s:19:\"postcss-load-config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"postcss-load-config\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-load-config\";}s:14:\"postcss-nested\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"postcss-nested\";s:10:\"\0*\0version\";s:5:\"6.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-nested\";}s:23:\"postcss-selector-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"postcss-selector-parser\";s:10:\"\0*\0version\";s:6:\"6.0.10\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-selector-parser\";}s:20:\"postcss-value-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"postcss-value-parser\";s:10:\"\0*\0version\";s:5:\"4.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/postcss-value-parser\";}s:14:\"proxy-from-env\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"proxy-from-env\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/proxy-from-env\";}s:15:\"queue-microtask\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"queue-microtask\";s:10:\"\0*\0version\";s:5:\"1.2.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"/Users/alirazamarchal/Herd/nexus/node_modules/queue-microtask\";}s:10:\"read-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"read-cache\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/read-cache\";}s:8:\"readdirp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:8:\"readdirp\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"/Users/alirazamarchal/Herd/nexus/node_modules/readdirp\";}s:17:\"require-directory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"require-directory\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"/Users/alirazamarchal/Herd/nexus/node_modules/require-directory\";}s:7:\"resolve\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"resolve\";s:10:\"\0*\0version\";s:7:\"1.22.11\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/resolve\";}s:7:\"reusify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"reusify\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/reusify\";}s:6:\"rollup\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:6:\"rollup\";s:10:\"\0*\0version\";s:6:\"4.53.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"/Users/alirazamarchal/Herd/nexus/node_modules/rollup\";}s:12:\"run-parallel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"run-parallel\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/run-parallel\";}s:4:\"rxjs\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"rxjs\";s:10:\"\0*\0version\";s:5:\"7.8.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/rxjs\";}s:11:\"shell-quote\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"shell-quote\";s:10:\"\0*\0version\";s:6:\"1.12.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/shell-quote\";}s:13:\"source-map-js\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"source-map-js\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"/Users/alirazamarchal/Herd/nexus/node_modules/source-map-js\";}s:12:\"string-width\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"string-width\";s:10:\"\0*\0version\";s:5:\"4.2.3\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/string-width\";}s:10:\"strip-ansi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"strip-ansi\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/strip-ansi\";}s:7:\"sucrase\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"sucrase\";s:10:\"\0*\0version\";s:6:\"3.35.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/sucrase\";}s:14:\"supports-color\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"supports-color\";s:10:\"\0*\0version\";s:5:\"8.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/supports-color\";}s:31:\"supports-preserve-symlinks-flag\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"supports-preserve-symlinks-flag\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"/Users/alirazamarchal/Herd/nexus/node_modules/supports-preserve-symlinks-flag\";}s:11:\"tailwindcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:6:\"3.4.19\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^3.4.19\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/tailwindcss\";}s:7:\"thenify\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"thenify\";s:10:\"\0*\0version\";s:5:\"3.3.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"/Users/alirazamarchal/Herd/nexus/node_modules/thenify\";}s:11:\"thenify-all\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"thenify-all\";s:10:\"\0*\0version\";s:5:\"1.6.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"/Users/alirazamarchal/Herd/nexus/node_modules/thenify-all\";}s:10:\"tinyglobby\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"tinyglobby\";s:10:\"\0*\0version\";s:6:\"0.2.15\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"/Users/alirazamarchal/Herd/nexus/node_modules/tinyglobby\";}s:14:\"to-regex-range\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"to-regex-range\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/to-regex-range\";}s:9:\"tree-kill\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"tree-kill\";s:10:\"\0*\0version\";s:5:\"1.2.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/tree-kill\";}s:20:\"ts-interface-checker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"ts-interface-checker\";s:10:\"\0*\0version\";s:6:\"0.1.13\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"/Users/alirazamarchal/Herd/nexus/node_modules/ts-interface-checker\";}s:5:\"tslib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"tslib\";s:10:\"\0*\0version\";s:5:\"2.8.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/tslib\";}s:22:\"update-browserslist-db\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"update-browserslist-db\";s:10:\"\0*\0version\";s:5:\"1.3.4\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"/Users/alirazamarchal/Herd/nexus/node_modules/update-browserslist-db\";}s:14:\"util-deprecate\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"util-deprecate\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"/Users/alirazamarchal/Herd/nexus/node_modules/util-deprecate\";}s:4:\"vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"vite\";s:10:\"\0*\0version\";s:5:\"6.4.5\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^6.4.5\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/vite\";}s:23:\"vite-plugin-full-reload\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"vite-plugin-full-reload\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"/Users/alirazamarchal/Herd/nexus/node_modules/vite-plugin-full-reload\";}s:9:\"wrap-ansi\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"wrap-ansi\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"/Users/alirazamarchal/Herd/nexus/node_modules/wrap-ansi\";}s:4:\"y18n\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"y18n\";s:10:\"\0*\0version\";s:5:\"5.0.8\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/y18n\";}s:5:\"yargs\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:5:\"yargs\";s:10:\"\0*\0version\";s:6:\"17.7.2\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"/Users/alirazamarchal/Herd/nexus/node_modules/yargs\";}s:12:\"yargs-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"yargs-parser\";s:10:\"\0*\0version\";s:6:\"21.1.1\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"/Users/alirazamarchal/Herd/nexus/node_modules/yargs-parser\";}s:4:\"fdir\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"fdir\";s:10:\"\0*\0version\";s:5:\"6.5.0\";s:9:\"\0*\0source\";r:1534;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"/Users/alirazamarchal/Herd/nexus/node_modules/fdir\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:184:{i:0;r:1531;i:1;r:1539;i:2;r:1547;i:3;r:1555;i:4;r:1563;i:5;r:1571;i:6;r:1579;i:7;r:1587;i:8;r:1595;i:9;r:1603;i:10;r:1611;i:11;r:1619;i:12;r:1627;i:13;r:1635;i:14;r:1643;i:15;r:1651;i:16;r:1659;i:17;r:1667;i:18;r:1675;i:19;r:1683;i:20;r:1691;i:21;r:1699;i:22;r:1707;i:23;r:1715;i:24;r:1723;i:25;r:1731;i:26;r:1739;i:27;r:1747;i:28;r:1755;i:29;r:1763;i:30;r:1771;i:31;r:1779;i:32;r:1787;i:33;r:1795;i:34;r:1803;i:35;r:1811;i:36;r:1819;i:37;r:1827;i:38;r:1835;i:39;r:1843;i:40;r:1851;i:41;r:1859;i:42;r:1867;i:43;r:1875;i:44;r:1883;i:45;r:1891;i:46;r:1899;i:47;r:1907;i:48;r:1915;i:49;r:1923;i:50;r:1931;i:51;r:1939;i:52;r:1947;i:53;r:1955;i:54;r:1963;i:55;r:1971;i:56;r:1979;i:57;r:1987;i:58;r:1995;i:59;r:2003;i:60;r:2011;i:61;r:2019;i:62;r:2027;i:63;r:2035;i:64;r:2043;i:65;r:2051;i:66;r:2059;i:67;r:2067;i:68;r:2075;i:69;r:2083;i:70;r:2091;i:71;r:2099;i:72;r:2107;i:73;r:2115;i:74;r:2123;i:75;r:2131;i:76;r:2139;i:77;r:2147;i:78;r:2155;i:79;r:2163;i:80;r:2171;i:81;r:2179;i:82;r:2187;i:83;r:2195;i:84;r:2203;i:85;r:2211;i:86;r:2219;i:87;r:2227;i:88;r:2235;i:89;r:2243;i:90;r:2251;i:91;r:2259;i:92;r:2267;i:93;r:2275;i:94;r:2283;i:95;r:2291;i:96;r:2299;i:97;r:2307;i:98;r:2315;i:99;r:2323;i:100;r:2331;i:101;r:2339;i:102;r:2347;i:103;r:2355;i:104;r:2363;i:105;r:2371;i:106;r:2379;i:107;r:2387;i:108;r:2395;i:109;r:2403;i:110;r:2411;i:111;r:2419;i:112;r:2427;i:113;r:2435;i:114;r:2443;i:115;r:2451;i:116;r:2459;i:117;r:2467;i:118;r:2475;i:119;r:2483;i:120;r:2491;i:121;r:2499;i:122;r:2507;i:123;r:2515;i:124;r:2523;i:125;r:2531;i:126;r:2539;i:127;r:2547;i:128;r:2555;i:129;r:2563;i:130;r:2571;i:131;r:2579;i:132;r:2587;i:133;r:2595;i:134;r:2603;i:135;r:2611;i:136;r:2619;i:137;r:2627;i:138;r:2635;i:139;r:2643;i:140;r:2651;i:141;r:2659;i:142;r:2667;i:143;r:2675;i:144;r:2683;i:145;r:2691;i:146;r:2699;i:147;r:2707;i:148;r:2715;i:149;r:2723;i:150;r:2731;i:151;r:2739;i:152;r:2747;i:153;r:2755;i:154;r:2763;i:155;r:2771;i:156;r:2779;i:157;r:2787;i:158;r:2795;i:159;r:2803;i:160;r:2811;i:161;r:2819;i:162;r:2827;i:163;r:2835;i:164;r:2843;i:165;r:2851;i:166;r:2859;i:167;r:2867;i:168;r:2875;i:169;r:2883;i:170;r:2891;i:171;r:2899;i:172;r:2907;i:173;r:2915;i:174;r:2923;i:175;r:2931;i:176;r:2939;i:177;r:2947;i:178;r:2955;i:179;r:2963;i:180;r:2971;i:181;r:2979;i:182;r:2987;i:183;r:2995;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:17:\"\0*\0packageManager\";E:41:\"Laravel\\Roster\\Enums\\JsPackageManager:Npm\";}s:6:\"stacks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:35:\"Laravel\\Roster\\Enums\\Stack:Livewire\";}}s:21:\"browserTestFrameworks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:9:\"frontends\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:6:\"agents\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:4:{i:0;E:37:\"Laravel\\Roster\\Enums\\Agent:ClaudeCode\";i:1;E:32:\"Laravel\\Roster\\Enums\\Agent:Codex\";i:2;E:34:\"Laravel\\Roster\\Enums\\Agent:Copilot\";i:3;E:33:\"Laravel\\Roster\\Enums\\Agent:Gemini\";}}s:7:\"editors\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}}', 1793206188);

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
('01a18188-1139-73e9-9fd2-950e96963df3', 'BAJK_DEBIT', 'BAJK Debit Card (Enhanced ATM & POS limits)', 'بی اے جے کے ڈیبٹ کارڈ', 19, 'individual', 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1139-73e9-9fd2-950e9735b2d7', 'BAJK_SILVER', 'BAJK Silver Debit Card', 'بی اے جے کے سلور ڈیبٹ کارڈ', 19, 'individual', 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-113a-73ab-8dd3-c256da8fae71', 'BAJK_GOLD', 'BAJK Gold Debit Card', 'بی اے جے کے گولڈ ڈیبٹ کارڈ', 19, 'individual', 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-115a-7091-b2dc-6b5dc7834f93', 'OWNER', 'Owner (direct or indirect)', 'مالک (بلاواسطہ یا بالواسطہ)', 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115a-7091-b2dc-6b5dc83c6b4d', 'CONTROL_OTHER_MEANS', 'Controlling Person by other means', 'کنٹرول کرنے والا دیگر ذرائع سے', 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115b-7046-acc7-485754331712', 'SENIOR_MGMT', 'Senior Management Official', 'سینئر مینجمنٹ آفیشل', 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115b-7046-acc7-4857543afd5b', 'BENEFICIARY', 'Beneficiary', 'بینیفشری', 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115c-70c6-bcc2-531ca3e0fd84', 'SETTLOR', 'Settlor', 'سیٹلر', 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115c-70c6-bcc2-531ca4183283', 'TRUSTEE', 'Trustee', 'ٹرسٹی', 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115d-72e6-a13d-9fdd62ec15b6', 'PROTECTOR', 'Protector', 'پروٹیکٹر', 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115d-72e6-a13d-9fdd6300862b', 'SETTLOR_EQUIVALENT', 'Settlor-equivalent', 'سیٹلر مساوی', 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115d-72e6-a13d-9fdd6323e66f', 'TRUSTEE_EQUIVALENT', 'Trustee-equivalent', 'ٹرسٹی مساوی', 9, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115e-7106-a211-9807e5f4d193', 'PROTECTOR_EQUIVALENT', 'Protector-equivalent', 'پروٹیکٹر مساوی', 10, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115e-7106-a211-9807e6111000', 'BENEFICIARY_EQUIVALENT', 'Beneficiary-equivalent', 'بینیفشری مساوی', 11, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-115f-72be-9e5b-26b96d64fb93', 'OTHER_EQUIVALENT', 'Other-equivalent', 'دیگر مساوی', 12, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-1152-7187-9679-71886b9f67bc', 'FINANCIAL_INSTITUTION', 'Financial Institution', 'مالیاتی ادارہ', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1153-7303-9c3a-ce7e51a65ea9', 'LIMITED_COMPANY', 'Limited Company', 'لمیٹڈ کمپنی', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1153-7303-9c3a-ce7e51a6bb08', 'PROPRIETORSHIP', 'Proprietorship', 'پروپرائٹرشپ', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1154-7061-9ca3-e7ef324372e6', 'GOVT_ENTITY', 'Govt. Entity', 'سرکاری ادارہ', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1154-7061-9ca3-e7ef32697904', 'NPO', 'NPO', 'این پی او', 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1155-7289-b026-66154a6cb2e0', 'COUNTER_OTHERS', 'Others', 'دیگر', 1, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-10df-71d9-b7bb-6beb9523f726', 'PK', 'PAK', 'Pakistan', 'پاکستان', '+92', 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e0-710b-914c-9a10092bfa30', 'US', 'USA', 'United States of America', 'امریکہ', '+1', 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e1-7075-b31f-c59bae5719a8', 'GB', 'GBR', 'United Kingdom', 'برطانیہ', '+44', 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e2-7204-8eee-2f346938b3c2', 'AE', 'ARE', 'United Arab Emirates', 'متحدہ عرب امارات', '+971', 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e3-70a0-aefd-7b26570d6feb', 'SA', 'SAU', 'Saudi Arabia', 'سعودی عرب', '+966', 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-1155-7289-b026-66154af882b5', 'INV_ENTITY_NP_COUNTRY', 'a', 'Investment Entity located in a Non-Participating Country and managed by another Financial Institution', NULL, 0, 1, 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1156-7030-a0db-3c4597fa9502', 'OTHER_INV_ENTITY', 'b', 'Other Investment Entity', NULL, 1, 0, 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1156-7030-a0db-3c4598d966b6', 'FI_DEPOSITORY', 'c', 'Financial Institution - Depository Institution, Custodial Institution or Specified Insurance Company', NULL, 1, 0, 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1157-7158-9e11-166d50e06e1a', 'ACTIVE_NFE_LISTED', 'd', 'An Active NFE, stock of which is regularly traded on an established securities market, or a corporation which is a related entity of such a corporation', NULL, 0, 0, 1, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1157-7158-9e11-166d51003641', 'GOVT_ENTITY_CENTRAL_BANK', 'e', 'Government Entity or Central Bank', NULL, 0, 0, 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1158-72fc-af1a-978a091054de', 'INTERNATIONAL_ORG', 'f', 'International Organization', NULL, 0, 0, 0, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1158-72fc-af1a-978a094a7089', 'NGO_NPO_STARTUP_NFE', 'g', 'NGO/NPO or a startup NFE or Non Profit NFE', NULL, 0, 0, 0, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1159-73f5-baf1-357ba39e917d', 'ACTIVE_NFE_INCOME', 'h', 'Active NFE - having 50% and above income derived from core business activity', NULL, 0, 0, 0, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1159-73f5-baf1-357ba4758831', 'PASSIVE_NFE', 'i', 'Passive NFE', NULL, 0, 1, 0, 9, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-10e5-7025-b95c-2d764906f228', 'PKR', 'Pakistani Rupee', 'Rs', 1, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e5-7025-b95c-2d764992be7a', 'USD', 'US Dollar', '$', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e6-73dc-b26c-aeb349387132', 'GBP', 'Great Britain Pound', '£', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e7-72d8-9290-18b8c1bc7c20', 'EUR', 'Euro', '€', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e8-72e1-85d5-cbfaa0d27f25', 'AED', 'UAE Dirham', 'د.إ', 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e8-72e1-85d5-cbfaa15a8f2d', 'SAR', 'Saudi Riyal', '﷼', 0, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10e9-71ff-b3c3-816adb2d6aae', 'JPY', 'Japanese Yen', '¥', 0, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-10ec-703b-a4fb-0ad6c37a6a23', 'INDIVIDUAL', 'Individual', 'انفرادی', 'individual', 1, 0, 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10ed-7079-aac8-a3cd97843b44', 'SOLE_PROP', 'Sole Proprietorship', 'سول پروپرائٹر شپ', 'individual', 1, 1, 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10ee-71b8-95ac-775178e00686', 'JOINT', 'Joint', 'مشترکہ', 'individual', 1, 0, 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10ef-70e9-8ee5-70f3af3d422a', 'IND_OTHERS', 'Others', 'دیگر', 'individual', 1, 0, 1, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10ef-70e9-8ee5-70f3affb90f2', 'PARTNERSHIP', 'Partnership (Registered/Unregistered)', 'شراکت (رجسٹرڈ/غیر رجسٹرڈ)', 'entity', 0, 1, 0, 10, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f0-732f-bfcb-a7528e93b289', 'LIMITED_CO', 'Limited Companies', 'لمیٹڈ کمپنیاں', 'entity', 0, 1, 0, 11, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f1-7334-b18e-d6ce3f33ee5b', 'PUBLIC_LISTED', 'Public Listed', 'پبلک لسٹڈ', 'entity', 0, 1, 0, 12, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f2-709b-aeaf-23ead4e139ff', 'PUBLIC_UNLISTED', 'Public Unlisted', 'پبلک ان لسٹڈ', 'entity', 0, 1, 0, 13, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f2-709b-aeaf-23ead4e6e07e', 'PRIVATE', 'Private', 'پرائیویٹ', 'entity', 0, 1, 0, 14, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f3-72e9-8bf9-804cb542c8de', 'FOREIGN_MISSION', 'Foreign Missions/Diplomates', 'غیر ملکی سفارت خانے/سفارتکار', 'entity', 0, 1, 0, 15, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f4-7114-948e-87db2936c967', 'GOVT_INSTITUTION', 'Government Institution (Federal/Provisional/Local)', 'سرکاری ادارہ (وفاقی/صوبائی/مقامی)', 'entity', 0, 1, 0, 16, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f5-70ff-9b4e-9f7d280ef8a8', 'SOCIETY', 'Societies', 'سوسائٹیز', 'entity', 0, 1, 0, 17, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f5-70ff-9b4e-9f7d2816a431', 'CLUB', 'Club', 'کلب', 'entity', 0, 1, 0, 18, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f6-70b3-8be8-932178843106', 'TRUST', 'Trust', 'ٹرسٹ', 'entity', 0, 1, 0, 19, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f7-709f-91e3-1310433d8cbe', 'ASSOCIATION', 'Associations', 'ایسوسی ایشن', 'entity', 0, 1, 0, 20, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f7-709f-91e3-1310434dbf1b', 'LOCAL_ZAKAT', 'Local Zakat Committee', 'لوکل زکوٰۃ کمیٹی', 'entity', 0, 1, 0, 21, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f8-7209-95af-32d8fc210bcd', 'MONEY_EXCHANGE', 'Business - Money Exchange Company', 'منی ایکسچینج کمپنی', 'entity', 0, 1, 0, 22, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 'FOREIGN_BRANCH', 'Branch Office or Liaison Office of Foreign Companies', 'غیر ملکی کمپنیوں کا برانچ/رابطہ دفتر', 'entity', 0, 1, 0, 23, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10fa-7046-b993-82d1e3bdf508', 'NGO_NPO', 'NGO/NPO\'s Charities', 'این جی او/این پی او/خیراتی ادارے', 'entity', 0, 1, 0, 24, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10fa-7046-b993-82d1e4958b1f', 'AGENT', 'Agents Accounts', 'ایجنٹ اکاؤنٹ', 'entity', 0, 1, 0, 25, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10fb-70b7-ba2a-204bedebc4f7', 'EXECUTOR', 'Executors & Administrators', 'مختار کنندہ اور منتظم', 'entity', 0, 1, 0, 26, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10fc-7147-8b2e-d84668a6740e', 'ENT_OTHERS', 'Others', 'دیگر', 'entity', 0, 1, 1, 27, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
  `date_of_birth` date DEFAULT NULL,
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
('01a18188-117a-7272-a886-c1ab4ebc1c80', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117b-71d1-b724-b8eaebd6e47a', '01a18188-1162-72f7-9dde-88061037382e', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'profession = HOUSEWIFE', NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117b-71d1-b724-b8eaec67c301', '01a18188-1163-72c0-8d8c-ae50f44b61e6', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'profession = AGRICULTURE', NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117c-7159-ac56-d09ce7a21411', '01a18188-1162-72f7-9dde-880610f0f7ee', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'profession IN (HOUSEWIFE, PROF_STUDENT, UNEMPLOYED)', NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117c-7159-ac56-d09ce835e952', '01a18188-1163-72c0-8d8c-ae50f3657b76', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'profession IN (GOVT_SERVICE, PRIVATE_SERVICE)', NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117d-706a-a7c0-a122073b223d', '01a18188-1164-7181-99ea-fcf66be56d2a', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'income_source = STOCK_INVESTMENT', NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117d-706a-a7c0-a12207e947ad', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'profession = SELF_EMPLOYED', NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117d-706a-a7c0-a122081c81af', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117e-712a-843c-d255f22aacf6', '01a18188-1170-71b3-a73d-99c301fde1a3', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'signature shaky/immature or photo account', NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117e-712a-843c-d255f2db374f', '01a18188-116f-7146-a7e6-176c4d899f6c', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'foreigner residing in Pakistan', NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-117f-7316-a0bb-d37723a9e2d6', '01a18188-1170-71b3-a73d-99c3014d26e3', '01a18188-10ec-703b-a4fb-0ad6c37a6a23', 0, 'signature other than English/Urdu', NULL, 11, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1180-7232-9302-ae50aaa5c1df', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10ee-71b8-95ac-775178e00686', 1, 'CDD on all joint account holders as if each were an individual customer', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1180-7232-9302-ae50ab6114b6', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10ee-71b8-95ac-775178e00686', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1181-7036-b576-fc90b891c978', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10ed-7079-aac8-a3cd97843b44', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1181-7036-b576-fc90b9772baf', '01a18188-1165-7393-9a7e-7fe7cffd2fd1', '01a18188-10ed-7079-aac8-a3cd97843b44', 1, 'registered concerns', NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1182-72bb-bd3d-f0b5346a72da', '01a18188-1165-7393-9a7e-7fe7d04b5347', '01a18188-10ed-7079-aac8-a3cd97843b44', 0, 'wherever applicable', NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1182-72bb-bd3d-f0b53515f88b', '01a18188-1166-7359-8102-32edc60bbe09', '01a18188-10ed-7079-aac8-a3cd97843b44', 0, 'wherever applicable', NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1183-7285-9639-a3dc81c6baf1', '01a18188-1166-7359-8102-32edc62a289d', '01a18188-10ed-7079-aac8-a3cd97843b44', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1183-7285-9639-a3dc81cba495', '01a18188-1167-7295-9afd-2325c26c5f3e', '01a18188-10ed-7079-aac8-a3cd97843b44', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1183-7285-9639-a3dc82720c06', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10ed-7079-aac8-a3cd97843b44', 1, NULL, NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1184-72ce-864e-f4cd0ceae088', '01a18188-1167-7295-9afd-2325c36655ac', '01a18188-10ed-7079-aac8-a3cd97843b44', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1184-72ce-864e-f4cd0d9486e8', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10ed-7079-aac8-a3cd97843b44', 1, NULL, NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1185-7233-956f-952d1faf19ec', '01a18188-1168-71fe-b402-7b2e0686292a', '01a18188-10ed-7079-aac8-a3cd97843b44', 0, 'where applicable', NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1186-73bd-b921-1dab6f998f94', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, 'All partners and authorized signatories', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1186-73bd-b921-1dab6fe5406f', '01a18188-1171-725b-bc57-340261f45bdd', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1187-7111-83aa-5d2d870861c4', '01a18188-1171-725b-bc57-34026295fa10', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, 'Unregistered partnership must be stated on the form', NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1187-7111-83aa-5d2d870ca17a', '01a18188-1172-729d-bdde-948599d61c5e', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1188-73fc-a212-694cd0d978a5', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1188-73fc-a212-694cd12a3709', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1188-73fc-a212-694cd20df9d2', '01a18188-116c-7248-ba33-27d6abc56fbd', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, 'Source of fund / proof of income of the UBO', NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1189-73c5-bba3-053f1dc9090a', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1189-73c5-bba3-053f1e1cd86c', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 1, 'Company rubber stamp affixed with partner sign', NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118a-73e5-8afd-f9fa2e45efc5', '01a18188-1172-729d-bdde-94859aae6fb5', '01a18188-10ef-70e9-8ee5-70f3affb90f2', 0, 'Where applicable', NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118a-73e5-8afd-f9fa2e5110e6', '01a18188-1168-71fe-b402-7b2e0744bcb9', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118b-731d-996e-6d6adac5caec', '01a18188-1168-71fe-b402-7b2e06bf46bb', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118b-731d-996e-6d6adaea9578', '01a18188-1174-7349-9cee-e115d083526f', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118c-72a6-9326-1e8086bdf314', '01a18188-1174-7349-9cee-e115d0c2c94e', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, 'Wherever applicable', NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118c-72a6-9326-1e8087baae61', '01a18188-1175-7152-82f2-6578c75eab6e', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118d-7211-b7f4-448820dde61f', '01a18188-1175-7152-82f2-6578c7ae0ba6', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118d-7211-b7f4-4488212efea1', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118e-7315-aa7b-9774c11fda0a', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118e-7315-aa7b-9774c1287680', '01a18188-1175-7152-82f2-6578c7e672a6', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118f-72ae-872c-698badfaf8d3', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, 'With company secretary signature', NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-118f-72ae-872c-698baef2d067', '01a18188-116c-7248-ba33-27d6abc56fbd', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1190-7001-b3f0-616f7980ae00', '01a18188-1176-7000-999e-a6e503d4b9d7', '01a18188-10f0-732f-bfcb-a7528e93b289', 0, NULL, NULL, 12, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1190-7001-b3f0-616f7a5280e8', '01a18188-1176-7000-999e-a6e50459b74b', '01a18188-10f0-732f-bfcb-a7528e93b289', 0, 'Where applicable', NULL, 13, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1190-7001-b3f0-616f7a8db050', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, NULL, NULL, 14, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1191-72fe-a00f-4bbe954f3887', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10f0-732f-bfcb-a7528e93b289', 1, NULL, NULL, 15, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1191-72fe-a00f-4bbe95ae1707', '01a18188-1172-729d-bdde-94859aae6fb5', '01a18188-10f0-732f-bfcb-a7528e93b289', 0, 'Where applicable', NULL, 16, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1192-7218-8a17-37675a014ffa', '01a18188-1177-706e-83c2-665e90da77b3', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, 'Newly registered branch or liaison office', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1193-70be-ad4e-c319c720062d', '01a18188-1177-706e-83c2-665e91d191d8', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, 'Already registered foreign company', NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1193-70be-ad4e-c319c81ca85e', '01a18188-1178-7381-a727-b55f130a8dfd', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1194-7068-b253-136c4b2382be', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, 'All signatories of the account', NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1194-7068-b253-136c4be17aba', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1195-703c-9c3f-4b223fe03512', '01a18188-1175-7152-82f2-6578c75eab6e', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1195-703c-9c3f-4b224009920f', '01a18188-1178-7381-a727-b55f13f26905', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, NULL, NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1195-703c-9c3f-4b22406aa69c', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, 'With company secretary signature', NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1196-71bc-af9a-6c0805940a89', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, NULL, NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1196-71bc-af9a-6c0805f2ebe7', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10f9-7173-9c63-2fb9c0ecd3b9', 1, NULL, NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1197-72d3-940a-0b8d1b8d1ec9', '01a18188-1179-7383-8fc7-eeef4f6b1d12', '01a18188-10f3-72e9-8bf9-804cb542c8de', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1197-72d3-940a-0b8d1c4617da', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f3-72e9-8bf9-804cb542c8de', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1198-7001-831d-9ab0004b52e0', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f3-72e9-8bf9-804cb542c8de', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1199-708e-b268-b45c346aeb2e', '01a18188-1178-7381-a727-b55f140a6fb1', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1199-708e-b268-b45c3534bb29', '01a18188-1168-71fe-b402-7b2e06bf46bb', '01a18188-10f6-70b3-8be8-932178843106', 1, 'By-laws / Rules and Regulations', NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119a-7038-ac2c-58ae9c5c7a72', '01a18188-1168-71fe-b402-7b2e0744bcb9', '01a18188-10f6-70b3-8be8-932178843106', 1, 'Resolution of the Governing body / Board of Trustees / Executive Committee', NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119a-7038-ac2c-58ae9cfa52b3', '01a18188-1169-7115-a699-a54ffc6f3d93', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119a-7038-ac2c-58ae9d6d1625', '01a18188-1169-7115-a699-a54ffcdfec02', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119b-7206-9b8a-4153c11b1bad', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119b-7206-9b8a-4153c1d2b0b0', '01a18188-116a-73e1-a829-862828537a69', '01a18188-10f6-70b3-8be8-932178843106', 0, NULL, NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119c-703d-a10c-51fd0e75d111', '01a18188-116b-7251-9da7-0fa91e1b762b', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119c-703d-a10c-51fd0f0674bc', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119d-7142-882f-f9fbac0790ac', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119d-7142-882f-f9fbacccac40', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 11, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119e-70a6-ae12-79fff9d2c6c1', '01a18188-116c-7248-ba33-27d6abc56fbd', '01a18188-10f6-70b3-8be8-932178843106', 1, NULL, NULL, 12, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119e-70a6-ae12-79fffa61948a', '01a18188-116c-7248-ba33-27d6ac614f0e', '01a18188-10f4-7114-948e-87db2936c967', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119f-7221-86cf-daa30ea67b0f', '01a18188-116d-7364-992f-e4e4e29d91d5', '01a18188-10f4-7114-948e-87db2936c967', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-119f-7221-86cf-daa30f13a601', '01a18188-116d-7364-992f-e4e4e2c8431d', '01a18188-10f4-7114-948e-87db2936c967', 0, 'Armed Forces / allied accounts', NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a0-706d-83c4-12eea8d43a17', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f4-7114-948e-87db2936c967', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a0-706d-83c4-12eea9438125', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f4-7114-948e-87db2936c967', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a1-721c-a114-6892ea033270', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f4-7114-948e-87db2936c967', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a1-721c-a114-6892ea9904d9', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10f4-7114-948e-87db2936c967', 1, NULL, NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a2-71ef-8db3-10b93995db68', '01a18188-1165-7393-9a7e-7fe7cffd2fd1', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a2-71ef-8db3-10b939a5c578', '01a18188-1168-71fe-b402-7b2e06bf46bb', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a3-70cd-a284-9f6d34dddded', '01a18188-1168-71fe-b402-7b2e0744bcb9', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a3-70cd-a284-9f6d35be2f75', '01a18188-1169-7115-a699-a54ffcdfec02', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a4-73f2-b002-4286a544ed5b', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a4-73f2-b002-4286a63bdfc9', '01a18188-116a-73e1-a829-862828537a69', '01a18188-10fa-7046-b993-82d1e3bdf508', 0, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a5-733d-a084-d3ffc127d0e8', '01a18188-116a-73e1-a829-8628285e8718', '01a18188-10fa-7046-b993-82d1e3bdf508', 0, 'International NGO receiving funds from abroad', NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a5-733d-a084-d3ffc1feeaa1', '01a18188-116b-7251-9da7-0fa91e1b762b', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a6-709f-a911-f29468234fff', '01a18188-1169-7115-a699-a54ffc6f3d93', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a6-709f-a911-f29468d3fcb6', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a7-72e2-9466-8c16bf2b006c', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 11, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a7-72e2-9466-8c16bf5cca62', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 12, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a7-72e2-9466-8c16bfdb8ada', '01a18188-116f-7146-a7e6-176c4c560a5d', '01a18188-10fa-7046-b993-82d1e3bdf508', 0, 'wherever applicable', NULL, 13, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a8-713e-865e-83c034d8eec1', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 14, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a8-713e-865e-83c035309496', '01a18188-116c-7248-ba33-27d6abc56fbd', '01a18188-10fa-7046-b993-82d1e3bdf508', 1, NULL, NULL, 15, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11a9-72d6-9cfa-d5075cf895d4', '01a18188-116e-709e-9922-24079a70af6b', '01a18188-10fa-7046-b993-82d1e4958b1f', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11aa-70b7-be42-2fa31bb4e969', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10fa-7046-b993-82d1e4958b1f', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11aa-70b7-be42-2fa31c4d912d', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10fa-7046-b993-82d1e4958b1f', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ab-728f-aba2-12bb5bf98214', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10fa-7046-b993-82d1e4958b1f', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ab-728f-aba2-12bb5cdb0ff1', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10fa-7046-b993-82d1e4958b1f', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ac-71fa-beb1-827b521d76a9', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10fb-70b7-ba2a-204bedebc4f7', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ac-71fa-beb1-827b52f28c4b', '01a18188-116e-709e-9922-24079a73a5ee', '01a18188-10fb-70b7-ba2a-204bedebc4f7', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ad-70aa-992e-68b1677beac4', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10fb-70b7-ba2a-204bedebc4f7', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ad-70aa-992e-68b167b71827', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10fb-70b7-ba2a-204bedebc4f7', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ae-72a5-a133-ef8777ea99f9', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10fb-70b7-ba2a-204bedebc4f7', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11af-734a-ae59-e6ff0106d2e3', '01a18188-1169-7115-a699-a54ffc6f3d93', '01a18188-10f5-70ff-9b4e-9f7d2816a431', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11af-734a-ae59-e6ff01b7cb3b', '01a18188-1169-7115-a699-a54ffcdfec02', '01a18188-10f5-70ff-9b4e-9f7d2816a431', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b0-732c-a7d6-abebb54886cc', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f5-70ff-9b4e-9f7d2816a431', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b0-732c-a7d6-abebb5e99977', '01a18188-116a-73e1-a829-862828537a69', '01a18188-10f5-70ff-9b4e-9f7d2816a431', 0, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b0-732c-a7d6-abebb66b649b', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f5-70ff-9b4e-9f7d2816a431', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b1-73fb-8f65-9239a6bfefdb', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f5-70ff-9b4e-9f7d2816a431', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b1-73fb-8f65-9239a6ea1c4a', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f5-70ff-9b4e-9f7d2816a431', 1, NULL, NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b2-72b6-b17b-d3a2eab0b183', '01a18188-1169-7115-a699-a54ffc6f3d93', '01a18188-10f5-70ff-9b4e-9f7d280ef8a8', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b3-7248-b7ff-55b326c53dee', '01a18188-1169-7115-a699-a54ffcdfec02', '01a18188-10f5-70ff-9b4e-9f7d280ef8a8', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b3-7248-b7ff-55b3271b2cf5', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f5-70ff-9b4e-9f7d280ef8a8', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b4-7304-8ed5-95f057dab9eb', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f5-70ff-9b4e-9f7d280ef8a8', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b4-7304-8ed5-95f058b4291e', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f5-70ff-9b4e-9f7d280ef8a8', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b5-7399-bbbe-8ff714cb9aab', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f5-70ff-9b4e-9f7d280ef8a8', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b5-7399-bbbe-8ff71542712f', '01a18188-1169-7115-a699-a54ffc6f3d93', '01a18188-10f7-709f-91e3-1310433d8cbe', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b6-73f1-8eb4-a0cb9cef8474', '01a18188-1169-7115-a699-a54ffcdfec02', '01a18188-10f7-709f-91e3-1310433d8cbe', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b6-73f1-8eb4-a0cb9cf73b42', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f7-709f-91e3-1310433d8cbe', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b7-7078-820f-4599872521df', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f7-709f-91e3-1310433d8cbe', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b7-7078-820f-459987b58cac', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f7-709f-91e3-1310433d8cbe', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b8-729d-8966-36088be459b0', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f7-709f-91e3-1310433d8cbe', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b8-729d-8966-36088caccb71', '01a18188-1168-71fe-b402-7b2e0744bcb9', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b9-73eb-90ae-06e97a0e72ef', '01a18188-1168-71fe-b402-7b2e06bf46bb', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11b9-73eb-90ae-06e97a67178f', '01a18188-1174-7349-9cee-e115d083526f', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ba-70d6-9923-7e78c7aa3827', '01a18188-1174-7349-9cee-e115d0c2c94e', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, 'Wherever applicable', NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ba-70d6-9923-7e78c7c0cc2a', '01a18188-1175-7152-82f2-6578c75eab6e', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11bb-7218-9d42-778201f4afff', '01a18188-1175-7152-82f2-6578c7ae0ba6', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11bb-7218-9d42-77820269de47', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11bc-7279-b284-16349c7e83cf', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11bc-7279-b284-16349cc38f40', '01a18188-1175-7152-82f2-6578c7e672a6', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11bd-71d6-8fff-6167331ac1f8', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, 'With company secretary signature', NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11bd-71d6-8fff-6167338d39fb', '01a18188-116c-7248-ba33-27d6abc56fbd', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11be-72ac-9023-50b17152a9d6', '01a18188-1176-7000-999e-a6e503d4b9d7', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 0, NULL, NULL, 12, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11be-72ac-9023-50b171db8155', '01a18188-1176-7000-999e-a6e50459b74b', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 0, 'Where applicable', NULL, 13, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11bf-73a5-a199-848a80c35d67', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, NULL, NULL, 14, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11bf-73a5-a199-848a81236208', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 1, NULL, NULL, 15, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c0-715e-9f62-0228aa4686a2', '01a18188-1172-729d-bdde-94859aae6fb5', '01a18188-10f1-7334-b18e-d6ce3f33ee5b', 0, 'Where applicable', NULL, 16, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c0-715e-9f62-0228aaafcff9', '01a18188-1168-71fe-b402-7b2e0744bcb9', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c1-7399-bcf5-7c0c0bbb2e64', '01a18188-1168-71fe-b402-7b2e06bf46bb', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c1-7399-bcf5-7c0c0bc4e553', '01a18188-1174-7349-9cee-e115d083526f', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c2-715a-afc9-467f27e35af0', '01a18188-1174-7349-9cee-e115d0c2c94e', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, 'Wherever applicable', NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c2-715a-afc9-467f27fe1c7d', '01a18188-1175-7152-82f2-6578c75eab6e', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c3-73c4-ac18-fe39e6e88c3b', '01a18188-1175-7152-82f2-6578c7ae0ba6', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c3-73c4-ac18-fe39e74d1424', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c4-735a-baf1-6dde45cfc151', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c4-735a-baf1-6dde46cd274b', '01a18188-1175-7152-82f2-6578c7e672a6', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c5-714f-a079-d4f1c689e4cd', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, 'With company secretary signature', NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c5-714f-a079-d4f1c73addac', '01a18188-116c-7248-ba33-27d6abc56fbd', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c6-703c-8c0c-88246cf59af4', '01a18188-1176-7000-999e-a6e503d4b9d7', '01a18188-10f2-709b-aeaf-23ead4e139ff', 0, NULL, NULL, 12, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c6-703c-8c0c-88246d4548e0', '01a18188-1176-7000-999e-a6e50459b74b', '01a18188-10f2-709b-aeaf-23ead4e139ff', 0, 'Where applicable', NULL, 13, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c7-73da-b127-fb2635907d3e', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, NULL, NULL, 14, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c7-73da-b127-fb26363d84bd', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10f2-709b-aeaf-23ead4e139ff', 1, NULL, NULL, 15, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c8-729e-8d87-78db7beb2eba', '01a18188-1172-729d-bdde-94859aae6fb5', '01a18188-10f2-709b-aeaf-23ead4e139ff', 0, 'Where applicable', NULL, 16, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c8-729e-8d87-78db7c188f4d', '01a18188-1168-71fe-b402-7b2e0744bcb9', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c9-7300-81ad-315277dfbd2f', '01a18188-1168-71fe-b402-7b2e06bf46bb', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11c9-7300-81ad-31527837916b', '01a18188-1174-7349-9cee-e115d083526f', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ca-7263-88c5-645fa8f65cae', '01a18188-1174-7349-9cee-e115d0c2c94e', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, 'Wherever applicable', NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ca-7263-88c5-645fa9b73442', '01a18188-1175-7152-82f2-6578c75eab6e', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11cb-71ee-9fd7-f4bda8ea2a77', '01a18188-1175-7152-82f2-6578c7ae0ba6', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11cb-71ee-9fd7-f4bda9b98a69', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11cc-7066-97fb-0625e182053d', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11cc-7066-97fb-0625e1c3015d', '01a18188-1175-7152-82f2-6578c7e672a6', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11cd-7218-8441-be17f695b5a0', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, 'With company secretary signature', NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11cd-7218-8441-be17f6d744f3', '01a18188-116c-7248-ba33-27d6abc56fbd', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ce-725a-9643-a9ccea6ad09c', '01a18188-1176-7000-999e-a6e503d4b9d7', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 0, NULL, NULL, 12, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11ce-725a-9643-a9cceacc6cd5', '01a18188-1176-7000-999e-a6e50459b74b', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 0, 'Where applicable', NULL, 13, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11cf-708f-a28f-3291b89d80f5', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, NULL, NULL, 14, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11cf-708f-a28f-3291b978b259', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 1, NULL, NULL, 15, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d0-718f-b999-e8d058f63b42', '01a18188-1172-729d-bdde-94859aae6fb5', '01a18188-10f2-709b-aeaf-23ead4e6e07e', 0, 'Where applicable', NULL, 16, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d0-718f-b999-e8d059b70a87', '01a18188-1168-71fe-b402-7b2e0744bcb9', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, 'Specifying the person(s) authorized to open and operate the account', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d1-71d8-ad3f-a8830cee722c', '01a18188-1168-71fe-b402-7b2e06bf46bb', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d1-71d8-ad3f-a8830cf817a7', '01a18188-1174-7349-9cee-e115d083526f', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d2-7049-ac85-bc96110efdcc', '01a18188-1174-7349-9cee-e115d0c2c94e', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, 'Wherever applicable', NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d2-7049-ac85-bc9611c28356', '01a18188-1175-7152-82f2-6578c75eab6e', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d3-7050-8ec3-4e7443796d09', '01a18188-1175-7152-82f2-6578c7ae0ba6', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, 'Or Annexure IV/INC form II under Companies Act 2017', NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d3-7050-8ec3-4e7443863d1e', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, 'All directors and persons authorized to open and operate the account', NULL, 7, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d4-73ff-a6c5-645209e9f7a5', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, NULL, NULL, 8, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d4-73ff-a6c5-64520a3b7a01', '01a18188-1175-7152-82f2-6578c7e672a6', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, 'Shareholders holding 10% or above stake', NULL, 9, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d5-73b3-9376-b1b42f183eb5', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, 'With company secretary signature', NULL, 10, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d5-73b3-9376-b1b42f97cc51', '01a18188-116c-7248-ba33-27d6abc56fbd', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, 'Source of fund / proof of income of the UBO', NULL, 11, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d5-73b3-9376-b1b43015c8ea', '01a18188-1176-7000-999e-a6e503d4b9d7', '01a18188-10f8-7209-95af-32d8fc210bcd', 0, NULL, NULL, 12, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d6-70f5-9af0-31b1e3e8367a', '01a18188-1176-7000-999e-a6e50459b74b', '01a18188-10f8-7209-95af-32d8fc210bcd', 0, 'Where applicable', NULL, 13, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d6-70f5-9af0-31b1e4e31dcd', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, NULL, NULL, 14, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d7-7047-9d53-bceab00baf35', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10f8-7209-95af-32d8fc210bcd', 1, NULL, NULL, 15, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d7-7047-9d53-bceab100a040', '01a18188-1172-729d-bdde-94859aae6fb5', '01a18188-10f8-7209-95af-32d8fc210bcd', 0, 'Where applicable', NULL, 16, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d8-718e-a00b-fc49096a749c', '01a18188-1169-7115-a699-a54ffc6f3d93', '01a18188-10f7-709f-91e3-1310434dbf1b', 1, NULL, NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d9-71ac-805b-57cae19ecc22', '01a18188-1169-7115-a699-a54ffcdfec02', '01a18188-10f7-709f-91e3-1310434dbf1b', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11d9-71ac-805b-57cae2281678', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10f7-709f-91e3-1310434dbf1b', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11da-72cc-864e-5190b2f8cd8b', '01a18188-116b-7251-9da7-0fa91f16f4ca', '01a18188-10f7-709f-91e3-1310434dbf1b', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11da-72cc-864e-5190b32c2c10', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10f7-709f-91e3-1310434dbf1b', 1, NULL, NULL, 5, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11db-7065-b33b-3f9b2247d52b', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10f7-709f-91e3-1310434dbf1b', 1, NULL, NULL, 6, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11db-7065-b33b-3f9b22f11853', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10ef-70e9-8ee5-70f3af3d422a', 1, 'Important Notes (b): attested after original seen', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11dc-7276-a27c-92520f897b69', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10ef-70e9-8ee5-70f3af3d422a', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11dc-7276-a27c-92521073a300', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10ef-70e9-8ee5-70f3af3d422a', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11dd-718a-b5ef-6e5659a63db1', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10ef-70e9-8ee5-70f3af3d422a', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11dd-718a-b5ef-6e5659c95d7f', '01a18188-1161-715c-a15c-e21926e86b7c', '01a18188-10fc-7147-8b2e-d84668a6740e', 1, 'Important Notes (b): attested after original seen', NULL, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11de-7027-809f-9999dcb192aa', '01a18188-116c-7248-ba33-27d6ab03bfa1', '01a18188-10fc-7147-8b2e-d84668a6740e', 1, NULL, NULL, 2, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11de-7027-809f-9999dd186738', '01a18188-1164-7181-99ea-fcf66c22fd5a', '01a18188-10fc-7147-8b2e-d84668a6740e', 1, NULL, NULL, 3, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-11df-72a2-a903-24a7963245ca', '01a18188-1165-7393-9a7e-7fe7cf87c6bb', '01a18188-10fc-7147-8b2e-d84668a6740e', 1, NULL, NULL, 4, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-1161-715c-a15c-e21926e86b7c', 'ID_DOC_COPY', 'Photocopy of valid identity document (CNIC/SNIC/NICOP/POC/ARC/PoR/Passport)', NULL, 1, NULL, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1161-715c-a15c-e21927085784', 'FORM_B_CRC', 'Form-B / Birth Certificate / Student ID card of minor', NULL, 1, NULL, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1162-72f7-9dde-88060fccfab6', 'GUARDIAN_ID', 'Photocopy of identity document of the guardian of the minor', NULL, 1, NULL, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1162-72f7-9dde-88061037382e', 'SOURCE_OF_FUNDS_DECL', 'Declaration for source of funds', NULL, 0, NULL, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1162-72f7-9dde-880610f0f7ee', 'PROOF_OF_INCOME', 'Proof of income (salary slip / utility bill / rent agreement / aqama etc.)', NULL, 0, NULL, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1163-72c0-8d8c-ae50f3657b76', 'SALARY_SLIP_APPOINTMENT', 'Salary slip, copy of appointment letter or letter from employer', NULL, 0, NULL, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1163-72c0-8d8c-ae50f44b61e6', 'LANDHOLDING_EVIDENCE', 'Landholding record / Passbook / Form 7 / Form 2 / Dhal receipt / Khasra-Khatooni / Land Agreement', NULL, 0, NULL, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1164-7181-99ea-fcf66be56d2a', 'STOCK_TRADING_SHEET', 'Trading sheet of stock investor', NULL, 0, NULL, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1164-7181-99ea-fcf66c22fd5a', 'PHYSICAL_VERIFICATION', 'Physical verification report', NULL, 0, NULL, 9, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1165-7393-9a7e-7fe7cf87c6bb', 'SS_UNDERTAKING', 'Undertaking for Acceptance of Different Specimen Signatures for Account Operation', NULL, 0, NULL, 10, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1165-7393-9a7e-7fe7cffd2fd1', 'REG_CERTIFICATE', 'Registration certificate for registered concerns / Certificate of Incorporation', NULL, 1, NULL, 11, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1165-7393-9a7e-7fe7d04b5347', 'SALES_TAX_NTN', 'Sales tax registration or NTN', NULL, 0, NULL, 12, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1166-7359-8102-32edc60bbe09', 'TRADE_BODY_MEMBERSHIP', 'Certificate or proof of membership of trade bodies', NULL, 0, NULL, 13, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1166-7359-8102-32edc62a289d', 'SOLE_PROP_DECLARATION', 'Declaration of sole proprietorship on business letter head including Source of Fund', NULL, 0, NULL, 14, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1167-7295-9afd-2325c26c5f3e', 'ACCOUNT_REQUISITION_LETTERHEAD', 'Account opening requisition on business letter head', NULL, 0, NULL, 15, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1167-7295-9afd-2325c36655ac', 'UBO_DECLARATION', 'Ultimate Beneficial Owner (UBO) Declaration Form', NULL, 0, NULL, 16, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1168-71fe-b402-7b2e0686292a', 'RENTAL_OWNERSHIP_DOC', 'Rental/Ownership document such as Rental Agreement or Lease document of business place', NULL, 0, NULL, 17, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1168-71fe-b402-7b2e06bf46bb', 'MEMORANDUM_ARTICLES', 'Memorandum & Article of Association / By-laws / Rules and Regulations', NULL, 1, NULL, 18, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1168-71fe-b402-7b2e0744bcb9', 'BOARD_RESOLUTION', 'Resolution of the Board of Directors/Trustees/Executive Committee for opening of account', NULL, 0, NULL, 19, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1169-7115-a699-a54ffc6f3d93', 'LIST_OF_TRUSTEES', 'List of trustees/members/governing body on letterhead', NULL, 0, NULL, 20, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1169-7115-a699-a54ffcdfec02', 'NPO_DECLARATION_FORM', 'Declaration form for NPOs/NGOs/Charities, Trust, Clubs, Societies and Associations signed by Governing Body', NULL, 0, NULL, 21, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116a-73e1-a829-862828537a69', 'LATEST_FINANCIALS', 'Latest financial statement including audit report', NULL, 0, NULL, 22, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116a-73e1-a829-8628285e8718', 'EAD_APPROVAL', 'Approval of EAD, Government of Pakistan (International NGO receiving funds from abroad)', NULL, 0, NULL, 23, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116b-7251-9da7-0fa91e1b762b', 'DECLARATION_QUESTIONNAIRE', 'Declaration statement / questionnaire', NULL, 0, NULL, 24, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116b-7251-9da7-0fa91f16f4ca', 'RUBBER_STAMP', 'Rubber stamp along with secretary/Member signature affixed on all documents', NULL, 0, NULL, 25, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116c-7248-ba33-27d6ab03bfa1', 'CIF_ALL_SIGNATORIES', 'CIF of all members and authorized signatories', NULL, 0, NULL, 26, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116c-7248-ba33-27d6abc56fbd', 'SOURCE_OF_FUND_GOVERNING_BODY', 'Source of Fund / proof of income of Governing Body/Board of Trustees/Controlling Persons', NULL, 0, NULL, 27, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116c-7248-ba33-27d6ac614f0e', 'GOVT_AUTHORITY_LETTER', 'Resolution/Authority letter from concerned department duly endorsed by Admin/Finance Division', NULL, 0, NULL, 28, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116d-7364-992f-e4e4e29d91d5', 'FUND_ALLOCATION_LETTER', 'Fund allocation letter issued by concerned authority', NULL, 0, NULL, 29, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116d-7364-992f-e4e4e2c8431d', 'GAZETTE_NOTIFICATION', 'Authenticated list of authorized individuals along with the gazette notification', NULL, 0, NULL, 30, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116e-709e-9922-24079a70af6b', 'POWER_OF_ATTORNEY', 'Certified copy of Power of Attorney or Agency Agreement', NULL, 1, NULL, 31, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116e-709e-9922-24079a73a5ee', 'LETTER_OF_ADMINISTRATION', 'Certified copy of Letter of Administration or Probate', NULL, 1, NULL, 32, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116f-7146-a7e6-176c4c560a5d', 'TAX_EXEMPTION_CERTIFICATE', 'Tax exemption certificate', NULL, 0, NULL, 33, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116f-7146-a7e6-176c4cdcc841', 'PEP_FORM', 'PEP Form approved from Head Office', NULL, 0, NULL, 34, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-116f-7146-a7e6-176c4d899f6c', 'QA22_FORM', 'QA-22 form (foreigner residing in Pakistan)', NULL, 0, NULL, 35, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1170-71b3-a73d-99c3014d26e3', 'VERNACULAR_FORM', 'Vernacular form on 100 rupees bond paper (signature other than English/Urdu)', NULL, 0, NULL, 36, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1170-71b3-a73d-99c301fde1a3', 'PASSPORT_PHOTOS', 'Two passport sized attested photographs (shaky/immature signature case)', NULL, 1, NULL, 37, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1171-725b-bc57-340261f45bdd', 'PARTNERSHIP_DEED', 'Attested copy of \"Partnership Deed\" duly signed by all partners of the firm', NULL, 1, NULL, 38, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1171-725b-bc57-34026295fa10', 'REGISTRAR_OF_FIRMS_CERT', 'Attested copy of Registration Certificate with Registrar of Firms (unregistered partnership must be stated on the account opening form)', NULL, 1, NULL, 39, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1172-729d-bdde-948599d61c5e', 'PARTNERS_AUTHORITY_LETTER', 'Authority letter from all partners, in original, authorizing the person(s) to operate the firm\'s account', NULL, 0, NULL, 40, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1172-729d-bdde-94859aae6fb5', 'CHAMBER_MEMBERSHIP_CERT', 'Membership Certificate — Chamber of Commerce \"Where Applicable\"', NULL, 0, NULL, 41, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1172-729d-bdde-94859ae6cbe2', 'LLP_AGREEMENT', 'Limited Liability Partnership Deed / Agreement', NULL, 1, NULL, 42, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1173-7287-bf1a-f0abbc57e318', 'LLP_FORM_III', 'LLP-Form-III (initial particulars of partners / designated partner in case of newly incorporated LLP)', NULL, 1, NULL, 43, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1173-7287-bf1a-f0abbcbb0015', 'LLP_FORM_V', 'LLP-Form-V (change in partners / designated partner in case of already incorporated LLP)', NULL, 1, NULL, 44, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1174-7349-9cee-e115d083526f', 'CERT_OF_INCORPORATION', 'Certificate of Incorporation', NULL, 1, NULL, 45, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1174-7349-9cee-e115d0c2c94e', 'CERT_COMMENCEMENT_BUSINESS', 'Certificate of commencement of business, wherever applicable', NULL, 1, NULL, 46, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1175-7152-82f2-6578c75eab6e', 'LIST_OF_DIRECTORS', 'List of directors required on company letterhead', NULL, 0, NULL, 47, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1175-7152-82f2-6578c7ae0ba6', 'FORM_29', 'Form-29 issued under Companies Ordinance 1984, or Annexure IV/INC form II issued under Companies Act 2017 (\"Form-A/Form C\") where applicable', NULL, 1, NULL, 48, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1175-7152-82f2-6578c7e672a6', 'SHAREHOLDER_VERIFICATION_10', 'Identification and verification of natural person shareholders holding 10% or above stake in the entity', NULL, 0, NULL, 49, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1176-7000-999e-a6e503d4b9d7', 'RISK_REGISTER_UBO', 'Copy of Risk Register (Register of Beneficial Owners)', NULL, 0, NULL, 50, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1176-7000-999e-a6e50459b74b', 'SHAREHOLDING_LETTERHEAD', 'Shareholding of the company on company letterhead \"Where Applicable\"', NULL, 0, NULL, 51, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1177-706e-83c2-665e90da77b3', 'FORM_II_FOREIGN_BRANCH', 'Form II about particulars of directors, Principal Officer etc. for a newly registered branch or liaison office of a foreign company', NULL, 1, NULL, 52, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1177-706e-83c2-665e91d191d8', 'FORM_III_FOREIGN_BRANCH', 'Form III about change in directors, principal officers etc. in an already registered foreign company branch or liaison office', NULL, 1, NULL, 53, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1178-7381-a727-b55f130a8dfd', 'BOI_PERMISSION_LETTER', 'Copy of permission letter from the relevant authority i.e. Board of Investment', NULL, 1, NULL, 54, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1178-7381-a727-b55f13f26905', 'PRINCIPAL_OFFICE_LETTER', 'Letter from the Principal Office of the entity authorizing the person(s) to open and operate the account', NULL, 0, NULL, 55, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1178-7381-a727-b55f140a6fb1', 'TRUST_REGISTRATION_CERT', 'Certificate of Registration / Instrument of Trust', NULL, 1, NULL, 56, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1179-7383-8fc7-eeef4f6b1d12', 'FOREIGN_EXCHANGE_MANUAL', 'As per Foreign Exchange manual for details on Resident / Non-Resident accounts', NULL, 0, NULL, 57, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-110f-71e2-bf2d-10d1544f75f5', 'NO_EDUCATION', 'No Education', 'نا خواندہ', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1110-72dc-b6dd-37602de3af27', 'BELOW_MATRIC', 'Below Matric', 'میٹرک سے کم', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1110-72dc-b6dd-37602dfdef9a', 'MATRIC_O_LEVEL', 'Matric/O level', 'میٹرک/او لیول', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1111-704a-b3b6-6582ed545d71', 'INTER_A_LEVEL', 'Intermediate/A level', 'انٹرمیڈیٹ/اے لیول', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1111-704a-b3b6-6582ede0917d', 'GRADUATE', 'Graduate', 'گریجویٹ', 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1112-73d4-a212-100fd0dfad8d', 'POSTGRADUATE', 'Postgraduate', 'پوسٹ گریجویٹ', 0, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1113-7110-bc99-9d9059805715', 'EDU_OTHERS', 'Others', 'دیگر', 1, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('0f598ed5-5a1d-4020-9462-637092224f5b', 'FC-009', 'Advances and Credit Records', 'Loan applications, sanction letters, financing agreements, security documents, guarantees, mortgage records, recovery proceedings and settlement documents.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('145acf8e-89f1-4132-b662-ffb6402afa86', 'FC-011', 'AML/CFT and Compliance Records', 'Customer due diligence records, compliance review documentation and transaction-monitoring records, subject to applicable retention and confidentiality requirements.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('18324feb-7781-42ba-992c-1bc4ab03b5e7', 'FC-006', 'Deposit Account Records', 'Term deposit applications, deposit receipts, maturity instructions, premature encashment records and related correspondence.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('1f026e97-3eb5-499d-bc5c-59d0982bea9c', 'FC-016', 'Regulatory and Tax Records', 'SBP returns, FBR notices and replies, tax-related documents, statutory statements and related correspondence.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('21c26120-ff5c-4cc0-9e54-75ac447760fd', 'FC-008', 'Reconciliation and Suspense Accounts', 'Inter-branch reconciliation statements, suspense account records, outstanding entries and adjustment documentation.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('23cf57f1-e7f1-4c18-9f27-25c57b7e8de4', 'FC-020', 'Fixed Assets and Other Registers', 'Fixed asset registers, asset disposal records, branch inventory records and other operational registers required under the Bank\'s policy.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('3c81c1e0-4b81-4d69-b5c7-866a8f503c1f', 'FC-007', 'General Ledger and Accounting Records', 'General ledger extracts, subsidiary ledgers, journal vouchers, day books, trial balances and accounting statements.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('5fe981b8-cfa8-4794-8806-8ee3efe5cc33', 'FC-017', 'Branch Administrative Records', 'Branch operational correspondence, inspection visit reports, handover/takeover documents, operational approvals and control registers.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('6b09b564-3945-4ab2-88bb-9d525bf0a756', 'FC-002', 'Cash Transaction Records', 'Cash payment and receipt vouchers, cash scrolls, teller sheets, daily cash balancing statements and cash difference reports.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('7e860385-6185-4039-baf6-dbecec645d23', 'FC-010', 'Collateral and Securities Records', 'Original or controlled security documents, title deeds, charge documents and other collateral records, subject to the Bank\'s secure custody arrangements.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('820d9ddb-993f-4ef5-b50f-429be5bb8a3a', 'FC-001', 'Account Opening Records', 'Account Opening Forms (AOFs), specimen signature cards, KYC/CDD documents, mandates, authorized signatory records and account closure documents.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('9bf99214-1ecc-4457-8eb9-a4e47ecd4e2c', 'FC-014', 'Cheque Book and Security Stationery Records', 'Cheque-book issuance registers, stock registers, security stationery records, damaged cheque-book records and destruction certificates.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('acda17e7-89bb-4c71-97fb-43d8caacfe89', 'FC-012', 'Audit and Inspection Records', 'Internal and external audit reports, SBP inspection observations, audit replies, compliance reports and rectification evidence.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('b0b5829e-cbc0-4384-a59f-c134f144c85d', 'FC-015', 'Cash Management Records', 'Cash remittance records, cash transfer documents, cash-in-transit records, cash insurance-related documents and cash-limit approvals.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('b48defeb-b3c1-457f-83e2-b0a70b591074', 'FC-004', 'Clearing Records', 'Inward and outward clearing registers, clearing instruments, returned cheques and clearing reconciliation statements.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('b4fdaf68-caee-47eb-a9ef-0317bbc2ea62', 'FC-019', 'Dormant and Unclaimed Account Records', 'Dormant account documentation, unclaimed deposit records, customer tracing correspondence and related regulatory submissions.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('ccd6fd25-1653-4e38-a3d3-43c77d458fa4', 'FC-003', 'Cheque and Payment Records', 'Paid cheques, debit/credit vouchers, payment instruments, stop-payment instructions and related supporting documents.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('d517b334-d1e9-4a13-8aa2-8d239546bd02', 'FC-013', 'Fraud, Investigation and Legal Records', 'FIRs, investigation reports, fraud-related correspondence, insurance claims, legal notices, court orders and litigation files.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('dc1a500e-5146-4d31-8d09-68e1b0575bae', 'FC-018', 'Customer Complaints and Claims', 'Customer complaints, disputed transactions, claim applications, investigation findings and final settlement correspondence.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('f5acfdb9-a9bd-410f-bddb-2d7ac91f7993', 'FC-005', 'Remittance and Funds Transfer Records', 'Domestic and international remittance documents, funds transfer instructions, payment confirmations and supporting vouchers.', '1', NULL, NULL, NULL, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-110a-70de-9be7-12194da0ecc6', 'MALE', 'Male', 'مرد', 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-110b-70cd-82d6-a0ca26d77481', 'FEMALE', 'Female', 'عورت', 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-110c-7195-b310-aaf43a2c1fd2', 'TRANSGENDER', 'Trans Gender', 'خواجہ سرا', 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-1124-70ce-a16a-0ed06cbd8dd3', 'CNIC', 'Computerized National Identity Card (CNIC)', 'کمپیوٹرائزڈ قومی شناختی کارڈ', 'NADRA', 0, 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1125-718a-a4e3-c0cbfa92ef42', 'SNIC', 'Smart National Identity Card (SNIC)', 'سمارٹ قومی شناختی کارڈ', 'NADRA', 0, 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1125-718a-a4e3-c0cbfabca909', 'NICOP', 'National Identity Card for Overseas Pakistanis (NICOP)', 'نائیکوپ', 'NADRA', 0, 1, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1126-7004-96eb-bbe1fc2d7b1c', 'POC', 'Pakistan Origin Card (POC)', 'پاکستان اوریجن کارڈ', 'NADRA', 0, 1, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1127-70b2-a0af-91fd20b4f888', 'ARC', 'Alien Registration Card (AR+D47C)', 'ایلین رجسٹریشن کارڈ', 'NARA, Ministry of Interior', 0, 1, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1127-70b2-a0af-91fd20dfde75', 'POR', 'Proof of Registration Card (PoR)', 'پی او آر کارڈ', 'NADRA', 0, 1, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1128-71b0-be14-82caf67544c9', 'PASSPORT', 'Passport (Foreign Individual Only)', 'پاسپورٹ', 'Passport Authority', 0, 1, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1128-71b0-be14-82caf740b95f', 'CRC_B_FORM', 'CRC / B-Form', 'سی آر سی/بی فارم', 'NADRA', 1, 0, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1129-7173-aa7e-e6387e5627ea', 'BIRTH_CERT', 'Birth Certificate (Government Authority)', 'برتھ سرٹیفکیٹ', 'Government Authority', 1, 0, 9, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1129-7173-aa7e-e6387e6d909d', 'STUDENT_ID', 'Student ID Card', 'طالب علم کارڈ', 'Educational Institution', 1, 0, 10, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-1140-7350-8978-7ad50d77c865', 'SALARIED', 'Salaried', 'تنخواہ دار', 'individual', 1, 0, 0, 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1140-7350-8978-7ad50dfce5db', 'PENSIONER', 'Pensioner', 'پنشنر', 'individual', 1, 0, 0, 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1141-7250-a6a6-b6eba0a432a6', 'STUDENT', 'Student', 'طالب علم', 'individual', 0, 1, 0, 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1141-7250-a6a6-b6eba159c1a6', 'HOUSE_WIFE', 'House Wife', 'خاتون خانہ', 'individual', 0, 1, 0, 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1142-7193-98cb-1c6b937775c3', 'UNEMPLOYED', 'Unemployed', 'بے روزگار', 'individual', 0, 1, 0, 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1143-72d8-b750-8586d6b6f1fa', 'SELF_EMPLOYED', 'Self Employed', 'سیلف ایمپلائیڈ', 'both', 0, 0, 0, 0, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1143-72d8-b750-8586d6c69c40', 'LABOR_DAILY_WAGES', 'Labor/Daily Wages', 'مزدور/یومیہ اجرت', 'individual', 0, 0, 0, 0, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1144-73af-8740-3c10a09bee40', 'AGRICULTURIST', 'Agriculturist', 'زمیندار', 'individual', 0, 0, 0, 0, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1144-73af-8740-3c10a156838a', 'FAMILY_BUSINESS', 'Family Business', 'خاندانی کاروبار', 'individual', 0, 0, 0, 0, 19, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1145-7168-b9cb-c88c9c3cf540', 'STOCK_INVESTMENT', 'Stock/Investment', 'اسٹاک/سرمایہ کاری', 'individual', 0, 0, 0, 0, 9, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1145-7168-b9cb-c88c9cd5acff', 'RENTED_PROPERTY', 'Rented Property', 'کرایہ کی جائیداد', 'both', 0, 0, 0, 0, 10, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1146-708c-9498-fb5f874bb452', 'HOME_REMITTANCE', 'Home Remittance', 'ترسیلات زر', 'individual', 0, 0, 1, 0, 11, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1146-708c-9498-fb5f876faab2', 'EXPORT_PROCEEDS', 'Export Proceeds', 'برآمدی آمدن', 'entity', 0, 0, 0, 0, 12, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1147-7051-8699-d610302faac0', 'PROPERTY_REAL_ESTATE', 'Property/Real Estate', 'جائیداد/رئیل اسٹیٹ', 'entity', 0, 0, 0, 0, 13, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1147-7051-8699-d61030360499', 'FDI', 'Foreign Direct Investment (FDI)', 'براہ راست غیر ملکی سرمایہ کاری', 'entity', 0, 0, 0, 0, 14, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1148-73f0-bfc2-70266bc813f5', 'LOCAL_TRADING', 'Local Trading', 'مقامی تجارت', 'entity', 0, 0, 0, 0, 15, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1148-73f0-bfc2-70266bf945fa', 'EQUITY_FX_TRADING', 'Equity/FX Trading', 'ایکویٹی/ایف ایکس ٹریڈنگ', 'entity', 0, 0, 0, 0, 16, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1149-738b-98c8-920fbd2be374', 'CHARITY_DONATIONS', 'Charity & Funds Donations', 'خیرات و عطیات', 'entity', 0, 0, 0, 0, 17, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1149-738b-98c8-920fbd31257e', 'INCOME_OTHERS', 'Others', 'دیگر', 'both', 0, 0, 0, 1, 18, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-110d-7397-bac8-a798877d376f', 'MARRIED', 'Married', 'شادی شدہ', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-110d-7397-bac8-a798878862ef', 'SINGLE', 'Single', 'غیر شادی شدہ', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-110e-7117-a021-2750b07a302b', 'MS_OTHERS', 'Others', 'دیگر', 1, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
(88, '2026_10_14_132052_drop_audit_circular_stationery_dispatch_and_daily_position_tables', 3),
(89, '2026_10_25_024533_allow_in_progress_status_and_link_branches_on_aksic_applications', 3),
(90, '2026_10_26_100000_move_file_management_pages_to_private_disk', 3),
(91, '2026_10_26_110000_recount_box_file_counts', 3),
(92, '2026_10_26_120000_set_old_account_opening_form_type_from_nature_of_account', 3),
(93, '2026_10_26_130000_make_customer_individual_date_of_birth_nullable', 3);

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
('01a18188-1132-731d-af95-4fe48bf605d5', 'SIGNING_AUTHORITY', 'Signing Authority', 'دستخط کنندہ', 0, 0, 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1132-731d-af95-4fe48c5f0e20', 'SINGLY', 'Singly', 'واحد مجاز', 0, 0, 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1133-72ca-a606-4927c32830cb', 'EITHER_OR_SURVIVOR', 'Either or Survivor', 'کوئی ایک یا باقی ماندہ', 1, 0, 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1134-7084-acf4-338ad4b647a4', 'JOINTLY', 'Jointly', 'مشترکہ', 1, 0, 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1134-7084-acf4-338ad516e115', 'MANDATE', 'Mandate', 'اختیار', 0, 1, 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1135-7128-a5f3-d207666b53bd', 'OI_OTHER', 'Other', 'دیگر', 0, 0, 1, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
(63, 'view file categories', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(64, 'create file categories', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(65, 'edit file categories', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(66, 'delete file categories', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(67, 'view file management systems', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(68, 'create file management systems', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(69, 'edit file management systems', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(70, 'delete file management systems', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(71, 'transfer file management systems', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(72, 'approve file management transfers', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(73, 'archive file management systems', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(74, 'create boxes', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(75, 'manage boxes', 'web', '2026-10-28 08:02:15', '2026-10-28 08:02:15'),
(76, 'view account openings', 'web', '2026-10-28 08:02:18', '2026-10-28 08:02:18'),
(77, 'create account openings', 'web', '2026-10-28 08:02:18', '2026-10-28 08:02:18'),
(78, 'edit account openings', 'web', '2026-10-28 08:02:18', '2026-10-28 08:02:18'),
(79, 'delete account openings', 'web', '2026-10-28 08:02:18', '2026-10-28 08:02:18'),
(80, 'approve account openings', 'web', '2026-10-28 08:02:18', '2026-10-28 08:02:18'),
(81, 'manage account opening references', 'web', '2026-10-28 08:02:18', '2026-10-28 08:02:18'),
(82, 'view aksic claims', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(83, 'lodge aksic claims', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(84, 'settle aksic claims', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(85, 'delete aksic claims', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(86, 'view aksic budget', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(87, 'manage aksic budget', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(88, 'view aksics', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(89, 'create aksics', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(90, 'edit aksics', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(91, 'delete aksics', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(92, 'view all aksic cases', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(93, 'view aksic dashboard', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(94, 'view file management dashboard', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(95, 'view account opening dashboard', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(96, 'export file management systems', 'web', '2026-10-28 08:02:19', '2026-10-28 08:02:19'),
(97, 'submit account openings', 'web', '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
(98, 'unlock account openings', 'web', '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
(99, 'submit file management systems', 'web', '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
(100, 'approve file management systems', 'web', '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
(101, 'unlock file management systems', 'web', '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
(102, 'view aksic applications', 'web', '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
(103, 'sync aksic applications', 'web', '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
(104, 'update aksic application status', 'web', '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-1114-7271-98c7-4310328b4012', 'GOVT_SERVICE', 'Government Service', 'سرکاری ملازمت', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1114-7271-98c7-431032ff963f', 'PRIVATE_SERVICE', 'Private Service', 'پرائیویٹ ملازمت', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1115-7294-afe6-08bd984e0da1', 'HOUSEWIFE', 'Housewife', 'خاتون خانہ', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1115-7294-afe6-08bd98a64e44', 'SELF_EMPLOYED', 'Self Employed', 'سیلف ایمپلائیڈ', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1116-7202-afce-6b6c15337712', 'UNEMPLOYED', 'Unemployed', 'بے روزگار', 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1117-723c-9bc9-19bccc70a190', 'AGRICULTURE', 'Agriculture', 'زراعت', 0, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1117-723c-9bc9-19bccd26672c', 'PROF_STUDENT', 'Student', 'طالب علم', 0, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1118-71b0-98b1-f1f03a8b77df', 'PROF_OTHERS', 'Others', 'دیگر', 1, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-111e-7373-baae-f00eb2bcf038', 'FATHER', 'Father (S/o)', 'والد', 1, 1, 1, 1, 1, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111e-7373-baae-f00eb2c1b459', 'MOTHER', 'Mother', 'والدہ', 0, 1, 1, 1, 1, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111f-725b-8be2-ed0bc7607695', 'HUSBAND', 'Husband (W/o)', 'شوہر', 1, 1, 1, 1, 1, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-111f-725b-8be2-ed0bc842793d', 'WIFE', 'Wife', 'بیوی', 0, 1, 1, 1, 1, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1120-730e-84e0-e7a4e8c23f89', 'SON', 'Son', 'بیٹا', 0, 1, 1, 1, 1, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1120-730e-84e0-e7a4e90d78a9', 'DAUGHTER', 'Daughter (D/o)', 'بیٹی', 1, 1, 1, 1, 1, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1121-7369-aee0-1a1bfca15b9a', 'BROTHER', 'Brother', 'بھائی', 0, 1, 1, 1, 1, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1122-7368-96ab-c3cc2b3f80ad', 'SISTER', 'Sister', 'بہن', 0, 1, 1, 1, 1, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1122-7368-96ab-c3cc2c384aa8', 'GUARDIAN', 'Guardian', 'سرپرست', 0, 1, 1, 1, 0, 9, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1123-73f3-8248-bb7e0899151a', 'EMPLOYER', 'Employer', 'آجر', 0, 0, 0, 1, 0, 10, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1123-73f3-8248-bb7e08e0cd7d', 'REL_OTHER', 'Other', 'دیگر', 0, 1, 1, 1, 1, 11, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('9WF54SzjxaB3EBxe8X2QHFUjDCXZW6YOvLFxYN61', NULL, '57.141.20.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 (compatible; meta-webindexer/1.1 (+https://developers.facebook.com/docs/sharing/webmasters/crawler))', 'ZXlKcGRpSTZJa2gwUTFVcmRXeERVVkYxU0VwdVVXZG5jbFZ6UlVFOVBTSXNJblpoYkhWbElqb2lLMUlyUm5sQ1drNDJRalExSzA1RVJHVlBkRzVIYzJWeEwyaFdZbTVqVGpJMEwxWktSa1pYZG5wbk0zRmpTbXhuTTJJcllWZERkVTFpVkVkR1pHdzVjMHRaVWswNE5WZ3JOV2N3U3pkRmVrdzRSVEJvVlhsemVtMVJWbHBWV1hWelJVbDNURmd5UzFoUlIySTJaMHR5U1RKRVMxUlZMMUkyTmxOcVpVSjRaMlo2VFRCaFRVcEljakJSUlVSWllYaEVPVkp0YVdWd2VHWjVia3BhVUhKMGNtMHJNMDVJUmpaaGFGcFFTVkZNWjJkcFZubDVRa000V1VSVWNreE5NMUF2YW1SQ1JVTlFTV2hOYjNad1pFNXJRMmhoTUhGUlZtSjRZM00xTm1abFpUWnZRemh5T0RKelIxbDZUakYwUzNaR1RsUlRUbmxZUjFWc1ZHSlViME5vZEVSMVRUZG1UMjFqZGpGaWFrazFlbGxLZVZwUlNVdDZSMkl4UmpOWWF6Y3liMjVOYVZaRGJqUk1WRkU5SWl3aWJXRmpJam9pTjJaak1qYzRORGs1TVdOa1lUTmlZemN6TWpreU5XWmtObVZtWkRnM09ESTFNemhoT0RGall6TmtORE15WVRCa1l6TTVPV1EwWXpKa01tTTROMlZoTWlJc0luUmhaeUk2SWlKOQ==', 1793168723),
('lQ4K8237hWxChbSJ7lAP79SaTftNI4RCnd5F4Sxt', NULL, '34.118.149.17', 'authorized-exposure-audit/6', 'ZXlKcGRpSTZJbVJJZG1VcmJreHRTR1U0TWxFcldIaDJOekJhV1hjOVBTSXNJblpoYkhWbElqb2laakIwTDBad1YyRlhiM2xwU0ZaaloyeFhNWEZPYURWWWRHTm5jSFZpVWt3M2FWRXJjMDluYVZSTGJtbE5TblY1ZUdKRmJuZEpabkp0Ym5ONlpFSk5SMWxZTWxaeE9FSlhiblEyZEV4UVFVRkVXa05PTDFwS1VUZExMelJFY1RCQk0yZDNUVEZQZWpSUlF6UldOakJqU0VWeWNUTjNURk5ZV25wME4wOWpTMXA2ZUZSSlptRTBWMFZRWkZGd2VWQk9kSEp1VDJRMWRYVlpiaTlhT0VvNU5rdFNOMUZKYkVGd1dGcG1RbUZGTjFoamVWY3JUMjUzYTNJMGNrWjBSbkJFUmpsRmVuTmpVaXRtZFZOV05XZ3Zaa0pIUm5KMVJFYzViM3BaTTFGcVRHRkRhemRMV0hVclNuWnlkemhHYm5sdmVXMU1SbUZzZHpWS1JHaHlSVGQyVXpVek4xVkRaa2hpVmpSb05YbEtNRzVGY0hJMmRFRTlQU0lzSW0xaFl5STZJalUyTURWa01tSm1ZVGt6TlRSbFlXRTNZbVEzT1RKaU9XSTROalZtWlRnNE9HUTFNbVJoWVRNME1UZGtPRGt3TTJNMk4yVTNNVE0xTlRWbE4yTTBaRGtpTENKMFlXY2lPaUlpZlE9PQ==', 1793173658),
('m59Zaeh6koTBfBZXRYe7uytOqu2ODJtoaRqbZore', NULL, '34.118.149.17', 'authorized-exposure-audit/6', 'ZXlKcGRpSTZJa2RSZWk5alNrbFNORkJvYTJWaU5FVTJkWEJYTmxFOVBTSXNJblpoYkhWbElqb2laMVZpVmpoM2IyVXljbEF5TDBOV1pucFJVWEJKVGtoTVVrY3pOM1pPTjFCSGJTOVFTMDVNU1Uxc1lrNHdhbVpZYWs1dWNTdGFSMjFqU0ZOTVpXc3lPSGRuVmxaV2F6WXpla0pvWld4V1drTk1ja3BqT1hkVFpEWklaMnB0VEdsMFF6SlFZV0oyYm5OT2FrbEhSVzVGVkZGeU4yRlFaVzB6Ym1WRlQySjRTME00VEVZcllXbHBWSEl5TnpoRmFHTmtUVFV2YjNkM1psZDNkR3RFVTI4M1VEbHNhVTVDTUVGVlRFVjVjMUZKV0ZWVFVHWkpSVmN6Y1RObWRGSm5UM0pzVG01MllXcG5NMVZxVlV4S2RtSlhaMU55Y0cxbldETllSaTlwUzBWQk0wNVJNVFJuTVdKVlJERk5WRGhwVlRsNmNGcG9OMVZpVDBoaVRreG1aa3BwYTFSWlQwTmhkVmRoVWsxVGVIUnNXRkZWVkdzdlZEbDBOaXN3S3pZM1pXSkRLMDV5VFdveFpFSllUVUU5SWl3aWJXRmpJam9pTVdaak1tUmhOVFV3WXpjMk5UWmtPRGsxWldNNVlUZG1OV1JtTW1OaFpEZG1aR1ZsWTJVeE5EZzBPRFJpTm1NM1lqZzNOemt3WXpVM05UVTFPVE0zTVNJc0luUmhaeUk2SWlKOQ==', 1793173662),
('TQfq62VCDMqV7uDoU5HzIZoA44BRGsb5Lox1xKwW', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJak01SzNGRVdGWTVXRkJKU0ZCallYVmhVVnA0ZFhjOVBTSXNJblpoYkhWbElqb2liMUpHT1VwV2RXUk5UM1ZQWWxWWGIxZFNRa1JqUzJKeVUyeE9RbGczU1ZGVU1FUTJNMkl4V1VSelNsbFlSbHBqTVVwcFNEaGplVFE1UVRscVV6QTFabkJsYXk5cGFHdzBZMFp5YzI1SFpIcERlRTlUZDB4c2NEbHJWU3RqUmpCRGJFbFlUM2wxTnpGVll5czRZV05MTmtFdmJGVXlWRWhEWW1KVGFIa3dOMFJ2U0ZFemVWQXhZbFI2Y0UxT1dVOXlla3BwUVV0QmQwNHJaREZ1Tlhvdk15dDJiVVl4WWtwVE1uTkNkWEZWWXk5Vk1UbGpRM0J5YjFaNVUzYzViVWwzVFdsM2FHZEZiVXAzTnpJeWJVVm1ZMUVyU0VkTFZWWllha2RwTUdnNFMzazVRMDk0TjBvME1VZHhiakJuTUUxR01GTlZMM1oyU2tkWmVGUnJZbTlOUkU1eWRIbEdhM2N5VlVaRllpODRSM055UjNnemFtZHVTME0zVkZCWlRFMHJVM1F4VUVGb1FtZERiVzg5SWl3aWJXRmpJam9pTkRNNVltSmlOR1U0WkdFME5UTXlORGt3TUdWalpqTTJPVFUxWXpBeVlqYzNaVGRqWVdWbE1EbGpaR1E1T1dVNU5EUTJNMll6TnpBNVpqQTNZakUyTnlJc0luUmhaeUk2SWlKOQ==', 1793176954),
('ZQJARFZA6iCmjjAvKkLlbrIM7QBoKr3bMFbLwSNZ', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbU5RYzBveFlVTm9hV2htTUdKU2NVUllibkJDU1ZFOVBTSXNJblpoYkhWbElqb2llVFUxYjNKdE4wWm1UbXA1VEdWTGRXRlRkMWxOWlVzMWNXZFhXa1pwUlZveU5qVmtZMUUwUW5RemJFb3lSMFJCVGpWc05HbEViVzgwUTNGSlNuSkJUa3BFVVVkNVpXZGxZbXMxVWs1Qk9FMVNhekZTVDNwWVNHdHVSVWxTV2pOc2RVOWFkMW9yVkZabFVWZDRUek55WmpWRlJuRmFRazAxUlN0Q1VtUXZZVVIzYldkWlEwUlBjalo2TjNWc09IcFZXREp4YjI0NWMxbElUWFJJVURZMU1rRmlOV2hwTDBsalRWQmFXVVJsZWpWMU5tMXJWWFJ3Y2pCNVYxSkhiR04yUjFCVmEyTTVWbFJvTkV4WFFXRk5PWE52VkZGRE5pczBUVVJPVldFMWEzUlhTRGRYVWxGMFJYRk1LM2xyWlhKRE5VRmFka2xZU0dNM2RuSjJOM0JyWTI1TFRuVnVRMEZEYm14YVlrTmhTbGN4YlVsQ1VXMDROSEpNZFU5WlJIUkJNV3RyV2t0R01rOTVhMk05SWl3aWJXRmpJam9pT1ROaE5tTXlNekppWXpoa1pqWmpaVGt5TURNMU5UWXpPV0ZqWW1Wa01ETm1PVEpqTjJRM1l6ZGtOakV5TVRrNFkyUXhZamt4WkdFd1lqSXpOamhoT1NJc0luUmhaeUk2SWlKOQ==', 1793176294);

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
('01a18188-10fd-728b-b113-5f31478971f9', 'BAJK_STAFF', 'BAJK Staff', 'بی اے جے کے کا عملہ', 0, NULL, 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10fe-7120-b324-549471cf515c', 'MINOR', 'Minor', 'نابالغ', 0, NULL, 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10fe-7120-b324-549471f52de2', 'PHOTO_ACCOUNT', 'Photo Account', 'فوٹو اکاؤنٹ', 0, NULL, 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-10ff-73ec-ad18-0c2a95224577', 'MUSTAHIQEEN_ZAKAT', 'Mustahiqeen-e-Zakat', 'مستحقین زکوٰۃ', 0, NULL, 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1100-72dd-9947-1dbc0be25cdb', 'PARDA_NASHEEN', 'Parda Nasheen', 'پردہ نشین', 0, NULL, 0, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1100-72dd-9947-1dbc0bf1ea65', 'STUDENT', 'Student', 'طالب علم', 0, NULL, 0, 6, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1101-72e1-b7bd-8ae75ecc227e', 'VISUALLY_IMPAIRED', 'Visually Impaired Person/Blind', 'کمزور بصارت/نابینا افراد', 0, NULL, 0, 7, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1102-7077-8241-c01c83eaa9f3', 'DISASTER_IDP', 'Disaster Affectees/IDPs', 'آفت زدہ/نقل مکانی کرنے والے', 0, NULL, 0, 8, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1102-7077-8241-c01c84dff5fb', 'REGISTERED_ALIEN', 'Registered Alien', 'رجسٹرڈ غیر ملکی', 0, NULL, 0, 9, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1103-728a-8d69-3257dcc7587e', 'WIDOW', 'Widow', 'بیوہ', 0, NULL, 0, 10, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1104-70d4-b74c-d45d055f0f97', 'PENSIONERS', 'Pensioners', 'پنشنرز', 0, NULL, 0, 11, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1104-70d4-b74c-d45d05d6ae48', 'SENIOR_CITIZEN', 'Senior Citizen', 'بزرگ شہری', 0, NULL, 0, 12, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1105-71b0-9dfd-12257885c936', 'GOVT_EMP', 'Govt. Emp/Semi Govt. Emp', 'سرکاری/نیم سرکاری ملازم', 0, NULL, 0, 13, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1106-720f-b503-45bfdf75e5ca', 'PENSION', 'Pension', 'پنشن', 0, NULL, 0, 14, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1106-720f-b503-45bfdfba9799', 'SALARY', 'Salary', 'تنخواہ', 0, NULL, 0, 15, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1107-71d2-88c8-566e51d4f927', 'PHYSICAL_HANDICAP', 'Physically Handicapped', 'معذور افراد', 0, NULL, 0, 16, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1107-71d2-88c8-566e52d4e214', 'NON_RESIDENT', 'Non-Resident (in years)', 'بیرون ملک رہائشی', 1, 'Time since residing out side Pakistan (in years)', 0, 17, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1108-72b8-8d33-c9408ca0f231', 'ILLITERATE', 'Illiterate', 'ناخواندہ', 0, NULL, 0, 18, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1109-7285-a856-b65f30aaf16e', 'SPECIAL_OTHERS', 'Others', 'دیگر', 0, NULL, 1, 19, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-113b-723b-bb86-16cad692cedd', 'E_STATEMENT', 'BAJK E-Statement', 'بی اے جے کے ای اسٹیٹمنٹ', 1, 'both', 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-113b-723b-bb86-16cad734ba50', 'POST_COURIER', 'Mail by Post/Courier', 'بذریعہ ڈاک یا کورئیر', 0, 'both', 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-113c-70cd-ab61-3171f29fa9a1', 'HOLD_MAIL', 'Hold Mail Facility', 'ڈاک روکنے کی سہولت', 0, 'both', 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-113c-70cd-ab61-3171f2ec00a9', 'MONTHLY', 'Monthly', 'ماہانہ', 1, 'both', 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-113d-73e0-9cd9-7ea1ffde4299', 'QUARTERLY', 'Quarterly', 'سہ ماہی', 3, 'both', 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-113d-73e0-9cd9-7ea200c4a4ec', 'SEMI_ANNUAL', 'Six Monthly / Semi-Annually', 'ششماہی', 6, 'both', 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-113e-70f1-8756-f013f981fdd2', 'YEARLY', 'Yearly', 'سالانہ', 12, 'individual', 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-114d-7298-9c7e-0f25b3974381', 'CASH', 'Cash', 'نقد', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-114d-7298-9c7e-0f25b488c86f', 'CLEARING', 'Clearing', 'کلیئرنگ', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-114e-70c9-aaf0-4ed7cbfa5a56', 'REMITTANCE', 'Remittance', 'ترسیل', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-114e-70c9-aaf0-4ed7cc1dc0dd', 'COLLECTION', 'Collection', 'کلیکشن', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-114f-71b3-94e2-040474c95d1e', 'MODE_OTHERS', 'Others', 'دیگر', 1, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
(1, 1, NULL, NULL, NULL, 0, 'Branch 0001-1-1', 'manager0001@bankajk.com', '2025-12-12 11:40:05', '$2y$12$nI9HHr9.Xnvy7g1hM1ALf.Us7BzW72sGvB3ifLKG/JQTpeIdWUcsu', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:05', '2026-10-28 08:04:45'),
(2, 2, NULL, NULL, NULL, 0, 'Branch 0002-9-4', 'manager0002@bankajk.com', '2025-12-12 11:40:06', '$2y$12$6mGMmCBEc1E22Axn7rp0ve2plaKkKh7cOS/Qj4JrUmBcIogqrVReS', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:06', '2026-10-28 08:04:45'),
(3, 3, NULL, NULL, NULL, 0, 'Branch 0003-9-4', 'manager0003@bankajk.com', '2025-12-12 11:40:06', '$2y$12$6pWDMoPk6YgeVd59ZQx7L.TGwrUsz.bLU/zpZmiRveD4PlhPP3nMe', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:06', '2026-10-28 08:04:45'),
(4, 4, NULL, NULL, NULL, 0, 'Branch 0004-9-4', 'manager0004@bankajk.com', '2025-12-12 11:40:06', '$2y$12$3Mab9VRnyXn6WFmiJWSmyuKPksptQ4xmSeIWq95GCnraF8.AWqvkm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:06', '2026-10-28 08:04:45'),
(5, 5, NULL, NULL, NULL, 0, 'Branch 0005-8-3', 'manager0005@bankajk.com', '2025-12-12 11:40:06', '$2y$12$15GkdnYdImh8np1wwqEzm.CXuECV8fEgoHpnKJkrz/UgAxDvJD.MG', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:06', '2026-10-28 08:04:45'),
(6, 6, NULL, NULL, NULL, 0, 'Branch 0006-5-2', 'manager0006@bankajk.com', '2025-12-12 11:40:07', '$2y$12$t9pZqsJNMpUWUKwUNnTVMeS6Wn359UfHY44oLvJMWNVekOsnLx0rm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:07', '2026-10-28 08:04:45'),
(7, 7, NULL, NULL, NULL, 0, 'Branch 0007-8-3', 'manager0007@bankajk.com', '2025-12-12 11:40:07', '$2y$12$3t7.SJic87tlRhYVBktFIuXS1cLSJqXNLs5WLjjeyxF0eImpzjKpa', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:07', '2026-10-28 08:04:45'),
(8, 8, NULL, NULL, NULL, 0, 'Branch 0008-2-1', 'manager0008@bankajk.com', '2025-12-12 11:40:07', '$2y$12$5sUOmI1LwlmsQGcbj/6DhOK55z.jB99q4dBMbAK030POBIpmH7ROK', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:07', '2026-10-28 08:04:45'),
(9, 9, NULL, NULL, NULL, 0, 'Branch 0009-9-4', 'manager0009@bankajk.com', '2025-12-12 11:40:07', '$2y$12$bcDPpg/0FOFmfbvBWTPQze4ryrRhBrade7QNFRTkMAZ6ZYNFo.SuC', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:07', '2026-10-28 08:04:45'),
(10, 10, NULL, NULL, NULL, 0, 'Branch 0010-4-2', 'manager0010@bankajk.com', '2025-12-12 11:40:08', '$2y$12$qz2fA4yHUY9dwHkVYHkbf.gX6sQMcE/dxlY.YgBl4hNwqd3N40GLC', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:08', '2026-10-28 08:04:45'),
(11, 11, NULL, NULL, NULL, 0, 'Branch 0011-4-2', 'manager0011@bankajk.com', '2025-12-12 11:40:08', '$2y$12$JeZrpT2pNxM1PdyEmtcGXuYAg5Pj7Aqm/uXODiP0VJaRfIdCKyeR.', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:08', '2026-10-28 08:04:45'),
(12, 12, NULL, NULL, NULL, 0, 'Branch 0012-10-4', 'manager0012@bankajk.com', '2025-12-12 11:40:08', '$2y$12$HcWoBQCKFqkJHFgz.ZCTnOIC2DSH45VnNY1dhWq0UUdsuuBXVDAym', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:08', '2026-10-28 08:04:45'),
(13, 13, NULL, NULL, NULL, 0, 'Branch 0013-5-2', 'manager0013@bankajk.com', '2025-12-12 11:40:08', '$2y$12$UW93vt.9ByigtcgVnaExK.0O66zNsKaI9R1lWWzuYfvXtMOlBaTYu', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:08', '2026-10-28 08:04:45'),
(14, 14, NULL, NULL, NULL, 0, 'Branch 0014-8-3', 'manager0014@bankajk.com', '2025-12-12 11:40:09', '$2y$12$t3xhSVn0afhRdzeNLcsC2uT9ejKB6l6VutyCbu5GE5V.qKDbhx4gO', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:09', '2026-10-28 08:04:45'),
(15, 15, NULL, NULL, NULL, 0, 'Branch 0015-9-4', 'manager0015@bankajk.com', '2025-12-12 11:40:09', '$2y$12$yrCyQ2udN7YDzz8PR4Tode0xDupZpz865EoCtqTViQTj8R3tE4IoG', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:09', '2026-10-28 08:04:45'),
(16, 16, NULL, NULL, NULL, 0, 'Branch 0016-5-2', 'manager0016@bankajk.com', '2025-12-12 11:40:09', '$2y$12$znO4DrwcCDo8FwzkwstRxeKi90Pn6JLGd5qKJx9giDjVmPvTtyaAa', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:09', '2026-10-28 08:04:45'),
(17, 17, NULL, NULL, NULL, 0, 'Branch 0017-1-1', 'manager0017@bankajk.com', '2025-12-12 11:40:10', '$2y$12$o3CfmbzIrz6uesEPuwh0NeloEjj4wXQ6jgupBKQ99K6PWhwXIhN3O', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:10', '2026-10-28 08:04:45'),
(18, 18, NULL, NULL, NULL, 0, 'Branch 0018-1-1', 'manager0018@bankajk.com', '2025-12-12 11:40:10', '$2y$12$omQWW.mWbrK3qmIu9vycmuQY7w87pmRgrphiwifWrUfmZxN21UZRW', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:10', '2026-10-28 08:04:45'),
(19, 19, NULL, NULL, NULL, 0, 'Branch 0019-1-1', 'manager0019@bankajk.com', '2025-12-12 11:40:10', '$2y$12$D7JK.27Eb9F/e.xTyM9sYO3UIpgGpjjBf.FjUJrJQ/76WKQTZvr0O', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:10', '2026-10-28 08:04:45'),
(20, 20, NULL, NULL, NULL, 0, 'Branch 0020-8-3', 'manager0020@bankajk.com', '2025-12-12 11:40:10', '$2y$12$gTg0s3z5xTsQEpz3FLVwB.i1nbeqenMxcu0RcMYMx0EyEljvZ/RMW', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:10', '2026-10-28 08:04:45'),
(21, 21, NULL, NULL, NULL, 0, 'Branch 0021-8-3', 'manager0021@bankajk.com', '2025-12-12 11:40:11', '$2y$12$lSgMu.SEkB1po2iSg0osyuSmNJqOM8xBgPIQZOpORS2TQCmTltNhi', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:11', '2026-10-28 08:04:45'),
(22, 22, NULL, NULL, NULL, 0, 'Branch 0022-1-1', 'manager0022@bankajk.com', '2025-12-12 11:40:11', '$2y$12$ukgsrjp.1NY7nqXmGTeocuLt2PwtNzNBDHj9jIIVH1pVQBbC/chW6', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:11', '2026-10-28 08:04:45'),
(23, 23, NULL, NULL, NULL, 0, 'Branch 0023-4-2', 'manager0023@bankajk.com', '2025-12-12 11:40:11', '$2y$12$73zfX6T92/6HiFFR6ehlxuZg.TO.XIh5XnCXP0QoGveP4HoU/v8di', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:11', '2026-10-28 08:04:45'),
(24, 24, NULL, NULL, NULL, 0, 'Branch 0024-1-1', 'manager0024@bankajk.com', '2025-12-12 11:40:11', '$2y$12$gyfGPID/UWJs.1mGfITAxO1qipyKavYndW91Ph3X6X5EYbDhCMflK', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:11', '2026-10-28 08:04:45'),
(25, 25, NULL, NULL, NULL, 0, 'Branch 0025-5-2', 'manager0025@bankajk.com', '2025-12-12 11:40:12', '$2y$12$X0gJkdIP9F6jauenfbJ9A.ZdKF9DZvxMEs8K8MVCwvL3XWfBjsRMK', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:12', '2026-10-28 08:04:45'),
(26, 26, NULL, NULL, NULL, 0, 'Branch 0026-8-3', 'manager0026@bankajk.com', '2025-12-12 11:40:12', '$2y$12$hOwRPPaY5Ep2DAiWo1wAFuprLPmb344Yz8QYEFjPezwiHXvKRNXM6', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:12', '2026-10-28 08:04:45'),
(27, 27, NULL, NULL, NULL, 0, 'Branch 0027-9-4', 'manager0027@bankajk.com', '2025-12-12 11:40:12', '$2y$12$jZKy746FDs9oHNhYllHs0.tZoZCM7GoxzGAXAzS1m5begTIiMMP9G', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:12', '2026-10-28 08:04:45'),
(28, 28, NULL, NULL, NULL, 0, 'Branch 0028-1-1', 'manager0028@bankajk.com', '2025-12-12 11:40:12', '$2y$12$7i1y3FPAGWQXl465Ymj2meuJxDqnFaYD5rgbjGf3tI/7XLrKqaDi6', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:12', '2026-10-28 08:04:45'),
(29, 29, NULL, NULL, NULL, 0, 'Branch 0029-10-4', 'manager0029@bankajk.com', '2025-12-12 11:40:13', '$2y$12$CPSPWAsH5u22Bw34TD5cwOUADFLSm6t/jbMPQsyVqkKnjl.3D7TZa', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:13', '2026-10-28 08:04:45'),
(30, 30, NULL, NULL, NULL, 0, 'Branch 0030-5-2', 'manager0030@bankajk.com', '2025-12-12 11:40:13', '$2y$12$ND4UVseXqCcVPMhtqrN.HO2IXaOKkb5AFRgXw9sq9DRq.hmLkVARq', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:13', '2026-10-28 08:04:45'),
(31, 31, NULL, NULL, NULL, 0, 'Branch 0031-8-3', 'manager0031@bankajk.com', '2025-12-12 11:40:13', '$2y$12$AxMljGg2PFZ68Qfct./KJet6N2QUhqv5e8Oc6UBhHvSHHDH1NcOZK', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:13', '2026-10-28 08:04:45'),
(32, 32, NULL, NULL, NULL, 0, 'Branch 0032-7-2', 'manager0032@bankajk.com', '2025-12-12 11:40:14', '$2y$12$jXtgPqHvBFz1cVVzKxU1Y.G/5AiezaKvA6GPEvyyabpK2g/SNgrg.', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:14', '2026-10-28 08:04:45'),
(33, 33, NULL, NULL, NULL, 0, 'Branch 0033-10-4', 'manager0033@bankajk.com', '2025-12-12 11:40:14', '$2y$12$Isjb4PF08zWsVoW7xItWj.hcuD/1nVm6cCwWo91.HE5f0ds5DbmZu', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:14', '2026-10-28 08:04:45'),
(34, 34, NULL, NULL, NULL, 0, 'Branch 0034-3-1', 'manager0034@bankajk.com', '2025-12-12 11:40:14', '$2y$12$eUWPOUr416HIUuQ4BJDpa.YOuw1URS9G1iQ6PhhNqGIeEk8ddDuxu', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:14', '2026-10-28 08:04:45'),
(35, 35, NULL, NULL, NULL, 0, 'Branch 0035-10-4', 'manager0035@bankajk.com', '2025-12-12 11:40:15', '$2y$12$qnWXuX7uPUWAyqLZCW/nIeeqeeB57MzS7ivW6eti0Lyyigd/P0tIu', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:15', '2026-10-28 08:04:45'),
(36, 36, NULL, NULL, NULL, 0, 'Branch 0036-8-3', 'manager0036@bankajk.com', '2025-12-12 11:40:15', '$2y$12$UCZ0hNCcP6OJY9aionB4uedVoXrc0WqJk1sSEDP3SAaIPS0KX3s6W', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:15', '2026-10-28 08:04:45'),
(37, 37, NULL, NULL, NULL, 0, 'Branch 0037-3-1', 'manager0037@bankajk.com', '2025-12-12 11:40:15', '$2y$12$j2mF3fN80tf7./AcrEyx/euYfjWtQBF6HxpyiHSnBPrNFOp9GTZ5.', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:15', '2026-10-28 08:04:45'),
(38, 38, NULL, NULL, NULL, 0, 'Branch 0038-6-2', 'manager0038@bankajk.com', '2025-12-12 11:40:15', '$2y$12$nTx3H6p9ntEGRnz2SWTMdufdfhHeS2BJCAhtP08DwTZYdPa/wIdzG', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:15', '2026-10-28 08:04:45'),
(39, 39, NULL, NULL, NULL, 0, 'Branch 0039-5-2', 'manager0039@bankajk.com', '2025-12-12 11:40:16', '$2y$12$LJ6VFEHP2K.Xhds4o7UW0efNtQgacvSTh2dmWpFtBOEO4q/WyXkwy', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:16', '2026-10-28 08:04:45'),
(40, 40, NULL, NULL, NULL, 0, 'Branch 0040-1-1', 'manager0040@bankajk.com', '2025-12-12 11:40:16', '$2y$12$QcBUt4lnOhAGigIopLvUc.C39v/xngoWcA0CowCU/JX5IM0k81L2.', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:16', '2026-10-28 08:04:45'),
(41, 41, NULL, NULL, NULL, 0, 'Branch 0041-7-2', 'manager0041@bankajk.com', '2025-12-12 11:40:16', '$2y$12$E6qUK9lFHSs51UNBDpWQuenB5BKqlomW6CdbNZmqlLnW3ynf8mSsW', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:16', '2026-10-28 08:04:45'),
(42, 42, NULL, NULL, NULL, 0, 'Branch 0042-8-3', 'manager0042@bankajk.com', '2025-12-12 11:40:16', '$2y$12$6/iVxgRkusAxYxlUoYwJk.6vdk.sGGA/UKrUCFpmbmam/7ylZtKc.', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:16', '2026-10-28 08:04:45'),
(43, 43, NULL, NULL, NULL, 0, 'Branch 0043-2-1', 'manager0043@bankajk.com', '2025-12-12 11:40:17', '$2y$12$/Wt8xUsxGJpDKg1GhBceo.II7IGGWsPLt.7nWIAIJ4Vn0wYqE6xPm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:17', '2026-10-28 08:04:45'),
(44, 44, NULL, NULL, NULL, 0, 'Branch 0044-8-3', 'manager0044@bankajk.com', '2025-12-12 11:40:17', '$2y$12$egSZsXAq7F0RkreaiVQv5O2NVtByOiKRd8SrUmBqPQTigca8ubsgi', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:17', '2026-10-28 08:04:45'),
(45, 45, NULL, NULL, NULL, 0, 'Branch 0045-9-4', 'manager0045@bankajk.com', '2025-12-12 11:40:17', '$2y$12$akcXWOgXGPbeQgDHTf2Rn.fyYqcM4CZNQWgtyjUN.9m/VN4FX05Oy', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:17', '2026-10-28 08:04:45'),
(46, 46, NULL, NULL, NULL, 0, 'Branch 0046-9-4', 'manager0046@bankajk.com', '2025-12-12 11:40:18', '$2y$12$XRvuiZ7gApX3oESWgPZ69.gwoqBUPcHlk7sh8IhrL6No1iGhK/XNy', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:18', '2026-10-28 08:04:45'),
(47, 47, NULL, NULL, NULL, 0, 'Branch 0047-9-4', 'manager0047@bankajk.com', '2025-12-12 11:40:18', '$2y$12$ZGtnlGdnEACAau9rjQC0IeQPUS/U9oT0ouI2AT157vK0H1Pva4vb2', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:18', '2026-10-28 08:04:45'),
(48, 48, NULL, NULL, NULL, 0, 'Branch 0048-8-3', 'manager0048@bankajk.com', '2025-12-12 11:40:18', '$2y$12$Sx/cujTlPf8ToB0V9CxXWOLSJJniKderyblVE/OMM/HId5wULUVkm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:18', '2026-10-28 08:04:45'),
(49, 49, NULL, NULL, NULL, 0, 'Branch 0049-7-2', 'manager0049@bankajk.com', '2025-12-12 11:40:18', '$2y$12$GkJBR7GbGajpYN9bFTFBruRgBrw2Fka2tPPIf6PtaLr.AZK8znphe', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:18', '2026-10-28 08:04:45'),
(50, 50, NULL, NULL, NULL, 0, 'Branch 0050-5-2', 'manager0050@bankajk.com', '2025-12-12 11:40:19', '$2y$12$TYWYhJ5ptklWKvtwITvsPuuE2lRDYWNrEbDnlXAvIq2GRN6bQ1mmy', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:19', '2026-10-28 08:04:45'),
(51, 51, NULL, NULL, NULL, 0, 'Branch 0051-8-3', 'manager0051@bankajk.com', '2025-12-12 11:40:19', '$2y$12$EVKblRbVPZiqYLd/qNhC7eWA2IWL1Yhyr3kn4EmAtjsMTI9VXgtam', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:19', '2026-10-28 08:04:45'),
(52, 52, NULL, NULL, NULL, 0, 'Branch 0052-2-1', 'manager0052@bankajk.com', '2025-12-12 11:40:19', '$2y$12$xC3YdvMAo4Zn6HekY3x72uL4CnOWNo58U8qg6InqQyELjsw.Oz2Jy', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:19', '2026-10-28 08:04:45'),
(53, 53, NULL, NULL, NULL, 0, 'Branch 0053-1-1', 'manager0053@bankajk.com', '2025-12-12 11:40:19', '$2y$12$Seu0Kla/L9CxV7als5j/geXirE4aGkDzRE5wIGFxzC0S2/gB.vGDy', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:19', '2026-10-28 08:04:45'),
(54, 54, NULL, NULL, NULL, 0, 'Branch 0054-9-4', 'manager0054@bankajk.com', '2025-12-12 11:40:20', '$2y$12$bHICuFrg3oj5HuZ8tBRA3u23EnxN3ovxN0IXwtQqpDIA3o8rPMoZK', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:20', '2026-10-28 08:04:45'),
(55, 55, NULL, NULL, NULL, 0, 'Branch 0055-8-3', 'manager0055@bankajk.com', '2025-12-12 11:40:20', '$2y$12$CnRs5XCmfDuiBvMjPAKKBOOhj8421taS5J4dhdFJPamvMyRERj3Kq', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:20', '2026-10-28 08:04:45'),
(56, 56, NULL, NULL, NULL, 0, 'Branch 0056-5-2', 'manager0056@bankajk.com', '2025-12-12 11:40:20', '$2y$12$azo5Q/6biTAzpF5jrkBYI.vD7m78R04QSFI2lNn50UDrmW7VPnz76', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:20', '2026-10-28 08:04:45'),
(57, 57, NULL, NULL, NULL, 0, 'Branch 0057-9-4', 'manager0057@bankajk.com', '2025-12-12 11:40:21', '$2y$12$vTmkgcUUetKjRVsIl/vIqed08V5iHBfxuxpfUPBgXBccI.15fjwme', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:21', '2026-10-28 08:04:45'),
(58, 58, NULL, NULL, NULL, 0, 'Branch 0058-9-4', 'manager0058@bankajk.com', '2025-12-12 11:40:21', '$2y$12$peDJKfScz3umPDmcTzasvOlk6XVbFoFho7T0pz7k4zX1CVgReBHxa', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:21', '2026-10-28 08:04:45'),
(59, 59, NULL, NULL, NULL, 0, 'Branch 0059-10-4', 'manager0059@bankajk.com', '2025-12-12 11:40:21', '$2y$12$b.iOs3UJpSnBkuc5HCWD0evyBBd8XYCkxXR0/yUernFUR81/EQFki', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:21', '2026-10-28 08:04:45'),
(60, 60, NULL, NULL, NULL, 0, 'Branch 0060-5-2', 'manager0060@bankajk.com', '2025-12-12 11:40:21', '$2y$12$cEzDXkHu0p53PZCQJ0dlyusL8bY9d4perow4lyDTEoQX37PKbqbXm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:21', '2026-10-28 08:04:45'),
(61, 61, NULL, NULL, NULL, 0, 'Branch 0061-1-1', 'manager0061@bankajk.com', '2025-12-12 11:40:22', '$2y$12$doQcU5knxc4mhJ7OM97FiuQAifn0CiEhZefOFgpMsYVAx0iK5RJq2', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:22', '2026-10-28 08:04:45'),
(62, 62, NULL, NULL, NULL, 0, 'Branch 0062-3-1', 'manager0062@bankajk.com', '2025-12-12 11:40:22', '$2y$12$ewe3GgB4lICAq3d6xd4gO.Le/HlrEBZrD11E2IyTLj3/RNM9XlaWm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:22', '2026-10-28 08:04:45'),
(63, 63, NULL, NULL, NULL, 0, 'Branch 0063-2-1', 'manager0063@bankajk.com', '2025-12-12 11:40:22', '$2y$12$Vk9Kg7ktciihr3qrk9KLoeROz76IcJkfyPL6CeMv5.1TwqzvQsIqa', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:22', '2026-10-28 08:04:45'),
(64, 64, NULL, NULL, NULL, 0, 'Branch 0064-8-3', 'manager0064@bankajk.com', '2025-12-12 11:40:22', '$2y$12$omTK7tsJyrOmTK4DvKC8.OylpACr68vtDhfzDTg8zFxg/8w/.I0vq', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:22', '2026-10-28 08:04:45'),
(65, 65, NULL, NULL, NULL, 0, 'Branch 0065-8-3', 'manager0065@bankajk.com', '2025-12-12 11:40:23', '$2y$12$ehKPEaa85e73IHE1UboNie.7onbXVMroB5.6fSebM.l5Qyu/PEetm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:23', '2026-10-28 08:04:45'),
(66, 66, NULL, NULL, NULL, 0, 'Branch 0066-8-3', 'manager0066@bankajk.com', '2025-12-12 11:40:23', '$2y$12$AHRgmSQ2Ew3wB5pcLtTgLuPdxbtJpE.aIIoMCcIYRSt29l7Lv.ORO', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:23', '2026-10-28 08:04:45'),
(67, 67, NULL, NULL, NULL, 0, 'Branch 0067-1-1', 'manager0067@bankajk.com', '2025-12-12 11:40:23', '$2y$12$D5iEcai75ZIoBOaSBuqyPuiIww.VVmGaBjWrzAOhcCZvOpyQRjR2K', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:23', '2026-10-28 08:04:45'),
(68, 68, NULL, NULL, NULL, 0, 'Branch 0068-7-2', 'manager0068@bankajk.com', '2025-12-12 11:40:24', '$2y$12$HIk3mY7Cm84p.wxF8YznhObRznlRL8Ggwa4kp4HSOQMGZankPy33e', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:24', '2026-10-28 08:04:45'),
(69, 69, NULL, NULL, NULL, 0, 'Branch 0069-2-1', 'manager0069@bankajk.com', '2025-12-12 11:40:24', '$2y$12$ReY588veN4Jx6cpnQDV05.wRdL./hmn8WhanMoY1/O0h0thNRzD6W', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:24', '2026-10-28 08:04:45'),
(70, 70, NULL, NULL, NULL, 0, 'Branch 0070-1-1', 'manager0070@bankajk.com', '2025-12-12 11:40:24', '$2y$12$HqkkLOueEHN.f6doyD07neDQDkFUDGSUHdva4xgTyuL.mfu8ttd2i', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:24', '2026-10-28 08:04:45'),
(71, 71, NULL, NULL, NULL, 0, 'Branch 0071-7-2', 'manager0071@bankajk.com', '2025-12-12 11:40:24', '$2y$12$ME92VLJoxOmV6sRb0ys8Ue6J1bwgU9ruKtACSyjDLxkHhn2URtsra', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:24', '2026-10-28 08:04:45'),
(72, 72, NULL, NULL, NULL, 0, 'Branch 0072-7-2', 'manager0072@bankajk.com', '2025-12-12 11:40:25', '$2y$12$LZSPnGizQmXjJLCc/vxBMeiAN9jQv11b0XKGanKS3HM1rGNmA5oEG', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:25', '2026-10-28 08:04:45'),
(73, 73, NULL, NULL, NULL, 0, 'Branch 0073-10-4', 'manager0073@bankajk.com', '2025-12-12 11:40:25', '$2y$12$1WEWZKYbDxRUuSeC8S4WjuRlOk1RwDPOdby1tcICqq7nlSEVuujva', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:25', '2026-10-28 08:04:45'),
(74, 74, NULL, NULL, NULL, 0, 'Branch 0074-7-2', 'manager0074@bankajk.com', '2025-12-12 11:40:25', '$2y$12$1yDFZwlzKHP9JJyrQODly.kOkUSB6pnIH0gTADSo8b8YawDlqGErm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:25', '2026-10-28 08:04:45'),
(75, 75, NULL, NULL, NULL, 0, 'Branch 0075-8-3', 'manager0075@bankajk.com', '2025-12-12 11:40:25', '$2y$12$WC19gYO.sUoSPHJUmM6V9OneWnItH4GWWdgbgryrRJ1QvLnroy7iK', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:25', '2026-10-28 08:04:45'),
(76, 76, NULL, NULL, NULL, 0, 'Branch 0076-3-1', 'manager0076@bankajk.com', '2025-12-12 11:40:26', '$2y$12$EXuNpuJe3wjK2In28BgNqu9Askka8.EF81ZhzqpotSPKeLpeRmuTm', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:26', '2026-10-28 08:04:45'),
(77, 77, NULL, NULL, NULL, 0, 'Branch 0077-1-1', 'manager0077@bankajk.com', '2025-12-12 11:40:26', '$2y$12$CN/Dy.lnLpuEv3k/JqGM8OjmIQY.tTDOFY0SNUw/.kdJmvgotIGOi', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:26', '2026-10-28 08:04:45'),
(78, 78, NULL, NULL, NULL, 0, 'Branch 0078-10-4', 'manager0078@bankajk.com', '2025-12-12 11:40:26', '$2y$12$zNKQEWOnxSDoXtIvi2D5CefYbch18ds4fOc.u3MV5j2/ZZKqJCrIe', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:26', '2026-10-28 08:04:45'),
(79, 79, NULL, NULL, NULL, 0, 'Branch 0079-9-4', 'manager0079@bankajk.com', '2025-12-12 11:40:27', '$2y$12$2MISQRntNL4aFQaZO.wX/OYlPkKdBVk1AtZEAWDbi/wgtO2FRdtiC', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:27', '2026-10-28 08:04:45'),
(80, 80, NULL, NULL, NULL, 0, 'Branch 0080-9-4', 'manager0080@bankajk.com', '2025-12-12 11:40:27', '$2y$12$zt2pMOfbe8u6Zfty73rm/eTf07mDr2fIE106dWlsZzNjoF/ttL81S', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:27', '2026-10-28 08:04:45'),
(81, 81, NULL, NULL, NULL, 0, 'Branch 0081-9-4', 'manager0081@bankajk.com', '2025-12-12 11:40:27', '$2y$12$oH3P3lZOT.2EY.1vqqsxZulVmuDSHO/BY9ErwCWUFl.u4.z82Xnlu', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:27', '2026-10-28 08:04:45'),
(82, 82, NULL, NULL, NULL, 0, 'Branch 0082-8-3', 'manager0082@bankajk.com', '2025-12-12 11:40:27', '$2y$12$zGF5B.OSihNRPleFAHZH/edNLF1DkTgWbaFGmR6nf8nG.IIs2wF/q', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:27', '2026-10-28 08:04:45'),
(83, 83, NULL, NULL, NULL, 0, 'Branch 0083-5-2', 'manager0083@bankajk.com', '2025-12-12 11:40:28', '$2y$12$HJq3GTD5ILbT61zVbfXgC.BZzXtC7.keO5j3T/RpINgrqwXdbV2ea', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:28', '2026-10-28 08:04:45'),
(84, 84, NULL, NULL, NULL, 0, 'Branch 0084-3-1', 'manager0084@bankajk.com', '2025-12-12 11:40:28', '$2y$12$PWPJuNxKUTQam7yD5PRiPOWZ3Vd9vxY//pFGYQWhrWWbIucbuCb4S', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:28', '2026-10-28 08:04:45'),
(85, 85, NULL, NULL, NULL, 0, 'Branch 0085-1-1', 'manager0085@bankajk.com', '2025-12-12 11:40:28', '$2y$12$NyS5QwGByShDILcCgro2F.E76DWuCFcQLtZbTUDjqlyf3UOhXJU0O', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:28', '2026-10-28 08:04:45'),
(86, 86, NULL, NULL, NULL, 0, 'Branch 0086-4-2', 'manager0086@bankajk.com', '2025-12-12 11:40:28', '$2y$12$QwF/GRBWGbLLgBevnAZpZODOmXUO1oxm6zKPA.lb6NnkoxPdpEsyW', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:28', '2026-10-28 08:04:45'),
(87, 87, NULL, NULL, NULL, 0, 'Branch 0087-9-4', 'manager0087@bankajk.com', '2025-12-12 11:40:29', '$2y$12$R1Qi373Vh5NuUeMSO8jM2OY6Mw2MeuKUrSjJ4ugm1u.QLJYwH/m76', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', NULL, '2025-12-12 11:40:29', '2026-10-28 08:04:45'),
(88, NULL, NULL, NULL, NULL, 0, 'Ali Raza Marchal', 'superadmin@example.com', '2025-12-12 11:40:29', '$2y$12$a33h7u8P6tgrJaxkw5zer.4kelspC8wY1uorT1CgTTPTJrRwsErnK', NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'Yes', NULL, '2025-12-12 11:40:29', '2025-12-12 11:40:29');

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
('01a18188-114a-706c-9197-07e3b4004d2c', 'GIFT', 'Gift', 'تحفہ', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-114b-7177-80ce-aba2d6ca0bb4', 'PERSONAL_SAVINGS', 'Personal Savings', 'ذاتی بچت', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-114b-7177-80ce-aba2d7178c7c', 'RENTED_PROPERTY_SALES', 'Rented Property/Property Sales', 'کرایہ/جائیداد کی فروخت', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-114c-71ca-93fb-4c2e70c1917d', 'INHERITANCE', 'Inheritance', 'وراثت', 0, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-114c-71ca-93fb-4c2e71b67eb6', 'WEALTH_OTHERS', 'Others', 'دیگر', 1, 5, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
('01a18188-1136-7300-a3af-5bc0f513dae9', 'NON_MUSLIM', 'Non-Muslim', 'غیر مسلم', 0, 1, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1137-73ba-913b-e20418b66d75', 'FOREIGNER', 'Foreigner', 'غیر ملکی', 0, 2, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1137-73ba-913b-e20418e0eb63', 'DUE_TO_FIQAH', 'Due to Fiqah', 'فقہ کی وجہ سے', 0, 3, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20'),
('01a18188-1138-7282-b397-030d4216e8ec', 'ZAKAT_OTHERS', 'Others', 'دیگر', 1, 4, 1, '2026-10-28 08:02:20', '2026-10-28 08:02:20');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=356;

--
-- AUTO_INCREMENT for table `aksic_amortizations`
--
ALTER TABLE `aksic_amortizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

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
