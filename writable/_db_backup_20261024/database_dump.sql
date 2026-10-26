-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: database
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
-- Current Database: `database`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `database` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `database`;

--
-- Table structure for table `inspections`
--

DROP TABLE IF EXISTS `inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspections` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mtop_no` varchar(50) NOT NULL,
  `route` varchar(255) DEFAULT NULL,
  `operator_name` varchar(255) NOT NULL,
  `operator_cellphone` varchar(20) DEFAULT NULL,
  `driver_name` varchar(255) DEFAULT NULL,
  `driver_license_no` varchar(100) DEFAULT NULL,
  `driver_cellphone` varchar(20) DEFAULT NULL,
  `mv_make_type` varchar(255) DEFAULT NULL,
  `body_color` varchar(100) DEFAULT NULL,
  `cert_attendance` tinyint(1) NOT NULL DEFAULT 0,
  `outside_route` tinyint(1) NOT NULL DEFAULT 0,
  `outside_route_8x8` tinyint(1) NOT NULL DEFAULT 0,
  `city_proper` tinyint(1) NOT NULL DEFAULT 0,
  `trash_can` tinyint(1) NOT NULL DEFAULT 0,
  `mtop_8x16_route` tinyint(1) NOT NULL DEFAULT 0,
  `mtop_8x8_route` tinyint(1) NOT NULL DEFAULT 0,
  `mtop_dashboard` tinyint(1) NOT NULL DEFAULT 0,
  `signal_light_left` tinyint(1) NOT NULL DEFAULT 0,
  `signal_light_right` tinyint(1) NOT NULL DEFAULT 0,
  `head_light` tinyint(1) NOT NULL DEFAULT 0,
  `stop_light` tinyint(1) NOT NULL DEFAULT 0,
  `side_mirror` tinyint(1) NOT NULL DEFAULT 0,
  `horn` tinyint(1) NOT NULL DEFAULT 0,
  `standard_muffler` tinyint(1) NOT NULL DEFAULT 0,
  `color_coding` tinyint(1) NOT NULL DEFAULT 0,
  `tarifa` tinyint(1) NOT NULL DEFAULT 0,
  `inspected_by` varchar(255) DEFAULT NULL,
  `inspection_date` date NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspections`
--

