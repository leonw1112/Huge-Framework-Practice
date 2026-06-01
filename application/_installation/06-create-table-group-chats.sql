CREATE TABLE IF NOT EXISTS `huge`.`group_chats` (
 `group_chat_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing group chat id',
 `group_chat_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name of the group chat',
 `group_chat_description` text COLLATE utf8_unicode_ci COMMENT 'description of the group chat',
 `created_by` int(11) NOT NULL COMMENT 'id of the user who created the group',
 `created_timestamp` bigint(20) NOT NULL COMMENT 'timestamp when the group was created',
 PRIMARY KEY (`group_chat_id`),
 KEY `created_by` (`created_by`),
 FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='group chats';
