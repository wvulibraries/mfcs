DROP TABLE IF EXISTS `obsoleteFileTypes`;
CREATE TABLE `obsoleteFileTypes` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;