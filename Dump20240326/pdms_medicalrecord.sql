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
-- Table structure for table `medicalrecord`
--

DROP TABLE IF EXISTS `medicalrecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicalrecord` (
  `medicalrecordid` varchar(6) NOT NULL,
  `doctorid` varchar(5) DEFAULT NULL,
  `patientid` varchar(5) DEFAULT NULL,
  `specialnotes` varchar(255) DEFAULT NULL,
  `presentingcomplaints` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `treatments` varchar(255) DEFAULT NULL,
  `allergies` varchar(255) DEFAULT NULL,
  `time` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`medicalrecordid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicalrecord`
--

LOCK TABLES `medicalrecord` WRITE;
/*!40000 ALTER TABLE `medicalrecord` DISABLE KEYS */;
INSERT INTO `medicalrecord` VALUES ('MR0001','D0001','P0001','Patient is allergic to penicillinn','Toothache,Bleeding gums,gum swelling','2024-03-02','Tooth extraction,scaling and cleaning','','08:45AM'),('MR0002','D0002','P0001','Latex allergy','Bleeding gums','2024-03-09','Scaling and cleaning','','09:00AM'),('MR0003','D0001','P0001','Aspirin, Penicillin','Gum swelling','2024-03-15','Periodontal therapy','','10:30AM'),('MR0004','D0001','P0002','Patient has history of dental surgeries, Allergic to penicillin','Toothache, Bleeding gums, Gum swelling','2024-03-23','Painkillers, Antibiotics, Mouthwash',NULL,'12:35AM');
/*!40000 ALTER TABLE `medicalrecord` ENABLE KEYS */;
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
