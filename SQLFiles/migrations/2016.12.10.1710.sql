ALTER TABLE `forms`
  ADD COLUMN `exportPublic` tinyint(4) NOT NULL DEFAULT 0 AFTER  `metadata`,
  ADD COLUMN `exportOAI` tinyint(4) NOT NULL DEFAULT 0 AFTER  `exportPublic`;
