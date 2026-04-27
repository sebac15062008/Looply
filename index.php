<?php
require_once(__DIR__ . "/includes/bootstrap.php");
require_once(__DIR__ . "/config/database.php");
require_once(__DIR__ . "/includes/queries.php");
redirigir_si_no_logueado();

$bd    = (new ConexionBaseDatos())->con;
$mi_id = $_SESSION['user_id'];

// ─── Acciones POST (menú de 3 puntos) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion              = $_POST['action'] ?? '';
    $id_usuario_objetivo = intval($_POST['target_user_id'] ?? 0);

    if ($id_usuario_objetivo) {
        $usuario_uno    = min($mi_id, $id_usuario_objetivo);
        $usuario_dos    = max($mi_id, $id_usuario_objetivo);
        $es_usuario_uno = ($mi_id === $usuario_uno);

        match ($accion) {
            'clear_chat'   => c_limpiar_chat($bd, $usuario_uno, $usuario_dos, $es_usuario_uno),
            'delete_chat'  => c_eliminar_chat($bd, $usuario_uno, $usuario_dos, $es_usuario_uno),
            'block_user'   => c_bloquear_usuario($bd, $mi_id, $id_usuario_objetivo),
            'unblock_user' => c_desbloquear_usuario($bd, $mi_id, $id_usuario_objetivo),
            default        => null,
        };

        $redireccion = ($accion === 'delete_chat') ? 'index.php' : "index.php?user=$id_usuario_objetivo";
        header("Location: $redireccion");
        exit;
    }
}

// ─── Sidebar: contactos activos ───────────────────────────────
$contactos = c_obtener_contactos_sidebar($bd, $mi_id);

// ─── Chat activo ──────────────────────────────────────────────
$id_usuario_chat   = $_GET['user'] ?? null;
$usuario_chat      = null;
$mensajes          = [];
$bloqueado_por_mi  = false;
$estoy_bloqueado   = false;

if ($id_usuario_chat) {
    $usuario_chat = c_obtener_usuario_por_id($bd, (int)$id_usuario_chat);

    if ($usuario_chat) {
        $usuario_uno    = min($mi_id, $id_usuario_chat);
        $usuario_dos    = max($mi_id, $id_usuario_chat);
        $es_usuario_uno = ($mi_id === $usuario_uno);

        $conversacion = c_obtener_conversacion($bd, $usuario_uno, $usuario_dos);

        if ($conversacion) {
            $limpiado_en = $es_usuario_uno ? $conversacion['cleared_at_user_one'] : $conversacion['cleared_at_user_two'];
            $mensajes    = c_obtener_mensajes($bd, $conversacion['id'], $limpiado_en ?: null);
        }

        // Estado de bloqueo
        $bloqueos = c_obtener_bloqueos_entre($bd, $mi_id, (int)$id_usuario_chat);
        foreach ($bloqueos as $bloqueo) {
            if ($bloqueo['blocker_id'] == $mi_id)   $bloqueado_por_mi = true;
            if ($bloqueo['blocked_id'] == $mi_id)   $estoy_bloqueado  = true;
        }
    }
}


$tituloPagina = "Looply";

