CREATE TABLE IF NOT EXISTS `temporary_permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'user that receives the temporary permission',
  `feature_key` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'unique identifier for a feature, e.g. gallery, chat, notes',
  `granted_at` bigint(20) NOT NULL COMMENT 'unix timestamp when permission was granted',
  `expires_at` bigint(20) NOT NULL COMMENT 'unix timestamp when permission expires',
  `granted_by` int(11) DEFAULT NULL COMMENT 'user_id of the admin who granted the permission',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = active, 0 = revoked',
  PRIMARY KEY (`permission_id`),
  KEY `idx_user_feature` (`user_id`, `feature_key`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_user_active` (`user_id`, `is_active`),
  CONSTRAINT `fk_temporary_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_temporary_permissions_granted_by` FOREIGN KEY (`granted_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='temporary feature permissions for users';
