<div class="container">
    <h1>Bild teilen</h1>
    <div class="box">
        <?php $this->renderFeedbackMessages(); ?>

        <h3>Bild: <?php echo htmlspecialchars($this->image->title); ?></h3>
        <img src="<?php echo Config::get('URL'); ?>gallery/serve/<?php echo htmlspecialchars($this->image->filename); ?>" alt="<?php echo htmlspecialchars($this->image->title); ?>" style="max-width: 300px; max-height: 300px; margin-bottom: 20px;">

        <h4>Mit User teilen</h4>
        <form method="post" action="<?php echo Config::get('URL'); ?>gallery/share/<?php echo $this->image->gallery_id; ?>">
            <label>User-ID des Empfängers:</label>
            <input type="number" name="share_with_user_id" min="1" required placeholder="z.B. 5" />
            <input type="submit" value="Teilen" />
        </form>

        <?php if (!empty($this->sharedUsers)) { ?>
            <h4>Bereits geteilt mit:</h4>
            <ul>
                <?php foreach ($this->sharedUsers as $user) { ?>
                    <li>
                        <?php echo htmlspecialchars($user->user_name); ?> (ID: <?php echo $user->user_id; ?>)
                        <a href="<?php echo Config::get('URL'); ?>gallery/unshare/<?php echo $this->image->gallery_id; ?>/<?php echo $user->user_id; ?>" onclick="return confirm('Freigabe wirklich entfernen?');">[Entfernen]</a>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>Dieses Bild ist noch mit niemandem geteilt.</p>
        <?php } ?>

        <p><a href="<?php echo Config::get('URL'); ?>gallery/index">Zurück zur Galerie</a></p>
    </div>
</div>
