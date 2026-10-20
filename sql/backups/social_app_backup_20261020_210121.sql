-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: localhost    Database: social_app
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comments_post` (`post_id`),
  KEY `fk_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (48,55,31,'Testing comment without notification','2026-10-19 12:17:15'),(49,61,31,'Test comment 1792418829','2026-10-19 14:07:09'),(50,64,31,'Debug comment 1792422718','2026-10-19 15:11:58'),(51,65,31,'Test comment 1792422785','2026-10-19 15:13:06'),(52,66,31,'Test comment 1792426029','2026-10-19 16:07:09'),(53,65,32,'sdafds','2026-10-20 11:30:11'),(54,65,32,'dsa','2026-10-20 11:30:13'),(55,65,32,'dasdsa','2026-10-20 11:30:15'),(56,65,32,'dsadsa','2026-10-20 11:30:17'),(57,65,32,'dsa','2026-10-20 11:30:19'),(58,64,32,'dasdas','2026-10-20 11:30:24'),(59,64,32,'dasdas','2026-10-20 11:30:26'),(60,64,32,'dsadsa','2026-10-20 11:30:28'),(61,64,32,'das','2026-10-20 11:30:29'),(62,66,32,'sdfdsf','2026-10-20 11:30:44'),(63,66,32,'dasdsa','2026-10-20 11:30:46'),(64,66,32,'dasdsa','2026-10-20 11:30:49'),(65,66,32,'dsadas','2026-10-20 11:30:51'),(66,66,32,'dsadas','2026-10-20 11:30:54'),(67,66,32,'dasdsa','2026-10-20 11:30:56'),(68,66,32,'dsadsa','2026-10-20 11:30:58');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like_post_user` (`post_id`,`user_id`),
  KEY `fk_likes_user` (`user_id`),
  CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES (58,54,31),(59,55,31),(60,56,31),(61,57,31),(62,58,31),(63,59,31),(64,60,31),(65,61,31),(66,62,31),(67,63,31),(68,65,31),(69,66,31);
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_messages_sender` (`sender_id`),
  KEY `idx_messages_receiver` (`receiver_id`),
  KEY `idx_messages_conversation` (`sender_id`,`receiver_id`),
  CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (31,32,31,'Test unsend message at 1792331635',0,'2026-10-18 13:53:55'),(33,32,31,'hiii',0,'2026-10-19 10:33:51'),(34,32,31,'dsada',0,'2026-10-19 11:54:39');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recipient_id` int NOT NULL COMMENT 'User who receives this notification',
  `actor_id` int NOT NULL COMMENT 'User who performed the action',
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'message | comment | like_post | like_comment',
  `target_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'message | post | comment',
  `target_id` int NOT NULL COMMENT 'ID of the target entity',
  `preview` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Short text preview (message snippet, comment excerpt, etc.)',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_recipient` (`recipient_id`),
  KEY `idx_notif_unread` (`recipient_id`,`is_read`),
  KEY `idx_notif_created` (`created_at` DESC),
  KEY `fk_notif_actor` (`actor_id`),
  CONSTRAINT `fk_notif_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (34,31,32,'message','message',31,'Test unsend message at 1792331635',0,'2026-10-18 13:53:55'),(36,31,32,'message','message',33,'hiii',0,'2026-10-19 10:33:51'),(37,31,32,'message','message',34,'dsada',0,'2026-10-19 11:54:39');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_images`
--

DROP TABLE IF EXISTS `post_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post_images_post_id` (`post_id`),
  CONSTRAINT `fk_post_images_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_images`
--

LOCK TABLES `post_images` WRITE;
/*!40000 ALTER TABLE `post_images` DISABLE KEYS */;
INSERT INTO `post_images` VALUES (1,52,'posts/d32198e0105daabb51259b4661e46dfe.jpg',0,'2026-10-18 11:52:07'),(2,52,'posts/e4bf4784c351783906114dcfa5bf2352.jpg',1,'2026-10-18 11:52:07'),(3,53,'posts/20f8fab534bc9b57e06505e8cdc610f6.png',0,'2026-10-18 13:58:45'),(4,53,'posts/071c00650d9b270f61ffb6fa6b32a0ab.jpg',1,'2026-10-18 13:58:45'),(5,53,'posts/76fc1dfd66c7d1866a93ee8a15d067b2.jpg',2,'2026-10-18 13:58:45'),(6,53,'posts/6651770e6459766d51dfc697ea646dba.jpg',3,'2026-10-18 13:58:45'),(7,53,'posts/debcd2bbbc41a6e3938cb1b3cb53760f.jpg',4,'2026-10-18 13:58:45');
/*!40000 ALTER TABLE `post_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_posts_user` (`user_id`),
  CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (52,32,'Automated test post with multiple photos and gallery grid!','posts/d32198e0105daabb51259b4661e46dfe.jpg','2026-10-18 11:52:07'),(53,32,'dfssd','posts/20f8fab534bc9b57e06505e8cdc610f6.png','2026-10-18 13:58:45'),(54,31,'Automated test post without chat or notifications 1792412112',NULL,'2026-10-19 12:15:13'),(55,31,'Automated test post without chat or notifications 1792412231',NULL,'2026-10-19 12:17:12'),(56,31,'Cleanup audit test post 1792416246',NULL,'2026-10-19 13:24:06'),(57,31,'Cleanup audit test post 1792416756',NULL,'2026-10-19 13:32:36'),(58,31,'Cleanup audit test post 1792417033',NULL,'2026-10-19 13:37:13'),(59,31,'Cleanup audit test post 1792417076',NULL,'2026-10-19 13:37:56'),(60,31,'Cleanup audit test post 1792418749',NULL,'2026-10-19 14:05:49'),(61,31,'Cleanup audit test post 1792418826',NULL,'2026-10-19 14:07:06'),(62,31,'Cleanup audit test post 1792420143',NULL,'2026-10-19 14:29:03'),(63,31,'Cleanup audit test post 1792420295',NULL,'2026-10-19 14:31:35'),(64,31,'Debug post 1792422716',NULL,'2026-10-19 15:11:56'),(65,31,'Cleanup audit test post 1792422783',NULL,'2026-10-19 15:13:03'),(66,31,'Cleanup audit test post 1792426026',NULL,'2026-10-19 16:07:06');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (31,'bob','$2y$10$CDzG.TxCbV.04HDPSMTNQ.yPJP/tYSlDhGXH5y2fhufJxo.B3eXfe','Bob Dylan','Orbit bio updated at 1792425915',NULL,'2026-10-18 10:27:30'),(32,'jan','$2y$10$Z4AnFjQVTtFaA6mNCLV0Vu1poltd/r8mH2q.C8rHTFRzC1Trqlv6O','jan',NULL,NULL,'2026-10-18 10:46:55'),(34,'user_b6ad2015','$2y$10$wvFEbx5vZc6rk3X..NP6O.liZyQ/u9aBla4FOUUfCwWjJu.sLZ8RK','Complexity Tester',NULL,NULL,'2026-10-20 12:32:44');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'social_app'
--

--
-- Dumping routines for database 'social_app'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-20 21:01:21
