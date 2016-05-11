-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: mfcs
-- ------------------------------------------------------
-- Server version	5.1.73-log

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
-- Table structure for table `checks`
--

DROP TABLE IF EXISTS `checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checks` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `field` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `value` text CHARACTER SET utf8 COLLATE utf8_bin,
  PRIMARY KEY (`ID`),
  KEY `dupeMatching` (`formID`,`field`,`value`(100))
) ENGINE=InnoDB AUTO_INCREMENT=2488545 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exports`
--

DROP TABLE IF EXISTS `exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exports` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `formID` int(10) unsigned DEFAULT NULL,
  `projectID` int(10) unsigned DEFAULT NULL,
  `objectID` int(10) unsigned DEFAULT NULL,
  `date` int(11) unsigned DEFAULT NULL,
  `info1` varchar(200) DEFAULT NULL,
  `info2` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filesChecks`
--

DROP TABLE IF EXISTS `filesChecks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filesChecks` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(1000) NOT NULL,
  `checksum` varchar(33) DEFAULT NULL,
  `lastChecked` int(20) unsigned DEFAULT NULL,
  `pass` tinyint(1) unsigned DEFAULT '0',
  `objectID` bigint(20) unsigned NOT NULL,
  `userProvided` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `location` (`location`(255)),
  KEY `objectID` (`objectID`)
) ENGINE=InnoDB AUTO_INCREMENT=82114 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `displayTitle` varchar(50) DEFAULT NULL,
  `objectTitleField` varchar(50) DEFAULT 'title',
  `navigation` mediumtext,
  `linkTitle` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `title` (`title`),
  UNIQUE KEY `linkTitle` (`linkTitle`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forms_projects`
--

DROP TABLE IF EXISTS `forms_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forms_projects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `formID` int(10) unsigned NOT NULL,
  `projectID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `formID` (`formID`,`projectID`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1 COMMENT='many to many link for projects and forms';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locks`
--

DROP TABLE IF EXISTS `locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locks` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(25) NOT NULL,
  `typeID` bigint(20) unsigned NOT NULL,
  `user` int(20) unsigned NOT NULL,
  `date` int(20) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `type` (`type`),
  KEY `typeID` (`typeID`)
) ENGINE=InnoDB AUTO_INCREMENT=10183 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `objectID` int(10) unsigned DEFAULT '0',
  `formID` int(10) unsigned DEFAULT '0',
  `info` varchar(1000) DEFAULT NULL,
  `date` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=133016 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `metadataStandards`
--

DROP TABLE IF EXISTS `metadataStandards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metadataStandards` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `typeID` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `objectProcessing`
--

DROP TABLE IF EXISTS `objectProcessing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectProcessing` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectID` bigint(20) unsigned NOT NULL,
  `fieldName` varchar(100) NOT NULL,
  `state` tinyint(1) unsigned DEFAULT '1',
  `timestamp` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `objectID` (`objectID`)
) ENGINE=InnoDB AUTO_INCREMENT=4693 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9416 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `data` longtext CHARACTER SET utf8 COLLATE utf8_bin,
  `metadata` tinyint(4) DEFAULT NULL,
  `idno` varchar(100) DEFAULT NULL,
  `modifiedTime` int(11) unsigned DEFAULT NULL,
  `createTime` int(10) unsigned NOT NULL,
  `modifiedBy` varchar(100) DEFAULT NULL,
  `createdBy` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idno_2` (`idno`),
  KEY `formID` (`formID`),
  KEY `idno` (`idno`),
  KEY `modifiedTime` (`modifiedTime`)
) ENGINE=InnoDB AUTO_INCREMENT=149833 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `objectsData`
--

DROP TABLE IF EXISTS `objectsData`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectsData` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formID` int(10) unsigned NOT NULL,
  `objectID` int(10) unsigned NOT NULL,
  `fieldName` varchar(100) NOT NULL,
  `value` text,
  `encoded` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `objectID` (`objectID`),
  KEY `formID` (`formID`)
) ENGINE=InnoDB AUTO_INCREMENT=8586852 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `obsoleteFileTypes`
--

DROP TABLE IF EXISTS `obsoleteFileTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `obsoleteFileTypes` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=1839 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projectName` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `projectID` varchar(10) DEFAULT NULL,
  `forms` varchar(1000) DEFAULT NULL,
  `groupings` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `numbering` varchar(20) DEFAULT '#',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `projectID` (`projectID`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`ID`),
  KEY `primaryID` (`primaryID`),
  KEY `secondaryID` (`secondaryID`)
) ENGINE=InnoDB AUTO_INCREMENT=81193 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_information`
--

DROP TABLE IF EXISTS `system_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_information` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `value` longtext CHARACTER SET utf8 COLLATE utf8_bin,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `isStudent` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) DEFAULT '0',
  `email` varchar(100) DEFAULT NULL,
  `formCreator` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COMMENT='many-many link for a user''s current projects';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `virusChecks`
--

DROP TABLE IF EXISTS `virusChecks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `virusChecks` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `objectID` bigint(20) unsigned NOT NULL,
  `fieldName` varchar(100) NOT NULL,
  `state` tinyint(1) unsigned DEFAULT '1',
  `timestamp` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1736 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watermarks`
--

DROP TABLE IF EXISTS `watermarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watermarks` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `data` longblob NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'mfcs'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-05-11  7:42:30
