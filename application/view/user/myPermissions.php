<div class="container">
    <h1>Meine temporären Rechte</h1>
    <p>Übersicht aller aktiven, zeitlich begrenzten Feature-Rechte.</p>

    <?php $this->renderFeedbackMessages(); ?>

    <?php if (empty($this->permissions)): ?>
        <p><em>Du hast aktuell keine temporären Rechte.</em></p>
    <?php else: ?>
        <table class="overview-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Erteilt am</th>
                    <th>Läuft ab am</th>
                    <th>Verbleibend</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->permissions as $perm): ?>
                <tr>
                    <td><?= htmlspecialchars(FeatureRegistry::getLabel($perm->feature_key) ?? $perm->feature_key) ?></td>
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
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
