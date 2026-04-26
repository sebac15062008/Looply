<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../includes/queries.php");
redirectIfNotLoggedIn();

$db    = (new connectionDatabase())->con;
$my_id = $_SESSION['user_id'];

// ─── Directorio de personas ───────────────────────────────────
$allUsers        = q_get_all_users_except_me($db, $my_id);
$peopleDirectory = [];
foreach ($allUsers as $user) {
    $firstLetter = strtoupper(substr($user['full_name'], 0, 1));
    $peopleDirectory[$firstLetter][] = [
        'id'     => $user['id'],
        'name'   => $user['full_name'],
        'handle' => '@' . $user['username'],
    ];
}

// ─── Sidebar: contactos activos ───────────────────────────────
$contacts = q_get_sidebar_contacts($db, $my_id);

$pageTitle = "Nuevo Chat - Looply";


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
<body class="no-mobile-nav">

<div class="app-container mobile-content-page">
    <div class="sidebar">
        <div class="sidebar-header sidebar-header-row pb-0">
            <span>Looply</span>
            <a href="new-chat.php" class="new-chat-trigger new-chat-trigger-active text-decoration-none">
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
            <a href="profile.php" class="btn-profile">
                <i class="bi bi-person-circle"></i>
                <span>Perfil</span>
            </a>
        </div>
    </div>

    <div class="chat-window">
        <div class="chat-header new-chat-header">
            <div class="mobile-back-slot">
                <a href="../index.php" class="mobile-back-button">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
            <div class="new-chat-heading-copy">
                <h5 class="fw-bold mb-0">Nuevo chat</h5>
                <small class="new-chat-subtitle">Busca personas separadas por letra</small>
            </div>
        </div>

        <div class="new-chat-search-bar">
            <div class="search-wrapper new-chat-search-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="directorySearchInput" class="search-input" placeholder="Buscar personas...">
            </div>
        </div>

        <div class="directory-list" id="directoryList">
            <?php if (empty($peopleDirectory)): ?>
                <div class="directory-empty-state">No hay otros usuarios registrados</div>
            <?php else: ?>
                <?php foreach ($peopleDirectory as $letter => $people): ?>
                    <section class="directory-letter-group" data-letter-group>
                        <div class="directory-letter-heading"><?php echo $letter; ?></div>
                        <div class="directory-people-list">
                            <?php foreach ($people as $person): ?>
                                <a href="../index.php?user=<?php echo $person['id']; ?>" class="directory-person-card text-decoration-none" data-person-name="<?php echo strtolower($person['name'] . ' ' . $person['handle']); ?>">
                                    <div class="avatar-wrapper">
                                        <div class="avatar avatar-initials" aria-label="<?php echo htmlspecialchars($person['name']); ?>">
                                            <?php echo getInitials($person['name']); ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 text-start">
                                        <div class="fw-bold" style="color: var(--text-bright); font-size: 0.92rem;"><?php echo htmlspecialchars($person['name']); ?></div>
                                        <div class="directory-person-handle"><?php echo htmlspecialchars($person['handle']); ?></div>
                                    </div>
                                    <i class="bi bi-chat-dots directory-person-icon"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // JS Básico para filtrar la lista
    document.getElementById('directorySearchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const groups = document.querySelectorAll('[data-letter-group]');
        
        groups.forEach(group => {
            const cards = group.querySelectorAll('.directory-person-card');
            let hasVisible = false;
            
            cards.forEach(card => {
                const name = card.getAttribute('data-person-name');
                if (name.includes(term)) {
                    card.style.display = 'flex';
                    hasVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            group.style.display = hasVisible ? 'block' : 'none';
        });
    });
</script>

</body>
</html>
