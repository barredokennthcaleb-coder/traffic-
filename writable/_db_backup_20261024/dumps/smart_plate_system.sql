-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: smart_plate_system
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
-- Current Database: `smart_plate_system`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `smart_plate_system` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `smart_plate_system`;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','0192023a7bbd73250516f069df18b500','2026-02-25 14:51:46');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(20) NOT NULL,
  `manual_name` varchar(255) DEFAULT NULL,
  `owner_name` varchar(150) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `log_time` datetime NOT NULL DEFAULT current_timestamp(),
  `action` enum('IN','OUT') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (152,'NCB1234',NULL,'Avan Montalvo','','','2026-04-05 20:35:07','IN',NULL),(153,'NCB1234',NULL,'Avan Montalvo','','','2026-04-05 20:55:47','IN',NULL),(154,'NCB1234',NULL,'Avan Montalvo','Motorcycle','NMAX','2026-04-05 21:00:27','OUT',NULL),(155,'NCB1234',NULL,'Avan Montalvo','Motorcycle','NMAX','2026-04-05 21:04:39','IN',NULL),(156,'NCB1234',NULL,'Avan Montalvo','Motorcycle','NMAX','2026-04-05 23:18:12','IN',NULL),(157,'BORJAK',NULL,'AVAN MONTA','SUV','KARBAW','2026-04-05 23:21:46','IN',NULL),(158,'BORJAK',NULL,'AVAN MONTA','SUV','KARBAW','2026-04-06 14:43:09','IN',NULL),(159,'TOTO1',NULL,'AVAN MONTA','Car','TRUCK','2026-04-06 15:16:46','IN',NULL),(160,'BORJAK',NULL,'AVAN MONTA','SUV','KARBAW','2026-04-07 10:06:35','IN',NULL),(161,'BORJAK',NULL,'AVAN MONTA','SUV','KARBAW','2026-04-07 10:06:58','IN',NULL),(162,'BORJAK',NULL,'AVAN MONTA','SUV','KARBAW','2026-04-07 10:09:17','OUT',NULL),(163,'BORJAK',NULL,'','','','2026-04-07 15:59:53','IN',NULL),(164,'NCB1234',NULL,'','','','2026-04-07 16:00:00','OUT',NULL),(165,'2213123',NULL,'','','','2026-04-07 16:05:15','IN',NULL),(166,'TUKO123',NULL,'avan staana','Sedan','Toyota','2026-04-07 16:52:19','IN',NULL),(167,'TUKO123',NULL,'avan staana','Motorcycle','Toyota','2026-04-07 17:01:39','OUT',NULL),(168,'TUKO123',NULL,'avan staana','Motorcycle','Toyota','2026-04-07 17:02:10','IN',NULL),(169,'TUKO123',NULL,'avan staana','Motorcycle','Toyota','2026-04-07 17:02:21','OUT',NULL),(170,'TUKO123',NULL,'avan staana','Motorcycle','Toyota','2026-04-07 17:02:22','IN',NULL),(171,'TUKO123',NULL,'avan staana','Motorcycle','Toyota','2026-04-07 17:02:23','OUT',NULL),(172,'TUKO123',NULL,'','','','2026-04-07 17:25:01','IN',NULL),(173,'TUKO',NULL,'','','','2026-04-07 17:35:45','IN',NULL),(174,'123123',NULL,'','','','2026-04-07 17:36:08','IN',NULL),(175,'123123',NULL,'','','','2026-04-07 17:39:59','IN',NULL),(176,'123123',NULL,'','','','2026-04-14 17:08:26','IN',NULL),(177,'ASDAS12312','asdas','','','','2026-04-14 17:30:53','IN',NULL);
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `owners`
--

DROP TABLE IF EXISTS `owners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `owners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_number` varchar(50) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `type` enum('Teacher','Student','Faculty','Guardian') NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_number` (`id_number`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `owners`
--

LOCK TABLES `owners` WRITE;
/*!40000 ALTER TABLE `owners` DISABLE KEYS */;
INSERT INTO `owners` VALUES (6,'12344','MONTA','AVAN','S','Student','Male',NULL,'PUROK BRGY1','1231231231','2026-02-25','jasonlinas040@gmail.com','2026-02-26 05:47:31'),(7,'12222','Montalvo','Avan','s','Student','Male',NULL,'Sipalay','12321312','2004-11-22','heartticker22@gmail.com','2026-03-14 03:34:16'),(8,'12312312','sheran','ed','','Faculty','Male','owner_12312312_1775549910.jpg','purok tumpok,brgy was ag','123','2003-07-03','3243242@gmail.com','2026-04-07 08:18:30'),(9,'122334','staana','avan','montalvo','Student','Male','owner_122334_1775551056.jpg','purok tumpok,brgy was ag','123','2012-07-18','3243242@gmail.com','2026-04-07 08:37:36');
/*!40000 ALTER TABLE `owners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (5,6,'TOTO1','Car','TRUCK','IPHONE','2026-02-26 05:47:59'),(6,6,'BORJAK','SUV','KARBAW','AMBOT','2026-02-26 05:55:07'),(8,7,'NCB1234','Motorcycle','NMAX','12312','2026-04-01 13:05:44'),(9,9,'TUKO123','Motorcycle','Toyota','NMAX','2026-04-07 08:38:04');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-24 16:38:20
