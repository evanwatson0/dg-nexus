/*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;CREATE DATABASE  IF NOT EXISTS `druggene_db` 
USE `druggene_db`;
-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: druggene_db
-- ------------------------------------------------------
-- Server version	8.0.44

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
-- Table structure for table `citation`
--

DROP TABLE IF EXISTS `citation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `citation` (
  `CitationID` int NOT NULL AUTO_INCREMENT,
  `PMID` varchar(200) DEFAULT NULL,
  `Title` varchar(500) DEFAULT NULL,
  `Source` varchar(500) DEFAULT NULL,
  `Year` int DEFAULT NULL,
  PRIMARY KEY (`CitationID`),
  KEY `idx_citation_pmid` (`PMID`),
  KEY `idx_citation_year` (`Year`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



--
-- Table structure for table `drug`
--

DROP TABLE IF EXISTS `drug`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `drug` (
  `DrugID` int NOT NULL AUTO_INCREMENT,
  `DrugName` varchar(255) NOT NULL,
  `DrugBankID` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`DrugID`),
  KEY `idx_drug_name` (`DrugName`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



--
-- Table structure for table `gene`
--

DROP TABLE IF EXISTS `gene`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gene` (
  `GeneID` int NOT NULL AUTO_INCREMENT,
  `GeneSymbol` varchar(200) NOT NULL,
  `GeneLongName`  VARCHAR(200),
  `EntrezID` varchar(200) DEFAULT NULL,
  `HGNC`          VARCHAR(30),
  PRIMARY KEY (`GeneID`),
  KEY `idx_gene_symbol` (`GeneSymbol`)
) ENGINE=InnoDB AUTO_INCREMENT=24576 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



--
-- Table structure for table `interaction`
--

DROP TABLE IF EXISTS `interaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interaction` (
  `InteractionID` int NOT NULL AUTO_INCREMENT,
  `DrugID` int NOT NULL,
  `GeneID` int NOT NULL,
  `RelationType` varchar(500) DEFAULT NULL,
  `Notes` text,
  PRIMARY KEY (`InteractionID`),
  UNIQUE KEY `uk_drug_gene_type` (`DrugID`,`GeneID`,`RelationType`),
  KEY `idx_interaction_gene` (`GeneID`),
  KEY `idx_interaction_drug` (`DrugID`),
  KEY `idx_interaction_type` (`RelationType`),
  CONSTRAINT `interaction_ibfk_1` FOREIGN KEY (`DrugID`) REFERENCES `drug` (`DrugID`),
  CONSTRAINT `interaction_ibfk_2` FOREIGN KEY (`GeneID`) REFERENCES `gene` (`GeneID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `interaction_citation`
--

DROP TABLE IF EXISTS `interaction_citation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interaction_citation` (
  `InteractionID` int NOT NULL,
  `CitationID` int NOT NULL,
  PRIMARY KEY (`InteractionID`,`CitationID`),
  KEY `CitationID` (`CitationID`),
  CONSTRAINT `interaction_citation_ibfk_1` FOREIGN KEY (`InteractionID`) REFERENCES `interaction` (`InteractionID`),
  CONSTRAINT `interaction_citation_ibfk_2` FOREIGN KEY (`CitationID`) REFERENCES `citation` (`CitationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `web_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `web_user` (
    `UserIdentifier` int NOT NULL AUTO_INCREMENT,
    `AccName` varchar(100) NOT NULL,
    `Email` varchar(100) DEFAULT NULL,
    `Password` varchar(256) NOT NULL,
    PRIMARY KEY (`UserIdentifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_session`
--

DROP TABLE IF EXISTS `llm_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `llm_session` (
  `SessionID` int NOT NULL AUTO_INCREMENT,
  `UserIdentifier` int NOT NULL,
  `SessionName` varchar(50) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`SessionID`),
  CONSTRAINT `user_identification` FOREIGN KEY (`UserIdentifier`) REFERENCES `web_user` (`UserIdentifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
--
-- Table structure for table `llm_prompt`
--

DROP TABLE IF EXISTS `llm_prompt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `llm_prompt` (
  `PromptID` int NOT NULL AUTO_INCREMENT,
  `SessionID` int NOT NULL,
  `PromptText` text NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`PromptID`),
  KEY `idx_prompt_session` (`SessionID`,`CreatedAt`),
  CONSTRAINT `llm_prompt_ibfk_1` FOREIGN KEY (`SessionID`) REFERENCES `llm_session` (`SessionID`)
);

--
-- Table structure for table `llm_response`
--

DROP TABLE IF EXISTS `llm_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `llm_response` (
  `ResponseID` int NOT NULL AUTO_INCREMENT,
  `PromptID` int NOT NULL,
  `ResponseText` longtext NOT NULL,
  `Rating` tinyint DEFAULT NULL,
  `IsHelpful` tinyint(1) DEFAULT NULL,
  `FeedbackText` text,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ResponseID`),
  KEY `idx_response_prompt` (`PromptID`),
  CONSTRAINT `llm_response_ibfk_1` FOREIGN KEY (`PromptID`) REFERENCES `llm_prompt` (`PromptID`)
);


-- Dump completed on 2025-11-06 22:50:34
