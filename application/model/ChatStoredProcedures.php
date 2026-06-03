<?php

/**
 * ChatStoredProcedures
 * Wrapper class for calling stored procedures for the messenger system.
 * This class provides methods to call the stored procedures defined in
 * 10-create-stored-procedures-messenger.sql
 */
class ChatStoredProcedures
{
    /**
     * Send a personal message from one user to another using stored procedure
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
        
        try {
            $sql = "CALL sp_send_message(:sender_id, :recipient_id, :message_text)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':sender_id' => $sender_id,
                ':recipient_id' => $recipient_id,
                ':message_text' => $message_text
            ));
            
            $result = $query->fetch();
            return $result && $result->success == 1;
        } catch (Exception $e) {
            error_log('Error in sp_send_message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all messages between two users using stored procedure
     * @param int $user_id_1 id of the first user
     * @param int $user_id_2 id of the second user
     * @return array array of message objects
     */
    public static function getMessagesBetweenUsers($user_id_1, $user_id_2)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        
        try {
            $sql = "CALL sp_get_messages_between_users(:user_id_1, :user_id_2)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':user_id_1' => $user_id_1,
                ':user_id_2' => $user_id_2
            ));
            
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('Error in sp_get_messages_between_users: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get unread message count between two users using stored procedure
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
        
        try {
            $sql = "CALL sp_get_unread_message_count(:sender_id, :recipient_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':sender_id' => $sender_id,
                ':recipient_id' => $recipient_id
            ));
            
            $result = $query->fetch();
            return $result ? $result->unread_count : 0;
        } catch (Exception $e) {
            error_log('Error in sp_get_unread_message_count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mark messages as read between two users using stored procedure
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
        
        try {
            $sql = "CALL sp_mark_messages_read(:sender_id, :recipient_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':sender_id' => $sender_id,
                ':recipient_id' => $recipient_id
            ));
            
            $result = $query->fetch();
            return $result && $result->rows_updated > 0;
        } catch (Exception $e) {
            error_log('Error in sp_mark_messages_read: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread message count per contact for a user using stored procedure
     * @param int $user_id id of the user
     * @return array array with sender_id => unread_count
     */
    public static function getUnreadCountPerContact($user_id)
    {
        if (!$user_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        
        try {
            $sql = "CALL sp_get_unread_per_contact(:user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(':user_id' => $user_id));
            
            $results = $query->fetchAll();
            $unread_map = array();
            foreach ($results as $row) {
                $unread_map[$row->sender_id] = $row->unread_count;
            }
            return $unread_map;
        } catch (Exception $e) {
            error_log('Error in sp_get_unread_per_contact: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Create a new group chat using stored procedure
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
        
        try {
            $sql = "CALL sp_create_group_chat(:group_name, :group_description, :creator_id, @group_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_name' => $group_name,
                ':group_description' => $group_description,
                ':creator_id' => $creator_id
            ));
            
            // Get the output parameter
            $result = $database->query("SELECT @group_id as group_chat_id")->fetch();
            return $result ? $result->group_chat_id : false;
        } catch (Exception $e) {
            error_log('Error in sp_create_group_chat: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a member to a group chat using stored procedure
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
        
        try {
            $sql = "CALL sp_add_member_to_group(:group_chat_id, :user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_chat_id' => $group_chat_id,
                ':user_id' => $user_id
            ));
            
            $result = $query->fetch();
            return $result && $result->rows_affected == 1;
        } catch (Exception $e) {
            error_log('Error in sp_add_member_to_group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a member from a group chat using stored procedure
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
        
        try {
            $sql = "CALL sp_remove_member_from_group(:group_chat_id, :user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_chat_id' => $group_chat_id,
                ':user_id' => $user_id
            ));
            
            $result = $query->fetch();
            return $result && $result->rows_deleted == 1;
        } catch (Exception $e) {
            error_log('Error in sp_remove_member_from_group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a message to a group chat using stored procedure
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
        
        try {
            $sql = "CALL sp_send_group_message(:group_chat_id, :sender_id, :message_text)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_chat_id' => $group_chat_id,
                ':sender_id' => $sender_id,
                ':message_text' => $message_text
            ));
            
            $result = $query->fetch();
            return $result && $result->rows_affected == 1;
        } catch (Exception $e) {
            error_log('Error in sp_send_group_message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all messages from a group chat using stored procedure
     * @param int $group_chat_id id of the group chat
     * @return array array of message objects
     */
    public static function getGroupChatMessages($group_chat_id)
    {
        if (!$group_chat_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        
        try {
            $sql = "CALL sp_get_group_chat_messages(:group_chat_id)";
            $query = $database->prepare($sql);
            $query->execute(array(':group_chat_id' => $group_chat_id));
            
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('Error in sp_get_group_chat_messages: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get unread message count in a group chat for a user using stored procedure
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
        
        try {
            $sql = "CALL sp_get_group_unread_count(:group_chat_id, :user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_chat_id' => $group_chat_id,
                ':user_id' => $user_id
            ));
            
            $result = $query->fetch();
            return $result ? $result->unread_count : 0;
        } catch (Exception $e) {
            error_log('Error in sp_get_group_unread_count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mark all messages in a group chat as read for a user using stored procedure
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
        
        try {
            $sql = "CALL sp_mark_group_messages_read(:group_chat_id, :user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_chat_id' => $group_chat_id,
                ':user_id' => $user_id
            ));
            
            $result = $query->fetch();
            return $result && $result->rows_updated > 0;
        } catch (Exception $e) {
            error_log('Error in sp_mark_group_messages_read: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all groups a user is member of using stored procedure
     * @param int $user_id id of the user
     * @return array array of group chat objects
     */
    public static function getUserGroupChats($user_id)
    {
        if (!$user_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        
        try {
            $sql = "CALL sp_get_user_group_chats(:user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(':user_id' => $user_id));
            
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('Error in sp_get_user_group_chats: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get group chat details using stored procedure
     * @param int $group_chat_id id of the group chat
     * @return object group chat object or null
     */
    public static function getGroupChatDetails($group_chat_id)
    {
        if (!$group_chat_id) {
            return null;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        
        try {
            $sql = "CALL sp_get_group_chat_details(:group_chat_id)";
            $query = $database->prepare($sql);
            $query->execute(array(':group_chat_id' => $group_chat_id));
            
            return $query->fetch();
        } catch (Exception $e) {
            error_log('Error in sp_get_group_chat_details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all members of a group chat using stored procedure
     * @param int $group_chat_id id of the group chat
     * @return array array of user objects
     */
    public static function getGroupMembers($group_chat_id)
    {
        if (!$group_chat_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        
        try {
            $sql = "CALL sp_get_group_members(:group_chat_id)";
            $query = $database->prepare($sql);
            $query->execute(array(':group_chat_id' => $group_chat_id));
            
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('Error in sp_get_group_members: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Check if a user is member of a group chat using stored procedure
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
        
        try {
            $sql = "CALL sp_is_group_member(:group_chat_id, :user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_chat_id' => $group_chat_id,
                ':user_id' => $user_id
            ));
            
            $result = $query->fetch();
            return $result && $result->is_member == 1;
        } catch (Exception $e) {
            error_log('Error in sp_is_group_member: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread count per group for a user using stored procedure
     * @param int $user_id id of the user
     * @return array array with group_chat_id => unread_count
     */
    public static function getUnreadCountPerGroup($user_id)
    {
        if (!$user_id) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        
        try {
            $sql = "CALL sp_get_unread_per_group(:user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(':user_id' => $user_id));
            
            $results = $query->fetchAll();
            $unread_map = array();
            foreach ($results as $row) {
                $unread_map[$row->group_chat_id] = $row->unread_count;
            }
            return $unread_map;
        } catch (Exception $e) {
            error_log('Error in sp_get_unread_per_group: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get total unread messages for a user across all groups and personal chats
     * @param int $user_id id of the user
     * @return int total count of unread messages
     */
    public static function getTotalUnreadMessages($user_id)
    {
        if (!$user_id) {
            return 0;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        
        try {
            $sql = "CALL sp_get_total_unread_messages(:user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(':user_id' => $user_id));
            
            $result = $query->fetch();
            return $result ? $result->total_unread : 0;
        } catch (Exception $e) {
            error_log('Error in sp_get_total_unread_messages: ' . $e->getMessage());
            return 0;
        }
    }
}
