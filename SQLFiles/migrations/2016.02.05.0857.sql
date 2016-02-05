DROP TABLE IF EXISTS `system_information`;
CREATE TABLE IF NOT EXISTS `system_information` (
	`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(100) DEFAULT NULL,
	`value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
	PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

INSERT INTO `system_information` (`name`,`value`) VALUES("file_types","");