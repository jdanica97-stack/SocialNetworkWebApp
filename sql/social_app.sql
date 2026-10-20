-- Database Schema for SMCC Connect (Social Network Web Application)
-- Database: smcc_connect_db
CREATE DATABASE IF NOT EXISTS `smcc_connect_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smcc_connect_db`;

-- Drop existing tables in reverse dependency order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `post_images`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `follows`;
DROP TABLE IF EXISTS `likes`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. users table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `bio` TEXT DEFAULT NULL,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `cover_photo` VARCHAR(255) DEFAULT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'user',
    `privacy_followers` ENUM('public', 'private') NOT NULL DEFAULT 'public',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. posts table
CREATE TABLE `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `image` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. comments table
CREATE TABLE `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. likes table
CREATE TABLE `likes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    UNIQUE KEY `unique_like_post_user` (`post_id`, `user_id`),
    CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. follows table
CREATE TABLE `follows` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `follower_id` INT NOT NULL,
    `following_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_follower_following` (`follower_id`, `following_id`),
    CONSTRAINT `fk_follows_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_follows_following` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. events table
CREATE TABLE `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `event_date` DATE NOT NULL,
    `event_time` VARCHAR(50) DEFAULT NULL,
    `location` VARCHAR(255) NOT NULL,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_events_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. messages table
CREATE TABLE `messages` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id`   INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `message`     TEXT NOT NULL,
    `is_read`     TINYINT(1) NOT NULL DEFAULT '0',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_messages_sender`       (`sender_id`),
    KEY `idx_messages_receiver`     (`receiver_id`),
    KEY `idx_messages_conversation` (`sender_id`, `receiver_id`),
    CONSTRAINT `fk_messages_sender`   FOREIGN KEY (`sender_id`)   REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. notifications table
CREATE TABLE `notifications` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `recipient_id` INT NOT NULL    COMMENT 'User who receives this notification',
    `actor_id`     INT NOT NULL    COMMENT 'User who performed the action',
    `type`         VARCHAR(40) NOT NULL COMMENT 'message | comment | like_post | like_comment',
    `target_type`  VARCHAR(20) NOT NULL COMMENT 'message | post | comment',
    `target_id`    INT NOT NULL    COMMENT 'ID of the target entity',
    `preview`      VARCHAR(160) DEFAULT NULL COMMENT 'Short text preview',
    `is_read`      TINYINT(1) NOT NULL DEFAULT '0',
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_notif_recipient` (`recipient_id`),
    KEY `idx_notif_unread`    (`recipient_id`, `is_read`),
    KEY `idx_notif_created`   (`created_at` DESC),
    CONSTRAINT `fk_notif_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notif_actor`     FOREIGN KEY (`actor_id`)     REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. post_images table
CREATE TABLE `post_images` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `post_id`    INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT '0',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_post_images_post_id` (`post_id`),
    CONSTRAINT `fk_post_images_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Sample Seed Data
-- Users: passwords hashed with PHP password_hash('password123', PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `bio`, `profile_image`, `role`, `created_at`) VALUES
(1, 'alice_wonder', '$2y$10$PVswc38Z/JDrR05o5TymYOTATOAseMbDa6l4BhK6jznYj95Z4K1/.', 'Alice Wonderland', 'Web developer and open-source enthusiast.', NULL, 'admin', NOW()),
(2, 'bob_builder', '$2y$10$c3DAdkBGY9YDj.vwnoSmw.RY0RQv7Ju3tONif.zln0gsM3lJ/wCuq', 'Bob Builder', 'Passionate about coding, architecture, and technology.', 'bob.png', 'user', NOW()),
(3, 'charlie_brown', '$2y$10$OoRSTbniHwss1bKPGjiYyeB2nYKu84352zTded63nh63RK5/Wa3.y', 'Charlie Brown', 'Coffee lover and backend explorer.', NULL, 'user', NOW());

-- Sample Posts
INSERT INTO `posts` (`id`, `user_id`, `content`, `image`, `created_at`) VALUES
(1, 1, 'Hello world! Welcome to SMCC Connect social network.', 'welcome.png', NOW()),
(2, 2, 'Excited to build this MVC social network web app from scratch!', NULL, NOW()),
(3, 1, 'Loving PHP, MySQL, and clean MVC architecture.', 'mvc_diagram.png', NOW());

-- Sample Comments
INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 2, 'Welcome Alice! Looking forward to your updates.', NOW()),
(2, 1, 3, 'Great to see you here Alice!', NOW()),
(3, 2, 1, 'Awesome project Bob, good luck!', NOW());

-- Sample Likes
INSERT INTO `likes` (`id`, `post_id`, `user_id`) VALUES
(1, 1, 2),
(2, 1, 3),
(3, 2, 1),
(4, 3, 2);

-- Sample Follows
INSERT INTO `follows` (`id`, `follower_id`, `following_id`, `created_at`) VALUES
(1, 2, 1, NOW()),
(2, 3, 1, NOW()),
(3, 1, 2, NOW());

-- Sample Events
INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `created_by`, `created_at`) VALUES
(1, 'Campus Orientation 2026', 'Welcome assembly for all new and returning students at SMCC Gymnasium.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00 AM', 'SMCC Main Gymnasium', 1, NOW()),
(2, 'Tech Innovation Summit', 'Annual IT and CS project exhibit and competition showcasing student web apps.', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '01:00 PM', 'Computer Studies Hall', 1, NOW());
