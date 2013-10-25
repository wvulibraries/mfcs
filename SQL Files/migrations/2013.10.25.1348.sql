-- Time to make data a Longtext field (bring on the 4GB of data!)
ALTER TABLE  `objects` CHANGE  `data`  `data` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL ;