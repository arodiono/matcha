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

    private $message = ['message'];

    private $ntf = ['like', 'unlike', 'mutual', 'visit', 'ntf'];

    protected $clients;

    private $directMessageConnections = [];

    private $notificationConnections = [];

    private $done = 0;

    private $onlineUrl = '';

    const ONLINE = 1;

    const OFFLINE = 0;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    /**
     * @param string $username
     * @return bool]
     */
    private function isConnectionExist(string $username) : bool
    {
        foreach ($this->directMessageConnections as $item)
        {
            if (array_key_exists($username, $item)) {
                return true;
            }
        }
        foreach ($this->notificationConnections as $item)
        {
            if (array_key_exists($username, $item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param bool $status
     * @param string $username]
     */
    private function setOnlineStatus(bool $status, string $username)
    {
        if (!$this->isConnectionExist($username)) {
            $data = ['username' => $username, 'status' => $status];
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                ]
            ];
            $context  = stream_context_create($options);
            file_get_contents($this->onlineUrl, false, $context);
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Connection %d sending message "%s" to other connections' . "\n"
            , $from->resourceId, $msg);
        $data = json_decode($msg);
        if (property_exists($data, 'auth') && property_exists($data, 'type') && property_exists($data, 'host')) {
            $this->onlineUrl = $data->host;
            $this->setOnlineStatus(self::ONLINE, $data->auth);
            if (in_array($data->type, $this->message)) {
                $this->directMessageConnections[$data->auth] = $from;
            } elseif (in_array($data->type, $this->ntf)) {
                $this->notificationConnections[$data->auth] = $from;
            }
        } else {
            if (property_exists($data, 'to')){
                $body = ['type' => $data->type, 'text' => $data->msg, 'from' => $data->auth];
                if (array_key_exists($data->to, $this->directMessageConnections)) {
                    $this->directMessageConnections[$data->to]->send(json_encode(['message' => $body]));
                } else {
                    if (array_key_exists($data->to, $this->notificationConnections)) {
                        $this->notificationConnections[$data->to]->send(json_encode(['notification' => $body]));
                    }
                }
            }
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn) {
        foreach ($this->notificationConnections as $key => $user)
        {

            if ($user == $conn) {
                unset($this->notificationConnections[$key]);
                $this->setOnlineStatus(self::OFFLINE, $key);
                $ts = $key;
                echo "Deleted {$ts} has disconnected\n";
            }
        }
        foreach ($this->directMessageConnections as $key => $user)
        {
            if ($user == $conn) {
                unset($this->directMessageConnections[$key]);
                $username = $key;
                $this->setOnlineStatus(self::OFFLINE, $username);
                echo "Deleted {$username} has disconnected\n";
            }
        }
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}