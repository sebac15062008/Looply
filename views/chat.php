<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../includes/queries.php");
redirigir_si_no_logueado();

$bd = (new ConexionBaseDatos())->con;
$mi_id = $_SESSION['user_id'];

$id_usuario_chat = $_GET['user'] ?? null;
if (!$id_usuario_chat) {
    header("Location: ../index.php");
    exit();
}

$usuario_chat = c_obtener_usuario_por_id($bd, (int)$id_usuario_chat);

if (!$usuario_chat) {
    header("Location: ../index.php");
    exit();
}

$usuario_uno = min($mi_id, $id_usuario_chat);
$usuario_dos = max($mi_id, $id_usuario_chat);

$conversacion = c_obtener_conversacion($bd, $usuario_uno, $usuario_dos);

$mensajes = [];
if ($conversacion) {
    $mensajes = c_obtener_mensajes($bd, $conversacion['id']);
}

$tituloPagina = "Chat con " . $usuario_chat['full_name'];

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
    <?php renderizar_cabecera_bootstrap('..'); ?>
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
                <div class="avatar avatar-initials" aria-label="<?php echo $usuario_chat['full_name']; ?>">
                    <?php echo obtener_iniciales($usuario_chat['full_name']); ?>
                </div>
                <div>
                    <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($usuario_chat['full_name']); ?></h5>
                    <small style="color: var(--text-dim);">Chat</small>
                </div>
            </div>
        </div>

        <div class="chat-messages" id="mensajes-chat">
            <?php if (empty($mensajes)): ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-white" style="gap: 20px;">
                    <?php $basePath = '../'; include '../includes/loader.php'; ?>
                    <div class="fw-bold opacity-75" style="font-size: 1rem;">Envía un mensaje para chatear</div>
                </div>
            <?php else: ?>
                <?php foreach ($mensajes as $msj): ?>
                <div class="message <?php echo ($msj['sender_id'] == $mi_id) ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($msj['message']); ?>
                    <span class="message-time"><?php echo date('H:i', strtotime($msj['created_at'])); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="input-area">
            <div class="input-wrapper">
                <i class="bi bi-emoji-smile action-icon me-2"></i>
                <input type="text" id="entrada-mensaje" class="input-field" placeholder="Escribe un mensaje...">
                <i class="bi bi-send-fill action-icon ms-2 send-icon-btn" id="boton-enviar"></i>
            </div>
        </div>
    </div>
</div>

<script>
    const miId = <?php echo $_SESSION['user_id']; ?>;
    const idUsuarioChat = <?php echo $id_usuario_chat; ?>;
    
    const conexion = new WebSocket('ws://127.0.0.1:8081');
    const contenedorMensajes = document.getElementById('mensajes-chat');
    const entradaMensaje = document.getElementById('entrada-mensaje');
    const botonEnviar = document.getElementById('boton-enviar');

    conexion.onopen = function(e) {
        conexion.send(JSON.stringify({
            type: 'auth',
            user_id: miId
        }));
    };

    conexion.onmessage = function(e) {
        const datos = JSON.parse(e.data);
        if (datos.type === 'message' && datos.sender_id == idUsuarioChat) {
            agregarMensaje(datos.message, 'received', datos.created_at);
        }
    };

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
            conexion.send(JSON.stringify({
                type: 'message',
                sender_id: miId,
                receiver_id: idUsuarioChat,
                message: texto
            }));
            agregarMensaje(texto, 'sent', new Date().toLocaleTimeString());
            entradaMensaje.value = "";
        }
    }

    function agregarMensaje(texto, tipo, hora) {
        const div = document.createElement('div');
        div.className = 'message ' + tipo;
        const cadenaHora = hora.includes(':') ? hora.split(' ')[1]?.substring(0, 5) || hora : hora;
        div.innerHTML = entidadesHtml(texto) + '<span class="message-time">' + cadenaHora + '</span>';
        contenedorMensajes.appendChild(div);
        contenedorMensajes.scrollTop = contenedorMensajes.scrollHeight;
    }

    function entidadesHtml(cadena) {
        return String(cadena).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    
    contenedorMensajes.scrollTop = contenedorMensajes.scrollHeight;
</script>

</body>
</html>
