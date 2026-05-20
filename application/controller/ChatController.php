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

        // Pass the user information to the view
        $this->View->render('chat/index', array(
            'other_user' => $other_user,
            'messages' => array() // Placeholder for messages - will be filled with database data later
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

        // Save the message (placeholder - needs ChatModel implementation)
        // ChatModel::sendMessage(Session::get('user_id'), $recipient_id, $message_text);

        Redirect::to('chat/index/' . $recipient_id);
    }
}
