DROP DATABASE IF EXISTS `EngineAPI`;
CREATE DATABASE IF NOT EXISTS `EngineAPI`;
GRANT ALL PRIVILEGES ON EngineAPI.* TO 'username'@'%';

DROP DATABASE IF EXISTS `engineCMS`;
CREATE DATABASE IF NOT EXISTS `engineCMS`;
GRANT ALL PRIVILEGES ON engineCMS.* TO 'username'@'%';

DROP DATABASE IF EXISTS `mfcs`;
CREATE DATABASE IF NOT EXISTS `mfcs`;
GRANT ALL PRIVILEGES ON mfcs.* TO 'username'@'%';
SET GLOBAL max_allowed_packet=1073741824;
USE `mfcs`;