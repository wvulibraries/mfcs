DROP TABLE IF EXISTS `objectProcessing`;
CREATE TABLE `objectProcessing` (
	`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`objectID` bigint(20) unsigned NOT NULL,
	`fieldName` varchar(100) NOT NULL,
	`state` tinyint(1) unsigned DEFAULT 1,
	`timestamp` int(11) unsigned,
	PRIMARY KEY (`ID`),
	INDEX(`objectID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;