DROP TABLE IF EXISTS `scheduler`;
CREATE TABLE `scheduler` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `minute` varchar(2) NOT NULL DEFAULT '*', -- 0-59
  `hour` varchar(2) NOT NULL DEFAULT '*', -- 0-23
  `dayofmonth` varchar(2) NOT NULL DEFAULT '*', -- 1-31
  `month` varchar(2) NOT NULL DEFAULT '*', -- 1-12
  `dayofweek` varchar(1) NOT NULL DEFAULT '*', -- 0-7 (0 or 7 is Sunday, or use names)
  `runnow` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `lastrun` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
