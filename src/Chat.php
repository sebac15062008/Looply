<?php
namespace Looply;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/queries.php';

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $user_connections; // To map user_id to connection

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->user_connections = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data) return;

        switch ($data['type']) {
            case 'auth':
                $this->user_connections[$data['user_id']] = $from;
                echo "User {$data['user_id']} authenticated\n";
                break;

            case 'message':
                $sender_id   = (int)$data['sender_id'];
                $receiver_id = (int)$data['receiver_id'];
                $message     = $data['message'];

                $db      = (new \connectionDatabase())->con;
                $user_one = min($sender_id, $receiver_id);
                $user_two = max($sender_id, $receiver_id);

                $conv = q_get_conversation($db, $user_one, $user_two);

                if (!$conv) {
                    $conversation_id = q_insert_conversation($db, $user_one, $user_two);
                } else {
                    $conversation_id = $conv['id'];
                    q_touch_conversation($db, $conversation_id);
                }

                q_insert_message($db, (int)$conversation_id, $sender_id, $message);

                if (isset($this->user_connections[$receiver_id])) {
                    $this->user_connections[$receiver_id]->send(json_encode([
                        'type'       => 'message',
                        'sender_id'  => $sender_id,
                        'message'    => $message,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]));
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        foreach ($this->user_connections as $user_id => $c) {
            if ($c === $conn) {
                unset($this->user_connections[$user_id]);
                break;
            }
        }
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
