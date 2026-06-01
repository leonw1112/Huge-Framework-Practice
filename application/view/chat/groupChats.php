<div class="container">
    <div class="chat-header-top">
        <h1>Gruppenchats</h1>
        <button class="btn-create-group" onclick="toggleCreateForm()">+ Neue Gruppe</button>
    </div>
    
    <div class="box">
        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <!-- Create Group Form (hidden by default) -->
        <div id="createGroupForm" class="create-group-form" style="display: none;">
            <h3>Neue Gruppe erstellen</h3>
            <form method="POST" action="<?= Config::get('URL') . 'chat/createGroupChat'; ?>">
                <div class="form-group">
                    <label for="group_name">Gruppenname:</label>
                    <input type="text" id="group_name" name="group_name" required placeholder="Geben Sie einen Namen ein..." maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="group_description">Beschreibung:</label>
                    <textarea id="group_description" name="group_description" placeholder="Optionale Beschreibung..." maxlength="255" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Erstellen</button>
                    <button type="button" class="btn-secondary" onclick="toggleCreateForm()">Abbrechen</button>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
            </form>
        </div>

        <!-- Group Chats List -->
        <div class="group-chats-list">
            <?php if (isset($this->group_chats) && !empty($this->group_chats)) { ?>
                <?php foreach ($this->group_chats as $group) { ?>
                    <div class="group-chat-card">
                        <div class="group-chat-header">
                            <h3><?= htmlspecialchars($group->group_chat_name); ?></h3>
                            <span class="member-count"><?= $group->member_count; ?> Mitglieder</span>
                        </div>
                        
                        <?php if ($group->group_chat_description) { ?>
                            <p class="group-description"><?= htmlspecialchars($group->group_chat_description); ?></p>
                        <?php } ?>
                        
                        <div class="group-actions">
                            <a href="<?= Config::get('URL') . 'chat/groupChat/' . $group->group_chat_id; ?>" class="btn-open">Öffnen</a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-groups">
                    <p>Sie sind noch in keinen Gruppenchats. Erstellen Sie eine neue Gruppe oder werden Sie zu einer eingeladen!</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<style>
.chat-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.btn-create-group {
    padding: 10px 20px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-create-group:hover {
    background: #218838;
}

.create-group-form {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #eee;
}

.create-group-form h3 {
    margin-top: 0;
    color: #333;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #333;
    font-weight: bold;
    font-size: 14px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group textarea {
    resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-primary,
.btn-secondary {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.group-chats-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.group-chat-card {
    background: white;
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 15px;
    transition: box-shadow 0.3s, transform 0.3s;
}

.group-chat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.group-chat-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 10px;
}

.group-chat-header h3 {
    margin: 0;
    color: #333;
    font-size: 16px;
    flex: 1;
}

.member-count {
    background: #e7f3ff;
    color: #0056b3;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    white-space: nowrap;
}

.group-description {
    color: #666;
    font-size: 13px;
    margin: 10px 0;
    line-height: 1.4;
}

.group-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-open {
    flex: 1;
    padding: 10px 15px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    text-align: center;
    transition: background 0.3s;
    border: none;
    cursor: pointer;
}

.btn-open:hover {
    background: #0056b3;
}

.no-groups {
    text-align: center;
    padding: 40px;
    color: #999;
}

.no-groups p {
    margin: 0;
    font-style: italic;
}

@media (max-width: 768px) {
    .chat-header-top {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }

    .btn-create-group {
        width: 100%;
    }

    .group-chats-list {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function toggleCreateForm() {
    const form = document.getElementById('createGroupForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
