DROP TABLE IF EXISTS `virusChecks`;
CREATE TABLE `virusChecks` (
	`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`objectID` bigint(20) unsigned NOT NULL,
	`fieldName` varchar(100) NOT NULL,
	`state` tinyint(1) unsigned DEFAULT 1,
	`timestamp` int(11) unsigned,
	PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `checks` (`name`,`value`) VALUES("virus_cmd","1");