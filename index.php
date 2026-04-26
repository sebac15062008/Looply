<?php
require_once(__DIR__ . "/includes/bootstrap.php");
$pageTitle = "Looply";
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
<body>

<div class="app-container mobile-sidebar-page">
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
                <input type="text" class="search-input" placeholder="Buscar chat...">
            </div>
        </div>

        <div class="contact-list">
            <a href="views/chat.php" class="contact-item active text-decoration-none">
                <div class="avatar-wrapper">
                    <div class="avatar avatar-initials" aria-label="Juan Perez">JP</div>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-bold" style="color: var(--text-bright); font-size: 0.9rem;">Juan Perez</div>
                    <div class="text-truncate" style="color: var(--text-dim); font-size: 0.75rem;">Como va ese codigo?</div>
                </div>
            </a>

            <a href="views/chat.php" class="contact-item text-decoration-none">
                <div class="avatar-wrapper">
                    <div class="avatar avatar-initials avatar-initials-soft" aria-label="Maria Garcia">MG</div>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-bold" style="color: var(--text-bright); font-size: 0.9rem;">Maria Garcia</div>
                    <div class="text-truncate" style="color: var(--text-dim); font-size: 0.75rem;">Visto hace 10 min</div>
                </div>
            </a>
        </div>

        <div class="p-4 sidebar-footer" style="border-top: 1px solid var(--glass-border);">
            <a href="views/profile.php" class="btn-profile">
                <i class="bi bi-person-circle"></i>
                <span>Perfil</span>
            </a>
        </div>
    </div>

    <div class="chat-window">
        <div class="chat-header">
            <div>
                <h5 class="fw-bold mb-0">Juan Perez</h5>
            </div>
            <div class="d-flex gap-3"></div>
        </div>

        <div class="chat-messages">
            <div class="message received">
                Oye, este nuevo diseno Aurora UI se ve increible. Es puro CSS?
                <span class="message-time">10:30 AM</span>
            </div>

            <div class="message sent">
                Si. Usando Backdrop Filter y Aurora Gradients. Nada de imagenes pesadas ni JS para el estilo.
                <span class="message-time">10:32 AM</span>
            </div>

            <div class="message received">
                La fluidez de las tarjetas y el efecto de cristal le dan un toque de aplicacion de 10k.
                <span class="message-time">10:45 AM</span>
            </div>
        </div>

        <div class="input-area">
            <div class="input-wrapper">
                <i class="bi bi-emoji-smile action-icon me-2"></i>
                <input type="text" class="input-field" placeholder="Escribe un mensaje...">
                <i class="bi bi-send-fill action-icon ms-2 send-icon-btn"></i>
            </div>
        </div>
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

</body>
</html>
