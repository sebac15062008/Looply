<?php
require_once(__DIR__ . "/includes/bootstrap.php");
require_once(__DIR__ . "/config/database.php");
require_once(__DIR__ . "/includes/queries.php");
redirectIfNotLoggedIn();

$db    = (new connectionDatabase())->con;
$my_id = $_SESSION['user_id'];

// ─── Acciones POST (menú de 3 puntos) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action         = $_POST['action'] ?? '';
    $target_user_id = intval($_POST['target_user_id'] ?? 0);

    if ($target_user_id) {
        $user_one    = min($my_id, $target_user_id);
        $user_two    = max($my_id, $target_user_id);
        $is_user_one = ($my_id === $user_one);

        match ($action) {
            'clear_chat'   => q_clear_chat($db, $user_one, $user_two, $is_user_one),
            'delete_chat'  => q_delete_chat($db, $user_one, $user_two, $is_user_one),
            'block_user'   => q_block_user($db, $my_id, $target_user_id),
            'unblock_user' => q_unblock_user($db, $my_id, $target_user_id),
            default        => null,
        };

        $redirect = ($action === 'delete_chat') ? 'index.php' : "index.php?user=$target_user_id";
        header("Location: $redirect");
        exit;
    }
}

// ─── Sidebar: contactos activos ───────────────────────────────
$contacts = q_get_sidebar_contacts($db, $my_id);

// ─── Chat activo ──────────────────────────────────────────────
$chat_user_id    = $_GET['user'] ?? null;
$chat_user       = null;
$messages        = [];
$is_blocked_by_me = false;
$am_i_blocked     = false;

if ($chat_user_id) {
    $chat_user = q_get_user_by_id($db, (int)$chat_user_id);

    if ($chat_user) {
        $user_one    = min($my_id, $chat_user_id);
        $user_two    = max($my_id, $chat_user_id);
        $is_user_one = ($my_id === $user_one);

        $conv = q_get_conversation($db, $user_one, $user_two);

        if ($conv) {
            $cleared_at = $is_user_one ? $conv['cleared_at_user_one'] : $conv['cleared_at_user_two'];
            $messages   = q_get_messages($db, $conv['id'], $cleared_at ?: null);
        }

        // Estado de bloqueo
        $blocks = q_get_blocks_between($db, $my_id, (int)$chat_user_id);
        foreach ($blocks as $b) {
            if ($b['blocker_id'] == $my_id)   $is_blocked_by_me = true;
            if ($b['blocked_id'] == $my_id)   $am_i_blocked     = true;
        }
    }
}


