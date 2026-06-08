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
            <?php if (!empty($this->photos)) { ?>
                <?php foreach ($this->photos as $photo) { ?>
                    <div class="gallery-item">
                        <img src="<?php echo Config::get('URL'); ?>uploads/<?php echo htmlspecialchars($photo->filename); ?>" alt="<?php echo htmlspecialchars($photo->title); ?>">
                        <p><?php echo htmlspecialchars($photo->title); ?></p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>Keine Bilder in der Galerie vorhanden.</p>
            <?php } ?>
        </div>

    </div>
</div>
