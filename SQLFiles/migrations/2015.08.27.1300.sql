# alter users table to keep track of students
ALTER TABLE `users` ADD COLUMN `isStudent` boolean NOT NULL DEFAULT 0;
# Adjust for new permission info
UPDATE `users` SET `status`='Admin' WHERE `status`='systems';
UPDATE `users` SET `status`='User' WHERE `status`='Staff';
UPDATE `users` SET `status`='User',`isStudent`='1' WHERE `status`='Student';
UPDATE `users` SET `status`='Editor' WHERE `status`='Librarian';
DELETE FROM `users` WHERE `username`='NULL';