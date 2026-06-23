<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/db.php';

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $groups;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->groups = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        global $pdo;
        $data = json_decode($msg, true);
        $group_id = $data['group_id'];
        $user_id = $data['user_id'];
        $message = htmlspecialchars($data['message']);
        $username = $data['username'];

        // Store message in database
        $stmt = $pdo->prepare("
            INSERT INTO messages (study_group_id, user_id, message, created_at)
            VALUES (:group_id, :user_id, :message, NOW())
        ");
        $stmt->execute(['group_id' => $group_id, 'user_id' => $user_id, 'message' => $message]);

        // Update last_active timestamp
        $stmt = $pdo->prepare("UPDATE study_groups SET last_active = NOW() WHERE id = :group_id");
        $stmt->execute(['group_id' => $group_id]);

        // Add client to group
        if (!isset($this->groups[$group_id])) {
            $this->groups[$group_id] = new \SplObjectStorage;
        }
        if (!$this->groups[$group_id]->contains($from)) {
            $this->groups[$group_id]->attach($from);
        }

        // Broadcast message to group
        $message_data = [
            'username' => $username,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        foreach ($this->groups[$group_id] as $client) {
            $client->send(json_encode($message_data));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        foreach ($this->groups as $group_id => $clients) {
            if ($clients->contains($conn)) {
                $clients->detach($conn);
            }
        }
        echo "Connection closed! ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new Chat()
        )
    ),
    8080
);
$server->run();