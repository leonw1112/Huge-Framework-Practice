<div class="container">
    <h1>Messenger</h1>
    <div class="box">
        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <?php if ($this->other_user) { ?>
            <div class="chat-container">
                <!-- Chat Header mit Benutzerinformation -->
                <div class="chat-header">
                    <div class="chat-user-info">
                        <div class="chat-avatar">
                            <?php if (isset($this->other_user->user_avatar_link)) { ?>
                                <img src="<?= $this->other_user->user_avatar_link; ?>" alt="<?= $this->other_user->user_name; ?>" />
                            <?php } else { ?>
                                <div class="avatar-placeholder"><?= strtoupper(substr($this->other_user->user_name, 0, 1)); ?></div>
                            <?php } ?>
                        </div>
                        <div class="chat-user-details">
                            <h2><?= $this->other_user->user_name; ?></h2>
                            <p class="user-status <?= ($this->other_user->user_active == 1 ? 'online' : 'offline'); ?>">
                                <?= ($this->other_user->user_active == 1 ? 'Online' : 'Offline'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <a href="<?= Config::get('URL') . 'profile/showProfile/' . $this->other_user->user_id; ?>" class="btn-link">Profil</a>
                    </div>
                </div>

                <!-- Nachrichten-Bereich -->
                <div class="chat-messages">
                    <?php if (isset($this->messages) && !empty($this->messages)) { ?>
                        <?php foreach ($this->messages as $message) { ?>
                            <div class="message <?= ($message->sender_id == Session::get('user_id') ? 'sent' : 'received'); ?>">
                                <div class="message-content">
                                    <p><?= htmlspecialchars($message->message_text); ?></p>
                                    <span class="message-time"><?= date('H:i', strtotime($message->message_timestamp)); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="no-messages">
                            <p>Keine Nachrichten vorhanden. Starten Sie das Gespräch!</p>
                        </div>
                    <?php } ?>
                </div>

                <!-- Nachrichteneingabe -->
                <div class="chat-input-section">
                    <form method="POST" action="<?= Config::get('URL') . 'chat/sendMessage'; ?>" class="chat-form">
                        <?php if (Session::userIsLoggedIn()) { ?>
                            <input type="hidden" name="recipient_id" value="<?= $this->other_user->user_id; ?>" />
                            
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
                <p>Benutzer nicht gefunden. <a href="<?= Config::get('URL') . 'profile/index'; ?>">Zurück zur Benutzerliste</a></p>
            </div>
        <?php } ?>

    </div>
</div>

<style>
.chat-container {
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

.chat-user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-avatar {
    position: relative;
}

.chat-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.chat-user-details h2 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.user-status {
    margin: 5px 0 0 0;
    font-size: 12px;
    color: #999;
}

.user-status.online {
    color: #28a745;
    font-weight: bold;
}

.user-status.offline {
    color: #999;
}

.chat-actions {
    display: flex;
    gap: 10px;
}

.btn-link {
    padding: 8px 15px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 12px;
    transition: background 0.3s;
}

.btn-link:hover {
    background: #0056b3;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f5f5f5;
}

.message {
    display: flex;
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
    justify-content: flex-end;
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

.message-time {
    font-size: 11px;
    color: #999;
    margin-top: 5px;
    display: block;
}

.message.sent .message-time {
    color: rgba(255, 255, 255, 0.7);
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
    .chat-container {
        height: auto;
    }

    .message-content {
        max-width: 85%;
    }

    .chat-header {
        flex-wrap: wrap;
    }

    .chat-actions {
        width: 100%;
        margin-top: 10px;
    }
}
</style>
