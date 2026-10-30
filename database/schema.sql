-- ========================================================
-- AI Skills Mirror - Complete Schema for Fresh Installation
-- Database: `skill mirror`
-- ========================================================

CREATE DATABASE IF NOT EXISTS `skill mirror` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `skill mirror`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `user_settings`;
DROP TABLE IF EXISTS `speech_sessions`;
DROP TABLE IF EXISTS `coach_messages`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `user_achievements`;
DROP TABLE IF EXISTS `achievements`;
DROP TABLE IF EXISTS `user_lessons`;
DROP TABLE IF EXISTS `roadmap_steps`;
DROP TABLE IF EXISTS `assessment_attempts`;
DROP TABLE IF EXISTS `assessment_options`;
DROP TABLE IF EXISTS `assessment_questions`;
DROP TABLE IF EXISTS `user_skills`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `user_metrics`;
DROP TABLE IF EXISTS `profiles`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Profiles Table
CREATE TABLE `profiles` (
  `user_id` INT(11) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `education` VARCHAR(150) DEFAULT NULL,
  `skills` TEXT DEFAULT NULL,
  `career_goal` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `profile_picture` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. User Metrics Table
CREATE TABLE `user_metrics` (
  `user_id` INT(11) NOT NULL,
  `skill_score` INT(11) NOT NULL DEFAULT 78,
  `monthly_pts_gain` INT(11) NOT NULL DEFAULT 14,
  `streak_days` INT(11) NOT NULL DEFAULT 7,
  `minutes_today` INT(11) NOT NULL DEFAULT 14,
  `goal_minutes_today` INT(11) NOT NULL DEFAULT 20,
  `total_sessions` INT(11) NOT NULL DEFAULT 24,
  `time_invested_hours` DECIMAL(5,1) NOT NULL DEFAULT 6.2,
  `xp_points` INT(11) NOT NULL DEFAULT 1240,
  `target_score` INT(11) NOT NULL DEFAULT 85,
  `target_date` VARCHAR(50) DEFAULT 'November 15',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_metrics` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Skills Table
CREATE TABLE `skills` (
  `id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `icon_type` VARCHAR(50) NOT NULL DEFAULT 'TECHNICAL',
  `order_index` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. User Skills Table
CREATE TABLE `user_skills` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `skill_id` VARCHAR(50) NOT NULL,
  `baseline_score` INT(11) NOT NULL DEFAULT 60,
  `current_score` INT(11) NOT NULL DEFAULT 75,
  `points_gained` INT(11) NOT NULL DEFAULT 15,
  `is_top_tier` TINYINT(1) NOT NULL DEFAULT 0,
  `last_assessed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_skill_unique` (`user_id`, `skill_id`),
  CONSTRAINT `fk_user_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_skills_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Assessment Questions Table
CREATE TABLE `assessment_questions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `skill_id` VARCHAR(50) NOT NULL,
  `topic_badge` VARCHAR(100) NOT NULL,
  `question_headline` TEXT NOT NULL,
  `question_scenario` TEXT NOT NULL,
  `question_order` INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_questions_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Assessment Options Table
CREATE TABLE `assessment_options` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `question_id` INT(11) NOT NULL,
  `option_key` VARCHAR(5) NOT NULL,
  `option_text` TEXT NOT NULL,
  `points` INT(11) NOT NULL DEFAULT 10,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `assessment_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Assessment Attempts Table
CREATE TABLE `assessment_attempts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `skill_id` VARCHAR(50) NOT NULL,
  `score` INT(11) NOT NULL,
  `answers_json` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Roadmap Steps Table
CREATE TABLE `roadmap_steps` (
  `id` VARCHAR(50) NOT NULL,
  `week_number` INT(11) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `subtitle` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `lesson_count_text` VARCHAR(50) NOT NULL,
  `icon_type` VARCHAR(50) NOT NULL DEFAULT 'LOGIC',
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. User Lessons Table
CREATE TABLE `user_lessons` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `duration_text` VARCHAR(50) NOT NULL DEFAULT '6 min',
  `duration_minutes` INT(11) NOT NULL DEFAULT 6,
  `xp_text` VARCHAR(50) NOT NULL DEFAULT '+30 XP',
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_lessons_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Achievements Table
CREATE TABLE `achievements` (
  `id` VARCHAR(50) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `icon_type` VARCHAR(50) NOT NULL DEFAULT 'REFLECTION',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. User Achievements Table
CREATE TABLE `user_achievements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `achievement_id` VARCHAR(50) NOT NULL,
  `earned_date_text` VARCHAR(50) NOT NULL,
  `earned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_ach_unique` (`user_id`, `achievement_id`),
  CONSTRAINT `fk_ach_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ach_def` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Notifications Table
CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `timestamp_text` VARCHAR(50) NOT NULL DEFAULT 'Just now',
  `badge_text` VARCHAR(50) DEFAULT NULL,
  `is_green_badge` TINYINT(1) NOT NULL DEFAULT 0,
  `icon_type` VARCHAR(50) NOT NULL DEFAULT 'BOOK',
  `category` VARCHAR(50) NOT NULL DEFAULT 'LEARNING',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Coach Messages Table
CREATE TABLE `coach_messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `sender` ENUM('user', 'coach') NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_coach_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Speech Sessions Table
CREATE TABLE `speech_sessions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `prompt_title` VARCHAR(150) NOT NULL DEFAULT 'Speak with confidence',
  `prompt_quote` TEXT NOT NULL,
  `duration_seconds` INT(11) NOT NULL DEFAULT 58,
  `speech_score` INT(11) NOT NULL DEFAULT 72,
  `baseline_gain_text` VARCHAR(50) NOT NULL DEFAULT '+8 pts from baseline',
  `pronunciation_score` INT(11) NOT NULL DEFAULT 76,
  `fluency_score` INT(11) NOT NULL DEFAULT 70,
  `clarity_score` INT(11) NOT NULL DEFAULT 70,
  `transcript` TEXT NOT NULL,
  `ai_tip` TEXT NOT NULL,
  `audio_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_speech_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. User Settings Table
CREATE TABLE `user_settings` (
  `user_id` INT(11) NOT NULL,
  `is_learning_reminders_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `is_personalized_ai_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `is_save_voice_recordings_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `current_appearance` VARCHAR(20) NOT NULL DEFAULT 'Dark',
  `current_language` VARCHAR(20) NOT NULL DEFAULT 'English',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. Password Resets Table
CREATE TABLE `password_resets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
