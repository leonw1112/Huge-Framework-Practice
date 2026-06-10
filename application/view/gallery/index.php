<div class="container">
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
                        <img class="gallery-thumb" src="<?php echo Config::get('URL'); ?>gallery/serve/<?php echo htmlspecialchars($photo->filename); ?>" alt="<?php echo htmlspecialchars($photo->title); ?>" data-full="<?php echo Config::get('URL'); ?>gallery/serve/<?php echo htmlspecialchars($photo->filename); ?>" data-title="<?php echo htmlspecialchars($photo->title); ?>">
                        <p><?php echo htmlspecialchars($photo->title); ?></p>
                        <div class="gallery-item-actions">
                            <a href="<?php echo Config::get('URL'); ?>gallery/download/<?php echo htmlspecialchars($photo->filename); ?>" class="btn-download">Download</a>
                            <a href="<?php echo Config::get('URL'); ?>gallery/share/<?php echo $photo->gallery_id; ?>" class="btn-share">Teilen</a>
                            <a href="<?php echo Config::get('URL'); ?>gallery/delete/<?php echo $photo->gallery_id; ?>" class="btn-delete" onclick="return confirm('Bild wirklich löschen?');">Löschen</a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>Keine Bilder in der Galerie vorhanden.</p>
            <?php } ?>
        </div>

        <?php if (!empty($this->sharedPhotos)) { ?>
            <h3 style="margin-top: 40px;">Mit mir geteilt</h3>
            <div class="gallery-grid">
                <?php foreach ($this->sharedPhotos as $photo) { ?>
                    <div class="gallery-item">
                        <img class="gallery-thumb" src="<?php echo Config::get('URL'); ?>gallery/serve/<?php echo htmlspecialchars($photo->filename); ?>" alt="<?php echo htmlspecialchars($photo->title); ?>" data-full="<?php echo Config::get('URL'); ?>gallery/serve/<?php echo htmlspecialchars($photo->filename); ?>" data-title="<?php echo htmlspecialchars($photo->title); ?>">
                        <p><?php echo htmlspecialchars($photo->title); ?></p>
                        <p style="font-size: 10px; color: #666;">Von: <?php echo htmlspecialchars($photo->owner_name); ?></p>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close">&times;</span>
    <img class="lightbox-img" id="lightbox-img" src="" alt="">
    <div class="lightbox-caption" id="lightbox-caption"></div>
</div>

<script>
(function() {
    var lightbox = document.getElementById('lightbox');
    var lightboxImg = document.getElementById('lightbox-img');
    var lightboxCaption = document.getElementById('lightbox-caption');
    var thumbs = document.querySelectorAll('.gallery-thumb');

    thumbs.forEach(function(thumb) {
        thumb.style.cursor = 'pointer';
        thumb.addEventListener('click', function(e) {
            e.preventDefault();
            lightboxImg.src = this.getAttribute('data-full');
            lightboxCaption.textContent = this.getAttribute('data-title');
            lightbox.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
            lightbox.style.display = 'none';
            lightboxImg.src = '';
            document.body.style.overflow = '';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.style.display === 'flex') {
            lightbox.style.display = 'none';
            lightboxImg.src = '';
            document.body.style.overflow = '';
        }
    });
})();
</script>
