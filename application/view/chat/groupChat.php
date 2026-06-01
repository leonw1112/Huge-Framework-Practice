<div class="container">
    <h1>Gruppenchat: Messenger</h1>
    <div class="box">
        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <?php if ($this->group_chat) { ?>
            <div class="group-chat-container">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-group-info">
                        <div class="group-icon">
                            <span>👥</span>
                        </div>
                        <div class="chat-group-details">
                            <h2><?= htmlspecialchars($this->group_chat->group_chat_name); ?></h2>
                            <p class="member-info"><?= $this->group_chat->member_count; ?> Mitglieder</p>
                        </div>
                    </div>
                    <div class="chat-header-actions">
                        <button class="btn-icon" onclick="toggleMemberList()" title="Mitglieder anzeigen">👥</button>
                        <a href="<?= Config::get('URL') . 'chat/groupChats'; ?>" class="btn-icon" title="Zurück">←</a>
                    </div>
                </div>

                <!-- Members Sidebar -->
                <div class="chat-sidebar" id="memberSidebar">
                    <div class="sidebar-header">
                        <h3>Mitglieder (<?= $this->group_chat->member_count; ?>)</h3>
                        <button class="btn-close" onclick="toggleMemberList()">✕</button>
                    </div>
                    <div class="members-list">
                        <?php if (isset($this->members) && !empty($this->members)) { ?>
                            <?php foreach ($this->members as $member) { ?>
                                <div class="member-item">
                                    <div class="member-avatar">
                                        <?php if (isset($member->user_has_avatar) && $member->user_has_avatar) { ?>
                                            <img src="<?= Config::get('URL'); ?>public/avatars/<?= $member->user_id; ?>.jpg" alt="<?= $member->user_name; ?>" />
                                        <?php } else { ?>
                                            <div class="avatar-placeholder"><?= strtoupper(substr($member->user_name, 0, 1)); ?></div>
                                        <?php } ?>
                                    </div>
                                    <div class="member-info">
                                        <div class="member-name">
                                            <?= htmlspecialchars($member->user_name); ?>
                                            <?php if ($member->user_id == $this->group_chat->created_by) { ?>
                                                <span class="creator-badge">Creator</span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <?php if ($this->group_chat->created_by == Session::get('user_id') && $member->user_id != Session::get('user_id')) { ?>
                                        <form method="POST" action="<?= Config::get('URL') . 'chat/removeMember'; ?>" style="display:inline;" onsubmit="return confirm('Möchten Sie dieses Mitglied entfernen?');">
                                            <input type="hidden" name="group_chat_id" value="<?= $this->group_chat->group_chat_id; ?>" />
                                            <input type="hidden" name="user_id_to_remove" value="<?= $member->user_id; ?>" />
                                            <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                                            <button type="submit" class="btn-remove-member" title="Entfernen">✕</button>
                                        </form>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <!-- Add Member Section (Creator Only) -->
                    <?php if ($this->group_chat->created_by == Session::get('user_id')) { ?>
                        <div class="add-member-section">
                            <form method="POST" action="<?= Config::get('URL') . 'chat/addMember'; ?>" class="add-member-form">
                                <input type="hidden" name="group_chat_id" value="<?= $this->group_chat->group_chat_id; ?>" />
                                <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                                
                                <label for="user_id">Benutzer hinzufügen:</label>
                                <div class="input-wrapper">
                                    <input type="number" id="user_id" name="user_id" placeholder="Benutzer-ID" required />
                                    <button type="submit" class="btn-add">+</button>
                                </div>
                            </form>
                        </div>
                    <?php } ?>

                    <!-- Leave Group Section -->
                    <div class="leave-group-section">
                        <form method="POST" action="<?= Config::get('URL') . 'chat/leaveGroup'; ?>" onsubmit="return confirm('Möchten Sie diese Gruppe wirklich verlassen?');">
                            <input type="hidden" name="group_chat_id" value="<?= $this->group_chat->group_chat_id; ?>" />
                            <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                            <button type="submit" class="btn-leave">Gruppe verlassen</button>
                        </form>
                    </div>
                </div>

                <!-- Nachrichten-Bereich -->
                <div class="chat-messages">
                    <?php if (isset($this->messages) && !empty($this->messages)) { ?>
                        <?php foreach ($this->messages as $message) { ?>
                            <div class="message <?= ($message->sender_id == Session::get('user_id') ? 'sent' : 'received'); ?>">
                                <div class="message-sender">
                                    <span class="sender-name"><?= htmlspecialchars($message->user_name); ?></span>
                                    <span class="message-time"><?= date('H:i', $message->message_timestamp); ?></span>
                                </div>
                                <div class="message-content">
                                    <p><?= htmlspecialchars($message->message_text); ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="no-messages">
                            <p>Noch keine Nachrichten. Seien Sie der Erste, der schreibt!</p>
                        </div>
                    <?php } ?>
                </div>

                <!-- Nachrichteneingabe -->
                <div class="chat-input-section">
                    <form method="POST" action="<?= Config::get('URL') . 'chat/sendGroupMessage'; ?>" class="chat-form">
                        <?php if (Session::userIsLoggedIn()) { ?>
                            <input type="hidden" name="group_chat_id" value="<?= $this->group_chat->group_chat_id; ?>" />
                            
                            <div class="input-wrapper">
                                <textarea 
                                    name="message_text" 
                                    class="message-input" 
                                    placeholder="Schreiben Sie eine Nachricht..."
                                    rows="3"
                                    required></textarea>
                                <button type="submit" class="btn-send">Senden</button>
                            </div>

                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                        <?php } else { ?>
                            <div class="login-prompt">
                                <p>Sie müssen <a href="<?= Config::get('URL'); ?>login/index">eingeloggt</a> sein, um Nachrichten zu senden.</p>
                            </div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        <?php } else { ?>
            <div class="error-box">
                <p>Gruppenchat nicht gefunden. <a href="<?= Config::get('URL') . 'chat/groupChats'; ?>">Zurück zu Gruppenchats</a></p>
            </div>
        <?php } ?>

    </div>
</div>

<style>
.group-chat-container {
    display: flex;
    flex-direction: column;
    height: 600px;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    background: #f9f9f9;
}

.chat-group-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.group-icon {
    font-size: 32px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e7f3ff;
    border-radius: 8px;
}

.chat-group-details h2 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.member-info {
    margin: 5px 0 0 0;
    font-size: 12px;
    color: #666;
}

.chat-header-actions {
    display: flex;
    gap: 10px;
}

.btn-icon {
    padding: 8px 12px;
    background: #f0f0f0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-icon:hover {
    background: #e0e0e0;
}

.chat-sidebar {
    position: fixed;
    right: 0;
    top: 0;
    width: 300px;
    height: 100%;
    background: white;
    border-left: 1px solid #eee;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.chat-sidebar.active {
    transform: translateX(0);
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    background: #f9f9f9;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 16px;
}

.btn-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #666;
}

.members-list {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    margin-bottom: 10px;
    background: #f9f9f9;
    border-radius: 6px;
    transition: background 0.3s;
}

.member-item:hover {
    background: #f0f0f0;
}

.member-avatar {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
}

.member-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

.member-info {
    flex: 1;
}

.member-name {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    color: #333;
}

.creator-badge {
    background: #ffc107;
    color: #333;
    padding: 2px 6px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.btn-remove-member {
    background: #dc3545;
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: background 0.3s;
}

.btn-remove-member:hover {
    background: #c82333;
}

.add-member-section {
    padding: 15px;
    background: #f9f9f9;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.add-member-form label {
    display: block;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
}

.add-member-form .input-wrapper {
    display: flex;
    gap: 5px;
}

.add-member-form input {
    flex: 1;
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 12px;
}

.btn-add {
    padding: 6px 10px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-add:hover {
    background: #218838;
}

.leave-group-section {
    padding: 15px;
}

.btn-leave {
    width: 100%;
    padding: 10px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-leave:hover {
    background: #c82333;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f5f5f5;
}

.message {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
    animation: slideIn 0.3s ease-in;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.sent {
    align-items: flex-end;
}

.message-sender {
    font-size: 12px;
    color: #666;
    margin-bottom: 3px;
    display: flex;
    gap: 10px;
}

.message.sent .message-sender {
    flex-direction: row-reverse;
    text-align: right;
}

.sender-name {
    font-weight: bold;
    color: #333;
}

.message-time {
    font-size: 11px;
    color: #999;
}

.message-content {
    max-width: 70%;
    padding: 12px 15px;
    border-radius: 8px;
    word-wrap: break-word;
}

.message.sent .message-content {
    background: #007bff;
    color: white;
    border-radius: 18px 18px 4px 18px;
}

.message.received .message-content {
    background: white;
    color: #333;
    border-radius: 18px 18px 18px 4px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-content p {
    margin: 0;
}

.no-messages {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #999;
    font-style: italic;
}

.chat-input-section {
    padding: 15px 20px;
    background: white;
    border-top: 1px solid #eee;
}

.chat-form {
    width: 100%;
}

.input-wrapper {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.message-input {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    resize: vertical;
    max-height: 100px;
}

.message-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
}

.btn-send {
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s;
    font-weight: bold;
}

.btn-send:hover {
    background: #0056b3;
}

.btn-send:active {
    transform: scale(0.98);
}

.login-prompt {
    padding: 20px;
    text-align: center;
    background: #fff3cd;
    border-radius: 4px;
    color: #856404;
}

.login-prompt a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

.login-prompt a:hover {
    text-decoration: underline;
}

.error-box {
    padding: 20px;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    color: #721c24;
}

.error-box a {
    color: #004085;
    text-decoration: none;
    font-weight: bold;
}

.error-box a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .group-chat-container {
        height: auto;
    }

    .message-content {
        max-width: 85%;
    }

    .chat-sidebar {
        width: 100%;
        right: -100%;
    }
}
</style>

<script>
function toggleMemberList() {
    const sidebar = document.getElementById('memberSidebar');
    sidebar.classList.toggle('active');
}

// Auto-scroll to bottom on load
window.addEventListener('load', function() {
    const chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});
</script>
