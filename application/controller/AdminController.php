<?php

class AdminController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();

        // special authentication check for the entire controller: Note the check-ADMIN-authentication!
        // All methods inside this controller are only accessible for admins (= users that have role type 7)
        Auth::checkAdminAuthentication();
    }

    /**
     * This method controls what happens when you move to /admin or /admin/index in your app.
     */
    public function index()
    {
        $this->View->render('admin/index', array(
                'users' => UserModel::getPublicProfilesOfAllUsers())
        );
    }

    public function actionAccountSettings()
    {
        AdminModel::setAccountSuspensionAndDeletionStatus(
            Request::post('suspension'), Request::post('softDelete'), Request::post('user_id')
        );

        Redirect::to("admin");
    }

     /**
     * Admin-Ansicht: Alle Benutzer mit Gruppenauswahl anzeigen
     */
    public function users()
    {
        Auth::checkAdminAuthentication();

        $this->View->render('admin/users', array(
            'users' => UserModel::getAllUsersWithGroup(),
            'groups' => UserGroupModel::getAllGroups()
        ));
    }

    /**
     * Admin-Aktion: Benutzergruppe ändern
     */
    public function updateUserGroup_action()
    {
        Auth::checkAdminAuthentication();

        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        if (Request::post('user_id') && Request::post('group_id')) {
            UserModel::updateUserGroup(
                Request::post('user_id'),
                Request::post('group_id')
            );
        }

        Redirect::to('admin/users');
    }
}
