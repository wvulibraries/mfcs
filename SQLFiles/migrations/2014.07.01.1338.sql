ALTER TABLE `objectsData` ADD COLUMN `formID` int(10) unsigned NOT NULL AFTER `ID`, ADD INDEX (`formID`);

UPDATE `objectsData` SET `formID`=(SELECT `formID` FROM `objects` WHERE `objects`.`ID`=`objectsData`.`objectID` LIMIT 1);

DELETE FROM `objectsData` WHERE `formID`='0';
