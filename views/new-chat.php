<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../includes/queries.php");
redirigir_si_no_logueado();

$bd    = (new ConexionBaseDatos())->con;
$mi_id = $_SESSION['user_id'];

// ─── Directorio de personas ───────────────────────────────────
$todosLosUsuarios   = c_obtener_todos_los_usuarios_menos_yo($bd, $mi_id);
$directorioPersonas = [];
foreach ($todosLosUsuarios as $usuario) {
    $primeraLetra = strtoupper(substr($usuario['full_name'], 0, 1));
    $directorioPersonas[$primeraLetra][] = [
        'id'     => $usuario['id'],
        'name'   => $usuario['full_name'],
        'handle' => '@' . $usuario['username'],
    ];
}

// ─── Sidebar: contactos activos ───────────────────────────────
$contactos = c_obtener_contactos_sidebar($bd, $mi_id);

$tituloPagina = "Nuevo Chat - Looply";


function obtener_iniciales($nombre) {
    $palabras = explode(" ", $nombre);
    $iniciales = "";
    foreach ($palabras as $p) {
        if (!empty($p)) $iniciales .= $p[0];
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
            <?php if (empty($contactos)): ?>
                <div class="p-5 text-center" style="color: var(--text-bright);">
                    <i class="bi bi-person-plus mb-3 d-block" style="font-size: 2.5rem; opacity: 0.8;"></i>
                    <p style="font-size: 0.9rem; line-height: 1.4;">No tienes chats activos.<br>Presiona el botón de <b>+ Nuevo chat</b> para empezar a chatear!</p>
                </div>
            <?php else: ?>
                <?php foreach ($contactos as $contacto): ?>
                <a href="../index.php?user=<?php echo $contacto['id']; ?>" class="contact-item text-decoration-none">
                    <div class="avatar-wrapper">
                        <div class="avatar avatar-initials" aria-label="<?php echo $contacto['full_name']; ?>">
                            <?php 
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
                <input type="text" id="entradaBusquedaDirectorio" class="search-input" placeholder="Buscar personas...">
            </div>
        </div>

        <div class="directory-list" id="listaDirectorio">
            <?php if (empty($directorioPersonas)): ?>
                <div class="directory-empty-state">No hay otros usuarios registrados</div>
            <?php else: ?>
                <?php foreach ($directorioPersonas as $letra => $personas): ?>
                    <section class="directory-letter-group" data-grupo-letra>
                        <div class="directory-letter-heading"><?php echo $letra; ?></div>
                        <div class="directory-people-list">
                            <?php foreach ($personas as $persona): ?>
                                <?php 
                                    $info_usuario = c_obtener_usuario_por_id($bd, $persona['id']);
                                    $bloqueos_nc = c_obtener_bloqueos_entre($bd, $mi_id, $persona['id']);
                                    $nc_bloqueado_por_mi = false;
                                    foreach ($bloqueos_nc as $b_nc) {
                                        if ($b_nc['blocker_id'] == $mi_id) $nc_bloqueado_por_mi = true;
                                    }
                                ?>
                                <div class="directory-person-card" 
                                     style="cursor: pointer;"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#modalAccionUsuario"
                                     data-user-id="<?php echo $persona['id']; ?>"
                                     data-user-name="<?php echo htmlspecialchars($persona['name']); ?>"
                                     data-user-handle="<?php echo htmlspecialchars($persona['handle']); ?>"
                                     data-user-email="<?php echo htmlspecialchars($info_usuario['email'] ?? ''); ?>"
                                     data-user-bio="<?php echo htmlspecialchars($info_usuario['bio'] ?? ''); ?>"
                                     data-user-joined="<?php echo date('M Y', strtotime($info_usuario['created_at'])); ?>"
                                     data-is-blocked="<?php echo $nc_bloqueado_por_mi ? '1' : '0'; ?>"
                                     data-person-name="<?php echo strtolower($persona['name'] . ' ' . $persona['handle']); ?>">
                                    <div class="avatar-wrapper">
                                        <div class="avatar avatar-initials" aria-label="<?php echo htmlspecialchars($persona['name']); ?>">
                                            <?php 
                                                $n = $persona['name'];
                                                $p = explode(" ", $n);
                                                $ini = "";
                                                foreach ($p as $w) $ini .= $w[0] ?? '';
                                                echo strtoupper(substr($ini, 0, 2));
                                            ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 text-start">
                                        <div class="fw-bold" style="color: var(--text-bright); font-size: 0.92rem;"><?php echo htmlspecialchars($persona['name']); ?></div>
                                        <div class="directory-person-handle"><?php echo htmlspecialchars($persona['handle']); ?></div>
                                    </div>
                                    <i class="bi bi-chat-dots directory-person-icon"></i>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Acción de Usuario -->
<div class="modal fade" id="modalAccionUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--panel-bg); border: 1px solid var(--glass-border); border-radius: 24px; color: var(--text-bright); box-shadow: var(--shadow-xl); backdrop-filter: blur(20px);">
            <div class="modal-banner"></div>
            <div class="modal-header border-0 pb-0" style="position: relative; z-index: 2;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8;"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-4 px-4" style="position: relative; z-index: 1;">
                <div class="avatar-wrapper mx-auto mb-3" style="margin-right: 0;">
                    <div class="avatar avatar-initials mx-auto" id="modalAvatarUsuario" style="width: 90px; height: 90px; font-size: 2rem; border-radius: 24px; border: 4px solid var(--panel-bg); box-shadow: var(--avatar-shadow);">
                        ??
                    </div>
                </div>
                <h4 class="fw-bold mb-1" id="modalNombreUsuario" style="letter-spacing: -0.5px;">Nombre</h4>
                <div class="text-dim mb-3" style="font-size: 0.85rem; font-weight: 500;">
                    <span id="modalArrobaUsuario">@usuario</span> • Desde <span id="modalUnidoUsuario">--</span>
                </div>

                <div class="text-start mb-4" id="contenedorBioModal">
                    <div class="fw-bold mb-2" style="color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Acerca de</div>
                    <div class="p-3" id="modalBioUsuario" style="background: var(--soft-bg-2); border: 1px solid var(--glass-border); border-radius: 16px; font-size: 0.95rem; opacity: 0.9; line-height: 1.5;">
                        Biografía aquí...
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="#" id="botonEnviarMensaje" class="btn py-3 fw-bold d-flex align-items-center justify-content-center gap-2" style="border-radius: 16px; background: var(--accent-primary); color: var(--chat-bg); border: none; transition: all 0.2s ease;">
                        <i class="bi bi-chat-fill"></i> Enviar mensaje
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('entradaBusquedaDirectorio').addEventListener('input', function(e) {
        const termino = e.target.value.toLowerCase();
        const grupos = document.querySelectorAll('[data-grupo-letra]');
        
        grupos.forEach(grupo => {
            const tarjetas = grupo.querySelectorAll('.directory-person-card');
            let tieneVisibles = false;
            
            tarjetas.forEach(tarjeta => {
                const nombre = tarjeta.getAttribute('data-person-name');
                if (nombre.includes(termino)) {
                    tarjeta.style.display = 'flex';
                    tieneVisibles = true;
                } else {
                    tarjeta.style.display = 'none';
                }
            });
            
            grupo.style.display = tieneVisibles ? 'block' : 'none';
        });
    });

    const modalAccionUsuario = document.getElementById('modalAccionUsuario');
    if (modalAccionUsuario) {
        modalAccionUsuario.addEventListener('show.bs.modal', function (evento) {
            const boton = evento.relatedTarget;
            const idUsuario = boton.getAttribute('data-user-id');
            const nombreUsuario = boton.getAttribute('data-user-name');
            const arrobaUsuario = boton.getAttribute('data-user-handle');
            const bioUsuario = boton.getAttribute('data-user-bio');
            const unidoUsuario = boton.getAttribute('data-user-joined');
            const esBloqueado = boton.getAttribute('data-is-blocked');
            
            const mNombre = modalAccionUsuario.querySelector('#modalNombreUsuario');
            const mArroba = modalAccionUsuario.querySelector('#modalArrobaUsuario');
            const mAvatar = modalAccionUsuario.querySelector('#modalAvatarUsuario');
            const mBio = modalAccionUsuario.querySelector('#modalBioUsuario');
            const mUnido = modalAccionUsuario.querySelector('#modalUnidoUsuario');
            const mContenedorBio = modalAccionUsuario.querySelector('#contenedorBioModal');
            const mBotonEnviar = modalAccionUsuario.querySelector('#botonEnviarMensaje');
            
            mNombre.textContent = nombreUsuario;
            mArroba.textContent = arrobaUsuario;
            mUnido.textContent = unidoUsuario;
            
            if (bioUsuario) {
                mBio.textContent = bioUsuario;
                mContenedorBio.style.display = 'block';
            } else {
                mContenedorBio.style.display = 'none';
            }
            
            const iniciales = nombreUsuario.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
            mAvatar.textContent = iniciales;
            
            if (esBloqueado === '1') {
                mBotonEnviar.innerHTML = '<i class="bi bi-slash-circle"></i> Usuario Bloqueado';
                mBotonEnviar.style.background = 'var(--soft-bg-2)';
                mBotonEnviar.style.color = 'var(--text-dim)';
                mBotonEnviar.style.pointerEvents = 'none';
                mBotonEnviar.href = '#';
            } else {
                mBotonEnviar.innerHTML = '<i class="bi bi-chat-fill"></i> Enviar mensaje';
                mBotonEnviar.style.background = 'var(--accent-primary)';
                mBotonEnviar.style.color = 'var(--chat-bg)';
                mBotonEnviar.style.pointerEvents = 'auto';
                mBotonEnviar.href = `../index.php?user=${idUsuario}`;
            }
        });
    }
</script>

<?php renderizar_script_bootstrap(); ?>

</body>
</html>
