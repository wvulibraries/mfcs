DROP TABLE IF EXISTS `objectsData`;
CREATE TABLE `objectsData` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `objectID` int(10) unsigned NOT NULL,
  `fieldName` varchar(100) NOT NULL,
  `value` text,
  `encoded` tinyint(1) unsigned DEFAULT 0,
  PRIMARY KEY (`ID`), 
  INDEX (`objectID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
