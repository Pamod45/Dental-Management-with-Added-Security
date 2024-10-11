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
-- Table structure for table `income`
--

DROP TABLE IF EXISTS `income`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `income` (
  `incomeid` varchar(6) NOT NULL,
  `branchid` varchar(5) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`incomeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income`
--

LOCK TABLES `income` WRITE;
/*!40000 ALTER TABLE `income` DISABLE KEYS */;
INSERT INTO `income` VALUES ('I0001','B0001',70000,'2024-01-01','Appointment'),('I0002','B0001',30000,'2024-01-05','Pharmacy Invoice'),('I0003','B0001',50000,'2024-01-10','Appointment'),('I0004','B0001',25000,'2024-01-15','Pharmacy Invoice'),('I0005','B0001',60000,'2024-01-20','Appointment'),('I0006','B0001',20000,'2024-01-25','Pharmacy Invoice'),('I0007','B0001',40000,'2024-01-28','Appointment'),('I0008','B0001',35000,'2024-01-31','Pharmacy Invoice'),('I0009','B0001',75000,'2024-02-03','Appointment'),('I0010','B0001',32000,'2024-02-08','Pharmacy Invoice'),('I0011','B0001',55000,'2024-02-12','Appointment'),('I0012','B0001',27000,'2024-02-17','Pharmacy Invoice'),('I0013','B0001',62000,'2024-02-21','Appointment'),('I0014','B0001',23000,'2024-02-26','Pharmacy Invoice'),('I0015','B0001',48000,'2024-02-29','Appointment'),('I0016','B0001',74000,'2024-03-04','Pharmacy Invoice'),('I0017','B0001',33000,'2024-03-07','Appointment'),('I0018','B0001',52000,'2024-03-13','Pharmacy Invoice'),('I0019','B0001',28000,'2024-03-18','Appointment'),('I0020','B0001',63000,'2024-03-22','Pharmacy Invoice'),('I0021','B0001',22000,'2024-03-24','Appointment'),('I0022','B0001',44000,'2024-03-27','Pharmacy Invoice'),('I0023','B0001',50000,'2020-03-15','Appointment'),('I0024','B0001',65000,'2021-01-03','Appointment'),('I0025','B0001',75000,'2022-02-15','Appointment'),('I0026','B0001',38000,'2023-12-11','Appointment');
/*!40000 ALTER TABLE `income` ENABLE KEYS */;
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
