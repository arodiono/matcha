<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/27/18
 * Time: 10:31 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    protected $table = 'blocked_users';

    public function blockUser(int $firstUserId, int $secondUserId)
    {
        $this::insert(
            ['user_id_1' => $firstUserId],
            ['user_id_2' => $secondUserId]
         );
    }

    public function getBlockedUsersForUser(int $userId) : array
    {
        $blockData = $this::where('user_id_1', '=', $userId)
            ->orWhere('user_id_2', '=', $userId)
            ->get();
        $blockedUsers = [];
        foreach ($blockData as $data)
        {
            $blockedUsers[] = $data->user_id_1 === $userId ? $data->user_id_2 : $data->user_id_1;
        }
        $blockedUsers[] = $userId;
        return $blockedUsers;
    }

}