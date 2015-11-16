ALTER TABLE `objects` ADD INDEX `modifiedTime` (`modifiedTime`);
ALTER TABLE `revisions` ADD INDEX `primaryID` (`primaryID`);
ALTER TABLE `revisions` ADD INDEX `secondaryID` (`secondaryID`);