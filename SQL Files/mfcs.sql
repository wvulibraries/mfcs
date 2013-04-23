-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 19, 2013 at 07:20 AM
-- Server version: 5.1.67
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;

--
-- Dumping data for table `dupeMatching`
--

INSERT INTO `dupeMatching` (`ID`, `formID`, `projectID`, `objectID`, `field`, `value`) VALUES
(7, 4, NULL, 6, 'persname', 'one'),
(8, 4, NULL, 7, 'persname', 'two'),
(9, 4, NULL, 8, 'persname', 'three'),
(10, 4, NULL, 9, 'persname', 'four'),
(16, 6, NULL, 15, 'title', 'foo'),
(17, 6, NULL, 16, 'title', 'foo'),
(18, 6, NULL, 17, 'title', 'testing'),
(19, 5, NULL, 18, 'title', 'test'),
(20, 5, NULL, 18, 'persnames', ''),
(21, 5, NULL, 18, 'untitled4', ''),
(22, 5, NULL, 18, 'untitled5', ''),
(23, 5, NULL, 18, 'untitled6', ''),
(24, 5, NULL, 18, 'untitled7', ''),
(25, 5, NULL, 18, 'untitled8', ''),
(26, 5, NULL, 18, 'untitled9', 'First Choice'),
(27, 5, NULL, 18, 'untitled10', '6'),
(28, 7, NULL, 19, 'title', 'test 2'),
(29, 7, NULL, 20, 'title', 'test2');

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
  `count` int(11) NOT NULL DEFAULT '0',
  `objectTitleField` varchar(50) DEFAULT 'title',
  `navigation` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `forms`
--

