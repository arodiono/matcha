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
    const LIKE = 'like';
    const UNLIKE = 'unlike';
    const MUTUAL = 'mutually';
    const VISIT = 'visit';

    protected $table = 'notifications';

    protected $fillable = ['has_been_read'];

    public function who()
    {
        return $this->hasOne('App\Models\User', 'id','who_id');
    }

    static public function setNotification(int $senderId, int $receiverId, string $type)
    {
        self::insert(
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