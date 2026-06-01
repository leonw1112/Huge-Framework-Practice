<?php

class ProfileController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This method controls what happens when you move to /overview/index in your app.
     * Shows a list of all users.
     */
    public function index()
    {
        $unread_counts = array();
        if (Session::userIsLoggedIn()) {
            $unread_counts = ChatModel::getUnreadCountPerContact(Session::get('user_id'));
        }

        $this->View->render('profile/index', array(
            'users' => UserModel::getPublicProfilesOfAllUsers(),
            'unread_counts' => $unread_counts
        ));
    }

    /**
     * This method controls what happens when you move to /overview/showProfile in your app.
     * Shows the (public) details of the selected user.
     * @param $user_id int id the the user
     */
    public function showProfile($user_id)
    {
        if (isset($user_id)) {
            $this->View->render('profile/showProfile', array(
                'user' => UserModel::getPublicProfileOfUser($user_id))
            );
        } else {
            Redirect::home();
        }
    }
}
