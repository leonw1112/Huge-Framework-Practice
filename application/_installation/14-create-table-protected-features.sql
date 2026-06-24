CREATE TABLE IF NOT EXISTS `protected_features` (
  `feature_key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `is_protected` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = geschützt, 0 = offen',
  `updated_at` bigint(20) DEFAULT NULL COMMENT 'unix timestamp of last change',
  PRIMARY KEY (`feature_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='persisted feature protection flags';

-- Pre-populate with known features (all open by default)
INSERT INTO `protected_features` (`feature_key`, `is_protected`, `updated_at`) VALUES
('notes', 0, NULL),
('gallery', 0, NULL),
('chat', 0, NULL),
('dashboard', 0, NULL)
ON DUPLICATE KEY UPDATE `feature_key` = `feature_key`;
