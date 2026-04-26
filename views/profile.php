<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
$pageTitle = "Perfil - Looply";
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
<body>

<div class="app-container mobile-content-page">
    <div class="sidebar">
        <div class="sidebar-header sidebar-header-row pb-0">
            <span>Looply</span>
            <a href="new-chat.php" class="new-chat-trigger text-decoration-none">
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
            <a href="../index.php" class="contact-item text-decoration-none">
                <div class="avatar-wrapper">
                    <div class="avatar avatar-initials" aria-label="Juan Perez">JP</div>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-bold" style="color: var(--text-bright); font-size: 0.9rem;">Juan Perez</div>
                    <div class="text-truncate" style="color: var(--text-dim); font-size: 0.75rem;">Como va ese codigo?</div>
                </div>
            </a>

            <a href="../index.php" class="contact-item text-decoration-none">
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
            <a href="profile.php" class="btn-profile btn-profile-active">
                <i class="bi bi-person-circle"></i>
                <span>Perfil</span>
            </a>
        </div>
    </div>

    <div class="profile-window">
        <div class="profile-topbar">
            <div>
                <h5 class="fw-bold mb-0">Mi perfil</h5>
            </div>
        </div>

        <div class="profile-panel-app">
            <div class="profile-card-main">
                <div class="profile-card-head">
                    <div class="profile-card-identity">
                        <div class="profile-avatar-large-wrapper">
                            <div class="profile-avatar-large avatar-initials avatar-initials-large" aria-label="Sebastian Cuevas">SC</div>
                        </div>

                        <div class="profile-card-copy">
                            <h2 class="profile-name-main">Sebastian Cuevas</h2>
                            <p class="profile-username-main">@sebastian_dev</p>
                            <p class="profile-description-main">
                                Desarrollador enfocado en construir experiencias de chat limpias, rapidas y faciles de usar.
                            </p>
                        </div>
                    </div>

                    <button class="profile-edit-button">
                        <i class="bi bi-pencil-square"></i>
                        <span>Editar perfil</span>
                    </button>
                </div>

                <div class="profile-meta-grid">
                    <div class="profile-meta-card">
                        <span class="profile-meta-label">Correo</span>
                        <strong>sebastian@looply.dev</strong>
                    </div>
                    <div class="profile-meta-card">
                        <span class="profile-meta-label">Nombre completo</span>
                        <strong>Sebastian Cuevas</strong>
                    </div>
                    <div class="profile-meta-card">
                        <span class="profile-meta-label">Usuario</span>
                        <strong>@sebastian_dev</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<nav class="mobile-bottom-nav">
    <a href="../index.php" class="mobile-bottom-link">
        <i class="bi bi-chat-dots-fill"></i>
        <span>Chat</span>
    </a>
    <a href="profile.php" class="mobile-bottom-link mobile-bottom-link-active">
        <i class="bi bi-person-circle"></i>
        <span>Perfil</span>
    </a>
</nav>

</body>
</html>
