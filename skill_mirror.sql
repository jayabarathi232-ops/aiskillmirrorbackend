-- Database: `skill mirror`
-- Host: 127.0.0.1
-- Server version: 10.4.24-MariaDB / MySQL 8.0+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `skill mirror` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `skill mirror`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `profiles`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `profiles` (
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `education` varchar(150) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `career_goal` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Sample initial test users (Password: 12345678)
-- Hash: $2y$10$xFyhQ1ky1Qtf2dLgd22UFu6j49m/oGzlZoqWDtGwxr5kCUxUxXp96
-- --------------------------------------------------------

INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Test Student', 'teststudent@example.com', '$2y$10$xFyhQ1ky1Qtf2dLgd22UFu6j49m/oGzlZoqWDtGwxr5kCUxUxXp96', '2026-10-27 05:15:42'),
(2, 'Jayabarathi', 'jayatest2026@example.com', '$2y$10$nXjE5lcBht2LE2cVLbPrDel20QQReQEOFeqwPwWJzahw49xLS/81G', '2026-10-28 04:59:00');

INSERT IGNORE INTO `profiles` (`user_id`, `phone`, `education`, `skills`, `career_goal`, `bio`) VALUES
(1, '9876543210', 'B.Tech Information Technology', 'Java, Kotlin, Python', 'Android Developer', 'Passionate about mobile apps.'),
(2, '9876543211', 'B.E Computer Science', 'AI, ML, Kotlin', 'AI Engineer', 'Specializing in intelligent apps.');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