function obtener_iniciales($nombre) {
    $palabras = explode(" ", $nombre);
    $iniciales = "";
    foreach ($palabras as $p) {
        $iniciales .= $p[0] ?? '';
    }
    return strtoupper(substr($iniciales, 0, 2));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloPagina; ?></title>
    <?php renderizar_cabecera_bootstrap(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="<?php echo $id_usuario_chat ? 'no-mobile-nav' : ''; ?>">

<div class="app-container <?php echo $id_usuario_chat ? 'mobile-content-page' : 'mobile-sidebar-page'; ?>">
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
                <input type="text" id="buscadorSidebar" class="search-input" placeholder="Buscar chat...">
            </div>
        </div>

        <div class="contact-list">
            <?php if (empty($contactos)): ?>
                <div class="p-5 text-center" style="color: var(--text-bright);">
                    <i class="bi bi-person-plus mb-3 d-block" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    <p style="font-size: 0.9rem; line-height: 1.4;">No tienes chats activos.<br>Presiona el botón de <b>+ Nuevo chat</b> para empezar a chatear!</p>
                </div>
            <?php else: ?>
                <?php foreach ($contactos as $contacto): ?>
                <a href="index.php?user=<?php echo $contacto['id']; ?>" class="contact-item <?php echo ($id_usuario_chat == $contacto['id']) ? 'active' : ''; ?> text-decoration-none">
                    <div class="avatar-wrapper">
                        <div class="avatar avatar-initials" aria-label="<?php echo $contacto['full_name']; ?>">
                            <?php 
                                // Reutilizando logica de iniciales directamente
                                $n = $contacto['full_name'];
                                $p = explode(" ", $n);
                                $ini = "";
                                foreach ($p as $w) $ini .= $w[0] ?? '';
                                echo strtoupper(substr($ini, 0, 2));
                            ?>
                        </div>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-bold" style="color: var(--text-bright); font-size: 0.9rem;"><?php echo htmlspecialchars($contacto['full_name']); ?></div>
                        <div class="text-truncate" style="color: var(--text-dim); font-size: 0.75rem;">
                            <?php echo htmlspecialchars($contacto['last_message'] ?? 'Sin mensajes'); ?>
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
        <?php if ($usuario_chat): ?>
        <div class="chat-header">
            <div class="d-flex align-items-center gap-3">
                <div class="mobile-back-slot">
                    <a href="index.php" class="mobile-back-button">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                </div>
                <h5 class="fw-bold mb-0" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#userProfileModal">
                    <?php echo htmlspecialchars($usuario_chat['full_name']); ?>
                </h5>
            </div>
            <div class="dropdown ms-auto">
                <button class="btn btn-link text-white text-decoration-none p-0 opacity-75" type="button" id="chatMenuBtn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical fs-5"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-glass" aria-labelledby="chatMenuBtn">
                    <li>
                        <button type="button" class="dropdown-item dropdown-item-custom" data-bs-toggle="modal" data-bs-target="#clearChatModal">
                            <i class="bi bi-eraser"></i> Limpiar mis mensajes
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item dropdown-item-custom text-danger" data-bs-toggle="modal" data-bs-target="#deleteChatModal">
                            <i class="bi bi-trash"></i> Eliminar chat
                        </button>
                    </li>
                    <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                    <li>
                        <button type="button" class="dropdown-item dropdown-item-custom text-warning" data-bs-toggle="modal" data-bs-target="#blockUserModal">
                            <i class="bi <?php echo $bloqueado_por_mi ? 'bi-unlock' : 'bi-slash-circle'; ?>"></i>
                            <?php echo $bloqueado_por_mi ? 'Desbloquear usuario' : 'Bloquear usuario'; ?>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            <?php if (empty($mensajes)): ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center p-4" style="gap: 15px; color: var(--text-dim);">
                    <div class="empty-chat-icon" style="font-size: 3.5rem; opacity: 0.4; margin-bottom: 10px;">
                        <i class="bi bi-chat-heart"></i>
                    </div>
                    <div class="text-center">
                        <h6 class="fw-bold text-white mb-2" style="font-size: 1.25rem;">
                            <?php 
                                if ($bloqueado_por_mi || $estoy_bloqueado) {
                                    echo "Conversación Bloqueada";
                                } else {
                                    echo "¡Saluda a " . htmlspecialchars($usuario_chat['full_name']) . "!";
                                }
                            ?>
                        </h6>
                        <p style="font-size: 0.9rem; max-width: 280px; margin: 0 auto; opacity: 0.7;">
                            <?php 
                                if ($bloqueado_por_mi) {
                                    echo "Has bloqueado a esta persona. Desbloquéala para iniciar la charla.";
                                } elseif ($estoy_bloqueado) {
                                    echo "No puedes enviar mensajes a este usuario en este momento.";
                                } else {
                                    echo "Aún no hay mensajes en este chat. ¡Envía el primero para romper el hielo!";
                                }
                            ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($mensajes as $mensaje): ?>
                <div class="message <?php echo ($mensaje['sender_id'] == $mi_id) ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($mensaje['message']); ?>
                    <span class="message-time"><?php echo date('H:i', strtotime($mensaje['created_at'])); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($bloqueado_por_mi || $estoy_bloqueado): ?>
            <div class="input-area text-center py-4" style="border-top: 1px solid var(--glass-border); background: rgba(0,0,0,0.1);">
                <div class="d-flex flex-column align-items-center gap-2">
                    <span style="color: var(--text-dim); font-size: 0.95rem; font-weight: 500;">
                        <i class="bi bi-slash-circle me-1"></i>
                        <?php echo $bloqueado_por_mi ? 'Has bloqueado a este usuario' : 'Este usuario te ha bloqueado'; ?>
                    </span>
                    <?php if ($bloqueado_por_mi): ?>
                        <button type="button" class="btn btn-sm px-4" 
                                style="background: var(--accent-primary); color: var(--chat-bg); border-radius: 12px; font-weight: bold; border: none;"
                                data-bs-toggle="modal" data-bs-target="#blockUserModal">
                            Desbloquear para chatear
                        </button>
                    <?php else: ?>
                        <small style="color: var(--text-dim); opacity: 0.6;">No puedes enviar mensajes a esta conversación</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="input-area" style="position: relative;">
                <emoji-picker id="selector-emojis" style="display: none; position: absolute; bottom: calc(100% - 10px); left: 24px; z-index: 100; --background: var(--panel-bg); --border-color: var(--glass-border); --indicator-color: var(--text-bright); --button-hover-background: var(--hover-bg); box-shadow: var(--shadow-xl); border-radius: 18px;"></emoji-picker>
                <div class="input-wrapper">
                    <i class="bi bi-emoji-smile action-icon me-2" id="boton-emoji" style="cursor: pointer;"></i>
                    <input type="text" id="entrada-mensaje" class="input-field" placeholder="Escribe un mensaje...">
                    <i class="bi bi-send-fill action-icon ms-2 send-icon-btn" id="boton-enviar"></i>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- User Profile Modal -->
        <div class="modal fade" id="userProfileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background: var(--panel-bg); border: 1px solid var(--glass-border); border-radius: 24px; color: var(--text-bright); box-shadow: var(--shadow-xl); backdrop-filter: blur(20px);">
                    <div class="modal-banner"></div>
                    <div class="modal-header border-0 pb-0" style="position: relative; z-index: 2;">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8;"></button>
                    </div>
                    <div class="modal-body text-center pt-0 pb-4 px-4" style="position: relative; z-index: 1;">
                        <div class="avatar-wrapper mx-auto mb-3" style="margin-right: 0;">
                            <div class="avatar avatar-initials mx-auto" style="width: 90px; height: 90px; font-size: 2rem; border-radius: 24px; border: 4px solid var(--panel-bg); box-shadow: var(--avatar-shadow);" aria-label="<?php echo $usuario_chat['full_name']; ?>">
                                <?php 
                                    $n = $usuario_chat['full_name'];
                                    $p = explode(" ", $n);
                                    $ini = "";
                                    foreach ($p as $w) $ini .= $w[0] ?? '';
                                    echo strtoupper(substr($ini, 0, 2));
                                ?>
                            </div>
                        </div>
                        
                        <h4 class="fw-bold mb-1" style="letter-spacing: -0.5px;"><?php echo htmlspecialchars($usuario_chat['full_name']); ?></h4>
                        <div class="text-dim mb-3" style="font-size: 0.85rem; font-weight: 500;">
                            <?php echo htmlspecialchars($usuario_chat['email']); ?> • 
                            Desde <?php echo date('M Y', strtotime($usuario_chat['created_at'])); ?>
                        </div>
                        
                        <?php if(!empty($usuario_chat['bio'])): ?>
                        <div class="text-start mt-4">
                            <div class="fw-bold mb-2" style="color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Acerca de</div>
                            <div class="p-3" style="background: var(--soft-bg-2); border: 1px solid var(--glass-border); border-radius: 16px; font-size: 0.95rem; opacity: 0.9; line-height: 1.5;">
                                <?php echo nl2br(htmlspecialchars($usuario_chat['bio'])); ?>
                            </div>
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

<!-- Clear Chat Modal -->
<div class="modal fade" id="clearChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background: var(--panel-bg); border: 1px solid var(--glass-border); border-radius: 24px; color: var(--text-bright); box-shadow: var(--shadow-xl); backdrop-filter: blur(20px);">
            <div class="modal-body text-center p-4">
                <div class="icon-circle mx-auto mb-3" style="width: 60px; height: 60px; background: var(--soft-bg); color: var(--text-bright); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="bi bi-eraser"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Limpiar mensajes?</h5>
                <p class="text-dim mb-4" style="font-size: 0.9rem;">Esta acción eliminará todos los mensajes actuales solo para ti. Esta acción no se puede deshacer.</p>
                <div class="d-grid gap-2">
                    <form action="index.php" method="POST" class="m-0">
                        <input type="hidden" name="action" value="clear_chat">
                        <input type="hidden" name="target_user_id" value="<?php echo $id_usuario_chat; ?>">
                        <button type="submit" class="btn w-100 py-2 fw-bold" style="border-radius: 14px; background: var(--accent-primary); color: var(--chat-bg); border: none;">Limpiar</button>
                    </form>
                    <button type="button" class="btn w-100 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 14px; color: var(--text-dim); background: transparent; border: none;">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Chat Modal -->
<div class="modal fade" id="deleteChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background: var(--panel-bg); border: 1px solid var(--glass-border); border-radius: 24px; color: var(--text-bright); box-shadow: var(--shadow-xl); backdrop-filter: blur(20px);">
            <div class="modal-body text-center p-4">
                <div class="icon-circle mx-auto mb-3" style="width: 60px; height: 60px; background: rgba(255,0,0,0.1); color: #ff4d4d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="bi bi-trash"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Eliminar chat?</h5>
                <p class="text-dim mb-4" style="font-size: 0.9rem;">¿Estás seguro de que quieres eliminar este chat de tu lista? Se borrará el historial.</p>
                <div class="d-grid gap-2">
                    <form action="index.php" method="POST" class="m-0">
                        <input type="hidden" name="action" value="delete_chat">
                        <input type="hidden" name="target_user_id" value="<?php echo $id_usuario_chat; ?>">
                        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold" style="border-radius: 14px; border: none;">Eliminar</button>
                    </form>
                    <button type="button" class="btn w-100 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 14px; color: var(--text-dim); background: transparent; border: none;">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block User Modal -->
<div class="modal fade" id="blockUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background: var(--panel-bg); border: 1px solid var(--glass-border); border-radius: 24px; color: var(--text-bright); box-shadow: var(--shadow-xl); backdrop-filter: blur(20px);">
            <div class="modal-body text-center p-4">
                <div class="icon-circle mx-auto mb-3" style="width: 60px; height: 60px; background: rgba(255,193,7,0.1); color: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="bi <?php echo $bloqueado_por_mi ? 'bi-unlock' : 'bi-slash-circle'; ?>"></i>
                </div>
                <h5 class="fw-bold mb-2"><?php echo $bloqueado_por_mi ? '¿Desbloquear?' : '¿Bloquear?'; ?></h5>
                <p class="text-dim mb-4" style="font-size: 0.9rem;">
                    <?php echo $bloqueado_por_mi 
                        ? 'Podrás volver a recibir mensajes de este usuario.' 
                        : 'No recibirás más mensajes de este usuario hasta que lo desbloquees.'; ?>
                </p>
                <div class="d-grid gap-2">
                    <form action="index.php" method="POST" class="m-0">
                        <input type="hidden" name="action" value="<?php echo $bloqueado_por_mi ? 'unblock_user' : 'block_user'; ?>">
                        <input type="hidden" name="target_user_id" value="<?php echo $id_usuario_chat; ?>">
                        <button type="submit" class="btn <?php echo $bloqueado_por_mi ? 'btn-warning' : 'btn-dark'; ?> w-100 py-2 fw-bold" style="border-radius: 14px; border: none;">
                            <?php echo $bloqueado_por_mi ? 'Desbloquear' : 'Bloquear'; ?>
                        </button>
                    </form>
                    <button type="button" class="btn w-100 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 14px; color: var(--text-dim); background: transparent; border: none;">Cancelar</button>
                </div>
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

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<script>
    const miId = <?php echo $_SESSION['user_id']; ?>;
    const idUsuarioChat = <?php echo $id_usuario_chat ?? 'null'; ?>;
    
    // Conexión constante al WebSocket
    const conexion = new WebSocket('ws://127.0.0.1:8081');
    
    conexion.onopen = function(e) {
        console.log("Conectado al servidor de chat");
        conexion.send(JSON.stringify({
            type: 'auth',
            user_id: miId
        }));
    };

    conexion.onmessage = function(e) {
        const datos = JSON.parse(e.data);
        if (datos.type === 'message') {
            if (idUsuarioChat && datos.sender_id == idUsuarioChat) {
                // Estamos en el chat con el remitente
                agregarMensaje(datos.message, 'received', datos.created_at);
            } else {
                // Mensaje de alguien más, actualizar sidebar
                let itemContacto = document.querySelector('.contact-item[href="index.php?user=' + datos.sender_id + '"]');
                if (itemContacto) {
                    let divMensaje = itemContacto.querySelector('.text-truncate');
                    if (divMensaje) divMensaje.textContent = datos.message;
                    let listaContactos = document.querySelector('.contact-list');
                    listaContactos.prepend(itemContacto);
                } else {
                    // Forzar recarga para mostrar el nuevo chat
                    window.location.reload();
                }
            }
        }
    };

    const mensajesChat = document.getElementById('chat-messages');
    const entradaMensaje = document.getElementById('entrada-mensaje');
    const botonEnviar = document.getElementById('boton-enviar');
    const botonEmoji = document.getElementById('boton-emoji');
    const selectorEmoji = document.getElementById('selector-emojis');

    if (botonEmoji && selectorEmoji && entradaMensaje) {
        botonEmoji.addEventListener('click', (e) => {
            e.stopPropagation();
            if (selectorEmoji.style.display === 'none') {
                selectorEmoji.style.display = 'block';
            } else {
                selectorEmoji.style.display = 'none';
            }
        });

        selectorEmoji.addEventListener('emoji-click', evento => {
            entradaMensaje.value += evento.detail.unicode;
            entradaMensaje.focus();
        });

        document.addEventListener('click', (e) => {
            if (!botonEmoji.contains(e.target) && !selectorEmoji.contains(e.target)) {
                selectorEmoji.style.display = 'none';
            }
        });
    }

    if (idUsuarioChat && entradaMensaje && botonEnviar) {
        botonEnviar.onclick = function() {
            enviarMensaje();
        };

        entradaMensaje.onkeypress = function(e) {
            if (e.key === 'Enter') {
                enviarMensaje();
            }
        };

        function enviarMensaje() {
            const texto = entradaMensaje.value.trim();
            if (texto !== "") {
                if (conexion.readyState === WebSocket.OPEN) {
                    conexion.send(JSON.stringify({
                        type: 'message',
                        sender_id: miId,
                        receiver_id: idUsuarioChat,
                        message: texto
                    }));
                    agregarMensaje(texto, 'sent', new Date().toLocaleTimeString());
                    entradaMensaje.value = "";
                } else {
                    alert("No estás conectado al servidor de chat.");
                }
            }
        }
    }

    function agregarMensaje(texto, tipo, hora) {
        if (!mensajesChat) return;
        
        const estadoVacio = mensajesChat.querySelector('.h-100.d-flex');
        if (estadoVacio) estadoVacio.remove();

        const div = document.createElement('div');
        div.className = 'message ' + tipo;
        
        let cadenaHora = hora;
        if (hora.match(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/)) {
            cadenaHora = hora.split(' ')[1].substring(0, 5);
        } else if (hora.includes(':')) {
            let partes = hora.split(':');
            cadenaHora = partes[0] + ':' + partes[1];
        }
        
        div.innerHTML = entidadesHtml(texto) + '<span class="message-time">' + cadenaHora + '</span>';
        mensajesChat.appendChild(div);
        mensajesChat.scrollTop = mensajesChat.scrollHeight;
    }

    function entidadesHtml(cadena) {
        return String(cadena).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    
    if (mensajesChat) {
        mensajesChat.scrollTop = mensajesChat.scrollHeight;
    }


    const buscadorSidebar = document.getElementById('buscadorSidebar');
    if (buscadorSidebar) {
        buscadorSidebar.addEventListener('input', function(e) {
            const termino = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.contact-item');
            
            items.forEach(item => {
                const nombre = item.querySelector('.fw-bold').textContent.toLowerCase();
                if (nombre.includes(termino)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
</script>

<?php renderizar_script_bootstrap(); ?>
</body>
</html>
