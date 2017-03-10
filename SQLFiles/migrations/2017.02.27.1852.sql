ALTER TABLE `forms`
  ADD COLUMN `objPublicReleaseShow` tinyint(1) NOT NULL DEFAULT 1 AFTER  `exportOAI`,
  ADD COLUMN `objPublicReleaseDefaultTrue` tinyint(1) NOT NULL DEFAULT 1 AFTER  `objPublicReleaseShow`;
