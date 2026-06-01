CREATE TABLE IF NOT EXISTS `huge`.`group_chat_messages` (
 `message_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing message id',
 `group_chat_id` int(11) NOT NULL COMMENT 'id of the group chat',
 `sender_id` int(11) NOT NULL COMMENT 'id of the user sending the message',
 `message_text` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'content of the message',
 `message_timestamp` bigint(20) NOT NULL COMMENT 'timestamp when the message was sent',
 PRIMARY KEY (`message_id`),
 KEY `group_chat_id` (`group_chat_id`),
 KEY `sender_id` (`sender_id`),
 FOREIGN KEY (`group_chat_id`) REFERENCES `group_chats`(`group_chat_id`) ON DELETE CASCADE,
 FOREIGN KEY (`sender_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='group chat messages';
