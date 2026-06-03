-- ============================================
-- STORED PROCEDURES FOR MESSENGER SYSTEM
-- ============================================

-- ============================================
-- SECTION 1: PERSONAL MESSAGES PROCEDURES
-- ============================================

-- Send a personal message from one user to another
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_send_message$$

CREATE PROCEDURE sp_send_message(
    IN p_sender_id INT,
    IN p_recipient_id INT,
    IN p_message_text TEXT
)
BEGIN
    DECLARE v_rows_affected INT;
    
    -- Validate input parameters
    IF p_sender_id IS NULL OR p_recipient_id IS NULL OR p_message_text IS NULL OR LENGTH(TRIM(p_message_text)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid parameters: sender_id, recipient_id and message_text are required';
    END IF;
    
    -- Verify both users exist
    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_sender_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sender user does not exist';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_recipient_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Recipient user does not exist';
    END IF;
    
    -- Insert the message
    INSERT INTO messages (sender_id, recipient_id, message_text, message_timestamp, message_read)
    VALUES (p_sender_id, p_recipient_id, p_message_text, UNIX_TIMESTAMP(), 0);
    
    SELECT ROW_COUNT() INTO v_rows_affected;
    
    SELECT IF(v_rows_affected = 1, 1, 0) as success, v_rows_affected as rows_affected;
END$$

-- Get all messages between two users
DROP PROCEDURE IF EXISTS sp_get_messages_between_users$$

CREATE PROCEDURE sp_get_messages_between_users(
    IN p_user_id_1 INT,
    IN p_user_id_2 INT
)
BEGIN
    IF p_user_id_1 IS NULL OR p_user_id_2 IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Both user IDs are required';
    END IF;
    
    SELECT 
        message_id,
        sender_id,
        recipient_id,
        message_text,
        message_timestamp,
        message_read,
        FROM_UNIXTIME(message_timestamp) as formatted_timestamp
    FROM messages
    WHERE (sender_id = p_user_id_1 AND recipient_id = p_user_id_2)
       OR (sender_id = p_user_id_2 AND recipient_id = p_user_id_1)
    ORDER BY message_timestamp ASC;
END$$

-- Get unread message count between two users
DROP PROCEDURE IF EXISTS sp_get_unread_message_count$$

CREATE PROCEDURE sp_get_unread_message_count(
    IN p_sender_id INT,
    IN p_recipient_id INT
)
BEGIN
    DECLARE v_unread_count INT DEFAULT 0;
    
    IF p_sender_id IS NULL OR p_recipient_id IS NULL THEN
        SELECT 0 as unread_count;
    ELSE
        SELECT COUNT(*) INTO v_unread_count
        FROM messages
        WHERE sender_id = p_sender_id 
        AND recipient_id = p_recipient_id 
        AND message_read = 0;
        
        SELECT v_unread_count as unread_count;
    END IF;
END$$

-- Mark messages as read between two users
DROP PROCEDURE IF EXISTS sp_mark_messages_read$$

CREATE PROCEDURE sp_mark_messages_read(
    IN p_sender_id INT,
    IN p_recipient_id INT
)
BEGIN
    DECLARE v_rows_updated INT;
    
    IF p_sender_id IS NULL OR p_recipient_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Both user IDs are required';
    END IF;
    
    UPDATE messages
    SET message_read = 1
    WHERE sender_id = p_sender_id 
    AND recipient_id = p_recipient_id 
    AND message_read = 0;
    
    SELECT ROW_COUNT() INTO v_rows_updated;
    SELECT v_rows_updated as rows_updated;
END$$

-- Get unread message count per contact for a user
DROP PROCEDURE IF EXISTS sp_get_unread_per_contact$$

CREATE PROCEDURE sp_get_unread_per_contact(
    IN p_user_id INT
)
BEGIN
    IF p_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User ID is required';
    END IF;
    
    SELECT 
        sender_id,
        COUNT(*) as unread_count
    FROM messages
    WHERE recipient_id = p_user_id 
    AND message_read = 0
    GROUP BY sender_id
    ORDER BY unread_count DESC, sender_id ASC;
END$$

-- ============================================
-- SECTION 2: GROUP CHAT PROCEDURES
-- ============================================

-- Create a new group chat
DROP PROCEDURE IF EXISTS sp_create_group_chat$$

CREATE PROCEDURE sp_create_group_chat(
    IN p_group_name VARCHAR(100),
    IN p_group_description TEXT,
    IN p_creator_id INT,
    OUT p_group_chat_id INT
)
BEGIN
    IF p_group_name IS NULL OR LENGTH(TRIM(p_group_name)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group name is required';
    END IF;
    
    IF p_creator_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Creator ID is required';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_creator_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Creator user does not exist';
    END IF;
    
    INSERT INTO group_chats (group_chat_name, group_chat_description, created_by, created_timestamp)
    VALUES (p_group_name, p_group_description, p_creator_id, UNIX_TIMESTAMP());
    
    SET p_group_chat_id = LAST_INSERT_ID();
    
    -- Add creator as first member
    INSERT INTO group_chat_members (group_chat_id, user_id, joined_timestamp)
    VALUES (p_group_chat_id, p_creator_id, UNIX_TIMESTAMP());
END$$

-- Add a member to a group chat
DROP PROCEDURE IF EXISTS sp_add_member_to_group$$

CREATE PROCEDURE sp_add_member_to_group(
    IN p_group_chat_id INT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_existing_member INT DEFAULT 0;
    
    IF p_group_chat_id IS NULL OR p_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group chat ID and user ID are required';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM group_chats WHERE group_chat_id = p_group_chat_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group chat does not exist';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User does not exist';
    END IF;
    
    -- Check if user is already a member
    SELECT COUNT(*) INTO v_existing_member
    FROM group_chat_members
    WHERE group_chat_id = p_group_chat_id AND user_id = p_user_id;
    
    IF v_existing_member > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User is already a member of this group';
    END IF;
    
    INSERT INTO group_chat_members (group_chat_id, user_id, joined_timestamp)
    VALUES (p_group_chat_id, p_user_id, UNIX_TIMESTAMP());
    
    SELECT ROW_COUNT() as rows_affected;
END$$

-- Remove a member from a group chat
DROP PROCEDURE IF EXISTS sp_remove_member_from_group$$

CREATE PROCEDURE sp_remove_member_from_group(
    IN p_group_chat_id INT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_rows_deleted INT;
    DECLARE v_is_creator INT DEFAULT 0;
    
    IF p_group_chat_id IS NULL OR p_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group chat ID and user ID are required';
    END IF;
    
    -- Check if user is the creator
    SELECT COUNT(*) INTO v_is_creator
    FROM group_chats
    WHERE group_chat_id = p_group_chat_id AND created_by = p_user_id;
    
    IF v_is_creator > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot remove group creator from the group';
    END IF;
    
    DELETE FROM group_chat_members
    WHERE group_chat_id = p_group_chat_id AND user_id = p_user_id;
    
    SELECT ROW_COUNT() INTO v_rows_deleted;
    SELECT v_rows_deleted as rows_deleted;
END$$

-- Get all groups a user is member of
DROP PROCEDURE IF EXISTS sp_get_user_group_chats$$

CREATE PROCEDURE sp_get_user_group_chats(
    IN p_user_id INT
)
BEGIN
    IF p_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User ID is required';
    END IF;
    
    SELECT 
        gc.group_chat_id,
        gc.group_chat_name,
        gc.group_chat_description,
        gc.created_by,
        gc.created_timestamp,
        FROM_UNIXTIME(gc.created_timestamp) as formatted_created_date,
        u.user_name as creator_name,
        COUNT(DISTINCT gcm.user_id) as member_count,
        COUNT(DISTINCT CASE WHEN gcm2.message_read = 0 AND gcm2.sender_id != p_user_id THEN gcm2.message_id END) as unread_count
    FROM group_chats gc
    JOIN group_chat_members gcm ON gc.group_chat_id = gcm.group_chat_id
    LEFT JOIN users u ON gc.created_by = u.user_id
    LEFT JOIN group_chat_messages gcm2 ON gc.group_chat_id = gcm2.group_chat_id
    WHERE gcm.user_id = p_user_id
    GROUP BY gc.group_chat_id
    ORDER BY gc.created_timestamp DESC;
END$$

-- Get group chat details
DROP PROCEDURE IF EXISTS sp_get_group_chat_details$$

CREATE PROCEDURE sp_get_group_chat_details(
    IN p_group_chat_id INT
)
BEGIN
    IF p_group_chat_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group chat ID is required';
    END IF;
    
    SELECT 
        gc.group_chat_id,
        gc.group_chat_name,
        gc.group_chat_description,
        gc.created_by,
        gc.created_timestamp,
        FROM_UNIXTIME(gc.created_timestamp) as formatted_created_date,
        u.user_name as creator_name,
        COUNT(DISTINCT gcm.user_id) as member_count
    FROM group_chats gc
    LEFT JOIN users u ON gc.created_by = u.user_id
    LEFT JOIN group_chat_members gcm ON gc.group_chat_id = gcm.group_chat_id
    WHERE gc.group_chat_id = p_group_chat_id
    GROUP BY gc.group_chat_id;
END$$

-- ============================================
-- SECTION 3: GROUP CHAT MESSAGES PROCEDURES
-- ============================================

-- Send a message to a group chat
DROP PROCEDURE IF EXISTS sp_send_group_message$$

CREATE PROCEDURE sp_send_group_message(
    IN p_group_chat_id INT,
    IN p_sender_id INT,
    IN p_message_text TEXT
)
BEGIN
    DECLARE v_is_member INT DEFAULT 0;
    
    IF p_group_chat_id IS NULL OR p_sender_id IS NULL OR p_message_text IS NULL OR LENGTH(TRIM(p_message_text)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group chat ID, sender ID and message text are required';
    END IF;
    
    -- Verify sender is a member of the group
    SELECT COUNT(*) INTO v_is_member
    FROM group_chat_members
    WHERE group_chat_id = p_group_chat_id AND user_id = p_sender_id;
    
    IF v_is_member = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sender is not a member of this group chat';
    END IF;
    
    INSERT INTO group_chat_messages (group_chat_id, sender_id, message_text, message_timestamp, message_read)
    VALUES (p_group_chat_id, p_sender_id, p_message_text, UNIX_TIMESTAMP(), 0);
    
    SELECT ROW_COUNT() as rows_affected;
END$$

-- Get all messages from a group chat
DROP PROCEDURE IF EXISTS sp_get_group_chat_messages$$

CREATE PROCEDURE sp_get_group_chat_messages(
    IN p_group_chat_id INT
)
BEGIN
    IF p_group_chat_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group chat ID is required';
    END IF;
    
    SELECT 
        gcm.message_id,
        gcm.group_chat_id,
        gcm.sender_id,
        u.user_name,
        u.user_has_avatar,
        gcm.message_text,
        gcm.message_timestamp,
        FROM_UNIXTIME(gcm.message_timestamp) as formatted_timestamp,
        gcm.message_read
    FROM group_chat_messages gcm
    JOIN users u ON gcm.sender_id = u.user_id
    WHERE gcm.group_chat_id = p_group_chat_id
    ORDER BY gcm.message_timestamp ASC;
END$$

-- Get unread message count in a group chat for a user
DROP PROCEDURE IF EXISTS sp_get_group_unread_count$$

CREATE PROCEDURE sp_get_group_unread_count(
    IN p_group_chat_id INT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_unread_count INT DEFAULT 0;
    
    IF p_group_chat_id IS NULL OR p_user_id IS NULL THEN
        SELECT 0 as unread_count;
    ELSE
        SELECT COUNT(*) INTO v_unread_count
        FROM group_chat_messages
        WHERE group_chat_id = p_group_chat_id 
        AND message_read = 0 
        AND sender_id != p_user_id;
        
        SELECT v_unread_count as unread_count;
    END IF;
END$$

-- Mark all messages in a group chat as read for a user
DROP PROCEDURE IF EXISTS sp_mark_group_messages_read$$

CREATE PROCEDURE sp_mark_group_messages_read(
    IN p_group_chat_id INT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_rows_updated INT;
    
    IF p_group_chat_id IS NULL OR p_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group chat ID and user ID are required';
    END IF;
    
    UPDATE group_chat_messages
    SET message_read = 1
    WHERE group_chat_id = p_group_chat_id 
    AND sender_id != p_user_id 
    AND message_read = 0;
    
    SELECT ROW_COUNT() INTO v_rows_updated;
    SELECT v_rows_updated as rows_updated;
END$$

-- Get unread count per group for a user
DROP PROCEDURE IF EXISTS sp_get_unread_per_group$$

CREATE PROCEDURE sp_get_unread_per_group(
    IN p_user_id INT
)
BEGIN
    IF p_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User ID is required';
    END IF;
    
    SELECT 
        gcm.group_chat_id,
        gc.group_chat_name,
        COUNT(*) as unread_count
    FROM group_chat_messages gcm
    JOIN group_chats gc ON gcm.group_chat_id = gc.group_chat_id
    WHERE sender_id != p_user_id 
    AND message_read = 0 
    AND gcm.group_chat_id IN (
        SELECT group_chat_id FROM group_chat_members WHERE user_id = p_user_id
    )
    GROUP BY gcm.group_chat_id
    ORDER BY unread_count DESC, gcm.group_chat_id ASC;
END$$

-- Get all members of a group chat
DROP PROCEDURE IF EXISTS sp_get_group_members$$

CREATE PROCEDURE sp_get_group_members(
    IN p_group_chat_id INT
)
BEGIN
    IF p_group_chat_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Group chat ID is required';
    END IF;
    
    SELECT 
        u.user_id,
        u.user_name,
        u.user_email,
        u.user_has_avatar,
        gcm.joined_timestamp,
        FROM_UNIXTIME(gcm.joined_timestamp) as formatted_joined_date,
        CASE WHEN gc.created_by = u.user_id THEN 1 ELSE 0 END as is_creator
    FROM group_chat_members gcm
    JOIN users u ON gcm.user_id = u.user_id
    JOIN group_chats gc ON gcm.group_chat_id = gc.group_chat_id
    WHERE gcm.group_chat_id = p_group_chat_id
    ORDER BY gcm.joined_timestamp ASC;
END$$

-- Check if a user is member of a group chat
DROP PROCEDURE IF EXISTS sp_is_group_member$$

CREATE PROCEDURE sp_is_group_member(
    IN p_group_chat_id INT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_is_member INT DEFAULT 0;
    
    IF p_group_chat_id IS NULL OR p_user_id IS NULL THEN
        SELECT 0 as is_member;
    ELSE
        SELECT COUNT(*) INTO v_is_member
        FROM group_chat_members
        WHERE group_chat_id = p_group_chat_id AND user_id = p_user_id;
        
        SELECT IF(v_is_member > 0, 1, 0) as is_member;
    END IF;
END$$

-- Get total unread messages for a user across all groups
DROP PROCEDURE IF EXISTS sp_get_total_unread_messages$$

CREATE PROCEDURE sp_get_total_unread_messages(
    IN p_user_id INT
)
BEGIN
    DECLARE v_total_unread INT DEFAULT 0;
    
    IF p_user_id IS NULL THEN
        SELECT 0 as total_unread;
    ELSE
        -- Count unread group messages
        SELECT COUNT(*) INTO v_total_unread
        FROM group_chat_messages gcm
        WHERE sender_id != p_user_id 
        AND message_read = 0 
        AND gcm.group_chat_id IN (
            SELECT group_chat_id FROM group_chat_members WHERE user_id = p_user_id
        );
        
        -- Add unread personal messages
        SELECT v_total_unread + COUNT(*) INTO v_total_unread
        FROM messages
        WHERE recipient_id = p_user_id AND message_read = 0;
        
        SELECT v_total_unread as total_unread;
    END IF;
END$$

DELIMITER ;