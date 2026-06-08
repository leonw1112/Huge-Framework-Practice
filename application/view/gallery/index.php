<div class="container">
    <h1>Galerie</h1>
    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <h3>Meine Galerie</h3>
        <p>
            <a href="<?php echo Config::get('URL');?>gallery/upload">Neues Bild hochladen</a>
        </p>

        <!-- TODO: Display gallery images here -->
        <div class="gallery-grid">
            <!-- Gallery images will be displayed here -->
             
        </div>

    </div>
</div>
