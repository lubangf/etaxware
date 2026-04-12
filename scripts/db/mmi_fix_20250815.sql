DROP TABLE IF EXISTS `tblplatformmode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblplatformmode` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblplatformmode`
--

LOCK TABLES `tblplatformmode` WRITE;
/*!40000 ALTER TABLE `tblplatformmode` DISABLE KEYS */;
INSERT INTO `tblplatformmode` VALUES (14,'INT','Integrated','Integrated',0,'2022-09-03 00:00:00',1000,'2022-09-03 00:00:00',1000),(15,'ERP','Abridged ERP','Abridged ERP',0,'2022-09-03 00:00:00',1000,'2022-09-03 00:00:00',1000);
/*!40000 ALTER TABLE `tblplatformmode` ENABLE KEYS */;
UNLOCK TABLES;



DROP TABLE IF EXISTS `tblefrismode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblefrismode` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblefrismode`
--

LOCK TABLES `tblefrismode` WRITE;
/*!40000 ALTER TABLE `tblefrismode` DISABLE KEYS */;
INSERT INTO `tblefrismode` VALUES (14,'ON','Online','Talking to EFRIS Online APIs',0,'2022-09-03 00:00:00',1000,'2022-09-03 00:00:00',1000),(15,'OFF','Offline Enabler','Talking to the EFRIS Offline Enabler',0,'2022-09-03 00:00:00',1000,'2022-09-03 00:00:00',1000);
/*!40000 ALTER TABLE `tblefrismode` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `tblerpdocumentypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblerpdocumentypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblerpdocumentypes`
--

LOCK TABLES `tblerpdocumentypes` WRITE;
/*!40000 ALTER TABLE `tblerpdocumentypes` DISABLE KEYS */;
INSERT INTO `tblerpdocumentypes` VALUES (14,'10','INV','Invoice','Invoice',0,'2022-09-25 14:22:57',1000,'2022-09-25 14:22:57',1000),(15,'11','INV','Sales Receipt','Sales Receipt',0,'2022-09-25 14:22:57',1000,'2022-09-25 14:22:57',1000),(16,'12','CN','Credit Memo','Credit Memo',0,'2022-09-25 14:22:57',1000,'2022-09-25 14:22:57',1000),(17,'13','CN','Refund Receipt','Refund Receipt',1,'2022-09-25 14:22:57',1000,'2022-09-25 14:22:57',1000),(18,'14','CN','Credit Note','Credit Note',1,'2022-09-25 14:22:57',1000,'2022-09-25 14:22:57',1000),(19,'15','DN','Debit Note','Debit Note',0,'2022-09-25 14:22:57',1000,'2022-09-25 14:22:57',1000);
/*!40000 ALTER TABLE `tblerpdocumentypes` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `tblenforcetaxexclusionlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblenforcetaxexclusionlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This field is used to control whether to UI displays the item as enabled or disabled',
  `inserteddt` datetime NOT NULL,
  `insertedby` int DEFAULT NULL,
  `modifieddt` datetime DEFAULT NULL,
  `modifiedby` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  CONSTRAINT `tblenforcetaxexclusionlist_chk_1` CHECK ((`disabled` in (0,1)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblenforcetaxexclusionlist`
--

LOCK TABLES `tblenforcetaxexclusionlist` WRITE;
/*!40000 ALTER TABLE `tblenforcetaxexclusionlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblenforcetaxexclusionlist` ENABLE KEYS */;
UNLOCK TABLES;