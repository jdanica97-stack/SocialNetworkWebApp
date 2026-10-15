-- Database Schema for Mini Social Networking Web Application
-- Database: social_app
CREATE DATABASE IF NOT EXISTS `social_app` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `social_app`;

-- Drop existing tables to ensure clean setup
SET FOREIGN_KEY_CHECKS = 0;
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
    `role` VARCHAR(20) NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. posts table
CREATE TABLE `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `image` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. comments table
CREATE TABLE `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. likes table
CREATE TABLE `likes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    UNIQUE KEY `unique_like_post_user` (`post_id`, `user_id`),
    CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data
-- Sample Users (Passwords are hashed with PHP password_hash():
-- alice_wonder: 'password123'
-- bob_builder: 'secret456'
-- charlie_brown: 'mypassword789')
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `bio`, `profile_image`, `role`, `created_at`) VALUES
(1, 'alice_wonder', '$2y$10$PVswc38Z/JDrR05o5TymYOTATOAseMbDa6l4BhK6jznYj95Z4K1/.', 'Alice Wonderland', 'Web developer and open-source enthusiast.', 'alice.jpg', 'admin', NOW()),
(2, 'bob_builder', '$2y$10$c3DAdkBGY9YDj.vwnoSmw.RY0RQv7Ju3tONif.zln0gsM3lJ/wCuq', 'Bob Builder', 'Passionate about coding, architecture, and technology.', 'bob.png', 'user', NOW()),
(3, 'charlie_brown', '$2y$10$OoRSTbniHwss1bKPGjiYyeB2nYKu84352zTded63nh63RK5/Wa3.y', 'Charlie Brown', 'Coffee lover and backend explorer.', NULL, 'user', NOW());

-- Sample Posts
INSERT INTO `posts` (`id`, `user_id`, `content`, `image`, `created_at`) VALUES
(1, 1, 'Hello world! Welcome to my first post on this mini social network.', 'welcome.png', NOW()),
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
