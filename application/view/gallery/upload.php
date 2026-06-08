<div class="container">
    <h1>Bild hochladen</h1>
    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <h3>Neues Bild hochladen</h3>
        <form method="post" action="<?php echo Config::get('URL');?>gallery/upload" enctype="multipart/form-data">
            <label>Bild auswählen: </label>
            <input type="file" name="image" accept="image/*" required />
            
            <label>Beschreibung: </label>
            <textarea name="description" placeholder="Beschreibung des Bildes (optional)"></textarea>
            
            <input type="submit" value="Hochladen" autocomplete="off" />
            <a href="<?php echo Config::get('URL');?>gallery/index">Zurück zur Galerie</a>
        </form>

    </div>
</div>
