<div class="container">
    <link rel="stylesheet" href="<?php echo Config::get('URL'); ?>css/style.css">
    <h1>Galerie</h1>
    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <h3>Meine Galerie</h3>
        <p>
            <a href="<?php echo Config::get('URL');?>gallery/upload">Neues Bild hochladen</a>
        </p>

        <div class="gallery-grid">
            <?php if (!empty($this->photos)) { ?>
                <?php foreach ($this->photos as $photo) { ?>
                    <div class="gallery-item">
                        <img src="<?php echo Config::get('URL'); ?>uploads/<?php echo htmlspecialchars($photo->filename); ?>" alt="<?php echo htmlspecialchars($photo->title); ?>" style="max-width: 350px; max-height: 350px;">
                        <p style="font-size: 12px;"><?php echo htmlspecialchars($photo->title); ?></p>
                        <a href="<?php echo Config::get('URL'); ?>gallery/delete/<?php echo $photo->gallery_id; ?>" class="btn-delete" onclick="return confirm('Bild wirklich löschen?');" style="font-size: 12px;">Löschen</a>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>Keine Bilder in der Galerie vorhanden.</p>
            <?php } ?>
        </div>

    </div>
</div>
