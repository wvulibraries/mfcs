
-- CREATE USER 'systems'@'localhost' IDENTIFIED BY 'Te$t1234';
-- GRANT ALL PRIVILEGES ON * . * TO 'systems'@'%';
-- SET GLOBAL max_allowed_packet=1073741824;
-- FLUSH PRIVILEGES;

DROP DATABASE IF EXISTS `mfcs`;
CREATE DATABASE IF NOT EXISTS `mfcs`;
GRANT ALL PRIVILEGES ON `mfcs`.* TO 'systems'@'localhost';
SET GLOBAL max_allowed_packet=1073741824;
USE `mfcs`;
