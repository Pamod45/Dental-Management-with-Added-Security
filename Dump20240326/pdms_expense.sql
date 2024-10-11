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
-- Table structure for table `expense`
--

DROP TABLE IF EXISTS `expense`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense` (
  `expenseid` varchar(6) NOT NULL,
  `branchid` varchar(5) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`expenseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense`
--

LOCK TABLES `expense` WRITE;
/*!40000 ALTER TABLE `expense` DISABLE KEYS */;
INSERT INTO `expense` VALUES ('EX0001','B0001',70000,'2024-01-01','Pharmacy Purchase'),('EX0002','B0001',30000,'2024-01-05','Electricity Bill'),('EX0003','B0001',50000,'2024-01-10','Water Bill'),('EX0004','B0001',25000,'2024-01-15','Employee Salary'),('EX0005','B0001',60000,'2024-01-20','Building Rental'),('EX0006','B0001',20000,'2024-01-25','Pharmacy Purchase'),('EX0007','B0001',40000,'2024-01-28','Electricity Bill'),('EX0008','B0001',35000,'2024-01-31','Water Bill'),('EX0009','B0001',70000,'2024-02-01','Pharmacy Purchase'),('EX0010','B0001',30000,'2024-02-05','Electricity Bill'),('EX0011','B0001',50000,'2024-02-10','Water Bill'),('EX0012','B0001',25000,'2024-02-15','Employee Salary'),('EX0013','B0001',60000,'2024-02-20','Building Rental'),('EX0014','B0001',20000,'2024-02-25','Pharmacy Purchase'),('EX0015','B0001',40000,'2024-02-28','Electricity Bill'),('EX0016','B0001',70000,'2024-03-01','Pharmacy Purchase'),('EX0017','B0001',30000,'2024-03-05','Electricity Bill'),('EX0018','B0001',50000,'2024-03-10','Water Bill'),('EX0019','B0001',25000,'2024-03-15','Employee Salary'),('EX0020','B0001',60000,'2024-03-20','Building Rental'),('EX0021','B0001',20000,'2024-03-25','Pharmacy Purchase'),('EX0022','B0001',40000,'2024-03-28','Electricity Bill'),('EX0023','B0001',50000,'2023-02-15',NULL),('EX0024','B0001',60000,'2022-11-15',NULL);
/*!40000 ALTER TABLE `expense` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-03-26 18:57:26
