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
    public static function getAllImages()
    {
        // TODO: Implement database query to fetch all images for current user
        return array();
    }

    /**
     * Get a single image by ID
     * @param int $image_id id of the image
     * @return object a single image object
     */
    public static function getImage($image_id)
    {
        // TODO: Implement database query to fetch a single image
        return null;
    }

    /**
     * Upload a new image
     * @param string $filename name of the uploaded file
     * @param string $description description of the image
     * @return bool success status
     */
    public static function uploadImage($filename, $description)
    {
        // TODO: Implement image upload logic
        return false;
    }

    /**
     * Delete an image
     * @param int $image_id id of the image to delete
     * @return bool success status
     */
    public static function deleteImage($image_id)
    {
        // TODO: Implement image deletion logic
        return false;
    }
}
