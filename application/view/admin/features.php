<div class="container">
    <h1>Feature-Schutz verwalten (Admin)</h1>
    <p>Hier kannst du einzelne Features als "geschützt" markieren. Geschützte Features erfordern ein temporäres Recht.</p>

    <?php $this->renderFeedbackMessages(); ?>

    <table class="overview-table">
        <thead>
            <tr>
                <th>Feature-Key</th>
                <th>Bezeichnung</th>
                <th>Status</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->features as $key => $feature): ?>
            <tr>
                <td><code><?= htmlspecialchars($key) ?></code></td>
                <td><?= htmlspecialchars($feature['label']) ?></td>
                <td>
                    <?php if ($feature['protected']): ?>
                        <span style="color:red; font-weight:bold;">Geschützt</span>
                        <small>(erfordert temporäres Recht)</small>
                    <?php else: ?>
                        <span style="color:green;">Offen</span>
                        <small>(für alle eingeloggten User)</small>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="<?= Config::get('URL') ?>admin/toggleFeatureProtection_action" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                        <input type="hidden" name="feature_key" value="<?= htmlspecialchars($key) ?>" />
                        <input type="hidden" name="protected" value="<?= $feature['protected'] ? '0' : '1' ?>" />
                        <button type="submit" class="btn btn-<?= $feature['protected'] ? 'success' : 'warning' ?>">
                            <?= $feature['protected'] ? 'Öffnen' : 'Schützen' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
