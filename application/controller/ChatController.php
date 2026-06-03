<?php

/**
 * The chat controller: Handles messaging between users.
 */
class ChatController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();

        // Only logged-in users can access chat
        Auth::checkAuthentication();
    }

    /**
     * Shows the chat window with a specific user.
     * @param $user_id int id of the user to chat with
     */
    public function index($user_id)
    {
        // Get the other user's information
        $other_user = UserModel::getPublicProfileOfUser($user_id);

        // Mark all messages from the other user as read using stored procedure
        ChatStoredProcedures::markMessagesAsRead($user_id, Session::get('user_id'));

        // Get all messages between the logged-in user and the other user using stored procedure
        $messages = ChatStoredProcedures::getMessagesBetweenUsers(Session::get('user_id'), $user_id);

        // Pass the user information and messages to the view
        $this->View->render('chat/index', array(
            'other_user' => $other_user,
            'messages' => $messages
        ));
    }

    /**
     * Sends a message to another user.
     * POST request.
     */
    public function sendMessage()
    {
        // Verify CSRF token
        if (!Csrf::isTokenValid(Request::post('csrf_token'))) {
            Redirect::to('chat/index/' . Request::post('recipient_id'));
        }

        $recipient_id = Request::post('recipient_id');
        $message_text = Request::post('message_text');

        // Validate inputs
        if (empty($message_text)) {
            Redirect::to('chat/index/' . $recipient_id);
        }

        // Save the message to the database using stored procedure
        ChatStoredProcedures::sendMessage(Session::get('user_id'), $recipient_id, $message_text);

        Redirect::to('chat/index/' . $recipient_id);
    }

    /**
     * Shows all group chats for the logged-in user
     */
    public function groupChats()
    {
        // Get all group chats and unread counts using stored procedures
        $group_chats = ChatStoredProcedures::getUserGroupChats(Session::get('user_id'));
        $unread_counts = ChatStoredProcedures::getUnreadCountPerGroup(Session::get('user_id'));

        $this->View->render('chat/groupChats', array(
            'group_chats' => $group_chats,
            'unread_counts' => $unread_counts
        ));
    }

    /**
     * Shows a specific group chat
     * @param $group_chat_id int id of the group chat
     */
    public function groupChat($group_chat_id)
    {
        // Check if user is member of this group using stored procedure
        if (!ChatStoredProcedures::isGroupMember($group_chat_id, Session::get('user_id'))) {
            Redirect::to('chat/groupChats');
        }

        // Mark all messages in this group as read for the current user using stored procedure
        ChatStoredProcedures::markGroupMessagesAsRead($group_chat_id, Session::get('user_id'));

        // Get group details, messages and members using stored procedures
        $group_chat = ChatStoredProcedures::getGroupChatDetails($group_chat_id);
        $messages = ChatStoredProcedures::getGroupChatMessages($group_chat_id);
        $members = ChatStoredProcedures::getGroupMembers($group_chat_id);

        $this->View->render('chat/groupChat', array(
            'group_chat' => $group_chat,
            'messages' => $messages,
            'members' => $members
        ));
    }

    /**
     * Creates a new group chat
     * POST request.
     */
    public function createGroupChat()
    {
        // Verify CSRF token
        if (!Csrf::isTokenValid(Request::post('csrf_token'))) {
            Redirect::to('chat/groupChats');
        }

        $group_name = Request::post('group_name');
        $group_description = Request::post('group_description');

        // Validate inputs
        if (empty($group_name)) {
            Redirect::to('chat/groupChats');
        }

        // Create the group using stored procedure (creator is added automatically)
        $group_id = ChatStoredProcedures::createGroupChat($group_name, $group_description, Session::get('user_id'));

        if ($group_id) {
            Redirect::to('chat/groupChat/' . $group_id);
        } else {
            Redirect::to('chat/groupChats');
        }
    }

    /**
     * Sends a message to a group chat
     * POST request.
     */
    public function sendGroupMessage()
    {
        // Verify CSRF token
        if (!Csrf::isTokenValid(Request::post('csrf_token'))) {
            $group_id = Request::post('group_chat_id');
            Redirect::to('chat/groupChat/' . $group_id);
        }

        $group_chat_id = Request::post('group_chat_id');
        $message_text = Request::post('message_text');

        // Check if user is member of this group using stored procedure
        if (!ChatStoredProcedures::isGroupMember($group_chat_id, Session::get('user_id'))) {
            Redirect::to('chat/groupChats');
        }

        // Validate inputs
        if (empty($message_text)) {
            Redirect::to('chat/groupChat/' . $group_chat_id);
        }

        // Save the message to the database using stored procedure
        ChatStoredProcedures::sendGroupMessage($group_chat_id, Session::get('user_id'), $message_text);

        Redirect::to('chat/groupChat/' . $group_chat_id);
    }

    /**
     * Adds a user to a group chat (creator only)
     * POST request.
     */
    public function addMember()
    {
        // Verify CSRF token
        if (!Csrf::isTokenValid(Request::post('csrf_token'))) {
            $group_id = Request::post('group_chat_id');
            Redirect::to('chat/groupChat/' . $group_id);
        }

        $group_chat_id = Request::post('group_chat_id');
        $user_id = Request::post('user_id');

        // Get group details using stored procedure
        $group_chat = ChatStoredProcedures::getGroupChatDetails($group_chat_id);

        // Check if user is creator
        if ($group_chat->created_by != Session::get('user_id')) {
            Redirect::to('chat/groupChat/' . $group_chat_id);
        }

        // Check if user is not already member using stored procedure
        if (!ChatStoredProcedures::isGroupMember($group_chat_id, $user_id)) {
            ChatStoredProcedures::addMemberToGroup($group_chat_id, $user_id);
        }

        Redirect::to('chat/groupChat/' . $group_chat_id);
    }

    /**
     * Removes a user from a group chat (creator only)
     * POST request.
     */
    public function removeMember()
    {
        // Verify CSRF token
        if (!Csrf::isTokenValid(Request::post('csrf_token'))) {
            $group_id = Request::post('group_chat_id');
            Redirect::to('chat/groupChat/' . $group_id);
        }

        $group_chat_id = Request::post('group_chat_id');
        $user_id_to_remove = Request::post('user_id_to_remove');

        // Get group details using stored procedure
        $group_chat = ChatStoredProcedures::getGroupChatDetails($group_chat_id);

        // Check if user is creator
        if ($group_chat->created_by != Session::get('user_id')) {
            Redirect::to('chat/groupChat/' . $group_chat_id);
        }

        // Remove member using stored procedure
        ChatStoredProcedures::removeMemberFromGroup($group_chat_id, $user_id_to_remove);

        Redirect::to('chat/groupChat/' . $group_chat_id);
    }

    /**
     * Leaves a group chat
     * POST request.
     */
    public function leaveGroup()
    {
        // Verify CSRF token
        if (!Csrf::isTokenValid(Request::post('csrf_token'))) {
            Redirect::to('chat/groupChats');
        }

        $group_chat_id = Request::post('group_chat_id');

        // Remove current user from group using stored procedure
        ChatStoredProcedures::removeMemberFromGroup($group_chat_id, Session::get('user_id'));

        Redirect::to('chat/groupChats');
    }
}
 