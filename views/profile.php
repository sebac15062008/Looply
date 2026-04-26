<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../includes/queries.php");
redirectIfNotLoggedIn();

$db    = (new connectionDatabase())->con;
$my_id = $_SESSION['user_id'];

$errorMsg   = '';
$successMsg = '';

// ─── Actualizar perfil ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username']  ?? '');
    $email    = trim($_POST['email']     ?? '');
    $bio      = trim($_POST['bio']       ?? '');

    if (empty($fullName) || empty($username) || empty($email)) {
        $errorMsg = "Por favor, completa los campos requeridos.";
    } elseif (q_check_username_taken($db, $username, $my_id)) {
        $errorMsg = "El nombre de usuario ya está en uso.";
    } elseif (q_check_email_taken($db, $email, $my_id)) {
        $errorMsg = "El correo electrónico ya está registrado.";
    } else {
        q_update_user_profile($db, $fullName, $username, $email, $bio, $my_id);
        $successMsg = "Perfil actualizado con éxito.";
    }
}

// ─── Datos ────────────────────────────────────────────────────
$contacts = q_get_sidebar_contacts($db, $my_id);
$user     = q_get_user_by_id($db, $my_id);


$pageTitle = "Perfil - Looply";

function getInitials($name) {
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        if (!empty($w)) $initials .= $w[0];
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
                <a href="../index.php?user=<?php echo $contact['id']; ?>" class="contact-item text-decoration-none">
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
            <a href="profile.php" class="btn-profile btn-profile-active">
                <i class="bi bi-person-circle"></i>
                <span>Perfil</span>
            </a>
        </div>
    </div>

    <div class="profile-window">
        <div class="profile-topbar">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <h5 class="fw-bold mb-0">Mi perfil</h5>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </div>

        <div class="profile-panel-app">
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger" style="border-radius: 14px;"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>
            <?php if ($successMsg): ?>
                <div class="alert alert-success" style="border-radius: 14px; background: var(--panel-bg); color: #00d97e; border-color: #00d97e;"><?php echo htmlspecialchars($successMsg); ?></div>
            <?php endif; ?>

            <div class="profile-card-main" id="view-mode">
                <div class="profile-card-head">
                    <div class="profile-card-identity">
                        <div class="profile-avatar-large-wrapper">
                            <div class="profile-avatar-large avatar-initials avatar-initials-large" aria-label="<?php echo htmlspecialchars($user['full_name']); ?>">
                                <?php echo getInitials($user['full_name']); ?>
                            </div>
                        </div>

                        <div class="profile-card-copy">
                            <h2 class="profile-name-main"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                            <p class="profile-username-main">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="profile-description-main">
                                <?php echo htmlspecialchars($user['bio'] ?? 'Sin biografía.'); ?>
                            </p>
                        </div>
                    </div>

                    <button class="profile-edit-button" onclick="toggleEditMode()">
                        <i class="bi bi-pencil-square"></i>
                        <span>Editar perfil</span>
                    </button>
                </div>

                <div class="profile-meta-grid">
                    <div class="profile-meta-card">
                        <span class="profile-meta-label">Correo</span>
                        <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                    </div>
                    <div class="profile-meta-card">
                        <span class="profile-meta-label">Nombre completo</span>
                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                    </div>
                    <div class="profile-meta-card">
                        <span class="profile-meta-label">Usuario</span>
                        <strong>@<?php echo htmlspecialchars($user['username']); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Edit Mode -->
            <div class="profile-card-main" id="edit-mode" style="display: none;">
                <form method="POST" action="">
                    <h4 class="fw-bold mb-4">Editar Perfil</h4>
                    
                    <div class="mb-3">
                        <label class="profile-meta-label mb-2">Nombre Completo</label>
                        <input type="text" name="full_name" class="input-field-custom w-100" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="profile-meta-label mb-2">Usuario</label>
                            <input type="text" name="username" class="input-field-custom w-100" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="profile-meta-label mb-2">Correo Electrónico</label>
                            <input type="email" name="email" class="input-field-custom w-100" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="profile-meta-label mb-2">Biografía</label>
                        <textarea name="bio" class="input-field-custom w-100" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" name="update_profile" class="profile-edit-button border-0" style="background: var(--text-bright); color: var(--chat-bg);">
                            <i class="bi bi-check-lg"></i> Guardar cambios
                        </button>
                        <button type="button" class="profile-edit-button" onclick="toggleEditMode()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
            
            <footer class="profile-footer">
                <div class="profile-footer-inner">
                    <!-- Marca a la izquierda -->
                    <div class="profile-footer-brand">
                        <div class="profile-footer-logo">Looply</div>
                        <p class="profile-footer-desc">
                            La plataforma de mensajer&iacute;a moderna dise&ntilde;ada para conectar personas de forma segura, r&aacute;pida y con una interfaz premium.
                        </p>
                        <div class="profile-footer-copy">
                            &copy; <?php echo date('Y'); ?> Looply Chat &bull; Todos los derechos reservados.
                        </div>
                    </div>
                    <!-- Columnas de links a la derecha -->
                    <div class="profile-footer-links">
                        <div class="profile-footer-col">
                            <h6 class="profile-footer-col-title">Plataforma</h6>
                            <ul class="list-unstyled">
                                <li><a href="../index.php" class="footer-link">Mensajes</a></li>
                                <li><a href="new-chat.php" class="footer-link">Directorio</a></li>
                                <li><a href="profile.php" class="footer-link">Mi Cuenta</a></li>
                            </ul>
                        </div>
                        <div class="profile-footer-col">
                            <h6 class="profile-footer-col-title">Legal</h6>
                            <ul class="list-unstyled">
                                <li><a href="#" class="footer-link">Privacidad</a></li>
                                <li><a href="#" class="footer-link">T&eacute;rminos</a></li>
                                <li><a href="#" class="footer-link">Seguridad</a></li>
                            </ul>
                        </div>
                        <div class="profile-footer-col">
                            <h6 class="profile-footer-col-title">Soporte</h6>
                            <ul class="list-unstyled">
                                <li><a href="#" class="footer-link">Centro de Ayuda</a></li>
                                <li><a href="#" class="footer-link">Contacto</a></li>
                                <li><a href="#" class="footer-link">Estado</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
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

<script>
    // Toggle Edit Mode
    function toggleEditMode() {
        const viewMode = document.getElementById('view-mode');
        const editMode = document.getElementById('edit-mode');
        if (viewMode.style.display === 'none') {
            viewMode.style.display = 'block';
            editMode.style.display = 'none';
        } else {
            viewMode.style.display = 'none';
            editMode.style.display = 'block';
        }
    }

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
