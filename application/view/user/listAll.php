<div class="container">
    <h1>Alle Benutzer</h1>
    <p>Übersicht aller registrierten Benutzer und ihrer Gruppen.</p>

    <table id="publicUserTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Avatar</th>
                <th>Username</th>
                <th>Gruppe</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->users as $user): ?>
            <tr>
                <td><?= $user->user_id ?></td>
                <td>
                    <img src="<?= $user->user_avatar_link ?>" style="width:32px;height:32px;border-radius:50%;" alt="Avatar">
                </td>
                <td>
                    <a href="<?= Config::get('URL') ?>profile/showProfile/<?= $user->user_id ?>">
                        <?= $user->user_name ?>
                    </a>
                </td>
                <td><?= $user->group_name ?></td>
                <td>
                    <?php if ($user->user_active == 1 && $user->user_deleted == 0): ?>
                        <span style="color:green;">Aktiv</span>
                    <?php elseif ($user->user_deleted == 1): ?>
                        <span style="color:red;">Gelöscht</span>
                    <?php else: ?>
                        <span style="color:orange;">Inaktiv</span>
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
    $('#publicUserTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json'
        },
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [1] }
        ]
    });
});
</script>