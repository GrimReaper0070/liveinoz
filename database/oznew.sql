-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 12:53 PM
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
-- Database: `oznew`
--

-- --------------------------------------------------------

--
-- Table structure for table `bad_words`
--

CREATE TABLE `bad_words` (
  `id` int(11) NOT NULL,
  `word` varchar(100) NOT NULL,
  `severity` enum('low','medium','high') DEFAULT 'medium',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bad_words`
--

INSERT INTO `bad_words` (`id`, `word`, `severity`, `is_active`, `created_at`) VALUES
(1, 'damn', 'low', 1, '2025-09-23 15:26:31'),
(2, 'hell', 'low', 1, '2025-09-23 15:26:31'),
(3, 'crap', 'low', 1, '2025-09-23 15:26:31'),
(4, 'stupid', 'medium', 1, '2025-09-23 15:26:31'),
(5, 'idiot', 'medium', 1, '2025-09-23 15:26:31'),
(6, 'moron', 'medium', 1, '2025-09-23 15:26:31'),
(7, 'asshole', 'high', 1, '2025-09-23 15:26:31'),
(8, 'bastard', 'high', 1, '2025-09-23 15:26:31'),
(9, 'fuck', 'high', 1, '2025-09-23 15:26:31'),
(10, 'shit', 'high', 1, '2025-09-23 15:26:31'),
(11, 'bitch', 'high', 1, '2025-09-23 15:26:31'),
(12, 'cunt', 'high', 1, '2025-09-23 15:26:31');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `content`, `image`, `author_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'live in oz', 'Live in oz chat will be live soon', 'uploads/blog/blog_68ea5ad8950e6.png', 2, 'published', '2025-10-11 13:25:44', '2025-10-11 15:29:53'),
(2, 'How AI Is Transforming Everyday Life in 2025', 'Artificial Intelligence has moved far beyond chatbots and smart assistants. In 2025, AI is deeply integrated into daily routines — from personalized healthcare to real-time translation glasses.\r\nModern smart homes can now anticipate residents’ needs, adjusting lighting, temperature, and even meals based on daily habits. In the workplace, AI-powered co-pilots help draft emails, generate reports, and analyze data instantly.\r\n\r\nBut with convenience comes responsibility. Ethical AI use and privacy protection have become key global discussions. As AI continues to evolve, the focus is shifting from “what AI can do” to “what AI should do.”', 'uploads/blog/blog_6912c9fb7d610.jpg', 2, 'published', '2025-11-11 05:30:35', '2025-11-11 05:30:35'),
(3, 'The Rise of Sustainable Tech: Going Green Without Sacrificing Performance', 'Sustainability is no longer just a buzzword — it’s a business standard. In 2025, companies are adopting “green computing” by designing energy-efficient data centers and recyclable hardware.\r\nSolar-powered gadgets, biodegradable packaging, and carbon-neutral manufacturing are becoming mainstream. Even the gaming industry is exploring low-power GPUs and cloud-based optimization to reduce energy waste.\r\n\r\nPro tip: If you’re upgrading devices this year, look for the “Eco Efficiency” certification — it means the product meets new global sustainability benchmarks.\r\n\r\nTakeaway: The future of tech is not just faster — it’s cleaner.', 'uploads/blog/blog_6912ca3513f72.png', 2, 'published', '2025-11-11 05:31:33', '2025-11-11 05:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `boosts`
--

CREATE TABLE `boosts` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `stripe_payment_id` varchar(255) DEFAULT NULL,
  `activated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','expired','canceled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boosts`
--

INSERT INTO `boosts` (`id`, `room_id`, `user_id`, `city`, `cost`, `stripe_payment_id`, `activated_at`, `expires_at`, `status`, `created_at`) VALUES
(15, 67, 2, 'Brisbane', 10.00, 'pi_3SSbeDDbk9v7YnqR0UPPivOl', '2025-11-12 10:51:17', '2025-11-15 05:51:17', 'active', '2025-11-12 10:51:17');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room` varchar(100) NOT NULL,
  `state` varchar(10) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `room`, `state`, `message`, `created_at`, `expires_at`) VALUES
(7, 2, 'General', 'GENERAL', 'hi', '2025-09-22 13:01:53', '2025-09-23 13:01:53'),
(12, 2, 'Males', 'WA', 'hello', '2025-09-23 15:15:56', '2025-09-24 15:15:56'),
(18, 2, 'General', 'ACT', 'hello', '2025-09-23 15:43:16', '2025-09-24 15:43:16'),
(19, 2, 'General', 'ACT', 'hello', '2025-09-23 15:43:16', '2025-09-24 15:43:16'),
(20, 2, 'General', 'ACT', 'hello', '2025-09-23 15:43:16', '2025-09-24 15:43:16'),
(21, 2, 'General', 'ACT', 'hello', '2025-09-23 15:43:16', '2025-09-24 15:43:16'),
(22, 2, 'General', 'ACT', 'nice day', '2025-09-23 15:49:49', '2025-09-24 15:49:49'),
(23, 2, 'General', 'ACT', 'nice day', '2025-09-23 15:49:49', '2025-09-24 15:49:49'),
(33, 2, 'General', 'ACT', 'hello', '2025-09-23 16:02:12', '2025-09-24 16:02:12'),
(34, 5, 'General', 'ACT', 'hi', '2025-09-24 12:00:31', NULL),
(35, 5, 'General', 'ACT', 'hi', '2025-09-24 12:00:31', NULL),
(36, 5, 'General', 'ACT', 'hi', '2025-09-26 12:19:41', NULL),
(37, 2, 'General', 'ACT', 'hello', '2025-09-26 12:19:50', NULL),
(38, 2, 'General', 'ACT', 'hello', '2025-09-26 12:19:50', NULL),
(39, 2, 'General', 'ACT', 'hi', '2025-09-26 12:22:18', NULL),
(40, 5, 'General', 'ACT', 'hello', '2025-09-26 12:22:27', NULL),
(41, 2, 'General', 'ACT', 'hi', '2025-09-26 12:38:37', NULL),
(42, 5, 'General', 'ACT', 'hi', '2025-09-26 12:39:22', NULL),
(44, 2, 'General', 'ACT', 'hello', '2025-09-26 12:49:13', NULL),
(46, 2, 'entrepreneurs', 'ACT', 'hi', '2025-10-12 09:50:18', NULL),
(47, 2, 'entrepreneurs', 'ACT', 'hello', '2025-10-12 09:51:08', NULL),
(48, 2, 'Art & Design', 'NSW', 'Test message to Art & Design at 2025-10-12 12:22:40', '2025-10-12 10:22:40', NULL),
(49, 2, 'Gamers & Tech', 'NSW', 'Test message to Gamers & Tech at 2025-10-12 12:22:40', '2025-10-12 10:22:40', NULL),
(50, 2, 'Movies & Series', 'NSW', 'Test message to Movies & Series at 2025-10-12 12:22:40', '2025-10-12 10:22:40', NULL),
(51, 2, 'Photography', 'NSW', 'Test message to Photography at 2025-10-12 12:22:40', '2025-10-12 10:22:40', NULL),
(52, 2, 'Weed', 'NSW', 'Test message to Weed at 2025-10-12 12:22:40', '2025-10-12 10:22:40', NULL),
(53, 2, 'Weed', 'ACT', 'hi', '2025-10-12 10:25:26', NULL),
(54, 2, 'Photography', 'ACT', 'hi', '2025-10-12 10:26:03', NULL),
(55, 2, 'Gamers & Tech', 'ACT', 'hi', '2025-10-12 10:31:23', NULL),
(56, 2, 'Movies & Series', 'ACT', 'hi', '2025-10-12 10:31:31', NULL),
(57, 2, 'Gamers & Tech', 'ACT', 'hey', '2025-10-12 10:35:22', NULL),
(58, 2, 'Help & Support', 'ACT', 'hi', '2025-10-12 10:53:36', NULL),
(59, 2, 'General', 'ACT', 'hello', '2025-10-12 12:24:45', NULL),
(60, 2, 'General', 'ACT', 'hey', '2025-10-12 12:32:41', NULL),
(61, 2, 'Housing', 'ACT', 'hi', '2025-10-12 12:32:50', NULL),
(63, 2, 'General', 'ACT', 'hello', '2025-11-09 05:46:36', NULL),
(66, 16, 'General', 'ACT', 'hi', '2025-11-21 11:32:27', NULL),
(67, 18, 'General', 'ACT', 'hi', '2025-11-21 11:39:47', NULL),
(68, 2, 'Visas & Docs', 'ACT', 'hey', '2025-11-21 12:08:32', NULL),
(69, 19, 'General', 'ACT', 'hey', '2025-11-23 11:11:55', NULL),
(70, 19, 'General', 'ACT', 'hi', '2025-11-23 11:13:42', NULL),
(71, 20, 'General', 'ACT', 'hi', '2025-11-23 11:31:44', NULL),
(72, 18, 'General', 'ACT', 'hi', '2025-11-23 11:32:36', NULL),
(73, 18, 'General', 'ACT', 'hi', '2025-11-23 11:50:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` int(11) NOT NULL,
  `state_code` varchar(5) NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `room_name_es` varchar(100) NOT NULL,
  `room_type` enum('general','housing','jobs','buy_sell','help','off_topic','suggestions','more','daily_static','fitness_static','hobbies_static') DEFAULT 'general',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `color` varchar(20) DEFAULT NULL,
  `emoji` varchar(50) DEFAULT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `state_code`, `city_name`, `room_name`, `room_name_es`, `room_type`, `is_active`, `created_at`, `color`, `emoji`, `display_name`) VALUES
