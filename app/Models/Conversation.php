<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/20/18
 * Time: 10:31 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Conversation
 * @package App\Models
 */
class Conversation extends Model
{

    /**
     * @param int $sender
     * @param int $receiver
     * @return bool
     */
    public function isConversationExist(int $sender, int $receiver) : bool
    {
        $conversation = $this::select('*')
        ->whereIn('user_id_1', [$sender, $receiver])
        ->whereIn('user_id_2', [$sender, $receiver])
        ->get()
        ->first();
        return $conversation === null ? false : true;
    }

    /**
     * @param int $sender
     * @param int $receiver
     */
    public function createConversation(int $sender, int $receiver)
    {
        $this::insert(
            [
                'user_id_1' => $sender,
                'user_id_2' => $receiver
            ]
        );
    }

    /**
     * @param int $sender
     * @param int $receiver
     * @return int
     */
    public function getConversationId(int $sender, int $receiver) : int
    {
        return $this::select('id')
            ->whereIn('user_id_1', [$sender, $receiver])
            ->whereIn('user_id_2', [$sender, $receiver])
            ->get()
            ->first()
            ->id;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getAllConversations(int $userId) : array
    {
        $data = $this::select('user_id_1', 'user_id_2', 'last_message')
            ->where('user_id_1', '=', $userId)
            ->orWhere('user_id_2', '=', $userId)
            ->latest()
            ->get();
        if ($data !== null) {
            return $data->toArray();
        } else {
            return [];
        }
    }

    /**
     * @param int $sender
     * @param int $receiver
     * @param string $message
     */
    public function setLastMessage(int $sender, int $receiver, string $message)
    {
        $this::whereIn('user_id_1', [$sender, $receiver])
            ->whereIn('user_id_2', [$sender, $receiver])
            ->update(['last_message' => $message]);
    }
}