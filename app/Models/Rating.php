<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 4/11/18
 * Time: 8:50 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    public function calculateRating($user_id)
    {
        $week = date('Y-m-d', time() - (7 * 24 * 60 * 60));
        $month = date('Y-m-d', time() - (30 * 24 * 60 * 60));
        $rating = [];
        $rating['photo'] = $this::from('users')
            ->selectRaw('count(photos.id) as photo')
            ->where('users.id', $user_id)
            ->leftJoin('photos', 'users.id', '=', 'photos.user_id')
            ->get()
            ->first()
            ->toArray()['photo'];
        $rating['convs_all'] = $this::from('conversations')
            ->selectRaw('count(id) as convs_all')
            ->where('user_id_1', $user_id)
            ->orWhere('user_id_2', $user_id)
            ->get()
            ->first()
            ->toArray()['convs_all'];
        $rating['convs_week'] = $this::from('conversations')
            ->selectRaw('count(id) as convs_week')
            ->whereDate('updated_at', '>', $week)
            ->where('user_id_1', $user_id)
            ->orWhere('user_id_2', $user_id)
            ->get()
            ->first()
            ->toArray()['convs_week'];
        $rating['convs_month'] = $this::from('conversations')
            ->selectRaw('count(id) as convs_month')
            ->whereDate('updated_at', '>', $month)
            ->where('user_id_1', $user_id)
            ->orWhere('user_id_2', $user_id)
            ->get()
            ->first()
            ->toArray()['convs_month'];
        $rating['bio'] = $this::from('users')
            ->selectRaw('count(id) as bio')
            ->where('id', $user_id)
            ->where('bio', '!=', null)
            ->get()
            ->first()
            ->toArray()['bio'];
        $rating['likes_set'] = $this::from('likes')
            ->selectRaw('count(id) as likes_set')
            ->where('who_id', $user_id)
            ->get()
            ->first()
            ->toArray()['likes_set'];
        $rating['likes_get_week'] = $this::from('likes')
            ->selectRaw('count(id) as likes_week')
            ->whereDate('updated_at', '>', $week)
            ->where('whom_id', $user_id)
            ->get()
            ->first()
            ->toArray()['likes_week'];
        $rating['likes_get_month'] = $this::from('likes')
            ->selectRaw('count(id) as likes_month')
            ->whereDate('updated_at', '>', $month)
            ->where('whom_id', $user_id)
            ->get()
            ->first()
            ->toArray()['likes_month'];

        $res = $rating['photo'] * 0.05 + ($rating['convs_all'] % $rating['convs_all']) * 0.05
        ~r(0 % 0);
    }

    // Photo 25%
    //
}