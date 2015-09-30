-- MySQL dump 10.13  Distrib 5.1.67, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: mfcs
-- ------------------------------------------------------
-- Server version	5.1.67

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `containers`
--

DROP TABLE IF EXISTS `containers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `containers` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `containerName` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `container` (`containerName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `containers`
--

LOCK TABLES `containers` WRITE;
/*!40000 ALTER TABLE `containers` DISABLE KEYS */;
/*!40000 ALTER TABLE `containers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dupeMatching`
--

DROP TABLE IF EXISTS `dupeMatching`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dupeMatching` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `formID` int(10) unsigned DEFAULT NULL,
  `projectID` int(10) unsigned DEFAULT NULL,
  `objectID` int(10) unsigned DEFAULT NULL,
  `field` varchar(50) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dupeMatching`
--

LOCK TABLES `dupeMatching` WRITE;
/*!40000 ALTER TABLE `dupeMatching` DISABLE KEYS */;
/*!40000 ALTER TABLE `dupeMatching` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forms`
--

DROP TABLE IF EXISTS `forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forms` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `fields` mediumtext,
  `idno` varchar(2048) DEFAULT NULL,
  `container` tinyint(4) NOT NULL DEFAULT '0',
  `production` tinyint(4) NOT NULL DEFAULT '0',
  `metadata` tinyint(4) NOT NULL DEFAULT '1',
  `submitButton` varchar(20) DEFAULT 'Submit',
  `updateButton` varchar(20) DEFAULT 'Update',
  `count` int(11) NOT NULL DEFAULT '0',
  `objectTitleField` varchar(50) DEFAULT 'title',
  `navigation` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forms`
--

LOCK TABLES `forms` WRITE;
/*!40000 ALTER TABLE `forms` DISABLE KEYS */;
/*!40000 ALTER TABLE `forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectMetadataLinks`
--

DROP TABLE IF EXISTS `objectMetadataLinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectMetadataLinks` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectID` int(10) unsigned DEFAULT NULL,
  `metadataID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectMetadataLinks`
--

LOCK TABLES `objectMetadataLinks` WRITE;
/*!40000 ALTER TABLE `objectMetadataLinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `objectMetadataLinks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectProjects`
--

DROP TABLE IF EXISTS `objectProjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectProjects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectID` int(10) unsigned DEFAULT NULL,
  `projectID` int(10) unsigned DEFAULT NULL,
  `projectNumber` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectProjects`
--

LOCK TABLES `objectProjects` WRITE;
/*!40000 ALTER TABLE `objectProjects` DISABLE KEYS */;
/*!40000 ALTER TABLE `objectProjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectTypes`
--

DROP TABLE IF EXISTS `objectTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectTypes` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectType` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `objectType` (`objectType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectTypes`
--

LOCK TABLES `objectTypes` WRITE;
/*!40000 ALTER TABLE `objectTypes` DISABLE KEYS */;
/*!40000 ALTER TABLE `objectTypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objects`
--

DROP TABLE IF EXISTS `objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentID` int(10) unsigned DEFAULT NULL,
  `formID` int(10) unsigned DEFAULT NULL,
  `defaultProject` int(10) unsigned DEFAULT NULL,
  `data` text,
  `metadata` tinyint(4) DEFAULT NULL,
  `idno` varchar(20) DEFAULT NULL,
  `modifiedTime` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objects`
--

LOCK TABLES `objects` WRITE;
/*!40000 ALTER TABLE `objects` DISABLE KEYS */;
/*!40000 ALTER TABLE `objects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `formID` int(10) unsigned DEFAULT NULL,
  `userID` int(10) unsigned DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projectName` varchar(50) DEFAULT NULL,
  `forms` varchar(1000) DEFAULT NULL,
  `groupings` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `numbering` varchar(20) DEFAULT '#',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `revisions`
--

DROP TABLE IF EXISTS `revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `revisions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productionTable` varchar(30) DEFAULT NULL,
  `primaryID` int(11) DEFAULT NULL,
  `secondaryID` int(10) unsigned DEFAULT NULL,
  `metadata` text,
  `digitalObjects` blob,
  `relatedData` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `revisions`
--

LOCK TABLES `revisions` WRITE;
/*!40000 ALTER TABLE `revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(25) DEFAULT NULL,
  `lastname` varchar(25) DEFAULT NULL,
  `username` varchar(25) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `pagination` int(11) NOT NULL DEFAULT '25',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Vagrant','Default','vagrant',NULL,25);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_projects`
--

DROP TABLE IF EXISTS `users_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_projects` (
  `UD` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `projectID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UD`),
  KEY `userID` (`userID`,`projectID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='many-many link for a user''s current projects';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_projects`
--

LOCK TABLES `users_projects` WRITE;
/*!40000 ALTER TABLE `users_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `watermarks`
--

DROP TABLE IF EXISTS `watermarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watermarks` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `watermarks`
--

LOCK TABLES `watermarks` WRITE;
/*!40000 ALTER TABLE `watermarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `watermarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'mfcs'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-04-23 14:43:13
