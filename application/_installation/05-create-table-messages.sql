CREATE TABLE IF NOT EXISTS `huge`.`messages` (
 `message_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing message id',
 `sender_id` int(11) NOT NULL COMMENT 'id of the user sending the message',
 `recipient_id` int(11) NOT NULL COMMENT 'id of the user receiving the message',
 `message_text` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'content of the message',
 `message_timestamp` bigint(20) NOT NULL COMMENT 'timestamp when the message was sent',
 `message_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'whether the message has been read',
 PRIMARY KEY (`message_id`),
 KEY `sender_id` (`sender_id`),
 KEY `recipient_id` (`recipient_id`),
 FOREIGN KEY (`sender_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
 FOREIGN KEY (`recipient_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user messages';
