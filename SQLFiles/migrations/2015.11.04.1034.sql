DROP TABLE IF EXISTS `filesChecks`;
CREATE TABLE `filesChecks` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(1000) NOT NULL,
  `checksum` varchar(33) DEFAULT NULL,
  `lastChecked` int(20) unsigned DEFAULT NULL,
  `pass` tinyint(1) unsigned DEFAULT 0,
  PRIMARY KEY (`ID`), 
  INDEX (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