INSERT INTO `forms` (`ID`, `title`, `description`, `fields`, `idno`, `container`, `production`, `metadata`, `submitButton`, `updateButton`, `count`, `objectTitleField`, `navigation`) VALUES
(3, 'Subject Headings', '', 'YToxOntpOjA7YToyNTp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoidGV4dCI7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO3M6NToibGFiZWwiO3M6MTU6IlN1YmplY3QgSGVhZGluZyI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czo0OiJuYW1lIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo0OiJ0cnVlIjtzOjg6InJlYWRvbmx5IjtzOjU6ImZhbHNlIjtzOjg6ImRpc2FibGVkIjtzOjU6ImZhbHNlIjtzOjEzOiJwdWJsaWNSZWxlYXNlIjtzOjQ6InRydWUiO3M6ODoic29ydGFibGUiO3M6NDoidHJ1ZSI7czoxMDoic2VhcmNoYWJsZSI7czo0OiJ0cnVlIjtzOjEyOiJkaXNwbGF5VGFibGUiO3M6NDoidHJ1ZSI7czoxMDoidmFsaWRhdGlvbiI7czowOiIiO3M6MTU6InZhbGlkYXRpb25SZWdleCI7czowOiIiO3M6NjoiYWNjZXNzIjtzOjA6IiI7czo4OiJmaWVsZHNldCI7czowOiIiO3M6MzoibWluIjtzOjA6IiI7czozOiJtYXgiO3M6MDoiIjtzOjQ6InN0ZXAiO3M6MDoiIjtzOjY6ImZvcm1hdCI7czoxMDoiY2hhcmFjdGVycyI7fX0=', 'Tjs=', 0, 1, 1, 'Submit', 'Update', 0, 'name', NULL),
(4, 'Personal Names', '', 'YToxOntpOjA7YToyNTp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoidGV4dCI7czo0OiJuYW1lIjtzOjg6InBlcnNuYW1lIjtzOjU6ImxhYmVsIjtzOjEzOiJQZXJzb25hbCBOYW1lIjtzOjU6InZhbHVlIjtzOjA6IiI7czoxMToicGxhY2Vob2xkZXIiO3M6MDoiIjtzOjI6ImlkIjtzOjg6InBlcnNuYW1lIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo0OiJ0cnVlIjtzOjg6InJlYWRvbmx5IjtzOjU6ImZhbHNlIjtzOjg6ImRpc2FibGVkIjtzOjU6ImZhbHNlIjtzOjEzOiJwdWJsaWNSZWxlYXNlIjtzOjQ6InRydWUiO3M6ODoic29ydGFibGUiO3M6NDoidHJ1ZSI7czoxMDoic2VhcmNoYWJsZSI7czo0OiJ0cnVlIjtzOjEyOiJkaXNwbGF5VGFibGUiO3M6NDoidHJ1ZSI7czoxMDoidmFsaWRhdGlvbiI7czowOiIiO3M6MTU6InZhbGlkYXRpb25SZWdleCI7czowOiIiO3M6NjoiYWNjZXNzIjtzOjA6IiI7czo4OiJmaWVsZHNldCI7czowOiIiO3M6MzoibWluIjtzOjA6IiI7czozOiJtYXgiO3M6MDoiIjtzOjQ6InN0ZXAiO3M6MDoiIjtzOjY6ImZvcm1hdCI7czoxMDoiY2hhcmFjdGVycyI7fX0=', 'Tjs=', 0, 0, 1, 'Submit', 'Update', 0, 'persname', NULL),
(5, 'Book', 'This is a book item. ', 'YToxMDp7aTowO2E6MjQ6e3M6ODoicG9zaXRpb24iO3M6MToiMCI7czo0OiJ0eXBlIjtzOjQ6Imlkbm8iO3M6NDoibmFtZSI7czo0OiJpZG5vIjtzOjU6ImxhYmVsIjtzOjQ6IklETk8iO3M6NToidmFsdWUiO3M6MDoiIjtzOjExOiJwbGFjZWhvbGRlciI7czowOiIiO3M6MjoiaWQiO3M6NDoiaWRubyI7czo1OiJjbGFzcyI7czowOiIiO3M6NToic3R5bGUiO3M6MDoiIjtzOjg6InJlcXVpcmVkIjtzOjQ6InRydWUiO3M6MTA6ImR1cGxpY2F0ZXMiO3M6NDoidHJ1ZSI7czo4OiJyZWFkb25seSI7czo0OiJ0cnVlIjtzOjg6ImRpc2FibGVkIjtzOjU6ImZhbHNlIjtzOjEzOiJwdWJsaWNSZWxlYXNlIjtzOjQ6InRydWUiO3M6ODoic29ydGFibGUiO3M6NDoidHJ1ZSI7czoxMDoic2VhcmNoYWJsZSI7czo0OiJ0cnVlIjtzOjEyOiJkaXNwbGF5VGFibGUiO3M6NDoidHJ1ZSI7czoxMDoidmFsaWRhdGlvbiI7czowOiIiO3M6MTU6InZhbGlkYXRpb25SZWdleCI7czowOiIiO3M6NjoiYWNjZXNzIjtzOjA6IiI7czo4OiJmaWVsZHNldCI7czowOiIiO3M6OToibWFuYWdlZEJ5IjtzOjY6InN5c3RlbSI7czoxMDoiaWRub0Zvcm1hdCI7czowOiIiO3M6MTQ6InN0YXJ0SW5jcmVtZW50IjtzOjE6IjEiO31pOjE7YToyNTp7czo4OiJwb3NpdGlvbiI7czoxOiIxIjtzOjQ6InR5cGUiO3M6NDoidGV4dCI7czo0OiJuYW1lIjtzOjU6InRpdGxlIjtzOjU6ImxhYmVsIjtzOjU6IlRpdGxlIjtzOjU6InZhbHVlIjtzOjA6IiI7czoxMToicGxhY2Vob2xkZXIiO3M6MDoiIjtzOjI6ImlkIjtzOjU6InRpdGxlIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo0OiJ0cnVlIjtzOjg6InJlYWRvbmx5IjtzOjU6ImZhbHNlIjtzOjg6ImRpc2FibGVkIjtzOjU6ImZhbHNlIjtzOjEzOiJwdWJsaWNSZWxlYXNlIjtzOjQ6InRydWUiO3M6ODoic29ydGFibGUiO3M6NDoidHJ1ZSI7czoxMDoic2VhcmNoYWJsZSI7czo0OiJ0cnVlIjtzOjEyOiJkaXNwbGF5VGFibGUiO3M6NDoidHJ1ZSI7czoxMDoidmFsaWRhdGlvbiI7czowOiIiO3M6MTU6InZhbGlkYXRpb25SZWdleCI7czowOiIiO3M6NjoiYWNjZXNzIjtzOjA6IiI7czo4OiJmaWVsZHNldCI7czowOiIiO3M6MzoibWluIjtzOjA6IiI7czozOiJtYXgiO3M6MDoiIjtzOjQ6InN0ZXAiO3M6MDoiIjtzOjY6ImZvcm1hdCI7czoxMDoiY2hhcmFjdGVycyI7fWk6MjthOjI0OntzOjg6InBvc2l0aW9uIjtzOjE6IjIiO3M6NDoidHlwZSI7czoxMToibXVsdGlzZWxlY3QiO3M6NDoibmFtZSI7czo5OiJwZXJzbmFtZXMiO3M6NToibGFiZWwiO3M6MTQ6InBlcnNvbmFsIG5hbWVzIjtzOjU6InZhbHVlIjtzOjA6IiI7czoxMToicGxhY2Vob2xkZXIiO3M6MDoiIjtzOjI6ImlkIjtzOjk6InVudGl0bGVkMyI7czo1OiJjbGFzcyI7czowOiIiO3M6NToic3R5bGUiO3M6MDoiIjtzOjg6InJlcXVpcmVkIjtzOjU6ImZhbHNlIjtzOjEwOiJkdXBsaWNhdGVzIjtzOjU6ImZhbHNlIjtzOjg6InJlYWRvbmx5IjtzOjU6ImZhbHNlIjtzOjg6ImRpc2FibGVkIjtzOjU6ImZhbHNlIjtzOjEzOiJwdWJsaWNSZWxlYXNlIjtzOjQ6InRydWUiO3M6ODoic29ydGFibGUiO3M6MDoiIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjA6IiI7czoxMjoiZGlzcGxheVRhYmxlIjtzOjA6IiI7czoxMDoidmFsaWRhdGlvbiI7czowOiIiO3M6MTU6InZhbGlkYXRpb25SZWdleCI7czowOiIiO3M6NjoiYWNjZXNzIjtzOjA6IiI7czo4OiJmaWVsZHNldCI7czowOiIiO3M6MTE6ImNob2ljZXNUeXBlIjtzOjQ6ImZvcm0iO3M6MTE6ImNob2ljZXNGb3JtIjtzOjE6IjQiO3M6MTI6ImNob2ljZXNGaWVsZCI7czo4OiJwZXJzbmFtZSI7fWk6MzthOjI0OntzOjg6InBvc2l0aW9uIjtzOjE6IjMiO3M6NDoidHlwZSI7czoxMToibXVsdGlzZWxlY3QiO3M6NDoibmFtZSI7czo5OiJ1bnRpdGxlZDQiO3M6NToibGFiZWwiO3M6ODoiVW50aXRsZWQiO3M6NToidmFsdWUiO3M6MDoiIjtzOjExOiJwbGFjZWhvbGRlciI7czowOiIiO3M6MjoiaWQiO3M6OToidW50aXRsZWQ0IjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NToiZmFsc2UiO3M6MTA6ImR1cGxpY2F0ZXMiO3M6NToiZmFsc2UiO3M6ODoicmVhZG9ubHkiO3M6NToiZmFsc2UiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czowOiIiO3M6MTA6InNlYXJjaGFibGUiO3M6MDoiIjtzOjEyOiJkaXNwbGF5VGFibGUiO3M6MDoiIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czoxMToiY2hvaWNlc1R5cGUiO3M6NjoibWFudWFsIjtzOjE0OiJjaG9pY2VzRGVmYXVsdCI7czowOiIiO3M6MTQ6ImNob2ljZXNPcHRpb25zIjthOjI6e2k6MDtzOjEyOiJGaXJzdCBDaG9pY2UiO2k6MTtzOjEzOiJTZWNvbmQgQ2hvaWNlIjt9fWk6NDthOjI0OntzOjg6InBvc2l0aW9uIjtzOjE6IjQiO3M6NDoidHlwZSI7czo4OiJjaGVja2JveCI7czo0OiJuYW1lIjtzOjk6InVudGl0bGVkNSI7czo1OiJsYWJlbCI7czo4OiJVbnRpdGxlZCI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czo5OiJ1bnRpdGxlZDUiO3M6NToiY2xhc3MiO3M6MDoiIjtzOjU6InN0eWxlIjtzOjA6IiI7czo4OiJyZXF1aXJlZCI7czo1OiJmYWxzZSI7czoxMDoiZHVwbGljYXRlcyI7czo1OiJmYWxzZSI7czo4OiJyZWFkb25seSI7czo1OiJmYWxzZSI7czo4OiJkaXNhYmxlZCI7czo1OiJmYWxzZSI7czoxMzoicHVibGljUmVsZWFzZSI7czo0OiJ0cnVlIjtzOjg6InNvcnRhYmxlIjtzOjA6IiI7czoxMDoic2VhcmNoYWJsZSI7czowOiIiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czowOiIiO3M6MTA6InZhbGlkYXRpb24iO3M6MDoiIjtzOjE1OiJ2YWxpZGF0aW9uUmVnZXgiO3M6MDoiIjtzOjY6ImFjY2VzcyI7czowOiIiO3M6ODoiZmllbGRzZXQiO3M6MDoiIjtzOjExOiJjaG9pY2VzVHlwZSI7czo2OiJtYW51YWwiO3M6MTQ6ImNob2ljZXNEZWZhdWx0IjtzOjA6IiI7czoxNDoiY2hvaWNlc09wdGlvbnMiO2E6Mzp7aTowO3M6MTI6IkZpcnN0IENob2ljZSI7aToxO3M6MTM6IlNlY29uZCBDaG9pY2UiO2k6MjtzOjM6IjNyZCI7fX1pOjU7YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiI1IjtzOjQ6InR5cGUiO3M6ODoiY2hlY2tib3giO3M6NDoibmFtZSI7czo5OiJ1bnRpdGxlZDYiO3M6NToibGFiZWwiO3M6ODoiVW50aXRsZWQiO3M6NToidmFsdWUiO3M6MDoiIjtzOjExOiJwbGFjZWhvbGRlciI7czowOiIiO3M6MjoiaWQiO3M6OToidW50aXRsZWQ2IjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NToiZmFsc2UiO3M6MTA6ImR1cGxpY2F0ZXMiO3M6NToiZmFsc2UiO3M6ODoicmVhZG9ubHkiO3M6NToiZmFsc2UiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czowOiIiO3M6MTA6InNlYXJjaGFibGUiO3M6MDoiIjtzOjEyOiJkaXNwbGF5VGFibGUiO3M6MDoiIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czoxMToiY2hvaWNlc1R5cGUiO3M6NDoiZm9ybSI7czoxMToiY2hvaWNlc0Zvcm0iO3M6MToiNCI7czoxMjoiY2hvaWNlc0ZpZWxkIjtzOjg6InBlcnNuYW1lIjt9aTo2O2E6MjQ6e3M6ODoicG9zaXRpb24iO3M6MToiNiI7czo0OiJ0eXBlIjtzOjU6InJhZGlvIjtzOjQ6Im5hbWUiO3M6OToidW50aXRsZWQ3IjtzOjU6ImxhYmVsIjtzOjg6IlVudGl0bGVkIjtzOjU6InZhbHVlIjtzOjA6IiI7czoxMToicGxhY2Vob2xkZXIiO3M6MDoiIjtzOjI6ImlkIjtzOjk6InVudGl0bGVkNyI7czo1OiJjbGFzcyI7czowOiIiO3M6NToic3R5bGUiO3M6MDoiIjtzOjg6InJlcXVpcmVkIjtzOjU6ImZhbHNlIjtzOjEwOiJkdXBsaWNhdGVzIjtzOjU6ImZhbHNlIjtzOjg6InJlYWRvbmx5IjtzOjU6ImZhbHNlIjtzOjg6ImRpc2FibGVkIjtzOjU6ImZhbHNlIjtzOjEzOiJwdWJsaWNSZWxlYXNlIjtzOjQ6InRydWUiO3M6ODoic29ydGFibGUiO3M6MDoiIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjA6IiI7czoxMjoiZGlzcGxheVRhYmxlIjtzOjA6IiI7czoxMDoidmFsaWRhdGlvbiI7czowOiIiO3M6MTU6InZhbGlkYXRpb25SZWdleCI7czowOiIiO3M6NjoiYWNjZXNzIjtzOjA6IiI7czo4OiJmaWVsZHNldCI7czowOiIiO3M6MTE6ImNob2ljZXNUeXBlIjtzOjY6Im1hbnVhbCI7czoxNDoiY2hvaWNlc0RlZmF1bHQiO3M6MDoiIjtzOjE0OiJjaG9pY2VzT3B0aW9ucyI7YToyOntpOjA7czoxMjoiRmlyc3QgQ2hvaWNlIjtpOjE7czoxMzoiU2Vjb25kIENob2ljZSI7fX1pOjc7YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiI3IjtzOjQ6InR5cGUiO3M6NToicmFkaW8iO3M6NDoibmFtZSI7czo5OiJ1bnRpdGxlZDgiO3M6NToibGFiZWwiO3M6ODoiVW50aXRsZWQiO3M6NToidmFsdWUiO3M6MDoiIjtzOjExOiJwbGFjZWhvbGRlciI7czowOiIiO3M6MjoiaWQiO3M6OToidW50aXRsZWQ4IjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NToiZmFsc2UiO3M6MTA6ImR1cGxpY2F0ZXMiO3M6NToiZmFsc2UiO3M6ODoicmVhZG9ubHkiO3M6NToiZmFsc2UiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czowOiIiO3M6MTA6InNlYXJjaGFibGUiO3M6MDoiIjtzOjEyOiJkaXNwbGF5VGFibGUiO3M6MDoiIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czoxMToiY2hvaWNlc1R5cGUiO3M6NDoiZm9ybSI7czoxMToiY2hvaWNlc0Zvcm0iO3M6MToiNCI7czoxMjoiY2hvaWNlc0ZpZWxkIjtzOjg6InBlcnNuYW1lIjt9aTo4O2E6MjQ6e3M6ODoicG9zaXRpb24iO3M6MToiOCI7czo0OiJ0eXBlIjtzOjY6InNlbGVjdCI7czo0OiJuYW1lIjtzOjk6InVudGl0bGVkOSI7czo1OiJsYWJlbCI7czo4OiJVbnRpdGxlZCI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czo5OiJ1bnRpdGxlZDkiO3M6NToiY2xhc3MiO3M6MDoiIjtzOjU6InN0eWxlIjtzOjA6IiI7czo4OiJyZXF1aXJlZCI7czo1OiJmYWxzZSI7czoxMDoiZHVwbGljYXRlcyI7czo1OiJmYWxzZSI7czo4OiJyZWFkb25seSI7czo1OiJmYWxzZSI7czo4OiJkaXNhYmxlZCI7czo1OiJmYWxzZSI7czoxMzoicHVibGljUmVsZWFzZSI7czo0OiJ0cnVlIjtzOjg6InNvcnRhYmxlIjtzOjA6IiI7czoxMDoic2VhcmNoYWJsZSI7czowOiIiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czowOiIiO3M6MTA6InZhbGlkYXRpb24iO3M6MDoiIjtzOjE1OiJ2YWxpZGF0aW9uUmVnZXgiO3M6MDoiIjtzOjY6ImFjY2VzcyI7czowOiIiO3M6ODoiZmllbGRzZXQiO3M6MDoiIjtzOjExOiJjaG9pY2VzVHlwZSI7czo2OiJtYW51YWwiO3M6MTQ6ImNob2ljZXNEZWZhdWx0IjtzOjA6IiI7czoxNDoiY2hvaWNlc09wdGlvbnMiO2E6Mjp7aTowO3M6MTI6IkZpcnN0IENob2ljZSI7aToxO3M6MTM6IlNlY29uZCBDaG9pY2UiO319aTo5O2E6MjQ6e3M6ODoicG9zaXRpb24iO3M6MToiOSI7czo0OiJ0eXBlIjtzOjY6InNlbGVjdCI7czo0OiJuYW1lIjtzOjEwOiJ1bnRpdGxlZDEwIjtzOjU6ImxhYmVsIjtzOjg6IlVudGl0bGVkIjtzOjU6InZhbHVlIjtzOjA6IiI7czoxMToicGxhY2Vob2xkZXIiO3M6MDoiIjtzOjI6ImlkIjtzOjEwOiJ1bnRpdGxlZDEwIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NToiZmFsc2UiO3M6MTA6ImR1cGxpY2F0ZXMiO3M6NToiZmFsc2UiO3M6ODoicmVhZG9ubHkiO3M6NToiZmFsc2UiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czowOiIiO3M6MTA6InNlYXJjaGFibGUiO3M6MDoiIjtzOjEyOiJkaXNwbGF5VGFibGUiO3M6MDoiIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czoxMToiY2hvaWNlc1R5cGUiO3M6NDoiZm9ybSI7czoxMToiY2hvaWNlc0Zvcm0iO3M6MToiNCI7czoxMjoiY2hvaWNlc0ZpZWxkIjtzOjg6InBlcnNuYW1lIjt9fQ==', 'YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoiaWRubyI7czo0OiJuYW1lIjtzOjQ6Imlkbm8iO3M6NToibGFiZWwiO3M6NDoiSUROTyI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czo0OiJpZG5vIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo0OiJ0cnVlIjtzOjg6InJlYWRvbmx5IjtzOjQ6InRydWUiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjQ6InRydWUiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czo5OiJtYW5hZ2VkQnkiO3M6Njoic3lzdGVtIjtzOjEwOiJpZG5vRm9ybWF0IjtzOjA6IiI7czoxNDoic3RhcnRJbmNyZW1lbnQiO3M6MToiMSI7fQ==', 1, 1, 0, 'Submit', 'Update', 1, 'title', NULL),
(6, 'Test 2', 'This is another form', 'YToyOntpOjA7YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoiaWRubyI7czo0OiJuYW1lIjtzOjQ6Imlkbm8iO3M6NToibGFiZWwiO3M6NDoiSUROTyI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czo0OiJpZG5vIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo0OiJ0cnVlIjtzOjg6InJlYWRvbmx5IjtzOjQ6InRydWUiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjQ6InRydWUiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czo5OiJtYW5hZ2VkQnkiO3M6Njoic3lzdGVtIjtzOjEwOiJpZG5vRm9ybWF0IjtzOjk6InRlc3RfIyMjIyI7czoxNDoic3RhcnRJbmNyZW1lbnQiO3M6MToiMSI7fWk6MTthOjI1OntzOjg6InBvc2l0aW9uIjtzOjE6IjEiO3M6NDoidHlwZSI7czo0OiJ0ZXh0IjtzOjQ6Im5hbWUiO3M6NToidGl0bGUiO3M6NToibGFiZWwiO3M6NToiVGl0bGUiO3M6NToidmFsdWUiO3M6MDoiIjtzOjExOiJwbGFjZWhvbGRlciI7czowOiIiO3M6MjoiaWQiO3M6NToidGl0bGUiO3M6NToiY2xhc3MiO3M6MDoiIjtzOjU6InN0eWxlIjtzOjA6IiI7czo4OiJyZXF1aXJlZCI7czo0OiJ0cnVlIjtzOjEwOiJkdXBsaWNhdGVzIjtzOjQ6InRydWUiO3M6ODoicmVhZG9ubHkiO3M6NToiZmFsc2UiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjQ6InRydWUiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czozOiJtaW4iO3M6MDoiIjtzOjM6Im1heCI7czowOiIiO3M6NDoic3RlcCI7czowOiIiO3M6NjoiZm9ybWF0IjtzOjEwOiJjaGFyYWN0ZXJzIjt9fQ==', 'YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoiaWRubyI7czo0OiJuYW1lIjtzOjQ6Imlkbm8iO3M6NToibGFiZWwiO3M6NDoiSUROTyI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czo0OiJpZG5vIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo0OiJ0cnVlIjtzOjg6InJlYWRvbmx5IjtzOjQ6InRydWUiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjQ6InRydWUiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czo5OiJtYW5hZ2VkQnkiO3M6Njoic3lzdGVtIjtzOjEwOiJpZG5vRm9ybWF0IjtzOjk6InRlc3RfIyMjIyI7czoxNDoic3RhcnRJbmNyZW1lbnQiO3M6MToiMSI7fQ==', 0, 1, 0, 'Submit', 'Update', 4, 'title', NULL),
(7, 'Chapter', '', 'YToyOntpOjA7YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoiaWRubyI7czo0OiJuYW1lIjtzOjQ6Imlkbm8iO3M6NToibGFiZWwiO3M6NDoiSUROTyI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czo0OiJpZG5vIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo0OiJ0cnVlIjtzOjg6InJlYWRvbmx5IjtzOjQ6InRydWUiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjQ6InRydWUiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czo5OiJtYW5hZ2VkQnkiO3M6Njoic3lzdGVtIjtzOjEwOiJpZG5vRm9ybWF0IjtzOjA6IiI7czoxNDoic3RhcnRJbmNyZW1lbnQiO3M6MToiMSI7fWk6MTthOjI1OntzOjg6InBvc2l0aW9uIjtzOjE6IjEiO3M6NDoidHlwZSI7czo0OiJ0ZXh0IjtzOjQ6Im5hbWUiO3M6NToidGl0bGUiO3M6NToibGFiZWwiO3M6NToiVGl0bGUiO3M6NToidmFsdWUiO3M6MDoiIjtzOjExOiJwbGFjZWhvbGRlciI7czowOiIiO3M6MjoiaWQiO3M6NToidGl0bGUiO3M6NToiY2xhc3MiO3M6MDoiIjtzOjU6InN0eWxlIjtzOjA6IiI7czo4OiJyZXF1aXJlZCI7czo0OiJ0cnVlIjtzOjEwOiJkdXBsaWNhdGVzIjtzOjQ6InRydWUiO3M6ODoicmVhZG9ubHkiO3M6NToiZmFsc2UiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjQ6InRydWUiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czozOiJtaW4iO3M6MDoiIjtzOjM6Im1heCI7czowOiIiO3M6NDoic3RlcCI7czowOiIiO3M6NjoiZm9ybWF0IjtzOjEwOiJjaGFyYWN0ZXJzIjt9fQ==', 'YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoiaWRubyI7czo0OiJuYW1lIjtzOjQ6Imlkbm8iO3M6NToibGFiZWwiO3M6NDoiSUROTyI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czowOiIiO3M6NToiY2xhc3MiO3M6MDoiIjtzOjU6InN0eWxlIjtzOjA6IiI7czo4OiJyZXF1aXJlZCI7czo0OiJ0cnVlIjtzOjEwOiJkdXBsaWNhdGVzIjtzOjQ6InRydWUiO3M6ODoicmVhZG9ubHkiO3M6NDoidHJ1ZSI7czo4OiJkaXNhYmxlZCI7czo1OiJmYWxzZSI7czoxMzoicHVibGljUmVsZWFzZSI7czo0OiJ0cnVlIjtzOjg6InNvcnRhYmxlIjtzOjQ6InRydWUiO3M6MTA6InNlYXJjaGFibGUiO3M6NDoidHJ1ZSI7czoxMjoiZGlzcGxheVRhYmxlIjtzOjQ6InRydWUiO3M6MTA6InZhbGlkYXRpb24iO3M6MDoiIjtzOjE1OiJ2YWxpZGF0aW9uUmVnZXgiO3M6MDoiIjtzOjY6ImFjY2VzcyI7czowOiIiO3M6ODoiZmllbGRzZXQiO3M6MDoiIjtzOjk6Im1hbmFnZWRCeSI7czo2OiJzeXN0ZW0iO3M6MTA6Imlkbm9Gb3JtYXQiO3M6MDoiIjtzOjE0OiJzdGFydEluY3JlbWVudCI7czoxOiIxIjt9', 1, 1, 0, 'Submit', 'Update', 3, 'title', NULL),
(8, 'Page', '', 'YTozOntpOjA7YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoiaWRubyI7czo0OiJuYW1lIjtzOjQ6Imlkbm8iO3M6NToibGFiZWwiO3M6NDoiSUROTyI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czo0OiJpZG5vIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo0OiJ0cnVlIjtzOjg6InJlYWRvbmx5IjtzOjQ6InRydWUiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjQ6InRydWUiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czo5OiJtYW5hZ2VkQnkiO3M6Njoic3lzdGVtIjtzOjEwOiJpZG5vRm9ybWF0IjtzOjA6IiI7czoxNDoic3RhcnRJbmNyZW1lbnQiO3M6MToiMSI7fWk6MTthOjI1OntzOjg6InBvc2l0aW9uIjtzOjE6IjEiO3M6NDoidHlwZSI7czo0OiJ0ZXh0IjtzOjQ6Im5hbWUiO3M6NToidGl0bGUiO3M6NToibGFiZWwiO3M6NToiVGl0bGUiO3M6NToidmFsdWUiO3M6MDoiIjtzOjExOiJwbGFjZWhvbGRlciI7czowOiIiO3M6MjoiaWQiO3M6NToidGl0bGUiO3M6NToiY2xhc3MiO3M6MDoiIjtzOjU6InN0eWxlIjtzOjA6IiI7czo4OiJyZXF1aXJlZCI7czo0OiJ0cnVlIjtzOjEwOiJkdXBsaWNhdGVzIjtzOjQ6InRydWUiO3M6ODoicmVhZG9ubHkiO3M6NToiZmFsc2UiO3M6ODoiZGlzYWJsZWQiO3M6NToiZmFsc2UiO3M6MTM6InB1YmxpY1JlbGVhc2UiO3M6NDoidHJ1ZSI7czo4OiJzb3J0YWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJzZWFyY2hhYmxlIjtzOjQ6InRydWUiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czo0OiJ0cnVlIjtzOjEwOiJ2YWxpZGF0aW9uIjtzOjA6IiI7czoxNToidmFsaWRhdGlvblJlZ2V4IjtzOjA6IiI7czo2OiJhY2Nlc3MiO3M6MDoiIjtzOjg6ImZpZWxkc2V0IjtzOjA6IiI7czozOiJtaW4iO3M6MDoiIjtzOjM6Im1heCI7czowOiIiO3M6NDoic3RlcCI7czowOiIiO3M6NjoiZm9ybWF0IjtzOjEwOiJjaGFyYWN0ZXJzIjt9aToyO2E6NDE6e3M6ODoicG9zaXRpb24iO3M6MToiMiI7czo0OiJ0eXBlIjtzOjQ6ImZpbGUiO3M6NDoibmFtZSI7czo5OiJwYWdlSW1hZ2UiO3M6NToibGFiZWwiO3M6MTA6IlBhZ2UgSW1hZ2UiO3M6NToidmFsdWUiO3M6MDoiIjtzOjExOiJwbGFjZWhvbGRlciI7czowOiIiO3M6MjoiaWQiO3M6OToicGFnZUltYWdlIjtzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7czowOiIiO3M6ODoicmVxdWlyZWQiO3M6NDoidHJ1ZSI7czoxMDoiZHVwbGljYXRlcyI7czo1OiJmYWxzZSI7czo4OiJyZWFkb25seSI7czo1OiJmYWxzZSI7czo4OiJkaXNhYmxlZCI7czo1OiJmYWxzZSI7czoxMzoicHVibGljUmVsZWFzZSI7czo0OiJ0cnVlIjtzOjg6InNvcnRhYmxlIjtzOjA6IiI7czoxMDoic2VhcmNoYWJsZSI7czowOiIiO3M6MTI6ImRpc3BsYXlUYWJsZSI7czowOiIiO3M6MTA6InZhbGlkYXRpb24iO3M6MDoiIjtzOjE1OiJ2YWxpZGF0aW9uUmVnZXgiO3M6MDoiIjtzOjY6ImFjY2VzcyI7czowOiIiO3M6ODoiZmllbGRzZXQiO3M6MDoiIjtzOjE3OiJhbGxvd2VkRXh0ZW5zaW9ucyI7YToxOntpOjA7czozOiJ0aWYiO31zOjEzOiJtdWx0aXBsZUZpbGVzIjtzOjA6IiI7czo3OiJjb21iaW5lIjtzOjA6IiI7czozOiJvY3IiO3M6NDoidHJ1ZSI7czo3OiJjb252ZXJ0IjtzOjQ6InRydWUiO3M6MTM6ImNvbnZlcnRIZWlnaHQiO3M6MDoiIjtzOjEyOiJjb252ZXJ0V2lkdGgiO3M6MDoiIjtzOjEzOiJjb252ZXJ0Rm9ybWF0IjtzOjM6IkpQMiI7czo5OiJ3YXRlcm1hcmsiO3M6NToiZmFsc2UiO3M6MTQ6IndhdGVybWFya0ltYWdlIjtzOjA6IiI7czoxNzoid2F0ZXJtYXJrTG9jYXRpb24iO3M6MDoiIjtzOjY6ImJvcmRlciI7czo1OiJmYWxzZSI7czoxMjoiYm9yZGVySGVpZ2h0IjtzOjA6IiI7czoxMToiYm9yZGVyV2lkdGgiO3M6MDoiIjtzOjExOiJib3JkZXJDb2xvciI7czowOiIiO3M6OToidGh1bWJuYWlsIjtzOjQ6InRydWUiO3M6MTU6InRodW1ibmFpbEhlaWdodCI7czozOiIyMDAiO3M6MTQ6InRodW1ibmFpbFdpZHRoIjtzOjM6IjIwMCI7czoxNToidGh1bWJuYWlsRm9ybWF0IjtzOjQ6IkpQRUciO3M6MzoibXAzIjtzOjI6Im9uIjt9fQ==', 'YToyNDp7czo4OiJwb3NpdGlvbiI7czoxOiIwIjtzOjQ6InR5cGUiO3M6NDoiaWRubyI7czo0OiJuYW1lIjtzOjQ6Imlkbm8iO3M6NToibGFiZWwiO3M6NDoiSUROTyI7czo1OiJ2YWx1ZSI7czowOiIiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjA6IiI7czoyOiJpZCI7czowOiIiO3M6NToiY2xhc3MiO3M6MDoiIjtzOjU6InN0eWxlIjtzOjA6IiI7czo4OiJyZXF1aXJlZCI7czo0OiJ0cnVlIjtzOjEwOiJkdXBsaWNhdGVzIjtzOjQ6InRydWUiO3M6ODoicmVhZG9ubHkiO3M6NDoidHJ1ZSI7czo4OiJkaXNhYmxlZCI7czo1OiJmYWxzZSI7czoxMzoicHVibGljUmVsZWFzZSI7czo0OiJ0cnVlIjtzOjg6InNvcnRhYmxlIjtzOjQ6InRydWUiO3M6MTA6InNlYXJjaGFibGUiO3M6NDoidHJ1ZSI7czoxMjoiZGlzcGxheVRhYmxlIjtzOjQ6InRydWUiO3M6MTA6InZhbGlkYXRpb24iO3M6MDoiIjtzOjE1OiJ2YWxpZGF0aW9uUmVnZXgiO3M6MDoiIjtzOjY6ImFjY2VzcyI7czowOiIiO3M6ODoiZmllbGRzZXQiO3M6MDoiIjtzOjk6Im1hbmFnZWRCeSI7czo2OiJzeXN0ZW0iO3M6MTA6Imlkbm9Gb3JtYXQiO3M6MDoiIjtzOjE0OiJzdGFydEluY3JlbWVudCI7czoxOiIxIjt9', 0, 1, 0, 'Submit', 'Update', 0, 'title', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `forms_projects`
--

DROP TABLE IF EXISTS `forms_projects`;
CREATE TABLE IF NOT EXISTS `forms_projects` (
	`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`formID` int(10) unsigned NOT NULL,
	`projectID` int(10) unsigned NOT NULL,
	PRIMARY KEY (`ID`),
	KEY `formID` (`formID`,`projectID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='many to many link for projects and forms' AUTO_INCREMENT=4 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `objectProjects`
--

DROP TABLE IF EXISTS `objectProjects`;
CREATE TABLE IF NOT EXISTS `objectProjects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectID` int(10) unsigned DEFAULT NULL,
  `projectID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `objectProjects`
--

INSERT INTO `objectProjects` (`ID`, `objectID`, `projectID`) VALUES
(4, 6, 2),
(5, 7, 2),
(6, 8, 2),
(7, 9, 2),
(13, 15, 2),
(14, 16, 2),
(15, 17, 2),
(16, 18, 2),
(17, 19, 2),
(18, 20, 2);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `objects`
--

INSERT INTO `objects` (`ID`, `parentID`, `formID`, `defaultProject`, `data`, `metadata`, `idno`, `modifiedTime`) VALUES
(6, 0, 4, NULL, 'YToxOntzOjg6InBlcnNuYW1lIjtzOjM6Im9uZSI7fQ==', 1, NULL, 1366134072),
(7, 0, 4, NULL, 'YToxOntzOjg6InBlcnNuYW1lIjtzOjM6InR3byI7fQ==', 1, NULL, 1366134076),
(8, 0, 4, NULL, 'YToxOntzOjg6InBlcnNuYW1lIjtzOjU6InRocmVlIjt9', 1, NULL, 1366134078),
(9, 0, 4, NULL, 'YToxOntzOjg6InBlcnNuYW1lIjtzOjQ6ImZvdXIiO30=', 1, NULL, 1366134082),
(15, 0, 6, NULL, 'YToxOntzOjU6InRpdGxlIjtzOjM6ImZvbyI7fQ==', 0, 'test_', 1366297619),
(16, 0, 6, NULL, 'YToxOntzOjU6InRpdGxlIjtzOjM6ImZvbyI7fQ==', 0, 'test_0003', 1366297844),
(17, 0, 6, NULL, 'YToxOntzOjU6InRpdGxlIjtzOjc6InRlc3RpbmciO30=', 0, 'test_0004', 1366298029),
(18, 0, 5, NULL, 'YTo5OntzOjU6InRpdGxlIjtzOjQ6InRlc3QiO3M6OToicGVyc25hbWVzIjtOO3M6OToidW50aXRsZWQ0IjtOO3M6OToidW50aXRsZWQ1IjtOO3M6OToidW50aXRsZWQ2IjtOO3M6OToidW50aXRsZWQ3IjtOO3M6OToidW50aXRsZWQ4IjtOO3M6OToidW50aXRsZWQ5IjtzOjEyOiJGaXJzdCBDaG9pY2UiO3M6MTA6InVudGl0bGVkMTAiO3M6MToiNiI7fQ==', 0, '', 1366305446),
(19, 0, 7, NULL, 'YToxOntzOjU6InRpdGxlIjtzOjY6InRlc3QgMiI7fQ==', 0, '', 1366305456),
(20, 18, 7, NULL, 'YToxOntzOjU6InRpdGxlIjtzOjU6InRlc3QyIjt9', 0, '', 1366305480);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `formID` int(10) unsigned DEFAULT NULL,
  `userID` int(10) unsigned DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`ID`, `formID`, `userID`, `type`) VALUES
(1, 5, 2, 1),
(2, 5, 2, 2),
(3, 5, 2, 3);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`ID`, `projectName`, `forms`, `groupings`, `numbering`) VALUES
(2, '1st', 'YToyOntzOjg6Im1ldGFkYXRhIjthOjA6e31zOjc6Im9iamVjdHMiO2E6Mzp7aTowO3M6MToiNSI7aToxO3M6MToiNyI7aToyO3M6MToiOCI7fX0=', 'YTowOnt9', '#'),
(3, '2nd', 'YToyOntzOjg6Im1ldGFkYXRhIjthOjA6e31zOjc6Im9iamVjdHMiO2E6MTp7aTowO3M6MToiNiI7fX0=', 'YTowOnt9', '#'),
(4, '3rd', NULL, NULL, '#');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
	`pagination` int(10) unsigned NOT NULL DEFAULT '25',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `firstname`, `lastname`, `username`, `status`) VALUES
(2, NULL, NULL, 'mrbond', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_projects`
--

DROP TABLE IF EXISTS `users_projects`;
CREATE TABLE IF NOT EXISTS `users_projects` (
  `UD` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `projectID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UD`),
  KEY `userID` (`userID`,`projectID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='many-many link for a user''s current projects' AUTO_INCREMENT=11 ;

--
-- Dumping data for table `users_projects`
--

INSERT INTO `users_projects` (`UD`, `userID`, `projectID`) VALUES
(10, 2, 2);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
