DROP TABLE IF EXISTS `checks`;
CREATE TABLE IF NOT EXISTS `checks` (
	`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(100) DEFAULT NULL,
	`value` varchar(100) DEFAULT NULL,
	PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

INSERT INTO `checks` (`name`,`value`) VALUES("uniqueIDCheck","1");