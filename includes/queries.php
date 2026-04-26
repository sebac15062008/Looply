<?php
/**
 * includes/queries.php
 * ─────────────────────────────────────────────────────────────
 * Capa de acceso a datos (DAL) de Looply.
 * Todas las consultas SQL del proyecto están aquí centralizadas.
 * Ningún archivo PHP debe escribir SQL directamente.
 * ─────────────────────────────────────────────────────────────
 */


// ═══════════════════════════════════════════════════════════════
//  USUARIOS
// ═══════════════════════════════════════════════════════════════

function q_get_user_by_id(PDO $db, int $id): array|false
{
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function q_get_user_by_email(PDO $db, string $email): array|false
{
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function q_check_username_taken(PDO $db, string $username, int $exclude_id): array|false
{
    $stmt = $db->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
    $stmt->execute([$username, $exclude_id]);
    return $stmt->fetch();
}

function q_check_email_taken(PDO $db, string $email, int $exclude_id): array|false
{
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $stmt->execute([$email, $exclude_id]);
    return $stmt->fetch();
}

function q_insert_user(PDO $db, string $full_name, string $username, string $email, string $password): void
{
    $stmt = $db->prepare('INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)');
    $stmt->execute([$full_name, $username, $email, $password]);
}

function q_update_user_profile(PDO $db, string $full_name, string $username, string $email, string $bio, int $id): void
{
    $stmt = $db->prepare('UPDATE users SET full_name = ?, username = ?, email = ?, bio = ? WHERE id = ?');
    $stmt->execute([$full_name, $username, $email, $bio, $id]);
}

function q_get_all_users_except_me(PDO $db, int $my_id): array
{
    $stmt = $db->prepare('SELECT id, full_name, username FROM users WHERE id != ? ORDER BY full_name ASC');
    $stmt->execute([$my_id]);
    return $stmt->fetchAll();
}


// ═══════════════════════════════════════════════════════════════
//  CONVERSACIONES
// ═══════════════════════════════════════════════════════════════

function q_get_conversation(PDO $db, int $user_one, int $user_two): array|false
{
    $stmt = $db->prepare('SELECT * FROM conversations WHERE user_one = ? AND user_two = ?');
    $stmt->execute([$user_one, $user_two]);
    return $stmt->fetch();
}

function q_insert_conversation(PDO $db, int $user_one, int $user_two): string
{
    $stmt = $db->prepare('INSERT INTO conversations (user_one, user_two) VALUES (?, ?)');
    $stmt->execute([$user_one, $user_two]);
    return $db->lastInsertId();
}

function q_touch_conversation(PDO $db, int $conv_id): void
{
    $stmt = $db->prepare('UPDATE conversations SET last_message_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$conv_id]);
}

function q_clear_chat(PDO $db, int $user_one, int $user_two, bool $is_user_one): void
{
    $col  = $is_user_one ? 'cleared_at_user_one' : 'cleared_at_user_two';
    $stmt = $db->prepare("UPDATE conversations SET $col = CURRENT_TIMESTAMP WHERE user_one = ? AND user_two = ?");
    $stmt->execute([$user_one, $user_two]);
}

function q_delete_chat(PDO $db, int $user_one, int $user_two, bool $is_user_one): void
{
    $col_cleared = $is_user_one ? 'cleared_at_user_one' : 'cleared_at_user_two';
    $col_hidden  = $is_user_one ? 'hidden_by_user_one'  : 'hidden_by_user_two';
    $stmt = $db->prepare("UPDATE conversations SET $col_cleared = CURRENT_TIMESTAMP, $col_hidden = TRUE WHERE user_one = ? AND user_two = ?");
    $stmt->execute([$user_one, $user_two]);
}

/**
 * Devuelve los contactos visibles en el sidebar para el usuario dado.
 * Respeta soft-deletes (cleared_at) y chats ocultos (hidden_by).
 */
function q_get_sidebar_contacts(PDO $db, int $my_id): array
{
    $stmt = $db->prepare("
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
    $stmt->execute([
        'uid1' => $my_id, 'uid2' => $my_id, 'uid3' => $my_id,
        'uid4' => $my_id, 'uid5' => $my_id, 'uid6' => $my_id,
        'uid7' => $my_id, 'uid8' => $my_id, 'uid9' => $my_id,
    ]);
    return $stmt->fetchAll();
}


// ═══════════════════════════════════════════════════════════════
//  MENSAJES
// ═══════════════════════════════════════════════════════════════

/**
 * Obtiene los mensajes de una conversación.
 * Si se pasa $since (TIMESTAMP), sólo devuelve los posteriores a esa fecha (post soft-delete).
 */
function q_get_messages(PDO $db, int $conv_id, ?string $since = null): array
{
    if ($since) {
        $stmt = $db->prepare('SELECT * FROM messages WHERE conversation_id = ? AND created_at > ? ORDER BY created_at ASC');
        $stmt->execute([$conv_id, $since]);
    } else {
        $stmt = $db->prepare('SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC');
        $stmt->execute([$conv_id]);
    }
    return $stmt->fetchAll();
}

function q_insert_message(PDO $db, int $conv_id, int $sender_id, string $message): void
{
    $stmt = $db->prepare('INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)');
    $stmt->execute([$conv_id, $sender_id, $message]);
}


// ═══════════════════════════════════════════════════════════════
//  BLOQUEOS
// ═══════════════════════════════════════════════════════════════

function q_get_blocks_between(PDO $db, int $my_id, int $other_id): array
{
    $stmt = $db->prepare('SELECT blocker_id, blocked_id FROM blocks WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)');
    $stmt->execute([$my_id, $other_id, $other_id, $my_id]);
    return $stmt->fetchAll();
}

function q_block_user(PDO $db, int $blocker_id, int $blocked_id): void
{
    $stmt = $db->prepare('INSERT IGNORE INTO blocks (blocker_id, blocked_id) VALUES (?, ?)');
    $stmt->execute([$blocker_id, $blocked_id]);
}

function q_unblock_user(PDO $db, int $blocker_id, int $blocked_id): void
{
    $stmt = $db->prepare('DELETE FROM blocks WHERE blocker_id = ? AND blocked_id = ?');
    $stmt->execute([$blocker_id, $blocked_id]);
}
