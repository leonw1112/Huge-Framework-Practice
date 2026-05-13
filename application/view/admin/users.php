<div class="container">
    <h1>Benutzerverwaltung (Admin)</h1>
    <p>Hier kannst du die Gruppenzugehörigkeit aller Benutzer ändern.</p>

    <?php $this->renderFeedbackMessages(); ?>

    <table id="adminUserTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Avatar</th>
                <th>Username</th>
                <th>E-Mail</th>
                <th>Status</th>
                <th>Gruppe</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->users as $user): ?>
            <tr>
                <td><?= $user->user_id ?></td>
                <td>
                    <img src="<?= $user->user_avatar_link ?>" style="width:32px;height:32px;border-radius:50%;" alt="Avatar">
                </td>
                <td><?= $user->user_name ?></td>
                <td><?= $user->user_email ?></td>
                <td>
                    <?php if ($user->user_active == 1 && $user->user_deleted == 0): ?>
                        <span style="color:green;">Aktiv</span>
                    <?php elseif ($user->user_deleted == 1): ?>
                        <span style="color:red;">Gelöscht</span>
                    <?php else: ?>
                        <span style="color:orange;">Inaktiv</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="<?= Config::get('URL') ?>admin/updateUserGroup_action" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                        <input type="hidden" name="user_id" value="<?= $user->user_id ?>" />
                        
                        <select name="group_id" onchange="this.form.submit()" 
                                <?= ($user->user_id == Session::get('user_id')) ? 'disabled title="Eigene Gruppe nicht änderbar"' : '' ?>>
                            <?php foreach ($this->groups as $group): ?>
                                <option value="<?= $group->group_id ?>" 
                                    <?= ($user->user_account_type == $group->group_id) ? 'selected' : '' ?>>
                                    <?= $group->group_name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td>
                    <?php if ($user->user_id == Session::get('user_id')): ?>
                        <em>(Du)</em>
                    <?php else: ?>
                        <a href="<?= Config::get('URL') ?>profile/showProfile/<?= $user->user_id ?>">Profil</a>
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
    $('#adminUserTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json'
        },
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [1, 6] }
        ]
    });
});
</script>