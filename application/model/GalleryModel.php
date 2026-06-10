<?php

/**
 * GalleryModel
 * Model for managing gallery images
 */
class GalleryModel
{
    /**
     * Get all images from the user's gallery
     * @return array an array with all gallery images
     */
    public static function getAllImages($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT gallery_id, user_id, filename, title, created_at 
                                    FROM gallery 
                                    WHERE user_id = :user_id 
                                    ORDER BY created_at DESC");
        $query->execute(array(':user_id' => $user_id));
        return $query->fetchAll();
    }

    /**
     * Get a single image by ID
     * @param int $image_id id of the image
     * @return object a single image object
     */
    public static function getImage($image_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT gallery_id, user_id, filename, title, created_at 
                                    FROM gallery 
                                    WHERE gallery_id = :gallery_id");
        $query->execute(array(':gallery_id' => $image_id));
        return $query->fetch();
    }

    /**
     * Get a single image by filename
     * @param string $filename filename of the image
     * @return object a single image object
     */
    public static function getImageByFilename($filename)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT gallery_id, user_id, filename, title, created_at 
                                    FROM gallery 
                                    WHERE filename = :filename");
        $query->execute(array(':filename' => $filename));
        return $query->fetch();
    }

    /**
     * Get all images shared with a specific user
     * @param int $user_id the user who receives shared images
     * @return array an array with all shared gallery images
     */
    public static function getSharedImages($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT g.gallery_id, g.user_id, g.filename, g.title, g.created_at,
                                           u.user_name as owner_name
                                    FROM gallery g
                                    INNER JOIN gallery_shares gs ON g.gallery_id = gs.gallery_id
                                    INNER JOIN users u ON g.user_id = u.user_id
                                    WHERE gs.shared_with_user_id = :user_id
                                    ORDER BY gs.created_at DESC");
        $query->execute(array(':user_id' => $user_id));
        return $query->fetchAll();
    }

    /**
     * Check if a user has access to an image (owner or shared with)
     * @param string $filename filename of the image
     * @param int $user_id the user to check
     * @return object|false the image object if access is granted, false otherwise
     */
    public static function getImageIfAccessible($filename, $user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT g.gallery_id, g.user_id, g.filename, g.title, g.created_at
                                    FROM gallery g
                                    WHERE g.filename = :filename
                                      AND (g.user_id = :user_id
                                           OR EXISTS (SELECT 1 FROM gallery_shares gs
                                                      WHERE gs.gallery_id = g.gallery_id
                                                        AND gs.shared_with_user_id = :user_id))");
        $query->execute(array(':filename' => $filename, ':user_id' => $user_id));
        return $query->fetch();
    }

    /**
     * Share an image with another user
     * @param int $gallery_id id of the image
     * @param int $owner_user_id id of the image owner
     * @param int $shared_with_user_id id of the user to share with
     * @return bool success status
     */
    public static function shareImage($gallery_id, $owner_user_id, $shared_with_user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("INSERT INTO gallery_shares (gallery_id, owner_user_id, shared_with_user_id, created_at)
                                    VALUES (:gallery_id, :owner_user_id, :shared_with_user_id, :created_at)
                                    ON DUPLICATE KEY UPDATE created_at = :created_at");
        return $query->execute(array(
            ':gallery_id' => $gallery_id,
            ':owner_user_id' => $owner_user_id,
            ':shared_with_user_id' => $shared_with_user_id,
            ':created_at' => time()
        ));
    }

    /**
     * Unshare an image from a user
     * @param int $gallery_id id of the image
     * @param int $owner_user_id id of the image owner
     * @param int $shared_with_user_id id of the user to unshare from
     * @return bool success status
     */
    public static function unshareImage($gallery_id, $owner_user_id, $shared_with_user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("DELETE FROM gallery_shares
                                    WHERE gallery_id = :gallery_id
                                      AND owner_user_id = :owner_user_id
                                      AND shared_with_user_id = :shared_with_user_id");
        return $query->execute(array(
            ':gallery_id' => $gallery_id,
            ':owner_user_id' => $owner_user_id,
            ':shared_with_user_id' => $shared_with_user_id
        ));
    }

    /**
     * Get all users an image is shared with
     * @param int $gallery_id id of the image
     * @param int $owner_user_id id of the image owner
     * @return array an array with user_id and user_name of shared users
     */
    public static function getSharedUsers($gallery_id, $owner_user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT u.user_id, u.user_name
                                    FROM gallery_shares gs
                                    INNER JOIN users u ON gs.shared_with_user_id = u.user_id
                                    WHERE gs.gallery_id = :gallery_id
                                      AND gs.owner_user_id = :owner_user_id");
        $query->execute(array(':gallery_id' => $gallery_id, ':owner_user_id' => $owner_user_id));
        return $query->fetchAll();
    }

    /**
     * Upload a new image
     * @param int $user_id the user who uploads
     * @param string $filename name of the uploaded file
     * @param string $title title/description of the image
     * @return bool success status
     */
    public static function uploadImage($user_id, $filename, $title)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("INSERT INTO gallery (user_id, filename, title, created_at) 
                                    VALUES (:user_id, :filename, :title, :created_at)");
        return $query->execute(array(
            ':user_id' => $user_id,
            ':filename' => $filename,
            ':title' => $title,
            ':created_at' => time()
        ));
    }

    /**
     * Delete an image
     * @param int $image_id id of the image to delete
     * @param int $user_id user_id of the image owner (security check)
     * @return bool success status
     */
    public static function deleteImage($image_id, $user_id)
    {
        // First get the image to verify ownership and get filename
        $image = self::getImage($image_id);
        
        if (!$image || $image->user_id != $user_id) {
            return false;
        }

        // Delete the file from filesystem
        $file_path = __DIR__ . '/../../uploads/' . $image->filename;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("DELETE FROM gallery WHERE gallery_id = :gallery_id AND user_id = :user_id");
        return $query->execute(array(
            ':gallery_id' => $image_id,
            ':user_id' => $user_id
        ));
    }
}
