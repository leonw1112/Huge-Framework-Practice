CREATE TABLE IF NOT EXISTS `huge`.`group_chat_members` (
 `member_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing member id',
 `group_chat_id` int(11) NOT NULL COMMENT 'id of the group chat',
 `user_id` int(11) NOT NULL COMMENT 'id of the user',
 `joined_timestamp` bigint(20) NOT NULL COMMENT 'timestamp when user joined',
 PRIMARY KEY (`member_id`),
 UNIQUE KEY `group_user` (`group_chat_id`, `user_id`),
 KEY `group_chat_id` (`group_chat_id`),
 KEY `user_id` (`user_id`),
 FOREIGN KEY (`group_chat_id`) REFERENCES `group_chats`(`group_chat_id`) ON DELETE CASCADE,
 FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='members of group chats';
