<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
$pageTitle = "Nuevo Chat - Looply";

$peopleDirectory = [
    'A' => [['name' => 'Ana Torres', 'handle' => '@ana.torres'], ['name' => 'Adrian Mejia', 'handle' => '@adrian.m']],
    'B' => [['name' => 'Brenda Lopez', 'handle' => '@brenda.lopez']],
    'C' => [['name' => 'Carlos Ruiz', 'handle' => '@carlos.ruiz'], ['name' => 'Camila Nunez', 'handle' => '@camila.n']],
    'D' => [['name' => 'Daniela Perez', 'handle' => '@daniela.p']],
    'E' => [['name' => 'Eduardo Feliz', 'handle' => '@edu.feliz']],
    'F' => [['name' => 'Fatima Diaz', 'handle' => '@fatima.d']],
    'G' => [['name' => 'Gabriel Santos', 'handle' => '@gabriel.s']],
    'H' => [['name' => 'Helena Cruz', 'handle' => '@helena.cruz']],
    'I' => [['name' => 'Ismael Reyes', 'handle' => '@ismael.r']],
    'J' => [['name' => 'Juan Perez', 'handle' => '@juan.perez']],
    'K' => [['name' => 'Karla Moreno', 'handle' => '@karla.m']],
    'L' => [['name' => 'Luis Gomez', 'handle' => '@luis.gomez']],
    'M' => [['name' => 'Maria Garcia', 'handle' => '@maria.g']],
    'N' => [['name' => 'Natalia Vega', 'handle' => '@natalia.v']],
    'O' => [['name' => 'Oscar Batista', 'handle' => '@oscar.b']],
    'P' => [['name' => 'Paola Martinez', 'handle' => '@paola.m']],
    'Q' => [['name' => 'Quincy Rosario', 'handle' => '@quincy.r']],
    'R' => [['name' => 'Raul Herrera', 'handle' => '@raul.h']],
    'S' => [['name' => 'Sofia Castillo', 'handle' => '@sofia.c']],
    'T' => [['name' => 'Tomas Diaz', 'handle' => '@tomas.d']],
    'U' => [['name' => 'Uriel Cabrera', 'handle' => '@uriel.c']],
    'V' => [['name' => 'Valeria Mendez', 'handle' => '@valeria.m']],
    'W' => [['name' => 'Wendy Polanco', 'handle' => '@wendy.p']],
    'X' => [['name' => 'Ximena Ortiz', 'handle' => '@ximena.o']],
    'Y' => [['name' => 'Yadiel Pena', 'handle' => '@yadiel.p']],
    'Z' => [['name' => 'Zoe Ramirez', 'handle' => '@zoe.r']],
];
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
            <?php foreach ($peopleDirectory as $letter => $people): ?>
                <section class="directory-letter-group" data-letter-group>
                    <div class="directory-letter-heading"><?php echo $letter; ?></div>
                        <div class="directory-people-list">
                            <?php foreach ($people as $person): ?>
                                <?php
                                $initials = '';
                                foreach (explode(' ', trim($person['name'])) as $part) {
                                    if ($part !== '') {
                                        $initials .= strtoupper(substr($part, 0, 1));
                                    }
                                }
                                $initials = substr($initials, 0, 2);
                                ?>
                                <button type="button" class="directory-person-card" data-person-name="<?php echo strtolower($person['name'] . ' ' . $person['handle']); ?>">
                                    <div class="avatar-wrapper">
                                        <div class="avatar avatar-initials" aria-label="<?php echo htmlspecialchars($person['name']); ?>"><?php echo htmlspecialchars($initials); ?></div>
                                    </div>
                                    <div class="flex-grow-1 text-start">
                                        <div class="fw-bold" style="font-size: 0.92rem;"><?php echo htmlspecialchars($person['name']); ?></div>
                                        <div class="directory-person-handle"><?php echo htmlspecialchars($person['handle']); ?></div>
                                </div>
                                <i class="bi bi-chat-dots directory-person-icon"></i>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

        </div>
    </div>
</div>

</body>
</html>
