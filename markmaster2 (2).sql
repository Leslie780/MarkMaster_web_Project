-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 04:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `markmaster2`
--

-- --------------------------------------------------------

--
-- Table structure for table `advisor_students`
--

CREATE TABLE `advisor_students` (
  `id` int(11) NOT NULL,
  `advisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `advisor_students`
--

INSERT INTO `advisor_students` (`id`, `advisor_id`, `student_id`) VALUES
(1, 32, 27),
(0, 35, 6),
(0, 35, 7),
(0, 35, 8),
(0, 35, 23),
(0, 35, 10);

-- --------------------------------------------------------

--
-- Table structure for table `assessment_components`
--

CREATE TABLE `assessment_components` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` enum('assignment','quiz','project','lab','test') NOT NULL,
  `max_mark` decimal(5,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_components`
--

INSERT INTO `assessment_components` (`id`, `course_id`, `title`, `type`, `max_mark`, `percentage`, `due_date`, `created_at`, `updated_at`) VALUES
(7, 1, 'Individual Assignment 1', 'assignment', 100.00, 5.00, '2025-07-09', '2025-06-13 18:08:42', '2025-06-13 18:21:31'),
(9, 1, 'Group Project', 'project', 100.00, 20.00, '2025-12-31', '2025-06-13 18:19:41', '2025-06-22 17:00:06'),
(10, 1, 'Individual Assignment 2', 'assignment', 100.00, 5.00, '2025-06-20', '2025-06-13 18:21:40', '2025-06-13 18:21:40'),
(11, 1, 'Midterm Test', '', 100.00, 20.00, '2025-12-31', '2025-06-13 18:21:59', '2025-06-22 17:00:06'),
(12, 1, 'Quiz 1', 'quiz', 100.00, 5.00, '2025-12-31', '2025-06-13 18:22:20', '2025-06-22 17:00:06'),
(13, 1, 'Quiz 2', 'quiz', 100.00, 5.00, '2025-12-31', '2025-06-13 18:22:41', '2025-06-22 17:00:06'),
(14, 1, 'Lab', '', 100.00, 10.00, '2025-12-31', '2025-06-13 18:24:10', '2025-06-22 17:00:06'),
(15, 5, 'QUIZ 1', 'quiz', 100.00, 10.00, '2025-06-12', '2025-06-13 18:57:24', '2025-06-19 15:19:38'),
(16, 3, 'Quiz 1', 'quiz', 100.00, 5.00, '2025-07-01', '2025-06-13 19:45:51', '2025-06-13 20:49:18'),
(17, 3, 'Quiz 2', 'quiz', 100.00, 5.00, '2025-06-12', '2025-06-13 20:49:42', '2025-06-13 20:49:42'),
(18, 3, 'Assignment 1', 'assignment', 100.00, 10.00, '2025-12-31', '2025-06-13 20:50:04', '2025-06-22 17:00:06'),
(19, 3, 'Assignment 2', 'assignment', 100.00, 10.00, '2025-06-12', '2025-06-13 20:50:28', '2025-06-13 20:50:28'),
(20, 3, 'Mid-Term Test', '', 100.00, 20.00, '2025-12-31', '2025-06-13 20:51:04', '2025-06-22 17:00:06'),
(21, 3, 'Group Project', 'project', 100.00, 20.00, '2025-12-31', '2025-06-13 20:51:24', '2025-06-22 17:00:06'),
(22, 5, 'Quiz2', 'quiz', 100.00, 60.00, '2025-12-31', '2025-06-19 15:19:58', '2025-06-22 17:00:06'),
(23, 4, 'Quiz 1', 'quiz', 100.00, 10.00, '2025-06-25', '2025-06-22 17:00:29', '2025-06-22 17:01:05'),
(24, 4, 'Assignment 1', 'assignment', 100.00, 10.00, '2025-06-26', '2025-06-22 17:01:33', '2025-06-22 17:01:33'),
(25, 4, 'Group Project', 'project', 100.00, 10.00, '2025-06-27', '2025-06-22 17:01:51', '2025-06-22 17:01:51'),
(26, 4, 'Lab 1', 'lab', 100.00, 10.00, '2025-06-29', '2025-06-22 17:02:01', '2025-06-22 17:02:01'),
(27, 4, 'Mid Test 1', 'test', 100.00, 15.00, '2025-06-29', '2025-06-22 17:02:20', '2025-06-22 17:02:43'),
(28, 4, 'Mid Test 2', 'test', 100.00, 15.00, '2025-06-29', '2025-06-22 17:02:59', '2025-06-22 17:02:59'),
(29, 9, 'QUIZ 1', 'quiz', 100.00, 70.00, '2025-06-25', '2025-06-23 20:22:36', '2025-06-23 20:22:44');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_scores`
--

CREATE TABLE `assessment_scores` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `mark_obtained` decimal(5,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_scores`
--

INSERT INTO `assessment_scores` (`id`, `student_id`, `course_id`, `component_id`, `mark_obtained`, `created_at`, `updated_at`) VALUES
(16, 6, 1, 7, 60.00, '2025-06-13 18:09:53', '2025-06-13 18:09:53'),
(18, 7, 1, 7, 100.00, '2025-06-13 18:09:53', '2025-06-20 12:04:32'),
(22, 6, 1, 10, 90.00, '2025-06-13 18:23:14', '2025-06-13 18:23:14'),
(24, 7, 1, 10, 100.00, '2025-06-13 18:23:14', '2025-06-20 12:04:43'),
(25, 6, 1, 9, 90.00, '2025-06-13 18:23:26', '2025-06-13 18:23:26'),
(27, 7, 1, 9, 100.00, '2025-06-13 18:23:26', '2025-06-20 12:04:38'),
(28, 6, 1, 11, 90.00, '2025-06-13 18:23:34', '2025-06-13 18:23:34'),
(30, 7, 1, 11, 100.00, '2025-06-13 18:23:34', '2025-06-20 12:04:49'),
(31, 6, 1, 12, 90.00, '2025-06-13 18:23:42', '2025-06-13 18:23:42'),
(33, 7, 1, 12, 100.00, '2025-06-13 18:23:42', '2025-06-20 12:04:54'),
(34, 6, 1, 13, 90.00, '2025-06-13 18:23:47', '2025-06-13 18:23:47'),
(36, 7, 1, 13, 100.00, '2025-06-13 18:23:47', '2025-06-20 12:04:58'),
(37, 7, 5, 15, 100.00, '2025-06-13 18:57:40', '2025-06-13 20:04:51'),
(38, 6, 5, 15, 100.00, '2025-06-13 20:24:04', '2025-06-13 20:33:22'),
(40, 6, 3, 16, 80.00, '2025-06-13 20:49:26', '2025-06-13 20:49:26'),
(42, 6, 3, 17, 80.00, '2025-06-13 20:49:51', '2025-06-13 20:49:51'),
(44, 6, 3, 18, 80.00, '2025-06-13 20:50:12', '2025-06-13 20:50:12'),
(46, 6, 3, 19, 80.00, '2025-06-13 20:50:35', '2025-06-13 20:50:35'),
(48, 6, 3, 20, 80.00, '2025-06-13 20:51:31', '2025-06-13 20:51:31'),
(50, 6, 3, 21, 80.00, '2025-06-13 20:51:36', '2025-06-13 20:51:36'),
(51, 7, 5, 22, 100.00, '2025-06-19 15:20:08', '2025-06-19 15:20:08'),
(52, 6, 5, 22, 95.00, '2025-06-19 15:20:08', '2025-06-19 15:20:08'),
(53, 8, 5, 22, 40.00, '2025-06-19 15:30:03', '2025-06-19 15:30:03'),
(54, 8, 5, 15, 40.00, '2025-06-19 15:30:10', '2025-06-19 15:30:10'),
(63, 8, 1, 7, 40.00, '2025-06-20 09:40:15', '2025-06-20 09:40:15'),
(66, 8, 1, 9, 46.00, '2025-06-20 11:14:12', '2025-06-20 11:14:12'),
(67, 8, 1, 12, 54.00, '2025-06-20 11:26:19', '2025-06-20 11:26:19'),
(68, 8, 1, 10, 45.00, '2025-06-20 11:34:20', '2025-06-20 11:34:20'),
(69, 8, 1, 11, 45.00, '2025-06-20 11:34:26', '2025-06-20 11:34:26'),
(70, 13, 1, 7, 56.00, '2025-06-20 11:39:17', '2025-06-20 11:39:17'),
(71, 12, 1, 7, 43.00, '2025-06-20 11:39:17', '2025-06-20 11:39:17'),
(72, 10, 1, 7, 65.00, '2025-06-20 11:39:17', '2025-06-20 11:39:17'),
(73, 11, 1, 7, 76.00, '2025-06-20 11:39:17', '2025-06-20 11:39:17'),
(74, 13, 1, 11, 56.00, '2025-06-20 11:39:59', '2025-06-20 11:39:59'),
(75, 12, 1, 11, 76.00, '2025-06-20 11:39:59', '2025-06-20 11:39:59'),
(76, 10, 1, 11, 56.00, '2025-06-20 11:39:59', '2025-06-20 11:39:59'),
(77, 11, 1, 11, 45.00, '2025-06-20 11:39:59', '2025-06-20 11:39:59'),
(78, 13, 1, 9, 45.00, '2025-06-20 11:40:07', '2025-06-20 11:40:07'),
(79, 12, 1, 9, 46.00, '2025-06-20 11:40:07', '2025-06-20 11:40:07'),
(80, 10, 1, 9, 76.00, '2025-06-20 11:40:07', '2025-06-20 11:40:07'),
(81, 11, 1, 9, 56.00, '2025-06-20 11:40:07', '2025-06-20 11:40:07'),
(82, 13, 1, 10, 56.00, '2025-06-20 11:40:20', '2025-06-20 11:40:20'),
(83, 12, 1, 10, 87.00, '2025-06-20 11:40:20', '2025-06-20 11:40:20'),
(84, 10, 1, 10, 76.00, '2025-06-20 11:40:20', '2025-06-20 11:40:20'),
(85, 11, 1, 10, 67.00, '2025-06-20 11:40:20', '2025-06-20 11:40:20'),
(86, 13, 1, 12, 76.00, '2025-06-20 11:40:31', '2025-06-20 11:40:31'),
(87, 12, 1, 12, 56.00, '2025-06-20 11:40:31', '2025-06-20 11:40:31'),
(88, 10, 1, 12, 56.00, '2025-06-20 11:40:31', '2025-06-20 11:40:31'),
(89, 11, 1, 12, 45.00, '2025-06-20 11:40:31', '2025-06-20 11:40:31'),
(90, 8, 1, 13, 34.00, '2025-06-20 11:40:45', '2025-06-20 11:40:45'),
(91, 13, 1, 13, 45.00, '2025-06-20 11:40:45', '2025-06-20 11:40:45'),
(92, 12, 1, 13, 65.00, '2025-06-20 11:40:45', '2025-06-20 11:40:45'),
(93, 10, 1, 13, 54.00, '2025-06-20 11:40:45', '2025-06-20 11:40:45'),
(94, 11, 1, 13, 78.00, '2025-06-20 11:40:45', '2025-06-20 11:40:45'),
(95, 7, 1, 14, 100.00, '2025-06-20 11:41:01', '2025-06-20 12:05:04'),
(96, 8, 1, 14, 65.00, '2025-06-20 11:41:01', '2025-06-20 11:41:01'),
(97, 6, 1, 14, 56.00, '2025-06-20 11:41:01', '2025-06-20 11:41:01'),
(98, 13, 1, 14, 97.00, '2025-06-20 11:41:01', '2025-06-20 11:41:01'),
(99, 12, 1, 14, 35.00, '2025-06-20 11:41:01', '2025-06-20 11:41:01'),
(100, 10, 1, 14, 23.00, '2025-06-20 11:41:01', '2025-06-20 11:41:01'),
(101, 11, 1, 14, 56.00, '2025-06-20 11:41:01', '2025-06-20 11:41:01'),
(102, 18, 1, 7, 34.00, '2025-06-20 11:56:15', '2025-06-20 11:56:15'),
(103, 17, 1, 7, 56.00, '2025-06-20 11:56:15', '2025-06-20 11:56:15'),
(104, 16, 1, 7, 78.00, '2025-06-20 11:56:15', '2025-06-20 11:56:15'),
(105, 14, 1, 7, 67.00, '2025-06-20 11:56:15', '2025-06-20 11:56:15'),
(106, 15, 1, 7, 67.00, '2025-06-20 11:56:15', '2025-06-20 11:56:15'),
(107, 20, 1, 7, 67.00, '2025-06-20 11:56:15', '2025-06-20 11:56:15'),
(108, 19, 1, 7, 67.00, '2025-06-20 11:56:15', '2025-06-20 11:56:15'),
(109, 18, 1, 9, 45.00, '2025-06-20 11:56:29', '2025-06-20 11:56:29'),
(110, 17, 1, 9, 64.00, '2025-06-20 11:56:29', '2025-06-20 11:56:29'),
(111, 16, 1, 9, 45.00, '2025-06-20 11:56:29', '2025-06-20 11:56:29'),
(112, 14, 1, 9, 45.00, '2025-06-20 11:56:29', '2025-06-20 11:56:29'),
(113, 15, 1, 9, 45.00, '2025-06-20 11:56:29', '2025-06-20 11:56:29'),
(114, 20, 1, 9, 45.00, '2025-06-20 11:56:29', '2025-06-20 11:56:29'),
(115, 19, 1, 9, 45.00, '2025-06-20 11:56:29', '2025-06-20 11:56:29'),
(116, 18, 1, 10, 56.00, '2025-06-20 11:56:43', '2025-06-20 11:56:43'),
(117, 17, 1, 10, 56.00, '2025-06-20 11:56:43', '2025-06-20 11:56:43'),
(118, 16, 1, 10, 56.00, '2025-06-20 11:56:43', '2025-06-20 11:56:43'),
(119, 14, 1, 10, 56.00, '2025-06-20 11:56:43', '2025-06-20 11:56:43'),
(120, 15, 1, 10, 6.00, '2025-06-20 11:56:43', '2025-06-20 11:56:43'),
(121, 20, 1, 10, 56.00, '2025-06-20 11:56:43', '2025-06-20 11:56:43'),
(122, 19, 1, 10, 56.00, '2025-06-20 11:56:43', '2025-06-20 11:56:43'),
(123, 18, 1, 11, 56.00, '2025-06-20 11:57:00', '2025-06-20 11:57:00'),
(124, 17, 1, 11, 65.00, '2025-06-20 11:57:00', '2025-06-20 11:57:00'),
(125, 16, 1, 11, 56.00, '2025-06-20 11:57:00', '2025-06-20 11:57:00'),
(126, 14, 1, 11, 65.00, '2025-06-20 11:57:00', '2025-06-20 11:57:00'),
(127, 15, 1, 11, 56.00, '2025-06-20 11:57:00', '2025-06-20 11:57:00'),
(128, 20, 1, 11, 65.00, '2025-06-20 11:57:00', '2025-06-20 11:57:00'),
(129, 19, 1, 11, 65.00, '2025-06-20 11:57:00', '2025-06-20 11:57:00'),
(130, 18, 1, 12, 65.00, '2025-06-20 11:57:08', '2025-06-20 11:57:08'),
(131, 17, 1, 12, 65.00, '2025-06-20 11:57:08', '2025-06-20 11:57:08'),
(132, 16, 1, 12, 75.00, '2025-06-20 11:57:08', '2025-06-20 11:57:08'),
(133, 14, 1, 12, 65.00, '2025-06-20 11:57:08', '2025-06-20 11:57:16'),
(134, 15, 1, 12, 56.00, '2025-06-20 11:57:08', '2025-06-20 11:57:16'),
(135, 20, 1, 12, 56.00, '2025-06-20 11:57:08', '2025-06-20 11:57:16'),
(136, 19, 1, 12, 45.00, '2025-06-20 11:57:08', '2025-06-20 11:57:16'),
(137, 18, 1, 13, 56.00, '2025-06-20 11:57:30', '2025-06-20 11:57:30'),
(138, 17, 1, 13, 45.00, '2025-06-20 11:57:30', '2025-06-20 11:57:30'),
(139, 16, 1, 13, 34.00, '2025-06-20 11:57:30', '2025-06-20 11:57:30'),
(140, 14, 1, 13, 34.00, '2025-06-20 11:57:30', '2025-06-20 11:57:30'),
(141, 15, 1, 13, 26.00, '2025-06-20 11:57:30', '2025-06-20 11:57:30'),
(142, 20, 1, 13, 76.00, '2025-06-20 11:57:30', '2025-06-20 11:57:30'),
(143, 19, 1, 13, 45.00, '2025-06-20 11:57:30', '2025-06-20 11:57:30'),
(144, 18, 1, 14, 56.00, '2025-06-20 11:57:43', '2025-06-20 11:57:43'),
(145, 17, 1, 14, 56.00, '2025-06-20 11:57:43', '2025-06-20 11:57:43'),
(146, 16, 1, 14, 75.00, '2025-06-20 11:57:43', '2025-06-20 11:57:43'),
(147, 14, 1, 14, 65.00, '2025-06-20 11:57:43', '2025-06-20 11:57:43'),
(148, 15, 1, 14, 45.00, '2025-06-20 11:57:43', '2025-06-20 11:57:43'),
(149, 20, 1, 14, 54.00, '2025-06-20 11:57:43', '2025-06-20 11:57:43'),
(150, 19, 1, 14, 45.00, '2025-06-20 11:57:43', '2025-06-20 11:57:43'),
(151, 23, 1, 7, 67.00, '2025-06-20 12:01:59', '2025-06-20 12:01:59'),
(152, 21, 1, 7, 67.00, '2025-06-20 12:01:59', '2025-06-20 12:01:59'),
(153, 22, 1, 7, 67.00, '2025-06-20 12:01:59', '2025-06-20 12:01:59'),
(154, 24, 1, 7, 76.00, '2025-06-20 12:01:59', '2025-06-20 12:01:59'),
(155, 26, 1, 7, 67.00, '2025-06-20 12:01:59', '2025-06-20 12:01:59'),
(156, 25, 1, 7, 67.00, '2025-06-20 12:01:59', '2025-06-20 12:01:59'),
(157, 23, 1, 9, 67.00, '2025-06-20 12:02:15', '2025-06-20 12:02:15'),
(158, 21, 1, 9, 56.00, '2025-06-20 12:02:15', '2025-06-20 12:02:15'),
(159, 22, 1, 9, 56.00, '2025-06-20 12:02:16', '2025-06-20 12:02:16'),
(160, 24, 1, 9, 76.00, '2025-06-20 12:02:16', '2025-06-20 12:02:16'),
(161, 26, 1, 9, 87.00, '2025-06-20 12:02:16', '2025-06-20 12:02:16'),
(162, 25, 1, 9, 56.00, '2025-06-20 12:02:16', '2025-06-20 12:02:16'),
(163, 23, 1, 10, 56.00, '2025-06-20 12:02:28', '2025-06-20 12:02:28'),
(164, 21, 1, 10, 45.00, '2025-06-20 12:02:28', '2025-06-20 12:02:28'),
(165, 22, 1, 10, 45.00, '2025-06-20 12:02:28', '2025-06-20 12:02:28'),
(166, 24, 1, 10, 56.00, '2025-06-20 12:02:28', '2025-06-20 12:02:28'),
(167, 26, 1, 10, 45.00, '2025-06-20 12:02:28', '2025-06-20 12:02:28'),
(168, 25, 1, 10, 78.00, '2025-06-20 12:02:28', '2025-06-20 12:02:28'),
(169, 23, 1, 11, 45.00, '2025-06-20 12:02:42', '2025-06-20 12:02:42'),
(170, 21, 1, 11, 65.00, '2025-06-20 12:02:42', '2025-06-20 12:02:42'),
(171, 22, 1, 11, 45.00, '2025-06-20 12:02:42', '2025-06-20 12:02:42'),
(172, 24, 1, 11, 75.00, '2025-06-20 12:02:42', '2025-06-20 12:02:42'),
(173, 26, 1, 11, 89.00, '2025-06-20 12:02:42', '2025-06-20 12:02:42'),
(174, 25, 1, 11, 90.00, '2025-06-20 12:02:42', '2025-06-20 12:02:42'),
(175, 23, 1, 12, 89.00, '2025-06-20 12:02:56', '2025-06-20 12:02:56'),
(176, 21, 1, 12, 78.00, '2025-06-20 12:02:56', '2025-06-20 12:02:56'),
(177, 22, 1, 12, 78.00, '2025-06-20 12:02:56', '2025-06-20 12:02:56'),
(178, 24, 1, 12, 9.00, '2025-06-20 12:02:56', '2025-06-20 12:02:56'),
(179, 26, 1, 12, 78.00, '2025-06-20 12:02:56', '2025-06-20 12:02:56'),
(180, 25, 1, 12, 67.00, '2025-06-20 12:02:56', '2025-06-20 12:02:56'),
(181, 23, 1, 14, 76.00, '2025-06-20 12:03:08', '2025-06-20 12:03:08'),
(182, 21, 1, 14, 67.00, '2025-06-20 12:03:08', '2025-06-20 12:03:08'),
(183, 22, 1, 14, 65.00, '2025-06-20 12:03:08', '2025-06-20 12:03:08'),
(184, 24, 1, 14, 67.00, '2025-06-20 12:03:08', '2025-06-20 12:03:08'),
(185, 26, 1, 14, 46.00, '2025-06-20 12:03:08', '2025-06-20 12:03:08'),
(186, 25, 1, 14, 65.00, '2025-06-20 12:03:08', '2025-06-20 12:03:08'),
(187, 23, 1, 13, 76.00, '2025-06-20 12:03:21', '2025-06-20 12:03:21'),
(188, 21, 1, 13, 87.00, '2025-06-20 12:03:21', '2025-06-20 12:03:21'),
(189, 22, 1, 13, 87.00, '2025-06-20 12:03:21', '2025-06-20 12:03:21'),
(190, 24, 1, 13, 56.00, '2025-06-20 12:03:21', '2025-06-20 12:03:21'),
(191, 26, 1, 13, 56.00, '2025-06-20 12:03:21', '2025-06-20 12:03:21'),
(192, 25, 1, 13, 78.00, '2025-06-20 12:03:21', '2025-06-20 12:03:21'),
(193, 11, 5, 15, 0.00, '2025-06-20 13:26:28', '2025-06-20 13:26:28'),
(194, 10, 5, 15, 0.00, '2025-06-20 13:26:28', '2025-06-20 13:26:28'),
(195, 13, 5, 15, 0.00, '2025-06-20 13:26:28', '2025-06-20 13:26:28'),
(196, 12, 5, 15, 0.00, '2025-06-20 13:26:28', '2025-06-20 13:26:28'),
(197, 26, 5, 15, 56.00, '2025-06-20 13:26:28', '2025-06-20 13:26:28'),
(198, 11, 5, 22, 0.00, '2025-06-20 13:26:33', '2025-06-20 13:26:33'),
(199, 10, 5, 22, 0.00, '2025-06-20 13:26:33', '2025-06-20 13:26:33'),
(200, 13, 5, 22, 0.00, '2025-06-20 13:26:33', '2025-06-20 13:26:33'),
(201, 12, 5, 22, 0.00, '2025-06-20 13:26:33', '2025-06-20 13:26:33'),
(202, 26, 5, 22, 57.00, '2025-06-20 13:26:33', '2025-06-20 13:26:33'),
(203, 7, 3, 16, 56.00, '2025-06-22 16:48:01', '2025-06-22 16:48:01'),
(204, 7, 3, 17, 75.00, '2025-06-22 16:48:08', '2025-06-22 16:48:08'),
(205, 7, 3, 18, 46.00, '2025-06-22 16:48:12', '2025-06-22 16:48:12'),
(206, 7, 3, 19, 74.00, '2025-06-22 16:48:18', '2025-06-22 16:48:18'),
(207, 7, 3, 20, 74.00, '2025-06-22 16:48:23', '2025-06-22 16:48:23'),
(208, 7, 3, 21, 74.00, '2025-06-22 16:48:28', '2025-06-22 16:48:28'),
(209, 7, 4, 23, 78.00, '2025-06-22 17:03:30', '2025-06-22 17:03:30'),
(210, 6, 4, 23, 56.00, '2025-06-22 17:03:30', '2025-06-22 17:03:30'),
(211, 7, 4, 24, 78.00, '2025-06-22 17:03:35', '2025-06-22 17:03:35'),
(212, 6, 4, 24, 57.00, '2025-06-22 17:03:35', '2025-06-22 17:03:35'),
(213, 7, 4, 25, 78.00, '2025-06-22 17:03:41', '2025-06-22 17:03:41'),
(214, 6, 4, 25, 56.00, '2025-06-22 17:03:41', '2025-06-22 17:03:41'),
(215, 7, 4, 26, 87.00, '2025-06-22 17:03:48', '2025-06-22 17:03:48'),
(216, 6, 4, 26, 56.00, '2025-06-22 17:03:48', '2025-06-22 17:03:48'),
(217, 7, 4, 27, 87.00, '2025-06-22 17:03:54', '2025-06-22 17:03:54'),
(218, 6, 4, 27, 98.00, '2025-06-22 17:03:54', '2025-06-22 17:03:54'),
(219, 7, 4, 28, 68.00, '2025-06-22 17:03:59', '2025-06-22 17:03:59'),
(220, 6, 4, 28, 79.00, '2025-06-22 17:03:59', '2025-06-22 17:03:59'),
(221, 36, 9, 29, 89.00, '2025-06-23 20:23:02', '2025-06-23 20:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `credit_hours` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `lecturer_id`, `academic_year`, `semester`, `credit_hours`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SECJ1235', 'Software Engineering', 2, '2025/2026', '1', 3, 'introduce Software Engineering', 'active', '2025-06-13 11:16:10', '2025-06-23 20:19:15'),
(2, 'SECJ3343', 'Web Technology', 4, '2024/2025', '1', 3, 'Introduce web technology', 'active', '2025-06-13 11:26:01', '2025-06-22 23:12:24'),
(3, 'SECJ3384', 'Software Quality Assurance', 5, '2024/2025', '1', 3, '123', 'active', '2025-06-13 11:27:24', '2025-06-13 11:27:24'),
(4, 'SECJ4512', 'COA', 5, '2024/2025', '1', 4, '123213', 'active', '2025-06-13 11:47:02', '2025-06-13 11:47:02'),
(5, 'SECJ3418', 'Web Technology', 2, '2024/2025', '2', 3, '12323', 'active', '2025-06-13 18:56:51', '2025-06-13 18:56:51'),
(6, 'SECJ3623', 'Mobile Application Programming', 5, '2025/2026', '2', 3, '1234', 'active', '2025-06-20 09:41:56', '2025-06-20 09:41:56'),
(9, 'SECJ2212', 'MAP', 5, '2024/2025', '2', 4, 'Introduce flutter 1', 'active', '2025-06-23 20:19:54', '2025-06-23 20:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `course_students`
--

CREATE TABLE `course_students` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_students`
--

INSERT INTO `course_students` (`id`, `student_id`, `course_id`, `created_at`) VALUES
(6, 7, 1, '2025-06-13 18:09:36'),
(7, 7, 5, '2025-06-13 18:57:10'),
(8, 6, 5, '2025-06-13 20:23:51'),
(9, 6, 3, '2025-06-13 20:49:08'),
(10, 8, 5, '2025-06-19 15:29:47'),
(13, 8, 1, '2025-06-20 09:34:33'),
(15, 6, 1, '2025-06-20 09:40:34'),
(17, 11, 5, '2025-06-20 11:30:10'),
(18, 10, 5, '2025-06-20 11:30:12'),
(19, 13, 1, '2025-06-20 11:36:44'),
(20, 12, 1, '2025-06-20 11:36:45'),
(21, 10, 1, '2025-06-20 11:36:47'),
(22, 11, 1, '2025-06-20 11:36:48'),
(23, 13, 5, '2025-06-20 11:37:10'),
(24, 12, 5, '2025-06-20 11:37:12'),
(25, 18, 1, '2025-06-20 11:45:39'),
(26, 17, 1, '2025-06-20 11:45:41'),
(27, 16, 1, '2025-06-20 11:45:42'),
(28, 14, 1, '2025-06-20 11:45:44'),
(29, 15, 1, '2025-06-20 11:45:45'),
(30, 20, 1, '2025-06-20 11:46:32'),
(31, 19, 1, '2025-06-20 11:46:33'),
(32, 23, 1, '2025-06-20 12:00:42'),
(33, 21, 1, '2025-06-20 12:00:43'),
(34, 22, 1, '2025-06-20 12:00:45'),
(35, 24, 1, '2025-06-20 12:01:37'),
(36, 26, 1, '2025-06-20 12:01:39'),
(37, 25, 1, '2025-06-20 12:01:40'),
(38, 26, 5, '2025-06-20 13:26:11'),
(39, 17, 5, '2025-06-21 18:36:21'),
(40, 15, 5, '2025-06-21 18:42:39'),
(41, 20, 5, '2025-06-21 18:46:02'),
(42, 24, 5, '2025-06-21 18:56:13'),
(43, 7, 3, '2025-06-22 16:47:48'),
(44, 7, 4, '2025-06-22 17:03:17'),
(45, 6, 4, '2025-06-22 17:03:19'),
(46, 30, 1, '2025-06-22 18:54:39'),
(48, 29, 1, '2025-06-22 19:09:46'),
(51, 28, 1, '2025-06-22 19:19:20'),
(53, 31, 5, '2025-06-22 19:21:37'),
(55, 31, 1, '2025-06-22 19:22:50'),
(56, 27, 1, '2025-06-22 19:50:35'),
(57, 29, 5, '2025-06-22 19:50:41'),
(58, 36, 1, '2025-06-22 22:03:06'),
(59, 36, 5, '2025-06-22 22:03:40'),
(60, 36, 4, '2025-06-23 20:21:59'),
(61, 36, 9, '2025-06-23 20:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `final_exam_scores`
--

CREATE TABLE `final_exam_scores` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `mark_obtained` decimal(5,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `final_exam_scores`
--

INSERT INTO `final_exam_scores` (`id`, `student_id`, `course_id`, `mark_obtained`, `created_at`, `updated_at`) VALUES
(2, 6, 1, 81.00, '2025-06-13 19:18:41', '2025-06-22 22:30:31'),
(4, 7, 1, 100.00, '2025-06-13 19:18:45', '2025-06-22 22:30:30'),
(5, 7, 5, 100.00, '2025-06-13 20:03:01', '2025-06-20 13:26:39'),
(6, 6, 5, 100.00, '2025-06-13 20:24:12', '2025-06-20 13:26:39'),
(7, 6, 3, 80.00, '2025-06-13 20:51:48', '2025-06-22 16:48:33'),
(9, 8, 5, 40.00, '2025-06-19 15:30:16', '2025-06-20 13:26:39'),
(12, 8, 1, 76.00, '2025-06-20 11:39:34', '2025-06-22 22:30:31'),
(13, 13, 1, 56.00, '2025-06-20 11:39:34', '2025-06-22 22:30:31'),
(14, 12, 1, 67.00, '2025-06-20 11:39:34', '2025-06-22 22:30:31'),
(15, 10, 1, 100.00, '2025-06-20 11:39:34', '2025-06-22 22:30:31'),
(16, 11, 1, 67.00, '2025-06-20 11:39:34', '2025-06-22 22:30:31'),
(17, 11, 5, 54.00, '2025-06-20 11:41:16', '2025-06-20 13:26:39'),
(18, 10, 5, 45.00, '2025-06-20 11:41:16', '2025-06-20 13:26:39'),
(19, 13, 5, 45.00, '2025-06-20 11:41:16', '2025-06-20 13:26:39'),
(20, 12, 5, 23.00, '2025-06-20 11:41:16', '2025-06-20 13:26:39'),
(21, 18, 1, 74.00, '2025-06-20 11:57:59', '2025-06-22 22:30:31'),
(22, 17, 1, 56.00, '2025-06-20 11:57:59', '2025-06-22 22:30:31'),
(23, 16, 1, 65.00, '2025-06-20 11:57:59', '2025-06-22 22:30:31'),
(24, 14, 1, 34.00, '2025-06-20 11:57:59', '2025-06-22 22:30:31'),
(25, 15, 1, 43.00, '2025-06-20 11:57:59', '2025-06-22 22:30:30'),
(26, 20, 1, 67.00, '2025-06-20 11:57:59', '2025-06-22 22:30:30'),
(27, 19, 1, 67.00, '2025-06-20 11:57:59', '2025-06-22 22:30:30'),
(28, 23, 1, 67.00, '2025-06-20 12:03:34', '2025-06-22 22:30:31'),
(29, 21, 1, 67.00, '2025-06-20 12:03:34', '2025-06-22 22:30:31'),
(30, 22, 1, 65.00, '2025-06-20 12:03:34', '2025-06-22 22:30:31'),
(31, 24, 1, 56.00, '2025-06-20 12:03:34', '2025-06-22 22:30:31'),
(32, 26, 1, 56.00, '2025-06-20 12:03:34', '2025-06-22 22:30:31'),
(33, 25, 1, 56.00, '2025-06-20 12:03:34', '2025-06-22 22:30:31'),
(34, 26, 5, 89.00, '2025-06-20 13:26:39', '2025-06-20 13:26:39'),
(35, 7, 3, 75.00, '2025-06-22 16:48:33', '2025-06-22 16:48:33'),
(36, 7, 4, 1.00, '2025-06-22 17:04:06', '2025-06-23 20:27:36'),
(37, 6, 4, 89.00, '2025-06-22 17:04:06', '2025-06-23 20:27:36'),
(38, 30, 1, 46.00, '2025-06-22 22:24:56', '2025-06-22 22:30:31'),
(39, 29, 1, 73.00, '2025-06-22 22:24:56', '2025-06-22 22:30:31'),
(40, 28, 1, 45.00, '2025-06-22 22:24:56', '2025-06-22 22:30:31'),
(41, 31, 1, 86.00, '2025-06-22 22:24:56', '2025-06-22 22:30:31'),
(42, 27, 1, 77.00, '2025-06-22 22:24:56', '2025-06-22 22:30:31'),
(43, 36, 1, 46.00, '2025-06-22 22:24:56', '2025-06-22 22:30:31'),
(44, 36, 9, 78.00, '2025-06-23 20:23:17', '2025-06-23 20:23:24'),
(45, 36, 4, 78.00, '2025-06-23 20:27:36', '2025-06-23 20:27:36');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_advisor_activity`
--

CREATE TABLE `student_advisor_activity` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `advisor_id` int(11) NOT NULL,
  `type` enum('comment','meeting') NOT NULL,
  `content` text NOT NULL,
  `meeting_time` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_advisor_activity`
--

INSERT INTO `student_advisor_activity` (`id`, `student_id`, `advisor_id`, `type`, `content`, `meeting_time`, `created_at`) VALUES
(0, 23, 35, 'comment', '123', NULL, '2025-06-23 20:04:29'),
(0, 23, 35, 'comment', '123', NULL, '2025-06-23 20:04:31'),
(0, 23, 35, 'meeting', '232', '2025-06-04', '2025-06-23 20:04:36'),
(0, 6, 35, 'meeting', '123', '2025-06-02', '2025-06-23 20:10:21'),
(0, 6, 35, 'comment', '12323', NULL, '2025-06-23 20:10:29'),
(0, 8, 35, 'comment', 'not good', NULL, '2025-06-23 20:12:13'),
(0, 6, 35, 'comment', '23', NULL, '2025-06-24 22:10:12'),
(0, 6, 35, 'meeting', '32', '2025-06-10', '2025-06-24 22:10:17');

-- --------------------------------------------------------

--
-- Table structure for table `student_course_results`
--

CREATE TABLE `student_course_results` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `total_score` decimal(5,2) DEFAULT NULL,
  `grade` varchar(2) DEFAULT NULL,
  `grade_point` decimal(3,2) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','lecturer','student','academic advisor') NOT NULL,
  `matric_no` varchar(50) DEFAULT NULL,
  `staff_no` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `matric_no`, `staff_no`, `phone`, `profile_pic`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$H6aSco.sHdDkfujvB85QMutN/Xa2csJj1PHuzYiwyN2XWbPKMWEhy', 'admin', 'A22EC4018', 's1212', '0177643837', NULL, 'active', '2025-06-13 11:14:47', '2025-06-22 22:39:24'),
(2, 'KATMON', 'katmon@utm.my', '$2y$10$bF5zxGCFTayRA0zaaMllqOiuntLkhLwJ3bR7RV7IYciJWf6facPqi', 'lecturer', NULL, 's23123', '1234564789', NULL, 'active', '2025-06-13 11:15:00', '2025-06-13 16:03:36'),
(4, 'lecturer', 'lecturer@example.com', '$2y$10$KW1FPacwHlivBmaVuLNNc.jEPRxwrP7fk.amRmmxBm.JcTRhaz29C', 'lecturer', NULL, 'S22EC4018', '0177643837', NULL, 'active', '2025-06-13 11:24:50', '2025-06-13 11:24:50'),
(5, 'DR.XING QINHAO', 'XING@utm.my', '$2y$10$Kj2he5IDwwJLIR55DgcZSOSuJfPpRRtUpAGam853Vh43mwlEAQfUe', 'lecturer', NULL, 'S22EC1234', '0177643837', NULL, 'active', '2025-06-13 11:26:43', '2025-06-13 11:26:43'),
(6, 'XING QINHAO', 'XING_student@utm.my', '$2y$10$CrQgc2otQFTKL0S6c9AMO./qSafV0d3MtUmy2KutdgdaR1zVY0dGS', 'student', 'A22EC4015', NULL, '123456789', NULL, 'active', '2025-06-13 15:46:31', '2025-06-22 17:12:21'),
(7, 'Zheng Shuo Yu', 'Zheng@utm.my', '$2y$10$TeY3LjNrh3.bGav3A1Vru.UNENuMmfcvAXOsJtniVvMoiqHigozXS', 'student', 'A22EC4019', NULL, '0177643837', NULL, 'active', '2025-06-13 18:09:27', '2025-06-22 17:53:21'),
(8, 'student 3', 'failure@student.com', '$2y$10$.4J3jrBvwtnLsRV.Xx4Rqe6c6q9UGNLUNRRUedfQomEsKwia/GmBy', 'student', 'A22EC1234', NULL, '0177643837', NULL, 'active', '2025-06-19 15:29:38', '2025-06-19 15:29:38'),
(10, 'student5', 'student5@student.com', '$2y$10$.REzxvH5h0h/Z.NcZetiYu20ThqHxbsN34JKdJGGXNuR7Wgo37x9G', 'student', 'A22EC1238', NULL, '0177643837', NULL, 'active', '2025-06-20 11:29:08', '2025-06-20 11:29:08'),
(11, ' student8', 'student6@Student.com', '$2y$10$NkYbA6rJD6Fg618fclKpD.xdI5czcxbv0IOs4AzyX1EZb1JNuj7Zy', 'student', 'A22EC1236', NULL, '0177643837', NULL, 'active', '2025-06-20 11:29:37', '2025-06-20 12:00:23'),
(12, 'student9', 'student7@student.com', '$2y$10$TDlOJn3Xq1TcnXduN7vQ8up0E7TZOQScZ6Vo2DJrdXKJRnDdZfR22', 'student', 'A22EC1239', NULL, '0177643837', NULL, 'active', '2025-06-20 11:32:34', '2025-06-20 12:00:14'),
(13, 'student10', 'student10@student.com', '$2y$10$B6BBGn99xxF13c/rm4TWSub2FMSnkeB9uV3FKe96FeACYNtvMAawm', 'student', 'A22EC1240', NULL, '1232323', NULL, 'active', '2025-06-20 11:33:22', '2025-06-20 11:33:22'),
(14, 'student11', 'student11@student.com', '$2y$10$nYaLh04U571d1p.YOfwTnu48SdTVe4wNc/ZpPuHWYcLPb0sLO5O7e', 'student', 'A22EC1245', NULL, '1232323', NULL, 'active', '2025-06-20 11:43:36', '2025-06-20 11:43:36'),
(15, 'student12', 'student12@123.com', '$2y$10$du3.lMzggRgD9q5MOPdF8uH4N.FlZLBn0Gy5daTwIkDmxKGnFt/xS', 'student', 'A22EC4056', NULL, '0177232323', NULL, 'active', '2025-06-20 11:44:31', '2025-06-20 11:44:31'),
(16, 'student13', 'student13@123.com', '$2y$10$cEp4vs0.sH8tNgiN/UugsOoahjkVjnqu5giWYRRIPisetOOfJ.gai', 'student', 'A22EC4057', NULL, '0177232323', NULL, 'active', '2025-06-20 11:44:50', '2025-06-20 11:44:50'),
(17, 'student14', 'student14@123.com', '$2y$10$RBv.ViCsfqm0riwaX0pO9eYUvTgnneKlq/EjyNzbiT5EzKFfgA42e', 'student', 'A22EC4058', NULL, '0177232323', NULL, 'active', '2025-06-20 11:45:14', '2025-06-20 11:45:14'),
(18, 'student15', 'student15@123.com', '$2y$10$T7B0zGdrZiWRn42qJmf0SeUKxWhFPxZVbvynCu08VgF4OUI9lR1ve', 'student', 'A22EC4059', NULL, '0177232323', NULL, 'active', '2025-06-20 11:45:32', '2025-06-20 11:45:32'),
(19, 'student16', 'student16@123.com', '$2y$10$WCQPEBarwfc9e7uvnVNTducwUSxlqo0dYtC68A/4ylRbTPoikCx8K', 'student', 'A22EC4061', NULL, '0177232323', NULL, 'active', '2025-06-20 11:45:59', '2025-06-20 11:45:59'),
(20, 'student17', 'student17@123.com', '$2y$10$qdhDCnaiHvfBDiRt0pUI.OPVkirJOUE62Ft/ssxih.RLj3svwg00u', 'student', 'A22EC4062', NULL, '0177232323', NULL, 'active', '2025-06-20 11:46:24', '2025-06-20 11:46:24'),
(21, 'student18', 'student18@123.com', '$2y$10$Yq40nn7dzDKIGd/SpfeIWuk4O.C5wuAwqEeeNy7mNtvYJBrCHT9qq', 'student', 'A22EC4063', NULL, '0177232323', NULL, 'active', '2025-06-20 11:59:27', '2025-06-20 11:59:27'),
(22, 'student19', 'student19@123.com', '$2y$10$9ylM6qD8pGVZcP1IQOlp5.cB/4ZbwQ4PVTMdsy1JhaGspb8MtkT6G', 'student', 'A22EC4064', NULL, '0177232323', NULL, 'active', '2025-06-20 11:59:41', '2025-06-20 11:59:41'),
(23, 'student20', 'student20@123.com', '$2y$10$vNbMncyZcX3wCDezn2GVT.bPfHO4XFPA1sBK4sVnyngmBCkbtvHyK', 'student', 'A22EC4065', NULL, '0177232323', NULL, 'active', '2025-06-20 11:59:55', '2025-06-20 11:59:55'),
(24, 'student21', 'student21@123.com', '$2y$10$GwGSuJyoK1cXh5X25ozAc.SVyIIAHN9Qqi009hiAf5U6q8uTMfSZ6', 'student', 'A22EC4066', NULL, '0177232323', NULL, 'active', '2025-06-20 12:00:58', '2025-06-20 12:00:58'),
(25, 'student22', 'student22@123.com', '$2y$10$i6c3mOv8o40WFI5wF/HF6ut5fGaWJKfIKCXLjEDvFZnPYTe.vD.O.', 'student', 'A22EC4067', NULL, '0177232323', NULL, 'active', '2025-06-20 12:01:08', '2025-06-20 12:01:08'),
(26, 'student23', 'student23@123.com', '$2y$10$BdfDwJUSD9OCV8/Bm723xO4joo.KmOf92PewVvlYV7Aqcq7P.YBT.', 'student', 'A22EC4068', NULL, '0177232323', NULL, 'active', '2025-06-20 12:01:19', '2025-06-20 12:01:19'),
(27, 'Ma LiWei', 'MALIWEI@utm.my', '$2y$10$DqMUqgu9PrBIEzrr8ucCeecWNSBNKxC1wMijdtU3R1Z2.VejDEe/.', 'student', 'A22EC7843', NULL, '0177643837', NULL, 'active', '2025-06-22 18:02:18', '2025-06-22 18:02:18'),
(28, 'John Doe', 'johndoe@example.com', '$2y$10$0xHnUhIjbwqw./PPDwZ4rOAv1osuE2xb8eClqNuZgQksBEkvhnfXe', 'student', 'A20231234', NULL, '0123456789', 'https://yourdomain.com/uploads/avatar123.jpg', 'active', '2025-06-22 18:40:54', '2025-06-22 18:40:54'),
(29, 'John Doe', 'johndoe1@example.com', '$2y$10$OV7tlOaw5OJ01wQjkFE1cO/sVu1JSQZstkhMn4PswNjWy4tzMR2jC', 'student', 'A20231234', NULL, '0123456789', 'https://yourdomain.com/uploads/avatar123.jpg', 'active', '2025-06-22 18:44:46', '2025-06-22 18:44:46'),
(30, 'Ma LiWei1', 'MALIWEI1@utm.my', '$2y$10$0cwumIj0D5CgcLFpct/zM.3G.qnszqZo0i3CPmjdlBmGf6ngxETMa', 'student', 'A22EC7843', NULL, '0177643837', 'http://localhost:8085/uploads/avatar_6857e0a715d947.54463152.jpeg', 'active', '2025-06-22 18:53:27', '2025-06-22 18:53:27'),
(31, 'Zhai CaoYu', 'zhaicaoyu@utm.my', '$2y$10$9x.JFmG9RrdAtTw4EcFBI.4NBUxuLO1Tkg0PIZckDif/lECZfzI3y', 'student', 'A22CS1123', NULL, '1232323', 'http://localhost:8085/uploads/avatar_6857e2a3e477d3.03272778.jpeg', 'active', '2025-06-22 19:01:55', '2025-06-22 19:01:55'),
(35, 'Haslina', 'Haslina@utm.my', '$2y$10$IvVW7zHflYQ1FKc3i3Fid.oPRAhbL3ne0z42ceiYSUjqauMB6FJLS', 'academic advisor', NULL, 'S20ED2321', '1672343432', NULL, 'active', '2025-06-22 19:46:38', '2025-06-22 19:46:38'),
(36, 'Zhai CaoYu1', 'zhaicaoy1u@utm.my', '$2y$10$0BprLN8sfYBi0XkUYHg8WOAs2s1WEV6uJPmCrb6HyWELHkvPHzJIG', 'student', 'A22CS11232', NULL, '1232323', 'http://localhost:8085/uploads/avatar_68580c909f1590.70686320.jpeg', 'active', '2025-06-22 22:00:48', '2025-06-22 22:00:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessment_components`
--
ALTER TABLE `assessment_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `course_students`
--
ALTER TABLE `course_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `final_exam_scores`
--
ALTER TABLE `final_exam_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_advisor_activity`
--
ALTER TABLE `student_advisor_activity`
  ADD KEY `student_advisor_activity_ibfk_1` (`student_id`),
  ADD KEY `student_advisor_activity_ibfk_2` (`advisor_id`);

--
-- Indexes for table `student_course_results`
--
ALTER TABLE `student_course_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessment_components`
--
ALTER TABLE `assessment_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `course_students`
--
ALTER TABLE `course_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `final_exam_scores`
--
ALTER TABLE `final_exam_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_course_results`
--
ALTER TABLE `student_course_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessment_components`
--
ALTER TABLE `assessment_components`
  ADD CONSTRAINT `assessment_components_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD CONSTRAINT `assessment_scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_scores_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_scores_ibfk_3` FOREIGN KEY (`component_id`) REFERENCES `assessment_components` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_students`
--
ALTER TABLE `course_students`
  ADD CONSTRAINT `course_students_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_students_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `final_exam_scores`
--
ALTER TABLE `final_exam_scores`
  ADD CONSTRAINT `final_exam_scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `final_exam_scores_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_advisor_activity`
--
ALTER TABLE `student_advisor_activity`
  ADD CONSTRAINT `student_advisor_activity_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `student_advisor_activity_ibfk_2` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
