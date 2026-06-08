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
        $this->View->render('gallery/index');
    }

    /**
     * Upload a new image to the gallery
     */
    public function upload()
    {
        // TODO: Implement image upload logic
        $this->View->render('gallery/upload');
    }
}