LOCK TABLES `inspections` WRITE;
/*!40000 ALTER TABLE `inspections` DISABLE KEYS */;
INSERT INTO `inspections` VALUES (1,'FM542E',NULL,'KENNETH','09515887102',NULL,NULL,'09639643910','TMX',NULL,1,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,1,'0','2026-05-06','2026-05-06 12:13:57','2026-05-09 01:22:33','2026-05-09 01:22:33'),(2,'123',NULL,'Jet','09',NULL,NULL,'09','honda',NULL,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,'0','2026-05-09','2026-05-09 06:57:42','2026-05-09 06:57:42',NULL);
/*!40000 ALTER TABLE `inspections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'20240424000001','App\\Database\\Migrations\\CreateAdminsTable','default','App',1777040581,1),(2,'20240424000002','App\\Database\\Migrations\\CreateViolationsTable','default','App',1777040581,1),(3,'20240424000003','App\\Database\\Migrations\\CreateUsersTable','default','App',1777040581,1),(4,'20240424000004','App\\Database\\Migrations\\UpdateViolationsTable','default','App',1777040603,2),(5,'20240424000005','App\\Database\\Migrations\\CreateViolationTypesTable','default','App',1777040603,2),(6,'2026-04-24-142535','App\\Database\\Migrations\\AddMissingColumnsToViolationsTable','default','App',1777040762,3),(7,'2026-04-25-231800','App\\Database\\Migrations\\UpdateUserRoles','default','App',1777130432,4),(8,'2026-04-25-232500','App\\Database\\Migrations\\DropAdminsTable','default','App',1777130715,5),(9,'2026-04-29-040500','App\\Database\\Migrations\\AddViolatorDetailsToViolationsTable','default','App',1777436069,6),(10,'2026-04-29-200000','App\\Database\\Migrations\\AddProfileFieldsToUsersTable','default','App',1777464005,7),(11,'2026-04-30-123000','App\\Database\\Migrations\\AddProfileImageToUsersTable','default','App',1777557298,8),(12,'2026-04-30-135433','App\\Database\\Migrations\\AddBirthdateAndMiddleInitialToUsersTable','default','App',1777557298,8),(13,'2026-05-06-052223','App\\Database\\Migrations\\CreateInspectionsTable','default','App',1778045297,9),(14,'2026-05-23-051452','App\\Database\\Migrations\\AddLicenseNumberToViolationsTable','default','App',1779513358,10),(15,'2026-06-03-220353','App\\Database\\Migrations\\CreatePaymentsTable','default','App',1780524262,11),(16,'2026-06-04-134229','App\\Database\\Migrations\\AddMissingTctFieldsToViolations','default','App',1780580647,12),(17,'2026-10-20-000001','App\\Database\\Migrations\\CreateTctSequencesTable','default','App',1792501732,13),(18,'2026-10-23-000001','App\\Database\\Migrations\\CreateViolatorsAndLinkViolations','default','App',1792730490,14),(19,'2026-10-24-000001','App\\Database\\Migrations\\AddAssignedAreaToUsers','default','App',1792817831,15);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `violation_id` int(11) unsigned NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_payments_violation` (`violation_id`),
  CONSTRAINT `fk_payment_violation` FOREIGN KEY (`violation_id`) REFERENCES `violations` (`id`),
  CONSTRAINT `fk_payments_violation` FOREIGN KEY (`violation_id`) REFERENCES `violations` (`id`),
  CONSTRAINT `payments_violation_id_foreign` FOREIGN KEY (`violation_id`) REFERENCES `violations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tct_sequences`
--

DROP TABLE IF EXISTS `tct_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tct_sequences` (
  `year` smallint(5) unsigned NOT NULL,
  `last_sequence` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tct_sequences`
--

LOCK TABLES `tct_sequences` WRITE;
/*!40000 ALTER TABLE `tct_sequences` DISABLE KEYS */;
INSERT INTO `tct_sequences` VALUES (2026,57,'2026-10-23 15:58:22');
/*!40000 ALTER TABLE `tct_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `middle_initial` varchar(10) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `age` int(3) unsigned DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `assigned_station` varchar(255) DEFAULT NULL,
  `assigned_area` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'driver',
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (3,'kenneth','kenneth caleb','t','barredo',22,'2004-01-12','bocana',NULL,NULL,NULL,'kenneth@gmail.com','$2y$10$549ldTjtuK1KaBZcp9niF.UAQEFwDE4HMQt07lwT16ugnBZB57y1q','admin','active','2026-04-24 06:37:44','2026-06-03 18:07:07'),(7,'janrey','Janrey','L','Calamba',21,'2006-01-20','Mambugsay','','Ceres Terminal, Kabankalan City','officer_7_1790748637.jpg','janrey@gmail.com','$2y$10$hiCw4.oCzGrQQROcohdHiu1h8hP39mmo4DUBavwEJPHmawnEY.gRe','enforcer','active','2026-04-24 14:19:59','2026-10-24 05:29:26');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `violation_types`
--

DROP TABLE IF EXISTS `violation_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `violation_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `violation_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `fine_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `points` int(3) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `violation_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `violation_name` (`violation_name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violation_types`
--

LOCK TABLES `violation_types` WRITE;
/*!40000 ALTER TABLE `violation_types` DISABLE KEYS */;
INSERT INTO `violation_types` VALUES (1,'Unlicensed Driver','Operating a motor vehicle without a valid driver\'s license',1000.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(2,'Unregistered MV','Operating an unregistered motor vehicle',500.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(3,'Colorum/Unfranchised Operation','Operating a public utility vehicle without a valid franchise or authority',5000.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(4,'Invalid or Suspended/Revoked/Expired CR','Operating with an invalid, suspended, revoked, or expired Certificate of Registration',1000.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(5,'Out of Route','Operating a public utility vehicle outside its authorized route',1000.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(6,'Discourteous Driver/Conduct','Rude, disrespectful, or improper behavior towards passengers or other motorists',500.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(7,'CR/OR Not Carried','Failure to carry Certificate of Registration and Official Receipt while operating',1000.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(8,'Unauthorized Improvised Plates','Using unauthorized, improvised, or tampered license plates',1000.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(9,'No Required MV Parts/Acc.','Operating without required motor vehicle parts or accessories',500.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(10,'No Body (Plate) Number, For Hire MV','For-hire motor vehicle operating without a visible body or plate number',500.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(11,'Allowing Passenger on Top of MV','Permitting passengers to ride on the roof or top of a motor vehicle',500.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(12,'Reckless Driving','Driving in a manner that endangers the safety of persons or property',500.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(13,'Obstruction','Causing obstruction on public roads or highways',500.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56',''),(14,'Other Violations','Any other traffic violations not specifically listed above',500.00,0,'active','2026-06-04 13:27:56','2026-06-04 13:27:56','');
/*!40000 ALTER TABLE `violation_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `violations`
--

DROP TABLE IF EXISTS `violations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `violations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `violator_id` int(11) unsigned DEFAULT NULL,
  `ticket_id` varchar(50) DEFAULT NULL,
  `driver_name` varchar(255) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `license_plate` varchar(20) NOT NULL,
  `mtop_number` varchar(50) DEFAULT NULL,
  `owner_name` varchar(150) DEFAULT NULL,
  `violation_type_id` int(11) unsigned DEFAULT NULL,
  `officer_id` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `penalty_amount` decimal(10,2) NOT NULL,
  `points` int(11) DEFAULT 0,
  `status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending',
  `violation_date` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paid_date` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `acknowledgment` varchar(50) DEFAULT NULL,
  `violation_type` varchar(50) NOT NULL,
  `driver_signature` longtext DEFAULT NULL,
  `officer_signature` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_officer` (`officer_id`),
  KEY `fk_created_by` (`created_by`),
  KEY `fk_violation_type_new` (`violation_type_id`),
  KEY `violations_violator_id` (`violator_id`),
  CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_officer` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_violation_type` FOREIGN KEY (`violation_type_id`) REFERENCES `violation_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violations`
--

LOCK TABLES `violations` WRITE;
/*!40000 ALTER TABLE `violations` DISABLE KEYS */;
INSERT INTO `violations` VALUES (1,4,'TKT-7481B9D5','kenneth barredo',NULL,'kenneth',NULL,'barredo',22,'bocana','MYOP09',NULL,NULL,1,7,7,1000.00,0,'Paid','2026-01-17 08:54:00','2026-06-04 08:39:17','2026-10-23 12:41:30','2026-06-04 08:45:12','Cash','7891','','Ceres Terminal, Kabankalan City','',NULL,'Unlicensed Driver',NULL,NULL),(2,4,'TKT-7481B9D5','kenneth barredo',NULL,'kenneth',NULL,'barredo',22,'bocana','MYOP09',NULL,NULL,8,7,7,1000.00,0,'Paid','2026-01-17 08:54:00','2026-06-04 08:39:17','2026-10-23 12:41:30','2026-06-04 08:45:12','Cash','7891','','Ceres Terminal, Kabankalan City','',NULL,'CR/OR Not Carried',NULL,NULL),(3,4,'TKT-7481B9D5','kenneth barredo',NULL,'kenneth',NULL,'barredo',22,'bocana','MYOP09',NULL,NULL,21,7,7,500.00,0,'Paid','2026-01-17 08:54:00','2026-06-04 08:39:17','2026-10-23 12:41:30','2026-06-04 08:45:12','Cash','7891','','Ceres Terminal, Kabankalan City','',NULL,'Beating the Red Light',NULL,NULL),(4,1,'TKT-A1CBA781','jeth salvio',NULL,'jeth',NULL,'salvio',34,'Ilog1','7890',NULL,NULL,1,3,3,1000.00,0,'Paid','2021-12-31 08:19:00','2026-06-04 08:47:12','2026-10-23 12:41:30','2026-06-04 08:47:44','Cash','4566','','Biyarin, Kabankalan City','',NULL,'Unlicensed Driver',NULL,NULL),(5,1,'TKT-A1CBA781','jeth salvio',NULL,'jeth',NULL,'salvio',34,'Ilog1','7890',NULL,NULL,4,3,3,500.00,0,'Paid','2021-12-31 08:19:00','2026-06-04 08:47:12','2026-10-23 12:41:30','2026-06-04 08:47:44','Cash','4566','','Biyarin, Kabankalan City','',NULL,'Unregistered MV',NULL,NULL),(6,1,'TKT-A1CBA781','jeth salvio',NULL,'jeth',NULL,'salvio',34,'Ilog1','7890',NULL,NULL,12,3,3,500.00,0,'Paid','2021-12-31 08:19:00','2026-06-04 08:47:12','2026-10-23 12:41:30','2026-06-04 08:47:44','Cash','4566','','Biyarin, Kabankalan City','',NULL,'Reckless Driving',NULL,NULL),(7,3,'TKT-FE9AC3F8','Janrey Calamba',NULL,'Janrey',NULL,'Calamba',22,'Mambugsay','89809',NULL,NULL,1,7,7,1000.00,0,'Paid','2024-12-01 14:45:00','2026-06-04 12:35:24','2026-10-23 12:41:30','2026-06-04 12:52:41','Cash','3567','','Binicuil, Kabankalan City','',NULL,'Unlicensed Driver',NULL,NULL),(8,3,'TKT-FE9AC3F8','Janrey Calamba',NULL,'Janrey',NULL,'Calamba',22,'Mambugsay','89809',NULL,NULL,2,7,7,1000.00,0,'Paid','2024-12-01 14:45:00','2026-06-04 12:35:24','2026-10-23 12:41:30','2026-06-04 12:52:41','Cash','3567','','Binicuil, Kabankalan City','',NULL,'Out of Route',NULL,NULL),(9,3,'TKT-FE9AC3F8','Janrey Calamba',NULL,'Janrey',NULL,'Calamba',22,'Mambugsay','89809',NULL,NULL,6,7,7,1000.00,0,'Paid','2024-12-01 14:45:00','2026-06-04 12:35:24','2026-10-23 12:41:30','2026-06-04 12:52:41','Cash','3567','','Binicuil, Kabankalan City','',NULL,'Invalid/Suspended/Revoked/Expired CR',NULL,NULL),(10,9,'TKT-F4A22BDD','Mark Villajera',NULL,'Mark',NULL,'Villajera',27,'Binicuil','FM542E',NULL,NULL,1,7,7,1000.00,0,'Paid','2026-06-05 21:35:00','2026-06-04 12:57:19','2026-10-23 12:41:30','2026-06-04 13:00:46','Cash','8902','','Binicuil, Kabankalan City','',NULL,'Unlicensed Driver',NULL,NULL),(11,9,'TKT-F4A22BDD','Mark Villajera',NULL,'Mark',NULL,'Villajera',27,'Binicuil','FM542E',NULL,NULL,5,7,7,5000.00,0,'Paid','2026-06-05 21:35:00','2026-06-04 12:57:19','2026-10-23 12:41:30','2026-06-04 13:00:46','Cash','8902','','Binicuil, Kabankalan City','',NULL,'Colorum/Unfranchised Operation',NULL,NULL),(12,9,'TKT-F4A22BDD','Mark Villajera',NULL,'Mark',NULL,'Villajera',27,'Binicuil','FM542E',NULL,NULL,10,7,7,500.00,0,'Paid','2026-06-05 21:35:00','2026-06-04 12:57:19','2026-10-23 12:41:30','2026-06-04 13:00:46','Cash','8902','','Binicuil, Kabankalan City','',NULL,'No Body (Plates) Number For Hire/MV',NULL,NULL),(13,6,'TKT-168C8707','John Mark Eriman',NULL,'John Mark',NULL,'Eriman',22,'bocana','FMR45',NULL,NULL,11,7,7,500.00,0,'Paid','2026-04-12 08:50:00','2026-06-04 12:58:57','2026-10-23 12:41:30','2026-06-04 13:06:07','Cash','8902','','Barangay 8 (Poblacion), Kabankalan City','',NULL,'Allowing Passenger on Top',NULL,NULL),(14,6,'TKT-168C8707','John Mark Eriman',NULL,'John Mark',NULL,'Eriman',22,'bocana','FMR45',NULL,NULL,18,7,7,500.00,0,'Paid','2026-04-12 08:50:00','2026-06-04 12:58:57','2026-10-23 12:41:30','2026-06-04 13:06:07','Cash','8902','','Barangay 8 (Poblacion), Kabankalan City','',NULL,'Counterflow',NULL,NULL),(17,12,'TKT-FC8E384A','Noel S Sanchez','','Noel','S','Sanchez',28,'Paho','NO','3030','',7,7,7,1000.00,0,'Paid','2026-09-20 13:45:00','2026-06-04 13:37:49','2026-10-23 22:09:07','2026-06-04 22:23:50','Cash','3567','','Tapi, Kabankalan City','','','CR/OR Not Carried',NULL,NULL),(18,12,'TKT-FC8E384A','Noel Sanchez',NULL,'Noel',NULL,'Sanchez',28,'Paho','NO',NULL,NULL,9,7,7,500.00,0,'Paid','2026-09-20 13:45:00','2026-06-04 13:37:49','2026-10-23 22:09:07','2026-06-04 22:23:50','Cash','3567','','Tapi, Kabankalan City','',NULL,'No Required MV Parts/Acc.',NULL,NULL),(19,12,'TKT-FC8E384A','Noel Sanchez',NULL,'Noel',NULL,'Sanchez',28,'Paho','NO',NULL,NULL,11,7,7,500.00,0,'Paid','2026-09-20 13:45:00','2026-06-04 13:37:49','2026-10-23 22:09:07','2026-06-04 22:23:50','Cash','3567','','Tapi, Kabankalan City','',NULL,'Allowing Passenger on Top of MV',NULL,NULL),(20,12,'TKT-FC8E384A','Noel Sanchez',NULL,'Noel',NULL,'Sanchez',28,'Paho','NO',NULL,NULL,13,7,7,500.00,0,'Paid','2026-09-20 13:45:00','2026-06-04 13:37:49','2026-10-23 22:09:07','2026-06-04 22:23:50','Cash','3567','','Tapi, Kabankalan City','',NULL,'Obstruction',NULL,NULL),(21,2,'TKT-18FFFAA8','Brayan G Dignus','7890','Brayan','G','Dignus',24,'Tabu','FMK60F','NO','Yes',1,7,7,1000.00,0,'Pending','2024-08-14 08:45:00','2026-06-04 22:15:27','2026-10-23 12:41:30',NULL,NULL,NULL,NULL,'KCC','','Admitted','Unlicensed Driver',NULL,NULL),(22,2,'TKT-18FFFAA8','Brayan G Dignus','7890','Brayan','G','Dignus',24,'Tabu','FMK60F','NO','Yes',5,7,7,1000.00,0,'Pending','2024-08-14 08:45:00','2026-06-04 22:15:27','2026-10-23 12:41:30',NULL,NULL,NULL,NULL,'KCC','','Admitted','Out of Route',NULL,NULL),(23,5,'TKT-F0916915','Jocel S Sanchez','','Jocel','S','Sanchez',23,'Pahu','7890','','No',2,7,7,500.00,0,'Pending','2026-04-05 09:10:00','2026-06-04 22:18:37','2026-10-23 12:41:30',NULL,NULL,NULL,NULL,'Biyarin, Kabankalan City','Violator','Admitted','Unregistered MV',NULL,NULL),(24,5,'TKT-F0916915','Jocel S Sanchez','','Jocel','S','Sanchez',23,'Pahu','7890','','No',5,7,7,1000.00,0,'Pending','2026-04-05 09:10:00','2026-06-04 22:18:37','2026-10-23 12:41:30',NULL,NULL,NULL,NULL,'Biyarin, Kabankalan City','Violator','Admitted','Out of Route',NULL,NULL),(25,5,'TKT-F0916915','Jocel S Sanchez','','Jocel','S','Sanchez',23,'Pahu','7890','','No',8,7,7,1000.00,0,'Pending','2026-04-05 09:10:00','2026-06-04 22:18:37','2026-10-23 12:41:30',NULL,NULL,NULL,NULL,'Biyarin, Kabankalan City','Violator','Admitted','Unauthorized Improvised Plates',NULL,NULL),(26,7,'TKT-11C36807','Lemuel S Sanchez','J8DA0','Lemuel','S','Sanchez',45,'Pahu','12434','980k','yes',1,7,7,1000.00,0,'Cancelled','2026-06-05 10:45:00','2026-06-04 22:25:42','2026-10-23 12:41:30',NULL,NULL,NULL,'no intereat','Barangay 2 (Poblacion), Kabankalan City','','Admitted','Unlicensed Driver',NULL,NULL),(27,7,'TKT-11C36807','Lemuel S Sanchez','J8DA0','Lemuel','S','Sanchez',45,'Pahu','12434','980k','yes',7,7,7,1000.00,0,'Cancelled','2026-06-05 10:45:00','2026-06-04 22:25:42','2026-10-23 12:41:30',NULL,NULL,NULL,'no intereat','Barangay 2 (Poblacion), Kabankalan City','','Admitted','CR/OR Not Carried',NULL,NULL),(28,8,'TKT-863FE3E1','Louie T Yude','f568','Louie','T','Yude',34,'Bocana','FMK78','','yes',3,7,7,5000.00,0,'Pending','2026-06-05 10:45:00','2026-06-05 03:30:15','2026-10-23 12:41:30',NULL,NULL,NULL,NULL,'Ceres Terminal, Kabankalan City','','Admitted','Colorum/Unfranchised Operation',NULL,NULL),(29,8,'TKT-863FE3E1','Louie T Yude','f568','Louie','T','Yude',34,'Bocana','FMK78','','yes',4,7,7,1000.00,0,'Pending','2026-06-05 10:45:00','2026-06-05 03:30:15','2026-10-23 12:41:30',NULL,NULL,NULL,NULL,'Ceres Terminal, Kabankalan City','','Admitted','Invalid or Suspended/Revoked/Expired CR',NULL,NULL),(35,11,'TKT-E3CE13F5','Arnold  Ripique','F02-02-000456','Arnold','','Ripique',25,'Kabankalan CIty','890','4859','',2,7,7,500.00,0,'Pending','2026-10-19 07:36:00','2026-10-19 07:39:18','2026-10-23 12:41:30',NULL,NULL,NULL,NULL,'Ceres Terminal, Kabankalan City','','Admitted','Unregistered MV','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAARgAAAA3CAYAAADXA1XbAAAJ6ElEQVR4AeydV8gkRRDH15xzzgoGjBgxY8AA6puCYnrwRb0H4UDuQTEHUBQVFVRQMSCK4JOCKGYxYM4Rc06nnp45/H/L9lFff5tmdlLP1kfVVHWq7q6erq/T9C7e8T/XgGvANVCSBtzAlKRYF+sacA10Om5g/C1wDbgGStOAG5jSVOuC69aA51+/BtzA1N8GXgLXQGs14AamtU07tGL/KvQ/4QpCB9dAaRpwA1OaavsKni/fJnTsxVQO4Bcejq6BsjTgBqYkzQ4Qu2rPv0kd+59emWLyvTwwhn+KOrgGcmnADUwutRWS6PlCpOQT8q1JNugdWL0XZylRDM1Xog6ugUwaGPRyZRLikcfWwLMm5i6GL5oNayzWkNg81rYO8X8LY8CoWL91rCMnf5nSfSBcX+gwBRpwA1NtI+9RUXZhjWXNIfkxBQrBSwTGUN6NhcYNGxsd/LLgXEXeTPic0CFVDWQoNy9RhugetQAN/GZkFDEqMOIyscOMTxDELtNbwdGjv/doVnKOEoT37RPx1woxNF+K/ir8SxhGXhiyV+V2SFwDocETr0ZSxX/ClPYxwzeV3UYFo8OLdGGZ7nPwY0UF/SAkjcVz5ReAkdwcOXYVritcXrikMIy8xHa25+GYtgbcwFTffoeaLDc3fNXsbSZDDIFxzmLj9ySOv5VSfCfEf4HoasJJwXevJtVgA9LHL04DijQVRQhbw3Xq/+iMmn4zio8BCOd63lbYGsIswHSIxWWmR58pIaM5plGMYlaRu/9ISQEO6Wigzhc8HS0VX9L7eyLpTKf0+KoJU5KQZ7yYG/wt3VYORigiXWD7Opzr6XqYB0aDelE/izY97x5lYJ3nSqXdX3i+EPiZh2P6GqCR069FejW4wBSZtQjjrIW9YkiuJymMg4EYB4yFnH2BOMcphDisw1wvPgbCYj/cbF9DHVumATcw9TQo52H+6GXN+kWPrZTYzn5WlPM8uRnVYFRuFM8oQ6QvsPuDrJUUeocwLzyQN6Gna64G3MBkaZti477TE7e06KbCsuCIMQVfqHhsoWNULhG/nLAf/CRP4oh0galSlxnjgSEKyDoLxikkOzgwTtujATcw9bXlrSZrzoQY58SsNQCXjiGN+Gcq3rLCfsC28w4KwDiw7hK/N6RXcCZgnQXjmimRR05LA/GLklbp0y7t5So+OykinX14lITx6OgW5cMOkMhQ4KTvxoqBUWGH6DXxFp6yDvFsU4tkBmuc2FXKLMATNFcDbmDqbZuve9mv3KNFEdtRGZXcLcFMR+jMJ4ofNK3h2yVGKBgVTvp+qriDYG8FBAMptoMRgmbFV0wCPlmwMk2Qs+VqoBzpbmDK0eu4Uh80ES82/KRsMFzIwVgcJYYtYZFZgNHhzAnx+AiSNZZZkQZ4YBBsELKsexx+J0Wy6SiHdSvYIVUNuIGpt+VON9kfb/i87MNKyOhlQ9FBQOcFQzhTm3GmTCF+TONrJ5AXxxnl5j20ZSI+bvtZBX6OiWmAhk2syK0qLlMSzo9QqQ145MCnlYaTwXTIA8THowp5dQj7WAyjA9ocKmcX9uo+8z92U1I7rck7VaJc70qWhTLXpmw+zpekARq1JNEudkwNvNyLR1sc0uNHkRcVIRgVPhwkrbwGAuHxYm+I/H5gJqCxUcOgZRC3KCpngqzxI4B6fiPmIyFTPRGHVDTAi5dKWdtazmtMxc4zfMy+IQ86G52XdYt+bUfYe4oXd1J5lQ4vRTnkmSoFEXaHinqupYBNhBz6E3FIRQM0XiplbWs571LFWDcR6WA4oAGZMgSjwrUJ/doLo8KHiBgVwrcMiSumOyu/IqZKEtNhh4p6wVssciHcynW+JA3wQpYk2sVm0AAXMBGd3ZwPxdBR6WBbiO/XRoSzvRuMCh8iKuoMIH3wsKMJe4GUjRPiTkLjqdIksqg39bPICeNJZHraijVAI1acZdOya0R5OHMSCsJaCZ0quAPFqLD2QhgdeccQMIC+YPzDBd54lT3CsTtSG5Gh4/RqwA1MfW3/ubLGaDCKGHTQjvBnFC8YlSwXhbO7o6RdIH2X0cPy5C2vQsEatocKlezCktOAG5jqmoxj918oO4wGHZub9W1nV1AXCHtcHGGMVPYUXyQgN8hjjSfwRVG77c0Ujzt89ytKuMtJSwNuYMptr90lnt8TwqhwDmU9uW0Hl7MLLPLy354w2qSoDomx6magBwvBIjNg6xmu4hzhKgoksq7E90/wjtVqoPbceJlrL0TLCnCY6sO5DYwK0xt+OQDDIe8ZwLdB98mHML4NOkh80cBCcJDJGZPAl035/ulsZfKjEHidh+P0acANTDFtzv22nMrFqGA0OLeB4YilY1TukSdhXFUw7l0tSpIL7LY3bW0XYO3oJpfwEYm4tY/Lv6lr2fUcURQPrksDvHR15Z16voerAqxh0FHvFM9OEJ1J7AygU98uH8IwKkeKrwsYKYW8wwni4HbqGihcA25gsqn0DEVnLYM1hnvFs4gpMgtY2LxBvhgV1iBOEF8XYAD75c3BuH7+Rfm5HNdAxw3M8JeA8yMsUPLrg5yovUjRWRhlJCJ2BmBUuB0fo8J1kyfPCK3P0W/9Y5DRqa+UnnMrNeAGZnazMvV5RN5c6citblzQxK8Pxrripzn4ZuYYxQ1GZa74pgFXXcZlYos89nO3a6BwDcSdpvAMEhEYT334jR5uybfFZwGXi5y4OZ+rFfhpDr6Z4VsiG69unrt+R3157Ncg1N1KU5J/Ww3MqOYbd+rDAi3f7rDlyqE3RjL89g8H5kblUVc4O1qjvjz2r5Lrap0py3eaDMy4U58FegceFbK1ygItXzGz5SqvJCC06fJDSjssbEgyD3INZNNAeBmzpUon9jhTHxY8WcS9WdViWsR3QdwMx3kWeSUHoU1ZF6LwTO2gjq6ByjUQXsbKMy4pw3GnPhzN5wIn7sRFByx6hp9ILalolYmlPmTGlA4aDA28Yws0kFIVwsuYUpn7lXWOPEft+ixUHG7x31eUA2fbifLbRCKtAkZkoUIc8Au8pW1pd1sn5xuogba8aFdJt0xvRBYBHY3j+9fJh//iK4hy5+2Tom0Ge+7lWFNR9BGc6CPwTl0DpWmgLQaGI/soicNw8KfJQd34nZ9TxU8T2HMv1pAcKCWgH5EOO2NQR9dAqRqgE5aaQUXCuTKSzsSPi/HV8NUV5dvUbOxoJZSRnTH0g57QV/DPRD2yayCLBtpiYLLUeRri2mnSNNTX69hQDbiBaWjDTFgsO01ClG9VowXHyjXgBqZyldeS4fxacvVMp14DSRmYqW+t/AqYlz+pp3QN5NeAG5j8uksp5U0pFdbL2h4NuIFpT1sOqkm/HaVBcd3fNVCoBtzAFKrORgljOxr0Nm5UswwoTEu9/wcAAP//GIGNUAAAAAZJREFUAwBoOEV+WDvn0QAAAABJRU5ErkJggg==',NULL),(36,12,'2026-0000036','Noel L. Sanchez','00000','Noel','L.','Sanchez',25,'Bocana','MYOP0','MC47','YES',5,7,7,1000.00,0,'Pending','2026-10-22 19:00:00','2026-10-22 11:03:11','2026-10-23 13:06:47',NULL,NULL,NULL,NULL,'Biyarin, Kabankalan City','','Admitted','Out of Route',NULL,NULL),(37,12,'2026-0000036','Noel L. Sanchez','00000','Noel','L.','Sanchez',25,'Bocana','MYOP0','MC47','YES',6,7,7,500.00,0,'Pending','2026-10-22 19:00:00','2026-10-22 11:03:11','2026-10-23 13:06:47',NULL,NULL,NULL,NULL,'Biyarin, Kabankalan City','','Admitted','Discourteous Driver/Conduct',NULL,NULL),(38,12,'2026-0000036','Noel L. Sanchez','00000','Noel','L.','Sanchez',25,'Bocana','MYOP0','MC47','YES',10,7,7,500.00,0,'Pending','2026-10-22 19:00:00','2026-10-22 11:03:11','2026-10-23 13:06:47',NULL,NULL,NULL,NULL,'Biyarin, Kabankalan City','','Admitted','No Body (Plate) Number, For Hire MV',NULL,NULL),(55,12,'2026-0000054','Noel Salvio Sanchez','00000','Noel','Salvio','Sanchez',25,'Bocana','MYOP0','mu989','YES',4,7,7,1000.00,0,'Pending','2026-10-23 14:13:00','2026-10-23 14:13:59','2026-10-23 15:23:33',NULL,NULL,NULL,NULL,'Biyarin, Kabankalan City','','Admitted','Invalid or Suspended/Revoked/Expired CR','data:image/png;base64,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','data:image/png;base64,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'),(56,12,'2026-0000054','Noel Salvio Sanchez','00000','Noel','Salvio','Sanchez',25,'Bocana','MYOP0','mu989','YES',6,7,7,500.00,0,'Pending','2026-10-23 14:13:00','2026-10-23 14:13:59','2026-10-23 15:23:33',NULL,NULL,NULL,NULL,'Biyarin, Kabankalan City','','Admitted','Discourteous Driver/Conduct','data:image/png;base64,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','data:image/png;base64,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'),(57,17,'2026-0000057','kenneth caleb t. barredo','8900','kenneth','caleb t.','barredo',25,'london','789','MC47','YES',4,7,7,1000.00,0,'Pending','2026-10-23 23:55:00','2026-10-23 15:58:22','2026-10-23 15:58:22',NULL,NULL,NULL,NULL,'Linao, Kabankalan City','','Admitted','Invalid or Suspended/Revoked/Expired CR',NULL,NULL),(58,17,'2026-0000057','kenneth caleb t. barredo','8900','kenneth','caleb t.','barredo',25,'london','789','MC47','YES',6,7,7,500.00,0,'Pending','2026-10-23 23:55:00','2026-10-23 15:58:22','2026-10-23 15:58:22',NULL,NULL,NULL,NULL,'Linao, Kabankalan City','','Admitted','Discourteous Driver/Conduct',NULL,NULL),(59,17,'2026-0000057','kenneth caleb t. barredo','8900','kenneth','caleb t.','barredo',25,'london','789','MC47','YES',9,7,7,500.00,0,'Pending','2026-10-23 23:55:00','2026-10-23 15:58:22','2026-10-23 15:58:22',NULL,NULL,NULL,NULL,'Linao, Kabankalan City','','Admitted','No Required MV Parts/Acc.',NULL,NULL);
/*!40000 ALTER TABLE `violations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `violators`
--

DROP TABLE IF EXISTS `violators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `violators` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identity_key` varchar(191) NOT NULL,
  `license_key` varchar(100) DEFAULT NULL,
  `name_key` varchar(191) DEFAULT NULL,
  `plate_key` varchar(50) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `driver_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `license_plate` varchar(20) DEFAULT NULL,
  `owner_name` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identity_key` (`identity_key`),
  KEY `license_key` (`license_key`),
  KEY `name_key_plate_key` (`name_key`,`plate_key`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violators`
--

LOCK TABLES `violators` WRITE;
/*!40000 ALTER TABLE `violators` DISABLE KEYS */;
INSERT INTO `violators` VALUES (1,'np:jeth salvio|7890',NULL,'jeth salvio','7890','','jeth salvio','jeth','','salvio',34,'Ilog1','7890','','2026-10-23 04:41:30','2026-10-23 04:41:30'),(2,'dl:7890','7890','brayan dignus','FMK60F','7890','Brayan G Dignus','Brayan','G','Dignus',24,'Tabu','FMK60F','Yes','2026-10-23 04:41:30','2026-10-23 04:41:30'),(3,'np:janrey calamba|89809',NULL,'janrey calamba','89809','','Janrey Calamba','Janrey','','Calamba',22,'Mambugsay','89809','','2026-10-23 04:41:30','2026-10-23 04:41:30'),(4,'np:kenneth barredo|MYOP09',NULL,'kenneth barredo','MYOP09','','kenneth barredo','kenneth','','barredo',22,'bocana','MYOP09','','2026-10-23 04:41:30','2026-10-23 04:41:30'),(5,'np:jocel sanchez|7890',NULL,'jocel sanchez','7890','','Jocel S Sanchez','Jocel','S','Sanchez',23,'Pahu','7890','No','2026-10-23 04:41:30','2026-10-23 04:41:30'),(6,'np:john mark eriman|FMR45',NULL,'john mark eriman','FMR45','','John Mark Eriman','John Mark','','Eriman',22,'bocana','FMR45','','2026-10-23 04:41:30','2026-10-23 04:41:30'),(7,'dl:J8DA0','J8DA0','lemuel sanchez','12434','J8DA0','Lemuel S Sanchez','Lemuel','S','Sanchez',45,'Pahu','12434','yes','2026-10-23 04:41:30','2026-10-23 04:41:30'),(8,'dl:F568','F568','louie yude','FMK78','f568','Louie T Yude','Louie','T','Yude',34,'Bocana','FMK78','yes','2026-10-23 04:41:30','2026-10-23 04:41:30'),(9,'np:mark villajera|FM542E',NULL,'mark villajera','FM542E','','Mark Villajera','Mark','','Villajera',27,'Binicuil','FM542E','','2026-10-23 04:41:30','2026-10-23 04:41:30'),(11,'dl:F0202000456','F0202000456','arnold ripique','890','F02-02-000456','Arnold Ripique','Arnold','','Ripique',25,'Kabankalan CIty','890','','2026-10-23 04:41:30','2026-10-23 04:41:30'),(12,'dl:00000','00000','noel sanchez','MYOP0','00000','Noel Salvio Sanchez','Noel','Salvio','Sanchez',25,'Bocana','MYOP0','YES','2026-10-23 04:41:30','2026-10-23 15:23:33'),(17,'dl:8900','8900','kenneth barredo','789','8900','kenneth caleb t. barredo','kenneth','caleb t.','barredo',25,'london','789','YES','2026-10-23 15:58:22','2026-10-23 15:58:22');
/*!40000 ALTER TABLE `violators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'database'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-24 16:37:46
