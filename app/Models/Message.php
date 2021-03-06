<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/17/18
 * Time: 3:41 AM
 */

namespace App\Models;


use App\Auth\Auth;
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

    protected $conversations;

    protected $fillable = ['has_been_read'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->conversations = new Conversation();
    }

    public function getMessageHistory(int $user1, int $user2, $limit=50) : array
    {
        $data = $this::whereIn('sender', [$user1, $user2])
            ->whereIn('receiver', [$user1, $user2])
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
        $ids = [];
        foreach ($data->toArray() as $message) {
            if ($message['has_been_read'] == false) {
                $ids[] = $message['id'];
            }
        }
        $this->setMessagesAsHasBeenReadByMessageIds($ids);
        if (!empty($data)) {
            return array_reverse($data->toArray());
        } else {
            return [];
        }
    }

    /**
     * @param int $sender
     * @param int $receiver
     * @param string $message
     * @return mixed
     */
    public function setMessage(int $sender, int $receiver, string $message)
    {
        if (!$this->conversations->isConversationExist($sender, $receiver)) {
            $this->conversations->createConversation($sender, $receiver);
            Rating::setRating(Auth::user()->id);
        }
        $this->conversations->setLastMessage($sender, $receiver, $message);
        return $this::insert(
            [
                'sender' => $sender,
                'receiver' => $receiver,
                'message' => $message,
                'conversation_id' => $this->conversations->getConversationId($sender, $receiver)
            ]
        );
    }

    /**
     * @param $messageIds
     */
    private function setMessagesAsHasBeenReadByMessageIds($messageIds)
    {
        $this::whereIn('id', $messageIds)
            ->update(['has_been_read' => 1]);
    }

    /**
     * @param $sender
     * @param $receiver
     * @return bool
     */
    public function setMessagesAsHasBeenRead($sender, $receiver) : bool
    {
        return $this::where('sender', '=', $sender)
            ->where('receiver', '=', $receiver)
            ->orderBy('created_at', 'desc')
            ->get()
            ->first()
            ->update(['has_been_read' => 1]);
    }

//        public function conversation()
//        {
//            return $this->hasOne('App\\Models\\Conversation', 'conversation_id');
//        }

}