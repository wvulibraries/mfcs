-- MySQL dump 10.13  Distrib 5.1.69, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: EngineAPI
-- ------------------------------------------------------
-- Server version	5.1.69-log

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
-- Table structure for table `engineConfig`
--

DROP TABLE IF EXISTS `engineConfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `engineConfig` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(50) DEFAULT NULL,
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) DEFAULT NULL,
  `referrer` text,
  `resource` text,
  `useragent` varchar(255) DEFAULT NULL,
  `function` varchar(25) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `querystring` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=126866539 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logArchives`
--

DROP TABLE IF EXISTS `logArchives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logArchives` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(10) unsigned NOT NULL,
  `ipID` int(10) unsigned DEFAULT NULL,
  `referrerID` int(6) unsigned DEFAULT NULL,
  `resourceID` int(10) unsigned DEFAULT NULL,
  `useragentID` int(10) unsigned DEFAULT NULL,
  `querystringID` int(6) unsigned DEFAULT NULL,
  `siteID` int(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `referrerID` (`referrerID`),
  KEY `useragentID` (`useragentID`),
  KEY `querystringID` (`querystringID`),
  KEY `siteID` (`siteID`),
  KEY `resourceID` (`resourceID`),
  KEY `ipID` (`ipID`),
  KEY `date` (`date`),
  CONSTRAINT `logArchives_ibfk_11` FOREIGN KEY (`referrerID`) REFERENCES `logReferrers` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logArchives_ibfk_12` FOREIGN KEY (`resourceID`) REFERENCES `logResources` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logArchives_ibfk_13` FOREIGN KEY (`useragentID`) REFERENCES `logUseragents` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logArchives_ibfk_14` FOREIGN KEY (`querystringID`) REFERENCES `logQuerystrings` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logArchives_ibfk_15` FOREIGN KEY (`siteID`) REFERENCES `logSites` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logArchives_ibfk_16` FOREIGN KEY (`ipID`) REFERENCES `logIPs` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=149423005 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logBrowserNames`
--

DROP TABLE IF EXISTS `logBrowserNames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logBrowserNames` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `browserName` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `browserName` (`browserName`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logBrowsers`
--

DROP TABLE IF EXISTS `logBrowsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logBrowsers` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `browserNameID` int(4) unsigned NOT NULL,
  `browserVersion` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `browserNameID` (`browserNameID`,`browserVersion`),
  CONSTRAINT `logBrowsers_ibfk_1` FOREIGN KEY (`browserNameID`) REFERENCES `logBrowserNames` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=782 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logDeviceNames`
--

DROP TABLE IF EXISTS `logDeviceNames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logDeviceNames` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `deviceName` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `deviceName` (`deviceName`)
) ENGINE=InnoDB AUTO_INCREMENT=1158 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logDevices`
--

DROP TABLE IF EXISTS `logDevices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logDevices` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `deviceNameID` int(4) unsigned NOT NULL,
  `deviceVersion` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `deviceNameID` (`deviceNameID`,`deviceVersion`),
  CONSTRAINT `logDevices_ibfk_1` FOREIGN KEY (`deviceNameID`) REFERENCES `logDeviceNames` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1517 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logDomains`
--

DROP TABLE IF EXISTS `logDomains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logDomains` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=13486 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logFragments`
--

DROP TABLE IF EXISTS `logFragments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logFragments` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `fragment` varchar(500) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `fragment` (`fragment`(255))
) ENGINE=InnoDB AUTO_INCREMENT=2464 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logIPs`
--

DROP TABLE IF EXISTS `logIPs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logIPs` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=396967 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logOSNames`
--

DROP TABLE IF EXISTS `logOSNames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logOSNames` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `osName` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `osName` (`osName`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logOSs`
--

DROP TABLE IF EXISTS `logOSs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logOSs` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `osNameID` int(4) unsigned NOT NULL,
  `osVersion` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `osNameID` (`osNameID`,`osVersion`),
  CONSTRAINT `logOSs_ibfk_1` FOREIGN KEY (`osNameID`) REFERENCES `logOSNames` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logQuerystrings`
--

DROP TABLE IF EXISTS `logQuerystrings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logQuerystrings` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `querystring` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `querystring` (`querystring`(255))
) ENGINE=InnoDB AUTO_INCREMENT=755582 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logReferrers`
--

DROP TABLE IF EXISTS `logReferrers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logReferrers` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `referrer` text NOT NULL,
  `domainID` int(6) unsigned DEFAULT NULL,
  `resourceID` int(10) unsigned DEFAULT NULL,
  `querystringID` int(6) unsigned DEFAULT NULL,
  `fragmentID` int(6) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `domainID` (`domainID`),
  KEY `resourceID` (`resourceID`),
  KEY `querystringID` (`querystringID`),
  KEY `fragmentID` (`fragmentID`),
  KEY `referrer` (`referrer`(200)),
  CONSTRAINT `logReferrers_ibfk_1` FOREIGN KEY (`domainID`) REFERENCES `logDomains` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logReferrers_ibfk_2` FOREIGN KEY (`resourceID`) REFERENCES `logResources` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logReferrers_ibfk_3` FOREIGN KEY (`querystringID`) REFERENCES `logQuerystrings` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logReferrers_ibfk_4` FOREIGN KEY (`fragmentID`) REFERENCES `logFragments` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=631739 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logResources`
--

DROP TABLE IF EXISTS `logResources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logResources` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(500) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `resource` (`resource`(255))
) ENGINE=InnoDB AUTO_INCREMENT=93095 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logSites`
--

DROP TABLE IF EXISTS `logSites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logSites` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `site` (`site`)
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logUseragentData`
--

