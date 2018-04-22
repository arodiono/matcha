<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 4/11/18
 * Time: 8:50 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rating extends Model
{
    private static function calculateRating($user_id)
    {
        $week = date('Y-m-d', time() - (7 * 24 * 60 * 60));
        $month = date('Y-m-d', time() - (30 * 24 * 60 * 60));
        $rating = [];
        $rating['photo'] = Rating::from('users')
            ->selectRaw('count(photos.id) as photo')
            ->where('users.id', $user_id)
            ->leftJoin('photos', 'users.id', '=', 'photos.user_id')
            ->get()
            ->first()
            ->toArray()['photo'];
        $rating['convs_all'] = Rating::from('conversations')
            ->selectRaw('count(id) as convs_all')
            ->where('user_id_1', $user_id)
            ->orWhere('user_id_2', $user_id)
            ->get()
            ->first()
            ->toArray()['convs_all'];
        $rating['convs_week'] = Rating::from('conversations')
            ->selectRaw('count(id) as convs_week')
            ->whereDate('updated_at', '>', $week)
            ->where('user_id_1', $user_id)
            ->orWhere('user_id_2', $user_id)
            ->get()
            ->first()
            ->toArray()['convs_week'];
        $rating['convs_month'] = Rating::from('conversations')
            ->selectRaw('count(id) as convs_month')
            ->whereDate('updated_at', '>', $month)
            ->where('user_id_1', $user_id)
            ->orWhere('user_id_2', $user_id)
            ->get()
            ->first()
            ->toArray()['convs_month'];
        $rating['bio'] = Rating::from('users')
            ->selectRaw('count(id) as bio')
            ->where('id', $user_id)
            ->where('bio', '!=', null)
            ->get()
            ->first()
            ->toArray()['bio'];
        $rating['likes_set'] = Rating::from('likes')
            ->selectRaw('count(id) as likes_set')
            ->where('who_id', $user_id)
            ->get()
            ->first()
            ->toArray()['likes_set'];
        $rating['likes_get_week'] = Rating::from('likes')
            ->selectRaw('count(id) as likes_week')
            ->whereDate('updated_at', '>', $week)
            ->where('whom_id', $user_id)
            ->get()
            ->first()
            ->toArray()['likes_week'];
        $rating['likes_get_month'] = Rating::from('likes')
            ->selectRaw('count(id) as likes_month')
            ->whereDate('updated_at', '>', $month)
            ->where('whom_id', $user_id)
            ->get()
            ->first()
            ->toArray()['likes_month'];

        $convs_all = $rating['convs_all'] == 0 ? 0 : 5;
        $convs_week = $rating['convs_week'] < 5 ? 0 : 10;
        $convs_month = $rating['convs_month'] < 5 ? 0 : 5;
        $likes_set = $rating['likes_set'] < 5 ? 0 : 10;
        $likes_week = $rating['likes_get_week'] == 0 ? 0 : 7;
        $likes_month = $rating['likes_get_month'] == 0 ? 0 : 3;
        $matches_week = Rating::getMatches(1022, $week) == 0 ? 0 : 5;
        $matches_month = Rating::getMatches(1022, $month) == 0 ? 0 : 5;
        $rate = $rating['photo'] * 5 +
            $convs_all +
            $convs_week +
            $convs_month +
            $rating['bio'] * 25 +
            $likes_set +
            $likes_month +
            $likes_week +
            $matches_month +
            $matches_week;
        return $rate;
    }

    private static function getMatches($user_id, $period)
    {
        $likes_set = Rating::from('likes')
            ->select('whom_id as whom')
            ->where('who_id', $user_id)
            ->whereDate('updated_at', '>', $period)
            ->get()
            ->toArray();
        $likes_get = Rating::from('likes')
            ->select('who_id as who')
            ->where('whom_id', $user_id)
            ->whereDate('updated_at', '>', $period)
            ->get()
            ->toArray();
        $set = [];
        $get = [];
        foreach ($likes_set as $item)
        {
            $set[] = $item['whom'];
        }
        foreach ($likes_get as $item)
        {
            $get[] = $item['who'];
        }
        $matches = 0;
        foreach ($set as $like)
        {
            if (in_array($like, $get)) {
                $matches++;
            }
        }
        return $matches;
    }
    
    public static function setRating($user_id)
    {
        Rating::from('users')
            ->where('id', $user_id)
            ->update(['rating' => Rating::calculateRating($user_id)]);
    }
}