DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
	`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`username` varchar(100) DEFAULT NULL,
	`ip` varchar(15) default NULL,
	`action` varchar(100) DEFAULT NULL,
	`objectID` int(10) unsigned DEFAULT 0,
	`formID` int(10) unsigned DEFAULT 0,
	`info` varchar(100) DEFAULT NULL,
	PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;