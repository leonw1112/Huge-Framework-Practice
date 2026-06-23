<div class="container">
    <h1>Temporäre Rechte verwalten (Admin)</h1>
    <p>Hier kannst du temporäre Feature-Rechte an Benutzer vergeben und verwalten.</p>

    <?php $this->renderFeedbackMessages(); ?>

    <h2>Neues Recht vergeben</h2>
    <form method="post" action="<?= Config::get('URL') ?>admin/grantPermission_action" class="form-inline" style="margin-bottom: 30px;">
        <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />

        <label for="user_id">Benutzer:</label>
        <select name="user_id" id="user_id" required>
            <option value="">-- Benutzer wählen --</option>
            <?php foreach ($this->users as $user): ?>
                <option value="<?= $user->user_id ?>">
                    <?= htmlspecialchars($user->user_name) ?> (ID: <?= $user->user_id ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="feature_key">Feature:</label>
        <select name="feature_key" id="feature_key" required>
            <option value="">-- Feature wählen --</option>
            <?php foreach ($this->features as $key => $feature): ?>
                <option value="<?= $key ?>">
                    <?= htmlspecialchars($feature['label']) ?> (<?= $key ?>)
                    <?= $feature['protected'] ? '— geschützt' : '— offen' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="duration_hours">Dauer (Stunden):</label>
        <input type="number" name="duration_hours" id="duration_hours" min="1" value="24" required style="width:80px;" />

        <button type="submit" class="btn btn-primary">Recht erteilen</button>
    </form>

    <h2>Alle temporären Rechte</h2>
    <table id="permissionsTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Benutzer</th>
                <th>Feature</th>
                <th>Erteilt am</th>
                <th>Läuft ab am</th>
                <th>Verbleibend</th>
                <th>Status</th>
                <th>Erteilt von</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->permissions as $perm): ?>
            <tr>
                <td><?= $perm->permission_id ?></td>
                <td><?= htmlspecialchars($perm->user_name ?? '—') ?></td>
                <td><?= htmlspecialchars($perm->feature_key) ?></td>
                <td><?= date('d.m.Y H:i', $perm->granted_at) ?></td>
                <td><?= date('d.m.Y H:i', $perm->expires_at) ?></td>
                <td>
                    <?php
                    $remaining = $perm->expires_at - time();
                    if ($remaining > 0) {
                        $hours = floor($remaining / 3600);
                        $mins = floor(($remaining % 3600) / 60);
                        echo $hours . 'h ' . $mins . 'm';
                    } else {
                        echo '<span style="color:red;">Abgelaufen</span>';
                    }
                    ?>
                </td>
                <td>
                    <?php if ($perm->is_active == 1 && $perm->expires_at > time()): ?>
                        <span style="color:green;">Aktiv</span>
                    <?php elseif ($perm->is_active == 0): ?>
                        <span style="color:gray;">Entzogen</span>
                    <?php else: ?>
                        <span style="color:red;">Abgelaufen</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($perm->granted_by_name ?? 'System') ?></td>
                <td>
                    <?php if ($perm->is_active == 1 && $perm->expires_at > time()): ?>
                        <form method="post" action="<?= Config::get('URL') ?>admin/revokePermission_action" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                            <input type="hidden" name="permission_id" value="<?= $perm->permission_id ?>" />
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Recht wirklich entziehen?');">Entziehen</button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- jQuery & DataTables CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#permissionsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json'
        },
        pageLength: 25,
        order: [[3, 'desc']]
    });
});
</script>
