-- Add `linkTitle` field to `forms`
ALTER TABLE `mfcs`.`forms` ADD `linkTitle` VARCHAR( 20 ) NULL DEFAULT NULL, ADD UNIQUE (`linkTitle`);
