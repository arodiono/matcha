<?php

namespace App\Models;


use App\Auth\Auth;
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
        return Auth::user()->conversations()->with('user_1', 'user_2')->get()->toArray();
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

    public function user_1()
    {
        return $this->belongsTo('App\Models\User', 'user_id_1');

    }

    public function user_2()
    {
        return $this->belongsTo('App\Models\User', 'user_id_2');

    }
}