DROP TABLE IF EXISTS `metadataStandards`;
CREATE TABLE `metadataStandards` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `typeID` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;