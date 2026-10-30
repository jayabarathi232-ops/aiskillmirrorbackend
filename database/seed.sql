-- ========================================================
-- AI Skills Mirror - Database Seed Data
-- ========================================================

USE `skill mirror`;

-- 1. Skills
INSERT IGNORE INTO `skills` (`id`, `name`, `description`, `icon_type`, `order_index`) VALUES
('comm', 'Communication', 'Clarity, expression & active listening', 'COMMUNICATION', 1),
('tech', 'Technical skills', 'Coding, concepts & practical thinking', 'TECHNICAL', 2),
('prob', 'Problem solving', 'Logic, reasoning & decision-making', 'PROBLEM_SOLVING', 3),
('interview', 'Interview readiness', 'Confidence, structure & professional presence', 'INTERVIEW', 4);

-- 2. Assessment Questions for Communication
INSERT IGNORE INTO `assessment_questions` (`id`, `skill_id`, `topic_badge`, `question_headline`, `question_scenario`, `question_order`) VALUES
(1, 'comm', 'ACTIVE LISTENING', 'A teammate disagrees with your approach and asks for your first response.', 'Your teammate says, "I don\'t think this will scale. We should do it the way we did the last project." How do you respond?', 1),
(2, 'comm', 'CLARITY & CONCISENESS', 'Explaining a complex bug to a non-technical manager.', 'You resolved a race condition issue causing data mismatch. How do you summarize it to product leadership?', 2),
(3, 'comm', 'EMPATHY & FEEDBACK', 'Delivering code review suggestions constructively.', 'A colleague submitted code with inefficient queries. What is the most productive feedback style?', 3),
(4, 'comm', 'ASSERTIVENESS', 'Handling tight deadlines without compromising software quality.', 'A stakeholder asks to skip testing to release 3 days early. What is your response?', 4);

-- 3. Options for Questions
INSERT IGNORE INTO `assessment_options` (`id`, `question_id`, `option_key`, `option_text`, `points`, `is_correct`) VALUES
(1, 1, 'A', 'Interrupt them and explain why your way is better immediately.', 0, 0),
(2, 1, 'B', 'Ask them to explain their concerns and listen before responding.', 25, 1),
(3, 1, 'C', 'Tell them to trust your expertise and move forward without discussion.', 5, 0),
(4, 1, 'D', 'Dismiss their input and continue with your original plan.', 0, 0),
(5, 2, 'A', 'Explain using database isolation levels and thread contention diagrams.', 10, 0),
(6, 2, 'B', 'Focus on the user impact: two actions clashed simultaneously, now fixed and verified.', 25, 1),
(7, 2, 'C', 'Tell them it was a backend glitch that they don\'t need details on.', 5, 0),
(8, 2, 'D', 'Forward the raw git pull request diff.', 0, 0),
(9, 3, 'A', 'Point out their mistake publicly and request an overhaul.', 0, 0),
(10, 3, 'B', 'Highlight positive aspects, explain the performance trade-off, and suggest an indexed query.', 25, 1),
(11, 3, 'C', 'Rewrite it yourself without commenting.', 5, 0),
(12, 3, 'D', 'Approve it anyway to avoid conflict.', 0, 0),
(13, 4, 'A', 'Agree immediately and work overtime.', 5, 0),
(14, 4, 'B', 'Transparently present the risk of skipping tests and propose a prioritized minimal scope.', 25, 1),
(15, 4, 'C', 'Refuse aggressively without offering alternatives.', 0, 0),
(16, 4, 'D', 'Ignore the request and deliver whenever ready.', 0, 0);

-- 4. Roadmap Steps
INSERT IGNORE INTO `roadmap_steps` (`id`, `week_number`, `title`, `subtitle`, `description`, `lesson_count_text`, `icon_type`, `is_active`) VALUES
('w1', 1, 'Core fundamentals', 'Week 1 · Foundation & assessment', 'Understand baseline strengths and identify growth targets.', '5 lessons', 'LOGIC', 0),
('w2', 2, 'Communicate with impact', 'Week 2 · 3 of 5 lessons complete', 'Build clarity with structured answers and active listening.', '5 lessons', 'SPEECH', 1),
('w3', 3, 'Think through challenges', 'Week 3 · Logic & problem solving', 'Pattern recognition, decomposition and analytical reasoning.', '5 lessons', 'LOGIC', 0),
('w4', 4, 'Own your interview', 'Week 4 · Mock interviews & review', 'Simulated technical and behavioral interview preparation.', '5 lessons', 'INTERVIEW', 0);

