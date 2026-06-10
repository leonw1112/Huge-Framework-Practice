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
     * Show all gallery images (own + shared)
     */
    public function index()
    {
        $user_id = Session::get('user_id');
        $this->View->photos = GalleryModel::getAllImages($user_id);
        $this->View->sharedPhotos = GalleryModel::getSharedImages($user_id);
        $this->View->render('gallery/index');
    }

    /**
     * Serve an image securely - owner or shared users can access it
     * @param string $filename filename of the image to serve
     */
    public function serve($filename)
    {
        $user_id = Session::get('user_id');
        
        // Verify ownership or shared access
        $image = GalleryModel::getImageIfAccessible($filename, $user_id);
        
        if (!$image) {
            header('HTTP/1.0 403 Forbidden');
            exit('Zugriff verweigert.');
        }
        
        $file_path = __DIR__ . '/../../uploads/' . $filename;
        
        if (!file_exists($file_path)) {
            header('HTTP/1.0 404 Not Found');
            exit('Datei nicht gefunden.');
        }
        
        // Get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        // Send headers to prevent caching and embedding
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file_path));
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Output file and exit
        readfile($file_path);
        exit;
    }

    /**
     * Download an image - only the owner can download
     * @param string $filename filename of the image to download
     */
    public function download($filename)
    {
        $user_id = Session::get('user_id');
        
        // Verify ownership strictly (only owner can download, not shared users)
        $image = GalleryModel::getImageByFilename($filename);
        
        if (!$image || $image->user_id != $user_id) {
            header('HTTP/1.0 403 Forbidden');
            exit('Zugriff verweigert.');
        }
        
        $file_path = __DIR__ . '/../../uploads/' . $filename;
        
        if (!file_exists($file_path)) {
            header('HTTP/1.0 404 Not Found');
            exit('Datei nicht gefunden.');
        }
        
        // Get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        // Send download headers
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file_path));
        header('Content-Disposition: attachment; filename="' . $image->title . '.' . pathinfo($filename, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Output file and exit
        readfile($file_path);
        exit;
    }

    /**
     * Share an image with another user
     * @param int $image_id id of the image to share
     */
    public function share($image_id)
    {
        $user_id = Session::get('user_id');
        
        // Verify ownership
        $image = GalleryModel::getImage($image_id);
        if (!$image || $image->user_id != $user_id) {
            Session::add('feedback_negative', 'Du kannst nur eigene Bilder teilen.');
            Redirect::to('gallery/index');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $share_with = trim($_POST['share_with_user_id']);
            
            if (empty($share_with) || !is_numeric($share_with)) {
                Session::add('feedback_negative', 'Bitte eine gültige User-ID eingeben.');
                Redirect::to('gallery/share/' . $image_id);
            }
            
            $share_with_user_id = (int) $share_with;
            
            // Cannot share with yourself
            if ($share_with_user_id == $user_id) {
                Session::add('feedback_negative', 'Du kannst ein Bild nicht mit dir selbst teilen.');
                Redirect::to('gallery/share/' . $image_id);
            }
            
            // Check if user exists
            $target_user = UserModel::getPublicProfileOfUser($share_with_user_id);
            if (!$target_user) {
                Session::add('feedback_negative', 'Der angegebene Benutzer existiert nicht.');
                Redirect::to('gallery/share/' . $image_id);
            }
            
            if (GalleryModel::shareImage($image_id, $user_id, $share_with_user_id)) {
                Session::add('feedback_positive', 'Bild erfolgreich mit ' . htmlspecialchars($target_user->user_name) . ' geteilt!');
            } else {
                Session::add('feedback_negative', 'Fehler beim Teilen des Bildes.');
            }
            
            Redirect::to('gallery/index');
        }
        
        $this->View->image = $image;
        $this->View->sharedUsers = GalleryModel::getSharedUsers($image_id, $user_id);
        $this->View->render('gallery/share');
    }

    /**
     * Unshare an image from a user
     * @param int $image_id id of the image
     * @param int $shared_with_user_id id of the user to unshare from
     */
    public function unshare($image_id, $shared_with_user_id)
    {
        $user_id = Session::get('user_id');
        
        // Verify ownership
        $image = GalleryModel::getImage($image_id);
        if (!$image || $image->user_id != $user_id) {
            Session::add('feedback_negative', 'Zugriff verweigert.');
            Redirect::to('gallery/index');
        }
        
        if (GalleryModel::unshareImage($image_id, $user_id, $shared_with_user_id)) {
            Session::add('feedback_positive', 'Freigabe erfolgreich entfernt.');
        } else {
            Session::add('feedback_negative', 'Fehler beim Entfernen der Freigabe.');
        }
        
        Redirect::to('gallery/share/' . $image_id);
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
            $upload_path = __DIR__ . '/../../uploads/';
            
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
