<?php

function c_obtener_usuario_por_id(PDO $bd, int $id): array|false
{
    $sentencia = $bd->prepare('SELECT * FROM users WHERE id = ?');
    $sentencia->execute([$id]);
    return $sentencia->fetch();
}

function c_obtener_usuario_por_email(PDO $bd, string $email): array|false
{
    $sentencia = $bd->prepare('SELECT * FROM users WHERE email = ?');
    $sentencia->execute([$email]);
    return $sentencia->fetch();
}

function c_verificar_nombre_usuario_tomado(PDO $bd, string $usuario, int $id_excluido): array|false
{
    $sentencia = $bd->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
    $sentencia->execute([$usuario, $id_excluido]);
    return $sentencia->fetch();
}

function c_verificar_email_tomado(PDO $bd, string $email, int $id_excluido): array|false
{
    $sentencia = $bd->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $sentencia->execute([$email, $id_excluido]);
    return $sentencia->fetch();
}

function c_insertar_usuario(PDO $bd, string $nombre_completo, string $usuario, string $email, string $contrasena): void
{
    $sentencia = $bd->prepare('INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)');
    $sentencia->execute([$nombre_completo, $usuario, $email, $contrasena]);
}

function c_actualizar_perfil_usuario(PDO $bd, string $nombre_completo, string $usuario, string $email, string $biografia, int $id): void
{
    $sentencia = $bd->prepare('UPDATE users SET full_name = ?, username = ?, email = ?, bio = ? WHERE id = ?');
    $sentencia->execute([$nombre_completo, $usuario, $email, $biografia, $id]);
}

function c_obtener_todos_los_usuarios_menos_yo(PDO $bd, int $mi_id): array
{
    $sentencia = $bd->prepare('SELECT id, full_name, username FROM users WHERE id != ? ORDER BY full_name ASC');
    $sentencia->execute([$mi_id]);
    return $sentencia->fetchAll();
}


// ═══════════════════════════════════════════════════════════════
//  CONVERSACIONES
// ═══════════════════════════════════════════════════════════════

function c_obtener_conversacion(PDO $bd, int $usuario_uno, int $usuario_dos): array|false
{
    $sentencia = $bd->prepare('SELECT * FROM conversations WHERE user_one = ? AND user_two = ?');
    $sentencia->execute([$usuario_uno, $usuario_dos]);
    return $sentencia->fetch();
}

function c_insertar_conversacion(PDO $bd, int $usuario_uno, int $usuario_dos): string
{
    $sentencia = $bd->prepare('INSERT INTO conversations (user_one, user_two) VALUES (?, ?)');
    $sentencia->execute([$usuario_uno, $usuario_dos]);
    return $bd->lastInsertId();
}

function c_actualizar_fecha_conversacion(PDO $bd, int $id_conv): void
{
    $sentencia = $bd->prepare('UPDATE conversations SET last_message_at = CURRENT_TIMESTAMP WHERE id = ?');
    $sentencia->execute([$id_conv]);
}

function c_limpiar_chat(PDO $bd, int $usuario_uno, int $usuario_dos, bool $es_usuario_uno): void
{
    $columna = $es_usuario_uno ? 'cleared_at_user_one' : 'cleared_at_user_two';
    $sentencia = $bd->prepare("UPDATE conversations SET $columna = CURRENT_TIMESTAMP WHERE user_one = ? AND user_two = ?");
    $sentencia->execute([$usuario_uno, $usuario_dos]);
}

function c_eliminar_chat(PDO $bd, int $usuario_uno, int $usuario_dos, bool $es_usuario_uno): void
{
    $col_limpia = $es_usuario_uno ? 'cleared_at_user_one' : 'cleared_at_user_two';
    $col_oculta  = $es_usuario_uno ? 'hidden_by_user_one'  : 'hidden_by_user_two';
    $sentencia = $bd->prepare("UPDATE conversations SET $col_limpia = CURRENT_TIMESTAMP, $col_oculta = TRUE WHERE user_one = ? AND user_two = ?");
    $sentencia->execute([$usuario_uno, $usuario_dos]);
}

