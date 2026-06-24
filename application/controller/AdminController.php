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

    /**
     * Admin-Ansicht: Übersicht aller temporären Rechte
     */
    public function permissions()
    {
        Auth::checkAdminAuthentication();

        $this->View->render('admin/permissions', array(
            'permissions' => TemporaryPermissionModel::getAllPermissions(),
            'users' => UserModel::getPublicProfilesOfAllUsers(),
            'features' => FeatureRegistry::getAllFeatures()
        ));
    }

    /**
     * Admin-Aktion: Temporäres Recht für einen User vergeben
     */
    public function grantPermission_action()
    {
        Auth::checkAdminAuthentication();

        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        if (Request::post('user_id') && Request::post('feature_key') && Request::post('duration_hours')) {
            $duration = (int) Request::post('duration_hours') * 3600; // Stunden in Sekunden
            $permission_id = TemporaryPermissionModel::grantPermission(
                Request::post('user_id'),
                Request::post('feature_key'),
                $duration,
                Session::get('user_id')
            );

            if ($permission_id) {
                Session::add('feedback_positive', Text::get('FEEDBACK_TEMPORARY_PERMISSION_GRANTED'));
            } else {
                Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            }
        }

        Redirect::to('admin/permissions');
    }

    /**
     * Admin-Aktion: Temporäres Recht entziehen (soft-revoke)
     */
    public function revokePermission_action()
    {
        Auth::checkAdminAuthentication();

        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        if (Request::post('permission_id')) {
            $success = TemporaryPermissionModel::revokePermission(Request::post('permission_id'));

            if ($success) {
                Session::add('feedback_positive', Text::get('FEEDBACK_TEMPORARY_PERMISSION_REVOKED'));
            } else {
                Session::add('feedback_negative', Text::get('FEEDBACK_TEMPORARY_PERMISSION_NOT_FOUND'));
            }
        }

        Redirect::to('admin/permissions');
    }

    /**
     * Admin-Ansicht: Feature-Registry Übersicht
     */
    public function features()
    {
        Auth::checkAdminAuthentication();

        $this->View->render('admin/features', array(
            'features' => FeatureRegistry::getAllFeatures()
        ));
    }

    /**
     * Admin-Aktion: Feature-Schutz toggeln
     */
    public function toggleFeatureProtection_action()
    {
        Auth::checkAdminAuthentication();

        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        if (Request::post('feature_key') && Request::post('protected') !== null) {
            FeatureRegistry::setProtected(
                Request::post('feature_key'),
                Request::post('protected') == '1'
            );
            Session::add('feedback_positive', Text::get('FEEDBACK_FEATURE_PROTECTION_UPDATED'));
        }

        Redirect::to('admin/features');
    }

}