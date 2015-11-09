DROP TABLE IF EXISTS `locks`;
CREATE TABLE `locks` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(25) NOT NULL,
  `typeID` bigint(20) unsigned NOT NULL,
  `user` int(20) unsigned NOT NULL,
  `date` int(20) unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  `mfcs`.`locks` ADD INDEX  `type` (  `type` );
ALTER TABLE  `mfcs`.`locks` ADD INDEX  `typeID` (  `typeID` );