/**
 * Devuelve los contactos visibles en el sidebar para el usuario dado.
 */
function c_obtener_contactos_sidebar(PDO $bd, int $mi_id): array
{
    $sentencia = $bd->prepare("
        SELECT u.id, u.full_name, u.username,
        (
            SELECT m.message FROM messages m
            JOIN conversations c ON m.conversation_id = c.id
            WHERE ((c.user_one = :uid1 AND c.user_two = u.id) OR (c.user_one = u.id AND c.user_two = :uid2))
              AND m.created_at > COALESCE(IF(c.user_one = :uid3, c.cleared_at_user_one, c.cleared_at_user_two), '1970-01-01')
            ORDER BY m.created_at DESC LIMIT 1
        ) as last_message
        FROM users u
        JOIN conversations conv ON (u.id = conv.user_one OR u.id = conv.user_two)
        WHERE (conv.user_one = :uid4 OR conv.user_two = :uid5)
          AND u.id != :uid6
          AND (
              (conv.user_one = :uid7 AND conv.hidden_by_user_one = FALSE) OR
              (conv.user_two = :uid8 AND conv.hidden_by_user_two = FALSE)
          )
          AND EXISTS (
              SELECT 1 FROM messages m
              WHERE m.conversation_id = conv.id
                AND m.created_at > COALESCE(IF(conv.user_one = :uid9, conv.cleared_at_user_one, conv.cleared_at_user_two), '1970-01-01')
          )
        ORDER BY conv.last_message_at DESC
    ");
    $sentencia->execute([
        'uid1' => $mi_id, 'uid2' => $mi_id, 'uid3' => $mi_id,
        'uid4' => $mi_id, 'uid5' => $mi_id, 'uid6' => $mi_id,
        'uid7' => $mi_id, 'uid8' => $mi_id, 'uid9' => $mi_id,
    ]);
    return $sentencia->fetchAll();
}


// ═══════════════════════════════════════════════════════════════
//  MENSAJES
// ═══════════════════════════════════════════════════════════════

/**
 * Obtiene los mensajes de una conversación.
 */
function c_obtener_mensajes(PDO $bd, int $id_conv, ?string $desde = null): array
{
    if ($desde) {
        $sentencia = $bd->prepare('SELECT * FROM messages WHERE conversation_id = ? AND created_at > ? ORDER BY created_at ASC');
        $sentencia->execute([$id_conv, $desde]);
    } else {
        $sentencia = $bd->prepare('SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC');
        $sentencia->execute([$id_conv]);
    }
    return $sentencia->fetchAll();
}

function c_insertar_mensaje(PDO $bd, int $id_conv, int $id_remitente, string $mensaje): void
{
    $sentencia = $bd->prepare('INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)');
    $sentencia->execute([$id_conv, $id_remitente, $mensaje]);
}


// ═══════════════════════════════════════════════════════════════
//  BLOQUEOS
// ═══════════════════════════════════════════════════════════════

function c_obtener_bloqueos_entre(PDO $bd, int $mi_id, int $otro_id): array
{
    $sentencia = $bd->prepare('SELECT blocker_id, blocked_id FROM blocks WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)');
    $sentencia->execute([$mi_id, $otro_id, $otro_id, $mi_id]);
    return $sentencia->fetchAll();
}

function c_bloquear_usuario(PDO $bd, int $id_bloqueador, int $id_bloqueado): void
{
    $sentencia = $bd->prepare('INSERT IGNORE INTO blocks (blocker_id, blocked_id) VALUES (?, ?)');
    $sentencia->execute([$id_bloqueador, $id_bloqueado]);
}

function c_desbloquear_usuario(PDO $bd, int $id_bloqueador, int $id_bloqueado): void
{
    $sentencia = $bd->prepare('DELETE FROM blocks WHERE blocker_id = ? AND blocked_id = ?');
    $sentencia->execute([$id_bloqueador, $id_bloqueado]);
}
