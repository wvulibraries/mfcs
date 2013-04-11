-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 11, 2013 at 09:11 AM
-- Server version: 5.1.61
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `mfcs`
--

-- --------------------------------------------------------

--
-- Table structure for table `containers`
--

DROP TABLE IF EXISTS `containers`;
CREATE TABLE IF NOT EXISTS `containers` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `containerName` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `container` (`containerName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dupeMatching`
--

DROP TABLE IF EXISTS `dupeMatching`;
CREATE TABLE IF NOT EXISTS `dupeMatching` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `formID` int(10) unsigned DEFAULT NULL,
  `projectID` int(10) unsigned DEFAULT NULL,
  `objectID` int(10) unsigned DEFAULT NULL,
  `field` varchar(50) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

DROP TABLE IF EXISTS `forms`;
CREATE TABLE IF NOT EXISTS `forms` (
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
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `objectMetadataLinks`
--

DROP TABLE IF EXISTS `objectMetadataLinks`;
CREATE TABLE IF NOT EXISTS `objectMetadataLinks` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectID` int(10) unsigned DEFAULT NULL,
  `metadataID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `objectProjects`
--

DROP TABLE IF EXISTS `objectProjects`;
CREATE TABLE IF NOT EXISTS `objectProjects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectID` int(10) unsigned DEFAULT NULL,
  `projectID` int(10) unsigned DEFAULT NULL,
  `projectNumber` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `objects`
--

DROP TABLE IF EXISTS `objects`;
CREATE TABLE IF NOT EXISTS `objects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentID` int(10) unsigned DEFAULT NULL,
  `formID` int(10) unsigned DEFAULT NULL,
  `defaultProject` int(10) unsigned DEFAULT NULL,
  `data` text,
  `metadata` tinyint(4) DEFAULT NULL,
  `idno` varchar(20) DEFAULT NULL,
  `modifiedTime` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `objectTypes`
--

DROP TABLE IF EXISTS `objectTypes`;
CREATE TABLE IF NOT EXISTS `objectTypes` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectType` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `objectType` (`objectType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projectID` int(10) unsigned DEFAULT NULL,
  `userID` int(10) unsigned DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projectName` varchar(50) DEFAULT NULL,
  `forms` varchar(1000) DEFAULT NULL,
  `groupings` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `numbering` varchar(20) DEFAULT '#',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `revisions`
--

DROP TABLE IF EXISTS `revisions`;
CREATE TABLE IF NOT EXISTS `revisions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productionTable` varchar(30) DEFAULT NULL,
  `primaryID` int(11) DEFAULT NULL,
  `secondaryID` int(10) unsigned DEFAULT NULL,
  `metadata` text,
  `digitalObjects` blob,
  `relatedData` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(25) DEFAULT NULL,
  `lastname` varchar(25) DEFAULT NULL,
  `username` varchar(25) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users_projects`
--

DROP TABLE IF EXISTS `users_projects`;
CREATE TABLE IF NOT EXISTS `users_projects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `projectID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UD`),
  KEY `userID` (`userID`,`projectID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='many-many link for a user''s current projects';

-- --------------------------------------------------------

--
-- Table structure for table `watermarks`
--

DROP TABLE IF EXISTS `watermarks`;
CREATE TABLE IF NOT EXISTS `watermarks` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
