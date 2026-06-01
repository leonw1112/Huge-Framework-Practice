<?php

/**
 * ChatModel
 * Handles all message-related database operations between users.
 */
class ChatModel
{
    /**
     * Send a message from one user to another
     * @param int $sender_id id of the user sending the message
     * @param int $recipient_id id of the user receiving the message
     * @param string $message_text the content of the message
     * @return bool feedback (was the message saved properly ?)
     */
    public static function sendMessage($sender_id, $recipient_id, $message_text)
    {
        if (!$sender_id || !$recipient_id || !$message_text || strlen($message_text) == 0) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO messages (sender_id, recipient_id, message_text, message_timestamp) 
                VALUES (:sender_id, :recipient_id, :message_text, :message_timestamp)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender_id' => $sender_id,
            ':recipient_id' => $recipient_id,
            ':message_text' => $message_text,
            ':message_timestamp' => time()
        ));

        return ($query->rowCount() == 1);
    }

    /**
     * Get all messages between two users
     * @param int $user_id_1 id of the first user
     * @param int $user_id_2 id of the second user
     * @return array array of message objects
     */
    public static function getMessagesBetweenUsers($user_id_1, $user_id_2)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT message_id, sender_id, recipient_id, message_text, message_timestamp, message_read 
                FROM messages 
                WHERE (sender_id = :user_id_1 AND recipient_id = :user_id_2) 
                   OR (sender_id = :user_id_2 AND recipient_id = :user_id_1)
                ORDER BY message_timestamp ASC";
        
        $query = $database->prepare($sql);
        $query->execute(array(
            ':user_id_1' => $user_id_1,
            ':user_id_2' => $user_id_2
        ));

        return $query->fetchAll();
    }

    /**
     * Get unread message count between two users
     * @param int $sender_id id of the user who sent the message
     * @param int $recipient_id id of the user who received the message
     * @return int count of unread messages
     */
    public static function getUnreadMessagesCount($sender_id, $recipient_id)
    {
        if (!$sender_id || !$recipient_id) {
            return 0;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT COUNT(*) as unread_count
                FROM messages
                WHERE sender_id = :sender_id 
                AND recipient_id = :recipient_id 
                AND message_read = 0";
        
        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender_id' => $sender_id,
            ':recipient_id' => $recipient_id
        ));

        $result = $query->fetch();
        return $result ? $result->unread_count : 0;
    }

    /**
     * Mark messages as read between two users
     * @param int $sender_id id of the user who sent the message
     * @param int $recipient_id id of the user who received the message
     * @return bool feedback
     */
    public static function markMessagesAsRead($sender_id, $recipient_id)
    {
        if (!$sender_id || !$recipient_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE messages 
                SET message_read = 1 
                WHERE sender_id = :sender_id 
                AND recipient_id = :recipient_id 
                AND message_read = 0";
        
        $query = $database->prepare($sql);
        return $query->execute(array(
            ':sender_id' => $sender_id,
            ':recipient_id' => $recipient_id
        ));
    }

    /**
     * Get all unread message counts for a user from all contacts
     * @param int $user_id id of the user
     * @return array array with user_id => unread_count
     */
    public static function getUnreadCountPerContact($user_id)
    {
        if (!$user_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT sender_id, COUNT(*) as unread_count
                FROM messages
                WHERE recipient_id = :user_id 
                AND message_read = 0
                GROUP BY sender_id";
        
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $user_id));

        $results = $query->fetchAll();
        $unread_map = array();
        foreach ($results as $row) {
            $unread_map[$row->sender_id] = $row->unread_count;
        }
        return $unread_map;
    }

    /**
     * Create a new group chat
     * @param string $group_name name of the group chat
     * @param string $group_description description of the group chat
     * @param int $creator_id id of the user creating the group
     * @return int group_chat_id of the created group or false on failure
     */
    public static function createGroupChat($group_name, $group_description, $creator_id)
    {
        if (!$group_name || !$creator_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO group_chats (group_chat_name, group_chat_description, created_by, created_timestamp) 
                VALUES (:group_name, :group_description, :creator_id, :timestamp)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_name' => $group_name,
            ':group_description' => $group_description,
            ':creator_id' => $creator_id,
            ':timestamp' => time()
        ));

        if ($query->rowCount() == 1) {
            return $database->lastInsertId();
        }
        return false;
    }

    /**
     * Add a user to a group chat
     * @param int $group_chat_id id of the group chat
     * @param int $user_id id of the user to add
     * @return bool feedback (was the user added properly ?)
     */
    public static function addMemberToGroup($group_chat_id, $user_id)
    {
        if (!$group_chat_id || !$user_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO group_chat_members (group_chat_id, user_id, joined_timestamp) 
                VALUES (:group_chat_id, :user_id, :timestamp)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_chat_id' => $group_chat_id,
            ':user_id' => $user_id,
            ':timestamp' => time()
        ));

        return ($query->rowCount() == 1);
    }

    /**
     * Remove a user from a group chat
     * @param int $group_chat_id id of the group chat
     * @param int $user_id id of the user to remove
     * @return bool feedback (was the user removed properly ?)
     */
    public static function removeMemberFromGroup($group_chat_id, $user_id)
    {
        if (!$group_chat_id || !$user_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM group_chat_members WHERE group_chat_id = :group_chat_id AND user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_chat_id' => $group_chat_id,
            ':user_id' => $user_id
        ));

        return ($query->rowCount() == 1);
    }

    /**
     * Send a message to a group chat
     * @param int $group_chat_id id of the group chat
     * @param int $sender_id id of the user sending the message
     * @param string $message_text the content of the message
     * @return bool feedback (was the message saved properly ?)
     */
    public static function sendGroupMessage($group_chat_id, $sender_id, $message_text)
    {
        if (!$group_chat_id || !$sender_id || !$message_text || strlen($message_text) == 0) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO group_chat_messages (group_chat_id, sender_id, message_text, message_timestamp) 
                VALUES (:group_chat_id, :sender_id, :message_text, :timestamp)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_chat_id' => $group_chat_id,
            ':sender_id' => $sender_id,
            ':message_text' => $message_text,
            ':timestamp' => time()
        ));

        return ($query->rowCount() == 1);
    }

    /**
     * Get all messages from a group chat
     * @param int $group_chat_id id of the group chat
     * @return array array of message objects
     */
    public static function getGroupChatMessages($group_chat_id)
    {
        if (!$group_chat_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT gcm.message_id, gcm.group_chat_id, gcm.sender_id, u.user_name, gcm.message_text, gcm.message_timestamp 
                FROM group_chat_messages gcm
                JOIN users u ON gcm.sender_id = u.user_id
                WHERE gcm.group_chat_id = :group_chat_id
                ORDER BY gcm.message_timestamp ASC";
        
        $query = $database->prepare($sql);
        $query->execute(array(':group_chat_id' => $group_chat_id));

        return $query->fetchAll();
    }

    /**
     * Get all groups a user is member of
     * @param int $user_id id of the user
     * @return array array of group chat objects
     */
    public static function getUserGroupChats($user_id)
    {
        if (!$user_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT gc.group_chat_id, gc.group_chat_name, gc.group_chat_description, gc.created_by, gc.created_timestamp,
                COUNT(gcm.user_id) as member_count
                FROM group_chats gc
                JOIN group_chat_members gcm ON gc.group_chat_id = gcm.group_chat_id
                WHERE gcm.user_id = :user_id
                GROUP BY gc.group_chat_id
                ORDER BY gc.created_timestamp DESC";
        
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $user_id));

        return $query->fetchAll();
    }

    /**
     * Get group chat details
     * @param int $group_chat_id id of the group chat
     * @return object group chat object or null
     */
    public static function getGroupChatDetails($group_chat_id)
    {
        if (!$group_chat_id) {
            return null;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT gc.group_chat_id, gc.group_chat_name, gc.group_chat_description, gc.created_by, gc.created_timestamp,
                u.user_name as creator_name,
                COUNT(gcm.user_id) as member_count
                FROM group_chats gc
                LEFT JOIN users u ON gc.created_by = u.user_id
                LEFT JOIN group_chat_members gcm ON gc.group_chat_id = gcm.group_chat_id
                WHERE gc.group_chat_id = :group_chat_id
                GROUP BY gc.group_chat_id";
        
        $query = $database->prepare($sql);
        $query->execute(array(':group_chat_id' => $group_chat_id));

        return $query->fetch();
    }

    /**
     * Get all members of a group chat
     * @param int $group_chat_id id of the group chat
     * @return array array of user objects
     */
    public static function getGroupMembers($group_chat_id)
    {
        if (!$group_chat_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT u.user_id, u.user_name, u.user_email, u.user_has_avatar, gcm.joined_timestamp
                FROM group_chat_members gcm
                JOIN users u ON gcm.user_id = u.user_id
                WHERE gcm.group_chat_id = :group_chat_id
                ORDER BY gcm.joined_timestamp ASC";
        
        $query = $database->prepare($sql);
        $query->execute(array(':group_chat_id' => $group_chat_id));

        return $query->fetchAll();
    }

    /**
     * Check if a user is member of a group chat
     * @param int $group_chat_id id of the group chat
     * @param int $user_id id of the user
     * @return bool true if user is member, false otherwise
     */
    public static function isGroupMember($group_chat_id, $user_id)
    {
        if (!$group_chat_id || !$user_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT member_id FROM group_chat_members WHERE group_chat_id = :group_chat_id AND user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_chat_id' => $group_chat_id,
            ':user_id' => $user_id
        ));

        return ($query->rowCount() == 1);
    }

    /**
     * Get count of unread messages in a group chat for a user
     * @param int $group_chat_id id of the group chat
     * @param int $user_id id of the user
     * @return int count of unread messages
     */
    public static function getUnreadMessageCount($group_chat_id, $user_id)
    {
        if (!$group_chat_id || !$user_id) {
            return 0;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT COUNT(*) as unread_count
                FROM group_chat_messages
                WHERE group_chat_id = :group_chat_id 
                AND message_read = 0 
                AND sender_id != :user_id";
        
        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_chat_id' => $group_chat_id,
            ':user_id' => $user_id
        ));

        $result = $query->fetch();
        return $result ? $result->unread_count : 0;
    }

    /**
     * Mark all messages in a group chat as read for a user
     * @param int $group_chat_id id of the group chat
     * @param int $user_id id of the user
     * @return bool feedback
     */
    public static function markGroupMessagesAsRead($group_chat_id, $user_id)
    {
        if (!$group_chat_id || !$user_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE group_chat_messages 
                SET message_read = 1 
                WHERE group_chat_id = :group_chat_id 
                AND sender_id != :user_id 
                AND message_read = 0";
        
        $query = $database->prepare($sql);
        return $query->execute(array(
            ':group_chat_id' => $group_chat_id,
            ':user_id' => $user_id
        ));
    }

    /**
     * Get total unread messages for a user across all groups
     * @param int $user_id id of the user
     * @return int total count of unread messages
     */
    public static function getTotalUnreadMessages($user_id)
    {
        if (!$user_id) {
            return 0;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT COUNT(*) as total_unread
                FROM group_chat_messages gcm
                WHERE sender_id != :user_id 
                AND message_read = 0 
                AND gcm.group_chat_id IN (
                    SELECT group_chat_id FROM group_chat_members WHERE user_id = :user_id
                )";
        
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $user_id));

        $result = $query->fetch();
        return $result ? $result->total_unread : 0;
    }

    /**
     * Get unread count per group for a user
     * @param int $user_id id of the user
     * @return array array with group_chat_id => unread_count
     */
    public static function getUnreadCountPerGroup($user_id)
    {
        if (!$user_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT gcm.group_chat_id, COUNT(*) as unread_count
                FROM group_chat_messages gcm
                WHERE sender_id != :user_id 
                AND message_read = 0 
                AND gcm.group_chat_id IN (
                    SELECT group_chat_id FROM group_chat_members WHERE user_id = :user_id
                )
                GROUP BY gcm.group_chat_id";
        
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $user_id));

        $results = $query->fetchAll();
        $unread_map = array();
        foreach ($results as $row) {
            $unread_map[$row->group_chat_id] = $row->unread_count;
        }
        return $unread_map;
    }
}
