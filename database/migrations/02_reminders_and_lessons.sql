-- ========================================================
-- AI Skills Mirror - Migration 02: Reminders & Lesson Updates
-- Database: `skill mirror`
-- Safe, non-destructive migration
-- ========================================================

USE `skill mirror`;

CREATE TABLE IF NOT EXISTS `reminders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `reminder_time` VARCHAR(50) NOT NULL DEFAULT '07:00 PM',
  `reminder_days` VARCHAR(100) NOT NULL DEFAULT 'Every day',
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_reminders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed initial reminder for test users if empty
INSERT IGNORE INTO `reminders` (`id`, `user_id`, `title`, `reminder_time`, `reminder_days`, `is_enabled`) VALUES
(1, 1, 'Daily speech practice & training', '07:00 PM', 'Every day', 1),
(2, 1, 'Weekly reflection review', '09:00 AM', 'Sundays', 1);

