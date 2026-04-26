CREATE DATABASE IF NOT EXISTS looply_db;
USE looply_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_one INT NOT NULL,
    user_two INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cleared_at_user_one TIMESTAMP NULL DEFAULT NULL,
    cleared_at_user_two TIMESTAMP NULL DEFAULT NULL,
    hidden_by_user_one BOOLEAN DEFAULT FALSE,
    hidden_by_user_two BOOLEAN DEFAULT FALSE,
    UNIQUE KEY unique_users (user_one, user_two),
    FOREIGN KEY (user_one) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_two) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_block (blocker_id, blocked_id),
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (full_name, username, email, password, bio) VALUES
('Ana Torres', 'ana.torres', 'ana@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Amante de la tecnología y el diseño.'),
('Adrian Mejia', 'adrian.m', 'adrian@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Desarrollador Fullstack en proceso.'),
('Brenda Lopez', 'brenda.lopez', 'brenda@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Hola! Estoy usando Looply.'),
('Carlos Ruiz', 'carlos.ruiz', 'carlos@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Entusiasta de los deportes y el código.'),
('Camila Nunez', 'camila.n', 'camila@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Diseñadora UI/UX.'),
('Daniela Perez', 'daniela.p', 'daniela@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Explorando nuevas apps de chat.'),
('Eduardo Feliz', 'edu.feliz', 'eduardo@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Siempre optimista.'),
('Fatima Diaz', 'fatima.d', 'fatima@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Café y código.'),
('Gabriel Santos', 'gabriel.s', 'gabriel@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Ingeniero de Software.'),
('Helena Cruz', 'helena.cruz', 'helena@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Viajera constante.'),
('Ismael Reyes', 'ismael.r', 'ismael@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Músico y programador.'),
('Juan Perez', 'juan.perez', 'juan@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Primer usuario de Looply.'),
('Karla Moreno', 'karla.m', 'karla@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Marketing Digital.'),
('Luis Gomez', 'luis.gomez', 'luis@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Fanático del minimalismo.'),
('Maria Garcia', 'maria.g', 'maria@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Hablemos de código.'),
('Natalia Vega', 'natalia.v', 'natalia@example.com',
  '$2y$10$8K1p/a0dxv.p8.v2yK.mbeX6L2B6C8uB8O6C8uB8O6C8uB8O6C8uB', 'Frontend Developer.');


-- =============================================================
--  CONSULTAS PREPARADAS DE LA APLICACIÓN
--  Referencia completa de todas las queries usadas en el código.
--  Archivo: includes/queries.php (fuente) → Vistas PHP (uso)
-- =============================================================


-- ─── USUARIOS ────────────────────────────────────────────────

-- Obtener un usuario por su ID
PREPARE get_user_by_id FROM
    'SELECT * FROM users WHERE id = ?';

-- Obtener un usuario por su email (login)
PREPARE get_user_by_email FROM
    'SELECT * FROM users WHERE email = ?';

-- Verificar si un username ya existe (excluyendo al usuario actual)
PREPARE check_username_taken FROM
    'SELECT id FROM users WHERE username = ? AND id != ?';

-- Verificar si un email ya existe (excluyendo al usuario actual)
PREPARE check_email_taken FROM
    'SELECT id FROM users WHERE email = ? AND id != ?';

-- Registrar un nuevo usuario
PREPARE insert_user FROM
    'INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)';

-- Actualizar el perfil de un usuario
PREPARE update_user_profile FROM
    'UPDATE users SET full_name = ?, username = ?, email = ?, bio = ? WHERE id = ?';

-- Listar todos los usuarios excepto el actual (directorio)
PREPARE get_all_users_except_me FROM
    'SELECT id, full_name, username FROM users WHERE id != ? ORDER BY full_name ASC';


-- ─── CONVERSACIONES ──────────────────────────────────────────

-- Obtener una conversación entre dos usuarios
PREPARE get_conversation FROM
    'SELECT * FROM conversations WHERE user_one = ? AND user_two = ?';

