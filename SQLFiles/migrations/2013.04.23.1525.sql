--
-- Database migration file
-- Use 2013.04.23.1445.sql as a base
--
CREATE TABLE IF NOT EXISTS `forms_projects` (
	`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`formID` int(10) unsigned NOT NULL,
	`projectID` int(10) unsigned NOT NULL,
	PRIMARY KEY (`ID`),
	KEY `formID` (`formID`,`projectID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='many to many link for projects and forms' AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

ALTER TABLE `objectProjects` DROP `projectNumber`