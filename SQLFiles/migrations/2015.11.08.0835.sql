DROP TABLE IF EXISTS `locks`;
CREATE TABLE `locks` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(25) NOT NULL,
  `typeID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` nt(20) unsigned NOT NULL,
  `date` int(20) unsigned DEFAULT NULL
  PRIMARY KEY (`ID`), 
  INDEX (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;