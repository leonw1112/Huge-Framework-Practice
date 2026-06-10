CREATE TABLE IF NOT EXISTS `huge`.`gallery_shares` (
 `share_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing share_id',
 `gallery_id` int(11) NOT NULL COMMENT 'id of the shared image',
 `owner_user_id` int(11) NOT NULL COMMENT 'id of the user who owns the image',
 `shared_with_user_id` int(11) NOT NULL COMMENT 'id of the user the image is shared with',
 `created_at` bigint(20) NOT NULL COMMENT 'timestamp of when the share was created',
 PRIMARY KEY (`share_id`),
 UNIQUE KEY `unique_share` (`gallery_id`, `shared_with_user_id`),
 KEY `gallery_id` (`gallery_id`),
 KEY `owner_user_id` (`owner_user_id`),
 KEY `shared_with_user_id` (`shared_with_user_id`),
 CONSTRAINT `gallery_shares_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`gallery_id`) ON DELETE CASCADE,
 CONSTRAINT `gallery_shares_ibfk_2` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
 CONSTRAINT `gallery_shares_ibfk_3` FOREIGN KEY (`shared_with_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='gallery image shares between users';
