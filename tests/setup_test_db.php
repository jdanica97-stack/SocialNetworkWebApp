<?php
// tests/setup_test_db.php
// Safe Test Database Initializer for SMCC Connect
// Creates and initializes 'social_app_test' WITHOUT touching production 'social_app'

$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = '';
$charset = 'utf8mb4';
$testDb = 'social_app_test';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset={$charset}", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Create test database safely
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$testDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$testDb}`");

    // Disable foreign keys temporarily for clean setup
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Table schemas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(50) NOT NULL,
          `password` varchar(255) NOT NULL,
          `full_name` varchar(100) NOT NULL,
          `bio` text DEFAULT NULL,
          `profile_image` varchar(255) DEFAULT NULL,
          `cover_photo` varchar(255) DEFAULT NULL,
          `role` varchar(20) NOT NULL DEFAULT 'user',
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `privacy_followers` enum('public','private') NOT NULL DEFAULT 'public',
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `posts` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `content` text NOT NULL,
          `image` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `fk_posts_user` (`user_id`),
          CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `comments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `post_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `content` text NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `fk_comments_post` (`post_id`),
          KEY `fk_comments_user` (`user_id`),
          CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `likes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `post_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_like_post_user` (`post_id`,`user_id`),
          KEY `fk_likes_user` (`user_id`),
          CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `follows` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `follower_id` int(11) NOT NULL,
          `following_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_follower_following` (`follower_id`,`following_id`),
          KEY `fk_follows_following` (`following_id`),
          CONSTRAINT `fk_follows_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `fk_follows_following` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `messages` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sender_id` int(11) NOT NULL,
          `receiver_id` int(11) NOT NULL,
          `message` text NOT NULL,
          `is_read` tinyint(1) DEFAULT 0,
          `is_edited` tinyint(1) DEFAULT 0,
          `is_unsent` tinyint(1) DEFAULT 0,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `fk_messages_sender` (`sender_id`),
          KEY `fk_messages_receiver` (`receiver_id`),
          CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Add is_edited and is_unsent if table already existed without them
    $colCheck = $pdo->query("SHOW COLUMNS FROM `messages` LIKE 'is_edited'")->fetch();
    if (!$colCheck) {
        $pdo->exec("ALTER TABLE `messages` ADD COLUMN `is_edited` tinyint(1) DEFAULT 0 AFTER `is_read`");
    }
    $colCheckUnsent = $pdo->query("SHOW COLUMNS FROM `messages` LIKE 'is_unsent'")->fetch();
    if (!$colCheckUnsent) {
        $pdo->exec("ALTER TABLE `messages` ADD COLUMN `is_unsent` tinyint(1) DEFAULT 0 AFTER `is_edited`");
    }



    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notifications` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `actor_id` int(11) NOT NULL,
          `type` varchar(50) NOT NULL,
          `entity_id` int(11) DEFAULT NULL,
          `message` varchar(255) NOT NULL,
          `is_read` tinyint(1) DEFAULT 0,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `fk_notifications_user` (`user_id`),
          KEY `fk_notifications_actor` (`actor_id`),
          CONSTRAINT `fk_notifications_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `events` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `description` text DEFAULT NULL,
          `event_date` date NOT NULL,
          `event_time` varchar(50) DEFAULT NULL,
          `location` varchar(255) NOT NULL,
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `created_by` (`created_by`),
          CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    return true;
} catch (PDOException $e) {
    fwrite(STDERR, "Test DB setup failed: " . $e->getMessage() . "\n");
    return false;
}
