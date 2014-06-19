DROP TABLE IF EXISTS `exports`;
CREATE TABLE IF NOT EXISTS `exports` (
	`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`formID` int(10) unsigned DEFAULT NULL,
	`projectID` int(10) unsigned DEFAULT NULL,
	`objectID` int(10) unsigned DEFAULT NULL,
	`date` int(11) unsigned DEFAULT NULL, 
	`info1` varchar(200) DEFAULT NULL,
	`info2` text,
	PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
