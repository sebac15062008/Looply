<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
require_once(__DIR__ . "/../config/database.php");
redirectIfNotLoggedIn();

$db = (new connectionDatabase())->con;
$my_id = $_SESSION['user_id'];

$chat_user_id = $_GET['user'] ?? null;
if (!$chat_user_id) {
    header("Location: ../index.php");
    exit();
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$chat_user_id]);
$chat_user = $stmt->fetch();

if (!$chat_user) {
    header("Location: ../index.php");
    exit();
}

$user_one = min($my_id, $chat_user_id);
$user_two = max($my_id, $chat_user_id);

$stmt = $db->prepare("SELECT id FROM conversations WHERE user_one = ? AND user_two = ?");
$stmt->execute([$user_one, $user_two]);
$conv = $stmt->fetch();

$messages = [];
if ($conv) {
    $stmt = $db->prepare("SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC");
    $stmt->execute([$conv['id']]);
    $messages = $stmt->fetchAll();
}

$pageTitle = "Chat con " . $chat_user['full_name'];

function getInitials($name) {
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        $initials .= $w[0] ?? '';
    }
    return strtoupper(substr($initials, 0, 2));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <?php renderBootstrapHead('..'); ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="no-mobile-nav">

<div class="app-container mobile-content-page">
    <div class="sidebar">
        <div class="sidebar-header sidebar-header-row pb-0">
            <span>Looply</span>
            <a href="new-chat.php" class="new-chat-trigger text-decoration-none">
                <i class="bi bi-plus-lg"></i>
                <span>Nuevo chat</span>
            </a>
        </div>
    </div>

    <div class="chat-window">
        <div class="chat-header">
            <div class="mobile-back-slot">
                <a href="../index.php" class="mobile-back-button">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
            <div class="d-flex align-items-center new-chat-heading-copy">
                <div class="avatar avatar-initials" aria-label="<?php echo $chat_user['full_name']; ?>">
                    <?php echo getInitials($chat_user['full_name']); ?>
                </div>
                <div>
                    <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($chat_user['full_name']); ?></h5>
                    <small style="color: var(--text-dim);">Chat</small>
                </div>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            <?php if (empty($messages)): ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-white" style="gap: 20px;">
                    <?php $basePath = '../'; include '../includes/ojos-loader.php'; ?>
                    <div class="fw-bold opacity-75" style="font-size: 1rem;">Envía un mensaje para poder chatear</div>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <div class="message <?php echo ($msg['sender_id'] == $my_id) ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($msg['message']); ?>
                    <span class="message-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="input-area">
            <div class="input-wrapper">
                <i class="bi bi-emoji-smile action-icon me-2"></i>
                <input type="text" id="message-input" class="input-field" placeholder="Escribe un mensaje...">
                <i class="bi bi-send-fill action-icon ms-2 send-icon-btn" id="send-btn"></i>
            </div>
        </div>
    </div>
</div>

<script>
    const myId = <?php echo $_SESSION['user_id']; ?>;
    const chatUserId = <?php echo $chat_user_id; ?>;
    
    const conn = new WebSocket('ws://localhost:8080');
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');

    conn.onopen = function(e) {
        conn.send(JSON.stringify({
            type: 'auth',
            user_id: myId
        }));
    };

    conn.onmessage = function(e) {
        const data = JSON.parse(e.data);
        if (data.type === 'message' && data.sender_id == chatUserId) {
            appendMessage(data.message, 'received', data.created_at);
        }
    };

    sendBtn.onclick = function() {
        sendMessage();
    };

    messageInput.onkeypress = function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    };

    function sendMessage() {
        const message = messageInput.value.trim();
        if (message !== "") {
            conn.send(JSON.stringify({
                type: 'message',
                sender_id: myId,
                receiver_id: chatUserId,
                message: message
            }));
            appendMessage(message, 'sent', new Date().toLocaleTimeString());
            messageInput.value = "";
        }
    }

    function appendMessage(text, type, time) {
        const div = document.createElement('div');
        div.className = 'message ' + type;
        const timeStr = time.includes(':') ? time.split(' ')[0].substring(0, 5) : time;
        div.innerHTML = htmlEntities(text) + '<span class="message-time">' + timeStr + '</span>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    
    chatMessages.scrollTop = chatMessages.scrollHeight;
</script>

</body>
</html>
