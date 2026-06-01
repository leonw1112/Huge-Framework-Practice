-- Add message_read column to group_chat_messages table
ALTER TABLE `huge`.`group_chat_messages` ADD COLUMN `message_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'whether the message has been read' AFTER `message_timestamp`;
ALTER TABLE `huge`.`group_chat_messages` ADD INDEX `group_read` (`group_chat_id`, `message_read`);
