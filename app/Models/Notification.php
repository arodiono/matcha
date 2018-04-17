<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 4/1/18
 * Time: 9:36 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['has_been_read'];

    public function sender()
    {
        return $this->hasOne('App\Models\User', 'id','sender_id');
    }

    public function setNotification(int $senderId, int $receiverId, string $type)
    {
        $this::insert(
            [
                'who_id' => $senderId,
                'whom_id' => $receiverId,
                'type' => $type
            ]
        );
    }

    public function setHasBeenRead($ids)
    {
        $this::whereIn('id', $ids)
            ->update(['has_been_read' => 1]);
    }
}