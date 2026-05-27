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

        $sql = "SELECT message_id, sender_id, recipient_id, message_text, message_timestamp 
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
}
