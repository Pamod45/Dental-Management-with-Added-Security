CREATE DATABASE  IF NOT EXISTS `pdms` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `pdms`;
-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: localhost    Database: pdms
-- ------------------------------------------------------
-- Server version	8.0.39

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
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `userid` varchar(6) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` varchar(8) NOT NULL,
  `registereddate` date DEFAULT NULL,
  `loginstatus` int DEFAULT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('U0001','$2y$10$EdqJWYdWruKtumGyfWBjSeQ8gnsktbNjBkH5Rde4JI.UEFPFyj0kK','Patient','2024-10-09',0),('U0002','$2y$10$lyUijkk0Wjwn9tBMUwgeK.w79Hv9fra5u.tor7XnZJ1F8ncsBbM1e','Doctor','2024-10-09',0),('U0003','$2y$10$Rt70CnMwmRazJIX53kKSWOtyPCghSrZ1/3guFDyuqa3k.R9ALBDAy','Employee','2024-10-09',0),('U0004','$2y$10$P2lMEWNrnC3tK7yvx5tm/./ho1uuj4S3m1OueTHjmwMAZjGlpfiGO','Employee','2024-10-09',0),('U0005','$2y$10$rSHevjtOWlcbviUaymmDUuukZsUlbbBO53XAw7R816qRSu4lMyydy','Patient','2024-10-14',0),('U0006','$2y$10$VRkt9WlJ94qA9uVQEwbtZuN0NAg/zsWfZT/tpLY0CeH7x9/rv0uSa','Patient','2024-10-14',0),('U0007','$2y$10$YEj/9g2kooHB22/BF39Pxu3qpDyvq/R2EjZOKZRwDQeLfuLi2WCne','Patient','2024-10-14',0),('U0008','$2y$10$gGiYwdspHK3b.Z5XdTkYmeCRvb4AyDSGJAurfTwQHzdOF36OrkV7m','Patient','2024-10-15',0),('U0009','$2y$10$N0Eq.IinSzEw7QvXdsrq5.ojctF6FZqHyjOazsWf0jhJicChC4xvy','Patient','2024-10-18',0),('U0010','$2y$10$J1HqdQysKrOYuHebQU9mQ.Qix2/bpsPJRJdhKHDRqZcQwKsmadsKm','Patient','2024-10-18',0),('U0011','$2y$10$Kr2XB.t/kA4pW7NMZNCHSu9JD2wxtE.mdFe4XpR0lsQog17RKDuWa','Patient','2024-10-18',0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-18  1:16:37
