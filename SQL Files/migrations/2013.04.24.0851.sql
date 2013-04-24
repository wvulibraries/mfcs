ALTER TABLE  `projects` ADD  `projectID` VARCHAR( 10 ) NULL DEFAULT NULL AFTER  `projectName` ,
ADD UNIQUE (
`projectID`
)