-- Crear una nueva conversación
PREPARE insert_conversation FROM
    'INSERT INTO conversations (user_one, user_two) VALUES (?, ?)';

-- Actualizar la fecha del último mensaje de una conversación
PREPARE touch_conversation FROM
    'UPDATE conversations SET last_message_at = CURRENT_TIMESTAMP WHERE id = ?';

-- Limpiar mensajes para un usuario (soft delete - user_one)
PREPARE clear_chat_user_one FROM
    'UPDATE conversations SET cleared_at_user_one = CURRENT_TIMESTAMP WHERE user_one = ? AND user_two = ?';

-- Limpiar mensajes para un usuario (soft delete - user_two)
PREPARE clear_chat_user_two FROM
    'UPDATE conversations SET cleared_at_user_two = CURRENT_TIMESTAMP WHERE user_one = ? AND user_two = ?';

-- Eliminar chat de la lista del usuario (soft delete - user_one)
PREPARE delete_chat_user_one FROM
    'UPDATE conversations SET cleared_at_user_one = CURRENT_TIMESTAMP, hidden_by_user_one = TRUE WHERE user_one = ? AND user_two = ?';

-- Eliminar chat de la lista del usuario (soft delete - user_two)
PREPARE delete_chat_user_two FROM
    'UPDATE conversations SET cleared_at_user_two = CURRENT_TIMESTAMP, hidden_by_user_two = TRUE WHERE user_one = ? AND user_two = ?';

-- Sidebar: contactos con mensajes visibles para el usuario (usa parámetros posicionales)
-- Nota: Esta query usa 9 parámetros con el mismo valor (my_id) por limitación de PDO emulate_prepares=false
PREPARE get_sidebar_contacts FROM
    'SELECT u.id, u.full_name, u.username,
        (SELECT m.message FROM messages m
         JOIN conversations c ON m.conversation_id = c.id
         WHERE ((c.user_one = ? AND c.user_two = u.id) OR (c.user_one = u.id AND c.user_two = ?))
           AND m.created_at > COALESCE(IF(c.user_one = ?, c.cleared_at_user_one, c.cleared_at_user_two), ''1970-01-01'')
         ORDER BY m.created_at DESC LIMIT 1
        ) as last_message
     FROM users u
     JOIN conversations conv ON (u.id = conv.user_one OR u.id = conv.user_two)
     WHERE (conv.user_one = ? OR conv.user_two = ?)
       AND u.id != ?
       AND ((conv.user_one = ? AND conv.hidden_by_user_one = FALSE)
            OR (conv.user_two = ? AND conv.hidden_by_user_two = FALSE))
       AND EXISTS (
           SELECT 1 FROM messages m
           WHERE m.conversation_id = conv.id
             AND m.created_at > COALESCE(IF(conv.user_one = ?, conv.cleared_at_user_one, conv.cleared_at_user_two), ''1970-01-01'')
       )
     ORDER BY conv.last_message_at DESC';


-- ─── MENSAJES ────────────────────────────────────────────────

-- Obtener mensajes de una conversación (sin filtro de fecha)
PREPARE get_messages FROM
    'SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC';

-- Obtener mensajes de una conversación desde una fecha (post soft-delete)
PREPARE get_messages_since FROM
    'SELECT * FROM messages WHERE conversation_id = ? AND created_at > ? ORDER BY created_at ASC';

-- Insertar un nuevo mensaje
PREPARE insert_message FROM
    'INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)';


-- ─── BLOQUEOS ────────────────────────────────────────────────

-- Verificar si existe algún bloqueo entre dos usuarios
PREPARE get_blocks_between FROM
    'SELECT blocker_id, blocked_id FROM blocks
     WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)';

-- Bloquear a un usuario
PREPARE block_user FROM
    'INSERT IGNORE INTO blocks (blocker_id, blocked_id) VALUES (?, ?)';

-- Desbloquear a un usuario
PREPARE unblock_user FROM
    'DELETE FROM blocks WHERE blocker_id = ? AND blocked_id = ?';