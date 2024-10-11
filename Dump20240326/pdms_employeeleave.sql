CREATE DATABASE  IF NOT EXISTS `pdms` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `pdms`;
-- MySQL dump 10.13  Distrib 8.0.32, for Win64 (x86_64)
--
-- Host: localhost    Database: pdms
-- ------------------------------------------------------
-- Server version	8.0.32

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `employeeleave`
--

DROP TABLE IF EXISTS `employeeleave`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employeeleave` (
  `empleaveid` varchar(6) NOT NULL,
  `employeeid` varchar(5) DEFAULT NULL,
  `leavetypeid` varchar(6) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `approverid` varchar(5) DEFAULT NULL,
  `requestdate` date DEFAULT NULL,
  PRIMARY KEY (`empleaveid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employeeleave`
--

LOCK TABLES `employeeleave` WRITE;
/*!40000 ALTER TABLE `employeeleave` DISABLE KEYS */;
INSERT INTO `employeeleave` VALUES ('EL0001','E0001','LT0001','Personal Emergency','2024-02-02','approved',NULL,'2024-01-15'),('EL0002','E0001','LT0002','Personal Emergency','2024-02-28','rejected',NULL,'2024-02-20'),('EL0003','E0001','LT0003','Personal Emergency','2024-03-09','approved',NULL,'2024-03-01'),('EL0004','E0001','LT0004','Personal Emergency','2024-03-28','pending',NULL,'2024-03-22'),('EL0005','E0001','LT0003','Personal Emergency','2024-04-22','pending',NULL,'2024-03-24'),('EL0006','E0001','LT0003','Personal Emergency','2024-03-30','pending',NULL,'2024-03-24'),('EL0007','E0001','LT0004','Personal Reason','2024-05-09','pending',NULL,'2024-03-24'),('EL0008','E0001','LT0003','Personal Emergency','2024-03-27','pending',NULL,'2024-03-24');
/*!40000 ALTER TABLE `employeeleave` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-03-26 18:57:25
