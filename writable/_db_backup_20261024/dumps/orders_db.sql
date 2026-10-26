-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: orders_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `orders_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `orders_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `orders_db`;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'john mark','abonda','john@example.com','09171234567','2025-10-07 09:19:08','2025-10-07 09:41:14'),(2,'Maria','Santos','maria.santos@example.com','09182345678','2025-10-07 09:19:08','2025-10-07 09:19:08'),(3,'David','Reyes','david.reyes@example.com','09193456789','2025-10-07 09:19:08','2025-10-07 09:19:08'),(4,'Angela','Lopez','angela.lopez@example.com','09204567890','2025-10-07 09:19:08','2025-10-07 09:19:08'),(5,'Robert','Garcia','robert.garcia@example.com','09215678901','2025-10-07 09:19:08','2025-10-07 09:19:08'),(6,'john','canete','johncanete@gmail.com','09515887102','2025-10-07 09:36:02','2025-10-07 09:36:02');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`order_item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,2,599.00,1198.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(2,1,2,1,2499.00,2499.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(3,1,3,1,1299.00,1299.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(4,1,4,1,899.00,899.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(5,1,5,1,3099.00,3099.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(6,2,6,1,999.00,999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(7,2,7,1,4999.00,4999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(8,2,8,2,699.00,1398.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(9,2,9,1,7499.00,7499.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(10,2,10,2,499.00,998.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(11,3,2,1,2499.00,2499.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(12,3,3,2,1299.00,2598.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(13,3,5,1,3599.00,3599.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(14,3,7,1,4999.00,4999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(15,3,10,2,499.00,998.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(16,4,1,3,599.00,1797.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(17,4,2,1,2499.00,2499.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(18,4,4,2,899.00,1798.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(19,4,6,1,999.00,999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(20,4,8,2,699.00,1398.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(21,5,5,2,3599.00,7198.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(22,5,7,1,4999.00,4999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(23,5,8,2,699.00,1398.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(24,5,9,1,7499.00,7499.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(25,5,10,2,499.00,998.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(26,6,1,3,599.00,1797.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(27,6,3,2,1299.00,2598.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(28,6,5,1,3599.00,3599.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(29,6,6,1,999.00,999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(30,6,9,1,7499.00,7499.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(31,7,2,2,2499.00,4998.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(32,7,3,1,1299.00,1299.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(33,7,4,3,899.00,2697.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(34,7,7,1,4999.00,4999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(35,7,10,2,499.00,998.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(36,8,1,4,599.00,2396.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(37,8,2,2,2499.00,4998.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(38,8,3,1,1299.00,1299.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(39,8,6,1,999.00,999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(40,8,8,4,699.00,2796.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(41,9,2,1,2499.00,2499.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(42,9,4,2,899.00,1798.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(43,9,5,1,3599.00,3599.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(44,9,7,1,4999.00,4999.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(45,9,10,2,499.00,998.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(46,10,1,4,599.00,2396.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(47,10,3,2,1299.00,2598.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(48,10,5,1,3599.00,3599.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(49,10,8,2,699.00,1398.00,'2025-10-14 09:24:19','2025-10-14 09:24:19'),(50,10,9,1,7499.00,7499.00,'2025-10-14 09:24:19','2025-10-14 09:24:19');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,'2025-10-10 01:15:00','completed',9895.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(2,2,'2025-10-10 03:45:00','completed',9785.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(3,3,'2025-10-11 06:20:00','completed',10495.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(4,4,'2025-10-11 09:10:00','completed',10495.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(5,5,'2025-10-12 02:05:00','completed',11295.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(6,1,'2025-10-12 04:30:00','pending',10095.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(7,2,'2025-10-13 00:55:00','completed',10695.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(8,3,'2025-10-13 05:45:00','completed',9975.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(9,4,'2025-10-14 01:25:00','pending',9885.00,'2025-10-14 07:42:13','2025-10-14 07:42:13'),(10,5,'2025-10-14 07:40:00','completed',9995.00,'2025-10-14 07:42:13','2025-10-14 07:42:13');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `descriptiON` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_qty` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Wireless Mouse','Ergonomic wireless mouse with adjustable DPI.',599.00,50,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(2,'Mechanical Keyboard','RGB backlit mechanical keyboard with blue switches.',2499.00,30,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(3,'USB-C Hub','6-in-1 USB-C hub with HDMI and card reader.',1299.00,25,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(4,'Laptop Stand','Aluminum adjustable laptop stand for better ergonomics.',899.00,40,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(5,'Noise-Canceling Headphones','Bluetooth over-ear headphones with active noise cancellation.',3599.00,15,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(6,'Webcam 1080p','Full HD USB webcam with built-in microphone.',999.00,20,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(7,'Portable SSD 1TB','High-speed 1TB external SSD with USB 3.2 support.',4999.00,10,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(8,'Smartphone Tripod','Adjustable tripod with Bluetooth remote control.',699.00,35,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(9,'Gaming Chair','Ergonomic gaming chair with lumbar support.',7499.00,8,'2025-10-07 09:19:28','2025-10-07 09:19:28'),(10,'LED Desk Lamp','Touch control LED lamp with adjustable brightness.',499.00,60,'2025-10-07 09:19:28','2025-10-07 09:19:28');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-24 16:38:19