(1, 'NSW', 'Sydney', 'General', 'General', 'general', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(2, 'NSW', 'Sydney', 'Housing', 'Vivienda', 'housing', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(3, 'NSW', 'Sydney', 'Jobs', 'Trabajos', 'jobs', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(4, 'NSW', 'Sydney', 'Buy & Sell', 'Compra y Venta', 'buy_sell', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(5, 'NSW', 'Sydney', 'Help & Support', 'Ayuda y Soporte', 'help', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(6, 'NSW', 'Sydney', 'Off-Topic', 'Fuera de Tema', 'off_topic', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(7, 'NSW', 'Sydney', 'Suggestions', 'Sugerencias', 'suggestions', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(8, 'VIC', 'Melbourne', 'General', 'General', 'general', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(9, 'VIC', 'Melbourne', 'Housing', 'Vivienda', 'housing', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(10, 'VIC', 'Melbourne', 'Jobs', 'Trabajos', 'jobs', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(11, 'VIC', 'Melbourne', 'Buy & Sell', 'Compra y Venta', 'buy_sell', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(12, 'VIC', 'Melbourne', 'Help & Support', 'Ayuda y Soporte', 'help', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(13, 'VIC', 'Melbourne', 'Off-Topic', 'Fuera de Tema', 'off_topic', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(14, 'VIC', 'Melbourne', 'Suggestions', 'Sugerencias', 'suggestions', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(15, 'QLD', 'Brisbane', 'General', 'General', 'general', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(16, 'QLD', 'Brisbane', 'Housing', 'Vivienda', 'housing', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(17, 'QLD', 'Brisbane', 'Jobs', 'Trabajos', 'jobs', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(18, 'QLD', 'Brisbane', 'Buy & Sell', 'Compra y Venta', 'buy_sell', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(19, 'QLD', 'Brisbane', 'Help & Support', 'Ayuda y Soporte', 'help', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(20, 'QLD', 'Brisbane', 'Off-Topic', 'Fuera de Tema', 'off_topic', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(21, 'QLD', 'Brisbane', 'Suggestions', 'Sugerencias', 'suggestions', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(22, 'WA', 'Perth', 'General', 'General', 'general', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(23, 'WA', 'Perth', 'Housing', 'Vivienda', 'housing', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(24, 'WA', 'Perth', 'Jobs', 'Trabajos', 'jobs', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(25, 'WA', 'Perth', 'Buy & Sell', 'Compra y Venta', 'buy_sell', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(26, 'WA', 'Perth', 'Help & Support', 'Ayuda y Soporte', 'help', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(27, 'WA', 'Perth', 'Off-Topic', 'Fuera de Tema', 'off_topic', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(28, 'WA', 'Perth', 'Suggestions', 'Sugerencias', 'suggestions', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(29, 'SA', 'Adelaide', 'General', 'General', 'general', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(30, 'SA', 'Adelaide', 'Housing', 'Vivienda', 'housing', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(31, 'SA', 'Adelaide', 'Jobs', 'Trabajos', 'jobs', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(32, 'SA', 'Adelaide', 'Buy & Sell', 'Compra y Venta', 'buy_sell', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(33, 'SA', 'Adelaide', 'Help & Support', 'Ayuda y Soporte', 'help', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(34, 'SA', 'Adelaide', 'Off-Topic', 'Fuera de Tema', 'off_topic', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(35, 'SA', 'Adelaide', 'Suggestions', 'Sugerencias', 'suggestions', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(36, 'TAS', 'Hobart', 'General', 'General', 'general', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(37, 'TAS', 'Hobart', 'Housing', 'Vivienda', 'housing', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(38, 'TAS', 'Hobart', 'Jobs', 'Trabajos', 'jobs', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(39, 'TAS', 'Hobart', 'Buy & Sell', 'Compra y Venta', 'buy_sell', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(40, 'TAS', 'Hobart', 'Help & Support', 'Ayuda y Soporte', 'help', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(41, 'TAS', 'Hobart', 'Off-Topic', 'Fuera de Tema', 'off_topic', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(42, 'TAS', 'Hobart', 'Suggestions', 'Sugerencias', 'suggestions', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(43, 'NT', 'Darwin', 'General', 'General', 'general', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(44, 'NT', 'Darwin', 'Housing', 'Vivienda', 'housing', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(45, 'NT', 'Darwin', 'Jobs', 'Trabajos', 'jobs', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(46, 'NT', 'Darwin', 'Buy & Sell', 'Compra y Venta', 'buy_sell', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(47, 'NT', 'Darwin', 'Help & Support', 'Ayuda y Soporte', 'help', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(48, 'NT', 'Darwin', 'Off-Topic', 'Fuera de Tema', 'off_topic', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(49, 'NT', 'Darwin', 'Suggestions', 'Sugerencias', 'suggestions', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(50, 'ACT', 'Canberra', 'General', 'General', 'general', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(51, 'ACT', 'Canberra', 'Housing', 'Vivienda', 'housing', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(52, 'ACT', 'Canberra', 'Jobs', 'Trabajos', 'jobs', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(53, 'ACT', 'Canberra', 'Buy & Sell', 'Compra y Venta', 'buy_sell', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(54, 'ACT', 'Canberra', 'Help & Support', 'Ayuda y Soporte', 'help', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(55, 'ACT', 'Canberra', 'Off-Topic', 'Fuera de Tema', 'off_topic', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(56, 'ACT', 'Canberra', 'Suggestions', 'Sugerencias', 'suggestions', 1, '2025-09-23 15:26:31', NULL, NULL, NULL),
(59, 'NSW', 'Sydney', 'Entrepreneurs', 'Emprendedores', 'jobs', 1, '2025-10-12 09:48:58', '#ff4444', '💡', NULL),
(60, 'VIC', 'Melbourne', 'Entrepreneurs', 'Emprendedores', 'jobs', 1, '2025-10-12 09:48:58', '#ff4444', '💡', NULL),
(61, 'QLD', 'Brisbane', 'Entrepreneurs', 'Emprendedores', 'jobs', 1, '2025-10-12 09:48:58', '#ff4444', '💡', NULL),
(62, 'WA', 'Perth', 'Entrepreneurs', 'Emprendedores', 'jobs', 1, '2025-10-12 09:48:58', '#ff4444', '💡', NULL),
(63, 'SA', 'Adelaide', 'Entrepreneurs', 'Emprendedores', 'jobs', 1, '2025-10-12 09:48:58', '#ff4444', '💡', NULL),
(64, 'TAS', 'Hobart', 'Entrepreneurs', 'Emprendedores', 'jobs', 1, '2025-10-12 09:48:58', '#ff4444', '💡', NULL),
(65, 'NT', 'Darwin', 'Entrepreneurs', 'Emprendedores', 'jobs', 1, '2025-10-12 09:48:58', '#ff4444', '💡', NULL),
(66, 'ACT', 'Canberra', 'Entrepreneurs', 'Emprendedores', 'jobs', 1, '2025-10-12 09:48:58', '#ff4444', '💡', NULL),
(147, 'NSW', 'Sydney', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00e5ff', '🎮', 'Gamers & Tech'),
(148, 'NSW', 'Sydney', 'Movies & Series', 'Movies & Series', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff3fd8', '🎬', 'Movies & Series'),
(149, 'NSW', 'Sydney', 'Photography', 'Photography', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff8a5c', '📸', 'Photography'),
(150, 'NSW', 'Sydney', 'Art & Design', 'Art & Design', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00ffb0', '🎨', 'Art & Design'),
(151, 'NSW', 'Sydney', 'Weed', 'Weed', 'hobbies_static', 1, '2025-10-12 10:04:04', '#5599ff', '🌿', 'Weed'),
(152, 'VIC', 'Melbourne', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00e5ff', '🎮', 'Gamers & Tech'),
(153, 'VIC', 'Melbourne', 'Movies & Series', 'Movies & Series', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff3fd8', '🎬', 'Movies & Series'),
(154, 'VIC', 'Melbourne', 'Photography', 'Photography', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff8a5c', '📸', 'Photography'),
(155, 'VIC', 'Melbourne', 'Art & Design', 'Art & Design', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00ffb0', '🎨', 'Art & Design'),
(156, 'VIC', 'Melbourne', 'Weed', 'Weed', 'hobbies_static', 1, '2025-10-12 10:04:04', '#5599ff', '🌿', 'Weed'),
(157, 'QLD', 'Brisbane', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00e5ff', '🎮', 'Gamers & Tech'),
(158, 'QLD', 'Brisbane', 'Movies & Series', 'Movies & Series', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff3fd8', '🎬', 'Movies & Series'),
(159, 'QLD', 'Brisbane', 'Photography', 'Photography', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff8a5c', '📸', 'Photography'),
(160, 'QLD', 'Brisbane', 'Art & Design', 'Art & Design', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00ffb0', '🎨', 'Art & Design'),
(161, 'QLD', 'Brisbane', 'Weed', 'Weed', 'hobbies_static', 1, '2025-10-12 10:04:04', '#5599ff', '🌿', 'Weed'),
(162, 'WA', 'Perth', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00e5ff', '🎮', 'Gamers & Tech'),
(163, 'WA', 'Perth', 'Movies & Series', 'Movies & Series', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff3fd8', '🎬', 'Movies & Series'),
(164, 'WA', 'Perth', 'Photography', 'Photography', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff8a5c', '📸', 'Photography'),
(165, 'WA', 'Perth', 'Art & Design', 'Art & Design', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00ffb0', '🎨', 'Art & Design'),
(166, 'WA', 'Perth', 'Weed', 'Weed', 'hobbies_static', 1, '2025-10-12 10:04:04', '#5599ff', '🌿', 'Weed'),
(167, 'SA', 'Adelaide', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00e5ff', '🎮', 'Gamers & Tech'),
(168, 'SA', 'Adelaide', 'Movies & Series', 'Movies & Series', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff3fd8', '🎬', 'Movies & Series'),
(169, 'SA', 'Adelaide', 'Photography', 'Photography', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff8a5c', '📸', 'Photography'),
(170, 'SA', 'Adelaide', 'Art & Design', 'Art & Design', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00ffb0', '🎨', 'Art & Design'),
(171, 'SA', 'Adelaide', 'Weed', 'Weed', 'hobbies_static', 1, '2025-10-12 10:04:04', '#5599ff', '🌿', 'Weed'),
(172, 'TAS', 'Hobart', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00e5ff', '🎮', 'Gamers & Tech'),
(173, 'TAS', 'Hobart', 'Movies & Series', 'Movies & Series', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff3fd8', '🎬', 'Movies & Series'),
(174, 'TAS', 'Hobart', 'Photography', 'Photography', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff8a5c', '📸', 'Photography'),
(175, 'TAS', 'Hobart', 'Art & Design', 'Art & Design', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00ffb0', '🎨', 'Art & Design'),
(176, 'TAS', 'Hobart', 'Weed', 'Weed', 'hobbies_static', 1, '2025-10-12 10:04:04', '#5599ff', '🌿', 'Weed'),
(177, 'NT', 'Darwin', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00e5ff', '🎮', 'Gamers & Tech'),
(178, 'NT', 'Darwin', 'Movies & Series', 'Movies & Series', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff3fd8', '🎬', 'Movies & Series'),
(179, 'NT', 'Darwin', 'Photography', 'Photography', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff8a5c', '📸', 'Photography'),
(180, 'NT', 'Darwin', 'Art & Design', 'Art & Design', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00ffb0', '🎨', 'Art & Design'),
(181, 'NT', 'Darwin', 'Weed', 'Weed', 'hobbies_static', 1, '2025-10-12 10:04:04', '#5599ff', '🌿', 'Weed'),
(182, 'ACT', 'Canberra', 'Gamers & Tech', 'Gamers & Tech', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00e5ff', '🎮', 'Gamers & Tech'),
(183, 'ACT', 'Canberra', 'Movies & Series', 'Movies & Series', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff3fd8', '🎬', 'Movies & Series'),
(184, 'ACT', 'Canberra', 'Photography', 'Photography', 'hobbies_static', 1, '2025-10-12 10:04:04', '#ff8a5c', '📸', 'Photography'),
(185, 'ACT', 'Canberra', 'Art & Design', 'Art & Design', 'hobbies_static', 1, '2025-10-12 10:04:04', '#00ffb0', '🎨', 'Art & Design'),
(186, 'ACT', 'Canberra', 'Weed', 'Weed', 'hobbies_static', 1, '2025-10-12 10:04:04', '#5599ff', '🌿', 'Weed'),
(188, 'NSW', 'Sydney', 'football', 'Football', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '⚽', 'Football'),
(189, 'NSW', 'Sydney', 'meetups', 'Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', '🧍', 'Meetups'),
(190, 'NSW', 'Sydney', 'bbq-meetups', 'BBQ Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', '🍖', 'BBQ Meetups'),
(191, 'NSW', 'Sydney', 'mate-lovers', 'Mate lovers', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', '🍵', 'Mate lovers'),
(192, 'NSW', 'Sydney', 'beach-meetups', 'Let\'s go to the beach', 'more', 1, '2025-10-12 10:50:59', '#5599ff', '🏖️', 'Let\'s go to the beach'),
(193, 'NSW', 'Sydney', 'music-dance', 'Music & Dance', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', '🎵', 'Music & Dance'),
(194, 'NSW', 'Sydney', 'latinas-united', 'Latinas United', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '👩', 'Latinas United'),
(195, 'NSW', 'Sydney', 'argentinians', 'Argentinians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Argentinians'),
(196, 'NSW', 'Sydney', 'colombians', 'Colombians', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Colombians'),
(197, 'NSW', 'Sydney', 'spaniards', 'Spaniards', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Spaniards'),
(198, 'NSW', 'Sydney', 'brazilians', 'Brazilians', 'more', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'Brazilians'),
(199, 'NSW', 'Sydney', 'chileans', 'Chileans', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', NULL, 'Chileans'),
(200, 'NSW', 'Sydney', 'mexicans', 'Mexicans', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Mexicans'),
(201, 'NSW', 'Sydney', 'peruvians', 'Peruvians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Peruvians'),
(202, 'NSW', 'Sydney', 'Visas & Docs', 'Visas & Docs', 'daily_static', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Visas & Docs'),
(203, 'NSW', 'Sydney', 'Rides & Transport', 'Rides & Transport', 'daily_static', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Rides & Transport'),
(204, 'NSW', 'Sydney', 'Shopping & Deals', 'Shopping & Deals', 'daily_static', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Shopping & Deals'),
(205, 'NSW', 'Sydney', 'Lost & Found', 'Lost & Found', 'daily_static', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Lost & Found'),
(206, 'NSW', 'Sydney', 'New arrivals', 'New arrivals', 'daily_static', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'New arrivals'),
(207, 'NSW', 'Sydney', 'Gym & Fitness', 'Gym & Fitness', 'fitness_static', 1, '2025-10-12 10:50:59', '#00e5ff', '🏋️', 'Gym & Fitness'),
(208, 'NSW', 'Sydney', 'Running & Marathons', 'Running & Marathons', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff3fd8', '🏃', 'Running & Marathons'),
(209, 'NSW', 'Sydney', 'Calisthenics / Streets', 'Calisthenics / Streets', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff8a5c', '🤸', 'Calisthenics / Streets'),
(210, 'NSW', 'Sydney', 'Tennis / Padel', 'Tennis / Padel', 'fitness_static', 1, '2025-10-12 10:50:59', '#00ffb0', '🎾', 'Tennis / Padel'),
(211, 'NSW', 'Sydney', 'Surf', 'Surf', 'fitness_static', 1, '2025-10-12 10:50:59', '#5599ff', '🏄', 'Surf'),
(217, 'VIC', 'Melbourne', 'football', 'Football', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '⚽', 'Football'),
(218, 'VIC', 'Melbourne', 'meetups', 'Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', '🧍', 'Meetups'),
(219, 'VIC', 'Melbourne', 'bbq-meetups', 'BBQ Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', '🍖', 'BBQ Meetups'),
(220, 'VIC', 'Melbourne', 'mate-lovers', 'Mate lovers', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', '🍵', 'Mate lovers'),
(221, 'VIC', 'Melbourne', 'beach-meetups', 'Let\'s go to the beach', 'more', 1, '2025-10-12 10:50:59', '#5599ff', '🏖️', 'Let\'s go to the beach'),
(222, 'VIC', 'Melbourne', 'music-dance', 'Music & Dance', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', '🎵', 'Music & Dance'),
(223, 'VIC', 'Melbourne', 'latinas-united', 'Latinas United', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '👩', 'Latinas United'),
(224, 'VIC', 'Melbourne', 'argentinians', 'Argentinians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Argentinians'),
(225, 'VIC', 'Melbourne', 'colombians', 'Colombians', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Colombians'),
(226, 'VIC', 'Melbourne', 'spaniards', 'Spaniards', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Spaniards'),
(227, 'VIC', 'Melbourne', 'brazilians', 'Brazilians', 'more', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'Brazilians'),
(228, 'VIC', 'Melbourne', 'chileans', 'Chileans', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', NULL, 'Chileans'),
(229, 'VIC', 'Melbourne', 'mexicans', 'Mexicans', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Mexicans'),
(230, 'VIC', 'Melbourne', 'peruvians', 'Peruvians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Peruvians'),
(231, 'VIC', 'Melbourne', 'Visas & Docs', 'Visas & Docs', 'daily_static', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Visas & Docs'),
(232, 'VIC', 'Melbourne', 'Rides & Transport', 'Rides & Transport', 'daily_static', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Rides & Transport'),
(233, 'VIC', 'Melbourne', 'Shopping & Deals', 'Shopping & Deals', 'daily_static', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Shopping & Deals'),
(234, 'VIC', 'Melbourne', 'Lost & Found', 'Lost & Found', 'daily_static', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Lost & Found'),
(235, 'VIC', 'Melbourne', 'New arrivals', 'New arrivals', 'daily_static', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'New arrivals'),
(236, 'VIC', 'Melbourne', 'Gym & Fitness', 'Gym & Fitness', 'fitness_static', 1, '2025-10-12 10:50:59', '#00e5ff', '🏋️', 'Gym & Fitness'),
(237, 'VIC', 'Melbourne', 'Running & Marathons', 'Running & Marathons', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff3fd8', '🏃', 'Running & Marathons'),
(238, 'VIC', 'Melbourne', 'Calisthenics / Streets', 'Calisthenics / Streets', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff8a5c', '🤸', 'Calisthenics / Streets'),
(239, 'VIC', 'Melbourne', 'Tennis / Padel', 'Tennis / Padel', 'fitness_static', 1, '2025-10-12 10:50:59', '#00ffb0', '🎾', 'Tennis / Padel'),
(240, 'VIC', 'Melbourne', 'Surf', 'Surf', 'fitness_static', 1, '2025-10-12 10:50:59', '#5599ff', '🏄', 'Surf'),
(246, 'QLD', 'Brisbane', 'football', 'Football', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '⚽', 'Football'),
(247, 'QLD', 'Brisbane', 'meetups', 'Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', '🧍', 'Meetups'),
(248, 'QLD', 'Brisbane', 'bbq-meetups', 'BBQ Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', '🍖', 'BBQ Meetups'),
(249, 'QLD', 'Brisbane', 'mate-lovers', 'Mate lovers', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', '🍵', 'Mate lovers'),
(250, 'QLD', 'Brisbane', 'beach-meetups', 'Let\'s go to the beach', 'more', 1, '2025-10-12 10:50:59', '#5599ff', '🏖️', 'Let\'s go to the beach'),
(251, 'QLD', 'Brisbane', 'music-dance', 'Music & Dance', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', '🎵', 'Music & Dance'),
(252, 'QLD', 'Brisbane', 'latinas-united', 'Latinas United', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '👩', 'Latinas United'),
(253, 'QLD', 'Brisbane', 'argentinians', 'Argentinians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Argentinians'),
(254, 'QLD', 'Brisbane', 'colombians', 'Colombians', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Colombians'),
(255, 'QLD', 'Brisbane', 'spaniards', 'Spaniards', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Spaniards'),
(256, 'QLD', 'Brisbane', 'brazilians', 'Brazilians', 'more', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'Brazilians'),
(257, 'QLD', 'Brisbane', 'chileans', 'Chileans', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', NULL, 'Chileans'),
(258, 'QLD', 'Brisbane', 'mexicans', 'Mexicans', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Mexicans'),
(259, 'QLD', 'Brisbane', 'peruvians', 'Peruvians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Peruvians'),
(260, 'QLD', 'Brisbane', 'Visas & Docs', 'Visas & Docs', 'daily_static', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Visas & Docs'),
(261, 'QLD', 'Brisbane', 'Rides & Transport', 'Rides & Transport', 'daily_static', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Rides & Transport'),
(262, 'QLD', 'Brisbane', 'Shopping & Deals', 'Shopping & Deals', 'daily_static', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Shopping & Deals'),
(263, 'QLD', 'Brisbane', 'Lost & Found', 'Lost & Found', 'daily_static', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Lost & Found'),
(264, 'QLD', 'Brisbane', 'New arrivals', 'New arrivals', 'daily_static', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'New arrivals'),
(265, 'QLD', 'Brisbane', 'Gym & Fitness', 'Gym & Fitness', 'fitness_static', 1, '2025-10-12 10:50:59', '#00e5ff', '🏋️', 'Gym & Fitness'),
(266, 'QLD', 'Brisbane', 'Running & Marathons', 'Running & Marathons', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff3fd8', '🏃', 'Running & Marathons'),
(267, 'QLD', 'Brisbane', 'Calisthenics / Streets', 'Calisthenics / Streets', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff8a5c', '🤸', 'Calisthenics / Streets'),
(268, 'QLD', 'Brisbane', 'Tennis / Padel', 'Tennis / Padel', 'fitness_static', 1, '2025-10-12 10:50:59', '#00ffb0', '🎾', 'Tennis / Padel'),
(269, 'QLD', 'Brisbane', 'Surf', 'Surf', 'fitness_static', 1, '2025-10-12 10:50:59', '#5599ff', '🏄', 'Surf'),
(275, 'WA', 'Perth', 'football', 'Football', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '⚽', 'Football'),
(276, 'WA', 'Perth', 'meetups', 'Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', '🧍', 'Meetups'),
(277, 'WA', 'Perth', 'bbq-meetups', 'BBQ Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', '🍖', 'BBQ Meetups'),
(278, 'WA', 'Perth', 'mate-lovers', 'Mate lovers', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', '🍵', 'Mate lovers'),
(279, 'WA', 'Perth', 'beach-meetups', 'Let\'s go to the beach', 'more', 1, '2025-10-12 10:50:59', '#5599ff', '🏖️', 'Let\'s go to the beach'),
(280, 'WA', 'Perth', 'music-dance', 'Music & Dance', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', '🎵', 'Music & Dance'),
(281, 'WA', 'Perth', 'latinas-united', 'Latinas United', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '👩', 'Latinas United'),
(282, 'WA', 'Perth', 'argentinians', 'Argentinians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Argentinians'),
(283, 'WA', 'Perth', 'colombians', 'Colombians', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Colombians'),
(284, 'WA', 'Perth', 'spaniards', 'Spaniards', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Spaniards'),
(285, 'WA', 'Perth', 'brazilians', 'Brazilians', 'more', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'Brazilians'),
(286, 'WA', 'Perth', 'chileans', 'Chileans', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', NULL, 'Chileans'),
(287, 'WA', 'Perth', 'mexicans', 'Mexicans', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Mexicans'),
(288, 'WA', 'Perth', 'peruvians', 'Peruvians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Peruvians'),
(289, 'WA', 'Perth', 'Visas & Docs', 'Visas & Docs', 'daily_static', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Visas & Docs'),
(290, 'WA', 'Perth', 'Rides & Transport', 'Rides & Transport', 'daily_static', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Rides & Transport'),
(291, 'WA', 'Perth', 'Shopping & Deals', 'Shopping & Deals', 'daily_static', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Shopping & Deals'),
(292, 'WA', 'Perth', 'Lost & Found', 'Lost & Found', 'daily_static', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Lost & Found'),
(293, 'WA', 'Perth', 'New arrivals', 'New arrivals', 'daily_static', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'New arrivals'),
(294, 'WA', 'Perth', 'Gym & Fitness', 'Gym & Fitness', 'fitness_static', 1, '2025-10-12 10:50:59', '#00e5ff', '🏋️', 'Gym & Fitness'),
(295, 'WA', 'Perth', 'Running & Marathons', 'Running & Marathons', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff3fd8', '🏃', 'Running & Marathons'),
(296, 'WA', 'Perth', 'Calisthenics / Streets', 'Calisthenics / Streets', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff8a5c', '🤸', 'Calisthenics / Streets'),
(297, 'WA', 'Perth', 'Tennis / Padel', 'Tennis / Padel', 'fitness_static', 1, '2025-10-12 10:50:59', '#00ffb0', '🎾', 'Tennis / Padel'),
(298, 'WA', 'Perth', 'Surf', 'Surf', 'fitness_static', 1, '2025-10-12 10:50:59', '#5599ff', '🏄', 'Surf'),
(304, 'SA', 'Adelaide', 'football', 'Football', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '⚽', 'Football'),
(305, 'SA', 'Adelaide', 'meetups', 'Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', '🧍', 'Meetups'),
(306, 'SA', 'Adelaide', 'bbq-meetups', 'BBQ Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', '🍖', 'BBQ Meetups'),
(307, 'SA', 'Adelaide', 'mate-lovers', 'Mate lovers', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', '🍵', 'Mate lovers'),
(308, 'SA', 'Adelaide', 'beach-meetups', 'Let\'s go to the beach', 'more', 1, '2025-10-12 10:50:59', '#5599ff', '🏖️', 'Let\'s go to the beach'),
(309, 'SA', 'Adelaide', 'music-dance', 'Music & Dance', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', '🎵', 'Music & Dance'),
(310, 'SA', 'Adelaide', 'latinas-united', 'Latinas United', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '👩', 'Latinas United'),
(311, 'SA', 'Adelaide', 'argentinians', 'Argentinians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Argentinians'),
(312, 'SA', 'Adelaide', 'colombians', 'Colombians', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Colombians'),
(313, 'SA', 'Adelaide', 'spaniards', 'Spaniards', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Spaniards'),
(314, 'SA', 'Adelaide', 'brazilians', 'Brazilians', 'more', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'Brazilians'),
(315, 'SA', 'Adelaide', 'chileans', 'Chileans', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', NULL, 'Chileans'),
(316, 'SA', 'Adelaide', 'mexicans', 'Mexicans', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Mexicans'),
(317, 'SA', 'Adelaide', 'peruvians', 'Peruvians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Peruvians'),
(318, 'SA', 'Adelaide', 'Visas & Docs', 'Visas & Docs', 'daily_static', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Visas & Docs'),
(319, 'SA', 'Adelaide', 'Rides & Transport', 'Rides & Transport', 'daily_static', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Rides & Transport'),
(320, 'SA', 'Adelaide', 'Shopping & Deals', 'Shopping & Deals', 'daily_static', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Shopping & Deals'),
(321, 'SA', 'Adelaide', 'Lost & Found', 'Lost & Found', 'daily_static', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Lost & Found'),
(322, 'SA', 'Adelaide', 'New arrivals', 'New arrivals', 'daily_static', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'New arrivals'),
(323, 'SA', 'Adelaide', 'Gym & Fitness', 'Gym & Fitness', 'fitness_static', 1, '2025-10-12 10:50:59', '#00e5ff', '🏋️', 'Gym & Fitness'),
(324, 'SA', 'Adelaide', 'Running & Marathons', 'Running & Marathons', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff3fd8', '🏃', 'Running & Marathons'),
(325, 'SA', 'Adelaide', 'Calisthenics / Streets', 'Calisthenics / Streets', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff8a5c', '🤸', 'Calisthenics / Streets'),
(326, 'SA', 'Adelaide', 'Tennis / Padel', 'Tennis / Padel', 'fitness_static', 1, '2025-10-12 10:50:59', '#00ffb0', '🎾', 'Tennis / Padel'),
(327, 'SA', 'Adelaide', 'Surf', 'Surf', 'fitness_static', 1, '2025-10-12 10:50:59', '#5599ff', '🏄', 'Surf'),
(333, 'TAS', 'Hobart', 'football', 'Football', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '⚽', 'Football'),
(334, 'TAS', 'Hobart', 'meetups', 'Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', '🧍', 'Meetups'),
(335, 'TAS', 'Hobart', 'bbq-meetups', 'BBQ Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', '🍖', 'BBQ Meetups'),
(336, 'TAS', 'Hobart', 'mate-lovers', 'Mate lovers', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', '🍵', 'Mate lovers'),
(337, 'TAS', 'Hobart', 'beach-meetups', 'Let\'s go to the beach', 'more', 1, '2025-10-12 10:50:59', '#5599ff', '🏖️', 'Let\'s go to the beach'),
(338, 'TAS', 'Hobart', 'music-dance', 'Music & Dance', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', '🎵', 'Music & Dance'),
(339, 'TAS', 'Hobart', 'latinas-united', 'Latinas United', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '👩', 'Latinas United'),
(340, 'TAS', 'Hobart', 'argentinians', 'Argentinians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Argentinians'),
(341, 'TAS', 'Hobart', 'colombians', 'Colombians', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Colombians'),
(342, 'TAS', 'Hobart', 'spaniards', 'Spaniards', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Spaniards'),
(343, 'TAS', 'Hobart', 'brazilians', 'Brazilians', 'more', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'Brazilians'),
(344, 'TAS', 'Hobart', 'chileans', 'Chileans', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', NULL, 'Chileans'),
(345, 'TAS', 'Hobart', 'mexicans', 'Mexicans', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Mexicans'),
(346, 'TAS', 'Hobart', 'peruvians', 'Peruvians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Peruvians'),
(347, 'TAS', 'Hobart', 'Visas & Docs', 'Visas & Docs', 'daily_static', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Visas & Docs'),
(348, 'TAS', 'Hobart', 'Rides & Transport', 'Rides & Transport', 'daily_static', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Rides & Transport'),
(349, 'TAS', 'Hobart', 'Shopping & Deals', 'Shopping & Deals', 'daily_static', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Shopping & Deals'),
(350, 'TAS', 'Hobart', 'Lost & Found', 'Lost & Found', 'daily_static', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Lost & Found'),
(351, 'TAS', 'Hobart', 'New arrivals', 'New arrivals', 'daily_static', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'New arrivals'),
(352, 'TAS', 'Hobart', 'Gym & Fitness', 'Gym & Fitness', 'fitness_static', 1, '2025-10-12 10:50:59', '#00e5ff', '🏋️', 'Gym & Fitness'),
(353, 'TAS', 'Hobart', 'Running & Marathons', 'Running & Marathons', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff3fd8', '🏃', 'Running & Marathons'),
(354, 'TAS', 'Hobart', 'Calisthenics / Streets', 'Calisthenics / Streets', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff8a5c', '🤸', 'Calisthenics / Streets'),
(355, 'TAS', 'Hobart', 'Tennis / Padel', 'Tennis / Padel', 'fitness_static', 1, '2025-10-12 10:50:59', '#00ffb0', '🎾', 'Tennis / Padel'),
(356, 'TAS', 'Hobart', 'Surf', 'Surf', 'fitness_static', 1, '2025-10-12 10:50:59', '#5599ff', '🏄', 'Surf'),
(362, 'NT', 'Darwin', 'football', 'Football', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '⚽', 'Football'),
(363, 'NT', 'Darwin', 'meetups', 'Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', '🧍', 'Meetups'),
(364, 'NT', 'Darwin', 'bbq-meetups', 'BBQ Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', '🍖', 'BBQ Meetups'),
(365, 'NT', 'Darwin', 'mate-lovers', 'Mate lovers', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', '🍵', 'Mate lovers'),
(366, 'NT', 'Darwin', 'beach-meetups', 'Let\'s go to the beach', 'more', 1, '2025-10-12 10:50:59', '#5599ff', '🏖️', 'Let\'s go to the beach'),
(367, 'NT', 'Darwin', 'music-dance', 'Music & Dance', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', '🎵', 'Music & Dance'),
(368, 'NT', 'Darwin', 'latinas-united', 'Latinas United', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '👩', 'Latinas United'),
(369, 'NT', 'Darwin', 'argentinians', 'Argentinians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Argentinians'),
(370, 'NT', 'Darwin', 'colombians', 'Colombians', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Colombians'),
(371, 'NT', 'Darwin', 'spaniards', 'Spaniards', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Spaniards'),
(372, 'NT', 'Darwin', 'brazilians', 'Brazilians', 'more', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'Brazilians'),
(373, 'NT', 'Darwin', 'chileans', 'Chileans', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', NULL, 'Chileans'),
(374, 'NT', 'Darwin', 'mexicans', 'Mexicans', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Mexicans'),
(375, 'NT', 'Darwin', 'peruvians', 'Peruvians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Peruvians'),
(376, 'NT', 'Darwin', 'Visas & Docs', 'Visas & Docs', 'daily_static', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Visas & Docs'),
(377, 'NT', 'Darwin', 'Rides & Transport', 'Rides & Transport', 'daily_static', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Rides & Transport'),
(378, 'NT', 'Darwin', 'Shopping & Deals', 'Shopping & Deals', 'daily_static', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Shopping & Deals'),
(379, 'NT', 'Darwin', 'Lost & Found', 'Lost & Found', 'daily_static', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Lost & Found'),
(380, 'NT', 'Darwin', 'New arrivals', 'New arrivals', 'daily_static', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'New arrivals'),
(381, 'NT', 'Darwin', 'Gym & Fitness', 'Gym & Fitness', 'fitness_static', 1, '2025-10-12 10:50:59', '#00e5ff', '🏋️', 'Gym & Fitness'),
(382, 'NT', 'Darwin', 'Running & Marathons', 'Running & Marathons', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff3fd8', '🏃', 'Running & Marathons'),
(383, 'NT', 'Darwin', 'Calisthenics / Streets', 'Calisthenics / Streets', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff8a5c', '🤸', 'Calisthenics / Streets'),
(384, 'NT', 'Darwin', 'Tennis / Padel', 'Tennis / Padel', 'fitness_static', 1, '2025-10-12 10:50:59', '#00ffb0', '🎾', 'Tennis / Padel'),
(385, 'NT', 'Darwin', 'Surf', 'Surf', 'fitness_static', 1, '2025-10-12 10:50:59', '#5599ff', '🏄', 'Surf'),
(391, 'ACT', 'Canberra', 'football', 'Football', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '⚽', 'Football'),
(392, 'ACT', 'Canberra', 'meetups', 'Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', '🧍', 'Meetups'),
(393, 'ACT', 'Canberra', 'bbq-meetups', 'BBQ Meetups', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', '🍖', 'BBQ Meetups'),
(394, 'ACT', 'Canberra', 'mate-lovers', 'Mate lovers', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', '🍵', 'Mate lovers'),
(395, 'ACT', 'Canberra', 'beach-meetups', 'Let\'s go to the beach', 'more', 1, '2025-10-12 10:50:59', '#5599ff', '🏖️', 'Let\'s go to the beach'),
(396, 'ACT', 'Canberra', 'music-dance', 'Music & Dance', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', '🎵', 'Music & Dance'),
(397, 'ACT', 'Canberra', 'latinas-united', 'Latinas United', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', '👩', 'Latinas United'),
(398, 'ACT', 'Canberra', 'argentinians', 'Argentinians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Argentinians'),
(399, 'ACT', 'Canberra', 'colombians', 'Colombians', 'more', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Colombians'),
(400, 'ACT', 'Canberra', 'spaniards', 'Spaniards', 'more', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Spaniards'),
(401, 'ACT', 'Canberra', 'brazilians', 'Brazilians', 'more', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'Brazilians'),
(402, 'ACT', 'Canberra', 'chileans', 'Chileans', 'more', 1, '2025-10-12 10:50:59', '#ffa03c', NULL, 'Chileans'),
(403, 'ACT', 'Canberra', 'mexicans', 'Mexicans', 'more', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Mexicans'),
(404, 'ACT', 'Canberra', 'peruvians', 'Peruvians', 'more', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Peruvians'),
(405, 'ACT', 'Canberra', 'Visas & Docs', 'Visas & Docs', 'daily_static', 1, '2025-10-12 10:50:59', '#00e5ff', NULL, 'Visas & Docs'),
(406, 'ACT', 'Canberra', 'Rides & Transport', 'Rides & Transport', 'daily_static', 1, '2025-10-12 10:50:59', '#ff3fd8', NULL, 'Rides & Transport'),
(407, 'ACT', 'Canberra', 'Shopping & Deals', 'Shopping & Deals', 'daily_static', 1, '2025-10-12 10:50:59', '#ff8a5c', NULL, 'Shopping & Deals'),
(408, 'ACT', 'Canberra', 'Lost & Found', 'Lost & Found', 'daily_static', 1, '2025-10-12 10:50:59', '#00ffb0', NULL, 'Lost & Found'),
(409, 'ACT', 'Canberra', 'New arrivals', 'New arrivals', 'daily_static', 1, '2025-10-12 10:50:59', '#5599ff', NULL, 'New arrivals'),
(410, 'ACT', 'Canberra', 'Gym & Fitness', 'Gym & Fitness', 'fitness_static', 1, '2025-10-12 10:50:59', '#00e5ff', '🏋️', 'Gym & Fitness'),
(411, 'ACT', 'Canberra', 'Running & Marathons', 'Running & Marathons', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff3fd8', '🏃', 'Running & Marathons'),
(412, 'ACT', 'Canberra', 'Calisthenics / Streets', 'Calisthenics / Streets', 'fitness_static', 1, '2025-10-12 10:50:59', '#ff8a5c', '🤸', 'Calisthenics / Streets'),
(413, 'ACT', 'Canberra', 'Tennis / Padel', 'Tennis / Padel', 'fitness_static', 1, '2025-10-12 10:50:59', '#00ffb0', '🎾', 'Tennis / Padel'),
(414, 'ACT', 'Canberra', 'Surf', 'Surf', 'fitness_static', 1, '2025-10-12 10:50:59', '#5599ff', '🏄', 'Surf');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `state_code` varchar(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_es` varchar(100) NOT NULL,
  `is_main_city` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `state_code`, `name`, `name_es`, `is_main_city`, `created_at`) VALUES
(1, 'NSW', 'Sydney', 'Sídney', 1, '2025-09-23 15:26:31'),
(2, 'VIC', 'Melbourne', 'Melbourne', 1, '2025-09-23 15:26:31'),
(3, 'QLD', 'Brisbane', 'Brisbane', 1, '2025-09-23 15:26:31'),
(4, 'WA', 'Perth', 'Perth', 1, '2025-09-23 15:26:31'),
(5, 'SA', 'Adelaide', 'Adelaida', 1, '2025-09-23 15:26:31'),
(6, 'TAS', 'Hobart', 'Hobart', 1, '2025-09-23 15:26:31'),
(7, 'NT', 'Darwin', 'Darwin', 1, '2025-09-23 15:26:31'),
(8, 'ACT', 'Canberra', 'Canberra', 1, '2025-09-23 15:26:31');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','responded') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', 'Test Subject', 'This is a test message', 'unread', '2025-11-12 12:06:22', '2025-11-12 12:06:22'),
(2, 'New Test User', 'newtest@example.com', 'New Test Subject', 'This is a new test message from the contact form', 'unread', '2025-11-12 12:41:23', '2025-11-12 12:41:23'),
(3, 'Ashraful Talukder', 'h@h.com', 'new', 'hi', 'unread', '2025-11-12 12:43:03', '2025-11-12 12:43:03'),
(4, 'Modal Test', 'modal@test.com', 'Testing Modal', 'This should show the alert modal', 'unread', '2025-11-12 12:45:20', '2025-11-12 12:45:20'),
(5, 'Ashraful Talukder', 'i@h.com', 'hi', 'hello', 'unread', '2025-11-12 12:46:05', '2025-11-12 12:46:05'),
(6, 'Emma Lewis', 'h@i.com', 'works', 'hey', 'unread', '2025-11-12 15:10:16', '2025-11-12 15:10:16'),
(7, 'ems', 'i@i.com', 'new', 'hello', 'unread', '2025-11-12 15:13:53', '2025-11-12 15:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `dm_messages`
--

CREATE TABLE `dm_messages` (
  `id` int(11) NOT NULL,
  `thread_id` varchar(50) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dm_messages`
--

INSERT INTO `dm_messages` (`id`, `thread_id`, `sender_id`, `message`, `file_path`, `file_name`, `file_size`, `file_type`, `mime_type`, `created_at`, `expires_at`, `is_read`) VALUES
(3, 'dm_68d2c50559fb59.20625322', 2, 'hey', NULL, NULL, NULL, NULL, NULL, '2025-09-23 16:05:14', '2025-09-24 12:05:14', 1),
(5, 'dm_68d2c50559fb59.20625322', 2, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-09-23 16:10:41', '2025-09-24 12:10:41', 1),
(9, 'dm_68d2c50559fb59.20625322', 2, 'hello', NULL, NULL, NULL, NULL, NULL, '2025-09-23 16:15:26', '2025-09-24 12:15:26', 1),
(11, 'dm_68d2c50559fb59.20625322', 2, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-09-24 05:20:52', '2025-09-25 01:20:52', 1),
(12, 'dm_68d2c50559fb59.20625322', 2, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-09-24 05:21:33', '2025-09-25 01:21:33', 1),
(13, 'dm_68d2c50559fb59.20625322', 2, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-09-24 05:21:37', '2025-09-25 01:21:37', 1),
(14, 'dm_68d2c50559fb59.20625322', 2, 'hello', NULL, NULL, NULL, NULL, NULL, '2025-09-24 05:26:33', '2025-09-25 01:26:33', 1),
(16, 'dm_68d2c50559fb59.20625322', 2, 'image', 'uploads/dm_files/dm_file_68d3e337109546.42211048.png', 'Screenshot 2025-09-07 200114.png', 26735, 'image/png', 'image/png', '2025-09-24 12:25:33', '2025-09-25 08:25:33', 1),
(17, 'dm_68d2c50559fb59.20625322', 2, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-09-24 12:29:35', '2025-09-25 08:29:35', 1),
(18, 'dm_68d2c5038882d9.81118100', 2, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-09-26 12:27:30', '2025-09-27 08:27:30', 0),
(19, 'dm_68d68a233596c3.40218604', 2, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-09-26 12:49:40', '2025-09-27 08:49:40', 1),
(23, 'dm_68d68a44d77833.40965048', 2, 'image', 'uploads/dm_files/dm_file_6922a9730a0786.81160764.jpg', '1.jpg', 495968, 'image/jpeg', 'image/jpeg', '2025-11-23 06:28:08', '2025-11-24 01:28:08', 0),
(24, 'dm_6922ebceb2a8f2.61560054', 18, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-11-23 11:12:31', '2025-11-24 06:12:31', 1),
(25, 'dm_6922ec108774f9.43423286', 19, 'hey', NULL, NULL, NULL, NULL, NULL, '2025-11-23 11:12:41', '2025-11-24 06:12:41', 1),
(26, 'dm_6922ec108774f9.43423286', 19, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-11-23 11:13:16', '2025-11-24 06:13:16', 1),
(27, 'dm_6922ebceb2a8f2.61560054', 18, 'hello', NULL, NULL, NULL, NULL, NULL, '2025-11-23 11:13:27', '2025-11-24 06:13:27', 1),
(28, 'dm_6922ec108774f9.43423286', 18, 'hello', NULL, NULL, NULL, NULL, NULL, '2025-11-23 11:13:51', '2025-11-24 06:13:51', 1),
(29, 'dm_6922f0be0cf939.53611462', 20, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-11-23 11:36:46', '2025-11-24 06:36:46', 1),
(30, 'dm_6922f0be0cf939.53611462', 18, 'hi', NULL, NULL, NULL, NULL, NULL, '2025-11-23 11:50:31', '2025-11-24 06:50:31', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dm_requests`
--

CREATE TABLE `dm_requests` (
  `id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dm_requests`
--

INSERT INTO `dm_requests` (`id`, `from_user_id`, `to_user_id`, `status`, `created_at`, `responded_at`) VALUES
(4, 5, 2, 'declined', '2025-09-24 12:08:19', '2025-09-26 12:47:30'),
(5, 2, 5, 'accepted', '2025-09-26 12:23:02', '2025-09-26 12:42:44'),
(6, 2, 5, 'accepted', '2025-09-26 12:37:54', '2025-09-26 12:42:44'),
(12, 2, 18, 'accepted', '2025-11-23 10:55:01', '2025-11-23 11:11:10'),
(13, 19, 18, 'accepted', '2025-11-23 11:12:09', '2025-11-23 11:12:16'),
(14, 20, 18, 'accepted', '2025-11-23 11:31:41', '2025-11-23 11:32:14');

-- --------------------------------------------------------

--
-- Table structure for table `dm_threads`
--

CREATE TABLE `dm_threads` (
  `id` int(11) NOT NULL,
  `thread_id` varchar(50) NOT NULL,
  `participant1_id` int(11) NOT NULL,
  `participant2_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dm_threads`
--

INSERT INTO `dm_threads` (`id`, `thread_id`, `participant1_id`, `participant2_id`, `created_at`, `last_message_at`, `is_active`) VALUES
(7, 'dm_68d68a44d77833.40965048', 2, 5, '2025-09-26 12:42:44', '2025-11-23 06:28:08', 1),
(9, 'dm_6922ebceb2a8f2.61560054', 2, 18, '2025-11-23 11:11:10', '2025-11-23 11:13:27', 1),
(10, 'dm_6922ec108774f9.43423286', 18, 19, '2025-11-23 11:12:16', '2025-11-23 11:13:51', 1),
(11, 'dm_6922f0be0cf939.53611462', 18, 20, '2025-11-23 11:32:14', '2025-11-23 11:50:31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state_code` varchar(5) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `contact_method` enum('phone','email','chat','multiple') DEFAULT 'multiple',
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `allow_chat` tinyint(1) DEFAULT 1,
  `poster_path` varchar(500) DEFAULT NULL,
  `status` enum('pending','approved','rejected','removed') DEFAULT 'pending',
  `is_featured` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `description`, `city`, `state_code`, `event_date`, `event_time`, `contact_method`, `contact_phone`, `contact_email`, `allow_chat`, `poster_path`, `status`, `is_featured`, `view_count`, `created_at`, `updated_at`, `approved_at`, `approved_by`, `expires_at`) VALUES
(1, 2, 'Latin Night Fever', 'Hot Latin beats and dancing all night long! Join us for an unforgettable evening of salsa, bachata, and reggaeton.', 'Sydney', 'NSW', '2025-12-15', '22:00:00', 'multiple', '+61 400 123 456', 'events@sydneyvenue.com', 1, 'images/poster1.png', 'approved', 0, 0, '2025-11-10 11:39:33', '2025-11-11 06:02:26', '2025-11-11 06:02:26', 2, NULL),
(2, 2, 'Melbourne BBQ Meetup', 'Summer BBQ with authentic Argentine asado! Bring your mates and enjoy traditional grilled meats, cold beers, and good company.', 'Melbourne', 'VIC', '2025-12-20', '18:00:00', 'chat', NULL, NULL, 1, 'images/poster2.png', 'approved', 0, 0, '2025-11-10 11:39:33', '2025-11-10 11:39:33', NULL, NULL, NULL),
(3, 2, 'Brisbane Latin Festival', 'Three days of Latin culture! Music, dance, food, and celebrations from across Latin America.', 'Brisbane', 'QLD', '2025-12-25', '20:00:00', 'email', NULL, 'info@brisbanefestival.com', 1, 'images/poster3.png', 'approved', 0, 0, '2025-11-10 11:39:33', '2025-11-10 11:39:33', NULL, NULL, NULL),
(4, 2, 'Perth Tango Night', 'Elegant tango dancing under the stars. All levels welcome - from beginners to advanced dancers.', 'Perth', 'WA', '2025-12-10', '19:30:00', 'phone', '+61 400 987 654', NULL, 1, 'images/poster4.png', 'approved', 0, 0, '2025-11-10 11:39:33', '2025-11-10 11:39:33', NULL, NULL, NULL),
(5, 2, 'latin', 'mahdi', 'Sydney', 'NSW', '2025-11-29', '18:11:00', 'multiple', '654646465654654654', 'f@g.com', 1, 'uploads/events/event_6911cf68f3d63.png', 'pending', 0, 0, '2025-11-10 11:41:28', '2025-11-10 11:56:35', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_photos`
--

CREATE TABLE `event_photos` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `photo_path` varchar(500) NOT NULL,
  `photo_order` tinyint(4) DEFAULT 1,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_attachments`
--

CREATE TABLE `file_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) DEFAULT NULL,
  `dm_message_id` int(11) DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_attachments`
--

INSERT INTO `file_attachments` (`id`, `message_id`, `dm_message_id`, `file_path`, `original_name`, `file_size`, `mime_type`, `uploaded_by`, `created_at`, `expires_at`) VALUES
(2, NULL, 16, 'uploads/dm_files/dm_file_68d3e337109546.42211048.png', 'Screenshot 2025-09-07 200114.png', 26735, 'image/png', 2, '2025-09-24 12:25:27', '2025-09-25 08:25:27'),
(4, NULL, 23, 'uploads/dm_files/dm_file_6922a9730a0786.81160764.jpg', '1.jpg', 495968, 'image/jpeg', 2, '2025-11-23 06:28:03', '2025-11-24 01:28:03');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `posted_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `company`, `location`, `email`, `phone`, `description`, `posted_by`, `created_at`) VALUES
(14, 'software engineer', 'webwiz', 'dhaka', 'f@g.com', '+8801750107526', 'new', 2, '2025-11-09 13:55:15'),
(15, 'Construction Builder', 'webwiz', 'dhaka', 'f@g.com', '65465465465456', 'We’re looking for a skilled Construction Builder to join our growing team. The ideal candidate will have hands-on experience in general construction, site preparation, and structural work. You’ll be responsible for building, repairing, and maintaining residential or commercial structures according to project specifications and safety standards.', 2, '2025-11-10 14:29:38'),
(16, 'Cleaner', 'webwiz', 'sydney', 'f@g.com', '4646546546545465', 'We are seeking a reliable and detail-oriented Cleaner to join our team. The ideal candidate will take pride in maintaining clean, safe, and organized spaces for our clients. You will be responsible for cleaning residential, office, or commercial areas according to company standards.', 2, '2025-11-10 14:30:33'),
(17, 'Retail Sales Associate', 'webwiz', 'dhaka', 'f@g.com', '46546456465', 'We’re looking for an enthusiastic and customer-focused Retail Sales Associate to join our team. You’ll be the face of our store, helping customers find what they need, maintaining a clean and organized environment, and ensuring every shopper enjoys a great experience.', 2, '2025-11-10 14:31:23');

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_inquiries`
--

CREATE TABLE `marketplace_inquiries` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `inquirer_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `inquiry_type` enum('question','offer','chat_request') DEFAULT 'question',
  `status` enum('pending','responded','closed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_items`
--

CREATE TABLE `marketplace_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` enum('electronics','furniture','vehicles','clothing','miscellaneous','free_items') NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_free` tinyint(1) DEFAULT 0,
  `city` varchar(100) NOT NULL,
  `state_code` varchar(5) NOT NULL,
  `contact_method` enum('phone','email','chat','multiple') DEFAULT 'multiple',
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `allow_chat` tinyint(1) DEFAULT 1,
  `status` enum('pending','approved','rejected','sold','removed') DEFAULT 'pending',
  `is_featured` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketplace_items`
--

INSERT INTO `marketplace_items` (`id`, `user_id`, `title`, `category`, `description`, `price`, `is_free`, `city`, `state_code`, `contact_method`, `contact_phone`, `contact_email`, `allow_chat`, `status`, `is_featured`, `view_count`, `created_at`, `updated_at`, `approved_at`, `approved_by`, `expires_at`) VALUES
(15, 2, 'table', 'furniture', 'new new', 15.00, 0, 'Sydney', 'NSW', 'multiple', '5454544554564456654', 'f@g.com', 1, 'approved', 0, 8, '2025-11-09 05:15:31', '2025-11-10 14:08:15', '2025-11-09 05:15:39', 2, NULL),
(16, 2, 'nice', 'electronics', 'asdasd', 258.00, 0, 'Melbourne', 'VIC', 'multiple', '564654465465450', 'f@g.com', 1, 'approved', 0, 1, '2025-11-09 05:16:39', '2025-11-11 05:18:39', '2025-11-09 05:16:46', 2, NULL),
(17, 2, 'car', 'vehicles', 'newww', 258.00, 0, 'Brisbane', 'QLD', 'multiple', '54646847456564', 'f@g.com', 1, 'approved', 0, 1, '2025-11-09 05:17:32', '2025-11-10 11:27:35', '2025-11-09 05:17:41', 2, NULL),
(18, 2, 'shirt', 'clothing', 'new new new new newn er', 258.00, 0, 'Perth', 'WA', 'multiple', '566468446651', 'f@g.com', 1, 'approved', 0, 0, '2025-11-09 05:19:03', '2025-11-09 05:19:10', '2025-11-09 05:19:10', 2, NULL),
(19, 2, 'game', 'miscellaneous', 'accccc', 369.00, 0, 'Adelaide', 'SA', 'multiple', '56464645465486', 'f@g.com', 1, 'approved', 0, 0, '2025-11-09 05:19:49', '2025-11-09 05:19:55', '2025-11-09 05:19:55', 2, NULL),
(20, 2, 'hki', 'electronics', 'asdasd', 698.00, 0, 'Hobart', 'TAS', 'multiple', '56465498', 'f@g.com', 1, 'approved', 0, 6, '2025-11-09 05:20:50', '2025-11-12 15:02:18', '2025-11-09 05:20:54', 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_photos`
--

CREATE TABLE `marketplace_photos` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `photo_path` varchar(500) NOT NULL,
  `photo_order` tinyint(4) DEFAULT 1,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketplace_photos`
--

INSERT INTO `marketplace_photos` (`id`, `item_id`, `photo_path`, `photo_order`, `file_size`, `mime_type`, `created_at`) VALUES
(28, 15, 'uploads/marketplace/marketplace_15_6910237336144.jpg', 1, 495968, 'image/jpeg', '2025-11-09 05:15:31'),
(29, 15, 'uploads/marketplace/marketplace_15_6910237337020.jpg', 2, 495968, 'image/jpeg', '2025-11-09 05:15:31'),
(30, 15, 'uploads/marketplace/marketplace_15_6910237337336.jpg', 3, 495968, 'image/jpeg', '2025-11-09 05:15:31'),
(31, 15, 'uploads/marketplace/marketplace_15_6910237338378.jpg', 4, 495968, 'image/jpeg', '2025-11-09 05:15:31'),
(32, 15, 'uploads/marketplace/marketplace_15_6910237338696.jpg', 5, 495968, 'image/jpeg', '2025-11-09 05:15:31'),
(33, 16, 'uploads/marketplace/marketplace_16_691023b79f0d9.png', 1, 71605, 'image/png', '2025-11-09 05:16:39'),
(34, 16, 'uploads/marketplace/marketplace_16_691023b7a005c.png', 2, 71643, 'image/png', '2025-11-09 05:16:39'),
(35, 16, 'uploads/marketplace/marketplace_16_691023b7a0429.png', 3, 484326, 'image/png', '2025-11-09 05:16:39'),
(36, 16, 'uploads/marketplace/marketplace_16_691023b7a1013.png', 4, 2637485, 'image/png', '2025-11-09 05:16:39'),
(37, 16, 'uploads/marketplace/marketplace_16_691023b7a12d4.png', 5, 51581, 'image/png', '2025-11-09 05:16:39'),
(38, 17, 'uploads/marketplace/marketplace_17_691023ed0054b.png', 1, 1999293, 'image/png', '2025-11-09 05:17:33'),
(39, 17, 'uploads/marketplace/marketplace_17_691023ed0127a.png', 2, 3258950, 'image/png', '2025-11-09 05:17:33'),
(40, 17, 'uploads/marketplace/marketplace_17_691023ed017a5.png', 3, 1426988, 'image/png', '2025-11-09 05:17:33'),
(41, 17, 'uploads/marketplace/marketplace_17_691023ed01ab1.png', 4, 94353, 'image/png', '2025-11-09 05:17:33'),
(42, 17, 'uploads/marketplace/marketplace_17_691023ed02aed.png', 5, 73006, 'image/png', '2025-11-09 05:17:33'),
(43, 18, 'uploads/marketplace/marketplace_18_691024475dfd4.png', 1, 1524348, 'image/png', '2025-11-09 05:19:03'),
(44, 18, 'uploads/marketplace/marketplace_18_691024475ef84.png', 2, 2710969, 'image/png', '2025-11-09 05:19:03'),
(45, 18, 'uploads/marketplace/marketplace_18_691024475ff71.png', 3, 2522723, 'image/png', '2025-11-09 05:19:03'),
(46, 18, 'uploads/marketplace/marketplace_18_69102447601fd.png', 4, 24954, 'image/png', '2025-11-09 05:19:03'),
(47, 18, 'uploads/marketplace/marketplace_18_69102447604af.png', 5, 808540, 'image/png', '2025-11-09 05:19:03'),
(48, 19, 'uploads/marketplace/marketplace_19_69102475f0247.png', 1, 3825748, 'image/png', '2025-11-09 05:19:49'),
(49, 19, 'uploads/marketplace/marketplace_19_69102475f1468.png', 2, 1563106, 'image/png', '2025-11-09 05:19:49'),
(50, 19, 'uploads/marketplace/marketplace_19_69102475f1761.png', 3, 284763, 'image/png', '2025-11-09 05:19:49'),
(51, 19, 'uploads/marketplace/marketplace_19_69102475f1bf5.png', 4, 1316737, 'image/png', '2025-11-09 05:19:49'),
(52, 19, 'uploads/marketplace/marketplace_19_69102475f1fb4.png', 5, 1421950, 'image/png', '2025-11-09 05:19:49'),
(53, 20, 'uploads/marketplace/marketplace_20_691024b2df97f.png', 1, 1335570, 'image/png', '2025-11-09 05:20:50'),
(54, 20, 'uploads/marketplace/marketplace_20_691024b2e0b51.png', 2, 1590187, 'image/png', '2025-11-09 05:20:50'),
(55, 20, 'uploads/marketplace/marketplace_20_691024b2e0f8a.png', 3, 2626777, 'image/png', '2025-11-09 05:20:50'),
(56, 20, 'uploads/marketplace/marketplace_20_691024b2e13ea.png', 4, 2448503, 'image/png', '2025-11-09 05:20:50'),
(57, 20, 'uploads/marketplace/marketplace_20_691024b2e2200.png', 5, 1890735, 'image/png', '2025-11-09 05:20:50');

-- --------------------------------------------------------

--
-- Table structure for table `message_rate_limits`
--

CREATE TABLE `message_rate_limits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_rate_limits`
--

INSERT INTO `message_rate_limits` (`id`, `user_id`, `created_at`) VALUES
(46, 18, '2025-11-23 11:12:31'),
(49, 18, '2025-11-23 11:13:27'),
(51, 18, '2025-11-23 11:13:51'),
(53, 18, '2025-11-23 11:32:36'),
(55, 18, '2025-11-23 11:50:31'),
(56, 18, '2025-11-23 11:50:36'),
(45, 19, '2025-11-23 11:11:55'),
(47, 19, '2025-11-23 11:12:41'),
(48, 19, '2025-11-23 11:13:16'),
(50, 19, '2025-11-23 11:13:42'),
(52, 20, '2025-11-23 11:31:44'),
(54, 20, '2025-11-23 11:36:46');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `stripe_payment_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'usd',
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_type` enum('room_posting','subscription','boost','event_banner') DEFAULT 'room_posting',
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `stripe_payment_id`, `user_id`, `amount`, `currency`, `status`, `payment_type`, `stripe_session_id`, `metadata`, `created_at`, `updated_at`) VALUES
(1, '', 2, 25.00, 'usd', 'completed', 'subscription', 'manual_payment_2', NULL, '2025-10-09 13:59:39', '2025-10-09 13:59:39'),
(10, 'pi_3SH3pODbk9v7YnqR11wKPETC', 2, 15.00, 'usd', 'completed', 'boost', 'cs_test_a1zuTzdsnoHk62TnehSiOzXCXxm2iWqZL1UQw13VM7sPygHZEPZZIvoH6D', '{\"user_id\":\"2\",\"room_id\":\"39\",\"payment_type\":\"boost\"}', '2025-10-11 14:31:06', '2025-10-11 14:31:06'),
(11, 'cs_test_a1KE5dl0HSEGsFsQHccdnA72EozGtujzD0pEKeWr3sjNzRkx1FuB67HjIW', 2, 50.00, 'usd', 'completed', 'subscription', 'cs_test_a1KE5dl0HSEGsFsQHccdnA72EozGtujzD0pEKeWr3sjNzRkx1FuB67HjIW', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"premium\"}', '2025-10-11 14:33:52', '2025-10-11 14:33:52'),
(12, 'pi_3SH4ghDbk9v7YnqR3NDIyfCQ', 2, 10.00, 'usd', 'completed', 'boost', 'cs_test_a118KMeQWudvnaXOIztB8GrIAWDKaA4EXPELnHt8AEX3mjhzgHrUCfVAlt', '{\"user_id\":\"2\",\"room_id\":\"44\",\"payment_type\":\"boost\"}', '2025-10-11 15:26:13', '2025-10-11 15:26:13'),
(13, 'pi_3SH4i2Dbk9v7YnqR0r08As2c', 2, 10.00, 'usd', 'completed', 'boost', 'cs_test_a13Lb8PQDDCWe6P6gdMiIzvB6ydS5Gf5ObPMUJMo4CzCOV2EFuCrvaCz6L', '{\"user_id\":\"2\",\"room_id\":\"45\",\"payment_type\":\"boost\"}', '2025-10-11 15:27:34', '2025-10-11 15:27:34'),
(14, 'pi_3SIT3lDbk9v7YnqR2IywUoFJ', 2, 10.00, 'usd', 'completed', 'boost', 'cs_test_a1E3NGlI5AN3DVfIxqjzY0CnIZe7R78ImQ1xGwQuMD8Uop53PbxwfD4n1X', '{\"user_id\":\"2\",\"room_id\":\"47\",\"payment_type\":\"boost\"}', '2025-10-15 11:40:13', '2025-10-15 11:40:13'),
(30, 'cs_test_a1kKDDJj15VJrtezhIFWCwvdQBFWTgURVlYBlv2FJ12kFmW4b1yBxYdea9', 2, 50.00, 'usd', 'completed', 'subscription', 'cs_test_a1kKDDJj15VJrtezhIFWCwvdQBFWTgURVlYBlv2FJ12kFmW4b1yBxYdea9', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"premium\"}', '2025-10-15 13:14:47', '2025-10-15 13:14:47'),
(31, 'pi_3SIY6kDbk9v7YnqR3BjBn6u3', 2, 10.00, 'usd', 'completed', 'boost', 'cs_test_a1INnj1h4xgoeRdnz8PwitFCLj7vTLdNvybLB2s37JTsUF9pUHaSaICvv0', '{\"user_id\":\"2\",\"room_id\":\"52\",\"payment_type\":\"boost\"}', '2025-10-15 17:03:28', '2025-10-15 17:03:28'),
(32, 'cs_test_a1HAYKLxE9QP9c8JJ8LCQkagSj0xRVG1DjRgBIncWJfRZ9C3S6sEmmk4Rh', 2, 50.00, 'usd', 'completed', 'subscription', 'cs_test_a1HAYKLxE9QP9c8JJ8LCQkagSj0xRVG1DjRgBIncWJfRZ9C3S6sEmmk4Rh', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"premium\"}', '2025-10-15 17:09:07', '2025-10-15 17:09:07'),
(33, 'pi_3SIYCwDbk9v7YnqR3m8enjgv', 2, 15.00, 'usd', 'completed', 'boost', 'cs_test_a1ZmgH31JD9HemSOEGHMHuJ24Jalfeiu9TCqBq9lmQUmc2keNvB0iPh0Uz', '{\"user_id\":\"2\",\"room_id\":\"52\",\"payment_type\":\"boost\"}', '2025-10-15 17:09:52', '2025-10-15 17:09:52'),
(34, 'cs_test_a12z5AKe6ikqglb9c9MBSB55VoSIShgCugftENwHsEpJ7P479cHoUq8hzc', 2, 50.00, 'usd', 'completed', 'subscription', 'cs_test_a12z5AKe6ikqglb9c9MBSB55VoSIShgCugftENwHsEpJ7P479cHoUq8hzc', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"premium\"}', '2025-10-15 17:10:44', '2025-10-15 17:10:44'),
(35, 'cs_test_a1OXnTCZKqFQs4VF2K2nvea5OK0jYDDk494tsPvC2ZHfjzyeKRVPQ4IiyU', 2, 50.00, 'usd', 'completed', 'subscription', 'cs_test_a1OXnTCZKqFQs4VF2K2nvea5OK0jYDDk494tsPvC2ZHfjzyeKRVPQ4IiyU', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"premium\"}', '2025-10-15 17:13:21', '2025-10-15 17:13:21'),
(36, 'pi_3SIYGaDbk9v7YnqR3tYMMlCe', 2, 10.00, 'usd', 'completed', 'boost', 'cs_test_a1SMrkBWVerwQOMojFlwiTYDMEpHi4L06KL0L6PdEnqWHi2K7lnNIOCQkR', '{\"user_id\":\"2\",\"room_id\":\"52\",\"payment_type\":\"boost\"}', '2025-10-15 17:13:38', '2025-10-15 17:13:38'),
(37, 'pi_3SJ5zaDbk9v7YnqR2NduOGHe', 2, 20.00, 'usd', 'completed', 'event_banner', 'cs_test_a1DrBvEmOCsYStO5FxsJ4mRskgKb6LcqCe1jrmcvGGWKcWcB1FdUYnEN1b', '{\"user_id\":\"2\",\"banner_id\":\"18\",\"payment_type\":\"event_banner\"}', '2025-10-17 05:14:19', '2025-10-17 05:14:19'),
(38, 'pi_3SJ67PDbk9v7YnqR3Ah1Iuhp', 2, 20.00, 'usd', 'completed', 'event_banner', 'cs_test_a1FbClA7akVYwGg4XWjhQDmheZAn0xYhY0sOyAqXWEcyHrDYh4oo5Q9jkK', '{\"user_id\":\"2\",\"banner_id\":\"19\",\"payment_type\":\"event_banner\"}', '2025-10-17 05:22:24', '2025-10-17 05:22:24'),
(39, 'pi_3SJ6JaDbk9v7YnqR1glrCXUe', 2, 20.00, 'usd', 'completed', 'event_banner', 'cs_test_a1m08uWezsYZ5qRmChYKGh97w6w4X7xs2ihh6B3K01tZbnNZU3qv3rAiyq', '{\"user_id\":\"2\",\"payment_type\":\"event_banner\",\"banner_id\":\"20\"}', '2025-10-17 05:34:59', '2025-10-17 05:34:59'),
(40, 'pi_3SJ6dZDbk9v7YnqR1bINzX4T', 2, 20.00, 'usd', 'completed', 'event_banner', 'cs_test_a1A9x7G8Nq1g949KVnjUFUFDiPMCPk6Z23OAsC9ItVdMTpceucJsuncHtP', '{\"user_id\":\"2\",\"payment_type\":\"event_banner\",\"banner_id\":\"21\"}', '2025-10-17 05:55:39', '2025-10-17 05:55:39'),
(41, 'cs_test_a1vrNGiQ0JcN75SoC076zUI7UuKfUG2tIxOF69icuXPSdnfcUFTThtQh7B', 2, 25.00, 'usd', 'completed', 'subscription', 'cs_test_a1vrNGiQ0JcN75SoC076zUI7UuKfUG2tIxOF69icuXPSdnfcUFTThtQh7B', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"basic\"}', '2025-10-17 14:46:17', '2025-10-17 14:46:17'),
(42, 'cs_test_a1LaTPVuXBTTHEZ0qOlIYV3PKPY22zNI4LhPxccLcnrkTwDCymmbZPiPAW', 2, 50.00, 'usd', 'completed', 'subscription', 'cs_test_a1LaTPVuXBTTHEZ0qOlIYV3PKPY22zNI4LhPxccLcnrkTwDCymmbZPiPAW', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"premium\"}', '2025-10-17 15:17:38', '2025-10-17 15:17:38'),
(48, 'pi_3SJFa3Dbk9v7YnqR0qmPnyso', 2, 10.00, 'usd', 'completed', 'boost', 'cs_test_a1RWLtTPgx0xVdW25aDlBXB2L1UyMuf213hJKh9uhoNGydDDTTv4nkLIir', '{\"user_id\":\"2\",\"room_id\":\"56\",\"payment_type\":\"boost\"}', '2025-10-17 15:28:36', '2025-10-17 15:28:36'),
(49, 'pi_3SRRI5Dbk9v7YnqR0hS60m4o', 2, 10.00, 'usd', 'completed', 'boost', 'cs_test_a1pwiurjest1g7MxCjsrv6apeSlHc1tkxhOeXxleeeA4H8964yhtkhlM4E', '{\"user_id\":\"2\",\"room_id\":\"60\",\"payment_type\":\"boost\"}', '2025-11-09 05:35:40', '2025-11-09 05:35:40'),
(50, 'pi_3SSbeDDbk9v7YnqR0UPPivOl', 2, 10.00, 'usd', 'completed', 'boost', 'cs_test_a1QjCBW7lrkZ0qyQPVwRArike7C6lyTbahsLOsR851SS8gcobqlEBWNjw2', '{\"user_id\":\"2\",\"room_id\":\"67\",\"payment_type\":\"boost\"}', '2025-11-12 10:51:17', '2025-11-12 10:51:17'),
(51, 'cs_test_a1F4c668p4opwe8SJskSvMLgfuwp4oDg0L7UARxXJnMu8WSaFY7zZSefxT', 2, 25.00, 'usd', 'completed', 'subscription', 'cs_test_a1F4c668p4opwe8SJskSvMLgfuwp4oDg0L7UARxXJnMu8WSaFY7zZSefxT', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"basic\"}', '2025-11-12 11:06:07', '2025-11-12 11:06:07'),
(52, 'cs_test_a1rZrz0YP8aqrzpX3G0SbL33nQ3YOBCxkBdycJjjaNZkNWhVVJ8BmDANgS', 2, 50.00, 'usd', 'completed', 'subscription', 'cs_test_a1rZrz0YP8aqrzpX3G0SbL33nQ3YOBCxkBdycJjjaNZkNWhVVJ8BmDANgS', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"premium\"}', '2025-11-12 11:06:43', '2025-11-12 11:06:43'),
(53, 'cs_test_a1wdXHePtHE9hQWNTjsHioBFcOEWuOJ0yWwGukL3IkqbwyGvMWOQFe1Ycj', 2, 25.00, 'usd', 'completed', 'subscription', 'cs_test_a1wdXHePtHE9hQWNTjsHioBFcOEWuOJ0yWwGukL3IkqbwyGvMWOQFe1Ycj', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"basic\"}', '2025-11-12 11:07:58', '2025-11-12 11:07:58'),
(54, 'cs_test_a1AufKZyMD0O1dkiBZACRepQRPjiKwrsQy0RIKvsuMLwgZO3kqPuarnHCj', 2, 50.00, 'usd', 'completed', 'subscription', 'cs_test_a1AufKZyMD0O1dkiBZACRepQRPjiKwrsQy0RIKvsuMLwgZO3kqPuarnHCj', '{\"user_id\":\"2\",\"payment_type\":\"subscription\",\"plan_type\":\"premium\"}', '2025-11-12 11:08:27', '2025-11-12 11:08:27');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reported_user_id` int(11) DEFAULT NULL,
  `message_id` int(11) DEFAULT NULL,
  `dm_message_id` int(11) DEFAULT NULL,
  `reason` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `reporter_id`, `reported_user_id`, `message_id`, `dm_message_id`, `reason`, `details`, `status`, `created_at`, `reviewed_at`, `reviewed_by`) VALUES
(2, 5, 2, NULL, NULL, 'spam', 'spamming', 'pending', '2025-09-24 12:06:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `suburb` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `rent` decimal(10,2) NOT NULL,
  `bond` decimal(10,2) NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `photo1` varchar(255) DEFAULT NULL,
  `photo2` varchar(255) DEFAULT NULL,
  `photo3` varchar(255) DEFAULT NULL,
  `photo4` varchar(255) DEFAULT NULL,
  `photo5` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_boosted` tinyint(1) DEFAULT 0,
  `boost_expires_at` timestamp NULL DEFAULT NULL,
  `boost_cost` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `user_id`, `address`, `suburb`, `city`, `rent`, `bond`, `contact_name`, `contact_number`, `description`, `photo1`, `photo2`, `photo3`, `photo4`, `photo5`, `created_at`, `is_approved`, `expires_at`, `is_boosted`, `boost_expires_at`, `boost_cost`) VALUES
(66, 2, '102 Flinders Lane, Melbourne VIC 3000, Australia', 'Melbourne CBD', 'Melbourne', 260.00, 4.00, 'Jacob Turner', '+61 451 222 408', 'Spacious room in a clean apartment with a shared kitchen and balcony. Walking distance to Federation Square and tram stops.', '69145d7a89dd4_pexels-fotoaibe-1643383.jpg', '69145d7a8a23e_pexels-jvdm-1457842.jpg', '69145d7a8a56d_pexels-pixabay-271816.jpg', NULL, NULL, '2025-11-12 10:12:10', 1, NULL, 0, NULL, NULL),
(67, 2, '55 Boundary Street, South Brisbane QLD 4101, Australia', 'South Brisbane', 'Brisbane', 230.00, 2.00, 'Chloe Anderson', '+61 423 998 301', 'Private room with air conditioning and city views. Access to pool and gym. Perfect for students or young professionals.', '69145daa23d76_photo-1602595688238-9fffe12d5af3.jpg', '69145daa242ff_photo-1618220179428-22790b461013.jpg', '69145daa24775_premium_photo-1664301231899-5a7b1a621238.jpg', '69145daa24a7f_premium_photo-1676823553207-758c7a66e9bb.jpg', '69145daa250e2_premium_photo-1676968002767-1f6a09891350.jpg', '2025-11-12 10:12:58', 1, NULL, 1, '2025-11-15 05:51:17', 10.00),
(71, 2, '14 Surf Parade, Broadbeach QLD 4218, Australia', 'Broadbeach', 'Gold Coast', 220.00, 2.00, 'Noah Davis', '+61 437 512 780', 'Stylish beachside room in shared apartment. Balcony view of the ocean, Wi-Fi and Netflix included. Close to tram and restaurants.', '6914676eaa506_photo-1583847268964-b28dc8f51f92.jpg', '6914676eaa937_photo-1586023492125-27b2c045efd7.jpg', '6914676eaae02_photo-1589834390005-5d4fb9bf3d32.jpg', '6914676eab2d1_photo-1598928506311-c55ded91a20c.jpg', '6914676eab751_photo-1602595688238-9fffe12d5af3.jpg', '2025-11-12 10:54:38', 1, NULL, 0, NULL, NULL),
(72, 2, '12 Bunda Street, Canberra ACT 2601, Australia', 'City Centre', 'Canberra', 250.00, 3.00, 'Sophie Harrison', '+61 425 778 906', 'Bright, fully furnished bedroom in a quiet apartment complex. 5 minutes from Canberra Centre and bus terminal. Includes heating, Wi-Fi, and shared kitchen. Ideal for working professionals or students.', '691467b81069a_photo-1602595688238-9fffe12d5af3.jpg', '691467b810ba0_premium_photo-1664301231899-5a7b1a621238.jpg', '691467b810f6a_premium_photo-1676823553207-758c7a66e9bb.jpg', '691467b8113cb_premium_photo-1676968002767-1f6a09891350.jpg', '691467b8118ba_premium_photo-1683133752824-b9fd877805f3.jpg', '2025-11-12 10:55:52', 1, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_es` varchar(100) NOT NULL,
  `main_city` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `code`, `name`, `name_es`, `main_city`, `created_at`) VALUES
(1, 'NSW', 'New South Wales', 'Nueva Gales del Sur', 'Sydney', '2025-09-23 15:26:31'),
(2, 'VIC', 'Victoria', 'Victoria', 'Melbourne', '2025-09-23 15:26:31'),
(3, 'QLD', 'Queensland', 'Queensland', 'Brisbane', '2025-09-23 15:26:31'),
(4, 'WA', 'Western Australia', 'Australia Occidental', 'Perth', '2025-09-23 15:26:31'),
(5, 'SA', 'South Australia', 'Australia del Sur', 'Adelaide', '2025-09-23 15:26:31'),
(6, 'TAS', 'Tasmania', 'Tasmania', 'Hobart', '2025-09-23 15:26:31'),
(7, 'NT', 'Northern Territory', 'Territorio del Norte', 'Darwin', '2025-09-23 15:26:31'),
(8, 'ACT', 'Australian Capital Territory', 'Territorio de la Capital Australiana', 'Canberra', '2025-09-23 15:26:31');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_type` enum('basic','premium') NOT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `status` enum('active','canceled','past_due','incomplete') DEFAULT 'active',
  `current_period_start` timestamp NULL DEFAULT NULL,
  `current_period_end` timestamp NULL DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_type`, `stripe_subscription_id`, `status`, `current_period_start`, `current_period_end`, `amount`, `currency`, `created_at`, `updated_at`) VALUES
(1, 2, 'basic', 'manual_test_subscription_2', 'canceled', '2025-10-09 13:59:39', '2025-11-09 09:59:39', 25.00, 'usd', '2025-10-09 13:59:39', '2025-10-17 14:45:35'),
(5, 2, 'premium', 'sub_1SH3s2Dbk9v7YnqRh0LE9pnC', 'canceled', '2025-10-11 14:34:06', '2025-11-11 14:34:06', 50.00, 'usd', '2025-10-11 14:33:52', '2025-10-17 14:45:42'),
(7, 2, 'basic', 'test_sub_1760529640', 'canceled', '2025-10-15 08:00:40', '2025-11-14 08:00:40', 25.00, 'usd', '2025-10-15 12:00:40', '2025-10-17 14:45:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `role` enum('user','admin') DEFAULT 'user',
  `is_verified` tinyint(1) DEFAULT 0,
  `user_type` enum('chat_only','full_access') DEFAULT 'full_access',
  `room_posts_count` int(11) DEFAULT 0,
  `available_posts` int(11) NOT NULL DEFAULT 3,
  `plan_type` enum('free','basic','premium') DEFAULT 'free',
  `plan_expires_at` timestamp NULL DEFAULT NULL,
  `active_listings_limit` int(11) DEFAULT 1,
  `boost_credits` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `profile_picture`, `email`, `password`, `created_at`, `updated_at`, `is_active`, `role`, `is_verified`, `user_type`, `room_posts_count`, `available_posts`, `plan_type`, `plan_expires_at`, `active_listings_limit`, `boost_credits`) VALUES
(2, 'Ashraful', 'Talukder', NULL, 'h@h.com', '$2y$10$jqh0.I4OqNAxpYGYxNipIe3pwq3YzmLjNXWS3DkDn3.BPzeIGnDom', '2025-09-18 10:50:43', '2025-11-21 11:27:39', 1, 'admin', 1, 'full_access', 51, 21, 'premium', '2025-12-12 06:08:27', 10, 0),
(5, 'mahdi', 'ahsan', NULL, 'j@g.com', '$2y$10$bHlp/fQpWpYHtR.RrVOoZeEWGP6aXvXmN4JO8LRerGZ9doAdDtq9m', '2025-09-24 11:44:01', '2025-09-24 11:55:47', 1, 'user', 1, 'full_access', 0, 3, 'free', NULL, 1, 1),
(16, 'new', 'new', NULL, 'r@r.com', '$2y$10$yqUDT5hmXQvXOhPUnFuOmOxnUWuF50Njf2qg4eaEBV4uhaUWx37r.', '2025-11-21 11:32:03', '2025-11-21 11:32:12', 1, 'user', 1, 'full_access', 0, 3, 'free', NULL, 1, 1),
(17, 'Ashraful', 'Talukder', NULL, 'fr@f.com', '$2y$10$Vfb1SWQzdQN8UP5GGjUJDOUGmltx2aO6EovO4N3OXotvSbDo4EznS', '2025-11-21 11:35:40', '2025-11-21 11:35:40', 0, 'user', 0, 'full_access', 0, 3, 'free', NULL, 1, 1),
(18, 'Ashraful', 'Talukder', 'uploads/profile_pictures/profile_69204f57e5d645.67045790.png', 'j@l.com', '$2y$10$ZHCYJWpg/IBcpkYkBFCd/Oajif3xHSxBJ0WR6lKJKXGnpZdcuX2Vm', '2025-11-21 11:39:04', '2025-11-21 11:39:25', 1, 'user', 1, 'full_access', 0, 3, 'free', NULL, 1, 1),
(19, 'Emma', 'Lewis', 'uploads/profile_pictures/profile_692185c07f2107.11713743.png', 'lo@lo.com', '$2y$10$sDDKZOM.kQKogyg8d1jJb.n8PWfh0ZJruSWPeJYmwHzfjfEWm3nza', '2025-11-22 09:43:28', '2025-11-23 11:11:21', 1, 'user', 1, 'full_access', 0, 3, 'free', NULL, 1, 1),
(20, 'mahdi', 'ahsan', 'uploads/profile_pictures/profile_6922f081856af9.39712120.jpg', 'lop@o.com', '$2y$10$zdblEvt6ZfxCLmMRuWpy0OeGGuxPtYqnf7v/x/JyBOi4RFa0mZLYq', '2025-11-23 11:31:13', '2025-11-23 11:31:30', 1, 'user', 1, 'full_access', 0, 3, 'free', NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_blocks`
--

CREATE TABLE `user_blocks` (
  `id` int(11) NOT NULL,
  `blocker_id` int(11) NOT NULL,
  `blocked_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `language` enum('en','es') DEFAULT 'en',
  `timezone` varchar(50) DEFAULT 'UTC',
  `notifications_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `language`, `timezone`, `notifications_enabled`, `created_at`, `updated_at`) VALUES
(1, 2, 'en', 'UTC', 1, '2025-09-23 15:27:51', '2025-11-22 10:46:52'),
(4, 5, 'en', 'UTC', 1, '2025-09-24 11:44:02', '2025-09-24 11:44:02'),
(153, 16, 'en', 'UTC', 1, '2025-11-21 11:32:23', '2025-11-21 11:32:23'),
(154, 18, 'en', 'UTC', 1, '2025-11-21 11:39:44', '2025-11-23 11:50:40'),
(248, 19, 'en', 'UTC', 1, '2025-11-23 11:11:49', '2025-11-23 11:11:49'),
(249, 20, 'en', 'UTC', 1, '2025-11-23 11:31:38', '2025-11-23 11:31:38');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_groups`
--

CREATE TABLE `whatsapp_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `whatsapp_link` varchar(500) NOT NULL,
  `state_code` varchar(5) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `whatsapp_groups`
--

INSERT INTO `whatsapp_groups` (`id`, `name`, `description`, `image`, `whatsapp_link`, `state_code`, `city_name`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Live in Brisbane 🇦🇺', '📍 Find your place, live your vibe! ✨🏠🌆', 'uploads/whatsapp/whatsapp_68ea5c1ceac85.png', 'https://chat.whatsapp.com/JnLJUf7FVQc8SvWAP2XaAy?mode=r_t', 'ACT', 'Brisbane', 1, 2, '2025-10-11 13:31:08', '2025-10-11 13:31:08'),
(2, 'Live in Brisbane 2', '📍 Find your place, live your vibe! ✨🏠🌇', 'uploads/whatsapp/whatsapp_68ea5c5f22a5d.png', 'https://chat.whatsapp.com/JnLJUf7FVQc8SvWAP2XaAy?mode=r_t', 'ACT', '', 1, 2, '2025-10-11 13:32:15', '2025-10-11 13:32:15'),
(3, 'st🧉🚬', '🧉 Vení, mateá y fumate uno con la mejor onda. 🔥🇦🇷🎶', 'uploads/whatsapp/whatsapp_68ea5ca341688.png', 'https://chat.whatsapp.com/BzKsnOHiea1AAFkDqM6wLY?mode=r_t', 'ACT', '', 1, 2, '2025-10-11 13:33:23', '2025-10-11 15:28:51');