DROP TABLE IF EXISTS `logUseragentData`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logUseragentData` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `browserID` int(4) unsigned DEFAULT NULL,
  `osID` int(4) unsigned DEFAULT NULL,
  `deviceID` int(4) unsigned DEFAULT NULL,
  `isComputer` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isMobile` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isTablet` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isSpider` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `browserID_2` (`browserID`,`osID`,`deviceID`,`isComputer`,`isMobile`,`isTablet`,`isSpider`),
  KEY `browserID` (`browserID`),
  KEY `osID` (`osID`),
  KEY `deviceID` (`deviceID`),
  CONSTRAINT `logUseragentData_ibfk_1` FOREIGN KEY (`browserID`) REFERENCES `logBrowsers` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logUseragentData_ibfk_2` FOREIGN KEY (`osID`) REFERENCES `logOSs` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `logUseragentData_ibfk_3` FOREIGN KEY (`deviceID`) REFERENCES `logDevices` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4257 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logUseragents`
--

DROP TABLE IF EXISTS `logUseragents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logUseragents` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `useragentDataID` int(6) unsigned NOT NULL,
  `useragent` varchar(5000) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `useragentID` (`useragentDataID`),
  KEY `useragent` (`useragent`(255)),
  CONSTRAINT `logUseragents_ibfk_1` FOREIGN KEY (`useragentDataID`) REFERENCES `logUseragentData` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66212 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statsBrowsers`
--

DROP TABLE IF EXISTS `statsBrowsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statsBrowsers` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteID` int(10) unsigned NOT NULL,
  `year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `resourceID` int(10) unsigned NOT NULL,
  `useragentDataID` int(10) unsigned NOT NULL,
  `hitsOnCampus` bigint(20) unsigned NOT NULL DEFAULT '0',
  `hitsOffCampus` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `siteID` (`siteID`,`year`,`month`,`resourceID`,`useragentDataID`),
  KEY `useragentDataID` (`useragentDataID`),
  KEY `resourceID` (`resourceID`),
  KEY `siteID_2` (`siteID`),
  CONSTRAINT `statsBrowsers_ibfk_1` FOREIGN KEY (`siteID`) REFERENCES `logSites` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `statsBrowsers_ibfk_2` FOREIGN KEY (`resourceID`) REFERENCES `logResources` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `statsBrowsers_ibfk_3` FOREIGN KEY (`useragentDataID`) REFERENCES `logUseragentData` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15629580 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statsHits`
--

DROP TABLE IF EXISTS `statsHits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statsHits` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteID` int(10) unsigned NOT NULL,
  `year` smallint(4) unsigned NOT NULL,
  `month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `hour` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `resourceID` int(10) unsigned NOT NULL,
  `visitsMobile` bigint(20) NOT NULL DEFAULT '0',
  `visitsTablet` bigint(20) NOT NULL DEFAULT '0',
  `visitsComputer` bigint(20) NOT NULL DEFAULT '0',
  `hitsMobile` bigint(20) NOT NULL DEFAULT '0',
  `hitsTablet` bigint(20) NOT NULL DEFAULT '0',
  `hitsComputer` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `siteID` (`siteID`,`year`,`month`,`day`,`hour`,`resourceID`),
  KEY `siteID_2` (`siteID`),
  KEY `resourceID` (`resourceID`),
  CONSTRAINT `statsHits_ibfk_1` FOREIGN KEY (`siteID`) REFERENCES `logSites` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `statsHits_ibfk_2` FOREIGN KEY (`resourceID`) REFERENCES `logResources` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20936857 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statsURLs`
--

DROP TABLE IF EXISTS `statsURLs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statsURLs` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteID` int(10) unsigned NOT NULL,
  `year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `resourceID` int(10) unsigned NOT NULL,
  `referrerID` int(10) unsigned DEFAULT NULL,
  `hitsMobile` bigint(20) NOT NULL DEFAULT '0',
  `hitsTablet` bigint(20) NOT NULL DEFAULT '0',
  `hitsComputer` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `siteID_2` (`siteID`,`year`,`month`,`resourceID`,`referrerID`),
  KEY `siteID` (`siteID`),
  KEY `resourceID` (`resourceID`),
  KEY `referrerID` (`referrerID`),
  CONSTRAINT `statsURLs_ibfk_1` FOREIGN KEY (`siteID`) REFERENCES `logSites` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `statsURLs_ibfk_2` FOREIGN KEY (`resourceID`) REFERENCES `logResources` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `statsURLs_ibfk_3` FOREIGN KEY (`referrerID`) REFERENCES `logReferrers` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42844879 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-11-07  8:45:13
