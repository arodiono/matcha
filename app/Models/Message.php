<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/17/18
 * Time: 3:41 AM
 */

namespace App\Models;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Message
 * @package App\Models
 */

class Message extends Model
{
    /**
     * @param int $user1
     * @param int $user2
     * @param int $limit
     * @return array
     */

    public function getMessageHistory(int $user1, int $user2, $limit=50) : array
    {
        $data = $this::select('*')
            ->whereIn('sender', [$user1, $user2])
            ->whereIn('receiver', [$user1, $user2])
            ->limit($limit)
            ->get();
        $ids = [];
        foreach ($data as $message) {
            if ($message['has_been_read'] == false) {
                $ids[] = $message['id'];
            }
        }
        $this->setMessagesAsHasBeenRead($ids);
        if (!empty($data)) {
            return $data->toArray();
        } else {
            return [];
        }
    }

    public function setMessage(int $sender, int $receiver, string $message)
    {
        return $this::insert(
            [
                'sender' => $sender,
                'receiver' => $receiver,
                'message' => $message
            ]
        );
    }

    private function setMessagesAsHasBeenRead($messageIds) {
        $this::whereIn('id', $messageIds)
            ->update(['has_been_read' => true]);
    }
}