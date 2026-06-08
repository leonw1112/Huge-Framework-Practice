<?php

/**
 * GalleryController
 * This controller manages the gallery functionality
 */
class GalleryController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();
        Auth::checkAuthentication();
    }

    /**
     * Show all gallery images
     */
    public function index()
    {
        $this->View->photos = GalleryModel::getAllImages(Session::get('user_id'));
        $this->View->render('gallery/index');
    }

    /**
     * Upload a new image to the gallery
     */
    public function upload()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validate file upload
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                Session::add('feedback_negative', 'Fehler beim Datei-Upload.');
                Redirect::to('gallery/upload');
            }

            $file = $_FILES['image'];
            $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
            $max_size = 5 * 1024 * 1024; // 5MB

            // Validate file type
            if (!in_array($file['type'], $allowed_types)) {
                Session::add('feedback_negative', 'Ungültiger Dateityp. Erlaubt sind: JPG, PNG, GIF, WebP');
                Redirect::to('gallery/upload');
            }

            // Validate file size
            if ($file['size'] > $max_size) {
                Session::add('feedback_negative', 'Datei zu groß. Maximum 5MB erlaubt.');
                Redirect::to('gallery/upload');
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = md5(uniqid() . time()) . '.' . $extension;
            $upload_path = __DIR__ . '/../../public/uploads/';
            
            // Create uploads directory if not exists
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            // Get title/description
            $title = !empty($_POST['description']) ? trim($_POST['description']) : 'Untitled';

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path . $filename)) {
                // Save to database
                if (GalleryModel::uploadImage(Session::get('user_id'), $filename, $title)) {
                    Session::add('feedback_positive', 'Bild erfolgreich hochgeladen!');
                    Redirect::to('gallery/index');
                } else {
                    Session::add('feedback_negative', 'Fehler beim Speichern in der Datenbank.');
                    unlink($upload_path . $filename);
                    Redirect::to('gallery/upload');
                }
            } else {
                Session::add('feedback_negative', 'Fehler beim Verschieben der Datei.');
                Redirect::to('gallery/upload');
            }
        }

        $this->View->render('gallery/upload');
    }

    /**
     * Delete an image from the gallery
     * @param int $image_id id of the image to delete
     */
    public function delete($image_id)
    {
        $user_id = Session::get('user_id');

        if (GalleryModel::deleteImage($image_id, $user_id)) {
            Session::add('feedback_positive', 'Bild erfolgreich gelöscht!');
        } else {
            Session::add('feedback_negative', 'Fehler beim Löschen des Bildes.');
        }

        Redirect::to('gallery/index');
    }
}
