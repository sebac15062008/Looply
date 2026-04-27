<?php
namespace Looply;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/queries.php';

class ChatServidor implements MessageComponentInterface {
    protected $clientes;
    protected $conexiones_usuarios; // Mapa de id_usuario => conexion

    public function __construct() {
        $this->clientes = new \SplObjectStorage;
        $this->conexiones_usuarios = [];
    }

    public function onOpen(ConnectionInterface $conexion) {
        $this->clientes->attach($conexion);
        echo "¡Nueva conexión! ({$conexion->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $desde, $mensaje_recibido) {
        $datos = json_decode($mensaje_recibido, true);
        if (!$datos) return;

        switch ($datos['type']) {
            case 'auth':
                $this->conexiones_usuarios[$datos['user_id']] = $desde;
                echo "Usuario {$datos['user_id']} autenticado\n";
                break;

            case 'message':
                $id_remitente    = (int)$datos['sender_id'];
                $id_destinatario = (int)$datos['receiver_id'];
                $texto_mensaje   = $datos['message'];

                $bd               = (new \ConexionBaseDatos())->con;
                $usuario_uno      = min($id_remitente, $id_destinatario);
                $usuario_dos      = max($id_remitente, $id_destinatario);

                $conversacion = c_obtener_conversacion($bd, $usuario_uno, $usuario_dos);

                if (!$conversacion) {
                    $id_conversacion = c_insertar_conversacion($bd, $usuario_uno, $usuario_dos);
                } else {
                    $id_conversacion = $conversacion['id'];
                    c_actualizar_fecha_conversacion($bd, $id_conversacion);
                }

                c_insertar_mensaje($bd, (int)$id_conversacion, $id_remitente, $texto_mensaje);

                if (isset($this->conexiones_usuarios[$id_destinatario])) {
                    $this->conexiones_usuarios[$id_destinatario]->send(json_encode([
                        'type'       => 'message',
                        'sender_id'  => $id_remitente,
                        'message'    => $texto_mensaje,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]));
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conexion) {
        $this->clientes->detach($conexion);
        foreach ($this->conexiones_usuarios as $id_usuario => $c) {
            if ($c === $conexion) {
                unset($this->conexiones_usuarios[$id_usuario]);
                break;
            }
        }
        echo "La conexión {$conexion->resourceId} se ha desconectado\n";
    }

    public function onError(ConnectionInterface $conexion, \Exception $e) {
        echo "Ha ocurrido un error: {$e->getMessage()}\n";
        $conexion->close();
    }
}
