<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/20/18
 * Time: 10:31 PM
 */

namespace App\Models;


use function FastRoute\TestFixtures\empty_options_cached;
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

    public function getAllConversations(int $userId) : array
    {
        $data = $this::select('id')
            ->where('user_id_1', '=', $userId)
            ->orWhere('user_id_2', '=', $userId)
            ->get();
        if ($data !== null) {
            return $data->toArray();
        } else {
            return [];
        }
    }
}