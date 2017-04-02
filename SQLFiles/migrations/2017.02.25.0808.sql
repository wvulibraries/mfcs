DROP TABLE IF EXISTS `objectUrls`;
CREATE TABLE `objectUrls` (
  `objectID` int(10) unsigned NOT NULL,
  `url` varchar(1000) NOT NULL,
  PRIMARY KEY (`objectID`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
