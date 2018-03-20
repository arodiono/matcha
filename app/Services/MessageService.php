<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/13/18
 * Time: 8:30 PM
 */

namespace App\Services;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class MessageService implements MessageComponentInterface
{
    protected $clients;

    private $directMessageConnections = [];

    private $notificationConnections = [];

    private $done = 0;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Connection %d sending message "%s" to other connections' . "\n"
            , $from->resourceId, $msg);
        $data = json_decode($msg);
        if (property_exists($data, 'auth') && property_exists($data, 'type')) {
            if ($data->type == 'msg') {
                $this->directMessageConnections[] = [$data->auth => $from];
            } elseif ($data->type == 'ntf') {
                $this->notificationConnections[] = [$data->auth => $from];
            }
        } else {
            if (property_exists($data, 'to') && property_exists($data, 'msg')){
                foreach ($this->directMessageConnections as $user) {
                    if (array_key_exists($data->to, $user)) {
                        $user[$data->to]->send($data->msg);
                        $this->done = 1;
                    }
                }
                if ($this->done === 0) {
                    foreach ($this->notificationConnections as $user) {
                        if (array_key_exists($data->to, $user)) {
                            $user[$data->to]->send($data->msg);
                        }
                    }
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        foreach ($this->notificationConnections as $key => $user)
        {
            if (array_values($user)[0] == $conn) {
                unset($this->notificationConnections[$key]);
                $ts = array_keys($user)[0];
                echo "Deleted {$ts} has disconnected\n";
            }
        }
        foreach ($this->directMessageConnections as $key => $user)
        {
            if (array_values($user)[0] == $conn) {
                unset($this->directMessageConnections[$key]);
                $ts = array_keys($user)[0];
                echo "Deleted {$ts} has disconnected\n";
            }
        }
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}