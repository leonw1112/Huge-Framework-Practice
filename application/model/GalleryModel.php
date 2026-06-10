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
