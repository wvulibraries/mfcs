ALTER TABLE `filesChecks` ADD COLUMN `objectID` bigint(20) UNSIGNED NOT NULL;
ALTER TABLE `filesChecks` ADD INDEX `objectID` (`objectID`);