<div class="container">
    <h1>ProfileController/index</h1>
    
    <!-- Navigation Tabs -->
    <div class="tabs-navigation">
        <button class="tab-btn active" onclick="switchTab('users-tab', this)">👥 Benutzer</button>
        <button class="tab-btn" onclick="switchTab('groupchats-tab', this)">👨‍👩‍👧‍👦 Gruppenchats</button>
    </div>

    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <!-- Users Tab -->
        <div id="users-tab" class="tab-content active">
            <h3>What happens here ?</h3>
            <div>
                This controller/action/view shows a list of all users in the system. You could use the underlying code to
                build things that use profile information of one or multiple/all users.
            </div>
            <div>
                <table class="overview-table">
                    <thead>
                    <tr>
                        <td>Id</td>
                        <td>Avatar</td>
                        <td>Username</td>
                        <td>User's email</td>
                        <td>Activated ?</td>
                        <td>Link to user's profile</td>
                        <td>Chat with user</td>
                    </tr>
                    </thead>
                    <?php foreach ($this->users as $user) { ?>
                        <tr class="<?= ($user->user_active == 0 ? 'inactive' : 'active'); ?>">
                            <td><?= $user->user_id; ?></td>
                            <td class="avatar">
                                <?php if (isset($user->user_avatar_link)) { ?>
                                    <img src="<?= $user->user_avatar_link; ?>" />
                                <?php } ?>
                            </td>
                            <td><?= $user->user_name; ?></td>
                            <td><?= $user->user_email; ?></td>
                            <td><?= ($user->user_active == 0 ? 'No' : 'Yes'); ?></td>
                            <td>
                                <a href="<?= Config::get('URL') . 'profile/showProfile/' . $user->user_id; ?>">Profile</a>
                            </td>
                            <td>
                                <?php if (Session::userIsLoggedIn()) { ?>
                                    <a href="<?= Config::get('URL') . 'chat/index/' . $user->user_id; ?>">Chat</a>
                                <?php } else { ?>
                                    <a href="<?= Config::get('URL'); ?>login/index">Login to chat</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>

        <!-- Groupchats Tab -->
        <div id="groupchats-tab" class="tab-content">
            <h3>Meine Gruppenchats</h3>
            <?php if (Session::userIsLoggedIn()) { ?>
                <div class="groupchats-actions">
                    <a href="<?= Config::get('URL') . 'chat/groupChats'; ?>" class="btn-primary">💬 Zu meinen Gruppenchats</a>
                </div>
                <p>Verwalte deine Gruppenchats und chatte mit mehreren Personen gleichzeitig!</p>
            <?php } else { ?>
                <div class="login-message">
                    <p>Bitte <a href="<?= Config::get('URL'); ?>login/index">einloggen</a>, um auf Gruppenchats zuzugreifen.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<style>
.tabs-navigation {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.tab-btn {
    padding: 12px 20px;
    background: none;
    border: none;
    font-size: 15px;
    font-weight: bold;
    color: #666;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.tab-btn:hover {
    color: #007bff;
}

.tab-btn.active {
    color: #007bff;
    border-bottom-color: #007bff;
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s ease-in;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.groupchats-actions {
    margin: 20px 0;
}

.btn-primary {
    display: inline-block;
    padding: 12px 24px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #0056b3;
}

.login-message {
    padding: 20px;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 4px;
    color: #856404;
}

.login-message a {
    color: #007bff;
    font-weight: bold;
    text-decoration: none;
}

.login-message a:hover {
    text-decoration: underline;
}
</style>

<script>
function switchTab(tabId, btn) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab and activate button
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}
</script>