$pageTitle = "Looply";

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
    <?php renderBootstrapHead(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="<?php echo $chat_user_id ? 'no-mobile-nav' : ''; ?>">

<div class="app-container <?php echo $chat_user_id ? 'mobile-content-page' : 'mobile-sidebar-page'; ?>">
    <div class="sidebar">
        <div class="sidebar-header sidebar-header-row pb-0">
            <span>Looply</span>
            <a href="views/new-chat.php" class="new-chat-trigger text-decoration-none">
                <i class="bi bi-plus-lg"></i>
                <span>Nuevo chat</span>
            </a>
        </div>

        <div class="px-4 py-3">
            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="sidebarSearch" class="search-input" placeholder="Buscar chat...">
            </div>
        </div>

        <div class="contact-list">
            <?php if (empty($contacts)): ?>
                <div class="p-5 text-center" style="color: var(--text-bright);">
                    <i class="bi bi-person-plus mb-3 d-block" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    <p style="font-size: 0.9rem; line-height: 1.4;">No tienes chats activos.<br>Presiona el botón de <b>+ Nuevo chat</b> para empezar a chatear!</p>
                </div>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                <a href="index.php?user=<?php echo $contact['id']; ?>" class="contact-item <?php echo ($chat_user_id == $contact['id']) ? 'active' : ''; ?> text-decoration-none">
                    <div class="avatar-wrapper">
                        <div class="avatar avatar-initials" aria-label="<?php echo $contact['full_name']; ?>">
                            <?php echo getInitials($contact['full_name']); ?>
                        </div>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-bold" style="color: var(--text-bright); font-size: 0.9rem;"><?php echo htmlspecialchars($contact['full_name']); ?></div>
                        <div class="text-truncate" style="color: var(--text-dim); font-size: 0.75rem;">
                            <?php echo htmlspecialchars($contact['last_message'] ?? 'Sin mensajes'); ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="p-4 sidebar-footer" style="border-top: 1px solid var(--glass-border);">
            <a href="views/profile.php" class="btn-profile">
                <i class="bi bi-person-circle"></i>
                <span>Perfil</span>
            </a>
        </div>
    </div>

    <div class="chat-window">
        <?php if ($chat_user): ?>
        <div class="chat-header">
            <div class="d-flex align-items-center gap-3">
                <div class="mobile-back-slot">
                    <a href="index.php" class="mobile-back-button">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                </div>
                <h5 class="fw-bold mb-0" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#userProfileModal">
                    <?php echo htmlspecialchars($chat_user['full_name']); ?>
                </h5>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <div class="dropdown">
                    <button class="btn btn-link text-white text-decoration-none p-0" type="button" id="chatMenuBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chatMenuBtn" style="background: var(--panel-bg); border: 1px solid var(--glass-border); border-radius: 12px; box-shadow: var(--shadow-soft);">
                        <li>
                            <form action="index.php" method="POST" class="m-0" onsubmit="return confirm('¿Seguro que quieres limpiar los mensajes para ti?');">
                                <input type="hidden" name="action" value="clear_chat">
                                <input type="hidden" name="target_user_id" value="<?php echo $chat_user_id; ?>">
                                <button type="submit" class="dropdown-item text-white bg-transparent border-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-eraser"></i> Limpiar mis mensajes
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="index.php" method="POST" class="m-0" onsubmit="return confirm('¿Seguro que quieres eliminar este chat de tu lista?');">
                                <input type="hidden" name="action" value="delete_chat">
                                <input type="hidden" name="target_user_id" value="<?php echo $chat_user_id; ?>">
                                <button type="submit" class="dropdown-item text-danger bg-transparent border-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-trash"></i> Eliminar chat
                                </button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                        <li>
                            <form action="index.php" method="POST" class="m-0">
                                <input type="hidden" name="action" value="<?php echo $is_blocked_by_me ? 'unblock_user' : 'block_user'; ?>">
                                <input type="hidden" name="target_user_id" value="<?php echo $chat_user_id; ?>">
                                <button type="submit" class="dropdown-item text-warning bg-transparent border-0 d-flex align-items-center gap-2">
                                    <i class="bi <?php echo $is_blocked_by_me ? 'bi-unlock' : 'bi-slash-circle'; ?>"></i>
                                    <?php echo $is_blocked_by_me ? 'Desbloquear usuario' : 'Bloquear usuario'; ?>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            <?php if (empty($messages)): ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center" style="gap: 20px; color: var(--text-dim);">
                    <img src="assets/output-onlinegiftools.gif" alt="Cargando..." style="background: white; width: 100%; max-width: 200px; opacity: 0.9; border-radius: 24px;">
                    <div class="fw-bold opacity-75" style="font-size: 1.1rem; text-align: center;">Envía un mensaje para comenzar</div>
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

        <?php if ($is_blocked_by_me || $am_i_blocked): ?>
            <div class="input-area text-center py-3" style="border-top: 1px solid var(--glass-border);">
                <span style="color: var(--text-dim); font-size: 0.95rem;">
                    <?php echo $is_blocked_by_me ? 'Has bloqueado a este usuario.' : 'No puedes enviar mensajes a este chat.'; ?>
                </span>
            </div>
        <?php else: ?>
            <div class="input-area" style="position: relative;">
                <emoji-picker id="emoji-picker" style="display: none; position: absolute; bottom: calc(100% - 10px); left: 24px; z-index: 100; --background: var(--panel-bg); --border-color: var(--glass-border); --indicator-color: var(--text-bright); --button-hover-background: var(--hover-bg); box-shadow: var(--shadow-xl); border-radius: 18px;"></emoji-picker>
                <div class="input-wrapper">
                    <i class="bi bi-emoji-smile action-icon me-2" id="emoji-btn" style="cursor: pointer;"></i>
                    <input type="text" id="message-input" class="input-field" placeholder="Escribe un mensaje...">
                    <i class="bi bi-send-fill action-icon ms-2 send-icon-btn" id="send-btn"></i>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- User Profile Modal -->
        <div class="modal fade" id="userProfileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background: var(--soft-bg-2); border: 1px solid var(--glass-border); border-radius: 20px; color: var(--text-bright); backdrop-filter: blur(10px);">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center pt-0 pb-4">
                        <div class="avatar avatar-initials mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;" aria-label="<?php echo $chat_user['full_name']; ?>">
                            <?php echo getInitials($chat_user['full_name']); ?>
                        </div>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($chat_user['full_name']); ?></h4>
                        <div class="text-dim mb-3" style="font-size: 0.9rem;"><?php echo htmlspecialchars($chat_user['email']); ?></div>
                        
                        <?php if(!empty($chat_user['bio'])): ?>
                        <div class="p-3 mt-3 text-start" style="background: rgba(0,0,0,0.15); border-radius: 12px; font-size: 0.95rem;">
                            <div class="fw-bold mb-1" style="color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Biografía</div>
                            <?php echo nl2br(htmlspecialchars($chat_user['bio'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <style>
            @keyframes text-up {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-text {
                animation: text-up 0.8s ease-out forwards;
            }
        </style>
        <div class="h-100 d-flex flex-column align-items-center justify-content-center" style="background: var(--chat-bg);">
            <div style="transform: scale(1.3); margin-bottom: 0;">
                <?php include 'includes/loader.php'; ?>
            </div>

            <div class="text-center animate-text">
                <h2 class="fw-bold" style="letter-spacing: -1px; color: var(--text-bright); font-size: 3.5rem; margin: 0; margin-top: -45px; position: relative; z-index: 1;">Looply</h2>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<nav class="mobile-bottom-nav">
    <a href="index.php" class="mobile-bottom-link mobile-bottom-link-active">
        <i class="bi bi-chat-dots-fill"></i>
        <span>Chat</span>
    </a>
    <a href="views/profile.php" class="mobile-bottom-link">
        <i class="bi bi-person-circle"></i>
        <span>Perfil</span>
    </a>
</nav>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<script>
    const myId = <?php echo $_SESSION['user_id']; ?>;
    const chatUserId = <?php echo $chat_user_id ?? 'null'; ?>;
    
    // Always connect to WebSocket to receive notifications
    const conn = new WebSocket('ws://localhost:8081');
    
    conn.onopen = function(e) {
        console.log("Conectado al servidor de chat");
        conn.send(JSON.stringify({
            type: 'auth',
            user_id: myId
        }));
    };

    conn.onmessage = function(e) {
        const data = JSON.parse(e.data);
        if (data.type === 'message') {
            if (chatUserId && data.sender_id == chatUserId) {
                // We are in the chat with the sender, append the message
                appendMessage(data.message, 'received', data.created_at);
            } else {
                // Message from someone else, update sidebar or reload
                let contactItem = document.querySelector('.contact-item[href="index.php?user=' + data.sender_id + '"]');
                if (contactItem) {
                    let msgDiv = contactItem.querySelector('.text-truncate');
                    if (msgDiv) msgDiv.textContent = data.message;
                    let contactList = document.querySelector('.contact-list');
                    contactList.prepend(contactItem);
                } else {
                    // Force reload to show the new chat in sidebar
                    window.location.reload();
                }
            }
        }
    };

    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const emojiBtn = document.getElementById('emoji-btn');
    const emojiPicker = document.getElementById('emoji-picker');

    if (emojiBtn && emojiPicker && messageInput) {
        emojiBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (emojiPicker.style.display === 'none') {
                emojiPicker.style.display = 'block';
            } else {
                emojiPicker.style.display = 'none';
            }
        });

        emojiPicker.addEventListener('emoji-click', event => {
            messageInput.value += event.detail.unicode;
            messageInput.focus();
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!emojiBtn.contains(e.target) && !emojiPicker.contains(e.target)) {
                emojiPicker.style.display = 'none';
            }
        });
    }

    if (chatUserId && messageInput && sendBtn) {
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
                if (conn.readyState === WebSocket.OPEN) {
                    conn.send(JSON.stringify({
                        type: 'message',
                        sender_id: myId,
                        receiver_id: chatUserId,
                        message: message
                    }));
                    appendMessage(message, 'sent', new Date().toLocaleTimeString());
                    messageInput.value = "";
                } else {
                    alert("No estás conectado al servidor de chat.");
                }
            }
        }
    }

    function appendMessage(text, type, time) {
        if (!chatMessages) return;
        
        // Remove empty state if it exists
        const emptyState = chatMessages.querySelector('.h-100.d-flex');
        if (emptyState) emptyState.remove();

        const div = document.createElement('div');
        div.className = 'message ' + type;
        
        let timeStr = time;
        if (time.match(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/)) {
            timeStr = time.split(' ')[1].substring(0, 5);
        } else if (time.includes(':')) {
            let parts = time.split(':');
            timeStr = parts[0] + ':' + parts[1];
        }
        
        div.innerHTML = htmlEntities(text) + '<span class="message-time">' + timeStr + '</span>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Escape key logic
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (chatUserId !== null) {
                window.location.href = 'index.php';
            }
        }
    });

    // Sidebar Search Logic
    const sidebarSearch = document.getElementById('sidebarSearch');
    if (sidebarSearch) {
        sidebarSearch.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.contact-item');
            
            items.forEach(item => {
                const name = item.querySelector('.fw-bold').textContent.toLowerCase();
                if (name.includes(term)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
</script>

</body>
</html>
