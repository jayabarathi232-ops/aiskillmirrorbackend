-- Migration: 03_performance_uploads.sql
-- Table to store user-submitted photos, videos, and audios for AI evaluation

CREATE TABLE IF NOT EXISTS `performance_uploads` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `media_type` ENUM('photo', 'video', 'audio') NOT NULL,
  `skill_category` VARCHAR(50) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_name` VARCHAR(150) NOT NULL,
  `file_size_bytes` BIGINT NOT NULL DEFAULT 0,
  `duration_seconds` INT DEFAULT NULL,
  `score` INT NOT NULL DEFAULT 80,
  `analysis_summary` TEXT DEFAULT NULL,
  `ai_feedback` TEXT DEFAULT NULL,
  `metrics_json` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_perf_user` (`user_id`),
  CONSTRAINT `fk_perf_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed sample performance uploads for test users so history is immediately rich
INSERT INTO `performance_uploads` (`user_id`, `media_type`, `skill_category`, `title`, `file_path`, `file_name`, `file_size_bytes`, `duration_seconds`, `score`, `analysis_summary`, `ai_feedback`, `metrics_json`)
VALUES
(1, 'photo', 'photography', 'Portrait Golden Hour Lighting', 'upload/performances/sample_portrait.jpg', 'sample_portrait.jpg', 2450000, NULL, 86, 'Strong rule-of-thirds composition with well-balanced dynamic range.', 'Excellent framing and shadow suppression. Try slightly lowering exposure compensation to avoid blowing out highlight textures.', '{"composition_score":88,"lighting_score":85,"framing_score":85}'),
(1, 'video', 'sports', 'Sprint & Agility Footwork', 'upload/performances/sample_sprint.mp4', 'sample_sprint.mp4', 18400000, 32, 84, 'MediaPipe motion tracking detected 92% knee flexion alignment and fast ground contact recovery.', 'Great acceleration posture and lateral cadence. Maintain higher forward chest tilt during deceleration phases for enhanced stability.', '{"cadence_spm":178,"posture_alignment":91,"acceleration_score":84}'),
(1, 'audio', 'music', 'Vocal Scale & Pitch Harmony', 'upload/performances/sample_vocal.mp3', 'sample_vocal.mp3', 4200000, 48, 89, 'Acoustic pitch tracking verified 94% frequency stability across harmonic transitions.', 'Clear vocal projection and stable diaphragm breath control. Focus on smoothing transitions around the upper octave register.', '{"pitch_accuracy":94,"timbre_warmth":88,"tempo_consistency":86}');
