<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
$pageTitle = "Mensajes - Looply";
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
                <div class="avatar avatar-initials" aria-label="Juan Perez">JP</div>
                <div>
                    <h5 class="fw-bold mb-0">Juan Perez</h5>
                    <small style="color: var(--text-dim);">En linea</small>
                </div>
            </div>
        </div>

        <div class="chat-messages">
            <div class="message received">
                Hola, como vas con el diseno?
                <span class="message-time">10:30 AM</span>
            </div>

            <div class="message sent">
                Bien. Justo estoy terminando el chat.
                <span class="message-time">10:32 AM</span>
            </div>

            <div class="message received">
                Se esta viendo mucho mas limpio. Cuando puedas me ensenas la version final.
                <span class="message-time">10:34 AM</span>
            </div>

            <div class="message sent">
                Dale, en un rato te la paso con todo el estilo aplicado.
                <span class="message-time">10:35 AM</span>
            </div>
        </div>

        <div class="input-area">
            <div class="input-wrapper">
                <i class="bi bi-emoji-smile action-icon me-2"></i>
                <input type="text" class="input-field" placeholder="Escribe un mensaje...">
                <i class="bi bi-send-fill action-icon ms-2 send-icon-btn" aria-label="Enviar mensaje"></i>
            </div>
        </div>
    </div>
</div>

</body>
</html>