-- 5. Achievements
INSERT IGNORE INTO `achievements` (`id`, `title`, `description`, `icon_type`) VALUES
('1', 'First reflection', 'Completed your initial skill assessment', 'REFLECTION'),
('2', 'Week warrior', 'Maintained a 7-day learning streak', 'WARRIOR'),
('3', 'Voice unlocked', 'Completed your first AI speech recording session', 'VOICE'),
('4', 'Rising expert', 'Reached an overall skill score of 85', 'WARRIOR');

-- 6. Initialize User Metrics & Skills for Seed Users (1 and 2)
INSERT IGNORE INTO `user_metrics` (`user_id`, `skill_score`, `monthly_pts_gain`, `streak_days`, `minutes_today`, `goal_minutes_today`, `total_sessions`, `time_invested_hours`, `xp_points`, `target_score`, `target_date`) VALUES
(1, 78, 14, 7, 14, 20, 24, 6.2, 1240, 85, 'November 15'),
(2, 82, 16, 9, 18, 20, 30, 8.5, 1580, 90, 'December 01');

INSERT IGNORE INTO `user_skills` (`user_id`, `skill_id`, `baseline_score`, `current_score`, `points_gained`, `is_top_tier`) VALUES
(1, 'tech', 74, 86, 12, 1),
(1, 'prob', 66, 80, 14, 1),
(1, 'interview', 58, 74, 16, 0),
(1, 'comm', 58, 72, 14, 0),
(2, 'tech', 78, 88, 10, 1),
(2, 'prob', 70, 84, 14, 1),
(2, 'interview', 64, 80, 16, 1),
(2, 'comm', 62, 76, 14, 0);

INSERT IGNORE INTO `user_lessons` (`user_id`, `title`, `category`, `duration_text`, `duration_minutes`, `xp_text`, `is_completed`) VALUES
(1, 'Active listening essentials', 'Communication · 6 min', '6 min', 6, '+30 XP', 1),
(1, 'Think in patterns', 'Problem solving · 8 min', '8 min', 8, '+40 XP', 1),
(1, 'Tell your project story', 'Speech training · Interview practice', '6 min', 6, '+30 XP', 0);

INSERT IGNORE INTO `user_achievements` (`user_id`, `achievement_id`, `earned_date_text`) VALUES
(1, '1', 'Sep 29'),
(1, '2', 'Oct 20'),
(1, '3', 'Oct 18');

INSERT IGNORE INTO `notifications` (`user_id`, `title`, `description`, `timestamp_text`, `badge_text`, `is_green_badge`, `icon_type`, `category`) VALUES
(1, 'Your next 6 minutes matter', 'Finish today\'s speech practice and complete your 20-minute goal.', '7:00 PM', 'COMING UP', 0, 'BOOK', 'LEARNING'),
(1, 'A week of progress. All you.', 'You\'ve earned the Week Warrior badge with a 7-day learning streak.', '8:15 AM', 'MILESTONE', 1, 'STREAK', 'MILESTONES'),
(1, 'Your plan got smarter', 'We added project storytelling to your communication practice.', 'Yesterday', NULL, 0, 'SPARKLE', 'LEARNING');

INSERT IGNORE INTO `coach_messages` (`user_id`, `sender`, `message`) VALUES
(1, 'coach', 'Morning! Your technical score is 86. Let\'s help your communication catch up. What would you like to work on?'),
(1, 'user', 'How can I explain my project better in an interview?'),
(1, 'coach', 'Try the STAR method. It keeps your answer focused and easy to follow:\n\nS - Set the scene\nT - Explain your responsibility\nA - Describe what you did\nR - Share a measurable result\n\nFor your study app, lead with the problem it solved. Want to practice a 60-second answer?');

INSERT IGNORE INTO `speech_sessions` (`user_id`, `prompt_title`, `prompt_quote`, `duration_seconds`, `speech_score`, `baseline_gain_text`, `pronunciation_score`, `fluency_score`, `clarity_score`, `transcript`, `ai_tip`) VALUES
(1, 'Speak with confidence', '\"Tell me about a project you\'re proud of.\"', 58, 72, '+8 pts from baseline', 76, 70, 70, '\"I built a collaborative study app. My role was to, um, design the API and improve the user experience.\"', 'Try a brief pause instead of "um." Break "collaborative" into five syllables: co-lab-o-ra-tive.');

INSERT IGNORE INTO `user_settings` (`user_id`, `is_learning_reminders_enabled`, `is_personalized_ai_enabled`, `is_save_voice_recordings_enabled`, `current_appearance`, `current_language`) VALUES
(1, 1, 1, 0, 'Dark', 'English');

